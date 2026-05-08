@extends('layouts.app')
@section('title', __('ui.admin_roles.title'))
@section('content')
<div class="fade-in">
    <div class="page-header">
        <div>
            <h2 class="page-title">{{ __('ui.admin_roles.heading') }}</h2>
            <p class="page-subtitle">{{ __('ui.admin_roles.description') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($roles as $role)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 card-hover">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold text-sm
                        @switch($role->name) @case('admin') bg-red-500 @break @case('vendor') bg-blue-500 @break @case('commercial_manager') bg-amber-500 @break @case('administrative') bg-emerald-500 @break @default bg-gray-500 @endswitch">
                        {{ strtoupper(substr($role->name, 0, 1)) }}
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</h3>
                        <p class="text-xs text-gray-400">{{ __('ui.admin_roles.permissions_assigned', ['count' => $role->permissions->count()]) }}</p>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-1.5 mb-4 max-h-24 overflow-y-auto">
                @foreach($role->permissions as $p)
                <span class="badge badge-blue">
                    {{ $p->name }}
                </span>
                @endforeach
            </div>
            <div class="pt-3 border-t border-gray-100 flex justify-end">
                <a href="{{ route('admin.roles.edit', $role) }}" class="inline-flex items-center space-x-1 text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>{{ __('ui.admin_roles.manage_permissions') }}</span>
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
