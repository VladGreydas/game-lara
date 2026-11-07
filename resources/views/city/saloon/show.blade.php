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
                                    <li>{{ __('contract.contract_board') }}</li>
                                </ul>
                            </div>

                            <p class="text-gray-700">
                                {{ __('tavern.right_now') }}
                            </p>

                            <!-- Session Messages -->
                            @if(session('success'))
                                <div class="p-3 bg-green-50 border border-green-200 rounded text-green-800 font-semibold text-sm">
                                    {{ session('success') }}
                                </div>
                            @endif
                            @if(session('error'))
                                <div class="p-3 bg-red-50 border border-red-200 rounded text-red-800 font-semibold text-sm">
                                    {{ session('error') }}
                                </div>
                            @endif

                            <!-- Contract Board -->
                            <div class="bg-white border border-[#d4b483] rounded p-4">
                                <h4 class="font-bold text-[#5d3a1a] mb-3 font-serif text-lg">📜 {{ __('contract.contract_board') }}</h4>

                                @if($contracts->isEmpty())
                                    <p class="text-gray-500 italic">{{ __('contract.no_contracts') }}</p>
                                @else
                                    <div class="space-y-3">
                                        @foreach($contracts as $contract)
                                            <div class="p-3 bg-[#f5e6c8] border border-[#d4b483] rounded flex items-center justify-between gap-3">
                                                <div>
                                                    <p class="font-semibold text-[#5d3a1a]">
                                                        {{ __('contract.deliver_resource_to', [
                                                            'quantity' => $contract->quantity,
                                                            'resource' => __('resource.' . $contract->resource->name),
                                                            'city' => $contract->destinationCity->name,
                                                        ]) }}
                                                    </p>
                                                    <p class="text-xs text-[#8b5a2b]">{{ __('contract.reward') }}: ${{ $contract->reward }}</p>
                                                </div>

                                                @if($player && !$player->isTraveling() && $player->city_id === $city->id)
                                                    <form method="POST" action="{{ route('contracts.accept', [$city, $contract]) }}">
                                                        @csrf
                                                        <button type="submit" class="victorian-btn py-1.5 px-3 rounded text-xs font-serif whitespace-nowrap">
                                                            {{ __('contract.accept_contract') }}
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Right: Action Area -->
                        <div class="flex flex-col justify-center space-y-4">
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
