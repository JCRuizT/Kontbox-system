<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('ui.login.title') }} — {{ __('ui.site_name') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }
        @keyframes glow { 0%,100% { opacity: 0.4; } 50% { opacity: 0.8; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideLeft { from { opacity: 0; transform: translateX(30px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes pulse-dot { 0%,100% { transform: scale(1); opacity: 0.5; } 50% { transform: scale(1.5); opacity: 1; } }
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-glow { animation: glow 3s ease-in-out infinite; }
        .animate-slide-up { animation: slideUp 0.6s ease forwards; }
        .animate-slide-left { animation: slideLeft 0.6s ease 0.2s forwards; opacity: 0; }
        .input-group { position: relative; }
        .input-group input { transition: all 0.2s ease; }
        .input-group input:focus { box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12); }
        .input-group input:focus + .input-icon { color: #4f46e5; }
        .input-group .input-icon { transition: color 0.2s ease; }
        .btn-primary { transition: all 0.2s ease; position: relative; overflow: hidden; }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 25px -6px rgba(79, 70, 229, 0.4); }
        .btn-primary:active { transform: translateY(0); }
        .bg-grid { background-image: radial-gradient(rgba(255,255,255,0.08) 1px, transparent 1px); background-size: 24px 24px; }
    </style>
</head>
<body class="h-full bg-slate-950">
    <div class="min-h-screen flex">
        {{-- LEFT SIDE — BRANDING --}}
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-950 p-12 xl:p-16 flex-col justify-between relative overflow-hidden">
            <div class="absolute top-20 -right-20 w-80 h-80 bg-indigo-500/10 rounded-full blur-3xl animate-glow"></div>
            <div class="absolute -bottom-20 -left-20 w-96 h-96 bg-violet-500/10 rounded-full blur-3xl animate-glow" style="animation-delay: 1.5s;"></div>
            <div class="relative z-10">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-400 to-violet-500 rounded-xl flex items-center justify-center text-white font-bold text-sm shadow-lg shadow-indigo-500/20">K</div>
                    <span class="text-white font-bold text-xl">{{ __('ui.site_name') }}</span>
                </div>
            </div>
            <div class="relative z-10 space-y-6">
                <div class="inline-flex items-center space-x-2 px-3 py-1.5 rounded-full bg-white/5 border border-white/10 text-white/60 text-xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse-dot" style="animation: pulse-dot 2s ease-in-out infinite;"></span>
                    <span>{{ __('ui.site_description') }}</span>
                </div>
                <h2 class="text-4xl xl:text-5xl font-bold text-white leading-tight">{{ __('ui.login.welcome') }}<br><span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-violet-400">Kontbox</span></h2>
                <p class="text-indigo-300/60 text-sm max-w-md leading-relaxed">{{ __('ui.login_page.system_description') }}</p>
                <div class="flex flex-wrap gap-4 text-indigo-300/40 text-xs">
                    <span class="flex items-center space-x-1.5"><svg class="w-3.5 h-3.5 text-indigo-400/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>{{ __('ui.dashboard_page.stats.microservices') }}</span></span>
                    <span class="flex items-center space-x-1.5"><svg class="w-3.5 h-3.5 text-indigo-400/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>{{ __('ui.dashboard_page.stats.plans') }}</span></span>
                    <span class="flex items-center space-x-1.5"><svg class="w-3.5 h-3.5 text-indigo-400/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>{{ __('ui.dashboard_page.stats.quotations') }}</span></span>
                    <span class="flex items-center space-x-1.5"><svg class="w-3.5 h-3.5 text-indigo-400/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>{{ __('ui.dashboard_page.stats.contracts') }}</span></span>
                </div>
            </div>
            <div class="relative z-10 text-indigo-300/30 text-xs">© {{ date('Y') }} {{ __('ui.site_name') }}. {{ __('ui.login_page.copyright') }}</div>
        </div>

        {{-- RIGHT SIDE — LOGIN FORM --}}
        <div class="w-full lg:w-1/2 bg-white flex items-center justify-center p-8 lg:p-16">
            <div class="w-full max-w-sm animate-slide-up">
                <div class="text-center lg:text-left mb-10">
                    <div class="lg:hidden flex items-center justify-center space-x-2 mb-6">
                        <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-violet-600 rounded-lg flex items-center justify-center text-white font-bold text-xs">K</div>
                        <span class="font-bold text-gray-900">{{ __('ui.site_name') }}</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ __('ui.login.title') }}</h3>
                    <p class="text-gray-500 text-sm mt-1.5">{{ __('ui.login.subtitle') }}</p>
                </div>

                @if ($errors->any())
                    <div class="mb-6 flex items-center space-x-2.5 bg-red-50 border border-red-200 text-red-700 px-4 py-3.5 rounded-xl text-sm">
                        <svg class="w-5 h-5 flex-shrink-0 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                        <span>{{ $errors->first('email') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5" novalidate>
                    @csrf
                    <div class="input-group">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('ui.login.email') }}</label>
                        <div class="relative">
                            <input type="email" name="email" value="{{ old('email') }}"
                                class="block w-full px-4 py-3 pl-11 rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition @error('email') border-red-300 bg-red-50 @enderror"
                                placeholder="{{ __('ui.login.placeholder_email') }}" autocomplete="email" autofocus>
                            <svg class="input-icon absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                        </div>
                    </div>
                    <div class="input-group">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('ui.login.password') }}</label>
                        <div class="relative">
                            <input type="password" name="password"
                                class="block w-full px-4 py-3 pl-11 rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition"
                                placeholder="••••••••" autocomplete="current-password">
                            <svg class="input-icon absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="checkbox-custom">
                            <span class="text-sm text-gray-600 select-none">{{ __('ui.login.remember') }}</span>
                        </label>
                    </div>
                    <button type="submit" class="btn-primary w-full bg-gradient-to-r from-indigo-600 to-indigo-700 text-white py-3 rounded-xl font-semibold hover:from-indigo-700 hover:to-indigo-800 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition shadow-lg shadow-indigo-200">
                        {{ __('ui.login.button') }}
                    </button>
                </form>
                <p class="text-center text-xs text-gray-400 mt-10">{{ __('ui.site_description') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
