@extends('layouts.app')

@section('title', 'Contact Us & Book Appointments - ' . $settings->hospital_name)

@section('content')
<div class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center space-y-3 max-w-2xl mx-auto mb-16">
            <span class="px-2.5 py-1 bg-blue-50 border border-blue-100 text-blue-600 rounded-lg text-[10px] font-extrabold uppercase tracking-wider">
                Get In Touch
            </span>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Contact Madhav Netralaya Hospital</h2>
            <p class="text-xs text-slate-500">
                Need consultations? Call, message, or drop an offline query below for immediate staff help.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            <!-- Left: Contact info -->
            <div class="space-y-8 lg:col-span-1">
                <div class="bg-slate-900 text-white rounded-3xl p-8 space-y-6 shadow-xl relative overflow-hidden">
                    <div class="absolute inset-0 bg-blue-600/10 z-10"></div>
                    <div class="relative z-20 space-y-6">
                        <h3 class="font-extrabold text-md text-white tracking-tight">Nagpur Main Campus</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            {{ $settings->address }}
                        </p>

                        <div class="space-y-4 pt-4 border-t border-white/5 text-xs text-slate-300">
                            <div>
                                <p class="text-[10px] text-slate-400 font-extrabold uppercase mb-1">Telephones</p>
                                @foreach($settings->phone_numbers as $num)
                                    <p class="font-semibold">{{ $num }}</p>
                                @endforeach
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 font-extrabold uppercase mb-1">Enquiry Emails</p>
                                @foreach($settings->emails as $email)
                                    <p class="font-semibold">{{ $email }}</p>
                                @endforeach
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 font-extrabold uppercase mb-1">Clinical OPD Hours</p>
                                <p>Mon - Fri: <span class="font-semibold">{{ $settings->working_hours['weekdays'] ?? '09:00 AM - 08:00 PM' }}</span></p>
                                <p>Saturday: <span class="font-semibold">{{ $settings->working_hours['saturday'] ?? '09:00 AM - 06:00 PM' }}</span></p>
                                <p>Sunday: <span class="font-bold text-rose-400">Closed (Trauma Emergency Only)</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Google Maps Embedded map -->
                @if($settings->google_map_embed_url)
                    <div class="rounded-3xl overflow-hidden border shadow-sm aspect-[4/3] w-full">
                        <iframe class="w-full h-full" src="{{ $settings->google_map_embed_url }}" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                @endif
            </div>

            <!-- Right: Booking Form & Query Form -->
            <div class="lg:col-span-2 space-y-8" id="book">
                <!-- Appointment Booking Card -->
                <div class="bg-white border p-6 md:p-8 rounded-3xl shadow-sm">
                    <h3 class="font-black text-slate-900 text-lg">Request Clinical OPD Appointment</h3>
                    <p class="text-xs text-slate-400 mb-6">Choose your specialty and physician. Our receptionist will call back to finalize.</p>

                    <form action="{{ route('appointments.book') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] uppercase text-slate-400 font-extrabold">Full Name</label>
                                <input type="text" name="patient_name" required placeholder="Dr. Pranav Patel" class="w-full p-2.5 border rounded-xl text-xs bg-slate-50 focus:bg-white transition outline-none">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] uppercase text-slate-400 font-extrabold">Patient Age (Yrs)</label>
                                <input type="number" name="patient_age" required placeholder="42" class="w-full p-2.5 border rounded-xl text-xs bg-slate-50 focus:bg-white transition outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] uppercase text-slate-400 font-extrabold">Gender</label>
                                <select name="patient_gender" required class="w-full p-2.5 border rounded-xl text-xs bg-slate-50 focus:bg-white transition outline-none">
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] uppercase text-slate-400 font-extrabold">Contact Phone</label>
                                <input type="text" name="patient_phone" required placeholder="+91 99887 76655" class="w-full p-2.5 border rounded-xl text-xs bg-slate-50 focus:bg-white transition outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] uppercase text-slate-400 font-extrabold">Specialty Specialities</label>
                                <select name="department_id" required class="w-full p-2.5 border rounded-xl text-xs bg-slate-50 focus:bg-white transition outline-none">
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] uppercase text-slate-400 font-extrabold">Expert Doctor</label>
                                <select name="doctor_id" required class="w-full p-2.5 border rounded-xl text-xs bg-slate-50 focus:bg-white transition outline-none">
                                    @foreach($doctors as $doc)
                                        <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] uppercase text-slate-400 font-extrabold">Date of Visit</label>
                                <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="w-full p-2.5 border rounded-xl text-xs bg-slate-50 focus:bg-white transition outline-none">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] uppercase text-slate-400 font-extrabold">Time Slot</label>
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
                            <label class="text-[10px] uppercase text-slate-400 font-extrabold">Symptoms or reason of consultation</label>
                            <input type="text" name="patient_email" required value="contact@madhavnetralaya.org" class="hidden">
                            <textarea name="reason" rows="3" placeholder="Describe symptoms: cataract, specs removal, retinal flashes..." class="w-full p-2.5 border rounded-xl text-xs bg-slate-50 focus:bg-white transition outline-none"></textarea>
                        </div>

                        <button type="submit" class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-widest rounded-xl transition shadow-lg cursor-pointer">
                            Request OPD Consultation Card
                        </button>
                    </form>
                </div>

                <!-- General enquiry contact form -->
                <div class="bg-white border p-6 md:p-8 rounded-3xl shadow-sm" id="enquiry">
                    <h3 class="font-black text-slate-900 text-lg">Send General Message</h3>
                    <p class="text-xs text-slate-400 mb-6">Need career placements or general inquiries? Send our HR and hospital operations a message.</p>

                    <form action="{{ route('enquiries.submit') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <input type="text" name="name" required placeholder="Full Name" class="w-full p-2.5 border rounded-xl text-xs bg-slate-50 focus:bg-white transition outline-none">
                            <input type="email" name="email" required placeholder="Email Address" class="w-full p-2.5 border rounded-xl text-xs bg-slate-50 focus:bg-white transition outline-none">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <input type="text" name="phone" required placeholder="Phone Number" class="w-full p-2.5 border rounded-xl text-xs bg-slate-50 focus:bg-white transition outline-none">
                            <input type="text" name="subject" required placeholder="Subject e.g. Resume Submission" class="w-full p-2.5 border rounded-xl text-xs bg-slate-50 focus:bg-white transition outline-none">
                        </div>
                        <textarea name="message" required placeholder="Message statement" rows="4" class="w-full p-2.5 border rounded-xl text-xs bg-slate-50 focus:bg-white transition outline-none"></textarea>
                        
                        <button type="submit" class="w-full py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-widest rounded-xl transition cursor-pointer">
                            Submit General Query
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
