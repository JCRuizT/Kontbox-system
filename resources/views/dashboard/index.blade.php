@extends('layouts.app')
@section('title', __('ui.dashboard'))
@section('content')
<div class="fade-in space-y-6">
    {{-- STATS CARDS — responsive grid --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
        @can('microservices.read')
        <div class="bg-white rounded-xl sm:rounded-2xl shadow-sm border border-gray-100 p-3 sm:p-5">
            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-indigo-100 rounded-lg sm:rounded-xl flex items-center justify-center mb-2 sm:mb-3">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ \App\Src\Infrastructure\Persistence\Models\Microservice::count() }}</p>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5 sm:mt-1">{{ __('ui.dashboard_page.stats.microservices') }}</p>
        </div>
        @endcan
        @can('plans.read')
        <div class="bg-white rounded-xl sm:rounded-2xl shadow-sm border border-gray-100 p-3 sm:p-5">
            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-blue-100 rounded-lg sm:rounded-xl flex items-center justify-center mb-2 sm:mb-3">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ \App\Src\Infrastructure\Persistence\Models\Plan::count() }}</p>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5 sm:mt-1">{{ __('ui.dashboard_page.stats.plans') }}</p>
        </div>
        @endcan
        @can('activities.read')
        <div class="bg-white rounded-xl sm:rounded-2xl shadow-sm border border-gray-100 p-3 sm:p-5">
            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-cyan-100 rounded-lg sm:rounded-xl flex items-center justify-center mb-2 sm:mb-3">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            </div>
            <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ \App\Src\Infrastructure\Persistence\Models\Activity::count() }}</p>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5 sm:mt-1">{{ __('ui.dashboard_page.stats.activities') }}</p>
        </div>
        @endcan
        @can('prospects.read')
        <div class="bg-white rounded-xl sm:rounded-2xl shadow-sm border border-gray-100 p-3 sm:p-5">
            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-green-100 rounded-lg sm:rounded-xl flex items-center justify-center mb-2 sm:mb-3">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ \App\Src\Infrastructure\Persistence\Models\Prospect::count() }}</p>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5 sm:mt-1">{{ __('ui.dashboard_page.stats.prospects') }}</p>
        </div>
        @endcan
        @can('quotations.read')
        <div class="bg-white rounded-xl sm:rounded-2xl shadow-sm border border-gray-100 p-3 sm:p-5">
            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-amber-100 rounded-lg sm:rounded-xl flex items-center justify-center mb-2 sm:mb-3">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ \App\Src\Infrastructure\Persistence\Models\Quotation::count() }}</p>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5 sm:mt-1">{{ __('ui.dashboard_page.stats.quotations') }}</p>
        </div>
        @endcan
        @can('contracts.read')
        <div class="bg-white rounded-xl sm:rounded-2xl shadow-sm border border-gray-100 p-3 sm:p-5">
            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-emerald-100 rounded-lg sm:rounded-xl flex items-center justify-center mb-2 sm:mb-3">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ \App\Src\Infrastructure\Persistence\Models\Contract::count() }}</p>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5 sm:mt-1">{{ __('ui.dashboard_page.stats.contracts') }}</p>
        </div>
        @endcan
        @can('invoices.read')
        <div class="bg-white rounded-xl sm:rounded-2xl shadow-sm border border-gray-100 p-3 sm:p-5">
            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-teal-100 rounded-lg sm:rounded-xl flex items-center justify-center mb-2 sm:mb-3">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
            </div>
            <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ \App\Src\Infrastructure\Persistence\Models\Invoice::count() }}</p>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5 sm:mt-1">{{ __('ui.dashboard_page.stats.invoices') }}</p>
        </div>
        @endcan
        @can('amendments.read')
        <div class="bg-white rounded-xl sm:rounded-2xl shadow-sm border border-gray-100 p-3 sm:p-5">
            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-purple-100 rounded-lg sm:rounded-xl flex items-center justify-center mb-2 sm:mb-3">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </div>
            <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ \App\Src\Infrastructure\Persistence\Models\ContractAmendment::count() }}</p>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5 sm:mt-1">{{ __('ui.dashboard_page.stats.amendments') }}</p>
        </div>
        @endcan
    </div>

    @can('audit.read')
    <div class="bg-white rounded-xl sm:rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-700 flex items-center space-x-2">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ __('ui.dashboard_page.recent_activity') }}</span>
            </h3>
        </div>
        <div class="overflow-x-auto -mx-4 sm:mx-0">
            <div class="inline-block min-w-full align-middle px-4 sm:px-0">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="px-2 sm:px-3 py-2 text-left text-xs text-gray-400 uppercase whitespace-nowrap">{{ __('ui.dashboard_page.columns.date') }}</th>
                            <th class="px-2 sm:px-3 py-2 text-left text-xs text-gray-400 uppercase whitespace-nowrap">{{ __('ui.dashboard_page.columns.description') }}</th>
                            <th class="px-2 sm:px-3 py-2 text-left text-xs text-gray-400 uppercase whitespace-nowrap hidden sm:table-cell">{{ __('ui.dashboard_page.columns.user') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @php $logEntries = \Spatie\Activitylog\Models\Activity::with('causer')->latest()->limit(10)->get(); @endphp
                        @forelse($logEntries as $log)
                        <tr>
                            <td class="px-2 sm:px-3 py-2 text-xs text-gray-500 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-2 sm:px-3 py-2">
                                <span class="inline-flex items-center space-x-1.5 px-2 py-0.5 rounded-full text-xs font-medium
                                    @if(str_contains($log->description, 'Creó')) bg-green-50 text-green-700
                                    @elseif(str_contains($log->description, 'Actualizó')) bg-blue-50 text-blue-700
                                    @elseif(str_contains($log->description, 'Desactivó')) bg-red-50 text-red-700
                                    @elseif(str_contains($log->description, 'Cambió estado')) bg-amber-50 text-amber-700
                                    @else bg-gray-100 text-gray-700 @endif">
                                    {{ Str::limit($log->description, 40) }}
                                </span>
                            </td>
                            <td class="px-2 sm:px-3 py-2 text-xs text-gray-500 hidden sm:table-cell">{{ $log->causer->name ?? __('ui.reviews.system') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 sm:px-5 py-16 text-center">@include('components.empty-state', ['title' => __('ui.dashboard_page.no_activity'), 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'])</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endcan
</div>
@endsection
