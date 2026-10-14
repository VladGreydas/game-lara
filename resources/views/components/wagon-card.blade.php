<div class="victorian-card mb-4">
    <div class="p-4 border-b border-[#c5a059] bg-[#f5e6c8]">
        <h4 class="text-lg font-bold text-[#5d3a1a] font-serif">
            {{ $wagon->name }} <span class="text-sm text-gray-600">({{ $wagon->isWeapon() ? __('wagon.weapon') : __('wagon.cargo') }})</span>
        </h4>
    </div>
    <div class="p-4">
        <div class="grid grid-cols-2 gap-2 text-sm">
            <div><strong>{{ __('wagon.level') }}:</strong> {{ $wagon->lvl }}</div>
            @if($wagon->isCargo())
                <div><strong>{{ __('wagon.capacity') }}:</strong> {{ $wagon->cargo_wagon->getCurrentCapacity() }} / {{$wagon->cargo_wagon->capacity}}</div>
            @endif
            <div><strong>{{ __('wagon.weight') }}:</strong> {{ $wagon->getTotalWeight() .' '. __('wagon.ton')}} </div>
            <div><strong>{{ __('wagon.durability') }}:</strong> {{ $wagon->armor }} / {{ $wagon->max_armor }}</div>
        </div>
        @if($rename)
            <form method="POST" action="{{ route('wagon.rename', $wagon) }}" class="mt-4">
                @csrf
                <div class="flex gap-2">
                    <input type="text" name="name" value="{{ $wagon->name }}" class="flex-1 px-3 py-1 border border-[#d4b483] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#8b5a2b]">
                    <button type="submit" class="victorian-btn py-1 px-3 rounded text-xs">
                        {{ __('wagon.rename') }}
                    </button>
                </div>
            </form>
        @endif
        @if($upgrade)
            <div class="mt-4">
                <form method="POST" action="{{ route('wagon.upgrade', $wagon) }}">
                    @csrf
                    <button type="submit" class="victorian-btn py-2 px-4 rounded text-sm">
                        {{ __('wagon.upgrade') }} ({{ $wagon->upgrade_cost }})
                    </button>
                </form>
            </div>
        @endif
        @if($wagon->isCargo())
            <div class="mt-4">
                <h5 class="text-lg font-bold text-[#5d3a1a] mb-2">{{ __('resource.resources') }}:</h5>
                @if($wagon->cargo_wagon->resources->where('quantity', '>', 0)->isNotEmpty())
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        @foreach($wagon->cargo_wagon->resources->where('quantity', '>', 0) as $cargoWagonResource)
                            <div class="p-2 bg-[#fff8e7] border border-[#d4b483] rounded flex justify-between items-center text-sm">
                                <span class="font-semibold text-[#5d3a1a]">{{ $cargoWagonResource->resource->name }}</span>
                                <span class="text-gray-700 font-bold">{{ $cargoWagonResource->quantity }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-600 italic">{{ __('resource.you_have_no_resources_yet') }}</p>
                @endif
            </div>
        @endif
        @if($wagon->isWeapon())
            <div class="mt-4">
                <h5 class="text-lg font-bold text-[#5d3a1a] mb-2">{{ __('weapon.weapons') }}:</h5>
                @if($wagon->weapon_wagon->weapons->isNotEmpty())
                    @foreach($wagon->weapon_wagon->weapons as $weapon)
                        <x-weapon-card :weapon="$weapon" :upgrade="$upgrade" :rename="true"/>
                    @endforeach
                @else
                    <p class="text-gray-600 italic">{{ __('weapon.you_have_no_weapons_yet') }}</p>
                @endif
            </div>
        @endif
    </div>
</div>
