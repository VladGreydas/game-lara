@extends('admin.layout')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-[#eab308] font-serif">{{ __('admin.resources') }}</h2>
    <p class="text-[#a3a3a3] mt-2">{{ __('admin.resources_desc') }}</p>
</div>

<div class="industrial-card overflow-x-auto">
    <table class="w-full">
        <thead>
            <tr class="border-b border-[#3f3f3f]">
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.id') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.name') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.tier') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.base_price') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.produced_from') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.cities_count') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resources as $resource)
            <tr class="border-b border-[#2d2d2d] hover:bg-[#2d2d2d]">
                <td class="px-6 py-4 text-[#e5e5e5]">{{ $resource->id }}</td>
                <td class="px-6 py-4 text-[#e5e5e5]">{{ $resource->name }}</td>
                <td class="px-6 py-4 text-[#a3a3a3]">T{{ $resource->tier }}</td>
                <td class="px-6 py-4 text-[#e5e5e5]">{{ $resource->base_price }}</td>
                <td class="px-6 py-4 text-[#a3a3a3]">{{ $resource->produced_from ?? '—' }}</td>
                <td class="px-6 py-4 text-[#a3a3a3]">{{ $resource->city_resources_count }}</td>
                <td class="px-6 py-4">
                    <a href="{{ route('admin.resources.edit', $resource) }}"
                        class="text-[#eab308] hover:underline text-sm">{{ __('admin.edit') }}</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-[#a3a3a3]">{{ __('admin.no_resources') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<x-admin-pagination :paginator="$resources" />
@endsection
