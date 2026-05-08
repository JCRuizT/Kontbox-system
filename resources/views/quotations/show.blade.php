@extends('layouts.app')
@section('title', __('ui.quotations.title') . ' ' . $quotation->quote_number)
@section('content')
<div class="max-w-4xl mx-auto fade-in">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="font-mono text-xs font-bold text-indigo-600">{{ $quotation->quote_number }}</span>
                        <span class="text-xs text-gray-400">v{{ $quotation->version }}</span>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $quotation->prospect->company_name ?? 'N/A' }}</h2>
                    <p class="text-sm text-gray-500">{{ $quotation->prospect->contact_name ?? '-' }} · {{ __('ui.common.created_by') }} {{ $quotation->createdBy->name ?? 'N/A' }} {{ __('ui.common.created_at') }} {{ $quotation->created_at->format('d/m/Y') }}</p>
                </div>
                <span class="badge
                    @switch($quotation->status)
                        @case('draft') badge-gray @break
                        @case('under_review') badge-amber @break
                        @case('approved') badge-green @break
                        @case('rejected') badge-red @break
                    @endswitch">
                    <span class="badge-dot
                        @switch($quotation->status)
                            @case('draft') bg-gray-400 @break
                            @case('under_review') bg-yellow-500 @break
                            @case('approved') bg-green-500 @break
                            @case('rejected') bg-red-500 @break
                        @endswitch"></span>
                    <span>{{ __("domain.quotation.statuses.{$quotation->status}") }}</span>
                </span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="p-4 bg-gray-50 rounded-xl"><p class="text-xs text-gray-500 uppercase">{{ __('ui.quotations.detail.valid_until') }}</p><p class="font-semibold">{{ $quotation->valid_until ? $quotation->valid_until->format('d/m/Y') : '-' }}</p></div>
                <div class="p-4 bg-gray-50 rounded-xl"><p class="text-xs text-gray-500 uppercase">{{ __('ui.common.subtotal') }}</p><p class="font-semibold">${{ number_format($quotation->subtotal, 2) }}</p></div>
                <div class="p-4 bg-gray-50 rounded-xl"><p class="text-xs text-gray-500 uppercase">{{ __('ui.common.tax') }}</p><p class="font-semibold">${{ number_format($quotation->tax, 2) }}</p></div>
                <div class="p-4 bg-indigo-50 rounded-xl border border-indigo-100"><p class="text-xs text-indigo-500 uppercase">{{ __('ui.common.total') }}</p><p class="text-lg font-bold text-indigo-600">${{ number_format($quotation->total, 2) }}</p></div>
            </div>
            <table class="w-full text-sm mb-6">
                <thead><tr class="bg-gray-50 border-y border-gray-100"><th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('ui.quotations.detail.services_heading') }}</th><th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">{{ __('ui.common.quantity') }}</th><th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('ui.common.unit_price') }}</th><th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('ui.common.total') }}</th></tr></thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($quotation->items as $item)
                    <tr><td class="px-4 py-3">{{ $item->service_name_snapshot }}</td><td class="px-4 py-3 text-center">{{ $item->quantity }}</td><td class="px-4 py-3 text-right font-mono">${{ number_format($item->unit_price, 2) }}</td><td class="px-4 py-3 text-right font-mono font-medium">${{ number_format($item->total_price, 2) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
            @if($quotation->rejection_reason)
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl flex items-center space-x-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div><p class="text-sm font-semibold text-red-800">{{ __('ui.quotations.detail.rejection_reason') }}</p><p class="text-sm text-red-600">{{ $quotation->rejection_reason }}</p></div>
            </div>
            @endif
            @if($quotation->parent_id)
            <p class="text-xs text-gray-400">{!! __('ui.quotations.detail.version_info', ['version' => $quotation->version, 'parent' => $quotation->parent->quote_number ?? 'N/A']) !!}</p>
            @endif
        </div>
        <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex justify-end space-x-3">
            <a href="{{ route('quotations.index') }}" class="btn px-6 py-2.5 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 font-medium">{{ __('ui.actions.back_list') }}</a>
        </div>
    </div>
</div>
@endsection
