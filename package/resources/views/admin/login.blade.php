@extends('layouts.app')

@section('title', 'Admin Gateway - ' . $settings->hospital_name)

@section('content')
<div class="min-h-screen bg-slate-950 flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl">
        <div class="text-center">
            <div class="mx-auto w-16 h-16 rounded-2xl bg-blue-600 text-white font-black flex items-center justify-center text-3xl shadow-xl shadow-blue-900/30">
                {{ $settings->logo ?? 'M' }}
            </div>
            <h2 class="mt-6 text-2xl font-extrabold text-white tracking-tight">Madhav Netralaya</h2>
            <p class="mt-2 text-xs uppercase tracking-widest text-slate-400 font-bold">Administrator Access Gate</p>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs font-semibold rounded-xl animate-fade-in text-center">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs font-semibold rounded-xl animate-fade-in text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <form class="mt-8 space-y-6" action="{{ route('admin.login.submit') }}" method="POST">
            @csrf
            <div class="rounded-xl shadow-sm space-y-4">
                <div class="space-y-1">
                    <label class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">Admin Username</label>
                    <input id="username" name="username" type="text" required value="{{ old('username') }}" 
                           placeholder="e.g. admin"
                           class="appearance-none relative block w-full px-4 py-3 bg-white/5 border border-white/10 placeholder-slate-500 text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:z-10 text-sm">
                </div>

                <div class="space-y-1">
                    <div class="flex justify-between items-center">
                        <label class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">Admin Password</label>
                        <a href="{{ route('admin.password.request') }}" class="text-[10px] text-blue-400 hover:text-blue-300 font-bold">
                            Forgot Password?
                        </a>
                    </div>
                    <input id="password" name="password" type="password" required placeholder="••••••••"
                           class="appearance-none relative block w-full px-4 py-3 bg-white/5 border border-white/10 placeholder-slate-500 text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:z-10 text-sm">
                </div>
            </div>


            <div>
                <button type="submit" class="group relative w-full flex justify-center py-3.5 px-4 border border-transparent text-xs font-bold uppercase tracking-widest text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 rounded-xl transition shadow-lg shadow-blue-500/20 cursor-pointer">
                    Authenticate Portal
                </button>
            </div>
        </form>

        <div class="text-center pt-2">
            <a href="{{ route('home') }}" class="text-xs text-slate-500 hover:text-slate-400 font-semibold flex items-center justify-center gap-1">
                &larr; Back to Public Website
            </a>
        </div>
    </div>
</div>
@endsection
