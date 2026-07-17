<div class="victorian-card">
    <div class="p-4 border-b border-[#c5a059] bg-[#f5e6c8]">
        <h4 class="text-lg font-bold text-[#5d3a1a] font-serif">
            {{ $weapon->name }} <span class="text-sm text-gray-600">({{ $weapon->type }})</span>
        </h4>
    </div>
    <div class="p-4">
        <div class="grid grid-cols-2 gap-2 text-sm">
            <div><strong>{{ __('city.level') }}:</strong> {{ $weapon->lvl }}</div>
            <div><strong>{{ __('city.damage') }}:</strong> {{ $weapon->damage }}</div>
            <div><strong>{{ __('city.durability') }}:</strong> {{ $weapon->durability }} / {{ $weapon->max_durability }}</div>
            <div><strong>{{ __('city.weight') }}:</strong> {{ $weapon->weight }}</div>
        </div>

        @if($rename)
            <form method="POST" action="{{ route('weapon.rename', $weapon) }}" class="mt-4">
                @csrf
                <div class="flex gap-2">
                    <input type="text" name="name" value="{{ $weapon->name }}" class="flex-1 px-3 py-1 border border-[#d4b483] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#8b5a2b]">
                    <button type="submit" class="victorian-btn py-1 px-3 rounded text-xs">
                        {{ __('city.rename') }}
                    </button>
                </div>
            </form>
        @endif

        @if($upgrade)
            <div class="mt-4">
                <form method="POST" action="{{ route('weapon.upgrade', $weapon) }}">
                    @csrf
                    <button type="submit" class="victorian-btn py-2 px-4 rounded text-sm">
                        {{ __('city.upgrade') }} ({{ $weapon->getUpgradeCost() }})
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
