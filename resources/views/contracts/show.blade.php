@extends('layouts.app')
@section('title', __('ui.contracts.title') . ' ' . $contract->contract_number)
@section('content')
<div class="max-w-4xl mx-auto fade-in">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="font-mono text-xs font-bold text-indigo-600">{{ $contract->contract_number }}</span>
                        <span class="text-xs text-gray-400">· {{ __('ui.quotations.title') }} {{ $contract->quotation->quote_number ?? 'N/A' }}</span>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $contract->quotation->prospect->company_name ?? 'N/A' }}</h2>
                    <p class="text-sm text-gray-500">{{ __('ui.common.approved_by') }} {{ $contract->approvedBy->name ?? 'N/A' }}</p>
                </div>
                <span class="badge
                    @switch($contract->status)
                        @case('pending_document') badge-amber @break
                        @case('document_loaded') badge-blue @break
                        @case('active') badge-green @break
                        @case('cancelled') badge-red @break
                    @endswitch">
                    <span class="badge-dot
                        @switch($contract->status)
                            @case('pending_document') bg-yellow-500 @break
                            @case('document_loaded') bg-blue-500 @break
                            @case('active') bg-green-500 @break
                            @case('cancelled') bg-red-500 @break
                        @endswitch"></span>
                    <span>{{ __("domain.contract.statuses.{$contract->status}") }}</span>
                </span>
            </div>

            {{-- DATOS --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="p-4 bg-gray-50 rounded-xl"><p class="text-xs text-gray-500 uppercase">{{ __('ui.contracts.detail.start_date') }}</p><p class="font-semibold">{{ $contract->start_date ? $contract->start_date->format('d/m/Y') : __('ui.common.pending') }}</p></div>
                <div class="p-4 bg-gray-50 rounded-xl"><p class="text-xs text-gray-500 uppercase">{{ __('ui.contracts.detail.end_date') }}</p><p class="font-semibold">{{ $contract->end_date ? $contract->end_date->format('d/m/Y') : '-' }}</p></div>
                <div class="p-4 bg-gray-50 rounded-xl"><p class="text-xs text-gray-500 uppercase">{{ __('ui.contracts.detail.signed_pdf') }}</p>
                    @if($contract->signed_pdf_path)
                    <a href="{{ route('pdf.contract', $contract) }}" target="_blank" class="inline-flex items-center space-x-1 text-sm text-emerald-600 hover:text-emerald-800 font-semibold mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span>{{ $contract->signed_pdf_original_name ?? __('ui.actions.view') }}</span>
                    </a>
                    @if($contract->signed_pdf_uploaded_at)
                    <p class="text-[10px] text-gray-400 mt-0.5">{{ $contract->signed_pdf_uploaded_at->format('d/m/Y H:i') }}</p>
                    @endif
                    @else
                    <p class="font-semibold text-sm text-gray-400">{{ __('ui.contracts.detail.not_loaded') }}</p>
                    @endif
                </div>
                <div class="p-4 bg-indigo-50 rounded-xl border border-indigo-100"><p class="text-xs text-indigo-500 uppercase">{{ __('ui.contracts.detail.total_amount') }}</p><p class="text-lg font-bold text-indigo-600">${{ number_format($contract->total_amount, 2) }}</p></div>
            </div>

            {{-- SERVICIOS --}}
            @if($contract->services->count() > 0)
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ __('ui.contracts.detail.services') }}</h3>
                <div class="space-y-2">
                    @foreach($contract->services as $svc)
                    <div class="flex justify-between py-2 px-4 bg-gray-50 rounded-lg text-sm">
                        <span>{{ $svc->microservice->name ?? 'N/A' }} ×{{ $svc->quantity }}</span>
                        <span class="font-mono font-medium">${{ number_format($svc->total_price, 2) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- OTROSÍ --}}
            @if($contract->amendments->count() > 0)
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ __('ui.contracts.detail.amendments') }}</h3>
                <div class="space-y-2">
                    @foreach($contract->amendments as $am)
                    <div class="flex items-center justify-between p-3 bg-purple-50 rounded-xl border border-purple-100 text-sm">
                        <span class="font-mono text-xs font-semibold text-purple-700">{{ $am->amendment_number }}</span>
                        <span class="text-gray-600 truncate mx-2">{{ Str::limit($am->description, 80) }}</span>
                        <span class="text-xs text-gray-400">{{ $am->created_at->format('d/m/Y') }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- LÍNEA DE TIEMPO --}}
            <div class="p-4 bg-gray-50 rounded-xl text-sm space-y-2">
                <div class="flex items-center space-x-2"><span class="w-2 h-2 rounded-full bg-green-500"></span><span class="text-gray-600">{{ __('ui.contracts.detail.created_at') }} {{ $contract->created_at->format('d/m/Y H:i') }}</span></div>
                @if($contract->signed_pdf_uploaded_at)
                <div class="flex items-center space-x-2"><span class="w-2 h-2 rounded-full bg-blue-500"></span><span class="text-gray-600">{{ __('ui.contracts.detail.pdf_uploaded') }} {{ $contract->signed_pdf_uploaded_at->format('d/m/Y H:i') }} ({{ $contract->signed_pdf_original_name ?? '' }})</span></div>
                @endif
                @if($contract->activated_at)
                <div class="flex items-center space-x-2"><span class="w-2 h-2 rounded-full bg-indigo-500"></span><span class="text-gray-600">{{ __('ui.contracts.detail.activated') }} {{ $contract->activated_at->format('d/m/Y H:i') }}</span></div>
                @endif
                @if($contract->cancelled_at)
                <div class="flex items-center space-x-2"><span class="w-2 h-2 rounded-full bg-red-500"></span><span class="text-gray-600">{{ __('ui.contracts.detail.cancelled') }} {{ $contract->cancelled_at->format('d/m/Y H:i') }} — {{ $contract->cancellation_reason }}</span></div>
                @endif
            </div>
        </div>

        <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
            <a href="{{ route('contracts.index') }}" class="btn px-6 py-2.5 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 font-medium">{{ __('ui.actions.back_list') }}</a>
            <div class="flex space-x-2">
                @if($contract->status === 'pending_document' && auth()->user()->can('contracts.upload_document'))
                <a href="{{ route('contracts.show', $contract) }}#upload-pdf" onclick="document.getElementById('upload-section').classList.remove('hidden'); this.remove();" class="btn px-4 py-2.5 bg-yellow-600 text-white rounded-xl hover:bg-yellow-700 font-medium text-sm shadow-lg shadow-yellow-200">{{ __('ui.contracts.actions.upload_pdf') }}</a>
                @endif
                @if($contract->status === 'document_loaded' && auth()->user()->can('contracts.activate'))
                <form action="{{ route('contracts.activate', $contract) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn px-4 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 font-medium text-sm shadow-lg shadow-green-200">{{ __('ui.actions.activate') }} {{ __('ui.contracts.title') }}</button>
                </form>
                @endif
                @if($contract->status === 'active' && auth()->user()->can('amendments.create'))
                <a href="{{ route('amendments.create', $contract) }}" class="btn px-4 py-2.5 bg-purple-600 text-white rounded-xl hover:bg-purple-700 font-medium text-sm shadow-lg shadow-purple-200">{{ __('ui.actions.register_amendment') }}</a>
                @endif
                @if($contract->status === 'active' && auth()->user()->can('contracts.anulate'))
                <button onclick="document.getElementById('anulate-section').classList.toggle('hidden')" class="btn px-4 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 font-medium text-sm shadow-lg shadow-red-200">{{ __('ui.actions.anulate') }}</button>
                @endif
            </div>
        </div>

        {{-- UPLOAD SECTION --}}
        <div id="upload-section" class="hidden p-8 border-t-2 border-dashed border-yellow-300 bg-yellow-50/30">
            <h3 class="font-semibold text-yellow-800 mb-3">{{ __('ui.contracts.upload_section.title') }}</h3>
            <p class="text-sm text-yellow-700 mb-4">{{ __('ui.contracts.upload_section.description') }}</p>
            <form action="{{ route('contracts.upload-document', $contract) }}" method="POST" enctype="multipart/form-data" class="flex items-end space-x-3" novalidate>
                @csrf
                <div class="flex-1">
                    <div class="drop-zone compact flex items-center justify-between" id="pdf-zone">
                        <span class="text-sm text-gray-600">{!! __('ui.contracts.upload_section.select_text') !!}</span>
                        <span id="pdf-label" class="text-xs text-gray-400">{{ __('ui.common.max_pdf', ['size' => round(config('kontbox.max_pdf_size_kb') / 1024)]) }}</span>
                        <input type="file" name="signed_pdf" accept=".pdf" required onchange="document.getElementById('pdf-label').textContent = this.files[0] ? this.files[0].name : ''">
                    </div>
                </div>
                <button type="submit" class="btn px-5 py-2.5 bg-yellow-600 text-white rounded-xl hover:bg-yellow-700 font-medium shadow-lg shadow-yellow-200">{{ __('ui.actions.upload_sign_pdf') }}</button>
            </form>
        </div>

        {{-- ANULATE SECTION --}}
        <div id="anulate-section" class="hidden p-8 border-t-2 border-red-300 bg-red-50/30">
            <h3 class="font-semibold text-red-800 mb-3">{{ __('ui.contracts.anulation_section.title') }}</h3>
            <p class="text-sm text-red-700 mb-4">{{ __('ui.contracts.anulation_section.description') }}</p>
            <form action="{{ route('contracts.anulate', $contract) }}" method="POST" class="flex items-end space-x-3" novalidate>
                @csrf
                <input type="text" name="reason" placeholder="{{ __('ui.contracts.anulation_section.reason_placeholder') }}"
                    class="flex-1 rounded-xl border-gray-300 text-sm focus:border-red-400 focus:ring-red-200" required minlength="10">
                <button type="submit" class="btn px-5 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 font-medium shadow-lg shadow-red-200">{{ __('ui.actions.confirm_anulation') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
