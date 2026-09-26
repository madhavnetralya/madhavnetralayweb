<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $seo->title ?? $settings->hospital_name)</title>
    <meta name="description" content="@yield('meta_description', $seo->description ?? $settings->tagline)">
    <meta name="keywords" content="@yield('meta_keywords', is_array($seo->keywords ?? null) ? implode(', ', $seo->keywords) : '')">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="{{ $seo->og_type ?? 'website' }}">
    <meta property="og:title" content="@yield('title', $seo->title ?? $settings->hospital_name)">
    <meta property="og:description" content="@yield('meta_description', $seo->description ?? $settings->tagline)">
    <meta property="og:image" content="{{ $seo->og_image ?? '' }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (via CDN for standalone portable execution) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['SpaceGrotesk', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        primary: '{{ $settings->primary_color ?? "#2563eb" }}',
                        secondary: '{{ $settings->secondary_color ?? "#0d9488" }}',
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js (for reactive UI interactivity: dropdowns, mobile menus, booking modals) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }
    </style>

    @if(!empty($settings->google_analytics_id))
        <!-- Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $settings->google_analytics_id }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ $settings->google_analytics_id }}');
        </script>
    @endif
</head>
<body class="text-slate-800" x-data="{ mobileMenuOpen: false }">

    <!-- Important Dynamic Top Notification Notice Banner -->
    @php
        $importantNotice = $notices->where('important', true)->first();
    @endphp
    @if($importantNotice)
        <div class="bg-rose-600 text-white text-xs font-semibold py-2 px-4 text-center relative z-50 flex justify-center items-center gap-2">
            <span class="bg-white/20 px-2 py-0.5 rounded text-[10px] uppercase font-bold tracking-wider">Alert</span>
            <span>{{ $importantNotice->title }}: {{ Str::limit($importantNotice->content, 120) }}</span>
            <a href="{{ route('home') }}#notices" class="underline hover:text-rose-100 ml-2 font-bold">Read Detail &rarr;</a>
        </div>
    @endif

    <!-- Main Navigation Header -->
    <header class="bg-white/80 backdrop-blur-md border-b border-slate-100 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white font-black flex items-center justify-center text-xl shadow-md shadow-blue-200">
                    {{ $settings->logo ?? 'M' }}
                </div>
                <div>
                    <h1 class="text-md font-extrabold text-slate-900 leading-tight tracking-tight">Madhav Netralaya</h1>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Tertiary Eye Care Centre</p>
                </div>
            </a>

            <!-- Desktop Menu Links -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
                <a href="{{ route('home') }}" class="hover:text-blue-600 transition">Home</a>
                <a href="{{ route('about') }}" class="hover:text-blue-600 transition">About</a>
                <a href="{{ route('departments.index') }}" class="hover:text-blue-600 transition">Specialties</a>
                <a href="{{ route('doctors') }}" class="hover:text-blue-600 transition">Doctors</a>
                <a href="{{ route('diagnostics') }}" class="hover:text-blue-600 transition">Diagnostics</a>
                <a href="{{ route('blogs.index') }}" class="hover:text-blue-600 transition">Blogs</a>
                <a href="{{ route('events') }}" class="hover:text-blue-600 transition">Camps & Events</a>
                <a href="{{ route('contact') }}" class="hover:text-blue-600 transition">Contact</a>
            </nav>

            <!-- Book Appointment Action -->
            <div class="hidden md:flex items-center gap-4">
                <a href="{{ route('contact') }}#book" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-blue-200 transition">
                    Book Consult
                </a>
            </div>

            <!-- Mobile Hamburger Menu Button -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-slate-600 hover:bg-slate-50 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
            </button>
        </div>

        <!-- Mobile Expandable Slide Menu -->
        <div x-show="mobileMenuOpen" @click.away="mobileMenuOpen = false" class="md:hidden border-t border-slate-100 bg-white px-4 py-4 space-y-3 shadow-lg absolute w-full left-0 z-30" style="display: none;">
            <a href="{{ route('home') }}" class="block px-4 py-2 text-slate-700 font-medium hover:bg-slate-50 rounded-xl">Home</a>
            <a href="{{ route('about') }}" class="block px-4 py-2 text-slate-700 font-medium hover:bg-slate-50 rounded-xl">About Us</a>
            <a href="{{ route('departments.index') }}" class="block px-4 py-2 text-slate-700 font-medium hover:bg-slate-50 rounded-xl">Our Specialities</a>
            <a href="{{ route('doctors') }}" class="block px-4 py-2 text-slate-700 font-medium hover:bg-slate-50 rounded-xl">Our Doctors</a>
            <a href="{{ route('diagnostics') }}" class="block px-4 py-2 text-slate-700 font-medium hover:bg-slate-50 rounded-xl">Diagnostics</a>
            <a href="{{ route('blogs.index') }}" class="block px-4 py-2 text-slate-700 font-medium hover:bg-slate-50 rounded-xl">Blogs</a>
            <a href="{{ route('events') }}" class="block px-4 py-2 text-slate-700 font-medium hover:bg-slate-50 rounded-xl">Camps & Outreach</a>
            <a href="{{ route('contact') }}" class="block px-4 py-2 text-slate-700 font-medium hover:bg-slate-50 rounded-xl">Contact Us</a>
            <a href="{{ route('contact') }}#book" class="block w-full text-center py-3 bg-blue-600 text-white font-bold text-xs uppercase rounded-xl tracking-wider shadow-md">
                Request Appointment
            </a>
        </div>
    </header>

    <!-- Main Content Container -->
    <main>
        @if(session('success'))
            <div class="max-w-4xl mx-auto mt-6 px-4">
                <div class="p-4 bg-teal-50 border border-teal-200 text-teal-800 text-sm font-semibold rounded-2xl flex items-center gap-3 shadow-sm">
                    <svg class="w-5 h-5 text-teal-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"></path></svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Hospital Dynamic Footer -->
    <footer class="bg-slate-900 text-slate-300 pt-16 pb-8 mt-24 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-12">
            <!-- Col 1: Identity -->
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white font-black flex items-center justify-center text-xl shadow-md">
                        {{ $settings->logo ?? 'M' }}
                    </div>
                    <div>
                        <h4 class="text-md font-extrabold text-white leading-tight">Madhav Netralaya</h4>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Tertiary Eye Hospital</p>
                    </div>
                </div>
                <p class="text-xs text-slate-400 leading-relaxed mt-4">
                    {{ $settings->tagline ?? 'Your Vision, Our Focused Expertise. NABH Accredited Tertiary Eye Care.' }}
                </p>
                <div class="pt-4 flex gap-3">
                    @foreach($settings->social_media ?? [] as $platform => $url)
                        <a href="{{ $url }}" target="_blank" class="w-8 h-8 rounded-lg bg-white/5 hover:bg-blue-600/20 text-slate-400 hover:text-blue-500 flex items-center justify-center transition border border-white/5">
                            <span class="text-xs font-bold uppercase tracking-widest">{{ substr($platform, 0, 2) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Col 2: Services -->
            <div>
                <h4 class="text-xs uppercase tracking-widest text-white font-extrabold mb-5">Ophthalmic Specialties</h4>
                <ul class="space-y-3.5 text-xs text-slate-400">
                    <li><a href="{{ route('departments.show', 'cataract') }}" class="hover:text-white transition">Cataract & Lens Replacement</a></li>
                    <li><a href="{{ route('departments.show', 'retina') }}" class="hover:text-white transition">Vitreoretinal Specialty</a></li>
                    <li><a href="{{ route('departments.show', 'cornea') }}" class="hover:text-white transition">LASIK Specs Removal & Cornea</a></li>
                    <li><a href="{{ route('departments.show', 'glaucoma') }}" class="hover:text-white transition">Glaucoma Preservations</a></li>
                    <li><a href="{{ route('departments.show', 'pediatric') }}" class="hover:text-white transition">Pediatric Ophthalmology</a></li>
                </ul>
            </div>

            <!-- Col 3: Quick Links -->
            <div>
                <h4 class="text-xs uppercase tracking-widest text-white font-extrabold mb-5">Quick Access</h4>
                <ul class="space-y-3.5 text-xs text-slate-400">
                    <li><a href="{{ route('about') }}" class="hover:text-white transition">About Our Institute</a></li>
                    <li><a href="{{ route('doctors') }}" class="hover:text-white transition">Meet Our Eye Specialists</a></li>
                    <li><a href="{{ route('diagnostics') }}" class="hover:text-white transition">Advanced Clinical Tests</a></li>
                    <li><a href="{{ route('events') }}" class="hover:text-white transition">Free Eye Camps & Events</a></li>
                    <li><a href="{{ route('admin.login') }}" class="hover:text-white transition flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg> Administrative Login</a></li>
                </ul>
            </div>

            <!-- Col 4: Contact details -->
            <div class="space-y-4 text-xs text-slate-400">
                <h4 class="text-xs uppercase tracking-widest text-white font-extrabold mb-2">Hospital Contacts</h4>
                <p class="leading-relaxed">
                    {{ $settings->address ?? 'Madhav Netralaya, Nagpur' }}
                </p>
                <div class="space-y-2 pt-2">
                    <p class="flex items-center gap-2"><svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg> {{ implode(' / ', $settings->phone_numbers ?? []) }}</p>
                    <p class="flex items-center gap-2"><svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg> {{ implode(' / ', $settings->emails ?? []) }}</p>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 pt-8 border-t border-slate-800 text-center text-xs text-slate-500 flex flex-col md:flex-row justify-between items-center gap-4">
            <p>&copy; {{ date('Y') }} {{ $settings->hospital_name }}. All Rights Reserved. NABH Accredited Eye Care.</p>
            <p class="flex gap-4">
                <a href="#" class="hover:text-slate-300">Privacy Policy</a>
                <span>&bull;</span>
                <a href="#" class="hover:text-slate-300">Terms of Service</a>
            </p>
        </div>
    </footer>

    <!-- Portable Dynamic WhatsApp Contact Bubble Widget -->
    @if(!empty($settings->whatsapp_number))
        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $settings->whatsapp_number) }}?text=Hello%20Madhav%20Netralaya%20hospital%2C%20I%20want%20to%20inquire%20about%20appointment%20bookings." target="_blank" class="fixed bottom-6 right-6 z-50 w-14 h-14 bg-emerald-500 text-white rounded-full flex items-center justify-center shadow-xl hover:bg-emerald-600 transition hover:scale-105" title="WhatsApp Chat Helpline">
            <svg class="w-7 h-7 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.73-1.455L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.86-4.37 9.864-9.799.002-2.63-1.023-5.101-2.885-6.966C16.63 1.974 14.161.95 11.531.95c-5.44 0-9.866 4.372-9.87 9.802 0 1.63.463 3.224 1.34 4.625l-.974 3.56 3.64-.932zm9.176-5.791c-.305-.153-1.805-.89-2.085-.992-.28-.102-.483-.153-.686.153-.203.305-.788.992-.966 1.195-.178.203-.356.229-.661.076-.305-.153-1.288-.475-2.454-1.516-.908-.81-1.52-1.81-1.698-2.115-.178-.305-.019-.47.133-.622.137-.137.305-.356.457-.534.153-.178.203-.305.305-.508.102-.203.051-.381-.025-.534-.076-.153-.686-1.654-.94-2.262-.247-.594-.499-.514-.686-.523-.178-.009-.381-.011-.584-.011-.203 0-.534.076-.814.381-.28.305-1.067 1.042-1.067 2.541 0 1.499 1.092 2.946 1.244 3.149.153.203 2.15 3.284 5.207 4.601.727.313 1.295.5 1.737.64.73.232 1.396.199 1.922.12.586-.088 1.805-.737 2.059-1.449.254-.712.254-1.322.178-1.449-.076-.127-.28-.203-.585-.356z"/></svg>
        </a>
    @endif
</body>
</html>
