<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ request()->cookie('theme') === 'dark' ? 'dark' : '' }}">
<head>
    <script>
        (function() {
            // Synchronously check localStorage or cookies before the DOM paints
            const savedTheme = localStorage.getItem('theme') || '{{ request()->cookie("theme") }}' || 'light';
            if (savedTheme === 'dark' || (savedTheme === 'match system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>
    <style>
        html.dark, html.dark body {
            background-color: #111827 !important;
            color: #f9fafb !important;
        }
    </style>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'POS System') }}</title>

    <script>
        window.systemTimezones = @json(\DateTimeZone::listIdentifiers());
        window.baseCurrency = @json(auth()->check() && auth()->user()->company ? auth()->user()->company->base_currency : 'USD');
    </script>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script type="module" src="https://unpkg.com/@splinetool/viewer@1.0.91/build/spline-viewer.js"></script>
</head>
<body class="font-sans antialiased text-[13px] font-medium text-gray-600 tracking-wide">
    <div id="app"></div>
</body>
</html>
