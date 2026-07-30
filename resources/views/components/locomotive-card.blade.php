<div class="victorian-card">
    <div class="p-4 border-b border-[#c5a059] bg-[#f5e6c8]">
        <h4 class="text-lg font-bold text-[#5d3a1a] font-serif">
            {{ $locomotive->name }} <span class="text-sm text-gray-600">({{ __('entity.'.$locomotive->type) }})</span>
        </h4>
    </div>
    <div class="p-4">
        <div class="grid grid-cols-2 gap-2 text-sm">
            <div><strong>{{ __('entity.level') }}:</strong> {{ $locomotive->lvl }}</div>
            <div><strong>{{ __('entity.power') }}:</strong> {{ $locomotive->power }} {{ __('entity.horsepower') }}</div>
            <div><strong>{{ __('entity.speed') }}:</strong> {{ $locomotive->speed }} {{__('entity.kmh')}}</div>
            <div><strong>{{ __('entity.armor') }}:</strong> {{ $locomotive->armor }} / {{ $locomotive->max_armor }}</div>
            <div><strong>{{ __('entity.fuel') }}:</strong> {{ $locomotive->fuel }} / {{ $locomotive->max_fuel }}</div>
            <div><strong>{{ __('entity.fuel_consumption') }}:</strong> {{ $locomotive->fuel_per_hundred_km.__('entity.per_km') }}</div>
        </div>

        @if($rename)
            <form method="POST" action="{{ route('locomotive.rename', $locomotive) }}" class="mt-4">
                @csrf
                <div class="flex gap-2">
                    <input type="text" name="name" value="{{ $locomotive->name }}" class="flex-1 px-3 py-1 border border-[#d4b483] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#8b5a2b]">
                    <button type="submit" class="victorian-btn py-1 px-3 rounded text-xs">
                        {{ __('entity.rename') }}
                    </button>
                </div>
            </form>
        @endif

        @if($upgrade)
            <div class="mt-4">
                <form method="POST" action="{{ route('locomotive.upgrade', $locomotive) }}">
                    @csrf
                    <button type="submit" class="victorian-btn py-2 px-4 rounded text-sm">
                        {{ __('entity.upgrade') }} ({{ $locomotive->upgrade_cost }})
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
