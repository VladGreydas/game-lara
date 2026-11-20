@extends('admin.layout')

@section('content')
<div class="mb-8 flex items-center justify-between">
    <div>
        <h2 class="text-3xl font-bold text-[#eab308] font-serif">{{ __('admin.routes') }}</h2>
        <p class="text-[#a3a3a3] mt-2">{{ __('admin.routes_desc') }}</p>
    </div>
    <a href="{{ route('admin.routes.create') }}" class="industrial-btn px-6 py-2">{{ __('admin.add_route') }}</a>
</div>

{{-- Filters --}}
<div class="industrial-card mb-6 p-4">
    <form method="GET" action="{{ route('admin.routes.index') }}" class="space-y-4">
        <div class="flex items-center gap-4 flex-wrap">
            <label class="text-sm font-semibold text-[#a3a3a3]">{{ __('admin.search') }}:</label>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="{{ __('admin.search_placeholder') }}"
                class="bg-[#1f1f1f] border border-[#3f3f3f] rounded px-3 py-1.5 text-[#e5e5e5] text-sm focus:border-[#eab308] focus:outline-none w-64">

            <label class="text-sm font-semibold text-[#a3a3a3]">{{ __('admin.type') }}:</label>
            <select name="type" class="bg-[#1f1f1f] border border-[#3f3f3f] rounded px-3 py-1.5 text-[#e5e5e5] text-sm focus:border-[#eab308] focus:outline-none">
                <option value="">{{ __('admin.all_types') }}</option>
                @foreach($routeTypes as $type)
                    <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ __('admin.route_type_' . $type) }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-4 flex-wrap">
            <label class="text-sm font-semibold text-[#a3a3a3]">{{ __('admin.distance') }}:</label>
            <input type="number" name="distance_min" value="{{ request('distance_min') }}"
                placeholder="{{ __('admin.distance_min') }}" min="0"
                class="bg-[#1f1f1f] border border-[#3f3f3f] rounded px-3 py-1.5 text-[#e5e5e5] text-sm focus:border-[#eab308] focus:outline-none w-32">
            <span class="text-[#a3a3a3]">—</span>
            <input type="number" name="distance_max" value="{{ request('distance_max') }}"
                placeholder="{{ __('admin.distance_max') }}" min="0"
                class="bg-[#1f1f1f] border border-[#3f3f3f] rounded px-3 py-1.5 text-[#e5e5e5] text-sm focus:border-[#eab308] focus:outline-none w-32">
            <span class="text-[#a3a3a3] text-sm">km</span>

            <button type="submit" class="industrial-btn px-4 py-1.5 text-sm">{{ __('admin.filter') }}</button>
            @if(request('search') || request('type') || request('distance_min') || request('distance_max'))
                <a href="{{ route('admin.routes.index') }}" class="text-[#a3a3a3] hover:text-[#e5e5e5] text-sm">{{ __('admin.clear_filter') }}</a>
            @endif
        </div>
    </form>
</div>

<div class="industrial-card overflow-x-auto">
    <table class="w-full">
        <thead>
            <tr class="border-b border-[#3f3f3f]">
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.id') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.from') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.to') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.distance') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($routes as $route)
            <tr class="border-b border-[#2d2d2d] hover:bg-[#2d2d2d]">
                <td class="px-6 py-4 text-[#e5e5e5]">{{ $route->id }}</td>
                <td class="px-6 py-4 text-[#e5e5e5]">
                    @if($route->fromCity)
                        {{ $route->fromCity->name }}
                    @elseif($route->fromLocation)
                        {{ $route->fromLocation->name }} ({{ $route->fromLocation->city->name ?? '' }})
                    @else
                        —
                    @endif
                </td>
                <td class="px-6 py-4 text-[#a3a3a3]">
                    @if($route->toCity)
                        {{ $route->toCity->name }}
                    @elseif($route->toLocation)
                        {{ $route->toLocation->name }} ({{ $route->toLocation->city->name ?? '' }})
                    @endif
                </td>
                <td class="px-6 py-4 text-[#e5e5e5]">{{ $route->distance_km }} km</td>
                <td class="px-6 py-4">
                    <form method="POST" action="{{ route('admin.routes.destroy', $route) }}" class="inline"
                        onsubmit="return confirm('{{ __('admin.confirm_delete') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-[#f87171] hover:underline text-sm">{{ __('admin.delete') }}</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-8 text-center text-[#a3a3a3]">{{ __('admin.no_routes') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<x-admin-pagination :paginator="$routes" />
@endsection
