@extends('layouts.app')

@section('title', 'Forgot Password - ' . ($settings->hospital_name ?? 'Madhav Netralaya'))

@section('content')
<div class="min-h-screen bg-slate-950 flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl">
        <div class="text-center">
            <div class="mx-auto w-16 h-16 rounded-2xl bg-blue-600 text-white font-black flex items-center justify-center text-3xl shadow-xl shadow-blue-900/30">
                {{ $settings->logo ?? 'M' }}
            </div>
            <h2 class="mt-6 text-2xl font-extrabold text-white tracking-tight">Madhav Netralaya</h2>
            <p class="mt-2 text-xs uppercase tracking-widest text-slate-400 font-bold">Reset Administrator Password</p>
        </div>

        @if(session('status'))
            <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs font-semibold rounded-xl animate-fade-in text-center">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs font-semibold rounded-xl animate-fade-in text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <p class="text-xs text-slate-400 text-center leading-relaxed">
            Enter your registered administrator email address. We will email you a secure, single-use password reset link.
        </p>

        <form class="mt-6 space-y-5" action="{{ route('admin.password.email') }}" method="POST">
            @csrf
            
            <div class="space-y-1">
                <label class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">Registered Administrator Email</label>
                <input id="email" name="email" type="email" required value="{{ old('email') }}" 
                       placeholder="e.g. admin@madhavnetralaya.org"
                       class="appearance-none block w-full px-4 py-3 bg-white/5 border border-white/10 placeholder-slate-500 text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </div>

            <div class="flex gap-4 pt-2">
                <a href="{{ route('admin.login') }}" class="w-1/2 text-center py-3.5 bg-white/5 hover:bg-white/10 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition flex items-center justify-center">
                    Back to Login
                </a>
                <button type="submit" class="w-1/2 py-3.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition shadow-lg shadow-blue-500/20 cursor-pointer flex items-center justify-center">
                    Send Reset Link
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
