@extends('admin.layout')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-[#eab308] font-serif">{{ __('admin.add_contract') }}</h2>
</div>

<form method="POST" action="{{ route('admin.contracts.store') }}" class="industrial-card">
    @csrf

    <div class="p-6 space-y-6">
        <!-- City -->
        <div>
            <label class="block text-sm font-semibold text-[#a3a3a3] mb-2">{{ __('admin.city') }}</label>
            <select name="city_id"
                class="w-full px-4 py-2 bg-[#1f1f1f] border border-[#3f3f3f] rounded text-[#e5e5e5] focus:outline-none focus:border-[#eab308]">
                <option value="">{{ __('admin.select_city') }}</option>
                @foreach($cities as $city)
                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Resource -->
        <div>
            <label class="block text-sm font-semibold text-[#a3a3a3] mb-2">{{ __('admin.resource') }}</label>
            <select name="resource_id"
                class="w-full px-4 py-2 bg-[#1f1f1f] border border-[#3f3f3f] rounded text-[#e5e5e5] focus:outline-none focus:border-[#eab308]">
                <option value="">{{ __('admin.select_resource') }}</option>
                @foreach($resources as $resource)
                    <option value="{{ $resource->id }}">{{ $resource->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Destination City -->
        <div>
            <label class="block text-sm font-semibold text-[#a3a3a3] mb-2">{{ __('admin.destination') }}</label>
            <select name="destination_city_id"
                class="w-full px-4 py-2 bg-[#1f1f1f] border border-[#3f3f3f] rounded text-[#e5e5e5] focus:outline-none focus:border-[#eab308]">
                <option value="">{{ __('admin.select_city') }}</option>
                @foreach($destinationCities as $city)
                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Quantity -->
        <div>
            <label class="block text-sm font-semibold text-[#a3a3a3] mb-2">{{ __('admin.quantity') }}</label>
            <input type="number" name="quantity" min="1" value="10"
                class="w-full px-4 py-2 bg-[#1f1f1f] border border-[#3f3f3f] rounded text-[#e5e5e5] focus:outline-none focus:border-[#eab308]">
        </div>

        <!-- Reward -->
        <div>
            <label class="block text-sm font-semibold text-[#a3a3a3] mb-2">{{ __('admin.reward') }}</label>
            <input type="number" name="reward" min="1" value="100"
                class="w-full px-4 py-2 bg-[#1f1f1f] border border-[#3f3f3f] rounded text-[#e5e5e5] focus:outline-none focus:border-[#eab308]">
        </div>
    </div>

    <div class="p-6 border-t border-[#3f3f3f] flex gap-4">
        <button type="submit" class="industrial-btn px-6 py-2">{{ __('admin.create') }}</button>
        <a href="{{ route('admin.contracts.index') }}" class="industrial-btn px-6 py-2">{{ __('admin.cancel') }}</a>
    </div>
</form>
@endsection
