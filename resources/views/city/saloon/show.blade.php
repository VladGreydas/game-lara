<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tavern') }} — {{ $city->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-[#f5e6c8] border border-[#d4b483] rounded-xl shadow-lg overflow-hidden">
                <!-- Header -->
                <div class="bg-[#8b5a2b] text-white px-6 py-4 flex items-center justify-between">
                    <h3 class="text-2xl font-bold tracking-wide">Таверна «Синій Баран»</h3>
                    <span class="text-[#eecfa1] text-sm font-mono">Оскільки {{ now()->year - rand(5, 20) }}</span>
                </div>

                <!-- Main Content -->
                <div class="p-6 md:p-8">
                    <div class="grid md:grid-cols-3 gap-6">
                        <!-- Left: Atmosphere & Description -->
                        <div class="md:col-span-2 space-y-4">
                            <p class="text-gray-800 leading-relaxed italic">
                                «Дерев’яні стіни, запах диму, кави та свіжого хліба. За стіл сідаєш — і вже чуєш, як дідусі сперечаються про минулі війни, а бабці діляться притчами.»
                            </p>

                            <div class="bg-white border-l-4 border-[#8b5a2b] p-4 rounded shadow-sm">
                                <h4 class="font-bold text-[#5d3a1a] mb-1">Що тут є?</h4>
                                <ul class="list-disc list-inside text-gray-700 space-y-1">
                                    <li>Теплий камінний камін і зручні лави</li>
                                    <li>Свічкове світло та народні вишиванки</li>
                                    <li>Майбутнє: <strong>Контракти та завдання</strong></li>
                                </ul>
                            </div>

                            <p class="text-gray-700">
                                Зараз таверна — це місце, де збираються місцеві, щоб відпочити, поспівати, поспілкуватися. Але в майбутньому тут з’являться дошки з завданнями, контрактами та пропозиціями роботи.
                            </p>
                        </div>

                        <!-- Right: Action Area -->
                        <div class="flex flex-col justify-center space-y-4">
                            <div class="bg-[#f5e6c8] rounded-lg p-4 text-center border border-[#d4b483]">
                                <p class="text-[#8b5a2b] font-semibold mb-2">Незабаром</p>
                                <div class="h-1 w-full bg-[#d4b483] rounded-full overflow-hidden">
                                    <div class="h-full bg-[#8b5a2b] w-1/3 animate-pulse"></div>
                                </div>
                                <p class="text-xs text-[#6d4a24] mt-1">33% — Система контрактів</p>
                            </div>

                            <button disabled class="w-full py-3 px-4 bg-[#8b5a2b] hover:bg-[#6d4a24] disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold rounded shadow transition-colors flex items-center justify-center gap-2">
                                📜 Відкрити дошку завдань
                            </button>

                            <a href="{{ route('city.show', $city) }}" class="w-full py-2 px-4 bg-[#d4b483] hover:bg-[#c2a66f] text-[#3e2723] font-semibold rounded text-center transition-colors">
                                ← Повернутися до {{ $city->name }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-[#5d3a1a] text-[#eecfa1] px-6 py-3 text-sm text-center">
                    «У таверні кожен гість — як родина. А кожен контракт — як обіцянка».
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
