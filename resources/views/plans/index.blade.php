@extends('layouts.app')
@section('title', __('ui.plans.title'))
@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">{{ __('ui.plans.title') }}</h2>
        <p class="page-subtitle">{{ __('ui.plans.description') }}</p>
    </div>
    @can('plans.create')
    <a href="{{ route('plans.create') }}" class="inline-flex items-center space-x-2 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white px-5 py-2 rounded-xl hover:from-indigo-700 hover:to-indigo-800 shadow-lg shadow-indigo-200 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>{{ __('ui.plans.new') }}</span>
    </a>
    @endcan
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    @forelse ($plans as $plan)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 card-hover">
        <div class="flex justify-between items-start">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-sm">{{ substr($plan->name, 0, 2) }}</div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $plan->name }}</h3>
                    @if($plan->description)
                    <p class="text-sm text-gray-500">{{ $plan->description }}</p>
                    @endif
                </div>
            </div>
            @can('plans.deactivate')
            <form action="{{ $plan->is_active ? route('plans.destroy', $plan) : route('plans.activate', $plan) }}" method="POST" onsubmit="{{ $plan->is_active ? "return confirm('".__('ui.plans.confirm_deactivate')."')" : '' }}">
                @csrf
                @if($plan->is_active) @method('DELETE') @endif
                <button type="submit" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $plan->is_active ? 'bg-indigo-600' : 'bg-gray-300' }}" role="switch" aria-checked="{{ $plan->is_active ? 'true' : 'false' }}" title="{{ $plan->is_active ? __('ui.actions.deactivate') : __('ui.actions.activate') }}">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition duration-200 {{ $plan->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                </button>
            </form>
            @else
            <span class="inline-flex items-center space-x-1.5 text-sm {{ $plan->is_active ? 'text-green-600' : 'text-red-500' }}">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4"/></svg>
                <span>{{ $plan->is_active ? __('ui.common.active') : __('ui.common.inactive') }}</span>
            </span>
            @endcan
        </div>
        <div class="mt-4 space-y-2">
            @foreach ($plan->services as $svc)
            <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-lg text-sm">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-gray-700">{{ $svc->microservice->name }}</span>
                </div>
                <span class="font-mono text-sm font-medium text-gray-900">${{ number_format($svc->custom_price ?? $svc->microservice->base_cost, 2) }}</span>
            </div>
            @endforeach
        </div>

        @php
            $allActivities = $plan->services->flatMap(fn ($svc) => $svc->microservice?->activities ?? collect());
            $paLookup = $plan->planActivities->keyBy('activity_id');
        @endphp

        <div class="mt-4 flex justify-between items-center pt-3 border-t border-gray-100">
            <button type="button" onclick="document.getElementById('activities-modal-{{ $plan->id }}').classList.remove('hidden')"
                class="inline-flex items-center space-x-1 text-xs text-gray-500 hover:text-indigo-600 font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <span>{{ __('ui.plans.view_activities') }}</span>
                <span class="text-gray-400">({{ $allActivities->count() }})</span>
            </button>
            <div class="flex space-x-2">
                @can('plans.update')
                <a href="{{ route('plans.edit', $plan) }}" class="inline-flex items-center space-x-1 text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>{{ __('ui.plans.edit') }}</span>
                </a>
                @endcan
            </div>
        </div>
    </div>

    {{-- MODAL ACTIVIDADES --}}
    <div id="activities-modal-{{ $plan->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm" onclick="if(event.target===this)this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg mx-4 max-h-[80vh] overflow-y-auto fade-in">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-gray-900">{{ $plan->name }} — {{ __('ui.plans.activities_title') }}</h3>
                <button type="button" onclick="this.closest('#activities-modal-{{ $plan->id }}').classList.add('hidden')" class="p-1 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="space-y-2">
                @php
                    $grouped = $allActivities->filter(fn($act) => ($paLookup->get($act->id) ? $paLookup->get($act->id)->is_enabled : true))
                        ->groupBy(fn($act) => $act->microservice->name ?? 'Otros');
                @endphp
                @forelse($grouped as $msName => $activities)
                <div class="mb-3">
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-1.5 px-1">{{ $msName }}</p>
                    <div class="space-y-1.5">
                        @foreach($activities as $act)
                        <div class="flex items-center justify-between px-3 py-2 rounded-lg border text-sm bg-emerald-50/50 border-emerald-200">
                            <div class="flex items-center space-x-2 min-w-0">
                                <span class="w-2 h-2 rounded-full flex-shrink-0 bg-emerald-500"></span>
                                <span class="truncate text-gray-800 font-medium">{{ $act->name }}</span>
                                @if($act->is_essential)
                                <span class="badge badge-purple text-[10px] px-1.5 py-0.5 leading-none flex-shrink-0">
                                    <svg class="w-2.5 h-2.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    {{ __('ui.activities.essential') }}
                                </span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-2 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <p class="text-sm">{{ __('ui.plans.no_activities') }}</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-2">@include('components.empty-state', ['title' => __('ui.plans.empty_title'), 'description' => __('ui.plans.empty_desc')])</div>
    @endforelse
</div>
<div class="mt-6">{{ $plans->links() }}</div>
@endsection
