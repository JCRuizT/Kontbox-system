@extends('layouts.app')
@section('title', __('ui.amendments.form_title') . ' ' . $amendment->amendment_number)
@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8">
            <div class="flex justify-between items-start mb-6">
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('ui.amendments.form_title') }} {{ $amendment->amendment_number }}</h2>
                        <p class="text-sm text-gray-500">{{ __('ui.amendments.form_description', ['number' => $amendment->contract->contract_number ?? 'N/A']) }}</p>
                    </div>
                </div>
                @if($amendment->signed_pdf_path)
                <a href="{{ route('pdf.amendment', $amendment) }}" target="_blank" class="badge badge-green hover:bg-green-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span>{{ __('ui.amendments.show_pdf_loaded') }}</span>
                </a>
                @else
                <span class="badge badge-red">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ __('ui.amendments.show_pdf_pending') }}</span>
                </span>
                @endif
            </div>
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="p-4 bg-gray-50 rounded-xl">
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">{{ __('ui.amendments.show_contract') }}</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ $amendment->contract->contract_number ?? 'N/A' }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-xl">
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">{{ __('ui.amendments.show_client') }}</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ $amendment->contract->quotation->prospect->company_name ?? 'N/A' }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-xl">
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">{{ __('ui.amendments.show_registered_by') }}</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ $amendment->createdBy->name ?? 'N/A' }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-xl">
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">{{ __('ui.amendments.show_date') }}</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ $amendment->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ __('ui.amendments.show_description_title') }}</h3>
                <div class="p-4 bg-gray-50 rounded-xl text-sm text-gray-700 leading-relaxed">{{ $amendment->description }}</div>
            </div>
            @if($amendment->modified_services)
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ __('ui.amendments.show_services_title') }}</h3>
                <pre class="p-4 bg-gray-50 rounded-xl text-xs text-gray-600 overflow-x-auto">{{ json_encode(json_decode($amendment->modified_services), JSON_PRETTY_PRINT) }}</pre>
            </div>
            @endif
        </div>
        <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
            <a href="{{ route('amendments.index') }}" class="px-6 py-2 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 font-medium transition">{{ __('ui.actions.back') }}</a>
        </div>
    </div>
</div>
@endsection
