@extends('layouts.app')
@section('title', __('ui.quotations.title'))
@section('content')
<div class="page-header fade-in">
    <div>
        <h2 class="page-title">{{ __('ui.quotations.title') }}</h2>
        <p class="page-subtitle">{{ __('ui.quotations.description') }}</p>
    </div>
    @can('quotations.create')
    <a href="{{ route('quotations.create') }}" class="btn inline-flex items-center space-x-2 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white px-5 py-2.5 rounded-xl hover:from-indigo-700 hover:to-indigo-800 shadow-lg shadow-indigo-200 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>{{ __('ui.quotations.new') }}</span>
    </a>
    @endcan
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-100">
                <th class="px-4 sm:px-5 py-3 sm:py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ __('ui.quotations.columns.reference') }}</th>
                <th class="px-4 sm:px-5 py-3 sm:py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">{{ __('ui.quotations.columns.prospect') }}</th>
                <th class="px-4 sm:px-5 py-3 sm:py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ __('ui.quotations.columns.total') }}</th>
                <th class="px-4 sm:px-5 py-3 sm:py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('ui.quotations.columns.status') }}</th>
                <th class="px-4 sm:px-5 py-3 sm:py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ __('ui.quotations.columns.action') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse ($quotations as $q)
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-4 sm:px-5 py-3 sm:py-4 whitespace-nowrap">
                    <span class="font-mono text-xs font-bold text-indigo-600">{{ $q->quote_number }}</span>
                    <span class="text-xs text-gray-400 ml-1">v{{ $q->version }}</span>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $q->createdBy->name ?? '' }} · {{ $q->created_at->format('d/m/Y') }}</p>
                </td>
                <td class="px-4 sm:px-5 py-3 sm:py-4 text-gray-700 hidden sm:table-cell">{{ $q->prospect->company_name ?? 'N/A' }}</td>
                <td class="px-4 sm:px-5 py-3 sm:py-4 text-right font-mono font-bold whitespace-nowrap">${{ number_format($q->total, 2) }}</td>
                <td class="px-4 sm:px-5 py-3 sm:py-4 text-center">
                    <span class="badge
                        @switch($q->status)
                            @case('draft') badge-gray @break
                            @case('under_review') badge-amber @break
                            @case('approved') badge-green @break
                            @case('rejected') badge-red @break
                        @endswitch">
                        <span class="badge-dot @switch($q->status) @case('draft') bg-gray-400 @break @case('under_review') bg-yellow-500 @break @case('approved') bg-green-500 @break @case('rejected') bg-red-500 @break @endswitch"></span>
                        {{ __("domain.quotation.statuses.{$q->status}") }}
                    </span>
                </td>
                <td class="px-4 sm:px-5 py-3 sm:py-4 text-right whitespace-nowrap">
                    <div class="flex items-center justify-end space-x-2">
                        <a href="{{ route('quotations.show', $q) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">{{ __('ui.quotations.actions.view') }}</a>
                        @if($q->status === 'draft' && auth()->user()->can('quotations.send_for_approval'))
                        <a href="{{ route('quotations.send-for-approval', $q) }}" class="text-xs text-amber-600 hover:text-amber-800 font-medium">{{ __('ui.quotations.actions.send') }}</a>
                        @endif
                        @if($q->status === 'under_review' && auth()->user()->can('quotations.approve'))
                        <a href="{{ route('reviews.index') }}" class="text-xs text-green-600 hover:text-green-800 font-medium">{{ __('ui.quotations.actions.review') }}</a>
                        @endif
                        @if($q->status === 'approved' && auth()->user()->can('contracts.create'))
                        <a href="{{ route('contracts.create', $q) }}" class="text-xs text-emerald-600 hover:text-emerald-800 font-medium">{{ __('ui.quotations.actions.create_contract') }}</a>
                        @endif
                        @if($q->status === 'rejected' && auth()->user()->can('quotations.send_for_approval'))
                        <form action="{{ route('quotations.new-version', $q) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">{{ __('ui.quotations.actions.new_version') }}</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="py-16">@include('components.empty-state', ['title' => __('ui.quotations.title'), 'description' => __('ui.quotations.form_description'), 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'])</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="px-4 sm:px-5 py-3 sm:py-4 border-t border-gray-100">{{ $quotations->links() }}</div>
</div>
@endsection
