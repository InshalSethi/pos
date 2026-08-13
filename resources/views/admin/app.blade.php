<!DOCTYPE html>
<html lang="en" class="notranslate" translate="no">
<head>
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            if (savedTheme === 'dark' || (['match system', 'system', 'auto'].includes(savedTheme) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google" content="notranslate" />
    <title>Admin Panel</title>

    <style>
        ::-webkit-scrollbar { width: 5px !important; height: 5px !important; }
        ::-webkit-scrollbar-track { background: transparent !important; }
        ::-webkit-scrollbar-thumb { background: rgba(0, 0, 0, 0.25) !important; border-radius: 10px !important; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(0, 0, 0, 0.5) !important; }
        html.dark, html.dark body {
            background-color: #09090b !important;
            color: #f4f4f5 !important;
        }
    </style>

    <!-- Local Scripts & Assets -->
    @vite(['resources/css/app.css', 'resources/js/admin/admin.js'])
</head>
<body class="notranslate font-sans antialiased text-[13px] font-medium text-zinc-600 dark:text-zinc-300 h-screen flex overflow-hidden bg-slate-50 dark:bg-zinc-950 selection:bg-black selection:text-white dark:selection:bg-white dark:selection:text-black">
    <div id="admin-app" class="w-full h-full flex overflow-hidden"></div>
</body>
</html>
