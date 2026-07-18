<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('tavern.tavern') }} — {{ $city->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-[#f5e6c8] border border-[#d4b483] rounded-xl shadow-lg overflow-hidden">
                <!-- Header -->
                <div class="bg-[#8b5a2b] text-white px-6 py-4 flex items-center justify-between">
                    <h3 class="text-2xl font-bold tracking-wide">{{ __('tavern.the_blue_ram_tavern') }}</h3>
                </div>

                <!-- Main Content -->
                <div class="p-6 md:p-8">
                    <div class="grid md:grid-cols-3 gap-6">
                        <!-- Left: Atmosphere & Description -->
                        <div class="md:col-span-2 space-y-4">
                            <p class="text-gray-800 leading-relaxed italic">
                                {{ __('tavern.wooden_walls') }}
                            </p>

                            <div class="bg-white border-l-4 border-[#8b5a2b] p-4 rounded shadow-sm">
                                <h4 class="font-bold text-[#5d3a1a] mb-1">{{ __('tavern.what_s_here') }}</h4>
                                <ul class="list-disc list-inside text-gray-700 space-y-1">
                                    <li>{{ __('tavern.warm_stone_hearth') }}</li>
                                    <li>{{ __('tavern.candlelight') }}</li>
                                    <li>{{ __('tavern.future_contracts') }}</li>
                                </ul>
                            </div>

                            <p class="text-gray-700">
                                {{ __('tavern.right_now') }}
                            </p>
                        </div>

                        <!-- Right: Action Area -->
                        <div class="flex flex-col justify-center space-y-4">
                            <div class="bg-[#f5e6c8] rounded-lg p-4 text-center border border-[#d4b483]">
                                <p class="text-[#8b5a2b] font-semibold mb-2">{{ __('tavern.coming_soon') }}</p>
                                <div class="h-1 w-full bg-[#d4b483] rounded-full overflow-hidden">
                                    <div class="h-full bg-[#8b5a2b] w-1/3 animate-pulse"></div>
                                </div>
                                <p class="text-xs text-[#6d4a24] mt-1">33% — {{ __('tavern.contracts_system') }}</p>
                            </div>

                            <button disabled class="w-full py-3 px-4 bg-[#8b5a2b] hover:bg-[#6d4a24] disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold rounded shadow transition-colors flex items-center justify-center gap-2">
                                📜 {{ __('tavern.open_contract_board') }}
                            </button>

                            <a href="{{ route('city.show', $city) }}" class="w-full py-2 px-4 bg-[#d4b483] hover:bg-[#c2a66f] text-[#3e2723] font-semibold rounded text-center transition-colors">
                                ← {{ __('tavern.back_to') }} {{ $city->name }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-[#5d3a1a] text-[#eecfa1] px-6 py-3 text-sm text-center">
                    {{ __('tavern.footer') }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
