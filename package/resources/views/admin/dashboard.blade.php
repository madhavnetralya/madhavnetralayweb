@extends('layouts.app')

@section('title', 'Admin Dashboard - ' . $settings->hospital_name)

@section('content')
<div class="min-h-screen bg-slate-50/50 py-10" x-data="{ activeTab: 'appointments' }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center pb-8 border-b border-slate-200 gap-4">
            <div>
                <span class="px-2.5 py-1 bg-blue-50 border border-blue-100 text-blue-600 rounded-lg text-[10px] font-extrabold uppercase tracking-wider">
                    Staff Management Panel
                </span>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Hospital Control Portal</h2>
                <p class="text-xs text-slate-500 mt-0.5">Welcome, <span class="font-semibold text-slate-700">{{ Auth::user()->name }}</span> ({{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }})</p>
            </div>
            
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl text-xs font-bold transition flex items-center gap-1.5 border border-rose-100 shadow-sm cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Terminate Session
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 mt-10">
            <!-- Navigation Sidebar -->
            <div class="space-y-2">
                <h3 class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest px-4">Workspace Navigation</h3>
                <nav class="space-y-1">
                    <button @click="activeTab = 'appointments'" 
                            :class="activeTab === 'appointments' ? 'bg-white text-blue-600 shadow-sm border border-slate-150' : 'text-slate-600 hover:bg-white/50'"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition w-full text-left">
                        <span class="w-2 h-2 rounded-full" :class="activeTab === 'appointments' ? 'bg-blue-600' : 'bg-slate-300'"></span>
                        Appointments ({{ $appointments->count() }})
                    </button>
                    
                    <button @click="activeTab = 'enquiries'" 
                            :class="activeTab === 'enquiries' ? 'bg-white text-blue-600 shadow-sm border border-slate-150' : 'text-slate-600 hover:bg-white/50'"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition w-full text-left">
                        <span class="w-2 h-2 rounded-full" :class="activeTab === 'enquiries' ? 'bg-blue-600' : 'bg-slate-300'"></span>
                        Enquiries ({{ $enquiries->where('status', 'unread')->count() }} Unread)
                    </button>

                    <button @click="activeTab = 'doctors'" 
                            :class="activeTab === 'doctors' ? 'bg-white text-blue-600 shadow-sm border border-slate-150' : 'text-slate-600 hover:bg-white/50'"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition w-full text-left">
                        <span class="w-2 h-2 rounded-full" :class="activeTab === 'doctors' ? 'bg-blue-600' : 'bg-slate-300'"></span>
                        Eye Specialists ({{ $doctors->count() }})
                    </button>

                    <button @click="activeTab = 'departments'" 
                            :class="activeTab === 'departments' ? 'bg-white text-blue-600 shadow-sm border border-slate-150' : 'text-slate-600 hover:bg-white/50'"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition w-full text-left">
                        <span class="w-2 h-2 rounded-full" :class="activeTab === 'departments' ? 'bg-blue-600' : 'bg-slate-300'"></span>
                        Clinics & Specialties
                    </button>

                    <button @click="activeTab = 'blogs'" 
                            :class="activeTab === 'blogs' ? 'bg-white text-blue-600 shadow-sm border border-slate-150' : 'text-slate-600 hover:bg-white/50'"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition w-full text-left">
                        <span class="w-2 h-2 rounded-full" :class="activeTab === 'blogs' ? 'bg-blue-600' : 'bg-slate-300'"></span>
                        Articles & Blogs ({{ $blogs->count() }})
                    </button>

                    <button @click="activeTab = 'notices'" 
                            :class="activeTab === 'notices' ? 'bg-white text-blue-600 shadow-sm border border-slate-150' : 'text-slate-600 hover:bg-white/50'"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition w-full text-left">
                        <span class="w-2 h-2 rounded-full" :class="activeTab === 'notices' ? 'bg-blue-600' : 'bg-slate-300'"></span>
                        Bulletin Board ({{ $notices->count() }})
                    </button>

                    <button @click="activeTab = 'settings'" 
                            :class="activeTab === 'settings' ? 'bg-white text-blue-600 shadow-sm border border-slate-150' : 'text-slate-600 hover:bg-white/50'"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition w-full text-left">
                        <span class="w-2 h-2 rounded-full" :class="activeTab === 'settings' ? 'bg-blue-600' : 'bg-slate-300'"></span>
                        General Settings
                    </button>

                    <button @click="activeTab = 'security'" 
                            :class="activeTab === 'security' ? 'bg-white text-blue-600 shadow-sm border border-slate-150' : 'text-slate-600 hover:bg-white/50'"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition w-full text-left">
                        <span class="w-2 h-2 rounded-full" :class="activeTab === 'security' ? 'bg-blue-600' : 'bg-slate-300'"></span>
                        Admin Passwords
                    </button>
                </nav>
            </div>

            <!-- Tab Contents -->
            <div class="lg:col-span-3 bg-white border border-slate-200 rounded-3xl p-6 shadow-sm min-h-[500px]">
                
                <!-- Tab: Appointments -->
                <div x-show="activeTab === 'appointments'" class="space-y-6">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-950">OPD Consultation Bookings</h3>
                        <p class="text-xs text-slate-500">Track and manage patient appointment requests below.</p>
                    </div>

                    <div class="divide-y divide-slate-100 border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                        @forelse($appointments as $app)
                            <div class="p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-slate-50/50 transition">
                                <div>
                                    <div class="font-bold text-slate-800 text-sm flex items-center gap-2">
                                        {{ $app->patient_name }} 
                                        <span class="px-2 py-0.5 bg-slate-100 text-[10px] text-slate-600 rounded font-medium capitalize">{{ $app->patient_gender }}, {{ $app->patient_age }} Yrs</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-1 space-y-0.5">
                                        <p>Contact: <span class="font-medium text-slate-700">{{ $app->patient_phone }}</span> | <span class="font-medium text-slate-700">{{ $app->patient_email }}</span></p>
                                        <p>Consulting: <span class="font-medium text-blue-600">{{ $app->doctor->name ?? 'Default' }}</span> ({{ $app->department->name ?? 'Default' }})</p>
                                        <p>Date & Time: <span class="font-bold text-slate-700">{{ $app->date->format('Y-m-d') }}</span> at <span class="font-bold text-slate-700">{{ $app->time_slot }}</span></p>
                                        @if($app->reason)
                                            <p class="italic text-slate-400 mt-1">"{{ $app->reason }}"</p>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg border {{ 
                                        $app->status === 'confirmed' ? 'bg-emerald-50 border-emerald-200 text-emerald-600' : 
                                        ($app->status === 'cancelled' ? 'bg-rose-50 border-rose-200 text-rose-600' : 'bg-amber-50 border-amber-200 text-amber-600')
                                    }}">
                                        {{ $app->status }}
                                    </span>
                                    
                                    <div class="flex gap-1" x-data="{ openMenu: false }">
                                        <form action="{{ route('admin.appointments.status', $app->id) }}" method="POST">
                                            @csrf
                                            <select name="status" onchange="this.form.submit()" class="text-xs bg-slate-50 border border-slate-200 p-1.5 rounded-lg font-bold outline-none">
                                                <option value="pending" {{ $app->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="confirmed" {{ $app->status === 'confirmed' ? 'selected' : '' }}>Confirm</option>
                                                <option value="cancelled" {{ $app->status === 'cancelled' ? 'selected' : '' }}>Cancel</option>
                                            </select>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-slate-400 text-xs font-semibold">No appointments registered.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Tab: Enquiries -->
                <div x-show="activeTab === 'enquiries'" class="space-y-6">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-950">Patient Queries & Contact Enquiries</h3>
                        <p class="text-xs text-slate-500">Contact form submissions received from website.</p>
                    </div>

                    <div class="space-y-4">
                        @forelse($enquiries as $enq)
                            <div class="p-4 border rounded-2xl bg-slate-50/30 flex flex-col justify-between gap-2">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-extrabold text-slate-800 text-sm">{{ $enq->subject }}</h4>
                                        <p class="text-[11px] text-slate-500">Submitted by: <span class="font-semibold text-slate-700">{{ $enq->name }}</span> ({{ $enq->phone }} / {{ $enq->email }})</p>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider {{ $enq->status === 'unread' ? 'bg-amber-50 text-amber-600 border border-amber-200' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $enq->status }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 mt-2 italic bg-white p-3 rounded-xl border border-slate-100">"{{ $enq->message }}"</p>
                                
                                <div class="flex justify-end pt-2">
                                    <form action="{{ route('admin.enquiries.status', $enq->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="{{ $enq->status === 'unread' ? 'read' : 'unread' }}">
                                        <button type="submit" class="text-[11px] font-bold text-blue-600 hover:underline">
                                            Mark as {{ $enq->status === 'unread' ? 'Read' : 'Unread' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-slate-400 text-xs font-semibold">No queries submitted.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Tab: Doctors -->
                <div x-show="activeTab === 'doctors'" class="space-y-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-950">Medical Eye Specialists</h3>
                            <p class="text-xs text-slate-500">Add, edit, or remove hospital clinical doctors.</p>
                        </div>
                    </div>

                    <form action="{{ route('admin.doctors.store') }}" method="POST" class="bg-slate-50 p-4 rounded-2xl space-y-3.5 border">
                        @csrf
                        <h4 class="text-xs font-bold text-slate-800 uppercase">Register New Doctor</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                            <input type="text" name="name" required placeholder="Doctor Name" class="p-2 border rounded-xl text-xs bg-white">
                            <input type="text" name="qualification" required placeholder="Qualification" class="p-2 border rounded-xl text-xs bg-white">
                            <input type="number" name="experience" required placeholder="Experience (Yrs)" class="p-2 border rounded-xl text-xs bg-white">
                            <input type="text" name="specialty" required placeholder="Specialty description" class="p-2 border rounded-xl text-xs bg-white">
                            
                            <select name="department_id" required class="p-2 border rounded-xl text-xs bg-white">
                                <option value="">Select Department Specialty</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>

                            <input type="text" name="languages" placeholder="Languages (comma separated)" class="p-2 border rounded-xl text-xs bg-white">
                            <input type="text" name="days" placeholder="Consulting Days (comma separated)" class="p-2 border rounded-xl text-xs bg-white">
                            <input type="text" name="time" placeholder="Consulting hours timing e.g. 10 AM - 1 PM" class="p-2 border rounded-xl text-xs bg-white">
                        </div>
                        <textarea name="biography" placeholder="Doctor profile biography statement" rows="2" class="w-full p-2 border rounded-xl text-xs bg-white"></textarea>
                        
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700 cursor-pointer">
                            Register Doctor Profile
                        </button>
                    </form>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4">
                        @foreach($doctors as $doc)
                            <div class="p-4 border rounded-2xl flex gap-3.5 items-center">
                                <img src="{{ $doc->photo }}" class="w-12 h-12 rounded-full object-cover">
                                <div>
                                    <h4 class="font-extrabold text-sm text-slate-800">{{ $doc->name }}</h4>
                                    <p class="text-[10px] text-slate-500 font-medium">{{ $doc->specialty }}</p>
                                    <p class="text-[10px] text-slate-400 font-bold mt-0.5">{{ $doc->department->name ?? 'None' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Tab: Departments -->
                <div x-show="activeTab === 'departments'" class="space-y-6">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-950">Clinical Specialties</h3>
                        <p class="text-xs text-slate-500">Configure core eye-care hospital specialties.</p>
                    </div>

                    <form action="{{ route('admin.departments.store') }}" method="POST" class="bg-slate-50 p-4 rounded-2xl space-y-3.5 border">
                        @csrf
                        <h4 class="text-xs font-bold text-slate-800 uppercase">Create New Specialty Clinic</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                            <input type="text" name="id" required placeholder="Unique Code / slug e.g. retina" class="p-2 border rounded-xl text-xs bg-white">
                            <input type="text" name="name" required placeholder="Department Name" class="p-2 border rounded-xl text-xs bg-white">
                        </div>
                        <textarea name="overview" required placeholder="Overview description content" rows="2" class="w-full p-2 border rounded-xl text-xs bg-white"></textarea>
                        <textarea name="symptoms" placeholder="Symptoms list (one per line)" rows="2" class="w-full p-2 border rounded-xl text-xs bg-white"></textarea>
                        
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700 cursor-pointer">
                            Add Department Specialty
                        </button>
                    </form>

                    <div class="space-y-3 pt-4">
                        @foreach($departments as $dept)
                            <div class="p-3 border rounded-xl flex justify-between items-center">
                                <div>
                                    <h4 class="font-bold text-slate-800 text-xs">{{ $dept->name }}</h4>
                                    <p class="text-[10px] text-slate-400">ID: {{ $dept->id }} | Doctors: {{ $dept->doctors->count() }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Tab: Blogs -->
                <div x-show="activeTab === 'blogs'" class="space-y-6">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-950">Hospital Blog Articles</h3>
                        <p class="text-xs text-slate-500">Draft and publish educational patient articles.</p>
                    </div>

                    <form action="{{ route('admin.blogs.store') }}" method="POST" class="bg-slate-50 p-4 rounded-2xl space-y-3.5 border">
                        @csrf
                        <h4 class="text-xs font-bold text-slate-800 uppercase">Draft Blog Post</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                            <input type="text" name="title" required placeholder="Post Title" class="p-2 border rounded-xl text-xs bg-white">
                            <input type="text" name="author" required placeholder="Author Name" class="p-2 border rounded-xl text-xs bg-white">
                            
                            <select name="category_id" required class="p-2 border rounded-xl text-xs bg-white">
                                <option value="">Select Category</option>
                                <option value="cat1">Daily Wellness</option>
                                <option value="cat2">Clinical Insights</option>
                            </select>
                            
                            <input type="text" name="tags" placeholder="Tags (comma separated)" class="p-2 border rounded-xl text-xs bg-white">
                        </div>
                        <textarea name="content" required placeholder="Blog HTML or text content" rows="4" class="w-full p-2 border rounded-xl text-xs bg-white"></textarea>
                        
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700 cursor-pointer">
                            Publish Article Now
                        </button>
                    </form>
                </div>

                <!-- Tab: Notices -->
                <div x-show="activeTab === 'notices'" class="space-y-6">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-950">Bulletin Board Announcement Notices</h3>
                        <p class="text-xs text-slate-500">Push real-time alerts or notifications dynamically on top of layout pages.</p>
                    </div>

                    <form action="{{ route('admin.notices.store') }}" method="POST" class="bg-slate-50 p-4 rounded-2xl space-y-3.5 border">
                        @csrf
                        <h4 class="text-xs font-bold text-slate-800 uppercase">Add Announcement</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                            <input type="text" name="title" required placeholder="Notice Title" class="p-2 border rounded-xl text-xs bg-white">
                            <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="p-2 border rounded-xl text-xs bg-white">
                            
                            <select name="category" required class="p-2 border rounded-xl text-xs bg-white">
                                <option value="general">General Notification</option>
                                <option value="camp">Community Eye Camp</option>
                                <option value="holiday">OPD Holiday</option>
                            </select>

                            <div class="flex items-center gap-2 px-2">
                                <input type="checkbox" name="important" id="important" class="rounded">
                                <label for="important" class="text-xs text-slate-600 font-bold">Highlight as Urgent Alert</label>
                            </div>
                        </div>
                        <textarea name="content" required placeholder="Notice banner details" rows="2" class="w-full p-2 border rounded-xl text-xs bg-white"></textarea>
                        
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700 cursor-pointer">
                            Publish Notice Bulletin
                        </button>
                    </form>
                </div>

                <!-- Tab: Settings -->
                <div x-show="activeTab === 'settings'" class="space-y-6">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-950">General Hospital Settings</h3>
                        <p class="text-xs text-slate-500">Modify address, phone coordinates, themes dynamically.</p>
                    </div>

                    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] uppercase text-slate-500 font-bold">Hospital Brand Name</label>
                                <input type="text" name="hospital_name" value="{{ $settings->hospital_name }}" class="w-full p-2 border rounded-xl text-xs">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] uppercase text-slate-500 font-bold">Brand Tagline</label>
                                <input type="text" name="tagline" value="{{ $settings->tagline }}" class="w-full p-2 border rounded-xl text-xs">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] uppercase text-slate-500 font-bold">Primary Brand Color</label>
                                <input type="color" name="primary_color" value="{{ $settings->primary_color }}" class="w-12 p-1 border rounded-lg h-8 cursor-pointer">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] uppercase text-slate-500 font-bold">Secondary Accent Color</label>
                                <input type="color" name="secondary_color" value="{{ $settings->secondary_color }}" class="w-12 p-1 border rounded-lg h-8 cursor-pointer">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] uppercase text-slate-500 font-bold">Phone Numbers (Comma separated)</label>
                                <input type="text" name="phone_numbers" value="{{ implode(',', $settings->phone_numbers) }}" class="w-full p-2 border rounded-xl text-xs">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] uppercase text-slate-500 font-bold">Emails (Comma separated)</label>
                                <input type="text" name="emails" value="{{ implode(',', $settings->emails) }}" class="w-full p-2 border rounded-xl text-xs">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] uppercase text-slate-500 font-bold">Emergency Phone Contacts (Comma separated)</label>
                                <input type="text" name="emergency_contacts" value="{{ implode(',', $settings->emergency_contacts) }}" class="w-full p-2 border rounded-xl text-xs">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] uppercase text-slate-500 font-bold">WhatsApp Helpline Number</label>
                                <input type="text" name="whatsapp_number" value="{{ $settings->whatsapp_number }}" class="w-full p-2 border rounded-xl text-xs">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] uppercase text-slate-500 font-bold">Google Analytics Tracking ID</label>
                                <input type="text" name="google_analytics_id" value="{{ $settings->google_analytics_id }}" class="w-full p-2 border rounded-xl text-xs">
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] uppercase text-slate-500 font-bold">Hospital Address Statement</label>
                            <textarea name="address" rows="2" class="w-full p-2 border rounded-xl text-xs">{{ $settings->address }}</textarea>
                        </div>
                        
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold uppercase rounded-xl tracking-wider cursor-pointer">
                            Save Layout Settings
                        </button>
                    </form>
                </div>

                <!-- Tab: Security -->
                <div x-show="activeTab === 'security'" class="space-y-6">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-950">Administrative Credentials & Security</h3>
                        <p class="text-xs text-slate-500">Secure staff user login passwords within the relational SQLite repository.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left: Users list -->
                        <div class="space-y-3">
                            <h4 class="text-xs font-extrabold uppercase text-slate-400">Staff Accounts</h4>
                            <div class="border divide-y rounded-2xl overflow-hidden bg-slate-50/20">
                                @foreach($users as $u)
                                    <div class="p-3.5 flex justify-between items-center text-xs">
                                        <div>
                                            <p class="font-bold text-slate-800">{{ $u->name }}</p>
                                            <p class="text-[10px] text-slate-500">Username: <span class="font-semibold text-slate-700">{{ $u->username }}</span> | Role: {{ $u->role }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Right: Password change form -->
                        <div class="bg-slate-50 p-4 border rounded-2xl space-y-3">
                            <h4 class="text-xs font-bold text-slate-800 uppercase">Modify Staff Password</h4>
                            
                            <form action="{{ route('admin.security.change-password') }}" method="POST" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="text-[10px] uppercase text-slate-500 font-bold">Select Staff Member</label>
                                    <select name="user_id" required class="w-full p-2 border rounded-xl text-xs bg-white">
                                        @foreach($users as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->username }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[10px] uppercase text-slate-500 font-bold">New Password</label>
                                    <input type="password" name="password" required placeholder="Min 4 characters" class="w-full p-2 border rounded-xl text-xs bg-white">
                                </div>
                                <div>
                                    <label class="text-[10px] uppercase text-slate-500 font-bold">Confirm New Password</label>
                                    <input type="password" name="password_confirmation" required placeholder="Confirm repeat password" class="w-full p-2 border rounded-xl text-xs bg-white">
                                </div>
                                
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700 cursor-pointer w-full mt-2">
                                    Apply Password Update
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
