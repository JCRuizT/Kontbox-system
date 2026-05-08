<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name')) — {{ __('ui.site_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full bg-gray-50 font-sans antialiased text-gray-900">
    <div class="min-h-screen flex">
        {{-- SIDEBAR --}}
        <aside class="hidden lg:flex lg:flex-col w-64 h-screen bg-gradient-to-b from-slate-900 to-slate-950 text-white shadow-2xl sticky top-0">
            <div class="p-5 border-b border-white/10">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
                    <div class="w-9 h-9 bg-gradient-to-br from-indigo-500 to-violet-600 rounded-xl flex items-center justify-center text-sm font-bold shadow-lg shadow-indigo-500/20 group-hover:scale-105 transition-transform">K</div>
                    <div>
                        <h1 class="text-base font-bold tracking-tight">{{ __('ui.site_name') }}</h1>
                        <p class="text-indigo-300/70 text-xs leading-tight">{{ __('ui.site_description') }}</p>
                    </div>
                </a>
            </div>
            <nav class="flex-1 p-3 space-y-0.5 text-sm overflow-y-auto overflow-x-hidden custom-scroll">
                @php $navSections = [
                    ['section' => null, 'items' => [
                        ['route' => 'dashboard', 'url' => route('dashboard'), 'label' => __('ui.dashboard'), 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'perm' => null],
                    ]],
                    ['section' => __('ui.nav_sections.config'), 'items' => [
                        ['route' => 'microservices.*', 'url' => route('microservices.index'), 'label' => __('ui.sidebar.microservices'), 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', 'perm' => 'microservices.read'],
                        ['route' => 'plans.*', 'url' => route('plans.index'), 'label' => __('ui.sidebar.plans'), 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'perm' => 'plans.read'],
                        ['route' => 'activities.*', 'url' => route('activities.index'), 'label' => __('ui.sidebar.activities'), 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'perm' => 'activities.read'],
                    ]],
                    ['section' => __('ui.nav_sections.commercial'), 'items' => [
                        ['route' => 'prospects.*', 'url' => route('prospects.index'), 'label' => __('ui.sidebar.prospects'), 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'perm' => 'prospects.read'],
                        ['route' => 'quotations.*', 'url' => route('quotations.index'), 'label' => __('ui.sidebar.quotations'), 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'perm' => 'quotations.read'],
                    ]],
                    ['section' => __('ui.nav_sections.approval'), 'items' => [
                        ['route' => 'reviews.*', 'url' => route('reviews.index'), 'label' => __('ui.sidebar.reviews'), 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'perm' => 'quotations.approve'],
                    ]],
                    ['section' => __('ui.nav_sections.contractual'), 'items' => [
                        ['route' => 'contracts.*', 'url' => route('contracts.index'), 'label' => __('ui.sidebar.contracts'), 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'perm' => 'contracts.read'],
                        ['route' => 'amendments.*', 'url' => route('amendments.index'), 'label' => __('ui.sidebar.amendments'), 'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z', 'perm' => 'amendments.read'],
                    ]],
                    ['section' => __('ui.nav_sections.financial'), 'items' => [
                        ['route' => 'invoices.*', 'url' => route('invoices.index'), 'label' => __('ui.sidebar.invoicing'), 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'perm' => 'invoices.read'],
                    ]],
                    ['section' => __('ui.nav_sections.audit'), 'items' => [
                        ['route' => 'audit.*', 'url' => route('audit.index'), 'label' => __('ui.sidebar.audit'), 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'perm' => 'audit.read'],
                    ]],
                    ['section' => __('ui.nav_sections.admin'), 'items' => [
                        ['route' => 'admin.users.*', 'url' => route('admin.users'), 'label' => __('ui.sidebar.users'), 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'perm' => 'admin.access'],
                        ['route' => 'admin.roles.*', 'url' => route('admin.roles'), 'label' => __('ui.sidebar.roles'), 'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'perm' => 'admin.access'],
                    ]],
                ]; @endphp
                @foreach ($navSections as $section)
                    @php
                        $sectionHasItems = false;
                        foreach($section['items'] as $it) {
                            $ok = (!isset($it['perm']) && !isset($it['role'])) || (isset($it['perm']) && auth()->user()->can($it['perm'])) || (isset($it['role']) && auth()->user()->hasRole($it['role']));
                            if ($ok) { $sectionHasItems = true; break; }
                        }
                    @endphp
                    @if(!$sectionHasItems) @continue @endif
                    @if($section['section'])
                    <div class="px-3 pt-4 pb-1">
                        <p class="text-[10px] font-bold text-white/25 uppercase tracking-widest">{{ $section['section'] }}</p>
                    </div>
                    @endif
                    @foreach ($section['items'] as $item)
                        @php
                            $showItem = (!isset($item['perm']) && !isset($item['role'])) || (isset($item['perm']) && auth()->user()->can($item['perm'])) || (isset($item['role']) && auth()->user()->hasRole($item['role']));
                        @endphp
                        @if($showItem)
                        <a href="{{ $item['url'] }}"
                       class="nav-link flex items-center space-x-3 px-3 py-2 rounded-xl {{ request()->routeIs($item['route']) ? 'bg-white/10 text-white font-medium' : 'text-white/60 hover:text-white hover:bg-white/5' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $item['icon'] }}"/></svg>
                        <span>{{ is_string($item['label']) ? $item['label'] : (is_array($item['label']) ? (count($item['label']) > 0 && is_string(reset($item['label'])) ? reset($item['label']) : '') : '') }}</span>
                    </a>
                    @endif
                    @endforeach
                @endforeach
            </nav>
            <div class="p-4 border-t border-white/10">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-9 h-9 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-full flex items-center justify-center text-xs font-bold shadow-lg flex-shrink-0">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ auth()->user()->name ?? '' }}</p>
                        <p class="text-xs text-white/40 truncate">{{ auth()->user()->getRoleNames()->implode(', ') }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center space-x-2 px-3 py-2 rounded-xl text-white/40 hover:text-white hover:bg-white/5 transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>{{ __('ui.logout') }}</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- MOBILE HEADER --}}
        <div class="lg:hidden fixed top-0 left-0 right-0 z-50 bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-3">
                <button id="mobile-menu-btn" class="p-2 rounded-lg hover:bg-gray-100 transition" onclick="document.getElementById('mobile-sidebar').classList.toggle('hidden')">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <span class="font-bold text-gray-900">{{ __('ui.site_name') }}</span>
            </div>
            <div class="text-xs text-gray-400">{{ auth()->user()->name ?? '' }}</div>
        </div>

        {{-- MOBILE SIDEBAR OVERLAY --}}
        <div id="mobile-sidebar" class="hidden fixed inset-0 z-40 lg:hidden" onclick="this.classList.add('hidden')">
            <div class="absolute inset-0 bg-black/30 backdrop-blur-sm"></div>
            <aside class="absolute left-0 top-0 bottom-0 w-72 bg-gradient-to-b from-slate-900 to-slate-950 text-white shadow-2xl overflow-y-auto custom-scroll">
                <div class="p-5 border-b border-white/10 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-violet-600 rounded-xl flex items-center justify-center text-sm font-bold">K</div>
                        <span class="font-bold">{{ __('ui.site_name') }}</span>
                    </div>
                    <button onclick="document.getElementById('mobile-sidebar').classList.add('hidden')" class="p-1 rounded-lg hover:bg-white/10 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <nav class="p-3 space-y-0.5 text-sm">
                    @foreach ($navSections as $section)
                        @php
                            $sectionHasItems = false;
                            foreach($section['items'] as $it) {
                                $ok = (!isset($it['perm']) && !isset($it['role'])) || (isset($it['perm']) && auth()->user()->can($it['perm'])) || (isset($it['role']) && auth()->user()->hasRole($it['role']));
                                if ($ok) { $sectionHasItems = true; break; }
                            }
                        @endphp
                        @if(!$sectionHasItems) @continue @endif
                        @if($section['section'])
                        <div class="px-3 pt-4 pb-1">
                            <p class="text-[10px] font-bold text-white/25 uppercase tracking-widest">{{ $section['section'] }}</p>
                        </div>
                        @endif
                        @foreach ($section['items'] as $item)
                            @php $showItem = (!isset($item['perm']) && !isset($item['role'])) || (isset($item['perm']) && auth()->user()->can($item['perm'])) || (isset($item['role']) && auth()->user()->hasRole($item['role'])); @endphp
                            @if($showItem)
                            <a href="{{ $item['url'] }}"
                               class="flex items-center space-x-3 px-3 py-2 rounded-xl {{ request()->routeIs($item['route']) ? 'bg-white/10 text-white font-medium' : 'text-white/60 hover:text-white hover:bg-white/5' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $item['icon'] }}"/></svg>
                                <span>{{ is_string($item['label']) ? $item['label'] : (is_array($item['label']) ? (count($item['label']) > 0 && is_string(reset($item['label'])) ? reset($item['label']) : '') : '') }}</span>
                            </a>
                            @endif
                        @endforeach
                    @endforeach
                </nav>
                <div class="p-4 border-t border-white/10">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center space-x-2 px-3 py-2 rounded-xl text-white/40 hover:text-white hover:bg-white/5 transition text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span>{{ __('ui.logout') }}</span>
                        </button>
                    </form>
                </div>
            </aside>
        </div>

        {{-- MAIN CONTENT --}}
        <main class="flex-1 lg:pt-0 pt-14">
            <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
                @if (session('success'))
                    <div class="alert alert-success toast-enter" role="alert">
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div class="flex-1 text-sm font-medium">{{ session('success') }}</div>
                        <button onclick="this.parentElement.remove()" class="text-green-400 hover:text-green-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-error toast-enter" role="alert">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div class="flex-1 text-sm font-medium">{{ session('error') }}</div>
                        <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('mobile-sidebar');
            if (sidebar && !sidebar.classList.contains('hidden')) {
                if (e.target === sidebar) sidebar.classList.add('hidden');
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
