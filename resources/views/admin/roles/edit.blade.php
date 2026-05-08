@extends('layouts.app')
@section('title', __('ui.admin_roles.edit_title', ['name' => $role->name]))
@section('content')
<div class="max-w-6xl mx-auto fade-in">
    <div class="flex items-center space-x-4 mb-8">
        <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-violet-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-200">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        </div>
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ __('ui.admin_roles.permissions_heading') }} <span class="text-indigo-600">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</span></h2>
            <p class="text-sm text-gray-500">{{ __('ui.admin_roles.edit_description') }}</p>
        </div>
    </div>

    <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="space-y-6" novalidate>
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($groupedPermissions as $module => $perms)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden card-hover">
                {{-- MODULE HEADER --}}
                <div class="px-5 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">{{ $module }}</h3>
                    </div>
                    <span class="inline-flex items-center space-x-1.5 text-xs text-gray-400">
                        <span class="perm-count-{{ $loop->index }} font-semibold text-indigo-600">{{ count(array_intersect(array_column($perms, 'name'), $rolePermissions)) }}</span>
                        <span>/{{ count($perms) }}</span>
                        <button type="button" onclick="toggleModule(this)" class="ml-2 text-xs font-medium text-indigo-500 hover:text-indigo-700 border border-indigo-200 rounded-md px-2 py-0.5 hover:bg-indigo-50 transition">
                            {{ __('ui.admin_roles.select_all') }}
                        </button>
                    </span>
                </div>

                {{-- PERMISSION GRID --}}
                <div class="p-4">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach($perms as $pIndex => $perm)
                        @php
                            $isChecked = in_array($perm['name'], $rolePermissions);
                            $inputId = "perm-{$module}-{$pIndex}";
                        @endphp
                        <label for="{{ $inputId }}"
                            class="flex items-center space-x-2.5 px-3 py-2.5 rounded-xl border-2 cursor-pointer transition-all duration-150
                            {{ $isChecked ? 'bg-indigo-50 border-indigo-300 shadow-sm' : 'bg-white border-gray-200 hover:border-indigo-200 hover:bg-gray-50' }}">
                            <input type="checkbox" name="permissions[]" value="{{ $perm['name'] }}"
                                id="{{ $inputId }}"
                                class="shrink-0 w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                {{ $isChecked ? 'checked' : '' }}
                                onchange="updatePermUI(this)">
                            <span class="text-xs leading-tight {{ $isChecked ? 'text-indigo-700 font-medium' : 'text-gray-600' }}">{{ str_replace('_', ' ', $perm['action']) }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- FOOTER --}}
        <div class="sticky bottom-6 bg-white/80 backdrop-blur-md rounded-2xl shadow-lg border border-gray-100 p-4 flex items-center justify-between">
            <div class="flex items-center space-x-3 text-sm text-gray-500">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ __('ui.admin_roles.security_notice') }}</span>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.roles') }}" class="btn px-5 py-2.5 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 font-medium">{{ __('ui.actions.cancel') }}</a>
                <button type="submit" class="btn px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-xl hover:from-indigo-700 hover:to-indigo-800 shadow-lg shadow-indigo-200 font-medium">
                    {{ __('ui.admin_roles.save') }}
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function toggleModule(btn) {
    const section = btn.closest('[class*="bg-white rounded-2xl"]') || btn.closest('.card-hover');
    if (!section) return;
    const checkboxes = section.querySelectorAll('input[type="checkbox"]');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => {
        cb.checked = !allChecked;
        updatePermUI(cb);
    });
}

function updatePermUI(cb) {
    const label = cb.closest('label');
    const span = label ? label.querySelector('span:last-child') : null;
    if (cb.checked) {
        if (label) { label.classList.add('bg-indigo-50', 'border-indigo-300', 'shadow-sm'); label.classList.remove('bg-white', 'border-gray-200', 'hover:border-indigo-200', 'hover:bg-gray-50'); }
        if (span) { span.classList.add('text-indigo-700', 'font-medium'); span.classList.remove('text-gray-600'); }
    } else {
        if (label) { label.classList.remove('bg-indigo-50', 'border-indigo-300', 'shadow-sm'); label.classList.add('bg-white', 'border-gray-200', 'hover:border-indigo-200', 'hover:bg-gray-50'); }
        if (span) { span.classList.remove('text-indigo-700', 'font-medium'); span.classList.add('text-gray-600'); }
    }
    // Update count
    const header = label ? label.closest('[class*="bg-white rounded-2xl"]') || label.closest('.card-hover') : null;
    if (header) {
        const countEl = header.querySelector('[class*="perm-count-"]');
        if (countEl) {
            countEl.textContent = header.querySelectorAll('input[type="checkbox"]:checked').length;
        }
    }
}
</script>
@endsection
