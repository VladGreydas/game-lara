<div>
    {{-- Кнопка відкриття --}}
    <button
        onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'modal-rename-{{ $type }}-{{ $id }}' }))"
        class="bg-slate-800 text-white ml-2 px-4 py-1 rounded-md shadow">
        {{ __('Rename') }}
    </button>

    {{-- Модалка --}}
    <x-modal name="modal-rename-{{ $type }}-{{ $id }}" :show="false" maxWidth="md">
        <div class="p-6">
            <h2 class="text-lg font-medium text-[#e5e5e5] mb-4">
                {{ __('Rename ' . ucfirst($type)) }}
            </h2>

            <form method="POST" action="{{ route($type . '.rename', $id) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label for="new_name" class="block text-sm font-medium text-[#d4d4d4]">
                        {{ __('Enter new name:') }}
                    </label>
                    <input type="text"
                           name="new_name"
                           id="new_name"
                           required
                           placeholder="Enter here..."
                           autocomplete="off"
                           class="mt-1 block w-full rounded-md border-[#3f3f3f] shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           value="{{ $name }}">
                </div>

                <div class="flex justify-end space-x-2">
                    <x-primary-button type="submit">
                        {{ __('Rename') }}
                    </x-primary-button>

                    <button type="button"
                            class="px-4 py-2 bg-gray-300 text-[#e5e5e5] rounded-md hover:bg-gray-400"
                            onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'modal-rename-{{ $type }}-{{ $id }}' }))">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </x-modal>
</div>
