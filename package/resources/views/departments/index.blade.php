@extends('layouts.app')

@section('title', 'Clinical Specialties - ' . $settings->hospital_name)

@section('content')
<div class="py-16 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center space-y-3 max-w-2xl mx-auto mb-16">
            <span class="px-2.5 py-1 bg-blue-50 border border-blue-100 text-blue-600 rounded-lg text-[10px] font-extrabold uppercase tracking-wider">
                Clinics & Specialties
            </span>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Comprehensive Tertiary Eye Services</h2>
            <p class="text-xs text-slate-500">
                Guiding clinical restoration across major eye structures with elite diagnostic machinery and modular operation rooms.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($departments as $dept)
                <div class="bg-white border rounded-[32px] overflow-hidden shadow-sm hover:shadow-xl transition duration-300 flex flex-col justify-between">
                    <div>
                        <img src="{{ $dept->banner_image ?? 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=600&q=80' }}" class="w-full h-48 object-cover">
                        <div class="p-6 space-y-3">
                            <h3 class="font-extrabold text-slate-950 text-md">{{ $dept->name }}</h3>
                            <p class="text-xs text-slate-500 leading-relaxed line-clamp-4">
                                {{ $dept->overview }}
                            </p>
                        </div>
                    </div>

                    <div class="p-6 border-t bg-slate-50/20 flex justify-between items-center mt-6">
                        <span class="text-[10px] font-extrabold text-blue-600 uppercase tracking-wider">
                            {{ $dept->doctors->count() }} Eye Specialists
                        </span>
                        <a href="{{ route('departments.show', $dept->id) }}" class="text-xs text-slate-950 font-bold hover:text-blue-600">
                            Explore Specialty &rarr;
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
