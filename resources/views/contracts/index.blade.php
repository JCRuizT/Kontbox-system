@extends('layouts.app')
@section('title', __('ui.contracts.title'))
@section('content')
<div class="flex justify-between items-center mb-6 fade-in">
    <div>
        <h2 class="text-xl font-bold text-gray-900">{{ __('ui.contracts.title') }}</h2>
        <p class="text-sm text-gray-500 mt-1">{{ __('ui.contracts.description') }}</p>
    </div>
</div>
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="px-4 sm:px-5 py-3 sm:py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ __('ui.contracts.columns.contract') }}</th>
                    <th class="px-4 sm:px-5 py-3 sm:py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ __('ui.contracts.columns.client') }}</th>
                    <th class="px-4 sm:px-5 py-3 sm:py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ __('ui.contracts.columns.amount') }}</th>
                    <th class="px-4 sm:px-5 py-3 sm:py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('ui.common.status') }}</th>
                    <th class="px-4 sm:px-5 py-3 sm:py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ __('ui.contracts.columns.action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($contracts as $c)
                <tr>
                    <td class="px-4 sm:px-5 py-3 sm:py-4 whitespace-nowrap">
                        <span class="font-mono text-xs font-bold text-indigo-600">{{ $c->contract_number }}</span>
                        <p class="text-xs text-gray-400 mt-0.5">{{ __('ui.quotations.title') }} {{ $c->quotation->quote_number ?? __('ui.common.na') }}</p>
                    </td>
                    <td class="px-4 sm:px-5 py-3 sm:py-4">
                        <span class="font-medium text-gray-900">{{ $c->quotation->prospect->company_name ?? __('ui.common.na') }}</span>
                        @if($c->start_date)<p class="text-xs text-gray-400 mt-0.5">{{ __('ui.contracts.detail.start_date') }}: {{ $c->start_date->format('d/m/Y') }}</p>@endif
                    </td>
                    <td class="px-4 sm:px-5 py-3 sm:py-4 text-right font-mono font-bold whitespace-nowrap">${{ number_format($c->total_amount, 2) }}</td>
                    <td class="px-4 sm:px-5 py-3 sm:py-4 text-center">
                        <span class="badge
                            @switch($c->status)
                                @case('pending_document') badge-amber @break
                                @case('document_loaded') badge-blue @break
                                @case('active') badge-green @break
                                @case('cancelled') badge-red @break
                            @endswitch">
                            <span class="badge-dot
                                @switch($c->status)
                                    @case('pending_document') bg-yellow-500 @break
                                    @case('document_loaded') bg-blue-500 @break
                                    @case('active') bg-green-500 @break
                                    @case('cancelled') bg-red-500 @break
                                @endswitch"></span>
                            {{ __("domain.contract.statuses.{$c->status}") }}
                        </span>
                    </td>
                    <td class="px-4 sm:px-5 py-3 sm:py-4 text-right whitespace-nowrap">
                        <div class="flex items-center justify-end space-x-2">
                            <a href="{{ route('contracts.show', $c) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">{{ __('ui.actions.view') }}</a>
                            @if($c->signed_pdf_path && auth()->user()->can('contracts.read'))
                            <a href="{{ route('pdf.contract', $c) }}" target="_blank" class="text-xs text-emerald-600 hover:text-emerald-800 font-medium" title="Ver PDF firmado">
                                <span class="flex items-center space-x-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg><span>PDF</span></span>
                            </a>
                            @endif
                            @if($c->status === 'pending_document' && auth()->user()->can('contracts.upload_document'))
                            <a href="{{ route('contracts.show', $c) }}" class="text-xs text-yellow-600 hover:text-yellow-800 font-medium">{{ __('ui.actions.sign') }}</a>
                            @endif
                            @if($c->status === 'document_loaded' && auth()->user()->can('contracts.activate'))
                            <span class="text-xs text-green-600 font-medium">{{ __('ui.contracts.actions.ready_activate') }}</span>
                            @endif
                            @if($c->status === 'active' && auth()->user()->can('amendments.create'))
                            <a href="{{ route('amendments.create', $c) }}" class="text-xs text-purple-600 hover:text-purple-800 font-medium">{{ __('ui.contracts.actions.amendment') }}</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 sm:px-5 py-16 text-center">@include('components.empty-state', ['title' => __('ui.contracts_index.empty_title'), 'description' => __('ui.contracts_index.empty_desc'), 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'])</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 sm:px-5 py-3 sm:py-4 border-t border-gray-100">{{ $contracts->links() }}</div>
</div>
@endsection
