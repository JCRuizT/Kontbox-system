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

        <div>
            <label class="form-label">{{ __('ui.activities_form.microservice_label') }} <span class="required">*</span></label>
            <p class="text-xs text-gray-400 mb-2">{{ __('ui.activities_form.microservice_hint') }}</p>
            <select name="microservice_id" class="select-custom block w-full px-4 py-3 rounded-xl border border-gray-300 shadow-sm @error('microservice_id') error @enderror">
                <option value="">{{ __('ui.activities_form.microservice_placeholder') }}</option>
                @foreach($microservices as $ms)
                <option value="{{ $ms->id }}" {{ old('microservice_id', $activity->microservice_id ?? '') == $ms->id ? 'selected' : '' }}>{{ $ms->name }} (${{ number_format($ms->base_cost, 2) }})</option>
                @endforeach
            </select>
            @error('microservice_id') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>

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

        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start space-x-3">
            <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
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
