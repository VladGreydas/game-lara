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
            <label for="shop-category" class="block mb-2 text-sm font-medium text-gray-700">Select Section</label>
            <select id="shop-category" class="form-select rounded-md border-gray-300 shadow-sm"
                    onchange="switchMainSection(this.value)">
                <option value="buy-section">Buy Items</option>
                <option value="sell-section">Sell Items</option>
                {{-- ДОДАНО: Опції для купівлі/продажу ресурсів --}}
                <option value="buy-resources-section">Buy Resources</option>
                <option value="sell-resources-section">Sell Resources</option>
            </select>
        </div>

        {{-- --- BUY SECTION (ЛОКОМОТИВИ, ВАГОНИ, ЗБРОЯ) --- --}}
        <div id="buy-section" class="main-shop-section">
            <h3 class="text-xl font-bold mb-4">Buy Items</h3>

            <div class="mb-6">
                <label for="buy-category" class="block mb-2 text-sm font-medium text-gray-700">Select Category</label>
                <select id="buy-category" class="form-select rounded-md border-gray-300 shadow-sm"
                        onchange="switchCategory(this.value, 'buy')">
                    <option value="locomotives">Locomotives</option>
                    <option value="wagons">Wagons</option>
                    <option value="weapons">Weapons</option>
                </select>
            </div>

            <div id="buy-content">
                {{-- Locomotives --}}
                <div id="locomotives-buy" class="shop-category-section">
                    <h4 class="text-lg font-bold mb-4">Available Locomotives</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($locomotives as $locomotive)
                            <div class="p-4 border rounded-xl shadow bg-white">
                                <h5 class="text-lg font-semibold">{{ $locomotive->name }}</h5>
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
                <div id="wagons-buy" class="shop-category-section hidden">
                    <h4 class="text-lg font-bold mb-4">Available Wagons</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($wagons as $wagon)
                            <div class="p-4 border rounded-xl shadow bg-white">
                                <h5 class="text-lg font-semibold capitalize">{{ $wagon->name }}</h5>
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
                <div id="weapons-buy" class="shop-category-section hidden">
                    <h4 class="text-lg font-bold mb-4">Available Weapons</h4>
                    @if($weaponWagonsForMounting->isEmpty())
                        <p class="text-red-600">You don’t have any Weapon Wagons to mount a weapon on.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($weapons as $weapon)
                                <div class="p-4 border rounded-xl shadow bg-white">
                                    <h5 class="text-lg font-semibold">{{ $weapon->name }}</h5>
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
                                            @foreach($weaponWagonsForMounting as $wagon)
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

        {{-- --- SELL SECTION (ВАГОНИ, ЗБРОЯ) --- --}}
        <div id="sell-section" class="main-shop-section hidden">
            <h3 class="font-bold text-xl mb-4">Sell Items</h3>

            <div class="mb-6">
                <label for="sell-category" class="block mb-2 font-medium text-sm text-gray-700">Select Category</label>
                <select id="sell-category" class="form-select rounded-md border-gray-300 shadow-sm"
                        onchange="switchCategory(this.value, 'sell')">
                    <option value="wagons">Your Wagons</option>
                    <option value="weapons">Your Weapons</option>
                </select>
            </div>

            <div id="sell-content">
                {{-- Your Wagons (For Sale) --}}
                <div id="wagons-sell" class="shop-category-section">
                    <h4 class="font-bold text-lg mb-4">Your Wagons (For Sale)</h4>
                    @if($playerWagonsForSale->isEmpty())
                        <p class="rounded-xl bg-white p-4 text-gray-600 shadow">
                            You don't have any wagons to sell.
                        </p>
                    @else
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                            @foreach($playerWagonsForSale as $wagon)
                                <div class="rounded-xl border bg-white p-4 shadow">
                                    <h5 class="font-semibold capitalize text-lg">{{ $wagon->name }}</h5>
                                    <p>Weight: {{ $wagon->weight }}</p>
                                    <p>Armor: {{ $wagon->armor }}/{{ $wagon->max_armor }}</p>
                                    <p>Level: {{ $wagon->lvl }}</p>

                                    @if($wagon->type === 'weapon')
                                        @php
                                            $weaponWagonData = $wagon->weapon_wagon;
                                            $attachedWeapons = $weaponWagonData->weapons;
                                        @endphp
                                        <p>Weapon Slots: {{ $weaponWagonData->slots_available }} available / {{ $attachedWeapons->count() }} attached</p>
                                        @if($attachedWeapons->isNotEmpty())
                                            <p class="font-bold text-red-600">Attached Weapons:</p>
                                            <ul>
                                                @foreach($attachedWeapons as $attachedWeapon)
                                                    <li>- {{ $attachedWeapon->name }} (DMG: {{ $attachedWeapon->damage }})</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    @elseif($wagon->type === 'cargo')
                                        @php
                                            $cargoWagonData = $wagon->cargo_wagon;
                                        @endphp
                                        <p>Cargo Capacity: {{ $cargoWagonData->capacity }}</p>
                                    @endif

                                    <p class="font-bold text-red-700">Sell Price: ${{ $wagon->price / 2 }}</p>

                                    {{-- --- ФОРМА ПРОДАЖУ ВАГОНА --- --}}
                                    <form action="{{ route('shop.wagon.sell', $wagon->id) }}" method="POST" class="mt-3 space-y-2"> {{-- Змінено action --}}
                                        @csrf
                                        @method('DELETE') {{-- ДОДАНО: Для імітації DELETE запиту --}}

                                        @if($wagon->type === 'weapon' && $wagon->weapon_wagon->weapons->isNotEmpty())
                                            <label for="dest_wagon_{{ $wagon->id }}" class="block font-medium text-sm text-gray-700">
                                                Transfer attached weapons to another wagon:
                                            </label>
                                            <select name="destination_weapon_wagon_id" id="dest_wagon_{{ $wagon->id }}"
                                                    class="form-select w-full rounded-md border-gray-300 shadow-sm">
                                                <option value="">Sell weapons along with wagon</option>
                                                @foreach($weaponWagonsForMounting->where('wagon_id', '!=', $wagon->id) as $destWagon)
                                                    <option value="{{ $destWagon->id }}">
                                                        #{{ $destWagon->id }} ({{ $destWagon->wagon->name }}) - Free Slots: {{ $destWagon->slots_available }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @endif

                                        <x-primary-button class="w-full bg-red-500 hover:bg-red-600">Sell Wagon</x-primary-button>
                                    </form>
                                    {{-- --- КІНЕЦЬ ФОРМИ ПРОДАЖУ ВАГОНА --- --}}
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Your Weapons (For Sale) --}}
                <div id="weapons-sell" class="shop-category-section hidden">
                    <h4 class="font-bold text-lg mb-4">Your Weapons (For Sale)</h4>
                    @if($playerWeaponsForSale->isEmpty())
                        <p class="rounded-xl bg-white p-4 text-gray-600 shadow">
                            You don't have any weapons to sell.
                        </p>
                    @else
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                            @foreach($playerWeaponsForSale as $weapon)
                                <div class="rounded-xl border bg-white p-4 shadow">
                                    <h5 class="font-semibold text-lg">{{ $weapon->name }}</h5>
                                    <p>Type: {{ $weapon->type }}</p>
                                    <p>Damage: {{ $weapon->damage }}</p>
                                    @if($weapon->weapon_wagon_id)
                                        <p class="text-red-600">Mounted on: {{ $weapon->weaponWagon->wagon->name ?? 'Unknown Wagon' }}</p>
                                    @else
                                        <p class="text-green-600">Status: Unmounted</p>
                                    @endif
                                    <p>UUID: {{ $weapon->uuid }}</p>
                                    <p class="font-bold text-red-700">Sell Price: ${{ $weapon->price / 2 }}</p>

                                    {{-- --- ФОРМА ПРОДАЖУ ЗБРОЇ --- --}}
                                    <form action="{{ route('shop.weapon.sell', $weapon->id) }}" method="POST" class="mt-3"> {{-- Змінено action --}}
                                        @csrf
                                        @method('DELETE') {{-- ДОДАНО: Для імітації DELETE запиту --}}
                                        <x-primary-button class="w-full bg-red-500 hover:bg-red-600">Sell Weapon</x-primary-button>
                                    </form>
                                    {{-- --- КІНЕЦЬ ФОРМИ ПРОДАЖУ ЗБРОЇ --- --}}
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

            {{-- --- BUY RESOURCES SECTION --- --}}
            <div id="buy-resources-section" class="main-shop-section hidden">
                <h3 class="text-xl font-bold mb-4">Buy Resources</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border-collapse">
                        <thead>
                        <tr>
                            <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600">Resource</th>
                            <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600">Available Qty</th>
                            <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600">Buy Price</th>
                            <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600">Your Cargo Space</th>
                            <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600">Amount</th>
                            <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($cityResources as $cityResource)
                            <tr>
                                <td class="py-2 px-4 border-b">{{ $cityResource->resource->name }} ({{ $cityResource->resource->unit }})</td>
                                <td class="py-2 px-4 border-b">{{ $cityResource->quantity }}</td>
                                <td class="py-2 px-4 border-b">${{ number_format($cityResource->getCurrentBuyPrice(), 2) }}</td>
                                <td class="py-2 px-4 border-b">
                                    <span class="text-green-600">{{ $availableCargoSpace }}</span> / <span class="text-gray-500">{{ $totalCargoCapacity }}</span>
                                </td>
                                <td class="py-2 px-4 border-b">
                                    <input type="number" name="quantity" min="1" max="{{ min($cityResource->quantity, $availableCargoSpace) }}"
                                           class="w-24 form-input rounded-md border-gray-300 shadow-sm text-sm"
                                           value="1" form="buy-resource-form-{{ $cityResource->id }}">
                                </td>
                                <td class="py-2 px-4 border-b">
                                    {{-- Форма для покупки ресурсу --}}
                                    <form id="buy-resource-form-{{ $cityResource->id }}"
                                          action="{{ route('shop.resource.buy', $cityResource->resource->slug) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="city_resource_id" value="{{ $cityResource->id }}">
                                        <x-primary-button>Buy</x-primary-button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-4 px-4 text-center text-gray-500">No resources available for purchase in this city.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <p class="mt-4 text-sm text-gray-600">Current Money: ${{ number_format($player->money, 2) }}</p>
            </div>

            {{-- --- SELL RESOURCES SECTION --- --}}
            <div id="sell-resources-section" class="main-shop-section hidden">
                <h3 class="text-xl font-bold mb-4">Sell Resources</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border-collapse">
                        <thead>
                        <tr>
                            <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600">Resource</th>
                            <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600">Your Qty</th>
                            <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600">Sell Price (in {{ $city->name }})</th>
                            <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600">Amount</th>
                            <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($groupedPlayerCargoWagonResources as $wagonResource) {{-- Змінено колекцію на згруповану --}}
                        @php
                            // Знаходимо відповідний CityResource для отримання ціни продажу
                            $cityResource = $city->resources->where('resource_id', $wagonResource->resource_id)->first();
                        @endphp

                        @if($cityResource) {{-- Показуємо ресурс тільки якщо його можна продати в цьому місті --}}
                        <tr>
                            <td class="py-2 px-4 border-b">{{ $wagonResource->resource->name }} ({{ $wagonResource->resource->unit }})</td>
                            <td class="py-2 px-4 border-b">{{ $wagonResource->quantity }}</td>
                            <td class="py-2 px-4 border-b">${{ number_format($cityResource->getCurrentSellPrice(), 2) }}</td>
                            <td class="py-2 px-4 border-b">
                                <input type="number" name="quantity" min="1" max="{{ $wagonResource->quantity }}"
                                       class="w-24 form-input rounded-md border-gray-300 shadow-sm text-sm"
                                       value="1" form="sell-resource-form-{{ $wagonResource->resource_id }}"> {{-- Використовуємо resource_id для унікальності форми --}}
                            </td>
                            <td class="py-2 px-4 border-b">
                                {{-- Форма для продажу ресурсу --}}
                                <form id="sell-resource-form-{{ $wagonResource->resource_id }}"
                                      action="{{ route('shop.resource.sell', $wagonResource->resource->slug) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    {{-- Не потрібно передавати cargo_wagon_resource_id, оскільки продаємо "загальну" кількість --}}
                                    {{-- Контролер сам знайде, з яких CargoWagonResource відняти --}}
                                    <x-primary-button class="bg-red-500 hover:bg-red-600">Sell</x-primary-button>
                                </form>
                            </td>
                        </tr>
                        @endif
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 px-4 text-center text-gray-500">You don't have any resources to sell.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <p class="mt-4 text-sm text-gray-600">Current Money: ${{ number_format($player->money, 2) }}</p>
            </div>
    </div>

    <script>
        // Головний перемикач між секціями "Buy" та "Sell"
        function switchMainSection(mainSectionId) {
            document.querySelectorAll('.main-shop-section').forEach(el => el.classList.add('hidden'));
            document.getElementById(mainSectionId).classList.remove('hidden');

            // Якщо перемикаємо на секцію купівлі/продажу товарів
            if (mainSectionId === 'buy-section') {
                const buyCategorySelect = document.getElementById('buy-category');
                switchCategory(buyCategorySelect.value, 'buy');
            } else if (mainSectionId === 'sell-section') {
                const sellCategorySelect = document.getElementById('sell-category');
                switchCategory(sellCategorySelect.value, 'sell');
            }

            // ЗБЕРІГАЄМО ТА ВІДНОВЛЮЄМО СТАН ДЛЯ ВСІХ СЕКЦІЙ
            localStorage.setItem('mainShopSection', mainSectionId);
        }

        // Перемикач категорій всередині головної секції (Buy або Sell)
        function switchCategory(category, mainSectionType) {
            document.querySelectorAll(`#${mainSectionType}-content .shop-category-section`).forEach(el => el.classList.add('hidden'));
            document.getElementById(`${category}-${mainSectionType}`).classList.remove('hidden');
            localStorage.setItem(`${mainSectionType}Category`, category);
        }

        // Ініціалізація при завантаженні сторінки
        document.addEventListener('DOMContentLoaded', () => {
            // Визначаємо, яка головна секція повинна бути показана за замовчуванням
            const initialMainSection = localStorage.getItem('mainShopSection') || 'buy-section';
            document.getElementById('shop-category').value = initialMainSection;
            switchMainSection(initialMainSection); // Викликаємо головний перемикач

            // Ініціалізація під-категорій для Buy
            // switchMainSection вже викликає switchCategory, тому тут не потрібно повторно викликати
            if (initialMainSection === 'buy-section') {
                const initialBuyCategory = localStorage.getItem('buyCategory') || 'locomotives';
                document.getElementById('buy-category').value = initialBuyCategory;
                switchCategory(initialBuyCategory, 'buy');
            }

            // Ініціалізація під-категорій для Sell
            // switchMainSection вже викликає switchCategory, тому тут не потрібно повторно викликати
            if (initialMainSection === 'sell-section') {
                const initialSellCategory = localStorage.getItem('sellCategory') || 'wagons';
                document.getElementById('sell-category').value = initialSellCategory;
                switchCategory(initialSellCategory, 'sell');
            }
        });
    </script>
</x-app-layout>
