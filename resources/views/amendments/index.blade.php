@extends('layouts.app')
@section('title', __('ui.amendments.title'))
@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">{{ __('ui.amendments.title') }}</h2>
        <p class="page-subtitle">{{ __('ui.amendments.description') }}</p>
    </div>
</div>
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-100">
                <th class="px-4 sm:px-5 py-3 sm:py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ __('ui.amendments.columns.number') }}</th>
                <th class="px-4 sm:px-5 py-3 sm:py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ __('ui.amendments.columns.contract') }}</th>
                <th class="px-4 sm:px-5 py-3 sm:py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">{{ __('ui.amendments.columns.description') }}</th>
                <th class="px-4 sm:px-5 py-3 sm:py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">{{ __('ui.amendments.columns.created_by') }}</th>
                <th class="px-4 sm:px-5 py-3 sm:py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ __('ui.amendments.columns.pdf') }}</th>
                <th class="px-4 sm:px-5 py-3 sm:py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ __('ui.amendments.columns.date') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse ($amendments as $a)
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-4 sm:px-5 py-3 sm:py-4 font-mono text-xs font-medium text-gray-900 whitespace-nowrap">{{ $a->amendment_number }}</td>
                <td class="px-4 sm:px-5 py-3 sm:py-4 whitespace-nowrap">
                    <span class="font-medium text-gray-900">{{ $a->contract->contract_number ?? 'N/A' }}</span>
                    <p class="text-xs text-gray-400">{{ $a->contract->quotation->prospect->company_name ?? '' }}</p>
                </td>
                <td class="px-4 sm:px-5 py-3 sm:py-4 max-w-xs truncate text-gray-600 hidden sm:table-cell">{{ $a->description }}</td>
                <td class="px-4 sm:px-5 py-3 sm:py-4 text-gray-600 hidden sm:table-cell">{{ $a->createdBy->name ?? 'N/A' }}</td>
                <td class="px-4 sm:px-5 py-3 sm:py-4 text-center">
                    @if($a->signed_pdf_path)
                    <a href="{{ route('pdf.amendment', $a) }}" target="_blank" class="inline-flex items-center space-x-1 text-xs text-emerald-600 hover:text-emerald-800 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ __('ui.amendments.pdf_loaded') }}</span>
                    </a>
                    @else
                    <span class="text-xs text-red-500">{{ __('ui.amendments.pdf_missing') }}</span>
                    @endif
                </td>
                <td class="px-4 sm:px-5 py-3 sm:py-4 text-right text-gray-500 text-sm whitespace-nowrap">{{ $a->created_at->format('d/m/Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 sm:px-5 py-16 text-center">@include('components.empty-state', ['title' => __('ui.amendments.empty_title'), 'description' => __('ui.amendments.empty_desc'), 'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'])</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="px-4 sm:px-5 py-3 sm:py-4 border-t border-gray-100">{{ $amendments->links() }}</div>
</div>
@endsection
