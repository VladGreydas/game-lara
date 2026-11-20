@extends('admin.layout')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-[#eab308] font-serif">{{ __('admin.cities') }}</h2>
    <p class="text-[#a3a3a3] mt-2">{{ __('admin.cities_desc') }}</p>
</div>

<div class="industrial-card overflow-x-auto">
    <table class="w-full">
        <thead>
            <tr class="border-b border-[#3f3f3f]">
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.id') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.name') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.level') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.resources') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.buildings') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cities as $city)
            <tr class="border-b border-[#2d2d2d] hover:bg-[#2d2d2d]">
                <td class="px-6 py-4 text-[#e5e5e5]">{{ $city->id }}</td>
                <td class="px-6 py-4 text-[#e5e5e5]">{{ $city->name }}</td>
                <td class="px-6 py-4 text-[#e5e5e5]">{{ $city->level }}</td>
                <td class="px-6 py-4 text-[#a3a3a3]">{{ $city->resources_count }}</td>
                <td class="px-6 py-4 text-[#a3a3a3]">
                    @if($city->has_shop) 🏪 @endif
                    @if($city->has_workshop) 🔧 @endif
                    @if($city->has_saloon) 🎰 @endif
                </td>
                <td class="px-6 py-4">
                    <a href="{{ route('admin.cities.edit', $city) }}"
                        class="text-[#eab308] hover:underline text-sm">{{ __('admin.edit') }}</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-[#a3a3a3]">{{ __('admin.no_cities') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<x-admin-pagination :paginator="$cities" />
@endsection
