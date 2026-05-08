@extends('layouts.app')
@section('title', __('ui.quotations.form_title'))
@section('content')
<div class="max-w-4xl mx-auto fade-in">
    <div class="flex items-center space-x-4 mb-8">
        <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center shadow-sm">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ __('ui.quotations.form_title') }}</h2>
            <p class="text-sm text-gray-500">{{ __('ui.quotations.form_description') }}</p>
        </div>
    </div>

    <form action="{{ route('quotations.store') }}" method="POST" class="space-y-6" novalidate>
        @csrf
        <input type="hidden" name="selected_items" id="selected-items-input" value="">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-select-searchable
                        name="prospect_id"
                        label="{{ __('ui.quotations.select_prospect') }}"
                        placeholder="{{ __('ui.quotations_form.search_prospect') }}"
                        search-url="{{ route('search.prospects') }}"
                        :options="$initialProspects"
                        :required="true" />
                </div>
                <div>
                    <x-select-searchable
                        name="plan_id"
                        label="{{ __('ui.quotations.select_plan') }}"
                        placeholder="{{ __('ui.quotations_form.search_plan') }}"
                        search-url="{{ route('search.plans') }}"
                        :options="$initialPlans" />
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('ui.quotations.valid_until') }}</label>
                <input type="date" name="valid_until" id="valid-until" value="{{ old('valid_until', now()->addDays(config('kontbox.quotation_valid_days'))->format('Y-m-d')) }}"
                    class="form-input date-custom block w-full px-4 py-3 rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500">
            </div>
        </div>

        {{-- SERVICIOS POR ACTIVIDAD --}}
        <div id="services-root" class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">{{ __('ui.quotations.selected_services') }} <span id="selected-count" class="text-indigo-600">(0)</span></h3>
                <button type="button" onclick="showAddServiceModal()" class="btn inline-flex items-center space-x-1.5 text-sm text-indigo-600 hover:text-indigo-800 font-medium px-3 py-1.5 rounded-lg hover:bg-indigo-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>{{ __('ui.quotations.add_service') }}</span>
                </button>
            </div>
            <div id="services-list" class="space-y-4">
                <p id="no-services-msg" class="text-center py-12 text-gray-400 text-sm">
                    {{ __('ui.quotations.no_services_msg') }}
                </p>
            </div>
        </div>

        {{-- MODAL AGREGAR MICROSERVICIO --}}
        <div id="add-service-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm" onclick="if(event.target===this)this.classList.add('hidden')">
            <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg mx-4 max-h-[80vh] overflow-y-auto fade-in">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">{{ __('ui.quotations.add_service_modal_title') }}</h3>
                    <button type="button" onclick="document.getElementById('add-service-modal').classList.add('hidden')" class="p-1 rounded-lg hover:bg-gray-100 transition">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="space-y-2">
                    @foreach($allMicroservices as $ms)
                    @php $msActs = $ms->activities->map(fn($a)=>['id'=>$a->id,'name'=>$a->name])->values()->toArray(); @endphp
                    <div class="flex items-center justify-between p-3 rounded-xl border border-gray-200 hover:border-indigo-200 hover:bg-indigo-50/30 transition cursor-pointer"
                         onclick="addServiceToQuote({{ $ms->id }}, '{{ addslashes($ms->name) }}', {{ $ms->base_cost }}, {{ json_encode($msActs) }})">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900">{{ $ms->name }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">${{ number_format($ms->base_cost, 2) }}
                                @if(count($msActs) > 0) · <span class="text-cyan-600">{{ $msActs[0]['name'] }}{{ count($msActs) > 1 ? ' (+' . (count($msActs)-1) . ' más)' : '' }}</span> @endif
                            </p>
                        </div>
                        <svg class="w-5 h-5 text-indigo-400 flex-shrink-0 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3 pt-4">
            <a href="{{ route('quotations.index') }}" class="btn px-5 py-2.5 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 font-medium">{{ __('ui.actions.cancel') }}</a>
            <button type="submit" class="btn px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-xl hover:from-indigo-700 hover:to-indigo-800 shadow-lg shadow-indigo-200 font-medium">
                {{ __('ui.actions.save') }} {{ __('ui.quotations_form.save') }}
            </button>
        </div>
    </form>
</div>

<script>
const plansData = {!! $plansDataJson !!};
let selectedServices = {};

document.addEventListener('change', function(e) {
    if (e.target && e.target.name === 'plan_id') {
        loadPlanServices(e.target.value);
    }
});

function loadPlanServices(planId) {
    if (!planId) return;
    const services = plansData[planId];
    if (!services) return;
    services.forEach(s => {
        if (selectedServices[s.id]) {
            selectedServices[s.id].quantity = s.quantity;
            selectedServices[s.id].unit_price = s.custom_price || s.base_cost;
            selectedServices[s.id].activities = s.activities || [];
            selectedServices[s.id].activity_name = s.activities && s.activities.length ? s.activities[0].name : null;
        } else {
            selectedServices[s.id] = {
                id: s.id, name: s.name, quantity: s.quantity, unit_price: s.custom_price || s.base_cost,
                activities: s.activities || [], activity_name: s.activities && s.activities.length ? s.activities[0].name : null,
                enabled: true, activity_enabled: true
            };
        }
    });
    renderServicesList();
}

function addServiceToQuote(id, name, baseCost, activities) {
    if (selectedServices[id]) {
        selectedServices[id].enabled = true;
    } else {
        selectedServices[id] = {
            id, name, quantity: 1, unit_price: baseCost,
            activities: activities || [], activity_name: activities && activities.length ? activities[0].name : null,
            enabled: true, activity_enabled: true
        };
    }
    renderServicesList();
    document.getElementById('add-service-modal').classList.add('hidden');
}

function toggleService(id) {
    if (!selectedServices[id]) return;
    selectedServices[id].enabled = !selectedServices[id].enabled;
    renderServicesList();
}

function toggleActivity(name) {
    const ids = Object.keys(selectedServices);
    const affected = ids.filter(id => selectedServices[id].activity_name === name);
    const allEnabled = affected.every(id => selectedServices[id].enabled && selectedServices[id].activity_enabled);
    const newState = !allEnabled;
    affected.forEach(id => {
        selectedServices[id].activity_enabled = newState;
        selectedServices[id].enabled = newState;
    });
    renderServicesList();
}

function removeService(id) {
    delete selectedServices[id];
    renderServicesList();
}

function updateQuantity(id, value) {
    if (selectedServices[id]) {
        selectedServices[id].quantity = Math.max(1, parseInt(value) || 1);
    }
    syncHiddenInput();
}

function updatePrice(id, value) {
    if (selectedServices[id]) {
        selectedServices[id].unit_price = parseFloat(value) || 0;
    }
    syncHiddenInput();
}

function renderServicesList() {
    const container = document.getElementById('services-list');
    const noMsg = document.getElementById('no-services-msg');
    const ids = Object.keys(selectedServices);
    const activeCount = ids.filter(i => selectedServices[i].enabled).length;

    document.getElementById('selected-count').textContent = `(${activeCount} activos)`;

    if (ids.length === 0) {
        container.innerHTML = '';
        container.appendChild(noMsg);
        if (noMsg) noMsg.style.display = '';
        syncHiddenInput();
        return;
    }
    if (noMsg) noMsg.style.display = 'none';

    // Group by activity
    const grouped = {};
    const ungrouped = [];
    ids.forEach(id => {
        const s = selectedServices[id];
        if (s.activity_name) {
            if (!grouped[s.activity_name]) grouped[s.activity_name] = [];
            grouped[s.activity_name].push(id);
        } else {
            ungrouped.push(id);
        }
    });

    let html = '';

    // Render activity groups
    Object.keys(grouped).forEach(actName => {
        const groupIds = grouped[actName];
        const allEnabled = groupIds.every(id => selectedServices[id].enabled);
        const someEnabled = groupIds.some(id => selectedServices[id].enabled);
        const actEnabled = groupIds.every(id => selectedServices[id].activity_enabled !== false);

        html += `<div class="bg-white rounded-2xl shadow-sm border ${actEnabled && someEnabled ? 'border-cyan-200' : 'border-gray-200'} overflow-hidden">`;
        html += `<div class="px-5 py-3 bg-gradient-to-r from-cyan-50 to-white border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <label class="inline-flex items-center space-x-2 cursor-pointer">
                    <span class="w-10 h-5 rounded-full relative transition ${actEnabled ? 'bg-cyan-500' : 'bg-gray-300'}" onclick="event.stopPropagation();toggleActivity('${actName}')">
                        <span class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white shadow transition ${actEnabled ? 'translate-x-5' : ''}"></span>
                    </span>
                    <span class="text-sm font-semibold ${actEnabled ? 'text-gray-900' : 'text-gray-400'}">${actName}</span>
                </label>
                <span class="text-xs text-gray-400">${groupIds.filter(id => selectedServices[id].enabled).length}/${groupIds.length}</span>
            </div>
        </div>`;

        groupIds.forEach(id => {
            const s = selectedServices[id];
            html += renderServiceCard(id, s);
        });
        html += `</div>`;
    });

    // Ungrouped services
    ungrouped.forEach(id => {
        html += renderServiceCard(id, selectedServices[id]);
    });

    container.innerHTML = html;
    syncHiddenInput();
}

function renderServiceCard(id, s) {
    const activitiesHtml = s.activities && s.activities.length
        ? `<div class="flex flex-wrap gap-1 mt-2 pt-2 border-t border-gray-100">
            ${s.activities.map(a => `<span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-md text-xs font-medium bg-cyan-50 text-cyan-700 ring-1 ring-cyan-200">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>${a.name}</span></span>`).join('')}
        </div>`
        : `<p class="text-xs text-gray-400 mt-2 pt-2 border-t border-gray-100 italic">Sin actividad</p>`;

    return `
    <div class="px-5 py-4 ${!s.activity_name ? 'bg-white rounded-2xl shadow-sm border border-gray-200' : 'border-b border-gray-50 last:border-b-0'}">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center space-x-3">
                <label class="inline-flex items-center space-x-2 cursor-pointer">
                    <span class="w-10 h-5 rounded-full relative transition ${s.enabled ? 'bg-indigo-600' : 'bg-gray-300'}" onclick="toggleService(${id})">
                        <span class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white shadow transition ${s.enabled ? 'translate-x-5' : ''}"></span>
                    </span>
                    <span class="text-sm font-medium ${s.enabled ? 'text-gray-900' : 'text-gray-400'}">${s.name}</span>
                </label>
            </div>
            <button type="button" onclick="removeService(${id})" class="text-red-400 hover:text-red-600 p-1 rounded-lg hover:bg-red-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        ${!s.enabled ? '<p class="text-xs text-gray-400 mb-2">Servicio excluido</p>' : ''}
        <div class="grid grid-cols-2 gap-3 ${!s.enabled ? 'opacity-40 pointer-events-none' : ''}">
            <div><label class="block text-xs text-gray-500 mb-1">Cantidad</label>
                <input type="number" min="1" value="${s.quantity}" onchange="updateQuantity(${id}, this.value)"
                    class="form-input no-spinner block w-full rounded-lg border-gray-300 text-sm"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Precio Unitario</label>
                <input type="number" step="0.01" value="${s.unit_price}" onchange="updatePrice(${id}, this.value)"
                    class="form-input no-spinner block w-full rounded-lg border-gray-300 text-sm"></div>
        </div>
        ${activitiesHtml}
    </div>`;
}

function syncHiddenInput() {
    document.getElementById('selected-items-input').value = JSON.stringify(
        Object.values(selectedServices).filter(s => s.enabled).map(s => ({
            microservice_id: s.id, quantity: s.quantity, unit_price: s.unit_price
        }))
    );
}

function showAddServiceModal() {
    document.getElementById('add-service-modal').classList.remove('hidden');
}
</script>
@endsection
