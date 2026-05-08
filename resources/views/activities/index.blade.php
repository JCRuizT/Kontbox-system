@extends('layouts.app')
@section('title', __('ui.activities.title'))
@section('content')
<div class="page-header fade-in">
    <div>
        <h2 class="page-title">{{ __('ui.activities.title') }}</h2>
        <p class="page-subtitle">{{ __('ui.activities.description') }}</p>
    </div>
    @can('activities.create')
    <a href="{{ route('activities.create') }}" class="btn inline-flex items-center space-x-2 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white px-5 py-2.5 rounded-xl hover:from-indigo-700 hover:to-indigo-800 shadow-lg shadow-indigo-200 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>{{ __('ui.activities.new') }}</span>
    </a>
    @endcan
</div>

<div class="space-y-3" id="activities-container">
    @forelse ($microservices as $ms)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden card-hover">
        {{-- HEADER (clickeable) --}}
        <button type="button" onclick="toggleActivities(this)"
            class="w-full px-4 sm:px-5 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100 flex items-center justify-between hover:bg-gray-100/50 transition text-left">
            <div class="flex items-center space-x-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                    {{ strtoupper(substr($ms->name, 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <h3 class="text-sm font-bold text-gray-900 truncate">{{ $ms->name }}</h3>
                    <p class="text-xs text-gray-400">${{ number_format($ms->base_cost, 2) }} · {{ $ms->type === 'recurring' ? __('ui.recurring') : __('ui.one_time') }} · <span class="font-medium text-indigo-500">{{ $ms->activities->count() }} {{ __('ui.activities.count_label', ['count' => $ms->activities->count()]) }}</span></p>
                </div>
            </div>
            <div class="flex items-center space-x-2 flex-shrink-0 ml-3">
                <span class="badge {{ $ms->is_active ? 'badge-green' : 'badge-red' }} hidden sm:inline-flex">
                    <span class="badge-dot {{ $ms->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    <span>{{ $ms->is_active ? __('ui.common.active') : __('ui.common.inactive') }}</span>
                </span>
                <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" data-chevron fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
        </button>

        {{-- ACTIVITIES BODY (collapsible) --}}
        <div class="activities-body divide-y divide-gray-50">
            @if($ms->activities->count() > 0)
                @foreach($ms->activities as $act)
                <div class="px-4 sm:px-5 py-3 flex items-center justify-between hover:bg-gray-50/50 transition">
                    <div class="flex items-center space-x-3 min-w-0">
                        @if($act->is_essential)
                        <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                        @else
                        <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                        @endif
                        <div class="min-w-0">
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-medium text-gray-900">{{ $act->name }}</span>
                                @if($act->is_essential)
                                <span class="badge badge-amber text-[10px] px-1.5 py-0.5">{{ __('ui.activities.essential') }}</span>
                                @endif
                                @if(!$act->is_active)
                                <span class="badge badge-red text-[10px] px-1.5 py-0.5">{{ __('ui.common.inactive') }}</span>
                                @endif
                            </div>
                            @if($act->description)
                            <p class="text-xs text-gray-400 mt-0.5 truncate max-w-md">{{ $act->description }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 flex-shrink-0 ml-3">
                        @can('activities.deactivate')
                        @if(!$act->is_essential)
                        <form action="{{ $act->is_active ? route('activities.destroy', $act) : route('activities.activate', $act) }}" method="POST" onsubmit="{{ $act->is_active ? "return confirm('".__('ui.activities.confirm_deactivate')."')" : '' }}" class="inline">
                            @csrf
                            @if($act->is_active) @method('DELETE') @endif
                            <button type="submit" class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $act->is_active ? 'bg-indigo-600' : 'bg-gray-300' }}" role="switch" aria-checked="{{ $act->is_active ? 'true' : 'false' }}" title="{{ $act->is_active ? __('ui.actions.deactivate') : __('ui.actions.activate') }}">
                                <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition duration-200 {{ $act->is_active ? 'translate-x-[18px]' : 'translate-x-[3px]' }}"></span>
                            </button>
                        </form>
                        @endif
                        @endcan

                        @can('activities.update')
                        <a href="{{ route('activities.edit', $act) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition" title="{{ __('ui.actions.edit') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        @endcan
                    </div>
                </div>
                @endforeach
            @else
                <div class="px-4 sm:px-5 py-6 text-center text-sm text-gray-400">
                    <svg class="w-10 h-10 mx-auto mb-2 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <p>{{ __('ui.activities.no_activities') }}</p>
                    @can('activities.create')
                    <a href="{{ route('activities.create', ['microservice_id' => $ms->id]) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium mt-1 inline-block">{{ __('ui.activities.new') }}</a>
                    @endcan
                </div>
            @endif
        </div>
    </div>
    @empty
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <p class="text-lg font-medium text-gray-500">{{ __('ui.activities.no_activities') }}</p>
        <p class="text-sm text-gray-400 mt-1">{{ __('ui.activities.form_description_create') }}</p>
    </div>
    @endforelse
</div>
@endsection

@push('scripts')
<script>
function toggleActivities(btn) {
    const body = btn.nextElementSibling;
    const chevron = btn.querySelector('[data-chevron]');

    if (body.classList.contains('hidden')) {
        body.classList.remove('hidden');
        body.classList.add('fade-in');
        chevron.classList.add('rotate-180');
    } else {
        body.classList.add('hidden');
        chevron.classList.remove('rotate-180');
    }
}

// Start all collapsed on load, but expand first one
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.activities-body').forEach(function(el, i) {
        if (i === 0) {
            // First one starts open
            el.classList.remove('hidden');
            const header = el.previousElementSibling;
            if (header) {
                const chevron = header.querySelector('[data-chevron]');
                if (chevron) chevron.classList.add('rotate-180');
            }
        } else {
            el.classList.add('hidden');
        }
    });
});
</script>
@endpush
