@extends('layouts.app')

@section('title', 'About Us - ' . $settings->hospital_name)

@section('content')
<!-- Hero Cover -->
<div class="relative bg-slate-950 py-24 text-white text-center">
    <div class="absolute inset-0 bg-gradient-to-b from-slate-900/40 to-slate-950 z-10"></div>
    <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=1600&q=80" class="absolute inset-0 w-full h-full object-cover opacity-35">
    
    <div class="relative z-20 max-w-4xl mx-auto px-4 space-y-4">
        <span class="px-2.5 py-1 bg-blue-600 rounded text-[10px] uppercase font-extrabold tracking-widest">Our Legacy</span>
        <h2 class="text-3xl md:text-5xl font-black tracking-tight leading-tight">About Madhav Netralaya</h2>
        <p class="text-xs text-slate-300 max-w-xl mx-auto leading-relaxed">
            Delivering elite standard ophthalmic care with a compassionate, patient-first approach in Nagpur since our inception.
        </p>
    </div>
</div>

<!-- History & Leadership -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
    <div class="space-y-6">
        <span class="px-2.5 py-1 bg-blue-50 border border-blue-100 text-blue-600 rounded-lg text-[10px] font-extrabold uppercase tracking-wider">
            Our Origin
        </span>
        <h3 class="text-2xl font-black text-slate-900 tracking-tight">Curing Visual Blindness With Advanced Technology</h3>
        <p class="text-xs text-slate-600 leading-relaxed">
            Madhav Netralaya Eye Institute & Research Centre is an advanced tertiary ophthalmic care centre located in Nagpur, Maharashtra. Guided by national healthcare parameters, our hospital operates with a robust mission to deliver high-quality, state-of-the-art diagnostic and surgical eye care to patients from all sections of society.
        </p>
        <p class="text-xs text-slate-600 leading-relaxed">
            Led by visionary ophthalmic leaders and a strong management team of specialists, we have established highly modern, modular Operation Theatres (OTs) with Hepa filters and laminar flow to ensure absolute clinical sterilizations.
        </p>

        <div class="grid grid-cols-3 gap-6 pt-4 text-center">
            <div class="p-4 bg-slate-50 border rounded-2xl">
                <p class="text-xl font-black text-blue-600">25k+</p>
                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-1">OPD Consults</p>
            </div>
            <div class="p-4 bg-slate-50 border rounded-2xl">
                <p class="text-xl font-black text-blue-600">15k+</p>
                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-1">Surgeries</p>
            </div>
            <div class="p-4 bg-slate-50 border rounded-2xl">
                <p class="text-xl font-black text-blue-600">50+</p>
                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-1">Outreach Camps</p>
            </div>
        </div>
    </div>

    <div>
        <img src="https://images.unsplash.com/photo-1581594693702-fbdc51b2763b?auto=format&fit=crop&w=1200&q=80" class="rounded-3xl shadow-xl shadow-slate-100 border">
    </div>
</section>

<!-- Rotary Eye Bank banner -->
<section class="bg-blue-50 border-y border-blue-100/60 py-16" id="eyebank">
    <div class="max-w-4xl mx-auto px-4 text-center space-y-6">
        <span class="px-2.5 py-1 bg-blue-100 text-blue-600 border border-blue-200 rounded text-[10px] font-extrabold uppercase tracking-widest">Noble Vision Crusade</span>
        <h3 class="text-2xl font-black text-slate-900 tracking-tight">Rotary Madhav Eye Bank</h3>
        <p class="text-xs text-slate-600 leading-relaxed max-w-xl mx-auto">
            Our fully licensed eye banking department operates 24/7. Partnering with major international eye collection trusts, our harvest team works tirelessly to restore sight through corneal transplantations (DALK, DSEK, PKP).
        </p>
        <div class="pt-2">
            <a href="{{ route('contact') }}#enquiry" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition shadow-lg">
                Pledge or Donate Eyes Now
            </a>
        </div>
    </div>
</section>

<!-- Gallery Section -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20" id="infrastructure">
    <div class="text-center space-y-3 max-w-2xl mx-auto mb-16">
        <span class="px-2.5 py-1 bg-blue-50 border border-blue-100 text-blue-600 rounded-lg text-[10px] font-extrabold uppercase tracking-wider">
            Visual Gallery
        </span>
        <h3 class="text-2xl font-black text-slate-900 tracking-tight">Our Hospital Infrastructure & Outreach</h3>
        <p class="text-xs text-slate-500">Explore real glimpses of our modern OPD wards, diagnostic rooms, and rural screening camps.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($gallery as $item)
            <div class="bg-white border rounded-3xl overflow-hidden shadow-sm group">
                <div class="aspect-[4/3] w-full overflow-hidden bg-slate-100 relative">
                    <img src="{{ $item->image_url }}" class="w-full h-full object-cover transition duration-500 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/60 to-transparent opacity-0 group-hover:opacity-100 transition duration-300 flex items-end p-4 z-10">
                        <span class="px-2 py-0.5 bg-blue-600 text-white text-[9px] font-extrabold uppercase rounded tracking-wider">
                            {{ ucfirst(str_replace('_', ' ', $item->category)) }}
                        </span>
                    </div>
                </div>
                <div class="p-4 border-t">
                    <h4 class="font-bold text-xs text-slate-800">{{ $item->title }}</h4>
                    <p class="text-[10px] text-slate-400 capitalize mt-0.5">{{ str_replace('_', ' ', $item->category) }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>
@endsection
