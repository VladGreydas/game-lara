<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Saloon') }} — {{ $city->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-amber-50 border border-amber-200 rounded-xl shadow-lg overflow-hidden">
                <!-- Header -->
                <div class="bg-amber-700 text-white px-6 py-4 flex items-center justify-between">
                    <h3 class="text-2xl font-bold tracking-wide">The Rusty Spur Saloon</h3>
                    <span class="text-amber-200 text-sm font-mono">Est. {{ now()->year - rand(1, 10) }}</span>
                </div>

                <!-- Main Content -->
                <div class="p-6 md:p-8">
                    <div class="grid md:grid-cols-3 gap-6">
                        <!-- Left: Atmosphere & Description -->
                        <div class="md:col-span-2 space-y-4">
                            <p class="text-gray-700 leading-relaxed italic">
                                “The air smells of whiskey, tobacco, and old stories. A few cowboys sit in the corner, 
                                playing cards. The bartender polishes a glass — he looks like he knows a thing or two.”
                            </p>

                            <div class="bg-white border-l-4 border-amber-500 p-4 rounded shadow-sm">
                                <h4 class="font-bold text-amber-800 mb-1">What’s here?</h4>
                                <ul class="list-disc list-inside text-gray-600 space-y-1">
                                    <li>Comfortable seating & quiet corners</li>
                                    <li>Warm fireplace & local gossip</li>
                                    <li>Future: <strong>Contracts & Quests</strong></li>
                                </ul>
                            </div>

                            <p class="text-gray-600">
                                Right now, the saloon is just a place to rest — but soon, you’ll be able to pick up 
                                jobs from the town’s mayor, merchants, or even wanted posters on the wall.
                            </p>
                        </div>

                        <!-- Right: Action Area -->
                        <div class="flex flex-col justify-center space-y-4">
                            <div class="bg-amber-100 rounded-lg p-4 text-center border border-amber-200">
                                <p class="text-amber-900 font-semibold mb-2">Coming Soon</p>
                                <div class="h-1 w-full bg-amber-300 rounded-full overflow-hidden">
                                    <div class="h-full bg-amber-600 w-1/3 animate-pulse"></div>
                                </div>
                                <p class="text-xs text-amber-700 mt-1">33% — Contracts system</p>
                            </div>

                            <button disabled class="w-full py-3 px-4 bg-amber-600 hover:bg-amber-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold rounded shadow transition-colors flex items-center justify-center gap-2">
                                📜 Open Contract Board
                            </button>

                            <a href="{{ route('city.show', $city) }}" class="w-full py-2 px-4 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded text-center transition-colors">
                                ← Back to {{ $city->name }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-amber-800 text-amber-100 px-6 py-3 text-sm text-center">
                    “Every journey begins with a drink — and every contract ends with a handshake.”
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
