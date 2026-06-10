<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ request()->cookie('theme') === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Company Setup') }} - Onboarding</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script type="module" src="https://unpkg.com/@splinetool/viewer@1.0.91/build/spline-viewer.js"></script>
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900">
    <!-- Embed the Livewire Component -->
    <livewire:onboarding-wizard :draftCompanyId="$company->id ?? null" :resumeStep="$currentStep ?? 1" />
    
    @livewireScripts
</body>
</html>
