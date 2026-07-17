<div class="victorian-card">
    <div class="p-4 border-b border-[#c5a059] bg-[#f5e6c8]">
        <h4 class="text-lg font-bold text-[#5d3a1a] font-serif">
            {{ $locomotive->name }} <span class="text-sm text-gray-600">({{ $locomotive->type }})</span>
        </h4>
    </div>
    <div class="p-4">
        <div class="grid grid-cols-2 gap-2 text-sm">
            <div><strong>{{ __('city.level') }}:</strong> {{ $locomotive->lvl }}</div>
            <div><strong>{{ __('city.power') }}:</strong> {{ $locomotive->power }}</div>
            <div><strong>{{ __('city.armor') }}:</strong> {{ $locomotive->armor }}</div>
            <div><strong>{{ __('city.fuel') }}:</strong> {{ $locomotive->fuel }} / {{ $locomotive->max_fuel }}</div>
        </div>

        @if($rename)
            <form method="POST" action="{{ route('locomotive.rename', $locomotive) }}" class="mt-4">
                @csrf
                <div class="flex gap-2">
                    <input type="text" name="name" value="{{ $locomotive->name }}" class="flex-1 px-3 py-1 border border-[#d4b483] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#8b5a2b]">
                    <button type="submit" class="victorian-btn py-1 px-3 rounded text-xs">
                        {{ __('city.rename') }}
                    </button>
                </div>
            </form>
        @endif

        @if($upgrade)
            <div class="mt-4">
                <form method="POST" action="{{ route('locomotive.upgrade', $locomotive) }}">
                    @csrf
                    <button type="submit" class="victorian-btn py-2 px-4 rounded text-sm">
                        {{ __('city.upgrade') }} ({{ $locomotive->getUpgradeCost() }})
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
