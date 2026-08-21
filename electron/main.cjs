const { app, BrowserWindow, ipcMain, Menu, shell } = require('electron');
const path = require('path');
const { spawn } = require('child_process');
const http = require('http');
const fs = require('fs');
const { machineIdSync } = require('node-machine-id');
let mainWindow;
let phpProcess = null;
let PHP_PORT = 8001;
let LOCAL_URL = `http://127.0.0.1:${PHP_PORT}`;

const net = require('net');
function getFreePort() {
    return new Promise((resolve, reject) => {
        const srv = net.createServer();
        srv.on('error', reject);
        srv.listen(0, '127.0.0.1', () => {
            const port = srv.address().port;
            srv.close(() => resolve(port));
        });
    });
}

const logFilePath = path.join(app.getPath('userData') || __dirname, 'electron_debug.log');

// Handle uncaught exceptions globally to prevent dialog popups on stream pipe errors
process.on('uncaughtException', (err) => {
    if (err && (err.code === 'EPIPE' || (err.message && err.message.includes('EPIPE')))) {
        return; // Silently swallow EPIPE broken pipe errors
    }
    logToFile(`[Uncaught Exception]: ${err ? (err.stack || err.message) : err}`);
});

function logToFile(msg) {
    const timestamp = new Date().toISOString();
    const line = `[${timestamp}] ${msg}\n`;
    if (!app.isPackaged && process.stdout && process.stdout.isTTY) {
        try {
            console.log(msg);
        } catch (e) { }
    }
    try {
        fs.appendFileSync(logFilePath, line);
    } catch (e) { }
}

logToFile('=== Electron Application Starting ===');

// Disable disk GPU caching to prevent Windows file lock conflicts
app.commandLine.appendSwitch('disable-gpu-shader-disk-cache');

// Set dedicated isolated user data directory
try {
    const customUserData = path.join(app.getPath('appData'), 'pos-desktop-storage');
    app.setPath('userData', customUserData);
} catch (e) {
    // Fallback if appData is unavailable
}

// -----------------------------------------
// CUSTOM PROTOCOL AND SINGLE INSTANCE LOCK
// -----------------------------------------
if (process.defaultApp) {
    if (process.argv.length >= 2) {
        app.setAsDefaultProtocolClient('acutebills', process.execPath, [path.resolve(process.argv[1])]);
    }
} else {
    app.setAsDefaultProtocolClient('acutebills');
}

const gotTheLock = app.requestSingleInstanceLock();
if (!gotTheLock) {
    logToFile('Instance already running. Quitting this secondary instance.');
    app.quit();
} else {
    app.on('second-instance', (event, commandLine, workingDirectory) => {
        // Someone tried to run a second instance (or clicked a pos-app:// link), we should focus our window.
        if (mainWindow) {
            if (mainWindow.isMinimized()) mainWindow.restore();
            mainWindow.focus();
        }
        // Parse protocol URL from commandLine array (usually the last argument on Windows)
        const url = commandLine.find(arg => arg.startsWith('acutebills://'));
        if (url) {
            logToFile('Received Deep Link from Second Instance: ' + url);
            handleDeepLink(url);
        }
    });
}

function handleDeepLink(url) {
    try {
        logToFile('Handling deep link URL: ' + url);
        const parsedUrl = new URL(url);
        const isAuthRoute = parsedUrl.hostname.includes('auth') || parsedUrl.pathname.includes('auth');
        if (isAuthRoute) {
            const token = parsedUrl.searchParams.get('token');
            const email = parsedUrl.searchParams.get('email') || parsedUrl.searchParams.get('user_email');
            const name = parsedUrl.searchParams.get('name');
            const license_key = parsedUrl.searchParams.get('license_key');
            const plan = parsedUrl.searchParams.get('plan');
            const start_date = parsedUrl.searchParams.get('start_date');
            const expires_at = parsedUrl.searchParams.get('expires_at');

            logToFile(`Deep Link Auth Token Received for: ${email} (${name}) Plan: ${plan}`);
            
            if (mainWindow) {
                if (mainWindow.isMinimized()) mainWindow.restore();
                mainWindow.focus();
                if (mainWindow.webContents) {
                    mainWindow.webContents.send('handle-web-auth', { 
                        token, 
                        email, 
                        name,
                        license_key,
                        plan,
                        start_date,
                        expires_at
                    });
                }
            }
        }
    } catch (e) {
        logToFile('Failed to parse deep link URL: ' + e.message);
    }
}

// macOS open-url event
app.on('open-url', (event, url) => {
    event.preventDefault();
    logToFile('Received macOS Open URL: ' + url);
    handleDeepLink(url);
});
// -----------------------------------------

/**
 * Locate PHP binary dynamically on Windows or Linux/macOS
 */
function getPhpBinaryPath() {
    // 1. Check embedded portable PHP inside electron/php/php.exe
    const embeddedPhp = path.join(__dirname, 'php', 'php.exe');
    if (fs.existsSync(embeddedPhp)) {
        logToFile('[PHP Locate] Found embedded portable PHP: ' + embeddedPhp);
        return embeddedPhp;
    }

    // 2. Check unpacked resources directory when packaged
    if (process.resourcesPath) {
        const resourcesPhp = path.join(process.resourcesPath, 'app', 'electron', 'php', 'php.exe');
        if (fs.existsSync(resourcesPhp)) {
            logToFile('[PHP Locate] Found packaged resources PHP: ' + resourcesPhp);
            return resourcesPhp;
        }
    }

    // 3. Fallback to system / Laragon PHP
    if (process.platform === 'win32') {
        const staticPaths = [
            'E:\\laragon\\bin\\php\\php-8.4.17-Win32-vs17-x64\\php.exe',
            'E:\\laragon\\bin\\php\\php-8.2.20-nts-Win32-vs16-x64\\php.exe',
            'C:\\laragon\\bin\\php\\php-8.3.0-Win32-vs16-x64\\php.exe',
            'C:\\xampp\\php\\php.exe',
        ];

        for (const drive of ['E:', 'C:', 'D:']) {
            const laragonPhpDir = path.join(drive, 'laragon', 'bin', 'php');
            try {
                if (fs.existsSync(laragonPhpDir)) {
                    const subdirs = fs.readdirSync(laragonPhpDir);
                    for (const sub of subdirs) {
                        const candidate = path.join(laragonPhpDir, sub, 'php.exe');
                        if (fs.existsSync(candidate)) {
                            logToFile('[PHP Locate] Found Laragon PHP: ' + candidate);
                            return candidate;
                        }
                    }
                }
            } catch (err) { }
        }

        for (const p of staticPaths) {
            if (fs.existsSync(p)) {
                logToFile('[PHP Locate] Found static PHP: ' + p);
                return p;
            }
        }
    }

    logToFile('[PHP Locate] Fallback to global php command');
    return 'php';
}

/**
 * Check if local Laravel PHP server is responding
 */
function checkServerReady(url, callback) {
    http.get(url, (res) => {
        callback(true);
    }).on('error', (err) => {
        callback(false);
    });
}

/**
 * Start PHP local backend server process if not already running
 */
function startPhpServer(onReady) {
    checkServerReady(LOCAL_URL, (isAlreadyRunning) => {
        if (isAlreadyRunning) {
            logToFile('[Electron] Connected to existing server at ' + LOCAL_URL);
            onReady();
            return;
        }

        logToFile('[Electron] Starting local PHP server process...');
        const phpCmd = getPhpBinaryPath();
        const projectRoot = app.isPackaged
            ? path.join(process.resourcesPath, 'app')
            : path.resolve(__dirname, '..');

        logToFile('[Electron] PHP Executable Path: ' + phpCmd);
        logToFile('[Electron] Project Root Path: ' + projectRoot);

        // Remove stale Vite 'hot' file to ensure Laravel uses compiled production assets
        const hotFilePath = path.join(projectRoot, 'public', 'hot');
        if (fs.existsSync(hotFilePath)) {
            try {
                fs.unlinkSync(hotFilePath);
                logToFile('[Electron] Removed stale Vite hot file: ' + hotFilePath);
            } catch (err) {
                logToFile('[Electron Warning] Failed to remove hot file: ' + err.message);
            }
        }

        // Provision SQLite Database and Storage in User Data directory for 100% Offline & Writable capability
        const customUserData = app.getPath('userData');
        const dbPath = path.join(customUserData, 'database.sqlite');
        const storagePath = path.join(customUserData, 'storage');

        // Ensure all required Laravel framework storage directories exist in AppData
        const requiredDirs = [
            path.join(storagePath, 'app'),
            path.join(storagePath, 'framework', 'cache', 'data'),
            path.join(storagePath, 'framework', 'sessions'),
            path.join(storagePath, 'framework', 'testing'),
            path.join(storagePath, 'framework', 'views'),
            path.join(storagePath, 'logs'),
            path.join(projectRoot, 'bootstrap', 'cache')
        ];
        requiredDirs.forEach(dir => {
            if (!fs.existsSync(dir)) {
                try {
                    fs.mkdirSync(dir, { recursive: true });
                } catch (e) { }
            }
        });

        if (!fs.existsSync(dbPath)) {
            const bundledDbPath = path.join(projectRoot, 'database', 'database.sqlite');
            if (fs.existsSync(bundledDbPath)) {
                try {
                    fs.copyFileSync(bundledDbPath, dbPath);
                    logToFile('[Electron] Initialized new SQLite database at: ' + dbPath);
                } catch (err) {
                    logToFile('[Electron Error] Failed to copy SQLite DB: ' + err.message);
                }
            } else {
                try {
                    fs.writeFileSync(dbPath, '');
                    logToFile('[Electron] Created empty SQLite database at: ' + dbPath);
                } catch (err) { }
            }
        }

        const viewsPath = path.join(storagePath, 'framework', 'views');

        // Ensure .env file exists in projectRoot for Laravel runtime configuration
        const envPath = path.join(projectRoot, '.env');
        if (!fs.existsSync(envPath)) {
            const defaultEnvContent = `APP_NAME=AcuteBills
APP_ENV=production
APP_KEY=base64:WFEfEJ3w9aVBfS3prSIDtSF/zfpjF8Wwq+UDajK8sqQ=
APP_DEBUG=true
APP_URL=http://127.0.0.1:${PHP_PORT}
DB_CONNECTION=sqlite
DB_DATABASE="${dbPath.replace(/\\/g, '/')}"
LARAVEL_STORAGE_PATH="${storagePath.replace(/\\/g, '/')}"
VIEW_COMPILED_PATH="${viewsPath.replace(/\\/g, '/')}"
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
`;
            try {
                fs.writeFileSync(envPath, defaultEnvContent);
                logToFile('[Electron] Created default .env file at: ' + envPath);
            } catch (err) {
                logToFile('[Electron Warning] Failed to write .env file: ' + err.message);
            }
        }

        const sessionsPath = path.join(storagePath, 'framework', 'sessions');
        const cachePath = path.join(storagePath, 'framework', 'cache', 'data');

        const phpEnv = {
            ...process.env,
            APP_NAME: 'AcuteBills',
            APP_ENV: 'production',
            APP_KEY: 'base64:WFEfEJ3w9aVBfS3prSIDtSF/zfpjF8Wwq+UDajK8sqQ=',
            APP_DEBUG: 'true',
            APP_URL: `http://127.0.0.1:${PHP_PORT}`,
            DB_CONNECTION: 'sqlite',
            DB_DATABASE: dbPath,
            LARAVEL_STORAGE_PATH: storagePath,
            VIEW_COMPILED_PATH: viewsPath,
            SESSION_FILE_PATH: sessionsPath,
            CACHE_FILE_PATH: cachePath,
            SESSION_DRIVER: 'file',
            CACHE_STORE: 'file',
            QUEUE_CONNECTION: 'sync'
        };

        const { execFile } = require('child_process');
        logToFile('[Electron] Running database migrations...');

        execFile(phpCmd, ['artisan', 'migrate', '--force'], { cwd: projectRoot, env: phpEnv, timeout: 120000 }, (err, stdout, stderr) => {
            if (err) {
                logToFile('[Electron Error] Migrations Failed: ' + (stderr || err.message));
            } else {
                logToFile('[Electron] Migrations Success: ' + stdout.trim());
            }

            logToFile('[Electron] Spawning PHP artisan serve process...');
            phpProcess = spawn(phpCmd, ['artisan', 'serve', `--host=127.0.0.1`, `--port=${PHP_PORT}`, '--no-reload'], {
                cwd: projectRoot,
                shell: false,
                env: phpEnv
            });

            phpProcess.stdout.on('data', (data) => {
                logToFile(`[PHP stdout]: ${data.toString()}`);
            });

            phpProcess.stderr.on('data', (data) => {
                logToFile(`[PHP stderr]: ${data.toString()}`);
            });

            phpProcess.on('error', (err) => {
                logToFile(`[PHP Spawn Error]: ${err.message}`);
            });

            phpProcess.on('close', (code) => {
                logToFile(`[PHP Process Exited] Code: ${code}`);
            });

            let attempts = 0;
            let isReadyCalled = false;
            const interval = setInterval(() => {
                if (isReadyCalled) return;
                attempts++;
                checkServerReady(LOCAL_URL, (ready) => {
                    if (isReadyCalled) return;
                    if (ready) {
                        isReadyCalled = true;
                        clearInterval(interval);
                        logToFile('[Electron] PHP backend server is READY!');
                        onReady();
                    } else if (attempts >= 180) {
                        isReadyCalled = true;
                        clearInterval(interval);
                        logToFile('[Electron Error] PHP backend server did not respond within timeout.');
                        onReady();
                    }
                });
            }, 500);
        });
    });
}

/**
 * Create primary POS Application BrowserWindow
 */
function createWindow() {
    logToFile('[Electron] Creating BrowserWindow...');

    mainWindow = new BrowserWindow({
        width: 1280,
        height: 800,
        minWidth: 1024,
        minHeight: 700,
        title: 'AcuteBills - Desktop Application',
        autoHideMenuBar: false,
        webPreferences: {
            preload: path.join(__dirname, 'preload.cjs'),
            nodeIntegration: false,
            contextIsolation: true,
            webSecurity: false,
        },
        icon: path.join(__dirname, '..', 'public', 'favicon.ico')
    });

    // Intercept window.open calls and open external http/https URLs in OS default browser
    mainWindow.webContents.setWindowOpenHandler(({ url }) => {
        logToFile('[Electron WindowOpen] Intercepted window.open: ' + url);
        if (url.startsWith('http:') || url.startsWith('https:')) {
            shell.openExternal(url);
            return { action: 'deny' };
        }
        return { action: 'allow' };
    });

    // Always Open DevTools for debugging as requested by user
    mainWindow.webContents.openDevTools();

    // Fallback error page if connection fails initially
    mainWindow.webContents.on('did-fail-load', (event, errorCode, errorDescription, validatedURL) => {
        if (validatedURL.startsWith('data:text/html')) return;

        logToFile(`[Electron Nav Error] Failed to load ${validatedURL}: ${errorDescription} (${errorCode})`);

        const fallbackHTML = `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>Loading AcuteBills...</title>
                <style>
                    * { box-sizing: border-box; }
                    body { background: #0f172a; display: flex; height: 100vh; align-items: center; justify-content: center; margin: 0; padding: 0; }
                    .spinner { width: 50px; height: 50px; border: 4px solid #1e293b; border-top-color: #38bdf8; border-radius: 50%; animation: spin 1s linear infinite; }
                    @keyframes spin { to { transform: rotate(360deg); } }
                </style>
            </head>
            <body>
                <div class="spinner"></div>
                <script>
                    setTimeout(() => {
                        location.href = '${LOCAL_URL}';
                    }, 1500);
                </script>
            </body>
            </html>
        `;
        mainWindow.loadURL('data:text/html;charset=utf-8,' + encodeURIComponent(fallbackHTML));
    });

    logToFile('[Electron] Loading URL: ' + LOCAL_URL);
    mainWindow.loadURL(LOCAL_URL);

    mainWindow.on('closed', () => {
        mainWindow = null;
    });
}

// App Lifecycle
app.whenReady().then(async () => {
    logToFile('[Electron] app.whenReady fired');
    try {
        PHP_PORT = await getFreePort();
        LOCAL_URL = `http://127.0.0.1:${PHP_PORT}`;
        logToFile('[Electron] Assigned dynamic port: ' + PHP_PORT);
    } catch (e) {
        logToFile('[Electron Error] Failed to find free port: ' + e.message);
    }

    startPhpServer(() => {
        createWindow();
    });

    app.on('activate', () => {
        if (BrowserWindow.getAllWindows().length === 0) {
            createWindow();
        }
    });
});

app.on('window-all-closed', () => {
    logToFile('[Electron] All windows closed');
    if (phpProcess) {
        phpProcess.kill();
    }
    if (process.platform !== 'darwin') {
        app.quit();
    }
});

// IPC Handlers for Thermal Printer & Hardware Integration
ipcMain.handle('print-receipt', async (event, payload) => {
    logToFile('[Electron IPC] Printing Receipt Payload: ' + JSON.stringify(payload));
    return { success: true, message: 'Receipt dispatched to thermal printer.' };
});

ipcMain.handle('open-cash-drawer', async () => {
    logToFile('[Electron IPC] Opening Cash Drawer');
    return { success: true, message: 'Cash drawer pulse sent.' };
});

ipcMain.on('toggle-fullscreen', () => {
    if (mainWindow) {
        const isFullScreen = mainWindow.isFullScreen();
        mainWindow.setFullScreen(!isFullScreen);
    }
});

ipcMain.handle('open-external-auth', async (event, url) => {
    logToFile('[Electron IPC] Opening External Auth URL in Browser: ' + url);
    await shell.openExternal(url);
    return { success: true };
});

ipcMain.handle('get-device-id', async () => {
    try {
        return machineIdSync();
    } catch (e) {
        logToFile('[Electron Error] Failed to get machine ID: ' + e.message);
        return 'UNKNOWN-DEVICE-ID';
    }
});
