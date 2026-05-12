@extends('layouts.app')
@section('title', __('ui.prospects.title'))
@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">{{ __('ui.prospects.title') }}</h2>
        <p class="page-subtitle">{{ __('ui.prospects.description') }}</p>
    </div>
    @can('prospects.create')
    <a href="{{ route('prospects.create') }}" class="inline-flex items-center space-x-2 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white px-5 py-2 rounded-xl hover:from-indigo-700 hover:to-indigo-800 shadow-lg shadow-indigo-200 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>{{ __('ui.prospects.new') }}</span>
    </a>
    @endcan
</div>
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-100">
                <th class="px-4 sm:px-5 py-3 sm:py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ __('ui.prospects.columns.company_contact') }}</th>
                <th class="px-4 sm:px-5 py-3 sm:py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">{{ __('ui.prospects.columns.email_phone') }}</th>
                <th class="px-4 sm:px-5 py-3 sm:py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('ui.prospects.columns.status') }}</th>
                <th class="px-4 sm:px-5 py-3 sm:py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">{{ __('ui.prospects.columns.seller') }}</th>
                <th class="px-4 sm:px-5 py-3 sm:py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ __('ui.prospects.columns.actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse ($prospects as $p)
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-4 sm:px-5 py-3 sm:py-4 whitespace-nowrap">
                    <p class="font-medium text-gray-900">{{ $p->company_name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $p->contact_name }}</p>
                </td>
                <td class="px-4 sm:px-5 py-3 sm:py-4 hidden sm:table-cell">
                    <p class="text-gray-700">{{ $p->email }}</p>
                    @if($p->phone)<p class="text-xs text-gray-400 mt-0.5">{{ $p->phone }}</p>@endif
                </td>
                <td class="px-4 sm:px-5 py-3 sm:py-4 text-center">
                    <span class="badge
                        @switch($p->status)
                            @case('new') badge-gray @break
                            @case('contacted') badge-blue @break
                            @case('negotiation') badge-amber @break
                            @case('won') badge-green @break
                            @case('lost') badge-red @break
                        @endswitch">
                        <span class="badge-dot
                            @switch($p->status)
                                @case('new') bg-gray-400 @break
                                @case('contacted') bg-blue-500 @break
                                @case('negotiation') bg-yellow-500 @break
                                @case('won') bg-green-500 @break
                                @case('lost') bg-red-500 @break
                            @endswitch">
                        </span>
                        <span>{{ __("domain.prospect.statuses.{$p->status}") }}</span>
                    </span>
                </td>
                <td class="px-4 sm:px-5 py-3 sm:py-4 text-sm text-gray-600 hidden sm:table-cell">{{ $p->createdBy->name ?? 'N/A' }}</td>
                <td class="px-4 sm:px-5 py-3 sm:py-4 text-right space-x-2 whitespace-nowrap">
                    <a href="{{ route('prospects.show', $p) }}" class="inline-flex items-center space-x-1 text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>{{ __('ui.actions.view') }}</span>
                    </a>
                    @can('prospects.update')
                    <a href="{{ route('prospects.edit', $p) }}" class="inline-flex items-center space-x-1 text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span>{{ __('ui.actions.edit') }}</span>
                    </a>
                    @endcan
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-4 sm:px-5 py-16 text-center">@include('components.empty-state', ['title' => __('ui.prospects.empty_title'), 'description' => __('ui.prospects.empty_desc'), 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'])</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="px-4 sm:px-5 py-3 sm:py-4 border-t border-gray-100">{{ $prospects->links() }}</div>
</div>
@endsection
