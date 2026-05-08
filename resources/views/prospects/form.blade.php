@extends('layouts.app')
@section('title', isset($prospect) ? __('ui.prospects.form_title_edit') : __('ui.prospects.form_title_create'))
@section('content')
<div class="max-w-2xl mx-auto fade-in">
    <div class="flex items-center space-x-4 mb-8">
        <div class="w-12 h-12 bg-green-100 rounded-2xl flex items-center justify-center shadow-sm">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ isset($prospect) ? __('ui.prospects.form_title_edit') : __('ui.prospects.form_title_create') }}</h2>
            <p class="text-sm text-gray-500">{{ isset($prospect) ? __('ui.prospects.form_description_edit') : __('ui.prospects.form_description_create') }}</p>
        </div>
    </div>

    <form action="{{ isset($prospect) ? route('prospects.update', $prospect) : route('prospects.store') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 space-y-6" novalidate>
        @csrf
        @if(isset($prospect)) @method('PUT') @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="form-label">{{ __('ui.prospects.form_company_label') }} <span class="required">*</span></label>
                <input type="text" name="company_name" value="{{ old('company_name', $prospect->company_name ?? '') }}"
                    class="form-input block w-full px-4 py-3 rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 @error('company_name') error @enderror"
                    placeholder="{{ __('ui.prospects.form_company_placeholder') }}">
                @error('company_name') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">{{ __('ui.prospects.form_contact_label') }} <span class="required">*</span></label>
                <input type="text" name="contact_name" value="{{ old('contact_name', $prospect->contact_name ?? '') }}"
                    class="form-input block w-full px-4 py-3 rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 @error('contact_name') error @enderror"
                    placeholder="{{ __('ui.prospects.form_contact_placeholder') }}">
                @error('contact_name') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="form-label">{{ __('ui.prospects.form_email_label') }} <span class="required">*</span></label>
                <input type="email" name="email" value="{{ old('email', $prospect->email ?? '') }}"
                    class="form-input block w-full px-4 py-3 rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 @error('email') error @enderror"
                    placeholder="{{ __('ui.prospects.form_email_placeholder') }}">
                @error('email') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">{{ __('ui.prospects.form_phone_label') }}</label>
                <input type="text" name="phone" value="{{ old('phone', $prospect->phone ?? '') }}"
                    class="form-input block w-full px-4 py-3 rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 @error('phone') error @enderror"
                    placeholder="{{ __('ui.prospects.form_phone_placeholder') }}">
                @error('phone') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        @if(isset($prospect))
        <div>
            <label class="form-label">{{ __('ui.prospects.form_status_label') }}</label>
            <select name="status" class="select-custom block w-full px-4 py-3 rounded-xl border border-gray-300 shadow-sm">
                <option value="new" {{ $prospect->status === 'new' ? 'selected' : '' }}>{{ __('domain.prospect.statuses.new') }}</option>
                <option value="contacted" {{ $prospect->status === 'contacted' ? 'selected' : '' }}>{{ __('domain.prospect.statuses.contacted') }}</option>
                <option value="negotiation" {{ $prospect->status === 'negotiation' ? 'selected' : '' }}>{{ __('domain.prospect.statuses.negotiation') }}</option>
                <option value="won" {{ $prospect->status === 'won' ? 'selected' : '' }}>{{ __('domain.prospect.statuses.won') }}</option>
                <option value="lost" {{ $prospect->status === 'lost' ? 'selected' : '' }}>{{ __('domain.prospect.statuses.lost') }}</option>
            </select>
        </div>
        @endif

        <div>
            <label class="form-label">{{ __('ui.prospects.form_notes_label') }}</label>
            <textarea name="notes" rows="3"
                class="form-input block w-full px-4 py-3 rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 @error('notes') error @enderror"
                placeholder="{{ __('ui.prospects.form_notes_placeholder') }}">{{ old('notes', $prospect->notes ?? '') }}</textarea>
            @error('notes') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
            <a href="{{ route('prospects.index') }}" class="btn px-5 py-2.5 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 font-medium">{{ __('ui.actions.cancel') }}</a>
            <button type="submit" class="btn px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-xl hover:from-indigo-700 hover:to-indigo-800 shadow-lg shadow-indigo-200 font-medium">
                {{ isset($prospect) ? __('ui.actions.update') : __('ui.actions.save') }}
            </button>
        </div>
    </form>
</div>
@endsection
