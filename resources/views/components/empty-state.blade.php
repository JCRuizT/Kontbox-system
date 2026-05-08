<div class="text-center py-16 fade-in">
    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gray-100 flex items-center justify-center">
        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $icon ?? 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4' }}"/></svg>
    </div>
    <p class="text-base font-semibold text-gray-900">{{ $title ?? __('ui.common.no_data') }}</p>
    @if($description ?? false)
    <p class="text-sm text-gray-500 mt-1 max-w-sm mx-auto">{{ $description }}</p>
    @endif
    @if($action ?? false)
    <div class="mt-6">
        {{ $action }}
    </div>
    @endif
</div>
