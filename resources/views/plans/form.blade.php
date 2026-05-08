@extends('layouts.app')
@section('title', isset($plan) ? __('ui.plans.form_title_edit') . ': ' . $plan->name : __('ui.plans.form_title_create'))
@section('content')
<div class="max-w-4xl mx-auto fade-in">
    <div class="flex items-center space-x-4 mb-8">
        <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center shadow-sm">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ isset($plan) ? __('ui.plans.form_title_edit') : __('ui.plans.form_title_create') }}</h2>
            <p class="text-sm text-gray-500">{{ isset($plan) ? __('ui.plans.form_description_edit') : __('ui.plans.form_description_create') }}</p>
        </div>
    </div>

    <form action="{{ isset($plan) ? route('plans.update', $plan) : route('plans.store') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 space-y-6" novalidate>
        @csrf
        @if(isset($plan)) @method('PUT') @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="form-label">{{ __('ui.plans.form_name_label') }} <span class="required">*</span></label>
                <input type="text" name="name" value="{{ old('name', $plan->name ?? '') }}"
                    class="form-input block w-full px-4 py-3 rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 @error('name') error @enderror"
                    placeholder="{{ __('ui.plans.form_name_placeholder') }}">
                @error('name') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label class="form-label">{{ __('ui.plans.form_description_label') }}</label>
                <textarea name="description" rows="2"
                    class="form-input block w-full px-4 py-3 rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 @error('description') error @enderror"
                    placeholder="{{ __('ui.plans.form_description_placeholder') }}">{{ old('description', $plan->description ?? '') }}</textarea>
                @error('description') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        <div id="services-container" class="space-y-3">
            <div class="flex items-center justify-between">
                <label class="form-label">{{ __('ui.plans.form_services_label') }} <span class="required">*</span></label>
                <button type="button" onclick="addService()" class="btn inline-flex items-center space-x-1.5 text-sm text-indigo-600 hover:text-indigo-800 font-medium px-3 py-1.5 rounded-lg hover:bg-indigo-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>{{ __('ui.plans.form_add_service') }}</span>
                </button>
            </div>
            @if(isset($plan) && $plan->services->count() > 0)
                @foreach($plan->services as $idx => $svc)
                <div class="service-row grid grid-cols-12 gap-3 p-4 bg-gray-50 rounded-xl items-end">
                    <div class="col-span-5">
                        <label class="block text-xs text-gray-500 mb-1">{{ __('ui.plans.form_service_label') }}</label>
                        <select name="services[{{ $idx }}][microservice_id]" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-200">
                            <option value="">{{ __('ui.plans.form_select_placeholder') }}</option>
                            @foreach($microservices as $ms)
                            <option value="{{ $ms->id }}" {{ $svc->microservice_id === $ms->id ? 'selected' : '' }}>{{ $ms->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">{{ __('ui.plans.form_quantity_label') }}</label>
                        <input type="number" name="services[{{ $idx }}][quantity]" min="1" value="{{ $svc->quantity }}" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-200">
                    </div>
                    <div class="col-span-4">
                        <label class="block text-xs text-gray-500 mb-1">{{ __('ui.plans.form_custom_price_label') }}</label>
                        <input type="number" step="0.01" name="services[{{ $idx }}][custom_price]" placeholder="{{ __('ui.plans.form_custom_price_placeholder') }}" value="{{ $svc->custom_price }}" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-200">
                    </div>
                    <div class="col-span-1">
                        <button type="button" onclick="this.closest('.service-row').remove()" class="btn w-full p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                            <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
                @endforeach
            @else
            <div class="service-row grid grid-cols-12 gap-3 p-4 bg-gray-50 rounded-xl items-end">
                <div class="col-span-5">
                    <label class="block text-xs text-gray-500 mb-1">{{ __('ui.plans_form.service_label') }}</label>
                    <select name="services[0][microservice_id]" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-200" required>
                        <option value="">{{ __('ui.plans_form.select_placeholder') }}</option>
                        @foreach($microservices as $ms)
                        <option value="{{ $ms->id }}">{{ $ms->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">{{ __('ui.plans_form.quantity_label') }}</label>
                    <input type="number" name="services[0][quantity]" min="1" value="1" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-200">
                </div>
                <div class="col-span-4">
                    <label class="block text-xs text-gray-500 mb-1">{{ __('ui.plans_form.custom_price_label') }}</label>
                    <input type="number" step="0.01" name="services[0][custom_price]" placeholder="{{ __('ui.plans.form_custom_price_placeholder') }}" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-200">
                </div>
                <div class="col-span-1">
                    <button type="button" onclick="this.closest('.service-row').remove()" class="btn w-full p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                        <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
            @endif
        </div>

        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
            <a href="{{ route('plans.index') }}" class="btn px-5 py-2 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 font-medium">{{ __('ui.actions.cancel') }}</a>
            <button type="submit" class="btn px-6 py-2 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-xl hover:from-indigo-700 hover:to-indigo-800 shadow-lg shadow-indigo-200 font-medium">
                {{ isset($plan) ? __('ui.actions.update') : __('ui.actions.save') }}
            </button>
        </div>
    </form>
</div>
<script>
    let svcIndex = {{ (isset($plan) ? $plan->services->count() : 1) }};
    function addService() {
        const container = document.getElementById('services-container');
        const row = document.createElement('div');
        row.className = 'service-row grid grid-cols-12 gap-3 p-4 bg-gray-50 rounded-xl items-end fade-in';
        row.innerHTML = `
            <div class="col-span-5">
                <label class="block text-xs text-gray-500 mb-1">{{ __('ui.plans.form_service_label') }}</label>
                <select name="services[\${svcIndex}][microservice_id]" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-200">
                    <option value="">{{ __('ui.plans.form_select_placeholder') }}</option>
                    @foreach($microservices as $ms)
                    <option value="{{ $ms->id }}">{{ $ms->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-2">
                <label class="block text-xs text-gray-500 mb-1">{{ __('ui.plans.form_quantity_label') }}</label>
                <input type="number" name="services[\${svcIndex}][quantity]" min="1" value="1" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-200">
            </div>
            <div class="col-span-4">
                <label class="block text-xs text-gray-500 mb-1">{{ __('ui.plans.form_custom_price_label') }}</label>
                <input type="number" step="0.01" name="services[\${svcIndex}][custom_price]" placeholder="{{ __('ui.plans.form_custom_price_placeholder') }}" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-200">
            </div>
            <div class="col-span-1">
                <button type="button" onclick="this.closest('.service-row').remove()" class="w-full p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                    <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        `;
        container.appendChild(row);
        svcIndex++;
    }
</script>
@endsection
