const { contextBridge, ipcRenderer } = require('electron');

contextBridge.exposeInMainWorld('electronAPI', {
    // System metadata
    isElectron: true,
    platform: process.platform,

    // Thermal Printer & Hardware IPC
    printReceipt: (payload) => ipcRenderer.invoke('print-receipt', payload),
    openCashDrawer: () => ipcRenderer.invoke('open-cash-drawer'),

    // App Control
    toggleFullscreen: () => ipcRenderer.send('toggle-fullscreen'),
    getNetworkStatus: () => ipcRenderer.invoke('get-network-status'),
    getDeviceId: () => ipcRenderer.invoke('get-device-id'),

    // Offline Sync IPC Listeners
    onSyncTrigger: (callback) => ipcRenderer.on('trigger-sync', (_event, value) => callback(value)),

    // External Browser Auth IPC
    openExternalAuth: (url) => ipcRenderer.invoke('open-external-auth', url),

    // Deep Linking Web Auth Listener
    onWebAuth: (callback) => ipcRenderer.on('handle-web-auth', (_event, value) => callback(value)),
});

