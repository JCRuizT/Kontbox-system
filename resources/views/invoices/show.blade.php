@extends('layouts.app')
@section('title', __('ui.invoices.show_title', ['number' => $invoice->invoice_number]))
@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8">
            <div class="flex justify-between items-start mb-6">
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-teal-500 to-emerald-600 rounded-2xl flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('ui.invoices.show_title', ['number' => $invoice->invoice_number]) }}</h2>
                        <p class="text-sm text-gray-500">{{ __('ui.invoices.show_emission', ['date' => $invoice->issued_date->format('d/m/Y')]) }}</p>
                    </div>
                </div>
                <span class="badge
                    @switch($invoice->status)
                        @case('issued') badge-blue @break
                        @case('paid') badge-green @break
                        @case('cancelled') badge-red @break
                    @endswitch">
                    <span class="badge-dot
                        @switch($invoice->status)
                            @case('issued') bg-blue-500 @break
                            @case('paid') bg-green-500 @break
                            @case('cancelled') bg-red-500 @break
                        @endswitch">
                    </span>
                    <span>{{ __("domain.invoice.statuses.{$invoice->status}") }}</span>
                </span>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-8">
                <div class="p-4 bg-gray-50 rounded-xl">
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">{{ __('ui.invoices.show_contract') }}</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ $invoice->contract->contract_number ?? 'N/A' }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-xl">
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">{{ __('ui.invoices.show_client') }}</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ $invoice->contract->quotation->prospect->company_name ?? 'N/A' }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-xl">
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">{{ __('ui.invoices.show_created_by') }}</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ $invoice->createdBy->name ?? 'N/A' }}</p>
                </div>
                <div class="p-4 bg-gradient-to-br from-indigo-50 to-blue-50 rounded-xl border border-indigo-100">
                    <p class="text-xs text-indigo-500 font-medium uppercase tracking-wider">{{ __('ui.invoices.show_total_amount') }}</p>
                    <p class="text-2xl font-bold text-indigo-600 mt-1">${{ number_format($invoice->amount, 2) }}</p>
                </div>
            </div>
            @if($invoice->notes)
            <div class="mb-6 p-4 bg-gray-50 rounded-xl">
                <h3 class="text-sm font-semibold text-gray-700 mb-1">{{ __('ui.invoices.show_notes') }}</h3>
                <p class="text-sm text-gray-600">{{ $invoice->notes }}</p>
            </div>
            @endif
        </div>
        <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex justify-end space-x-3">
            <a href="{{ route('invoices.index') }}" class="px-6 py-2 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 font-medium transition">{{ __('ui.actions.back') }}</a>
            <a href="{{ route('invoices.pdf', $invoice) }}" class="px-6 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 shadow-lg shadow-green-200 font-medium transition">
                <span class="flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>{{ __('ui.actions.download') }}</span>
                </span>
            </a>
        </div>
    </div>
</div>
@endsection
