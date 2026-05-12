@extends('layouts.app')
@section('title', __('ui.admin_roles.title'))
@section('content')
<div class="fade-in">
    <div class="page-header">
        <div>
            <h2 class="page-title">{{ __('ui.admin_roles.heading') }}</h2>
            <p class="page-subtitle">{{ __('ui.admin_roles.description') }}</p>
        </div>
        @can('admin.access')
        <a href="{{ route('admin.roles.create') }}" class="inline-flex items-center space-x-2 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white px-5 py-2.5 rounded-xl hover:from-indigo-700 hover:to-indigo-800 shadow-lg shadow-indigo-200 transition self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>{{ __('ui.admin_roles.create_role') }}</span>
        </a>
        @endcan
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($roles as $role)
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
            <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                <button type="button" onclick="document.getElementById('rename-role-{{ $role->id }}').classList.remove('hidden')"
                    class="inline-flex items-center space-x-1 text-xs text-gray-500 hover:text-indigo-600 font-medium transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>{{ __('ui.admin_roles.rename') }}</span>
                </button>
                <a href="{{ route('admin.roles.edit', $role) }}" class="inline-flex items-center space-x-1 text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>{{ __('ui.admin_roles.manage_permissions') }}</span>
                </a>
            </div>
        </div>

        {{-- MODAL RENOMBRAR --}}
        <div id="rename-role-{{ $role->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm" onclick="if(event.target===this)this.classList.add('hidden')">
            <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm mx-4 fade-in" onclick="event.stopPropagation()">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-gray-900">{{ __('ui.admin_roles.rename') }}: {{ ucfirst(str_replace('_', ' ', $role->name)) }}</h3>
                    <button type="button" onclick="this.closest('[id^=rename-role-]').classList.add('hidden')" class="p-1 rounded-lg hover:bg-gray-100 transition">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form action="{{ route('admin.roles.rename', $role) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('ui.admin_roles.name_label') }}</label>
                        <input type="text" name="name" value="{{ $role->name }}" required maxlength="255"
                            class="form-input block w-full px-4 py-3 rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500"
                            placeholder="{{ __('ui.admin_roles.name_placeholder') }}">
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="this.closest('[id^=rename-role-]').classList.add('hidden')" class="btn px-4 py-2 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 font-medium text-sm">{{ __('ui.actions.cancel') }}</button>
                        <button type="submit" class="btn px-4 py-2 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-xl hover:from-indigo-700 hover:to-indigo-800 font-medium text-sm">{{ __('ui.actions.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
        @empty
        @include('components.empty-state', ['title' => __('ui.admin_roles.empty_title'), 'description' => __('ui.admin_roles.empty_desc'), 'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'])
        @endforelse
    </div>
</div>
@endsection
