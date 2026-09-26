@extends('layouts.app')

@section('title', $department->name . ' - ' . $settings->hospital_name)

@section('content')
<!-- Cover banner -->
<div class="relative bg-slate-950 py-24 text-white">
    <div class="absolute inset-0 bg-gradient-to-r from-slate-900 via-slate-900/80 to-transparent z-10"></div>
    <img src="{{ $department->banner_image ?? 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=1200&q=80' }}" class="absolute inset-0 w-full h-full object-cover opacity-30">
    
    <div class="relative z-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        <span class="px-2.5 py-1 bg-blue-600 rounded text-[9px] uppercase font-extrabold tracking-widest">Ophthalmic Wing</span>
        <h2 class="text-3xl md:text-5xl font-black tracking-tight leading-tight">{{ $department->name }}</h2>
        <p class="text-xs text-slate-300 max-w-xl leading-relaxed">
            Leading-edge surgical and medical treatments utilizing sophisticated technologies and custom treatments.
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 grid grid-cols-1 lg:grid-cols-3 gap-12">
    <!-- Center panel: details -->
    <div class="lg:col-span-2 space-y-12">
        <div class="space-y-4">
            <h3 class="text-xl font-extrabold text-slate-950">Clinical Overview</h3>
            <p class="text-xs text-slate-600 leading-relaxed">
                {{ $department->overview }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Symptoms checklist -->
            @if(!empty($department->symptoms))
                <div class="p-6 bg-slate-50 border border-slate-100 rounded-3xl space-y-3">
                    <h4 class="text-xs uppercase font-extrabold text-rose-600 tracking-wider">Symptoms To Watch:</h4>
                    <ul class="space-y-2 text-xs text-slate-600">
                        @foreach($department->symptoms as $sym)
                            <li class="flex items-start gap-1.5">&bull; <span>{{ $sym }}</span></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Diagnosis checklist -->
            @if(!empty($department->diagnosis))
                <div class="p-6 bg-slate-50 border border-slate-100 rounded-3xl space-y-3">
                    <h4 class="text-xs uppercase font-extrabold text-blue-600 tracking-wider">Diagnostic Tests:</h4>
                    <ul class="space-y-2 text-xs text-slate-600">
                        @foreach($department->diagnosis as $diag)
                            <li class="flex items-start gap-1.5">&bull; <span>{{ $diag }}</span></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4">
            <!-- Treatments list -->
            @if(!empty($department->treatments))
                <div class="space-y-4">
                    <h4 class="text-xs uppercase font-extrabold text-slate-900 tracking-wider">Treatments & Procedures:</h4>
                    <ul class="space-y-3 text-xs text-slate-600">
                        @foreach($department->treatments as $treat)
                            <li class="flex items-start gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600 mt-1.5 shrink-0"></span>
                                <span>{{ $treat }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Technology list -->
            @if(!empty($department->technology))
                <div class="space-y-4">
                    <h4 class="text-xs uppercase font-extrabold text-slate-900 tracking-wider">Clinical Technology & Lasers:</h4>
                    <ul class="space-y-3 text-xs text-slate-600">
                        @foreach($department->technology as $tech)
                            <li class="flex items-start gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-teal-600 mt-1.5 shrink-0"></span>
                                <span>{{ $tech }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

    <!-- Right panel: doctors in department & appointment shortcut -->
    <div class="lg:col-span-1 space-y-8">
        <!-- Doctors list -->
        <div class="border border-slate-150 p-6 rounded-3xl bg-white space-y-4">
            <h4 class="font-extrabold text-sm text-slate-950">Specialty Doctors</h4>
            <div class="space-y-4">
                @forelse($doctors as $doc)
                    <div class="flex items-center gap-3">
                        <img src="{{ $doc->photo }}" class="w-10 h-10 rounded-full object-cover">
                        <div>
                            <h5 class="font-bold text-xs text-slate-900">{{ $doc->name }}</h5>
                            <p class="text-[10px] text-slate-500 font-medium">{{ $doc->qualification }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 font-medium">Consultants roster scheduled shortly.</p>
                @endforelse
            </div>
        </div>

        <!-- Appointment Card -->
        <div class="bg-blue-600 text-white p-6 rounded-3xl space-y-4">
            <h4 class="font-extrabold text-md">Request Consult</h4>
            <p class="text-xs text-blue-100 leading-relaxed">
                Click below to book a slot for a comprehensive screening or vision test.
            </p>
            <a href="{{ route('contact') }}#book" class="block w-full text-center py-2.5 bg-white text-blue-600 font-bold text-xs uppercase tracking-wider rounded-xl hover:bg-blue-50 transition">
                Book Appointment
            </a>
        </div>
    </div>
</div>
@endsection
