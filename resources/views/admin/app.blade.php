<!DOCTYPE html>
<html lang="en" class="">
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
    <title>Admin Panel</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/admin/admin.js'])
    
    <style>
        body { font-family: 'figtree', sans-serif; background-color: #f3f4f6; }
        html.dark, html.dark body { background-color: #111827 !important; color: #f9fafb !important; }
        .sidebar-link { transition: all 0.2s; }
        .sidebar-link.router-link-active { background-color: #eeebff; color: #5b21b6; font-weight: 600; border-left: 4px solid #5b21b6; }
        html.dark .sidebar-link.router-link-active { background-color: rgba(91, 33, 182, 0.15); color: #c4b5fd; border-left: 4px solid #8b5cf6; }
        .sidebar-link.router-link-active i { color: #5b21b6; }
        html.dark .sidebar-link.router-link-active i { color: #c4b5fd; }
        .sidebar-link:not(.router-link-active):hover { background-color: #f9fafb; color: #111827; }
        html.dark .sidebar-link:not(.router-link-active):hover { background-color: #1f2937; color: #f3f4f6; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        html.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }
    </style>
</head>
<body class="text-gray-600 dark:text-gray-300 antialiased h-screen flex overflow-hidden bg-gray-50 dark:bg-gray-900">
    <div id="admin-app" class="w-full h-full flex overflow-hidden"></div>
</body>
</html>
