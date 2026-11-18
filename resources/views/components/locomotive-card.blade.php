<div class="industrial-card">
    <div class="p-4 border-b border-[#3f3f3f] bg-[#2d2d2d]">
        <h4 class="text-lg font-bold text-[#e5e5e5] font-serif">
            {{ $locomotive->name }} <span class="text-sm text-[#a3a3a3]">({{ __('locomotive.'.$locomotive->type) }})</span>
        </h4>
    </div>
    <div class="p-4">
        <div class="grid grid-cols-2 gap-2 text-sm">
            <div><strong>{{ __('locomotive.level') }}:</strong> {{ $locomotive->lvl }}</div>
            <div><strong>{{ __('locomotive.power') }}:</strong> {{ $locomotive->power }} {{ __('locomotive.horsepower') }}</div>
            <div><strong>{{ __('locomotive.speed') }}:</strong> {{ $locomotive->speed }} {{__('locomotive.kmh')}}</div>
            <div><strong>{{ __('locomotive.armor') }}:</strong> {{ $locomotive->armor }} / {{ $locomotive->max_armor }}</div>
            <div><strong>{{ __('locomotive.fuel') }}:</strong> {{ $locomotive->fuel }} / {{ $locomotive->max_fuel }}</div>
            <div><strong>{{ __('locomotive.fuel_consumption') }}:</strong> {{ $locomotive->fuel_per_hundred_km.__('locomotive.per_km') }}</div>
        </div>

        @if($rename)
            <form method="POST" action="{{ route('locomotive.rename', $locomotive) }}" class="mt-4">
                @csrf
                <div class="flex gap-2">
                    <input type="text" name="name" value="{{ $locomotive->name }}" class="flex-1 px-3 py-1 border border-[#d4b483] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#eab308]">
                    <button type="submit" class="industrial-btn py-1 px-3 rounded text-xs">
                        {{ __('locomotive.rename') }}
                    </button>
                </div>
            </form>
        @endif

        @if($upgrade)
            <div class="mt-4">
                <form method="POST" action="{{ route('locomotive.upgrade', $locomotive) }}">
                    @csrf
                    <button type="submit" class="industrial-btn py-2 px-4 rounded text-sm">
                        {{ __('locomotive.upgrade') }} ({{ $locomotive->upgrade_cost }})
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
