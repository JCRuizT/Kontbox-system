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

        <input type="hidden" name="services_data" id="services-data-input" value="">

        <div id="services-root" class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">{{ __('ui.plans.form_services_label') }} <span class="required">*</span> <span id="selected-count" class="text-indigo-600">(0)</span></h3>
                <button type="button" onclick="showAddServiceModal()" class="btn inline-flex items-center space-x-1.5 text-sm text-indigo-600 hover:text-indigo-800 font-medium px-3 py-1.5 rounded-lg hover:bg-indigo-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>{{ __('ui.plans.form_add_service') }}</span>
                </button>
            </div>
            <div id="services-list" class="space-y-4">
                <p id="no-services-msg" class="text-center py-12 text-gray-400 text-sm">
                    {{ __('ui.plans.form_no_services_msg') }}
                </p>
            </div>
        </div>

        {{-- MODAL AGREGAR MICROSERVICIO --}}
        <div id="add-service-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm" onclick="if(event.target===this)this.classList.add('hidden')">
            <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg mx-4 max-h-[80vh] overflow-y-auto fade-in">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">{{ __('ui.plans.form_add_service_modal_title') }}</h3>
                    <button type="button" onclick="document.getElementById('add-service-modal').classList.add('hidden')" class="p-1 rounded-lg hover:bg-gray-100 transition">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="space-y-2">
                    @foreach($microservices as $ms)
                    @php $firstAct = $ms->activities->first(); $actCount = $ms->activities->count(); @endphp
                    <div class="flex items-center justify-between p-3 rounded-xl border border-gray-200 hover:border-indigo-200 hover:bg-indigo-50/30 transition cursor-pointer"
                         onclick="addServiceToPlan({{ $ms->id }})">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900">{{ $ms->name }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">${{ number_format($ms->base_cost, 2) }}
                                @if($firstAct) · <span class="text-cyan-600">{{ $firstAct->name }}{{ $actCount > 1 ? ' (+' . ($actCount-1) . ' más)' : '' }}</span> @endif
                            </p>
                        </div>
                        <svg class="w-5 h-5 text-indigo-400 flex-shrink-0 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    @endforeach
                </div>
            </div>
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
let selectedServices = {};
let excludedActivities = {};
let serviceIndex = 0;
let planFormMicroservices = {};

@php
    $planFormMsData = [];
    foreach ($microservices as $ms) {
        $acts = [];
        foreach ($ms->activities as $act) {
            $acts[] = ['id' => $act->id, 'name' => $act->name, 'essential' => $act->is_essential];
        }
        $planFormMsData[$ms->id] = [
            'id' => $ms->id,
            'name' => $ms->name,
            'base_cost' => $ms->base_cost,
            'activities' => $acts,
        ];
    }
@endphp
planFormMicroservices = @json($planFormMsData);

@if(isset($plan) && $plan->services->count() > 0)
    @foreach($plan->services as $svc)
        @php
            $msData = $planFormMsData[$svc->microservice_id] ?? null;
            $excluded = $plan->planActivities
                ->where('activity_id', '!=', null)
                ->where('is_enabled', false)
                ->pluck('activity_id')
                ->toArray();
        @endphp
        @if($msData)
        (function() {
            const id = {{ $msData['id'] }};
            selectedServices[id] = {
                id: id,
                name: @json($msData['name']),
                unit_price: {{ $svc->custom_price ?? $msData['base_cost'] }},
                activities: @json($msData['activities']),
                enabled: true
            };
            excludedActivities[id] = @json($excluded);
            serviceIndex++;
        })();
        @endif
    @endforeach
    renderServicesList();
@endif

function addServiceToPlan(id) {
    const data = planFormMicroservices[id];
    if (!data) return;
    if (selectedServices[id]) {
        selectedServices[id].enabled = true;
    } else {
        selectedServices[id] = {
            id: id,
            name: data.name,
            unit_price: data.base_cost,
            activities: data.activities || [],
            enabled: true
        };
        if (!excludedActivities[id]) excludedActivities[id] = [];
    }
    renderServicesList();
    document.getElementById('add-service-modal').classList.add('hidden');
}

function removeService(id) {
    delete selectedServices[id];
    delete excludedActivities[id];
    renderServicesList();
}

function updatePrice(id, value) {
    if (selectedServices[id]) {
        selectedServices[id].unit_price = parseFloat(value) || 0;
    }
    syncHiddenInput();
}

function toggleService(id) {
    if (!selectedServices[id]) return;
    selectedServices[id].enabled = !selectedServices[id].enabled;
    renderServicesList();
}

function toggleActivity(msId, actId) {
    if (!excludedActivities[msId]) excludedActivities[msId] = [];
    const idx = excludedActivities[msId].indexOf(actId);
    if (idx > -1) {
        excludedActivities[msId].splice(idx, 1);
    } else {
        excludedActivities[msId].push(actId);
    }
    renderServicesList();
}

function renderServicesList() {
    const container = document.getElementById('services-list');
    const noMsg = document.getElementById('no-services-msg');
    const ids = Object.keys(selectedServices);
    const activeCount = ids.filter(i => selectedServices[i].enabled).length;

    document.getElementById('selected-count').textContent = `(${activeCount})`;

    if (ids.length === 0) {
        container.innerHTML = '';
        container.appendChild(noMsg);
        if (noMsg) noMsg.style.display = '';
        syncHiddenInput();
        return;
    }
    if (noMsg) noMsg.style.display = 'none';

    let html = '';
    ids.forEach((id, idx) => {
        const s = selectedServices[id];
        const excluded = excludedActivities[id] || [];

        const activitiesHtml = s.activities && s.activities.length
            ? `<div class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-gray-100">
                ${s.activities.map(a => {
                    const isExcluded = excluded.includes(a.id);
                    const isEssential = a.essential;
                    return `<label class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-lg border cursor-pointer transition-all duration-150
                        ${isExcluded ? 'bg-gray-50 border-gray-200' : 'bg-cyan-50 border-cyan-200'}
                        ${isEssential ? 'opacity-60 cursor-not-allowed' : 'hover:bg-cyan-100'}">
                        <input type="checkbox" ${isEssential ? 'disabled checked' : (isExcluded ? '' : 'checked')}
                            onchange="toggleActivity(${id}, ${a.id})"
                            class="w-3.5 h-3.5 rounded border-gray-300 text-cyan-600 focus:ring-cyan-500 cursor-pointer ${isEssential ? 'opacity-50' : ''}">
                        <span class="text-xs ${isExcluded ? 'text-gray-400' : 'text-cyan-800 font-medium'}">${a.name}</span>
                        ${isEssential ? '<svg class="w-3 h-3 text-amber-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>' : ''}
                    </label>`;
                }).join('')}
            </div>`
            : '';

        html += `
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="px-5 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3 min-w-0 flex-1">
                        <label class="inline-flex items-center space-x-2 cursor-pointer flex-shrink-0">
                            <span class="w-10 h-5 rounded-full relative transition ${s.enabled ? 'bg-indigo-600' : 'bg-gray-300'}" onclick="toggleService(${id})">
                                <span class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white shadow transition ${s.enabled ? 'translate-x-5' : ''}"></span>
                            </span>
                            <span class="text-sm font-semibold ${s.enabled ? 'text-gray-900' : 'text-gray-400'} truncate">${s.name}</span>
                        </label>
                    </div>
                    <div class="flex items-center space-x-2 flex-shrink-0 ml-2">
                        <div class="w-28">
                            <input type="number" step="0.01" value="${s.unit_price}" onchange="updatePrice(${id}, this.value)"
                                placeholder="{{ __('ui.plans.form_custom_price_placeholder') }}"
                                class="form-input no-spinner block w-full rounded-lg border-gray-300 text-xs py-1.5 px-2 ${!s.enabled ? 'opacity-40' : ''}"
                                ${!s.enabled ? 'disabled' : ''}>
                        </div>
                        <button type="button" onclick="removeService(${id})" class="text-red-400 hover:text-red-600 p-1 rounded-lg hover:bg-red-50 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
                ${!s.enabled ? '<p class="text-xs text-gray-400 mt-2">{{ __('ui.plans.form_service_excluded') }}</p>' : ''}
                <div class="${s.enabled ? '' : 'hidden'}">
                    ${activitiesHtml}
                </div>
            </div>
        </div>`;
    });

    container.innerHTML = html;
    syncHiddenInput();
}

function syncHiddenInput() {
    document.getElementById('services-data-input').value = JSON.stringify(
        Object.values(selectedServices).filter(s => s.enabled).map(s => ({
            microservice_id: s.id,
            unit_price: s.unit_price,
            excluded_activities: excludedActivities[s.id] || []
        }))
    );
}

function showAddServiceModal() {
    document.getElementById('add-service-modal').classList.remove('hidden');
}
</script>
@endsection
