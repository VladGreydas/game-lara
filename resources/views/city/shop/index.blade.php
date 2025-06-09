<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Shop - ') . $city->name }}
        </h2>
        <a href="{{ route('city.show', $city) }}" class="inline-block mt-2 px-4 py-2 bg-gray-800 text-white rounded font-semibold">
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

                                <form action="{{ route('shop.locomotive.buy', $locomotive->shop_uuid) }}" method="POST" class="mt-3">
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
                    <p class="text-gray-600">Coming soon..</p>
                </div>

                {{-- Weapons --}}
                <div id="weapons" class="shop-section hidden">
                    <h3 class="text-lg font-bold mb-4">Available Weapons</h3>
                    <p class="text-gray-600">Coming soon...</p>
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
