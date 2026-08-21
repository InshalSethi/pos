<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AcuteBills Desktop Authentication</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: #f8fafc;
        }
        .card {
            background: rgba(30, 41, 59, 0.75);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 48px 40px;
            max-width: 460px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.4s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .icon-box {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px auto;
            box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4);
        }
        .icon-box svg { width: 36px; height: 36px; color: #ffffff; }
        h1 { font-size: 24px; font-weight: 800; margin-bottom: 10px; letter-spacing: -0.02em; }
        p { color: #94a3b8; font-size: 14px; line-height: 1.6; margin-bottom: 28px; }
        .user-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            padding: 8px 18px;
            border-radius: 100px;
            font-size: 13px;
            font-weight: 600;
            color: #e2e8f0;
            margin-bottom: 24px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            background: #38bdf8;
            color: #0f172a;
            font-weight: 700;
            font-size: 15px;
            padding: 14px 24px;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(56, 189, 248, 0.3);
        }
        .btn:hover { background: #7dd3fc; transform: translateY(-1px); }
        .status-dot { width: 8px; height: 8px; background: #10b981; border-radius: 50%; display: inline-block; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-box">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h1>Authenticated Successfully</h1>
        
        <div class="user-badge">
            <span class="status-dot"></span>
            <span>{{ $user['email'] }}</span>
        </div>

        <p>You have signed in to AcuteBills Cloud. Launching your session in the Desktop Application...</p>

        <button onclick="launchDesktop()" class="btn">
            Open AcuteBills Desktop App
        </button>
    </div>

    <script>
        const token = @json($token);
        const user = @json($user);
        const license = @json($license ?? null);
        
        let deepLink = `acutebills://auth-callback?token=${encodeURIComponent(token)}&email=${encodeURIComponent(user.email)}&name=${encodeURIComponent(user.name || '')}`;
        if (license) {
            deepLink += `&license_key=${encodeURIComponent(license.license_key || '')}&plan=${encodeURIComponent(license.plan || '')}&start_date=${encodeURIComponent(license.start_date || '')}&expires_at=${encodeURIComponent(license.expires_at || '')}`;
        }

        function launchDesktop() {
            window.location.href = deepLink;
        }

        // Trigger deep link automatically
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(launchDesktop, 300);
        });
    </script>
</body>
</html>
