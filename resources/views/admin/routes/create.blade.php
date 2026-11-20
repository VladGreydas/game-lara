@extends('admin.layout')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-[#eab308] font-serif">{{ __('admin.add_route') }}</h2>
</div>

<form method="POST" action="{{ route('admin.routes.store') }}" class="industrial-card">
    @csrf

    <div class="p-6 space-y-6">
        <!-- From City -->
        <div>
            <label class="block text-sm font-semibold text-[#a3a3a3] mb-2">{{ __('admin.from') }}</label>
            <select name="from_city_id"
                class="w-full px-4 py-2 bg-[#1f1f1f] border border-[#3f3f3f] rounded text-[#e5e5e5] focus:outline-none focus:border-[#eab308]">
                <option value="">{{ __('admin.select_city') }}</option>
                @foreach($cities as $city)
                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- To Type -->
        <div>
            <label class="block text-sm font-semibold text-[#a3a3a3] mb-2">{{ __('admin.to') }}</label>
            <div class="flex gap-4">
                <label class="flex items-center gap-2">
                    <input type="radio" name="to_type" value="city" checked
                        class="w-4 h-4 bg-[#1f1f1f] border-[#3f3f3f] text-[#eab308] focus:ring-[#eab308]"
                        onchange="document.getElementById('to_city').style.display='block'; document.getElementById('to_location').style.display='none'">
                    <span class="text-[#e5e5e5]">{{ __('admin.city') }}</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="radio" name="to_type" value="location"
                        class="w-4 h-4 bg-[#1f1f1f] border-[#3f3f3f] text-[#eab308] focus:ring-[#eab308]"
                        onchange="document.getElementById('to_city').style.display='none'; document.getElementById('to_location').style.display='block'">
                    <span class="text-[#e5e5e5]">{{ __('admin.location') }}</span>
                </label>
            </div>
        </div>

        <!-- To City -->
        <div id="to_city">
            <label class="block text-sm font-semibold text-[#a3a3a3] mb-2">{{ __('admin.to_city') }}</label>
            <select name="to_city_id"
                class="w-full px-4 py-2 bg-[#1f1f1f] border border-[#3f3f3f] rounded text-[#e5e5e5] focus:outline-none focus:border-[#eab308]">
                <option value="">{{ __('admin.select_city') }}</option>
                @foreach($cities as $city)
                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- To Location -->
        <div id="to_location" style="display:none">
            <label class="block text-sm font-semibold text-[#a3a3a3] mb-2">{{ __('admin.to_location') }}</label>
            <select name="to_location_id"
                class="w-full px-4 py-2 bg-[#1f1f1f] border border-[#3f3f3f] rounded text-[#e5e5e5] focus:outline-none focus:border-[#eab308]">
                <option value="">{{ __('admin.select_location') }}</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}">{{ $location->name }} ({{ $location->city->name ?? '' }})</option>
                @endforeach
            </select>
        </div>

        <!-- Distance -->
        <div>
            <label class="block text-sm font-semibold text-[#a3a3a3] mb-2">{{ __('admin.distance') }}</label>
            <input type="number" name="distance" min="1" value="10"
                class="w-full px-4 py-2 bg-[#1f1f1f] border border-[#3f3f3f] rounded text-[#e5e5e5] focus:outline-none focus:border-[#eab308]">
        </div>

        <!-- One Way -->
        <label class="flex items-center gap-3">
            <input type="checkbox" name="is_one_way" value="1"
                class="w-4 h-4 bg-[#1f1f1f] border-[#3f3f3f] rounded text-[#eab308] focus:ring-[#eab308]">
            <span class="text-[#e5e5e5]">{{ __('admin.one_way') }}</span>
        </label>
    </div>

    <div class="p-6 border-t border-[#3f3f3f] flex gap-4">
        <button type="submit" class="industrial-btn px-6 py-2">{{ __('admin.create') }}</button>
        <a href="{{ route('admin.routes.index') }}" class="industrial-btn px-6 py-2">{{ __('admin.cancel') }}</a>
    </div>
</form>
@endsection
