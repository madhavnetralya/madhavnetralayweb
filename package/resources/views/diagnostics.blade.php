@extends('layouts.app')

@section('title', 'Advanced Ocular Diagnostics - ' . $settings->hospital_name)

@section('content')
<div class="py-16 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center space-y-3 max-w-2xl mx-auto mb-16">
            <span class="px-2.5 py-1 bg-blue-50 border border-blue-100 text-blue-600 rounded-lg text-[10px] font-extrabold uppercase tracking-wider">
                Ocular Diagnostics
            </span>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Advanced Eye Clinical Testing</h2>
            <p class="text-xs text-slate-500">
                Highly modern digital imaging and computerized optic diagnostic procedures to detect eye issues early.
            </p>
        </div>

        <!-- Diagnostic Cards -->
        <div class="space-y-12">
            @foreach($diagnostics as $index => $diag)
                <div class="bg-white border rounded-[32px] overflow-hidden p-6 md:p-8 shadow-sm flex flex-col {{ $index % 2 === 1 ? 'md:flex-row-reverse' : 'md:flex-row' }} gap-10 items-center">
                    <div class="w-full md:w-1/2">
                        <img src="{{ $diag->image }}" class="w-full h-72 object-cover rounded-2xl border">
                    </div>

                    <div class="w-full md:w-1/2 space-y-6">
                        <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">{{ $diag->name }}</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">{{ $diag->description }}</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-50">
                            <div>
                                <h4 class="text-[10px] uppercase font-extrabold text-blue-600 tracking-wider mb-2">Key Indications:</h4>
                                <ul class="space-y-1.5 text-xs text-slate-500">
                                    @foreach($diag->indications as $ind)
                                        <li class="flex items-start gap-1.5">&bull; <span>{{ $ind }}</span></li>
                                    @endforeach
                                </ul>
                            </div>
                            
                            <div>
                                <h4 class="text-[10px] uppercase font-extrabold text-teal-600 tracking-wider mb-2">Patient Benefits:</h4>
                                <p class="text-xs text-slate-500 leading-relaxed">
                                    {{ $diag->procedure }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
