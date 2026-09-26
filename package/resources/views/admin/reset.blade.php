<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Administrator Password - {{ $settings->hospital_name ?? 'Madhav Netralaya' }}</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    <!-- Glow Effects -->
    <div class="absolute top-[-20%] left-[-20%] w-[60%] h-[60%] bg-blue-500/10 rounded-full filter blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-20%] right-[-20%] w-[60%] h-[60%] bg-pink-500/10 rounded-full filter blur-[120px] pointer-events-none"></div>

    <div class="max-w-md w-full space-y-6 bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-3xl p-8 shadow-2xl relative z-10">
        <div class="text-center">
            <div class="mx-auto w-16 h-16 rounded-2xl bg-blue-600 text-white font-black flex items-center justify-center text-2xl shadow-xl shadow-blue-900/30">
                {{ $settings->logo ?? 'MN' }}
            </div>
            <h2 class="mt-5 text-xl font-extrabold text-white tracking-wide">Madhav Netralaya</h2>
            <p class="mt-1 text-[10px] uppercase tracking-widest text-slate-400 font-bold">Set New Administrator Password</p>
        </div>

        @if($errors->any())
            <div class="p-3.5 bg-rose-500/20 border border-rose-500/30 text-rose-300 text-xs font-semibold rounded-xl text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="p-3 bg-blue-500/10 border border-blue-500/20 rounded-xl text-center">
            <p class="text-xs text-blue-300 font-medium">Verified Email: <span class="font-bold text-white">{{ $email }}</span></p>
        </div>

        <form class="mt-6 space-y-4" action="{{ route('admin.password.update') }}" method="POST">
            @csrf
            
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="space-y-1">
                <label class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">New Password</label>
                <input id="password" name="password" type="password" required placeholder="At least 6 characters"
                       class="appearance-none block w-full px-4 py-3 bg-white/5 border border-white/10 placeholder-slate-500 text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </div>

            <div class="space-y-1">
                <label class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">Confirm New Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required placeholder="Repeat new password"
                       class="appearance-none block w-full px-4 py-3 bg-white/5 border border-white/10 placeholder-slate-500 text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </div>

            <div class="flex gap-4 pt-3">
                <a href="{{ route('admin.login') }}" class="w-1/2 text-center py-3.5 bg-white/5 hover:bg-white/10 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition flex items-center justify-center">
                    Cancel
                </a>
                <button type="submit" class="w-1/2 py-3.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition shadow-lg shadow-blue-500/20 cursor-pointer flex items-center justify-center">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</body>
</html>
