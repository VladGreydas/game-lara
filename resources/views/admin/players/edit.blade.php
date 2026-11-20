@extends('admin.layout')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-[#eab308] font-serif">{{ __('admin.edit_player') }}</h2>
    <p class="text-[#a3a3a3] mt-2">{{ $player->user->name }} ({{ $player->user->email }})</p>
</div>

<form method="POST" action="{{ route('admin.players.update', $player) }}" class="industrial-card">
    @csrf
    @method('PUT')

    <div class="p-6 space-y-6">
        <!-- Money -->
        <div>
            <label class="block text-sm font-semibold text-[#a3a3a3] mb-2">{{ __('admin.money') }}</label>
            <input type="number" name="money" value="{{ $player->money }}" min="0"
                class="w-full px-4 py-2 bg-[#1f1f1f] border border-[#3f3f3f] rounded text-[#e5e5e5] focus:outline-none focus:border-[#eab308]">
        </div>

        <!-- Level -->
        <div>
            <label class="block text-sm font-semibold text-[#a3a3a3] mb-2">{{ __('admin.level') }}</label>
            <input type="number" name="lvl" value="{{ $player->lvl }}" min="1"
                class="w-full px-4 py-2 bg-[#1f1f1f] border border-[#3f3f3f] rounded text-[#e5e5e5] focus:outline-none focus:border-[#eab308]">
        </div>

        <!-- Experience -->
        <div>
            <label class="block text-sm font-semibold text-[#a3a3a3] mb-2">{{ __('admin.experience') }}</label>
            <input type="number" name="exp" value="{{ $player->exp }}" min="0"
                class="w-full px-4 py-2 bg-[#1f1f1f] border border-[#3f3f3f] rounded text-[#e5e5e5] focus:outline-none focus:border-[#eab308]">
        </div>

        <!-- Stamina -->
        <div>
            <label class="block text-sm font-semibold text-[#a3a3a3] mb-2">{{ __('admin.stamina') }}</label>
            <input type="number" name="stamina" value="{{ $player->stamina }}" min="0"
                class="w-full px-4 py-2 bg-[#1f1f1f] border border-[#3f3f3f] rounded text-[#e5e5e5] focus:outline-none focus:border-[#eab308]">
        </div>

        <!-- City -->
        <div>
            <label class="block text-sm font-semibold text-[#a3a3a3] mb-2">{{ __('admin.city') }}</label>
            <select name="city_id"
                class="w-full px-4 py-2 bg-[#1f1f1f] border border-[#3f3f3f] rounded text-[#e5e5e5] focus:outline-none focus:border-[#eab308]">
                @foreach($cities as $city)
                    <option value="{{ $city->id }}" {{ $player->city_id == $city->id ? 'selected' : '' }}>
                        {{ $city->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="p-6 border-t border-[#3f3f3f] flex gap-4">
        <button type="submit" class="industrial-btn px-6 py-2">{{ __('admin.save') }}</button>
        <a href="{{ route('admin.players.index') }}" class="industrial-btn px-6 py-2">{{ __('admin.cancel') }}</a>
    </div>
</form>
@endsection
