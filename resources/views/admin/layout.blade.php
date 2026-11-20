<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} — {{ __('admin.panel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=jetbrains-mono:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#0f0f0f] text-[#e5e5e5]">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-[#1f1f1f] border-r border-[#3f3f3f] flex flex-col">
            <div class="p-6 border-b border-[#3f3f3f]">
                <h1 class="text-xl font-bold text-[#eab308] font-serif">{{ __('admin.panel') }}</h1>
            </div>
            <nav class="flex-1 p-4 space-y-2">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 rounded {{ request()->routeIs('admin.dashboard') ? 'bg-[#2d2d2d] text-[#eab308]' : 'text-[#e5e5e5] hover:bg-[#2d2d2d] hover:text-[#eab308]' }}">
                    📊 {{ __('admin.dashboard') }}
                </a>
                <a href="{{ route('admin.players.index') }}" class="flex items-center px-4 py-3 rounded {{ request()->routeIs('admin.players.*') ? 'bg-[#2d2d2d] text-[#eab308]' : 'text-[#e5e5e5] hover:bg-[#2d2d2d] hover:text-[#eab308]' }}">
                    👤 {{ __('admin.players') }}
                </a>
                <a href="{{ route('admin.cities.index') }}" class="flex items-center px-4 py-3 rounded {{ request()->routeIs('admin.cities.*') ? 'bg-[#2d2d2d] text-[#eab308]' : 'text-[#e5e5e5] hover:bg-[#2d2d2d] hover:text-[#eab308]' }}">
                    🏙️ {{ __('admin.cities') }}
                </a>
                <a href="{{ route('admin.locations.index') }}" class="flex items-center px-4 py-3 rounded {{ request()->routeIs('admin.locations.*') ? 'bg-[#2d2d2d] text-[#eab308]' : 'text-[#e5e5e5] hover:bg-[#2d2d2d] hover:text-[#eab308]' }}">
                    📍 {{ __('admin.locations') }}
                </a>
                <a href="{{ route('admin.routes.index') }}" class="flex items-center px-4 py-3 rounded {{ request()->routeIs('admin.routes.*') ? 'bg-[#2d2d2d] text-[#eab308]' : 'text-[#e5e5e5] hover:bg-[#2d2d2d] hover:text-[#eab308]' }}">
                    🛤️ {{ __('admin.routes') }}
                </a>
                <a href="{{ route('admin.contracts.index') }}" class="flex items-center px-4 py-3 rounded {{ request()->routeIs('admin.contracts.*') ? 'bg-[#2d2d2d] text-[#eab308]' : 'text-[#e5e5e5] hover:bg-[#2d2d2d] hover:text-[#eab308]' }}">
                    📋 {{ __('admin.contracts') }}
                </a>
                <a href="{{ route('admin.resources.index') }}" class="flex items-center px-4 py-3 rounded {{ request()->routeIs('admin.resources.*') ? 'bg-[#2d2d2d] text-[#eab308]' : 'text-[#e5e5e5] hover:bg-[#2d2d2d] hover:text-[#eab308]' }}">
                    ⚙️ {{ __('admin.resources') }}
                </a>
            </nav>
            <div class="p-4 border-t border-[#3f3f3f]">
                <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 rounded text-[#a3a3a3] hover:bg-[#2d2d2d] hover:text-[#e5e5e5]">
                    ← {{ __('admin.back_to_game') }}
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            @if(session('success'))
                <div class="mb-6 p-4 bg-[#14532c] border border-[#4ade80] rounded text-[#4ade80]">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 p-4 bg-[#7f1d1d] border border-[#f87171] rounded text-[#f87171]">
                    {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>
