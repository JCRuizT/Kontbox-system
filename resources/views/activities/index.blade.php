@extends('layouts.app')
@section('title', __('ui.activities.title'))
@section('content')
<div class="page-header fade-in">
    <div>
        <h2 class="page-title">{{ __('ui.activities.title') }}</h2>
        <p class="page-subtitle">{{ __('ui.activities.description') }}</p>
    </div>
    @can('activities.create')
    <a href="{{ route('activities.create') }}" class="btn inline-flex items-center space-x-2 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white px-5 py-2.5 rounded-xl hover:from-indigo-700 hover:to-indigo-800 shadow-lg shadow-indigo-200 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>{{ __('ui.activities.new') }}</span>
    </a>
    @endcan
</div>
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-100">
                <th class="px-4 sm:px-5 py-3 sm:py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ __('ui.activities.columns.name') }}</th>
                <th class="px-4 sm:px-5 py-3 sm:py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">{{ __('ui.activities.columns.microservices') }}</th>
                <th class="px-4 sm:px-5 py-3 sm:py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">{{ __('ui.activities.columns.description') }}</th>
                <th class="px-4 sm:px-5 py-3 sm:py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('ui.activities.columns.status') }}</th>
                <th class="px-4 sm:px-5 py-3 sm:py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ __('ui.activities.columns.actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse ($activities as $act)
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-4 sm:px-5 py-3 sm:py-4 font-medium text-gray-900 whitespace-nowrap">{{ $act->name }}</td>
                <td class="px-4 sm:px-5 py-3 sm:py-4 hidden sm:table-cell">
                    <span class="text-sm text-gray-700">
                        @if($act->microservice)
                        <span class="badge badge-cyan">{{ $act->microservice->name }}</span>
                        @else
                        <span class="text-xs text-gray-400 italic">{{ __('ui.activities_form.no_microservice') }}</span>
                        @endif
                    </span>
                </td>
                <td class="px-4 sm:px-5 py-3 sm:py-4 text-gray-500 max-w-xs truncate hidden sm:table-cell">{{ $act->description ?? '-' }}</td>
                <td class="px-4 sm:px-5 py-3 sm:py-4 text-center">
                    <span class="badge {{ $act->is_active ? 'badge-green' : 'badge-red' }}">
                        <span class="badge-dot {{ $act->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                        <span>{{ $act->is_active ? __('ui.common.active') : __('ui.common.inactive') }}</span>
                    </span>
                </td>
                <td class="px-4 sm:px-5 py-3 sm:py-4 text-right whitespace-nowrap">
                    @can('activities.update')
                    <a href="{{ route('activities.edit', $act) }}" class="inline-flex items-center space-x-1 text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span>{{ __('ui.actions.edit') }}</span>
                    </a>
                    @endcan
                </td>
            </tr>
            @empty
            <tr><td colspan="5">@include('components.empty-state', ['title' => __('ui.activities.no_activities'), 'description' => __('ui.activities.form_description_create'), 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'])</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="px-4 sm:px-5 py-3 sm:py-4 border-t border-gray-100">{{ $activities->links() }}</div>
</div>
@endsection
