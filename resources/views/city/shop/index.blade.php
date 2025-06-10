@php use App\Models\CargoWagon;use App\Models\WeaponWagon; @endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Shop - ') . $city->name }}
        </h2>
        <a href="{{ route('city.show', $city) }}"
           class="inline-block mt-2 px-4 py-2 bg-gray-800 text-white rounded font-semibold">
            Back to City
        </a>
    </x-slot>

    <div class="mt-6 mx-auto max-w-7xl bg-white shadow-sm rounded-lg p-6 space-y-4">
        @if(session('success'))
            <div class="text-green-600 font-semibold">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="text-red-600 font-semibold">{{ session('error') }}</div>
        @endif

        <div class="mb-6">
            <label for="shop-category" class="block mb-2 text-sm font-medium text-gray-700">Select Category</label>
            <select id="shop-category" class="form-select rounded-md border-gray-300 shadow-sm"
                    onchange="switchCategory(this.value)">
                <option value="locomotives">Locomotives</option>
                <option value="wagons">Wagons</option>
                <option value="weapons">Weapons</option>
            </select>
        </div>

        <div id="shop-content">
            {{-- Locomotives --}}
            <div id="locomotives" class="shop-section">
                <h3 class="text-lg font-bold mb-4">Available Locomotives</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($locomotives as $locomotive)
                        <div class="p-4 border rounded-xl shadow bg-white">
                            <h4 class="text-lg font-semibold">{{ $locomotive->name }}</h4>
                            <p>Type: {{ $locomotive->type }}</p>
                            <p>Power: {{ $locomotive->power }}</p>
                            <p>Armor: {{ $locomotive->armor }}</p>
                            <p>Fuel: {{ $locomotive->fuel }}</p>
                            <p>UUID: {{ $locomotive->shop_uuid }}</p>
                            <p class="font-bold text-green-700">Price: ${{ $locomotive->price }}</p>

                            <form action="{{ route('shop.locomotive.buy', $locomotive->shop_uuid) }}" method="POST"
                                  class="mt-3">
                                @csrf
                                <x-primary-button>Buy</x-primary-button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Wagons --}}
            <div id="wagons" class="shop-section hidden">
                <h3 class="text-lg font-bold mb-4">Available Wagons</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($wagons as $wagon)
                        <div class="p-4 border rounded-xl shadow bg-white">
                            <h4 class="text-lg font-semibold capitalize">{{ $wagon->name }}</h4>
                            <p>Weight: {{ $wagon->weight }}</p>
                            <p>Level: {{ $wagon->lvl }}</p>

                            @if($wagon->type === 'cargo')
                                <p>Capacity: {{ $wagon->cargo_data['capacity'] }}</p>
                            @elseif($wagon->type === 'weapon')
                                <p>Weapon slot: empty</p>
                            @endif
                            <p>UUID: {{ $wagon->shop_uuid }}</p>
                            <p class="font-bold text-green-700">Price: ${{ $wagon->price }}</p>

                            <form action="{{ route('shop.wagon.buy', $wagon->shop_uuid) }}" method="POST" class="mt-3">
                                @csrf
                                <x-primary-button>Buy</x-primary-button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>


            {{-- Weapons --}}
            <div id="weapons" class="shop-section hidden">
                <h3 class="text-lg font-bold mb-4">Available Weapons</h3>

                @if($weaponWagons->isEmpty())
                    <p class="text-red-600">You don’t have any Weapon Wagons to mount a weapon on.</p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($weapons as $weapon)
                            <div class="p-4 border rounded-xl shadow bg-white">
                                <h4 class="text-lg font-semibold">{{ $weapon->name }}</h4>
                                <p>Type: {{ $weapon->type }}</p>
                                <p>Damage: {{ $weapon->damage }}</p>
                                <p>UUID: {{ $weapon->shop_uuid }}</p>
                                <p class="font-bold text-green-700">Price: ${{ $weapon->price }}</p>

                                <form action="{{ route('shop.weapon.buy', $weapon->shop_uuid) }}" method="POST" class="mt-3 space-y-2">
                                    @csrf
                                    <label for="wagon_{{ $weapon->shop_uuid }}" class="block text-sm font-medium text-gray-700">
                                        Mount on Weapon Wagon:
                                    </label>
                                    <select name="weapon_wagon_id" id="wagon_{{ $weapon->shop_uuid }}" class="form-select rounded-md border-gray-300 shadow-sm w-full">
                                        @foreach($weaponWagons as $wagon)
                                            <option value="{{ $wagon->id }}">#{{ $wagon->id }} ({{ $wagon->wagon->name }})</option>
                                        @endforeach
                                    </select>

                                    <x-primary-button class="w-full">Buy & Mount</x-primary-button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function switchCategory(category) {
            document.querySelectorAll('.shop-section').forEach(el => el.classList.add('hidden'));
            document.getElementById(category).classList.remove('hidden');
        }
    </script>
</x-app-layout>
