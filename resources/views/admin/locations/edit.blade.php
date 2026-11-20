@extends('admin.layout')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-[#eab308] font-serif">{{ __('admin.edit_location') }}</h2>
    <p class="text-[#a3a3a3] mt-2">{{ $location->name }} ({{ $location->city->name ?? '—' }})</p>
</div>

<form method="POST" action="{{ route('admin.locations.update', $location) }}" class="industrial-card">
    @csrf
    @method('PUT')

    <div class="p-6 space-y-6">
        <!-- Level -->
        <div>
            <label class="block text-sm font-semibold text-[#a3a3a3] mb-2">{{ __('admin.level') }}</label>
            <input type="number" name="lvl" value="{{ $location->lvl }}" min="1" max="10"
                class="w-full px-4 py-2 bg-[#1f1f1f] border border-[#3f3f3f] rounded text-[#e5e5e5] focus:outline-none focus:border-[#eab308]">
        </div>
    </div>

    <div class="p-6 border-t border-[#3f3f3f] flex gap-4">
        <button type="submit" class="industrial-btn px-6 py-2">{{ __('admin.save') }}</button>
        <a href="{{ route('admin.locations.index') }}" class="industrial-btn px-6 py-2">{{ __('admin.cancel') }}</a>
    </div>
</form>
@endsection
