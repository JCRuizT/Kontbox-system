@extends('layouts.app')
@section('title', __('ui.invoices.form_title'))
@section('content')
<div class="max-w-2xl mx-auto fade-in">
    <div class="flex items-center space-x-4 mb-8">
        <div class="w-12 h-12 bg-teal-100 rounded-2xl flex items-center justify-center shadow-sm">
            <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ __('ui.invoices.form_title') }}</h2>
            <p class="text-sm text-gray-500">{{ __('ui.invoices.form_description') }}</p>
        </div>
    </div>

    <form action="{{ route('invoices.store') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 space-y-6" novalidate>
        @csrf
        <div>
            <x-select-searchable
                name="contract_id"
                label="{{ __('ui.invoices.form_contract_label') }}"
                placeholder="Buscar contrato activo..."
                search-url="{{ route('search.contracts') }}"
                :options="$initialContracts"
                :required="true" />
            @error('contract_id') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('ui.invoices.form_amount_label') }} <span class="text-red-400">*</span></label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium text-sm">$</span>
                <input type="number" step="0.01" name="amount" value="{{ old('amount') }}"
                    class="form-input no-spinner block w-full pl-8 pr-4 py-3 rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 @error('amount') error @enderror"
                    placeholder="0.00">
            </div>
            @error('amount') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('ui.invoices.form_emission_label') }} <span class="text-red-400">*</span></label>
            <input type="date" name="issued_date" value="{{ old('issued_date', date('Y-m-d')) }}"
                class="form-input date-custom block w-full px-4 py-3 rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 @error('issued_date') error @enderror">
            @error('issued_date') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('ui.invoices.form_notes_label') }}</label>
            <textarea name="notes" rows="3"
                class="form-input block w-full px-4 py-3 rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 @error('notes') error @enderror"
                placeholder="{{ __('ui.invoices.form_notes_placeholder') }}">{{ old('notes') }}</textarea>
            @error('notes') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>
        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
            <a href="{{ route('invoices.index') }}" class="btn px-5 py-2.5 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 font-medium">{{ __('ui.actions.cancel') }}</a>
            <button type="submit" class="btn px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-xl hover:from-indigo-700 hover:to-indigo-800 shadow-lg shadow-indigo-200 font-medium">{{ __('ui.actions.save') }}</button>
        </div>
    </form>
</div>
@endsection
