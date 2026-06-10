<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Aura - Setup' }}</title>
    @vite(['resources/css/app.css'])
    @livewireStyles
</head>
<body class="antialiased text-[13px] font-medium text-gray-600 tracking-wide">
    {{ $slot }}
    @livewireScripts
</body>
</html>
