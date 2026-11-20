@extends('admin.layout')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-[#eab308] font-serif">{{ __('admin.edit_city') }}</h2>
    <p class="text-[#a3a3a3] mt-2">{{ $city->name }}</p>
</div>

<form method="POST" action="{{ route('admin.cities.update', $city) }}" class="industrial-card">
    @csrf
    @method('PUT')

    <div class="p-6 space-y-6">
        <!-- Level -->
        <div>
            <label class="block text-sm font-semibold text-[#a3a3a3] mb-2">{{ __('admin.level') }}</label>
            <input type="number" name="level" value="{{ $city->level }}" min="1" max="10"
                class="w-full px-4 py-2 bg-[#1f1f1f] border border-[#3f3f3f] rounded text-[#e5e5e5] focus:outline-none focus:border-[#eab308]">
        </div>

        <!-- Buildings -->
        <div class="space-y-3">
            <label class="block text-sm font-semibold text-[#a3a3a3]">{{ __('admin.buildings') }}</label>

            <label class="flex items-center gap-3">
                <input type="checkbox" name="has_shop" value="1" {{ $city->has_shop ? 'checked' : '' }}
                    class="w-4 h-4 bg-[#1f1f1f] border-[#3f3f3f] rounded text-[#eab308] focus:ring-[#eab308]">
                <span class="text-[#e5e5e5]">🏪 {{ __('admin.shop') }}</span>
            </label>

            <label class="flex items-center gap-3">
                <input type="checkbox" name="has_workshop" value="1" {{ $city->has_workshop ? 'checked' : '' }}
                    class="w-4 h-4 bg-[#1f1f1f] border-[#3f3f3f] rounded text-[#eab308] focus:ring-[#eab308]">
                <span class="text-[#e5e5e5]">🔧 {{ __('admin.workshop') }}</span>
            </label>

            <label class="flex items-center gap-3">
                <input type="checkbox" name="has_saloon" value="1" {{ $city->has_saloon ? 'checked' : '' }}
                    class="w-4 h-4 bg-[#1f1f1f] border-[#3f3f3f] rounded text-[#eab308] focus:ring-[#eab308]">
                <span class="text-[#e5e5e5]">🎰 {{ __('admin.saloon') }}</span>
            </label>
        </div>
    </div>

    <div class="p-6 border-t border-[#3f3f3f] flex gap-4">
        <button type="submit" class="industrial-btn px-6 py-2">{{ __('admin.save') }}</button>
        <a href="{{ route('admin.cities.index') }}" class="industrial-btn px-6 py-2">{{ __('admin.cancel') }}</a>
    </div>
</form>
@endsection
