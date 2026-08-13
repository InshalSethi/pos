<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="notranslate" translate="no">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google" content="notranslate" />

    <title>{{ config('app.name', 'POS') }} - Business Onboarding</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900">
    <!-- Embed the Livewire Component -->
    <livewire:onboarding-wizard :company="$company ?? null" :currentStep="$currentStep ?? 1" :hasExistingActiveCompany="$hasExistingActiveCompany ?? false" />
    
    @livewireScripts
</body>
</html>
