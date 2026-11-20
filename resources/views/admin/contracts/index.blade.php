@extends('admin.layout')

@section('content')
<div class="mb-8 flex items-center justify-between">
    <div>
        <h2 class="text-3xl font-bold text-[#eab308] font-serif">{{ __('admin.contracts') }}</h2>
        <p class="text-[#a3a3a3] mt-2">{{ __('admin.contracts_desc') }}</p>
    </div>
    <a href="{{ route('admin.contracts.create') }}" class="industrial-btn px-6 py-2">{{ __('admin.add_contract') }}</a>
</div>

<div class="industrial-card overflow-x-auto">
    <table class="w-full">
        <thead>
            <tr class="border-b border-[#3f3f3f]">
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.id') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.city') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.resource') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.quantity') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.destination') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.reward') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.status') }}</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#a3a3a3]">{{ __('admin.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contracts as $contract)
            <tr class="border-b border-[#2d2d2d] hover:bg-[#2d2d2d]">
                <td class="px-6 py-4 text-[#e5e5e5]">{{ $contract->id }}</td>
                <td class="px-6 py-4 text-[#e5e5e5]">{{ $contract->city->name ?? '—' }}</td>
                <td class="px-6 py-4 text-[#a3a3a3]">{{ $contract->resource->name ?? '—' }}</td>
                <td class="px-6 py-4 text-[#e5e5e5]">{{ $contract->quantity }}</td>
                <td class="px-6 py-4 text-[#a3a3a3]">{{ $contract->destinationCity->name ?? '—' }}</td>
                <td class="px-6 py-4 text-[#e5e5e5]">{{ number_format($contract->reward) }}</td>
                <td class="px-6 py-4">
                    @if($contract->status === \App\Models\Contract::STATUS_AVAILABLE)
                        <span class="text-[#4ade80] text-sm">{{ __('admin.available') }}</span>
                    @elseif($contract->status === \App\Models\Contract::STATUS_ACCEPTED)
                        <span class="text-[#eab308] text-sm">{{ __('admin.accepted') }}</span>
                    @else
                        <span class="text-[#a3a3a3] text-sm">{{ __('admin.completed') }}</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <form method="POST" action="{{ route('admin.contracts.destroy', $contract) }}" class="inline"
                        onsubmit="return confirm('{{ __('admin.confirm_delete') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-[#f87171] hover:underline text-sm">{{ __('admin.delete') }}</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-6 py-8 text-center text-[#a3a3a3]">{{ __('admin.no_contracts') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<x-admin-pagination :paginator="$contracts" />
@endsection
