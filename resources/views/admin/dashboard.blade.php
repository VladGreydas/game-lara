@extends('admin.layout')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-[#eab308] font-serif">{{ __('admin.dashboard') }}</h2>
    <p class="text-[#a3a3a3] mt-2">{{ __('admin.dashboard_desc') }}</p>
</div>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Players -->
    <div class="industrial-card">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-[#a3a3a3] mb-1">{{ __('admin.players') }}</p>
                    <p class="text-4xl font-bold text-[#e5e5e5]">{{ $playersCount }}</p>
                </div>
                <div class="text-5xl">👤</div>
            </div>
            <a href="{{ route('admin.players.index') }}" class="mt-4 inline-block text-sm text-[#eab308] hover:underline">{{ __('admin.view_all') }}</a>
        </div>
    </div>

    <!-- Cities -->
    <div class="industrial-card">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-[#a3a3a3] mb-1">{{ __('admin.cities') }}</p>
                    <p class="text-4xl font-bold text-[#e5e5e5]">{{ $citiesCount }}</p>
                </div>
                <div class="text-5xl">🏙️</div>
            </div>
            <a href="{{ route('admin.cities.index') }}" class="mt-4 inline-block text-sm text-[#eab308] hover:underline">{{ __('admin.view_all') }}</a>
        </div>
    </div>

    <!-- Locations -->
    <div class="industrial-card">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-[#a3a3a3] mb-1">{{ __('admin.locations') }}</p>
                    <p class="text-4xl font-bold text-[#e5e5e5]">{{ $locationsCount }}</p>
                </div>
                <div class="text-5xl">📍</div>
            </div>
            <a href="{{ route('admin.locations.index') }}" class="mt-4 inline-block text-sm text-[#eab308] hover:underline">{{ __('admin.view_all') }}</a>
        </div>
    </div>

    <!-- Contracts -->
    <div class="industrial-card">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-[#a3a3a3] mb-1">{{ __('admin.available_contracts') }}</p>
                    <p class="text-4xl font-bold text-[#e5e5e5]">{{ $contractsCount }}</p>
                </div>
                <div class="text-5xl">📋</div>
            </div>
            <a href="{{ route('admin.contracts.index') }}" class="mt-4 inline-block text-sm text-[#eab308] hover:underline">{{ __('admin.view_all') }}</a>
        </div>
    </div>

    <!-- Active Travels -->
    <div class="industrial-card">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-[#a3a3a3] mb-1">{{ __('admin.active_travels') }}</p>
                    <p class="text-4xl font-bold text-[#e5e5e5]">{{ $activeTravels }}</p>
                </div>
                <div class="text-5xl">🚂</div>
            </div>
        </div>
    </div>
</div>
@endsection
