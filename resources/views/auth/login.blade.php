@extends('layouts.guest')

@section('title', 'Sign In')

@section('content')
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-slate-700/20 p-8 md:p-10">
        
        <!-- Brand Badge & Header -->
        <div class="flex items-center gap-3 mb-6">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-sky-600 to-cyan-500 flex items-center justify-center text-white font-extrabold text-xl shadow-lg shadow-sky-500/30">
                KK
            </div>
            <div>
                <h1 class="font-black text-slate-900 text-lg tracking-tight">KK WHOLESALERS</h1>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-widest">Inventory Management ERP</p>
            </div>
        </div>

        <div class="mb-6">
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Sign in to your account</h2>
            <p class="text-xs text-slate-500 mt-1">Enter your credentials below to access the ERP portal.</p>
        </div>

        <!-- Feedback & Error Alerts -->
        @if(session('info'))
            <div class="mb-5 p-3 rounded-lg bg-sky-50 border border-sky-200 text-sky-800 text-xs">
                {{ session('info') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-5 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('login.submit') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                    Email Address
                </label>
                <div class="relative">
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        placeholder="you@kkwholesalers.com"
                        class="w-full px-3.5 py-2.5 rounded-lg border @error('email') border-rose-400 focus:border-rose-500 focus:ring-rose-500/20 @else border-slate-300 focus:border-sky-600 focus:ring-sky-600/20 @enderror text-sm focus:outline-none focus:ring-2 transition-all">
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                    Password
                </label>
                <div class="relative">
                    <input type="password" id="password" name="password" required
                        placeholder="••••••••"
                        class="w-full px-3.5 py-2.5 rounded-lg border @error('password') border-rose-400 focus:border-rose-500 focus:ring-rose-500/20 @else border-slate-300 focus:border-sky-600 focus:ring-sky-600/20 @enderror text-sm focus:outline-none focus:ring-2 transition-all">
                </div>
            </div>

            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center gap-2 text-slate-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded text-sky-600 focus:ring-sky-500 border-slate-300">
                    <span>Remember session</span>
                </label>
            </div>

            <button type="submit"
                class="w-full py-3 px-4 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-sm tracking-wide shadow-md shadow-sky-600/30 transition-all flex items-center justify-center gap-2 mt-2">
                <span>Access ERP Portal</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-100 text-center text-xs text-slate-400">
            KK Wholesalers &copy; {{ date('Y') }} &bull; Monolith ERP
        </div>

    </div>
@endsection