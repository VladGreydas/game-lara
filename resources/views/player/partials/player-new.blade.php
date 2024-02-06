<section>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Player') }}
            </h2>

        </div>
    </x-slot>
    <div class="mt-6 shadow-sm rounded-lg divide-y">
        <form method="POST" action="{{route('player.store')}}">
            @csrf
            <input autocomplete="off" type="text" name="nickname" class="ml-10 h-12 w-4/5 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-opacity-50 rounded-md shadow-sm" placeholder="Enter your nickname here...">
            <x-primary-button class="ml-1 h-12">{{ __('Create New Player') }}</x-primary-button>
        </form>
    </div>
</section>
