<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Saloon') }} — {{ $city->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold mb-4">Welcome to the Saloon</h3>
                <p class="text-gray-600 mb-6">
                    This is a quiet place to rest your weary bones. In the future, you’ll be able to take on contracts and quests here.
                </p>

                <div class="mt-6">
                    <x-primary-button disabled>
                        📜 Future: Contracts
                    </x-primary-button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
