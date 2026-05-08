@extends('layouts.app')
@section('title', __('ui.prospects.show_title'))
@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">{{ $prospect->company_name }}</h2>
                <p class="text-gray-500">{{ __('ui.prospects.show_contact', ['name' => $prospect->contact_name]) }}</p>
            </div>
            <span class="badge {{ $prospect->status === 'won' ? 'badge-green' : ($prospect->status === 'lost' ? 'badge-red' : 'badge-amber') }}">
                {{ __("domain.prospect.statuses.{$prospect->status}") }}
            </span>
        </div>
        <div class="grid grid-cols-2 gap-4 mb-6 p-4 bg-gray-50 rounded">
            <div>
                <p class="text-sm text-gray-500">{{ __('ui.prospects.show_email') }}</p>
                <p class="font-medium">{{ $prospect->email }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">{{ __('ui.prospects.show_phone') }}</p>
                <p class="font-medium">{{ $prospect->phone ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">{{ __('ui.prospects.show_created_by') }}</p>
                <p class="font-medium">{{ $prospect->createdBy->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">{{ __('ui.prospects.show_created_at') }}</p>
                <p class="font-medium">{{ $prospect->created_at->format('d/m/Y') }}</p>
            </div>
        </div>
        @if($prospect->notes)
        <div class="mb-6">
            <h3 class="text-sm font-medium text-gray-700 mb-1">{{ __('ui.prospects.show_notes') }}</h3>
            <p class="text-gray-600">{{ $prospect->notes }}</p>
        </div>
        @endif
        <div class="flex justify-end space-x-3">
            <a href="{{ route('prospects.index') }}" class="px-4 py-2 border rounded text-gray-700 hover:bg-gray-50">{{ __('ui.actions.back') }}</a>
            <a href="{{ route('prospects.edit', $prospect) }}" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">{{ __('ui.actions.edit') }}</a>
        </div>
    </div>
</div>
@endsection
