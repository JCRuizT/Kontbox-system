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
            <span class="badge {{ $plan->is_active ? 'badge-green' : 'badge-red' }}">
                <span class="badge-dot {{ $plan->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                <span>{{ $plan->is_active ? __('ui.common.active') : __('ui.common.inactive') }}</span>
            </span>
        </div>
        <div class="mt-4 space-y-2">
            @foreach ($plan->services as $svc)
            <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-lg text-sm">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-gray-700">{{ $svc->microservice->name }} <span class="text-gray-400">×{{ $svc->quantity }}</span></span>
                </div>
                <span class="font-mono text-sm font-medium text-gray-900">${{ number_format($svc->custom_price ?? $svc->microservice->base_cost, 2) }}</span>
            </div>
            @endforeach
        </div>
        <div class="mt-4 flex justify-end space-x-3 pt-3 border-t border-gray-100">
            @can('plans.update')
            <a href="{{ route('plans.edit', $plan) }}" class="inline-flex items-center space-x-1 text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>{{ __('ui.plans.edit') }}</span>
            </a>
            @endcan
        </div>
    </div>
    @empty
    <div class="col-span-2 text-center py-16 text-gray-400">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <p class="text-lg font-medium">{{ __('ui.plans.empty_title') }}</p>
        <p class="text-sm mt-1">{{ __('ui.plans.empty_desc') }}</p>
    </div>
    @endforelse
</div>
<div class="mt-6">{{ $plans->links() }}</div>
@endsection
