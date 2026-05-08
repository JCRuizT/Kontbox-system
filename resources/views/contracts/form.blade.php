@extends('layouts.app')
@section('title', __('ui.actions.create') . ' ' . __('ui.contracts.title'))
@section('content')
<div class="max-w-4xl mx-auto fade-in">
    <div class="flex items-center space-x-4 mb-8">
        <div class="w-12 h-12 bg-amber-100 rounded-2xl flex items-center justify-center shadow-sm">
            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ __('ui.actions.create') . ' ' . __('ui.contracts.title') }}</h2>
            <p class="text-sm text-gray-500">{{ __('ui.contracts.form_based_on', ['number' => $quotation->quote_number]) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">{{ __('ui.contracts.form_summary') }}</h3>
        <div class="grid grid-cols-2 gap-4">
            <div class="p-4 bg-gray-50 rounded-xl">
                <p class="text-xs text-gray-500 uppercase tracking-wider">{{ __('ui.quotations.select_prospect') }}</p>
                <p class="font-semibold mt-1">{{ $quotation->prospect->company_name ?? 'N/A' }}</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-xl">
                <p class="text-xs text-gray-500 uppercase tracking-wider">{{ __('ui.contracts.form_quoted_total') }}</p>
                <p class="font-semibold mt-1 text-indigo-600">${{ number_format($quotation->total, 2) }}</p>
            </div>
        </div>
    </div>

    <form action="{{ route('contracts.store') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 space-y-6" novalidate>
        @csrf
        <input type="hidden" name="quotation_id" value="{{ $quotation->id }}">
        <div>
            <label class="form-label">{{ __('ui.contracts.form_total_label') }} <span class="required">*</span></label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium text-sm">$</span>
                <input type="number" step="0.01" name="total_amount" value="{{ old('total_amount', $quotation->total) }}"
                    class="form-input no-spinner block w-full pl-8 pr-4 py-3 rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 @error('total_amount') error @enderror"
                    placeholder="0.00">
            </div>
            @error('total_amount') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>
        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
            <a href="{{ route('quotations.show', $quotation) }}" class="btn px-5 py-2.5 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 font-medium">{{ __('ui.actions.cancel') }}</a>
            <button type="submit" class="btn px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-xl hover:from-indigo-700 hover:to-indigo-800 shadow-lg shadow-indigo-200 font-medium">{{ __('ui.actions.save') }}</button>
        </div>
    </form>
</div>
@endsection
