@extends('layouts.app')
@section('title', isset($activity) ? __('ui.activities.form_title_edit') : __('ui.activities.form_title_create'))
@section('content')
<div class="max-w-2xl mx-auto fade-in">
    <div class="flex items-center space-x-4 mb-8">
        <div class="w-12 h-12 bg-cyan-100 rounded-2xl flex items-center justify-center shadow-sm">
            <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ isset($activity) ? __('ui.activities.form_title_edit') : __('ui.activities.form_title_create') }}</h2>
            <p class="text-sm text-gray-500">{{ isset($activity) ? __('ui.activities.form_description_edit') : __('ui.activities.form_description_create') }}</p>
        </div>
    </div>

    <form action="{{ isset($activity) ? route('activities.update', $activity) : route('activities.store') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 space-y-6" novalidate>
        @csrf
        @if(isset($activity)) @method('PUT') @endif

        @if(isset($activity))
        {{-- Edición: microservice_id es inmutable, solo se muestra como información --}}
        <div>
            <label class="form-label">{{ __('ui.activities_form.microservice_label') }}</label>
            <p class="text-xs text-gray-400 mb-2">{{ __('ui.activities_form.microservice_immutable_hint') }}</p>
            <div class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-gray-50 border border-gray-200">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                    {{ strtoupper(substr($activity->microservice->name ?? '--', 0, 2)) }}
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $activity->microservice->name ?? 'N/A' }}</p>
                    <p class="text-xs text-gray-400">{{ __('ui.activities_form.microservice_immutable') }}</p>
                </div>
            </div>
            <input type="hidden" name="microservice_id" value="{{ $activity->microservice_id }}">
        </div>
        @else
        {{-- Creación: se selecciona el microservicio padre --}}
        <div>
            <label class="form-label">{{ __('ui.activities_form.microservice_label') }} <span class="required">*</span></label>
            <p class="text-xs text-gray-400 mb-2">{{ __('ui.activities_form.microservice_hint') }}</p>
            <select name="microservice_id" class="select-custom block w-full px-4 py-3 rounded-xl border border-gray-300 shadow-sm @error('microservice_id') error @enderror">
                <option value="">{{ __('ui.activities_form.microservice_placeholder') }}</option>
                @foreach($microservices as $ms)
                <option value="{{ $ms->id }}" {{ old('microservice_id', request('microservice_id')) == $ms->id ? 'selected' : '' }}>{{ $ms->name }} (${{ number_format($ms->base_cost, 2) }})</option>
                @endforeach
            </select>
            @error('microservice_id') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>
        @endif

        <div>
            <label class="form-label">{{ __('ui.activities.name_label') }} <span class="required">*</span></label>
            <input type="text" name="name" value="{{ old('name', $activity->name ?? '') }}"
                class="form-input block w-full px-4 py-3 rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 @error('name') error @enderror"
                placeholder="{{ __('ui.activities_form.name_placeholder') }}">
            @error('name') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="form-label">{{ __('ui.common.description') }}</label>
            <textarea name="description" rows="3"
                class="form-input block w-full px-4 py-3 rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 @error('description') error @enderror"
                placeholder="{{ __('ui.activities_form.description_placeholder') }}">{{ old('description', $activity->description ?? '') }}</textarea>
            @error('description') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>

        @can('activities.essential')
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('ui.activities_form.essential_label') }}</label>
            <p class="text-xs text-gray-400 mb-3">{{ __('ui.activities_form.essential_hint') }}</p>
            <label for="is_essential"
                class="inline-flex items-center space-x-3 px-4 py-3 rounded-xl border-2 cursor-pointer transition-all duration-150
                {{ old('is_essential', $activity->is_essential ?? false) ? 'bg-amber-50 border-amber-300 shadow-sm' : 'bg-white border-gray-200 hover:border-amber-200 hover:bg-amber-50/30' }}">
                <input type="hidden" name="is_essential" value="0">
                <input type="checkbox" name="is_essential" id="is_essential" value="1"
                    class="shrink-0 w-5 h-5 rounded border-gray-300 text-amber-500 focus:ring-amber-400 cursor-pointer"
                    {{ old('is_essential', $activity->is_essential ?? false) ? 'checked' : '' }}>
                <div>
                    <span class="text-sm font-medium {{ old('is_essential', $activity->is_essential ?? false) ? 'text-amber-800' : 'text-gray-700' }}">{{ __('ui.activities_form.essential_label') }}</span>
                    <p class="text-xs {{ old('is_essential', $activity->is_essential ?? false) ? 'text-amber-600' : 'text-gray-400' }}">{{ __('ui.activities_form.essential_hint') }}</p>
                </div>
            </label>
            @error('is_essential') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>
        @else
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 flex items-start space-x-3">
            <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <div>
                <p class="text-sm font-medium text-gray-500">{{ __('ui.activities_form.essential_label') }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $activity->is_essential ?? false ? __('ui.activities.essential') : __('ui.common.no') }}</p>
            </div>
        </div>
        @endcan

        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start space-x-3">
            <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
            <p class="text-sm text-amber-800">{{ __('ui.activities_form.switch_info') }}</p>
        </div>

        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
            <a href="{{ route('activities.index') }}" class="btn px-5 py-2.5 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 font-medium">{{ __('ui.actions.cancel') }}</a>
            <button type="submit" class="btn px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-xl hover:from-indigo-700 hover:to-indigo-800 shadow-lg shadow-indigo-200 font-medium">
                {{ isset($activity) ? __('ui.actions.update') : __('ui.actions.save') }}
            </button>
        </div>
    </form>
</div>
@endsection
