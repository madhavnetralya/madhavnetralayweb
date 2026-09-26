@extends('layouts.app')

@section('title', 'Camps & Events - ' . $settings->hospital_name)

@section('content')
<div class="py-16 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center space-y-3 max-w-2xl mx-auto mb-16">
            <span class="px-2.5 py-1 bg-blue-50 border border-blue-100 text-blue-600 rounded-lg text-[10px] font-extrabold uppercase tracking-wider">
                Camps & Outreach
            </span>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Community Events & Free Screening Camps</h2>
            <p class="text-xs text-slate-500">
                Register for upcoming rural outreach campaigns, pediatric checkups, and specialized surgical seminars.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            @foreach($events as $event)
                <div class="bg-white border rounded-[36px] overflow-hidden shadow-sm flex flex-col justify-between">
                    <div>
                        <img src="{{ $event->image }}" class="w-full h-56 object-cover">
                        <div class="p-6 space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="px-2.5 py-1 text-[9px] font-extrabold uppercase tracking-wider rounded-lg border {{ 
                                    $event->status === 'completed' ? 'bg-slate-100 border-slate-200 text-slate-500' : 'bg-emerald-50 border-emerald-200 text-emerald-600'
                                }}">
                                    {{ $event->status }}
                                </span>
                                <span class="text-[10px] text-slate-400 font-extrabold uppercase">
                                    {{ $event->registrations_count }} Registered
                                </span>
                            </div>

                            <h3 class="text-md font-extrabold text-slate-950 tracking-tight leading-snug">{{ $event->title }}</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">{{ $event->description }}</p>

                            <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl text-[11px] text-slate-600 space-y-1">
                                <p>Date: <span class="font-bold text-slate-700">{{ $event->date->format('M d, Y') }}</span></p>
                                <p>Time: <span class="font-bold text-slate-700">{{ $event->time }}</span></p>
                                <p>Location: <span class="font-bold text-slate-700">{{ $event->location }}</span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Registration Box -->
                    @if($event->status !== 'completed')
                        <div class="p-6 border-t bg-slate-50/20">
                            <h4 class="font-bold text-xs text-slate-800 mb-3">Register For This Event</h4>
                            
                            <form action="{{ route('events.register') }}" method="POST" class="space-y-3">
                                @csrf
                                <input type="hidden" name="event_id" value="{{ $event->id }}">
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <input type="text" name="name" required placeholder="Full Name" class="p-2 border rounded-xl text-xs bg-white outline-none">
                                    <input type="email" name="email" required placeholder="Email Address" class="p-2 border rounded-xl text-xs bg-white outline-none">
                                    <input type="text" name="phone" required placeholder="Phone Number" class="p-2 border rounded-xl text-xs bg-white outline-none">
                                </div>
                                
                                <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-[10px] uppercase tracking-wider rounded-xl transition cursor-pointer">
                                    Submit Event Registration
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
