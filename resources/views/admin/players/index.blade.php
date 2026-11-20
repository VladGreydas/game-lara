@extends('admin.layout')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-[#eab308] font-serif">{{ __('admin.players') }}</h2>
    <p class="text-[#a3a3a3] mt-2">{{ __('admin.players_desc') }}</p>
</div>

<!-- Search -->
<form method="GET" action="{{ route('admin.players.index') }}" class="mb-6">
    <div class="flex gap-4">
        <input type="text" name="search" value="{{ request('search') }}"
            class="flex-1 px-4 py-2 bg-[#1f1f1f] border border-[#3f3f3f] rounded text-[#e5e5e5] placeholder-[#737373] focus:outline-none focus:border-[#eab308]"
            placeholder="{{ __('admin.search') }}">
        <button type="submit" class="industrial-btn px-6 py-2">
            {{ __('admin.search') }}
        </button>
    </div>
</form>

<!-- Table -->
<div class="industrial-card overflow-x-auto">
    <table class="w-full">
        <thead>
            <tr class="border-b border-[#3f3f3f]">
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.id') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.name') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.email') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.level') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.money') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.city') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($players as $player)
            <tr class="border-b border-[#2d2d2d] hover:bg-[#2d2d2d]">
                <td class="px-6 py-4 text-[#e5e5e5]">{{ $player->id }}</td>
                <td class="px-6 py-4 text-[#e5e5e5]">{{ $player->user->name }}</td>
                <td class="px-6 py-4 text-[#a3a3a3]">{{ $player->user->email }}</td>
                <td class="px-6 py-4 text-[#e5e5e5]">{{ $player->lvl }}</td>
                <td class="px-6 py-4 text-[#e5e5e5]">{{ number_format($player->money) }}</td>
                <td class="px-6 py-4 text-[#a3a3a3]">{{ $player->city->name ?? '—' }}</td>
                <td class="px-6 py-4">
                    <a href="{{ route('admin.players.edit', $player) }}"
                        class="text-[#eab308] hover:underline text-sm">{{ __('admin.edit') }}</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-[#a3a3a3]">{{ __('admin.no_players') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
<x-admin-pagination :paginator="$players" />
@endsection
