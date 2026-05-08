@extends('layouts.app')
@section('title', __('ui.reviews.title'))
@section('content')
<div class="fade-in">
    <div class="flex items-center space-x-4 mb-8">
        <div class="w-12 h-12 bg-yellow-100 rounded-2xl flex items-center justify-center shadow-sm">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ __('ui.reviews.title') }}</h2>
            <p class="text-sm text-gray-500">{{ __('ui.reviews.description') }}</p>
        </div>
    </div>

    @if($underReview->isEmpty())
        @include('components.empty-state', ['title' => __('ui.reviews.no_pending'), 'description' => __('ui.reviews.no_pending_desc'), 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'])
    @else
    <div class="space-y-4">
        @foreach($underReview as $q)
        @php
            $sender = $q->createdBy;
            $senderRole = $sender ? $sender->getRoleNames()->implode(', ') : __('ui.reviews.system');
        @endphp
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 card-hover">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="font-mono text-xs font-bold text-indigo-600">{{ $q->quote_number }}</span>
                        <span class="text-xs text-gray-400">v{{ $q->version }}</span>
                        <span class="badge badge-amber">
                            <span class="badge-dot bg-yellow-500"></span>
                            <span>{{ __('domain.quotation.statuses.under_review') }}</span>
                        </span>
                    </div>
                    <p class="font-semibold text-gray-900">{{ $q->prospect->company_name ?? 'N/A' }}</p>
                    <p class="text-sm text-gray-500">{{ $q->prospect->contact_name ?? '' }}</p>
                    <div class="flex items-center space-x-2 mt-1">
                        <div class="w-6 h-6 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white text-[8px] font-bold flex-shrink-0">
                            {{ $sender ? strtoupper(substr($sender->name, 0, 2)) : 'S' }}
                        </div>
                        <p class="text-xs text-gray-400">
                            <span class="font-medium text-gray-600">{{ $sender->name ?? __('ui.reviews.system') }}</span>
                            <span class="text-gray-400">· {{ ucfirst(str_replace('_', ' ', $senderRole)) }}</span>
                            <span class="text-gray-400">· {{ $q->created_at->format('d/m/Y') }}</span>
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-lg font-bold text-indigo-600">${{ number_format($q->total, 2) }}</p>
                    <p class="text-xs text-gray-400">{{ $q->items->count() }} {{ __('ui.services') }}</p>
                </div>
            </div>
            <div class="mt-4 flex items-center space-x-3 pt-4 border-t border-gray-100 flex-wrap gap-3">
                <a href="{{ route('quotations.show', $q) }}" class="btn text-sm text-indigo-600 hover:text-indigo-800 font-medium">{{ __('ui.reviews.view_detail') }}</a>

                <div class="flex items-center space-x-2 ml-auto">
                    <form action="{{ route('quotations.approve', $q) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn px-5 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 shadow-lg shadow-green-200 font-medium text-sm">
                            {{ __('ui.reviews.approve') }}
                        </button>
                    </form>
                    <form action="{{ route('quotations.reject', $q) }}" method="POST" class="flex items-end space-x-2" novalidate>
                        @csrf
                        <input type="text" name="rejection_reason" placeholder="{{ __('ui.reviews.reject_placeholder') }}"
                            class="rounded-xl border-gray-300 text-sm w-36 sm:w-44 focus:border-red-400 focus:ring-red-200" required minlength="10">
                        <button type="submit" class="btn px-5 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 shadow-lg shadow-red-200 font-medium text-sm">
                            {{ __('ui.reviews.reject') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-6">{{ $underReview->links() }}</div>
    @endif
</div>
@endsection
