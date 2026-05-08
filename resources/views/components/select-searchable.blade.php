@props([
    'name' => 'select',
    'label' => '',
    'placeholder' => '',
    'searchUrl' => '',
    'value' => '',
    'text' => '',
    'required' => false,
    'error' => null,
    'options' => [],  // SSR options: [['id'=>1, 'name'=>'...', 'subtext'=>'...'], ...]
])

@php $uid = 'ss_' . uniqid(); @endphp

<div class="relative" id="{{ $uid }}_wrap">
    @if($label)
    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
        {{ $label }}
        @if($required) <span class="text-red-400">*</span> @endif
    </label>
    @endif

    <button type="button" id="{{ $uid }}_trigger"
        class="form-input w-full px-4 py-3 rounded-xl border border-gray-300 shadow-sm text-left flex items-center justify-between bg-white hover:border-indigo-300 transition"
        onclick="SSToggle('{{ $uid }}')">
        <span id="{{ $uid }}_text" class="{{ $value ? 'text-gray-900' : 'text-gray-400' }}">
            {{ $text ?: ($value && $options ? collect($options)->firstWhere('id', $value)['name'] ?? $placeholder : $placeholder) }}
        </span>
        <svg class="w-4 h-4 text-gray-400 transition-transform" id="{{ $uid }}_chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>

    <input type="hidden" name="{{ $name }}" id="{{ $uid }}_hidden" value="{{ $value }}">

    <div id="{{ $uid }}_dropdown" class="hidden absolute z-50 mt-1 w-full bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="p-2 border-b border-gray-100">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="{{ $uid }}_query"
                    class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                    placeholder="{{ $placeholder }}"
                    oninput="SSDebouncedSearch('{{ $uid }}')">
            </div>
        </div>
        <div id="{{ $uid }}_loading" class="hidden p-4 text-center">
            <svg class="w-6 h-6 mx-auto text-indigo-500 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <p class="text-xs text-gray-400 mt-1">{{ __('ui.select_searchable.searching') }}</p>
        </div>
        <div id="{{ $uid }}_results" class="max-h-48 overflow-y-auto divide-y divide-gray-50">
            @forelse($options as $opt)
            <button type="button"
                class="ss-option w-full text-left px-4 py-2.5 hover:bg-indigo-50 transition text-sm"
                data-id="{{ $opt['id'] }}"
                data-name="{{ $opt['name'] }}"
                data-subtext="{{ $opt['subtext'] ?? '' }}"
                onclick="SSSelect('{{ $uid }}', {id:'{{ $opt['id'] }}', name:'{{ addslashes($opt['name']) }}', subtext:'{{ addslashes($opt['subtext'] ?? '') }}'})">
                <p class="font-medium text-gray-900">{{ $opt['name'] }}</p>
                @if($opt['subtext'] ?? false)
                <p class="text-xs text-gray-500 mt-0.5">{{ $opt['subtext'] }}</p>
                @endif
            </button>
            @empty
            <div id="{{ $uid }}_empty_initial" class="p-4 text-center text-sm text-gray-400">{{ __('ui.select_searchable.no_results') }}</div>
            @endforelse
        </div>
        <div id="{{ $uid }}_ajax_empty" class="hidden p-4 text-center text-sm text-gray-400">{!! __('ui.select_searchable.no_results_for', ['query' => '<span id="' . $uid . '_ajax_term"></span>']) !!}</div>
    </div>

    @if($error)
    <p class="mt-1.5 text-sm text-red-500">{{ $error }}</p>
    @endif
</div>

<script>
(function(){
    if (window.__SSInit) return;
    window.__SSInit = true;
    window.__SS = window.__SS || {};

    function el(id) { return document.getElementById(id); }
    function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    window.SSToggle = function(uid) {
        var dd = el(uid + '_dropdown'), ch = el(uid + '_chevron');
        if (dd.classList.contains('hidden')) {
            dd.classList.remove('hidden'); dd.classList.add('fade-in');
            ch.classList.add('rotate-180');
            el(uid + '_query').value = '';
            el(uid + '_query').focus();
            document.addEventListener('click', SSOutside);
        } else {
            SSC(uid);
        }
    };

    function SSC(uid) {
        el(uid + '_dropdown').classList.add('hidden');
        el(uid + '_chevron').classList.remove('rotate-180');
        document.removeEventListener('click', SSOutside);
    }

    function SSOutside(e) {
        document.querySelectorAll('[id$="_wrap"]').forEach(function(w) {
            if (w.contains(e.target)) return;
            var uid = w.id.replace('_wrap','');
            var dd = el(uid + '_dropdown');
            if (dd && !dd.classList.contains('hidden')) SSC(uid);
        });
    }

    window.SSDebouncedSearch = function(uid) {
        if (window.__SS['db_' + uid]) clearTimeout(window.__SS['db_' + uid]);
        window.__SS['db_' + uid] = setTimeout(function() { SSSearch(uid); }, 400);
    };

    window.SSSearch = function(uid) {
        var q = el(uid + '_query').value.trim();
        if (!q) { showSSR(uid); return; }
        el(uid + '_loading').classList.remove('hidden');
        el(uid + '_results').innerHTML = '';
        var url = el(uid + '_wrap').querySelector('[name="' + uid.replace('ss_','') + '"]')
            ? null : null;
        var searchUrl = '{{ $searchUrl }}';
        if (!searchUrl) { el(uid + '_loading').classList.add('hidden'); return; }

        fetch(searchUrl + '?q=' + encodeURIComponent(q))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                el(uid + '_loading').classList.add('hidden');
                var c = el(uid + '_results'); c.innerHTML = '';
                if (data.length === 0) {
                    el(uid + '_ajax_term').textContent = q;
                    el(uid + '_ajax_empty').classList.remove('hidden');
                    return;
                }
                el(uid + '_ajax_empty').classList.add('hidden');
                data.forEach(function(item) {
                    var b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'ss-option w-full text-left px-4 py-2.5 hover:bg-indigo-50 transition text-sm';
                    b.innerHTML = '<p class="font-medium text-gray-900">' + esc(item.name) + '</p>' +
                        (item.subtext ? '<p class="text-xs text-gray-500 mt-0.5">' + esc(item.subtext) + '</p>' : '');
                    b.onclick = function() { SSSelect(uid, item); };
                    c.appendChild(b);
                });
            })
            .catch(function() { el(uid + '_loading').classList.add('hidden'); });
    };

    function showSSR(uid) {
        var c = el(uid + '_results');
        var opts = c.querySelectorAll('.ss-option');
        if (opts.length > 0) { c.innerHTML = ''; opts.forEach(function(o) { c.appendChild(o.cloneNode(true)); }); }
        el(uid + '_ajax_empty').classList.add('hidden');
    }

    window.SSSelect = function(uid, item) {
        el(uid + '_hidden').value = item.id;
        el(uid + '_text').textContent = item.name + (item.subtext ? ' — ' + item.subtext : '');
        el(uid + '_text').className = 'text-gray-900';
        SSC(uid);
        el(uid + '_hidden').dispatchEvent(new Event('change', { bubbles: true }));
    };
})();
</script>
