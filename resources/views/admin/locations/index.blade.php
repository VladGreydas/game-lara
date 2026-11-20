@extends('admin.layout')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-[#eab308] font-serif">{{ __('admin.locations') }}</h2>
    <p class="text-[#a3a3a3] mt-2">{{ __('admin.locations_desc') }}</p>
</div>

<div class="industrial-card overflow-x-auto">
    <table class="w-full">
        <thead>
            <tr class="border-b border-[#3f3f3f]">
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.id') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.name') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.city') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.type') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.level') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.resource') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($locations as $location)
            <tr class="border-b border-[#2d2d2d] hover:bg-[#2d2d2d]">
                <td class="px-6 py-4 text-[#e5e5e5]">{{ $location->id }}</td>
                <td class="px-6 py-4 text-[#e5e5e5]">{{ $location->name }}</td>
                <td class="px-6 py-4 text-[#a3a3a3]">{{ $location->city->name ?? '—' }}</td>
                <td class="px-6 py-4 text-[#a3a3a3]">{{ $location->type }}</td>
                <td class="px-6 py-4 text-[#e5e5e5]">{{ $location->lvl }}</td>
                <td class="px-6 py-4 text-[#a3a3a3]">{{ $location->locationResource->resource->name ?? '—' }}</td>
                <td class="px-6 py-4">
                    <a href="{{ route('admin.locations.edit', $location) }}"
                        class="text-[#eab308] hover:underline text-sm">{{ __('admin.edit') }}</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-[#a3a3a3]">{{ __('admin.no_locations') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<x-admin-pagination :paginator="$locations" />
@endsection
