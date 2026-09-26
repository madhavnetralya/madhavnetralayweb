@extends('layouts.app')

@section('content')
<!-- Hero Slider Section -->
<div class="relative overflow-hidden bg-slate-950" x-data="{ currentSlide: 0, slidesCount: {{ $sliders->count() }} }">
    <div class="relative h-[480px] md:h-[620px] w-full">
        @foreach($sliders as $index => $slide)
            <div x-show="currentSlide === {{ $index }}" 
                 x-transition:enter="transition ease-out duration-700"
                 x-transition:enter-start="opacity-0 scale-105"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="absolute inset-0 w-full h-full"
                 style="display: {{ $index === 0 ? 'block' : 'none' }};">
                <!-- Background Image with Overlay -->
                <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-900/80 to-transparent z-10"></div>
                <img src="{{ $slide->background_image }}" class="absolute inset-0 w-full h-full object-cover">

                <!-- Slide Content -->
                <div class="absolute inset-0 z-20 flex items-center">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                        <div class="max-w-2xl space-y-6">
                            <span class="px-3 py-1 bg-blue-600 text-white rounded-lg text-[10px] font-extrabold uppercase tracking-widest">
                                NABH Accredited Eye Centre
                            </span>
                            <h2 class="text-3xl md:text-5xl font-black text-white leading-tight tracking-tight">
                                {{ $slide->title }}
                            </h2>
                            <p class="text-sm md:text-md text-slate-300 leading-relaxed">
                                {{ $slide->subtitle }}
                            </p>
                            @if($slide->cta_text)
                                <div class="pt-2">
                                    <a href="{{ $slide->cta_link }}" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-blue-500/20 transition">
                                        {{ $slide->cta_text }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Slider Pagination Dots -->
    @if($sliders->count() > 1)
        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-30 flex gap-2">
            @foreach($sliders as $index => $slide)
                <button @click="currentSlide = {{ $index }}" 
                        :class="currentSlide === {{ $index }} ? 'bg-blue-600 w-8' : 'bg-white/30 hover:bg-white/50 w-2.5'"
                        class="h-2.5 rounded-full transition-all duration-300"></button>
            @endforeach
        </div>
    @endif
</div>

<!-- Specialties & Clinical Departments Section -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="text-center space-y-3 max-w-2xl mx-auto mb-16">
        <span class="px-2.5 py-1 bg-blue-50 border border-blue-100 text-blue-600 rounded-lg text-[10px] font-extrabold uppercase tracking-wider">
            Our Core Competencies
        </span>
        <h3 class="text-3xl font-black text-slate-900 tracking-tight">Advanced Eye Care Specialties</h3>
        <p class="text-xs text-slate-500 leading-relaxed">
            From pediatric squint corrections to complex multi-incision vitreoretinal surgeries, our clinical departments deliver elite-tier sight saving treatments.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($departments as $dept)
            <div class="bg-white border border-slate-100 rounded-3xl p-6 hover:shadow-xl hover:shadow-slate-100 hover:border-slate-200 transition-all duration-300 flex flex-col justify-between">
                <div>
                    <h4 class="text-md font-bold text-slate-900 mb-3">{{ $dept->name }}</h4>
                    <p class="text-xs text-slate-500 leading-relaxed line-clamp-3">
                        {{ $dept->overview }}
                    </p>
                </div>
                <div class="pt-6 border-t border-slate-50 mt-6 flex justify-between items-center">
                    <span class="text-[10px] font-extrabold text-blue-600 uppercase tracking-wider">
                        {{ $dept->doctors->count() }} Doctors
                    </span>
                    <a href="{{ route('departments.show', $dept->id) }}" class="text-xs text-slate-900 font-bold hover:text-blue-600 flex items-center gap-1">
                        Read Specialties &rarr;
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</section>

<!-- Hospital Clinical Facilities Section -->
<section class="bg-slate-900 text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-16">
            <div class="space-y-3 max-w-xl">
                <span class="px-2.5 py-1 bg-white/10 text-blue-400 rounded-lg text-[10px] font-extrabold uppercase tracking-wider">
                    Hospital Infrastructure
                </span>
                <h3 class="text-3xl font-black tracking-tight">World-Class Diagnostics & Facilities</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Committed to safe, sterile, and advanced tertiary operations using global benchmark standards.
                </p>
            </div>
            <a href="{{ route('about') }}#infrastructure" class="px-5 py-3 bg-white/5 hover:bg-white/10 border border-white/10 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition">
                View Infrastructure
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            @foreach($facilities as $fac)
                <div class="bg-slate-950 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl flex flex-col justify-between">
                    <img src="{{ $fac->image }}" class="w-full h-40 object-cover opacity-80 hover:opacity-100 transition">
                    <div class="p-6">
                        <h4 class="font-bold text-sm text-white mb-2">{{ $fac->name }}</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            {{ $fac->description }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="text-center space-y-3 max-w-2xl mx-auto mb-16">
        <span class="px-2.5 py-1 bg-blue-50 border border-blue-100 text-blue-600 rounded-lg text-[10px] font-extrabold uppercase tracking-wider">
            Patient Stories
        </span>
        <h3 class="text-3xl font-black text-slate-900 tracking-tight">Curing Visual Blindness: What Patients Say</h3>
        <p class="text-xs text-slate-500">Read verified video and photo testimonial success reviews from Nagpur.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @foreach($testimonials as $test)
            <div class="bg-white border border-slate-100 p-6 rounded-3xl hover:shadow-xl hover:shadow-slate-100 transition duration-300">
                <div class="flex items-center gap-4 mb-4">
                    @if($test->photo)
                        <img src="{{ $test->photo }}" class="w-12 h-12 rounded-full object-cover">
                    @else
                        <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 font-bold flex items-center justify-center text-sm">
                            {{ substr($test->patient_name, 0, 2) }}
                        </div>
                    @endif
                    <div>
                        <h4 class="font-extrabold text-sm text-slate-900">{{ $test->patient_name }}</h4>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ $test->treatment }} (Age: {{ $test->age }} Yrs)</p>
                    </div>
                </div>

                @if($test->video_url)
                    <div class="aspect-video w-full rounded-2xl overflow-hidden bg-slate-100 border mb-4 shadow-sm">
                        <iframe class="w-full h-full" src="{{ $test->video_url }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                @endif

                <p class="text-xs text-slate-600 italic leading-relaxed">
                    "{{ $test->comment }}"
                </p>
            </div>
        @endforeach
    </div>
</section>

<!-- Active Educational Articles & Blogs Section -->
<section class="bg-slate-50 py-20 border-t border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center space-y-3 max-w-2xl mx-auto mb-16">
            <span class="px-2.5 py-1 bg-blue-50 border border-blue-100 text-blue-600 rounded-lg text-[10px] font-extrabold uppercase tracking-wider">
                Daily Wellness Bulletin
            </span>
            <h3 class="text-3xl font-black text-slate-900 tracking-tight">Ocular Health Resources & Blogs</h3>
            <p class="text-xs text-slate-500">Expert health recommendations from Nagpur's premium eye specialists.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($blogs as $blog)
                <div class="bg-white border border-slate-100 rounded-3xl overflow-hidden hover:shadow-xl transition duration-300 flex flex-col justify-between">
                    <div>
                        <img src="{{ $blog->featured_image }}" class="w-full h-48 object-cover">
                        <div class="p-6">
                            <span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-[10px] font-extrabold uppercase tracking-wider rounded">
                                {{ $blog->category->name ?? 'Default' }}
                            </span>
                            <h4 class="font-extrabold text-md text-slate-900 tracking-tight mt-3 mb-2 leading-snug line-clamp-2">
                                <a href="{{ route('blogs.show', $blog->slug) }}" class="hover:text-blue-600">{{ $blog->title }}</a>
                            </h4>
                            <p class="text-[11px] text-slate-400 font-bold mb-4">By {{ $blog->author }} | {{ $blog->published_at ? $blog->published_at->format('M d, Y') : '' }}</p>
                            <p class="text-xs text-slate-500 leading-relaxed line-clamp-3">
                                {!! strip_tags($blog->content) !!}
                            </p>
                        </div>
                    </div>
                    <div class="px-6 pb-6 border-t border-slate-50 pt-4 bg-slate-50/20">
                        <a href="{{ route('blogs.show', $blog->slug) }}" class="text-xs text-blue-600 font-bold hover:underline">
                            Read Full Article &rarr;
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Call To Action Booking Block -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20" id="notices">
    <div class="bg-blue-600 rounded-[36px] p-8 md:p-12 text-white shadow-2xl relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-700 to-indigo-800 opacity-90 z-10"></div>
        
        <div class="relative z-20 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="space-y-6">
                <span class="bg-white/10 px-3 py-1 rounded text-[10px] uppercase font-bold tracking-widest border border-white/10">
                    Outreach & Events
                </span>
                <h3 class="text-3xl md:text-4xl font-black leading-tight">Join Our Community Eye Camps</h3>
                <p class="text-xs text-blue-100 leading-relaxed">
                    As part of our mission to fight blindness, we organize periodic free screenings and camps across Nagpur rural zones. Check out upcoming campaigns and register.
                </p>

                <div class="space-y-4 pt-2">
                    @foreach($events as $event)
                        <div class="p-4 bg-white/5 border border-white/10 rounded-2xl">
                            <h4 class="font-bold text-sm text-white">{{ $event->title }}</h4>
                            <p class="text-[10px] text-blue-200 mt-1">Date: {{ $event->date->format('M d, Y') }} | Location: {{ $event->location }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Fast Appointment Booking box -->
            <div class="bg-white rounded-3xl p-6 md:p-8 text-slate-800 shadow-xl">
                <h4 class="font-extrabold text-lg text-slate-900 tracking-tight">Request OPD Appointment</h4>
                <p class="text-[11px] text-slate-400 mb-6">Choose specialty and doctor for diagnostic visits.</p>

                <form action="{{ route('appointments.book') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] uppercase text-slate-400 font-extrabold">Full Name</label>
                            <input type="text" name="patient_name" required placeholder="Gopal Sharma" class="w-full p-2.5 border rounded-xl text-xs bg-slate-50 focus:bg-white transition outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] uppercase text-slate-400 font-extrabold">Age (Yrs)</label>
                            <input type="number" name="patient_age" required placeholder="54" class="w-full p-2.5 border rounded-xl text-xs bg-slate-50 focus:bg-white transition outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] uppercase text-slate-400 font-extrabold">Gender</label>
                            <select name="patient_gender" required class="w-full p-2.5 border rounded-xl text-xs bg-slate-50 focus:bg-white transition outline-none">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] uppercase text-slate-400 font-extrabold">Phone Number</label>
                            <input type="text" name="patient_phone" required placeholder="+91 99887 76655" class="w-full p-2.5 border rounded-xl text-xs bg-slate-50 focus:bg-white transition outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] uppercase text-slate-400 font-extrabold">Specialty Specialty</label>
                            <select name="department_id" required class="w-full p-2.5 border rounded-xl text-xs bg-slate-50 focus:bg-white transition outline-none">
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] uppercase text-slate-400 font-extrabold">Select Expert Doctor</label>
                            <select name="doctor_id" required class="w-full p-2.5 border rounded-xl text-xs bg-slate-50 focus:bg-white transition outline-none">
                                @foreach($doctors as $doc)
                                    <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] uppercase text-slate-400 font-extrabold">Consultation Date</label>
                            <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="w-full p-2.5 border rounded-xl text-xs bg-slate-50 focus:bg-white transition outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] uppercase text-slate-400 font-extrabold">Select Time Slot</label>
                            <select name="time_slot" required class="w-full p-2.5 border rounded-xl text-xs bg-slate-50 focus:bg-white transition outline-none">
                                <option value="10:00 AM - 11:00 AM">10:00 AM - 11:00 AM</option>
                                <option value="11:00 AM - 12:00 PM">11:00 AM - 12:00 PM</option>
                                <option value="12:00 PM - 01:00 PM">12:00 PM - 01:00 PM</option>
                                <option value="04:00 PM - 05:00 PM">04:00 PM - 05:00 PM</option>
                                <option value="05:00 PM - 06:00 PM">05:00 PM - 06:00 PM</option>
                            </select>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] uppercase text-slate-400 font-extrabold">Reason or Symptoms Description</label>
                        <input type="text" name="patient_email" required value="info@madhavnetralaya.org" class="hidden">
                        <textarea name="reason" rows="2" placeholder="Describe eye problem, cataract, or laser queries" class="w-full p-2.5 border rounded-xl text-xs bg-slate-50 focus:bg-white transition outline-none"></textarea>
                    </div>

                    <button type="submit" class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-widest rounded-xl transition shadow-lg shadow-blue-500/10 cursor-pointer mt-2">
                        Submit Appointment Request
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
