@extends('layouts.app')
@section('title', __('ui.invoices.title'))
@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">{{ __('ui.invoices.title') }}</h2>
        <p class="page-subtitle">{{ __('ui.invoices.description') }}</p>
    </div>
    @can('invoices.create')
    <a href="{{ route('invoices.create') }}" class="inline-flex items-center space-x-2 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white px-5 py-2 rounded-xl hover:from-indigo-700 hover:to-indigo-800 shadow-lg shadow-indigo-200 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>{{ __('ui.invoices.new') }}</span>
    </a>
    @endcan
</div>
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-100">
                <th class="px-4 sm:px-5 py-3 sm:py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ __('ui.invoices.columns.number') }}</th>
                <th class="px-4 sm:px-5 py-3 sm:py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">{{ __('ui.invoices.columns.contract_client') }}</th>
                <th class="px-4 sm:px-5 py-3 sm:py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ __('ui.invoices.columns.amount') }}</th>
                <th class="px-4 sm:px-5 py-3 sm:py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ __('ui.invoices.columns.emission') }}</th>
                <th class="px-4 sm:px-5 py-3 sm:py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('ui.invoices.columns.status') }}</th>
                <th class="px-4 sm:px-5 py-3 sm:py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ __('ui.invoices.columns.actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse ($invoices as $inv)
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-4 sm:px-5 py-3 sm:py-4 font-mono text-xs font-medium text-gray-900 whitespace-nowrap">{{ $inv->invoice_number }}</td>
                <td class="px-4 sm:px-5 py-3 sm:py-4 hidden sm:table-cell">
                    <p class="text-gray-900 font-medium">{{ $inv->contract->contract_number ?? 'N/A' }}</p>
                    <p class="text-xs text-gray-400">{{ $inv->contract->quotation->prospect->company_name ?? '' }}</p>
                </td>
                <td class="px-4 sm:px-5 py-3 sm:py-4 text-right font-mono font-bold text-indigo-600 whitespace-nowrap">${{ number_format($inv->amount, 2) }}</td>
                <td class="px-4 sm:px-5 py-3 sm:py-4 text-center text-gray-600 whitespace-nowrap">{{ $inv->issued_date->format('d/m/Y') }}</td>
                <td class="px-4 sm:px-5 py-3 sm:py-4 text-center">
                    <span class="badge
                        @switch($inv->status)
                            @case('issued') badge-blue @break
                            @case('paid') badge-green @break
                            @case('cancelled') badge-red @break
                        @endswitch">
                        <span class="badge-dot
                            @switch($inv->status)
                                @case('issued') bg-blue-500 @break
                                @case('paid') bg-green-500 @break
                                @case('cancelled') bg-red-500 @break
                            @endswitch">
                        </span>
                        <span>{{ __("domain.invoice.statuses.{$inv->status}") }}</span>
                    </span>
                </td>
                <td class="px-4 sm:px-5 py-3 sm:py-4 text-right space-x-2 whitespace-nowrap">
                    <a href="{{ route('invoices.show', $inv) }}" class="inline-flex items-center space-x-1 text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>{{ __('ui.actions.view') }}</span>
                    </a>
                    <a href="{{ route('invoices.pdf', $inv) }}" class="inline-flex items-center space-x-1 text-sm text-green-600 hover:text-green-800 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>{{ __('ui.invoices.pdf') }}</span>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-16 text-gray-400">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
                    <p class="text-lg font-medium">{{ __('ui.invoices.empty_title') }}</p>
                    <p class="text-sm mt-1">{{ __('ui.invoices.empty_desc') }}</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="px-4 sm:px-5 py-3 sm:py-4 border-t border-gray-100">{{ $invoices->links() }}</div>
</div>
@endsection
