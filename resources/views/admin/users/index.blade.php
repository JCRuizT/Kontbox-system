@extends('layouts.app')
@section('title', __('ui.admin_users.title'))
@section('content')
<div class="fade-in">
    <div class="page-header">
        <div>
            <h2 class="page-title">{{ __('ui.admin_users.heading') }}</h2>
            <p class="page-subtitle">{{ __('ui.admin_users.description') }}</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn inline-flex items-center space-x-2 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white px-5 py-2.5 rounded-xl hover:from-indigo-700 hover:to-indigo-800 shadow-lg shadow-indigo-200 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>{{ __('ui.admin_users.new') }}</span>
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="px-4 sm:px-5 py-3 sm:py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ __('ui.admin_users.columns.name') }}</th>
                    <th class="px-4 sm:px-5 py-3 sm:py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">{{ __('ui.admin_users.columns.email') }}</th>
                    <th class="px-4 sm:px-5 py-3 sm:py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">{{ __('ui.admin_users.columns.role') }}</th>
                    <th class="px-4 sm:px-5 py-3 sm:py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('ui.admin_users.columns.status') }}</th>
                    <th class="px-4 sm:px-5 py-3 sm:py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ __('ui.admin_users.columns.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach ($users as $u)
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-4 sm:px-5 py-3 sm:py-4 whitespace-nowrap">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white text-xs font-bold">{{ strtoupper(substr($u->name, 0, 2)) }}</div>
                            <span class="font-medium text-gray-900">{{ $u->name }}</span>
                        </div>
                    </td>
                    <td class="px-4 sm:px-5 py-3 sm:py-4 text-gray-600 hidden sm:table-cell">{{ $u->email }}</td>
                    <td class="px-4 sm:px-5 py-3 sm:py-4 hidden sm:table-cell">
                        @foreach($u->roles as $r)
                        <span class="badge badge-blue">
                            <span class="badge-dot bg-indigo-500"></span>
                            <span>{{ $r->name }}</span>
                        </span>
                        @endforeach
                    </td>
                    <td class="px-4 sm:px-5 py-3 sm:py-4 text-center">
                        <span class="badge {{ $u->deleted_at ? 'badge-red' : 'badge-green' }}">
                            <span class="badge-dot {{ $u->deleted_at ? 'bg-red-500' : 'bg-green-500' }}"></span>
                            <span>{{ $u->deleted_at ? __('ui.admin_users.inactive') : __('ui.admin_users.active') }}</span>
                        </span>
                    </td>
                    <td class="px-4 sm:px-5 py-3 sm:py-4 text-right whitespace-nowrap">
                        <div class="flex items-center justify-end space-x-2">
                            <a href="{{ route('admin.users.edit', $u) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">{{ __('ui.admin_users.edit_action') }}</a>
                            @if(!$u->deleted_at && $u->id !== auth()->id())
                            <form action="{{ route('admin.users.delete', $u) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('ui.admin_users.delete_confirm') }}')">
                                @csrf
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium">{{ __('ui.admin_users.delete_action') }}</button>
                            </form>
                            @endif
                            @if($u->deleted_at && $u->id !== auth()->id())
                            <form action="{{ route('admin.users.restore', $u->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-xs text-emerald-600 hover:text-emerald-800 font-medium">{{ __('ui.admin_users.restore_action') }}</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        <div class="px-4 sm:px-5 py-3 sm:py-4 border-t border-gray-100">{{ $users->links() }}</div>
    </div>
</div>
@endsection
