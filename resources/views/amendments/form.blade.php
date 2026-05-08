@extends('layouts.app')
@section('title', __('ui.amendments.form_title') . ' - ' . $contract->contract_number)
@section('content')
<div class="max-w-3xl mx-auto fade-in">
    <div class="flex items-center space-x-4 mb-8">
        <div class="w-12 h-12 bg-purple-100 rounded-2xl flex items-center justify-center shadow-sm">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ __('ui.amendments.form_title') }}</h2>
            <p class="text-sm text-gray-500">{{ __('ui.amendments.form_description', ['number' => $contract->contract_number]) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">{{ __('ui.amendments.form_section_active') }}</h3>
        <div class="grid grid-cols-2 gap-4">
            <div class="p-4 bg-gray-50 rounded-xl">
                <p class="text-xs text-gray-500 uppercase tracking-wider">{{ __('ui.common.client') }}</p>
                <p class="font-semibold mt-1">{{ $contract->quotation->prospect->company_name ?? 'N/A' }}</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-xl">
                <p class="text-xs text-gray-500 uppercase tracking-wider">{{ __('ui.common.amount') }}</p>
                <p class="font-semibold mt-1">${{ number_format($contract->total_amount, 2) }}</p>
            </div>
        </div>
    </div>

    <form action="{{ route('amendments.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 space-y-6" novalidate>
        @csrf
        <input type="hidden" name="contract_id" value="{{ $contract->id }}">

        <div>
            <label class="form-label">{{ __('ui.amendments.form_description_label') }} <span class="required">*</span></label>
            <textarea name="description" rows="4"
                class="form-input block w-full px-4 py-3 rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 @error('description') error @enderror"
                placeholder="{{ __('ui.amendments.form_description_placeholder') }}">{{ old('description') }}</textarea>
            @error('description') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="form-label">{{ __('ui.amendments.form_pdf_label') }} <span class="required">*</span></label>
            <p class="text-xs text-gray-500 mb-3">{{ __('domain.amendment.pdf_required') }}</p>
            <div class="drop-zone" id="pdf-dropzone">
                <div class="flex flex-col items-center space-y-2">
                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                    <p class="text-sm text-gray-500">{!! __('ui.amendments.form_dropzone_text') !!}</p>
                    <p class="text-xs text-gray-400">{{ __('ui.amendments.form_dropzone_hint', ['size' => round(config('kontbox.max_pdf_size_kb') / 1024)]) }}</p>
                </div>
                <input type="file" name="signed_pdf" accept=".pdf" class="file-zone-input" required>
            </div>
            <div id="file-name" class="hidden mt-3 flex items-center space-x-2 text-sm text-emerald-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span id="file-name-text"></span>
            </div>
            @error('signed_pdf') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>

        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start space-x-3">
            <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
            <p class="text-sm text-amber-800">{!! __('ui.amendments.form_security_notice', ['message' => __('domain.amendment.pdf_required')]) !!}</p>
        </div>

        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
            <a href="{{ route('contracts.show', $contract) }}" class="btn px-5 py-2.5 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 font-medium">{{ __('ui.actions.cancel') }}</a>
            <button type="submit" class="btn px-6 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-700 text-white rounded-xl hover:from-purple-700 hover:to-indigo-800 shadow-lg shadow-purple-200 font-medium">{{ __('ui.actions.register_amendment') }}</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const dropzone = document.getElementById('pdf-dropzone');
    const fileInput = dropzone.querySelector('.file-zone-input');
    const fileName = document.getElementById('file-name');
    const fileNameText = document.getElementById('file-name-text');

    ['dragenter', 'dragover'].forEach(evt => {
        dropzone.addEventListener(evt, e => { e.preventDefault(); dropzone.classList.add('dragover'); });
    });
    ['dragleave', 'drop'].forEach(evt => {
        dropzone.addEventListener(evt, e => { e.preventDefault(); dropzone.classList.remove('dragover'); });
    });
    dropzone.addEventListener('drop', e => {
        fileInput.files = e.dataTransfer.files;
        updateFileName();
    });
    fileInput.addEventListener('change', updateFileName);
    function updateFileName() {
        if (fileInput.files.length > 0) {
            fileName.classList.remove('hidden');
            fileNameText.textContent = fileInput.files[0].name + ' (' + (fileInput.files[0].size / 1024).toFixed(1) + ' KB)';
        }
    }
</script>
@endpush
