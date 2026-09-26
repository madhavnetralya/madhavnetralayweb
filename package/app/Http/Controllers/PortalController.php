<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\Diagnostic;
use App\Models\Facility;
use App\Models\BlogCategory;
use App\Models\Blog;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Testimonial;
use App\Models\GalleryItem;
use App\Models\Notice;
use App\Models\Slider;
use App\Models\Setting;
use App\Models\SeoMeta;
use App\Models\Appointment;
use App\Models\Enquiry;
use App\Models\EyeConsultationEnquiry;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\DB;

class PortalController
{
    /**
     * Atomically mutate and persist state_store in SQLite with transaction lock & automatic pre-write backup
     */
    protected function atomicUpdateState(callable $modifier)
    {
        return DB::transaction(function () use ($modifier) {
            $row = DB::table('state_store')->where('key', 'state')->lockForUpdate()->first();
            if (!$row) {
                throw new \Exception('State store record not found in SQLite database');
            }
            $state = json_decode($row->value, true);
            if (!is_array($state)) {
                $state = [];
            }

            // Automatic Pre-Write Backup
            $backupDir = storage_path('app/state_backups');
            if (!is_dir($backupDir)) {
                @mkdir($backupDir, 0755, true);
            }
            $timestamp = date('Ymd_His') . '_' . substr(str_replace('.', '', (string)microtime(true)), -4);
            $backupFile = $backupDir . "/state_backup_auto_{$timestamp}.json";
            @file_put_contents($backupFile, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            // Execute the caller's mutation
            $result = $modifier($state);

            // Persist modified state
            DB::table('state_store')->where('key', 'state')->update([
                'value' => json_encode($state, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            ]);

            return $result;
        }, 5);
    }

    /**
     * Get primary layout data: hospital settings, important notices, etc.
     */
    protected function getLayoutData()
    {
        return [
            'settings' => Setting::first(),
            'notices' => Notice::orderBy('date', 'desc')->get(),
        ];
    }

    public function home()
    {
        $data = $this->getLayoutData();
        $data['seo'] = SeoMeta::where('page_key', 'home')->first();
        $data['sliders'] = Slider::orderBy('order')->get();
        $data['departments'] = Department::all();
        $data['doctors'] = Doctor::all();
        $data['diagnostics'] = Diagnostic::all();
        $data['facilities'] = Facility::all();
        $data['blogs'] = Blog::where('status', 'published')->orderBy('published_at', 'desc')->take(3)->get();
        $data['testimonials'] = Testimonial::where('approved', true)->orderBy('created_at', 'desc')->get();
        $data['events'] = Event::orderBy('date')->take(2)->get();

        return view('home', $data);
    }

    public function about()
    {
        $data = $this->getLayoutData();
        $data['seo'] = SeoMeta::where('page_key', 'about')->first();
        $data['gallery'] = GalleryItem::orderBy('created_at', 'desc')->get();
        return view('about', $data);
    }

    public function departments()
    {
        $data = $this->getLayoutData();
        $data['departments'] = Department::all();
        return view('departments.index', $data);
    }

    public function departmentShow($id)
    {
        $data = $this->getLayoutData();
        $department = Department::findOrFail($id);
        $data['department'] = $department;
        $data['doctors'] = Doctor::where('department_id', $id)->get();
        return view('departments.show', $data);
    }

    public function doctors(Request $request)
    {
        $data = $this->getLayoutData();
        $query = Doctor::query();

        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('specialty', 'like', "%{$search}%")
                  ->orWhere('qualification', 'like', "%{$search}%");
            });
        }

        $data['doctors'] = $query->get();
        $data['departments'] = Department::all();
        return view('doctors', $data);
    }

    public function diagnostics()
    {
        $data = $this->getLayoutData();
        $data['diagnostics'] = Diagnostic::all();
        return view('diagnostics', $data);
    }

    public function blogs(Request $request)
    {
        $data = $this->getLayoutData();
        $query = Blog::where('status', 'published');

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $data['blogs'] = $query->orderBy('published_at', 'desc')->paginate(6);
        $data['categories'] = BlogCategory::all();
        return view('blogs.index', $data);
    }

    public function blogShow($slug)
    {
        $data = $this->getLayoutData();
        $blog = Blog::where('slug', $slug)->firstOrFail();
        $data['blog'] = $blog;
        $data['related_blogs'] = Blog::where('status', 'published')
            ->where('id', '!=', $blog->id)
            ->where('category_id', $blog->category_id)
            ->take(3)
            ->get();

        return view('blogs.show', $data);
    }

    public function events()
    {
        $data = $this->getLayoutData();
        $data['events'] = Event::orderBy('date')->get();
        return view('events', $data);
    }

    public function contact()
    {
        $data = $this->getLayoutData();
        return view('contact', $data);
    }

    /**
     * Handle Public Booking Form Submission (Blade View)
     */
    public function bookAppointment(Request $request)
    {
        $request->validate([
            'patient_name' => 'required|string|max:255',
            'patient_age' => 'required|integer|min:0|max:120',
            'patient_gender' => 'required|string',
            'patient_phone' => 'required|string',
            'patient_email' => 'required|email',
            'department_id' => 'required|exists:departments,id',
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'required|date|after_or_equal:today',
            'time_slot' => 'required|string',
            'reason' => 'nullable|string',
        ]);

        Appointment::create([
            'id' => 'app_' . time() . '_' . rand(100, 999),
            'patient_name' => $request->patient_name,
            'patient_age' => $request->patient_age,
            'patient_gender' => $request->patient_gender,
            'patient_phone' => $request->patient_phone,
            'patient_email' => $request->patient_email,
            'department_id' => $request->department_id,
            'doctor_id' => $request->doctor_id,
            'date' => $request->date,
            'time_slot' => $request->time_slot,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Your appointment request has been submitted successfully! Our receptionist will call you shortly to confirm.');
    }

    /**
     * Handle Contact Enquiry Submission (Blade View)
     */
    public function submitEnquiry(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        Enquiry::create([
            'id' => 'enq_' . time(),
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'unread',
        ]);

        return back()->with('success', 'Thank you for contacting Madhav Netralaya! We have received your query and will reply shortly.');
    }

    /**
     * Handle Newsletter Subscription (Blade View)
     */
    public function subscribeNewsletter(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $exists = Subscriber::where('email', $request->email)->exists();
        if (!$exists) {
            Subscriber::create([
                'id' => 'sub_' . time(),
                'email' => $request->email,
                'subscribed_at' => now(),
            ]);
        }

        return back()->with('success', 'Congratulations! You have successfully subscribed to our monthly newsletter and camp alerts.');
    }

    /**
     * Handle Event Registration (Blade View)
     */
    public function registerEvent(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string',
        ]);

        $event = Event::findOrFail($request->event_id);

        EventRegistration::create([
            'id' => 'reg_' . time(),
            'event_id' => $request->event_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'registered_at' => now(),
        ]);

        // Increment count
        $event->increment('registrations_count');

        return back()->with('success', 'Excellent! Your registration for the event "' . $event->title . '" has been confirmed.');
    }

    // ==========================================================
    // API ENDPOINTS (Returns JSON exactly matching Node.js responses)
    // ==========================================================

    /**
     * Mirrors Express GET /api/state
     */
    public function getApiState()
    {
        return response()->json([
            'users' => \App\Models\User::all(),
            'doctors' => Doctor::all(),
            'departments' => Department::all(),
            'diagnostics' => Diagnostic::all(),
            'facilities' => Facility::all(),
            'blogs' => Blog::all(),
            'blogCategories' => BlogCategory::all(),
            'events' => Event::all(),
            'eventRegistrations' => EventRegistration::all(),
            'testimonials' => Testimonial::all(),
            'gallery' => GalleryItem::all(),
            'notices' => Notice::all(),
            'appointments' => Appointment::all(),
            'enquiries' => Enquiry::all(),
            'eyeConsultationEnquiries' => (function() {
                try {
                    return EyeConsultationEnquiry::all();
                } catch (\Exception $e) {
                    return [];
                }
            })(),
            'subscribers' => Subscriber::all(),
            'settings' => Setting::first(),
            'seoMeta' => [
                'home' => SeoMeta::where('page_key', 'home')->first(),
                'about' => SeoMeta::where('page_key', 'about')->first()
            ],
            'sliders' => Slider::all(),
        ]);
    }

    /**
     * Mirrors Express POST /api/appointments
     */
    public function bookAppointmentApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'patientName' => 'required|string|max:255',
            'patientAge' => 'required|integer',
            'patientGender' => 'required|string|max:50',
            'patientPhone' => 'required|string|max:50',
            'patientEmail' => 'required|email|max:255',
            'departmentId' => 'required|string',
            'doctorId' => 'required|string',
            'date' => 'required|string',
            'timeSlot' => 'required|string',
            'reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $appointment = [
            'id' => 'app_' . (int)(microtime(true) * 1000) . '_' . rand(100, 999),
            'patientName' => trim($request->patientName),
            'patientAge' => (int)$request->patientAge,
            'patientGender' => trim($request->patientGender),
            'patientPhone' => trim($request->patientPhone),
            'patientEmail' => strtolower(trim($request->patientEmail)),
            'departmentId' => trim($request->departmentId),
            'doctorId' => trim($request->doctorId),
            'date' => trim($request->date),
            'timeSlot' => trim($request->timeSlot),
            'reason' => trim($request->reason ?? ''),
            'status' => 'pending',
            'createdAt' => now()->toIso8601String(),
        ];

        try {
            $this->atomicUpdateState(function (&$state) use ($appointment) {
                if (!isset($state['appointments']) || !is_array($state['appointments'])) {
                    $state['appointments'] = [];
                }
                array_unshift($state['appointments'], $appointment);
            });

            return response()->json([
                'success' => true,
                'appointment' => $appointment
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mirrors Express POST /api/enquiries
     */
    public function submitEnquiryApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $enquiry = [
            'id' => 'enq_' . (int)(microtime(true) * 1000) . '_' . rand(100, 999),
            'name' => trim($request->name),
            'email' => strtolower(trim($request->email)),
            'phone' => trim($request->phone),
            'subject' => trim($request->subject),
            'message' => trim($request->message),
            'status' => 'unread',
            'createdAt' => now()->toIso8601String(),
        ];

        try {
            $this->atomicUpdateState(function (&$state) use ($enquiry) {
                if (!isset($state['enquiries']) || !is_array($state['enquiries'])) {
                    $state['enquiries'] = [];
                }
                array_unshift($state['enquiries'], $enquiry);
            });

            return response()->json([
                'success' => true,
                'enquiry' => $enquiry
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mirrors Express POST /api/subscribers
     */
    public function subscribeNewsletterApi(Request $request)
    {
        $email = strtolower(trim($request->input('email', '')));
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => 'Please enter a valid email address.'], 400);
        }

        $subscriber = [
            'id' => 'sub_' . (int)(microtime(true) * 1000) . '_' . rand(100, 999),
            'email' => $email,
            'subscribedAt' => now()->toIso8601String(),
        ];

        try {
            $alreadySubscribed = false;
            $this->atomicUpdateState(function (&$state) use ($subscriber, &$alreadySubscribed) {
                if (!isset($state['subscribers']) || !is_array($state['subscribers'])) {
                    $state['subscribers'] = [];
                }
                foreach ($state['subscribers'] as $s) {
                    if (strtolower(trim($s['email'] ?? '')) === $subscriber['email']) {
                        $alreadySubscribed = true;
                        return;
                    }
                }
                array_unshift($state['subscribers'], $subscriber);
            });

            if ($alreadySubscribed) {
                return response()->json(['success' => true, 'message' => 'Already subscribed']);
            }

            return response()->json([
                'success' => true,
                'subscriber' => $subscriber
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mirrors Express POST /api/registrations
     */
    public function registerEventApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'eventId' => 'required',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $eventId = (string)$request->eventId;
        $registration = [
            'id' => 'reg_' . (int)(microtime(true) * 1000) . '_' . rand(100, 999),
            'eventId' => $eventId,
            'name' => trim($request->name),
            'email' => strtolower(trim($request->email)),
            'phone' => trim($request->phone),
            'registeredAt' => now()->toIso8601String(),
        ];

        try {
            $this->atomicUpdateState(function (&$state) use ($registration, $eventId) {
                if (!isset($state['eventRegistrations']) || !is_array($state['eventRegistrations'])) {
                    $state['eventRegistrations'] = [];
                }
                array_unshift($state['eventRegistrations'], $registration);

                // Increment registrationsCount on the matched event
                if (isset($state['events']) && is_array($state['events'])) {
                    foreach ($state['events'] as &$ev) {
                        if (($ev['id'] ?? '') === $eventId) {
                            $ev['registrationsCount'] = ($ev['registrationsCount'] ?? 0) + 1;
                            break;
                        }
                    }
                }
            });

            return response()->json([
                'success' => true,
                'registration' => $registration
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mirrors Express POST /api/eye-donations
     */
    public function eyeDonationApi(Request $request)
    {
        try {
            $pledge = $request->all();
            $pledge['id'] = 'pledge_' . (int)(microtime(true) * 1000);
            $type = $request->input('type') ?: 'Pledge';
            $pledge['type'] = $type;
            $pledge['createdAt'] = now()->format('Y-m-d\TH:i:s.v\Z');
            
            // Generate server-side unique Certificate Number and Issue Date
            $year = date('Y');
            $prefix = (strtolower(trim($type)) === 'eye donation' || strtolower(trim($type)) === 'eyedonation') ? 'MN-ED' : 'MN-PLEDGE';
            $randomDigits = str_pad(rand(1000, 99999), 5, '0', STR_PAD_LEFT);
            $pledge['certificateNumber'] = "{$prefix}-{$year}-{$randomDigits}";
            $pledge['issueDate'] = date('d M Y');
            $pledge['emailStatus'] = 'not_sent';
            
            $this->atomicUpdateState(function (&$state) use ($pledge) {
                if (!isset($state['eyeDonations']) || !is_array($state['eyeDonations'])) {
                    $state['eyeDonations'] = [];
                }
                $state['eyeDonations'][] = $pledge;
            });
            
            return response()->json(['success' => true, 'pledge' => $pledge]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Database error', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Resolve image path to direct absolute filesystem path or local cache file for Dompdf
     * Guarantees never returning empty strings, invalid paths, or large data URIs that break Dompdf regex.
     */
    protected function resolveImagePathForPdf($path)
    {
        if (empty($path) || !is_string($path)) {
            return null;
        }
        $path = trim($path);
        if ($path === '' || str_contains($path, "\0")) {
            return null;
        }

        // 1. If it's a base64 data URI, save to a local temp file so Dompdf reads it cleanly via file stream
        if (str_starts_with($path, 'data:image/')) {
            $commaPos = strpos($path, ',');
            if ($commaPos !== false) {
                $header = substr($path, 0, $commaPos);
                $data = substr($path, $commaPos + 1);
                $binary = base64_decode(trim($data));
                if ($binary !== false && strlen($binary) > 0) {
                    $ext = 'png';
                    if (str_contains($header, 'jpeg') || str_contains($header, 'jpg')) $ext = 'jpg';
                    elseif (str_contains($header, 'webp')) $ext = 'webp';
                    elseif (str_contains($header, 'gif')) $ext = 'gif';
                    elseif (str_contains($header, 'svg')) $ext = 'svg';

                    $cacheDir = storage_path('app/public/temp');
                    if (!file_exists($cacheDir)) {
                        @mkdir($cacheDir, 0777, true);
                    }
                    $hash = md5($path);
                    $targetFile = $cacheDir . DIRECTORY_SEPARATOR . "img_{$hash}.{$ext}";
                    if (!file_exists($targetFile) || filesize($targetFile) === 0) {
                        @file_put_contents($targetFile, $binary);
                    }
                    if (file_exists($targetFile) && filesize($targetFile) > 0) {
                        return str_replace('\\', '/', $targetFile);
                    }
                }
            }
            return null;
        }

        // 2. Direct SVG XML string
        if (str_starts_with($path, '<svg') || (str_starts_with($path, '<?xml') && str_contains($path, '<svg'))) {
            $cacheDir = storage_path('app/public/temp');
            if (!file_exists($cacheDir)) {
                @mkdir($cacheDir, 0777, true);
            }
            $hash = md5($path);
            $targetFile = $cacheDir . DIRECTORY_SEPARATOR . "svg_{$hash}.svg";
            if (!file_exists($targetFile) || filesize($targetFile) === 0) {
                @file_put_contents($targetFile, $path);
            }
            if (file_exists($targetFile) && filesize($targetFile) > 0) {
                return str_replace('\\', '/', $targetFile);
            }
            return null;
        }

        // 3. Remote HTTP / HTTPS URL
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            try {
                $ctx = stream_context_create([
                    'http' => [
                        'timeout' => 5,
                        'ignore_errors' => true,
                    ]
                ]);
                $data = @file_get_contents($path, false, $ctx);
                if ($data !== false && strlen($data) > 0) {
                    $ext = strtolower(pathinfo(parse_url($path, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION)) ?: 'png';
                    $cacheDir = storage_path('app/public/temp');
                    if (!file_exists($cacheDir)) {
                        @mkdir($cacheDir, 0777, true);
                    }
                    $hash = md5($path);
                    $targetFile = $cacheDir . DIRECTORY_SEPARATOR . "remote_{$hash}.{$ext}";
                    if (!file_exists($targetFile) || filesize($targetFile) === 0) {
                        @file_put_contents($targetFile, $data);
                    }
                    if (file_exists($targetFile) && filesize($targetFile) > 0) {
                        return str_replace('\\', '/', $targetFile);
                    }
                }
            } catch (\Throwable $e) {}
            return null;
        }

        // 4. Local disk files
        $cleanPath = ltrim($path, '/\\');
        $stripStorage = str_starts_with($cleanPath, 'storage/') ? substr($cleanPath, 8) : $cleanPath;
        $stripUploads = str_starts_with($cleanPath, 'uploads/') ? substr($cleanPath, 8) : $cleanPath;

        $candidatePaths = [
            $path, // in case it is an absolute path
            storage_path('app/public/' . $cleanPath),
            storage_path('app/public/' . $stripStorage),
            storage_path('app/' . $cleanPath),
            public_path('storage/' . $stripStorage),
            public_path($cleanPath),
            public_path('uploads/' . $stripUploads),
            base_path($cleanPath),
            base_path('public/' . $cleanPath),
        ];

        foreach ($candidatePaths as $candidate) {
            if (empty($candidate) || !is_string($candidate) || str_contains($candidate, "\0")) {
                continue;
            }
            $trimmed = trim($candidate);
            if ($trimmed === '') {
                continue;
            }
            try {
                if (@file_exists($trimmed) && @is_file($trimmed) && @is_readable($trimmed)) {
                    $size = @filesize($trimmed);
                    if ($size && $size > 0) {
                        // Return normalized absolute path with forward slashes for Dompdf
                        return str_replace('\\', '/', realpath($trimmed) ?: $trimmed);
                    }
                }
            } catch (\Throwable $e) {}
        }

        // If not found or not readable, return null so Dompdf skips rendering the tag
        return null;
    }

    /**
     * Prepare Certificate View Data from Pledge & CMS Settings
     */
    protected function getCertificateData($pledge, $certSettings = [])
    {
        $type = $pledge['type'] ?? 'Pledge';
        $isDonation = (strtolower(trim($type)) === 'eye donation' || strtolower(trim($type)) === 'eyedonation');
        
        $certTitle = $isDonation 
            ? (!empty($certSettings['titleDonation']) ? $certSettings['titleDonation'] : 'Eye Donation Certificate')
            : (!empty($certSettings['titlePledge']) ? $certSettings['titlePledge'] : 'Eye Donation Pledge Certificate');
            
        $bodyText = $isDonation
            ? ($certSettings['bodyTextDonation'] ?? '')
            : ($certSettings['bodyTextPledge'] ?? '');
            
        $organizationName = $certSettings['organizationName'] ?? '';
        $trustName = $certSettings['trustName'] ?? '';
        $address = $certSettings['address'] ?? '';
        $phone = $certSettings['phone'] ?? '';
        $email = $certSettings['email'] ?? '';
        $website = $certSettings['website'] ?? '';
        
        $logo = $this->resolveImagePathForPdf($certSettings['logo'] ?? '');
        
        $photoKey = !empty($pledge['photo']) ? $pledge['photo'] : (!empty($pledge['photoFileName']) ? $pledge['photoFileName'] : '');
        $donorPhoto = $this->resolveImagePathForPdf($photoKey);
        
        $signatories = [];
        if (!empty($certSettings['signatories']) && is_array($certSettings['signatories'])) {
            // Sort by order
            $sigs = $certSettings['signatories'];
            usort($sigs, function($a, $b) {
                return ($a['order'] ?? 0) <=> ($b['order'] ?? 0);
            });
            foreach ($sigs as $sig) {
                $signatories[] = [
                    'name' => $sig['name'] ?? '',
                    'designation' => $sig['designation'] ?? '',
                    'signatureImage' => $this->resolveImagePathForPdf($sig['signatureImage'] ?? ''),
                    'order' => $sig['order'] ?? 0,
                ];
            }
        }
        
        $ribbonTopLeft = public_path('images/certificates/cert_top_left_ribbon.png');
        $ribbonBottomRight = public_path('images/certificates/cert_bottom_right_ribbon.png');
        $chevronBullet = public_path('images/certificates/chevron_bullet.png');

        $ribbonTopLeft = file_exists($ribbonTopLeft) ? str_replace('\\', '/', $ribbonTopLeft) : null;
        $ribbonBottomRight = file_exists($ribbonBottomRight) ? str_replace('\\', '/', $ribbonBottomRight) : null;
        $chevronBullet = file_exists($chevronBullet) ? str_replace('\\', '/', $chevronBullet) : null;

        return [
            'pledge' => $pledge,
            'certificateTitle' => $certTitle,
            'certificateNumber' => $pledge['certificateNumber'] ?? 'MN-CERT-0000',
            'issueDate' => $pledge['issueDate'] ?? date('d M Y'),
            'bodyText' => $bodyText,
            'organizationName' => $organizationName,
            'trustName' => $trustName,
            'address' => $address,
            'phone' => $phone,
            'email' => $email,
            'website' => $website,
            'logo' => $logo,
            'donorPhoto' => $donorPhoto,
            'signatories' => $signatories,
            'ribbonTopLeft' => $ribbonTopLeft,
            'ribbonBottomRight' => $ribbonBottomRight,
            'chevronBullet' => $chevronBullet,
        ];
    }

    /**
     * Unified method to generate Dompdf instance for View, Download and Email flows
     */
    protected function generateCertificatePdf($pledge, $certSettings = [])
    {
        $certData = $this->getCertificateData($pledge, $certSettings);
        
        $fontDir = storage_path('fonts');
        if (!file_exists($fontDir)) {
            @mkdir($fontDir, 0777, true);
        }

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('certificates.eye_donation', $certData)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Helvetica',
                'enable_font_subsetting' => false,
                'isFontSubsettingEnabled' => false,
                'tempDir' => sys_get_temp_dir(),
                'fontDir' => $fontDir,
                'fontCache' => $fontDir,
                'chroot' => [base_path(), storage_path(), public_path()],
            ]);
    }

    /**
     * Non-destructively update a pledge in state_store
     */
    protected function updatePledgeRecordInState($id, $updatedPledge)
    {
        try {
            $this->atomicUpdateState(function (&$state) use ($id, $updatedPledge) {
                if (isset($state['eyeDonations']) && is_array($state['eyeDonations'])) {
                    foreach ($state['eyeDonations'] as &$p) {
                        if (($p['id'] ?? '') === $id) {
                            $p = array_merge($p, $updatedPledge);
                            break;
                        }
                    }
                }
            });
        } catch (\Exception $e) {}
    }

    /**
     * Generate / Stream / Download Eye Donation Certificate PDF
     */
    public function downloadCertificatePdfApi($id, Request $request)
    {
        try {
            $stateRow = DB::table('state_store')->where('key', 'state')->first();
            if (!$stateRow) {
                return response()->json(['error' => 'Record not found'], 404);
            }
            
            $state = json_decode($stateRow->value, true) ?: [];
            $eyeDonations = $state['eyeDonations'] ?? [];
            $pledge = null;
            
            foreach ($eyeDonations as $p) {
                if (($p['id'] ?? '') === $id) {
                    $pledge = $p;
                    break;
                }
            }
            
            if (!$pledge) {
                return response()->json(['error' => 'Pledge record not found'], 404);
            }
            
            // Assign Certificate Number & Issue Date non-destructively if missing from legacy records
            $mutated = false;
            if (empty($pledge['certificateNumber'])) {
                $type = $pledge['type'] ?? 'Pledge';
                $year = !empty($pledge['createdAt']) ? date('Y', strtotime($pledge['createdAt'])) : date('Y');
                $prefix = (strtolower(trim($type)) === 'eye donation' || strtolower(trim($type)) === 'eyedonation') ? 'MN-ED' : 'MN-PLEDGE';
                $randomDigits = str_pad(rand(1000, 99999), 5, '0', STR_PAD_LEFT);
                $pledge['certificateNumber'] = "{$prefix}-{$year}-{$randomDigits}";
                $mutated = true;
            }
            if (empty($pledge['issueDate'])) {
                $pledge['issueDate'] = !empty($pledge['createdAt']) ? date('d M Y', strtotime($pledge['createdAt'])) : date('d M Y');
                $mutated = true;
            }
            if ($mutated) {
                $this->updatePledgeRecordInState($id, $pledge);
            }
            
            $certSettings = $state['certificateSettings'] ?? [];
            $pdf = $this->generateCertificatePdf($pledge, $certSettings);
            
            $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $pledge['name'] ?? 'Donor');
            $certNumSafe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $pledge['certificateNumber'] ?? $pledge['id']);
            $filename = "Certificate_{$safeName}_{$certNumSafe}.pdf";
            
            if ($request->query('download') == '1' || $request->query('download') == 'true') {
                return $pdf->download($filename);
            }
            
            return $pdf->stream($filename, ['Attachment' => false]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'PDF generation error', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Send Certificate PDF via Email
     */
    public function sendCertificateEmailApi($id, Request $request)
    {
        try {
            $stateRow = DB::table('state_store')->where('key', 'state')->first();
            if (!$stateRow) {
                return response()->json(['error' => 'Record not found'], 404);
            }
            
            $state = json_decode($stateRow->value, true) ?: [];
            $eyeDonations = $state['eyeDonations'] ?? [];
            $pledge = null;
            
            foreach ($eyeDonations as $p) {
                if (($p['id'] ?? '') === $id) {
                    $pledge = $p;
                    break;
                }
            }
            
            if (!$pledge) {
                return response()->json(['error' => 'Pledge record not found'], 404);
            }
            
            $donorEmail = trim($pledge['email'] ?? '');
            if (empty($donorEmail) || !filter_var($donorEmail, FILTER_VALIDATE_EMAIL)) {
                return response()->json(['error' => 'Donor has no valid email address recorded.'], 400);
            }
            
            // Assign Certificate Number & Issue Date non-destructively if missing
            $mutated = false;
            if (empty($pledge['certificateNumber'])) {
                $type = $pledge['type'] ?? 'Pledge';
                $year = !empty($pledge['createdAt']) ? date('Y', strtotime($pledge['createdAt'])) : date('Y');
                $prefix = (strtolower(trim($type)) === 'eye donation' || strtolower(trim($type)) === 'eyedonation') ? 'MN-ED' : 'MN-PLEDGE';
                $randomDigits = str_pad(rand(1000, 99999), 5, '0', STR_PAD_LEFT);
                $pledge['certificateNumber'] = "{$prefix}-{$year}-{$randomDigits}";
                $mutated = true;
            }
            if (empty($pledge['issueDate'])) {
                $pledge['issueDate'] = !empty($pledge['createdAt']) ? date('d M Y', strtotime($pledge['createdAt'])) : date('d M Y');
                $mutated = true;
            }
            if ($mutated) {
                $this->updatePledgeRecordInState($id, $pledge);
            }
            
            $certSettings = $state['certificateSettings'] ?? [];
            $pdf = $this->generateCertificatePdf($pledge, $certSettings);
            $pdfContent = $pdf->output();
            
            $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $pledge['name'] ?? 'Donor');
            $certNumSafe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $pledge['certificateNumber'] ?? $pledge['id']);
            $pdfFilename = "Certificate_{$safeName}_{$certNumSafe}.pdf";
            
            $mailHost = env('MAIL_HOST');
            $mailUser = env('MAIL_USERNAME');
            $isSmtpConfigured = !empty($mailHost) && !empty($mailUser) && !in_array($mailHost, ['mailpit', 'localhost', '127.0.0.1']);
            
            $orgName = $certSettings['organizationName'] ?? 'Madhav Netralaya';
            $subjectTemplate = $certSettings['emailSubject'] ?? 'Eye Donation Certificate - {donor_name}';
            $bodyTemplate = $certSettings['emailBodyTemplate'] ?? "Dear {donor_name},\n\nThank you for your noble commitment to eye donation. Please find attached your certificate ({certificate_number}).\n\nWarm regards,\n{organization_name}";
            
            $subject = str_replace(
                ['{donor_name}', '{certificate_number}', '{organization_name}'],
                [$pledge['name'] ?? '', $pledge['certificateNumber'] ?? '', $orgName],
                $subjectTemplate
            );
            $body = str_replace(
                ['{donor_name}', '{certificate_number}', '{organization_name}'],
                [$pledge['name'] ?? '', $pledge['certificateNumber'] ?? '', $orgName],
                $bodyTemplate
            );
            
            $fromAddress = env('MAIL_FROM_ADDRESS') ?: ($certSettings['email'] ?? 'noreply@madhavnetralaya.org');
            $fromName = env('MAIL_FROM_NAME') ?: $orgName;
            
            if ($isSmtpConfigured) {
                try {
                    \Illuminate\Support\Facades\Mail::raw($body, function ($message) use ($donorEmail, $subject, $pdfContent, $pdfFilename, $fromAddress, $fromName) {
                        $message->to($donorEmail)
                                ->from($fromAddress, $fromName)
                                ->subject($subject)
                                ->attachData($pdfContent, $pdfFilename, [
                                    'mime' => 'application/pdf',
                                ]);
                    });
                    
                    $pledge['emailStatus'] = 'sent';
                    $pledge['emailSentAt'] = now()->format('Y-m-d\TH:i:s.v\Z');
                    $this->updatePledgeRecordInState($id, $pledge);
                    
                    return response()->json([
                        'success' => true,
                        'emailStatus' => 'sent',
                        'message' => "Certificate successfully emailed to {$donorEmail}"
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("Certificate email sending failed: " . $e->getMessage());
                    $pledge['emailStatus'] = 'failed';
                    $this->updatePledgeRecordInState($id, $pledge);
                    
                    return response()->json([
                        'success' => true,
                        'emailStatus' => 'pending',
                        'message' => 'Certificate ready. Email dispatch logged as pending (SMTP delivery error: ' . $e->getMessage() . ').'
                    ]);
                }
            } else {
                \Illuminate\Support\Facades\Log::info("Certificate email queued/logged for {$donorEmail} (SMTP not yet configured in .env)");
                $pledge['emailStatus'] = 'pending';
                $this->updatePledgeRecordInState($id, $pledge);
                
                return response()->json([
                    'success' => true,
                    'emailStatus' => 'pending',
                    'message' => 'Certificate ready. Email dispatch logged as pending (SMTP server configuration will be enabled soon).'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Email workflow error', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Mirrors Express GET /api/career-applications
     */
    public function getCareerApplicationsApi()
    {
        try {
            $stateRow = DB::table('state_store')->where('key', 'state')->first();
            $applications = [];
            if ($stateRow) {
                $state = json_decode($stateRow->value, true);
                $applications = $state['careerApplications'] ?? [];
            }
            return response()->json(['success' => true, 'applications' => $applications]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Database error', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Mirrors Express POST /api/career-applications
     */
    public function submitCareerApplicationApi(Request $request)
    {
        try {
            $fullName = $request->input('fullName');
            $email = $request->input('email');
            $phone = $request->input('phone');
            $department = $request->input('department');
            $position = $request->input('position');
            $resumeUrl = $request->input('resumeUrl');
            $resumeFilename = $request->input('resumeFilename');
            $aboutText = $request->input('aboutText');

            if (!$fullName || !is_string($fullName) || strlen(trim($fullName)) < 2) {
                return response()->json(['error' => 'Please enter your full name.'], 400);
            }

            if (!$email || !filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
                return response()->json(['error' => 'Please enter a valid email address.'], 400);
            }

            $cleanPhone = preg_replace('/\D/', '', $phone ?? '');
            if (!$cleanPhone || strlen($cleanPhone) < 10) {
                return response()->json(['error' => 'Please enter a valid 10-digit mobile phone number.'], 400);
            }

            if (!$department || !is_string($department) || !trim($department)) {
                return response()->json(['error' => 'Please select a department.'], 400);
            }

            if (!$resumeUrl || !is_string($resumeUrl) || !trim($resumeUrl)) {
                return response()->json(['error' => 'Please upload your CV/Resume (PDF, DOC, DOCX).'], 400);
            }

            $this->atomicUpdateState(function (&$state) use ($application) {
                if (!isset($state['careerApplications']) || !is_array($state['careerApplications'])) {
                    $state['careerApplications'] = [];
                }
                array_unshift($state['careerApplications'], $application);
            });

            return response()->json(['success' => true, 'application' => $application]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() ?: 'Failed to submit application'], 500);
        }
    }

    /**
     * Mirrors Express DELETE /api/career-applications/:id
     */
    public function deleteCareerApplicationApi($id)
    {
        try {
            $targetApp = null;
            $this->atomicUpdateState(function (&$state) use ($id, &$targetApp) {
                if (!isset($state['careerApplications']) || !is_array($state['careerApplications'])) {
                    $state['careerApplications'] = [];
                }

                $state['careerApplications'] = array_values(array_filter($state['careerApplications'], function($a) use ($id, &$targetApp) {
                    if (($a['id'] ?? '') === $id) {
                        $targetApp = $a;
                        return false;
                    }
                    return true;
                }));
            });

            // If a file exists, we should optionally delete it from Laravel storage if no longer used.
            if ($targetApp && isset($targetApp['resumeUrl']) && !str_starts_with($targetApp['resumeUrl'], 'data:')) {
                $filePath = $targetApp['resumeUrl'];
                if (str_starts_with($filePath, '/storage/')) {
                    $filePath = substr($filePath, 9);
                }
                
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($filePath)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($filePath);
                }
            }

            return response()->json(['success' => true, 'message' => 'Application deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() ?: 'Failed to delete application'], 500);
        }
    }

    /**
     * Mirrors Express POST /api/tender-payment
     */
    public function submitTenderPaymentApi(Request $request)
    {
        try {
            $fullName = $request->input('fullName');
            $email = $request->input('email');
            $mobile = $request->input('mobile');
            $amount = $request->input('amount');
            $paymentType = $request->input('paymentType', 'tender_fee');
            $paymentId = $request->input('paymentId');
            $orderId = $request->input('orderId');
            $tenderTitle = $request->input('tenderTitle');

            if (!$fullName || !is_string($fullName) || strlen(trim($fullName)) < 2) {
                return response()->json(['error' => 'Please enter a valid full name.'], 400);
            }

            if (!$email || !filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
                return response()->json(['error' => 'Please enter a valid email address.'], 400);
            }

            $cleanMobile = preg_replace('/\D/', '', $mobile ?? '');
            if (!$cleanMobile || !preg_match('/^[6-9]\d{9}$/', $cleanMobile)) {
                return response()->json(['error' => 'Please enter a valid 10-digit mobile number.'], 400);
            }

            $record = [
                'id' => 'tp_' . (int)(microtime(true) * 1000),
                'fullName' => trim($fullName),
                'email' => strtolower(trim($email)),
                'mobile' => $cleanMobile,
                'amount' => is_numeric($amount) ? (float)$amount : 10000,
                'paymentType' => $paymentType,
                'paymentId' => $paymentId ?: 'pay_rzp_' . (int)(microtime(true) * 1000) . '_' . rand(0, 9999),
                'orderId' => $orderId ?: 'ord_rzp_' . (int)(microtime(true) * 1000),
                'status' => 'success',
                'createdAt' => now()->format('Y-m-d\TH:i:s.v\Z'),
                'tenderTitle' => $tenderTitle ?: 'Siddhi Kendra - Tender Form Payment'
            ];

            $this->atomicUpdateState(function (&$state) use ($record) {
                if (!isset($state['tenderPayments']) || !is_array($state['tenderPayments'])) {
                    $state['tenderPayments'] = [];
                }
                array_unshift($state['tenderPayments'], $record);
            });

            return response()->json(['success' => true, 'payment' => $record]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() ?: 'Failed to record tender payment'], 500);
        }
    }

    /**
     * Mirrors Express GET /api/tender-payments
     */
    public function getTenderPaymentsApi()
    {
        try {
            $stateRow = DB::table('state_store')->where('key', 'state')->first();
            $tenderPayments = [];
            if ($stateRow) {
                $state = json_decode($stateRow->value, true);
                $tenderPayments = $state['tenderPayments'] ?? [];
            }
            return response()->json(['success' => true, 'tenderPayments' => $tenderPayments]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Database error', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Eye Consultation Enquiry API Methods
     */
    public function submitEyeConsultationEnquiryApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'mobile' => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
            'concern' => 'required|string',
        ], [
            'mobile.regex' => 'Please enter a valid 10-digit Indian mobile number.'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $id = 'ece_' . (int)(microtime(true) * 1000) . '_' . rand(100, 999);
        $enquiry = [
            'id' => $id,
            'name' => trim($request->name),
            'mobile' => trim($request->mobile),
            'concern' => trim($request->concern),
            'status' => 'new',
            'created_at' => now()->toIso8601String(),
            'createdAt' => now()->toIso8601String(),
        ];

        try {
            DB::statement("CREATE TABLE IF NOT EXISTS eye_consultation_enquiries (id VARCHAR(255) PRIMARY KEY, name VARCHAR(255), mobile VARCHAR(255), concern TEXT, status VARCHAR(50) DEFAULT 'new', created_at DATETIME, updated_at DATETIME)");
            EyeConsultationEnquiry::create([
                'id' => $id,
                'name' => trim($request->name),
                'mobile' => trim($request->mobile),
                'concern' => trim($request->concern),
                'status' => 'new',
            ]);
        } catch (\Exception $e) {}

        try {
            $stateRow = DB::table('state_store')->where('key', 'state')->first();
            if ($stateRow) {
                $state = json_decode($stateRow->value, true) ?: [];
                if (!isset($state['eyeConsultationEnquiries']) || !is_array($state['eyeConsultationEnquiries'])) {
                    $state['eyeConsultationEnquiries'] = [];
                }
                $state['eyeConsultationEnquiries'][] = $enquiry;
                DB::table('state_store')->where('key', 'state')->update([
                    'value' => json_encode($state, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                ]);
            }
        } catch (\Exception $e) {}

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your enquiry. Our team will contact you shortly.',
            'enquiry' => $enquiry
        ]);
    }

    public function getEyeConsultationEnquiriesApi()
    {
        try {
            $enquiries = [];
            try {
                DB::statement("CREATE TABLE IF NOT EXISTS eye_consultation_enquiries (id VARCHAR(255) PRIMARY KEY, name VARCHAR(255), mobile VARCHAR(255), concern TEXT, status VARCHAR(50) DEFAULT 'new', created_at DATETIME, updated_at DATETIME)");
                $dbItems = EyeConsultationEnquiry::orderBy('created_at', 'desc')->get();
                $enquiries = $dbItems->map(function($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'mobile' => $item->mobile,
                        'concern' => $item->concern,
                        'status' => $item->status ?? 'new',
                        'createdAt' => $item->created_at ? $item->created_at->toIso8601String() : now()->toIso8601String(),
                        'created_at' => $item->created_at ? $item->created_at->toIso8601String() : now()->toIso8601String(),
                    ];
                })->toArray();
            } catch (\Exception $e) {}

            if (empty($enquiries)) {
                $stateRow = DB::table('state_store')->where('key', 'state')->first();
                if ($stateRow) {
                    $state = json_decode($stateRow->value, true);
                    $enquiries = $state['eyeConsultationEnquiries'] ?? [];
                }
            }

            return response()->json(['success' => true, 'enquiries' => $enquiries]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Database error', 'details' => $e->getMessage()], 500);
        }
    }

    public function updateEyeConsultationEnquiryStatusApi($id, Request $request)
    {
        $status = $request->input('status');
        if (!in_array($status, ['new', 'contacted', 'closed'])) {
            return response()->json(['error' => 'Invalid status value'], 400);
        }

        try {
            try {
                $item = EyeConsultationEnquiry::find($id);
                if ($item) {
                    $item->status = $status;
                    $item->save();
                }
            } catch (\Exception $e) {}

            try {
                $stateRow = DB::table('state_store')->where('key', 'state')->first();
                if ($stateRow) {
                    $state = json_decode($stateRow->value, true) ?: [];
                    if (isset($state['eyeConsultationEnquiries']) && is_array($state['eyeConsultationEnquiries'])) {
                        foreach ($state['eyeConsultationEnquiries'] as &$enq) {
                            if (($enq['id'] ?? '') === $id) {
                                $enq['status'] = $status;
                            }
                        }
                        DB::table('state_store')->where('key', 'state')->update([
                            'value' => json_encode($state, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                        ]);
                    }
                }
            } catch (\Exception $e) {}

            return response()->json(['success' => true, 'status' => $status]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update status'], 500);
        }
    }

    public function deleteEyeConsultationEnquiryApi($id)
    {
        try {
            try {
                EyeConsultationEnquiry::destroy($id);
            } catch (\Exception $e) {}

            try {
                $stateRow = DB::table('state_store')->where('key', 'state')->first();
                if ($stateRow) {
                    $state = json_decode($stateRow->value, true) ?: [];
                    if (isset($state['eyeConsultationEnquiries']) && is_array($state['eyeConsultationEnquiries'])) {
                        $state['eyeConsultationEnquiries'] = array_values(array_filter($state['eyeConsultationEnquiries'], function($e) use ($id) {
                            return ($e['id'] ?? '') !== $id;
                        }));
                        DB::table('state_store')->where('key', 'state')->update([
                            'value' => json_encode($state, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                        ]);
                    }
                }
            } catch (\Exception $e) {}

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete enquiry'], 500);
        }
    }

    /**
     * Submit Patient Feedback
     */
    public function submitPatientFeedbackApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comments' => 'required|string',
            'name' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:50',
            'email' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $id = 'fb_' . (int)(microtime(true) * 1000) . '_' . rand(100, 999);
        $record = [
            'id' => $id,
            'rating' => (int)($request->input('rating', 5)),
            'name' => trim($request->input('name', '')),
            'mobile' => trim($request->input('mobile', '')),
            'email' => strtolower(trim($request->input('email', ''))),
            'comments' => trim($request->input('comments', '')),
            'createdAt' => now()->toIso8601String(),
            'created_at' => now()->toIso8601String(),
        ];

        try {
            DB::statement("CREATE TABLE IF NOT EXISTS patient_feedbacks (id VARCHAR(255) PRIMARY KEY, rating INTEGER, name VARCHAR(255), mobile VARCHAR(255), email VARCHAR(255), comments TEXT, created_at DATETIME)");
            DB::table('patient_feedbacks')->insert([
                'id' => $id,
                'rating' => $record['rating'],
                'name' => $record['name'],
                'mobile' => $record['mobile'],
                'email' => $record['email'],
                'comments' => $record['comments'],
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {}

        try {
            $this->atomicUpdateState(function (&$state) use ($record) {
                if (!isset($state['patientFeedbacks']) || !is_array($state['patientFeedbacks'])) {
                    $state['patientFeedbacks'] = [];
                }
                array_unshift($state['patientFeedbacks'], $record);
            });
        } catch (\Exception $e) {}

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your valuable feedback!',
            'feedback' => $record
        ]);
    }

    /**
     * Get All Patient Feedbacks
     */
    public function getPatientFeedbacksApi()
    {
        try {
            $feedbacks = [];
            try {
                DB::statement("CREATE TABLE IF NOT EXISTS patient_feedbacks (id VARCHAR(255) PRIMARY KEY, rating INTEGER, name VARCHAR(255), mobile VARCHAR(255), email VARCHAR(255), comments TEXT, created_at DATETIME)");
                $dbItems = DB::table('patient_feedbacks')->orderBy('created_at', 'desc')->get();
                $feedbacks = $dbItems->map(function($item) {
                    return (array)$item;
                })->toArray();
            } catch (\Exception $e) {}

            if (empty($feedbacks)) {
                $stateRow = DB::table('state_store')->where('key', 'state')->first();
                if ($stateRow) {
                    $state = json_decode($stateRow->value, true);
                    $feedbacks = $state['patientFeedbacks'] ?? [];
                }
            }

            return response()->json(['success' => true, 'feedbacks' => $feedbacks]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Database error', 'details' => $e->getMessage()], 500);
        }
    }
}

