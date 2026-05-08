@extends('layouts.app')
@section('title', __('domain.audit.title'))
@section('content')
<div class="fade-in">
    <div class="flex items-center space-x-4 mb-6">
        <div class="w-12 h-12 bg-gray-100 rounded-2xl flex items-center justify-center shadow-sm">
            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ __('domain.audit.title') }}</h2>
            <p class="text-sm text-gray-500">{{ __('domain.audit.description') }}</p>
        </div>
    </div>

    {{-- FILTRO --}}
    @php
        $currentLog = request('log_name', '');
        $categories = [
            '' => ['label' => __('ui.audit_view.all')],
            'app' => ['label' => __('ui.audit_view.cat_app'), 'dot' => 'bg-purple-500'],
            'crud' => ['label' => __('ui.audit_view.cat_crud'), 'dot' => 'bg-blue-500'],
            'business' => ['label' => __('ui.audit_view.cat_business'), 'dot' => 'bg-emerald-500'],
            'error' => ['label' => __('ui.audit_view.cat_error'), 'dot' => 'bg-red-500'],
        ];
    @endphp
    <div class="flex items-center flex-wrap gap-2 mb-6">
        <span class="text-xs text-gray-400 font-medium uppercase tracking-wider mr-1">{{ __('ui.audit_view.filter_label') }}</span>
        @foreach($categories as $val => $cat)
        <a href="{{ $val ? route('audit.index', ['log_name' => $val]) : route('audit.index') }}"
           class="badge transition
           {{ $currentLog === $val ? ($val === '' ? 'badge-purple' : ($val === 'app' ? 'badge-purple' : ($val === 'crud' ? 'badge-blue' : ($val === 'business' ? 'badge-emerald' : 'badge-red')))) : 'badge-gray' }}">
            @if($val) <span class="badge-dot {{ $currentLog === $val ? $cat['dot'] : 'bg-gray-300' }}"></span> @endif
            <span>{{ $cat['label'] }}</span>
        </a>
        @endforeach
    </div>

    {{-- TABLA --}}
    @php
        $query = \Spatie\Activitylog\Models\Activity::with('causer');
        if ($currentLog) $query->where('log_name', $currentLog);
        $logs = $query->latest()->paginate(config('kontbox.items_per_page'));
    @endphp

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('ui.audit_view.category') }}</th>
                        <th class="px-4 py-3 text-left text-xs text-gray-500 uppercase">{{ __('ui.audit_view.date') }}</th>
                        <th class="px-4 py-3 text-left text-xs text-gray-500 uppercase">{{ __('ui.audit_view.user_role') }}</th>
                        <th class="px-4 py-3 text-left text-xs text-gray-500 uppercase">{{ __('ui.audit_view.action') }}</th>
                        <th class="px-4 py-3 text-left text-xs text-gray-500 uppercase">{{ __('ui.audit_view.entity') }}</th>
                        <th class="px-4 py-3 text-right text-xs text-gray-500 uppercase">{{ __('ui.audit_view.detail') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($logs as $log)
                    @php $props = $log->properties->toArray(); @endphp
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-4 py-3">
                             <span class="badge
                                @switch($log->log_name)
                                    @case('app') badge-purple @break
                                    @case('crud') badge-blue @break
                                    @case('business') badge-emerald @break
                                    @case('error') badge-red @break
                                    @default badge-gray @break
                                @endswitch">
                                <span class="badge-dot
                                    @switch($log->log_name) @case('app') bg-purple-500 @break @case('crud') bg-blue-500 @break @case('business') bg-emerald-500 @break @case('error') bg-red-500 @break @default bg-gray-400 @break @endswitch"></span>
                                <span>{{ $categories[$log->log_name]['label'] ?? $log->log_name }}</span>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center space-x-2">
                                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white text-[9px] font-bold flex-shrink-0">
                                    {{ $log->causer ? strtoupper(substr($log->causer->name, 0, 2)) : substr(__('ui.audit_view.system'), 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-900">{{ $log->causer->name ?? __('ui.audit_view.system') }}</p>
                                    @if($log->causer && method_exists($log->causer, 'getRoleNames'))
                                    <p class="text-[10px] text-gray-400">{{ $log->causer->getRoleNames()->implode(', ') }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="badge
                                @if(str_contains($log->description, 'Creó')) badge-green
                                @elseif(str_contains($log->description, 'Actualizó')) badge-blue
                                @elseif(str_contains($log->description, 'Desactivó')) badge-red
                                @elseif(str_contains($log->description, 'Cambió estado')) badge-amber
                                @elseif(str_contains($log->description, 'Inició sesión') || str_contains($log->description, 'Cerró sesión')) badge-purple
                                @else badge-gray
                                @endif">
                                {{ Str::limit($log->description, 45) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs font-mono text-gray-500">
                            {{ $log->subject_type ? class_basename($log->subject_type) : '-' }}
                            @if($log->subject_id)<span class="text-gray-400">#{{ $log->subject_id }}</span>@endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button onclick="openDetail({{ $log->id }})"
                                class="inline-flex items-center space-x-1 text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>{{ __('ui.audit_view.view_detail') }}</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-16 text-center text-sm text-gray-400">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p>{{ __('ui.audit_view.empty') }}</p>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-4 border-t border-gray-100">{{ $logs->withQueryString()->links() }}</div>
    </div>
</div>

{{-- MODAL DE DETALLE --}}
<div id="detail-modal" class="hidden fixed inset-0 z-50 flex items-start justify-center pt-10 pb-10 px-4 overflow-y-auto" onclick="if(event.target===this)closeDetail()">
    <div class="fixed inset-0 bg-black/40 backdrop-blur-sm"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[85vh] overflow-y-auto fade-in">
        <div class="sticky top-0 bg-white border-b border-gray-100 px-6 py-4 flex items-center justify-between z-10">
            <div>
                <h3 class="text-lg font-bold text-gray-900">{{ __('ui.audit_view.modal_title') }}</h3>
                <p class="text-xs text-gray-500" id="modal-log-name"></p>
            </div>
            <button onclick="closeDetail()" class="p-2 rounded-lg hover:bg-gray-100 transition">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="modal-body" class="p-6 space-y-5">
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
@php $logsData = $logs->map(fn($l) => [
    'id' => $l->id,
    'log_name' => $l->log_name,
    'description' => $l->description,
    'created_at' => $l->created_at->format('d/m/Y H:i:s'),
    'ip' => $l->properties['ip'] ?? '-',
    'user_agent' => $l->properties['user_agent'] ?? '',
    'url' => $l->properties['url'] ?? '',
    'method' => $l->properties['method'] ?? '',
    'changes' => $l->properties['changes'] ?? [],
    'new_values' => $l->properties['new_values'] ?? [],
    'from' => $l->properties['from'] ?? null,
    'to' => $l->properties['to'] ?? null,
    'file' => $l->properties['file'] ?? null,
    'causer_name' => $l->causer->name ?? 'Sistema',
    'causer_email' => $l->causer->email ?? '',
    'causer_role' => ($l->causer && method_exists($l->causer, 'getRoleNames')) ? $l->causer->getRoleNames()->implode(', ') : '',
    'subject_type' => $l->subject_type ? class_basename($l->subject_type) : '-',
    'subject_id' => $l->subject_id ?? null,
])->values(); @endphp

const logsData = @json($logsData);

const categoryStyles = {
    app: { color: 'badge-purple', dot: 'bg-purple-500', label: 'App' },
    crud: { color: 'badge-blue', dot: 'bg-blue-500', label: 'CRUD' },
    business: { color: 'badge-emerald', dot: 'bg-emerald-500', label: 'Negocio' },
    error: { color: 'badge-red', dot: 'bg-red-500', label: 'Error' },
};

function openDetail(id) {
    const item = logsData.find(l => l.id === id);
    if (!item) return;
    const cat = categoryStyles[item.log_name] || { color: 'bg-gray-100 text-gray-600', dot: 'bg-gray-400', label: item.log_name };
    const changes = item.changes && typeof item.changes === 'object' && !Array.isArray(item.changes) ? item.changes : {};
    const changeKeys = Object.keys(changes);
    const hasChanges = changeKeys.length > 0;
    const hasFile = item.file;

    let changesHtml = '';
    if (hasChanges) {
        changesHtml = changeKeys.map((field, i) => {
            const c = changes[field];
            const from = formatVal(c.from);
            const to = formatVal(c.to);
            return `<div class="p-3 ${i % 2 === 0 ? 'border-r border-gray-200' : ''}">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">${field}</p>
                <div class="space-y-1.5">
                    <div class="flex items-start space-x-2 px-3 py-2 rounded-lg bg-red-50 border-l-2 border-red-400">
                        <span class="text-red-500 font-bold text-sm">−</span>
                        <div><p class="text-xs text-red-400 font-medium">Anterior</p><p class="text-sm text-red-700 font-mono line-through break-words">${from}</p></div>
                    </div>
                    <div class="flex items-start space-x-2 px-3 py-2 rounded-lg bg-green-50 border-l-2 border-green-400">
                        <span class="text-green-500 font-bold text-sm">+</span>
                        <div><p class="text-xs text-green-400 font-medium">Nuevo</p><p class="text-sm text-green-700 font-mono break-words">${to}</p></div>
                    </div>
                </div>
            </div>`;
        }).join('');
        if (changeKeys.length > 1) {
            changesHtml = `<div class="grid grid-cols-1 sm:grid-cols-2 gap-0 border border-gray-200 rounded-xl overflow-hidden">${changesHtml}</div>`;
        }
    }

    let statusHtml = '';
    if (item.from && item.to && !hasChanges) {
        statusHtml = `<div class="flex items-center space-x-2 p-4 bg-gray-50 rounded-xl">
            <span class="text-xs text-gray-500 font-medium">Estado:</span>
            <span class="px-3 py-1 rounded-lg bg-red-100 text-red-700 text-sm font-mono line-through">${item.from}</span>
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            <span class="px-3 py-1 rounded-lg bg-green-100 text-green-700 text-sm font-mono font-medium">${item.to}</span>
        </div>`;
    }

    let fileHtml = hasFile ? `<div class="flex items-center space-x-2 p-3 bg-emerald-50 rounded-xl">
        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div><p class="text-xs text-gray-500">Archivo cargado</p><p class="text-sm font-mono text-emerald-700">${item.file}</p></div>
    </div>` : '';

    const newValsCount = item.new_values && typeof item.new_values === 'object' ? Object.keys(item.new_values).length : 0;

    document.getElementById('modal-log-name').textContent = `#${item.id} · ${item.created_at}`;
    document.getElementById('modal-body').innerHTML = `
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="p-4 bg-gray-50 rounded-xl">
                <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Usuario</p>
                <p class="font-semibold text-gray-900">${item.causer_name}</p>
                <p class="text-xs text-gray-500">${item.causer_email || ''}</p>
                ${item.causer_role ? `<p class="text-xs text-gray-400 mt-1">Rol: ${item.causer_role}</p>` : ''}
            </div>
            <div class="p-4 bg-gray-50 rounded-xl">
                <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Categoría</p>
                <span class="badge ${cat.color}">
                    <span class="badge-dot ${cat.dot}"></span>
                    <span>${cat.label}</span>
                </span>
                <p class="text-xs text-gray-500 mt-1">${item.description}</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-xl">
                <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Entidad</p>
                <p class="font-semibold text-gray-900">${item.subject_type}${item.subject_id ? ' <span class="text-gray-400 font-mono">#' + item.subject_id + '</span>' : ''}</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-xl">
                <p class="text-xs text-gray-400 uppercase font-semibold mb-1">IP / URL</p>
                <p class="text-xs font-mono text-gray-700">${item.ip}</p>
                ${item.url ? `<p class="text-xs text-gray-400 truncate mt-1" title="${item.url}">${item.method || ''} ${item.url.substring(0, 50)}</p>` : ''}
            </div>
        </div>

        ${statusHtml}

        ${fileHtml}

        ${newValsCount > 0 ? `<div class="flex items-center space-x-2 p-3 bg-gray-50 rounded-xl">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-sm text-gray-700">${newValsCount} campo(s) creados en el registro</span>
        </div>` : ''}

        ${changesHtml ? `<div><h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center space-x-2">
            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            <span>Cambios realizados (<span class="text-blue-600">${changeKeys.length}</span>)</span>
        </h4>${changesHtml}</div>` : ''}
    `;
    document.getElementById('detail-modal').classList.remove('hidden');
}

function closeDetail() {
    document.getElementById('detail-modal').classList.add('hidden');
}

function formatVal(v) {
    if (v === null || v === undefined) return '<span class="text-gray-300 italic">—</span>';
    if (typeof v === 'boolean') return v ? 'true' : 'false';
    if (typeof v === 'string') return v || '<span class="text-gray-300 italic">(vacío)</span>';
    if (typeof v === 'number') return v.toString();
    return JSON.stringify(v);
}
</script>
@endpush
