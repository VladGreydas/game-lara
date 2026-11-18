<div class="industrial-card">
    <div class="p-4 border-b border-[#3f3f3f] bg-[#2d2d2d]">
        <h4 class="text-lg font-bold text-[#e5e5e5] font-serif">
            {{ $weapon->name }} <span class="text-sm text-[#a3a3a3]">({{ $weapon->type }})</span>
        </h4>
    </div>
    <div class="p-4">
        <div class="grid grid-cols-2 gap-2 text-sm">
            <div><strong>{{ __('weapon.level') }}:</strong> {{ $weapon->lvl }}</div>
            <div><strong>{{ __('weapon.damage') }}:</strong> {{ $weapon->damage }}</div>
            <div><strong>{{ __('weapon.durability') }}:</strong> {{ $weapon->durability }} / {{ $weapon->max_durability }}</div>
            <div><strong>{{ __('weapon.weight') }}:</strong> {{ $weapon->weight }}</div>
        </div>

        @if($rename)
            <form method="POST" action="{{ route('weapon.rename', $weapon) }}" class="mt-4">
                @csrf
                <div class="flex gap-2">
                    <input type="text" name="name" value="{{ $weapon->name }}" class="flex-1 px-3 py-1 border border-[#d4b483] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#eab308]">
                    <button type="submit" class="industrial-btn py-1 px-3 rounded text-xs">
                        {{ __('weapon.rename') }}
                    </button>
                </div>
            </form>
        @endif

        @if($upgrade)
            <div class="mt-4">
                <form method="POST" action="{{ route('weapon.upgrade', $weapon) }}">
                    @csrf
                    <button type="submit" class="industrial-btn py-2 px-4 rounded text-sm">
                        {{ __('weapon.upgrade') }} ({{ $weapon->upgrade_cost }})
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
