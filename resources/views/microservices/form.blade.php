@extends('layouts.app')
@section('title', isset($microservice) ? __('ui.microservices.form_title_edit') : __('ui.microservices.form_title_create'))
@section('content')
<div class="max-w-2xl mx-auto fade-in">
    <div class="flex items-center space-x-4 mb-8">
        <div class="w-12 h-12 bg-indigo-100 rounded-2xl flex items-center justify-center shadow-sm">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ isset($microservice) ? __('ui.microservices.form_title_edit') : __('ui.microservices.form_title_create') }}</h2>
            <p class="text-sm text-gray-500">{{ isset($microservice) ? __('ui.microservices.form_description_edit') : __('ui.microservices.form_description_create') }}</p>
        </div>
    </div>

    <form action="{{ isset($microservice) ? route('microservices.update', $microservice) : route('microservices.store') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 space-y-6" novalidate>
        @csrf
        @if(isset($microservice)) @method('PUT') @endif

        <div class="space-y-5">
            <div>
                <label class="form-label">{{ __('ui.microservices.form_name_label') }} <span class="required">*</span></label>
                <input type="text" name="name" value="{{ old('name', $microservice->name ?? '') }}"
                    class="form-input block w-full px-4 py-3 rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 @error('name') error @enderror"
                    placeholder="{{ __('ui.microservices.form_name_placeholder') }}">
                @error('name') <p class="mt-1.5 text-sm text-red-500 flex items-center space-x-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg><span>{{ $message }}</span></p> @enderror
            </div>
            <div>
                <label class="form-label">{{ __('ui.microservices.form_description_label') }}</label>
                <textarea name="description" rows="3"
                    class="form-input block w-full px-4 py-3 rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 @error('description') error @enderror"
                    placeholder="{{ __('ui.microservices.form_description_placeholder') }}">{{ old('description', $microservice->description ?? '') }}</textarea>
                @error('description') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">{{ __('ui.microservices.form_base_cost_label') }} <span class="required">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium text-sm">$</span>
                        <input type="number" step="0.01" name="base_cost" value="{{ old('base_cost', $microservice->base_cost ?? '') }}"
                            class="form-input no-spinner block w-full pl-8 pr-4 py-3 rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 @error('base_cost') error @enderror"
                            placeholder="0.00">
                    </div>
                    @error('base_cost') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">{{ __('ui.microservices.form_type_label') }} <span class="required">*</span></label>
                    <select name="type"
                        class="select-custom block w-full px-4 py-3 rounded-xl border border-gray-300 shadow-sm @error('type') error @enderror">
                        <option value="recurring" {{ old('type', $microservice->type ?? '') === 'recurring' ? 'selected' : '' }}>{{ __('ui.microservices.form_type_recurring') }}</option>
                        <option value="one_time" {{ old('type', $microservice->type ?? '') === 'one_time' ? 'selected' : '' }}>{{ __('ui.microservices.form_type_one_time') }}</option>
                    </select>
                    @error('type') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
            <a href="{{ route('microservices.index') }}" class="btn px-5 py-2.5 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 font-medium">{{ __('ui.actions.cancel') }}</a>
            <button type="submit" class="btn px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-xl hover:from-indigo-700 hover:to-indigo-800 shadow-lg shadow-indigo-200 font-medium">
                {{ isset($microservice) ? __('ui.actions.update') : __('ui.actions.save') }}
            </button>
        </div>
    </form>
</div>
@endsection
