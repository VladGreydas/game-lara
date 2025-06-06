<div class="my-4 p-2 border rounded-lg shadow bg-gray-50">
    <div class="flex flex-row">
        <p class="text-lg font-bold m-1 mt-0"><strong>Wagon: {{ $wagon->name }}</strong></p>
        @if($rename)
            <x-rename type="wagon" id="{{$wagon->id}}" name="{{$wagon->name}}"/>
        @endif
    </div>
    <ul class="list-disc ml-6">
        <li>Armor: {{ $wagon->armor }} / {{ $wagon->max_armor }}</li>
        @if($upgrade && $wagon->armor < $wagon->max_armor)
            <form action="{{ route('wagon.repair', $wagon) }}" method="POST">
                @csrf
                <x-primary-button>Repair</x-primary-button>
            </form>
        @endif
        <li>Level: {{ $wagon->lvl }}</li>
        <li>Weight: {{ $wagon->weight }}</li>
        @if($wagon->cargo_wagon)
            <li>Type: <span class="text-indigo-600">Cargo</span></li>
            <li>Capacity: {{ $wagon->cargo_wagon->capacity }}</li>
        @endif

        @if($wagon->weapon_wagon)
            <li>Type: <span class="text-red-600">Weapon</span></li>
        @endif

        @if($upgrade)
            <li><strong>Upgrade cost:</strong> {{ $wagon->upgrade_cost }}</li>
            <form action="{{ route('wagon.upgrade', $wagon) }}" method="POST" class="mt-2">
                @csrf
                <x-primary-button>Upgrade {{ucfirst($wagon->type)}} Wagon</x-primary-button>
            </form>
        @endif

        @if($wagon->weapon_wagon)
            @if($wagon->weapon_wagon->weapons->count())
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Weapons:</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach($wagon->weapon_wagon->weapons as $weapon)
                            <x-weapon-card :weapon='$weapon' :rename="$rename" :upgrade="$upgrade" />
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </ul>
</div>
