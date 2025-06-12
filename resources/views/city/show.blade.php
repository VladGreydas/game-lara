<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Current City: ') . $city->name }}
        </h2>
    </x-slot>

    <div class="mt-6 mx-auto max-w-7xl bg-white shadow-sm rounded-lg p-6 space-y-4">
        <div>
            <p class="text-lg font-semibold text-gray-800">Welcome to {{ $city->name }}!</p>
            <p class="text-gray-700 mt-2">You can refuel your locomotive here and plan a trip to other cities.</p>
        </div>

        @if(session('success'))
            <div class="text-green-600 font-semibold">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="text-red-600 font-semibold">{{ session('error') }}</div>
        @endif

        <div class="mt-6 p-4 bg-white shadow-md rounded">
            <h2 class="text-xl font-semibold mb-4">Refuel Locomotive</h2>
            <p>Fuel: {{ $player->train->locomotive->fuel }} / {{ $player->train->locomotive->max_fuel }}</p>
            <p>Refueling Cost: ${{ 2 * ($player->train->locomotive->max_fuel - $player->train->locomotive->fuel) }}</p>

            @if($player->train->locomotive->fuel < $player->train->locomotive->max_fuel)
                <form action="{{ route('city.refuel') }}" method="POST" class="mt-4">
                    @csrf
                    <x-primary-button>Refuel</x-primary-button>
                </form>
            @else
                <p class="text-gray-600 mt-2">Your fuel tank is full.</p>
            @endif
        </div>

        <div class="mt-6 p-4 bg-white shadow-md rounded">
            <h2 class="text-xl font-semibold mb-2">Outgoing Routes</h2>
            @if($city->outgoingRoutes->count())
                <ul class="list-disc ml-6">
                    @foreach($city->outgoingRoutes as $route)
                        <li>
                            Travel to <strong>{{ $route->toCity->name }}</strong>
                            (Fuel Cost: {{ $route->fuel_cost }}, Travel Time: {{ $route->travel_time }} hours) {{-- Додано час подорожі --}}
                            <form method="POST" action="{{ route('city.travel', $route) }}" class="inline ml-2"> {{-- Оновлено назву маршруту --}}
                                @csrf
                                <x-primary-button class="h-8 px-2 text-sm">Travel</x-primary-button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-gray-600 mt-2">No outgoing routes from this city.</p>
            @endif
        </div>

        @if ($player->city->has_workshop)
            <div class="mt-6 p-4 border rounded bg-gray-50">
                <h2 class="font-bold text-xl mb-2">Workshop</h2>
                <p>Here you can upgrade your train, wagons, and weapons.</p>
                <a href="{{ route('workshop.index') }}" class="inline-block mt-2 px-4 py-2 bg-gray-800 text-white rounded font-semibold">Go to Workshop</a>
            </div>
        @endif

        @if ($player->city->has_shop)
            <div class="mt-6 p-4 border rounded bg-gray-50">
                <h2 class="font-bold text-xl mb-2">Shop</h2>
                <p>Here you can buy and sell locomotives, wagons, weapons and more.</p>
                <a href="{{ route('shop.index') }}" class="inline-block mt-2 px-4 py-2 bg-gray-800 text-white rounded font-semibold">Go to Shop</a>
            </div>
        @endif

    </div>
</x-app-layout>
