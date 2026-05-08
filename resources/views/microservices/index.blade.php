@extends('layouts.app')
@section('title', __('ui.microservices.title'))
@section('content')
<div class="flex justify-between items-center mb-6 fade-in flex-col sm:flex-row gap-3">
    <div>
        <h2 class="text-xl font-bold text-gray-900">{{ __('ui.microservices.title') }}</h2>
        <p class="text-sm text-gray-500 mt-1">{{ __('ui.microservices.description') }}</p>
    </div>
    @can('microservices.create')
    <a href="{{ route('microservices.create') }}" class="btn inline-flex items-center space-x-2 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white px-5 py-2.5 rounded-xl hover:from-indigo-700 hover:to-indigo-800 shadow-lg shadow-indigo-200 transition self-start sm:self-auto">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>{{ __('ui.microservices.new') }}</span>
    </a>
    @endcan
</div>
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="px-4 sm:px-5 py-3 sm:py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ __('ui.microservices.columns.name') }}</th>
                    <th class="px-4 sm:px-5 py-3 sm:py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">{{ __('ui.microservices.columns.type') }}</th>
                    <th class="px-4 sm:px-5 py-3 sm:py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ __('ui.microservices.columns.base_cost') }}</th>
                    <th class="px-4 sm:px-5 py-3 sm:py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ __('ui.microservices.columns.status') }}</th>
                    <th class="px-4 sm:px-5 py-3 sm:py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ __('ui.microservices.columns.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach ($microservices as $ms)
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-4 sm:px-5 py-3 sm:py-4 whitespace-nowrap">
                        <p class="font-medium text-gray-900">{{ $ms->name }}</p>
                        @if($ms->description)
                        <p class="text-xs text-gray-400 mt-0.5 truncate max-w-[200px]">{{ $ms->description }}</p>
                        @endif
                    </td>
                    <td class="px-4 sm:px-5 py-3 sm:py-4 hidden sm:table-cell">
                        <span class="inline-flex items-center space-x-1 text-sm {{ $ms->type === 'recurring' ? 'text-blue-600' : 'text-purple-600' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $ms->type === 'recurring' ? 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15' : 'M13 10V3L4 14h7v7l9-11h-7z' }}"/></svg>
                            <span>{{ $ms->type === 'recurring' ? __('ui.recurring') : __('ui.one_time') }}</span>
                        </span>
                    </td>
                    <td class="px-4 sm:px-5 py-3 sm:py-4 text-right font-mono text-sm font-medium whitespace-nowrap">${{ number_format($ms->base_cost, 2) }}</td>
                    <td class="px-4 sm:px-5 py-3 sm:py-4 text-center whitespace-nowrap">
                        <span class="badge {{ $ms->is_active ? 'badge-green' : 'badge-red' }}">
                            <span class="badge-dot {{ $ms->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                            <span>{{ $ms->is_active ? __('ui.common.active') : __('ui.common.inactive') }}</span>
                        </span>
                    </td>
                    <td class="px-4 sm:px-5 py-3 sm:py-4 text-right whitespace-nowrap">
                        @can('microservices.update')
                        <a href="{{ route('microservices.edit', $ms) }}" class="inline-flex items-center space-x-1 text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>{{ __('ui.actions.edit') }}</span>
                        </a>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="px-4 sm:px-5 py-3 sm:py-4 border-t border-gray-100">{{ $microservices->links() }}</div>
</div>
@endsection
