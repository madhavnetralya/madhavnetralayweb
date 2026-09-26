@extends('layouts.app')

@section('title', 'Meet Our Eye Specialists - ' . $settings->hospital_name)

@section('content')
<div class="bg-slate-50 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center space-y-3 max-w-2xl mx-auto mb-16">
            <span class="px-2.5 py-1 bg-blue-50 border border-blue-100 text-blue-600 rounded-lg text-[10px] font-extrabold uppercase tracking-wider">
                Clinical Experts
            </span>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Nagpur's Renowned Ophthalmologists</h2>
            <p class="text-xs text-slate-500">
                Highly qualified micro-surgeons, Vitreoretinal specialists, and pediatric corneal coordinators.
            </p>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white border rounded-2xl p-4 shadow-sm mb-12">
            <form action="{{ route('doctors') }}" method="GET" class="flex flex-col md:flex-row gap-4 justify-between">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search specialists by name, qualification, or specialty..."
                           class="w-full p-2.5 border border-slate-200 rounded-xl text-xs bg-slate-50/50 outline-none focus:bg-white focus:border-blue-400 transition">
                </div>

                <div class="flex gap-4">
                    <select name="department" class="p-2.5 border border-slate-200 rounded-xl text-xs bg-slate-50/50 outline-none focus:bg-white transition font-semibold text-slate-600">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department') === $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition cursor-pointer">
                        Filter List
                    </button>
                </div>
            </form>
        </div>

        <!-- Doctors Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            @forelse($doctors as $doc)
                <div class="bg-white border border-slate-100 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl hover:border-slate-200 transition duration-300 flex flex-col justify-between">
                    <div>
                        <div class="aspect-square bg-slate-100 relative">
                            <img src="{{ $doc->photo }}" class="w-full h-full object-cover">
                        </div>
                        <div class="p-5 space-y-2">
                            <span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-[9px] font-extrabold uppercase tracking-wider rounded">
                                {{ $doc->department->name ?? 'Specialist' }}
                            </span>
                            <h3 class="font-extrabold text-sm text-slate-950 leading-tight mt-1">{{ $doc->name }}</h3>
                            <p class="text-[10px] text-slate-500 font-bold leading-relaxed">{{ $doc->qualification }}</p>
                            
                            <div class="pt-2 text-xs text-slate-600 leading-relaxed border-t border-slate-50">
                                <span class="font-extrabold text-slate-800 text-[10px] uppercase">Specialty:</span>
                                <p class="mt-0.5 text-[11px]">{{ $doc->specialty }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 border-t border-slate-50 bg-slate-50/20 text-[11px]">
                        <p class="font-bold text-slate-800">OPD Consultation timings:</p>
                        <p class="text-slate-500 mt-1">Days: <span class="font-semibold text-slate-700">{{ implode(', ', $doc->consultation_timing['days'] ?? []) }}</span></p>
                        <p class="text-slate-500 mt-0.5">Hours: <span class="font-semibold text-slate-700">{{ $doc->consultation_timing['time'] ?? 'As Scheduled' }}</span></p>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-12 text-center text-slate-400 text-xs font-semibold">No medical specialists found matching your search.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
