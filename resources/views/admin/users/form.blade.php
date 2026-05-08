@extends('layouts.app')
@section('title', isset($user) ? __('ui.admin_users.edit_title') : __('ui.admin_users.create_title'))
@section('content')
<div class="max-w-2xl mx-auto fade-in">
    <div class="flex items-center space-x-4 mb-6">
        <div class="w-12 h-12 bg-indigo-100 rounded-2xl flex items-center justify-center shadow-sm">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ isset($user) ? __('ui.admin_users.edit_title') : __('ui.admin_users.create_title') }}</h2>
            <p class="text-sm text-gray-500">{{ isset($user) ? __('ui.admin_users.edit_description') : __('ui.admin_users.create_description') }}</p>
        </div>
    </div>

    <form action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 space-y-6" novalidate>
        @csrf
        @if(isset($user)) @method('PUT') @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="form-label">{{ __('ui.admin_users.name_label') }} <span class="required">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}"
                    class="form-input block w-full px-4 py-3 rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 @error('name') error @enderror"
                    placeholder="{{ __('ui.admin_users.name_placeholder') }}" required>
                @error('name') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">{{ __('ui.admin_users.email_label') }} <span class="required">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}"
                    class="form-input block w-full px-4 py-3 rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 @error('email') error @enderror"
                    placeholder="{{ __('ui.admin_users.email_placeholder') }}" required>
                @error('email') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="form-label">{{ __('ui.admin_users.password_label') }} {{ isset($user) ? __('ui.admin_users.password_keep') : '' }} <span class="required">{{ isset($user) ? '' : '*' }}</span></label>
                <input type="password" name="password"
                    class="form-input block w-full px-4 py-3 rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 @error('password') error @enderror"
                    placeholder="••••••••" {{ isset($user) ? '' : 'required' }}>
                @error('password') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">{{ __('ui.admin_users.role_label') }} <span class="required">*</span></label>
                <select name="role" class="select-custom block w-full px-4 py-3 rounded-xl border border-gray-300 shadow-sm" required>
                    @foreach($roles as $r)
                    <option value="{{ $r->name }}" {{ old('role', isset($user) ? $user->roles->first()->name ?? '' : '') === $r->name ? 'selected' : '' }}>{{ ucfirst($r->name) }}</option>
                    @endforeach
                </select>
                @error('role') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        @if(isset($user))
        @php $hasInteractions = \Spatie\Activitylog\Models\Activity::where('causer_id', $user->id)->exists(); @endphp
        @if($hasInteractions)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start space-x-3">
            <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
            <p class="text-sm text-amber-800">{{ __('ui.admin_users.has_interactions') }}</p>
        </div>
        @endif
        @endif

        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
            <a href="{{ route('admin.users') }}" class="btn px-5 py-2.5 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 font-medium">{{ __('ui.actions.cancel') }}</a>
            <button type="submit" class="btn px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-xl hover:from-indigo-700 hover:to-indigo-800 shadow-lg shadow-indigo-200 font-medium">
                {{ isset($user) ? __('ui.actions.update') : __('ui.actions.create') }} {{ __('ui.admin_users.heading') }}
            </button>
        </div>
    </form>
</div>
@endsection
