<?php

namespace App\Http\Controllers;

use App\Models\User;
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
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    protected function getSettings()
    {
        try {
            $row = DB::table('state_store')->where('key', 'state')->first();
            if ($row && !empty($row->value)) {
                $state = json_decode($row->value, true);
                if (!empty($state['settings'])) {
                    return (object)$state['settings'];
                }
            }
        } catch (\Throwable $e) {}

        return (object)[
            'hospital_name' => 'Madhav Netralaya Eye Institute & Research Centre',
            'logo' => 'MN'
        ];
    }

    /**
     * Show administrator login screen
     */
    public function showLogin()
    {
        return view('admin.login', [
            'settings' => $this->getSettings()
        ]);
    }

    /**
     * Authenticate Administrator
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Attempt authentication using standard guard
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our administrator access keys.',
        ])->withInput($request->only('username'));
    }

    /**
     * Show Forgot Password screen (requests email only)
     */
    public function showForgotPassword()
    {
        return view('admin.forgot-password', [
            'settings' => $this->getSettings()
        ]);
    }

    /**
     * Handle Forgot Password email submission (sends token link via mail)
     */
    public function sendResetLink(Request $request)
    {
        AdminAuthController::ensureTokensTable();

        $request->validate([
            'email' => 'required|email',
        ]);

        $email = trim(strtolower($request->email));

        // Check if user exists in state_store or users table
        $userExists = false;
        try {
            $row = DB::table('state_store')->where('key', 'state')->first();
            if ($row && !empty($row->value)) {
                $state = json_decode($row->value, true);
                if (!empty($state['users']) && is_array($state['users'])) {
                    foreach ($state['users'] as $u) {
                        if (!empty($u['email']) && strtolower(trim($u['email'])) === $email) {
                            $userExists = true;
                            break;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error("Error checking user for password reset: " . $e->getMessage());
        }

        $genericMessage = 'If an account with that email exists, a password reset link has been sent to your registered email address.';

        if (!$userExists) {
            return back()->with('status', $genericMessage);
        }

        $rawToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);
        $expiresAt = date('Y-m-d H:i:s', time() + (30 * 60));

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => $tokenHash,
                'created_at' => date('Y-m-d H:i:s'),
                'expires_at' => $expiresAt
            ]
        );

        $appUrl = rtrim(config('app.url', url('/')), '/');
        $resetUrl = $appUrl . '/admin/reset-password?token=' . $rawToken . '&email=' . urlencode($email);

        try {
            $hospitalName = 'Madhav Netralaya Eye Institute & Research Centre';
            $htmlContent = '
            <!DOCTYPE html>
            <html>
            <body style="font-family: sans-serif; background-color: #f8fafc; padding: 24px; color: #1e293b;">
                <div style="max-width: 540px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden;">
                    <div style="background: #1e40af; padding: 28px; text-align: center; color: #ffffff;">
                        <h1 style="margin:0; font-size: 20px;">' . htmlspecialchars($hospitalName) . '</h1>
                        <p style="margin: 6px 0 0 0; font-size: 11px; opacity: 0.85; text-transform: uppercase;">Administrator Access Recovery</p>
                    </div>
                    <div style="padding: 32px 28px; font-size: 14px; line-height: 1.6; color: #334155;">
                        <p>A request was received to reset your administrator password.</p>
                        <div style="text-align: center; margin: 28px 0;">
                            <a href="' . htmlspecialchars($resetUrl) . '" style="background: #2563eb; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 14px;">Reset Password</a>
                        </div>
                        <p style="font-size: 12px; color: #64748b;">This link expires in <strong>30 minutes</strong> and can only be used once.</p>
                    </div>
                </div>
            </body>
            </html>';

            Mail::html($htmlContent, function ($message) use ($email, $hospitalName) {
                $message->to($email)->subject('Password Reset Request - ' . $hospitalName);
            });

            return back()->with('status', $genericMessage);
        } catch (\Throwable $e) {
            Log::error("Failed to send blade password reset email to {$email}: " . $e->getMessage());
            return back()->withErrors(['email' => 'Unable to send password reset email due to a mail server connection error. Please verify server SMTP configuration.']);
        }
    }

    /**
     * Show Password Reset screen (requires valid, unexpired token)
     */
    public function showPasswordReset(Request $request)
    {
        AdminAuthController::ensureTokensTable();

        $token = $request->query('token');
        $email = trim(strtolower($request->query('email', '')));

        if (!$token || !$email) {
            return redirect()->route('admin.password.request')->withErrors(['email' => 'A valid reset token and email are required.']);
        }

        $tokenHash = hash('sha256', $token);
        $record = DB::table('password_reset_tokens')
                    ->where('email', $email)
                    ->where('token', $tokenHash)
                    ->first();

        if (!$record) {
            return redirect()->route('admin.password.request')->withErrors(['email' => 'Invalid or already used password reset link.']);
        }

        if (strtotime($record->expires_at) < time()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return redirect()->route('admin.password.request')->withErrors(['email' => 'This password reset link has expired. Please request a new one.']);
        }

        return view('admin.reset', [
            'settings' => $this->getSettings(),
            'token' => $token,
            'email' => $email
        ]);
    }

    /**
     * Handle Password Reset via verified Token
     */
    public function handlePasswordReset(Request $request)
    {
        AdminAuthController::ensureTokensTable();

        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $email = trim(strtolower($request->email));
        $token = $request->token;
        $tokenHash = hash('sha256', $token);

        $record = DB::table('password_reset_tokens')
                    ->where('email', $email)
                    ->where('token', $tokenHash)
                    ->first();

        if (!$record) {
            return back()->withErrors(['email' => 'Invalid or already used password reset link.'])->withInput();
        }

        if (strtotime($record->expires_at) < time()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return redirect()->route('admin.password.request')->withErrors(['email' => 'This password reset link has expired. Please request a new one.']);
        }

        // Update password in state_store & users table
        try {
            DB::transaction(function () use ($email, $request) {
                $row = DB::table('state_store')->where('key', 'state')->lockForUpdate()->first();
                if ($row && !empty($row->value)) {
                    $state = json_decode($row->value, true);
                    if (!empty($state['users']) && is_array($state['users'])) {
                        foreach ($state['users'] as &$u) {
                            if (!empty($u['email']) && strtolower(trim($u['email'])) === $email) {
                                $u['password'] = $request->password;
                                break;
                            }
                        }
                        DB::table('state_store')->updateOrInsert(
                            ['key' => 'state'],
                            ['value' => json_encode($state, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
                        );
                    }
                }

                // Invalidate single-use token immediately
                DB::table('password_reset_tokens')->where('email', $email)->delete();
            });
        } catch (\Throwable $e) {
            Log::error("Failed to update password: " . $e->getMessage());
            return back()->withErrors(['password' => 'An error occurred while saving the new password.'])->withInput();
        }

        return redirect()->route('admin.login')->with('success', 'Your administrator password was reset successfully! Please log in with your new password.');
    }

    /**
     * Log out of Admin panel
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    /**
     * Change Administrator Password from inside Dashboard
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'password' => 'required|string|min:4|confirmed',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Password updated successfully for administrator ' . $user->name . '!');
    }

    /**
     * Admin Dashboard view containing all lists
     */
    public function dashboard()
    {
        return view('admin.dashboard', [
            'settings' => Setting::first(),
            'users' => User::all(),
            'appointments' => Appointment::orderBy('date', 'desc')->get(),
            'enquiries' => Enquiry::orderBy('created_at', 'desc')->get(),
            'doctors' => Doctor::all(),
            'departments' => Department::all(),
            'blogs' => Blog::orderBy('created_at', 'desc')->get(),
            'notices' => Notice::orderBy('date', 'desc')->get(),
            'sliders' => Slider::orderBy('order')->get(),
            'testimonials' => Testimonial::orderBy('created_at', 'desc')->get(),
            'events' => Event::orderBy('date', 'desc')->get(),
            'subscribers' => Subscriber::orderBy('subscribed_at', 'desc')->get(),
        ]);
    }

    /**
     * Update global hospital settings
     */
    public function updateSettings(Request $request)
    {
        $settings = Setting::first();
        if (!$settings) {
            $settings = new Setting();
            $settings->id = 'main_settings';
        }

        $settings->hospital_name = $request->hospital_name;
        $settings->tagline = $request->tagline;
        $settings->primary_color = $request->primary_color;
        $settings->secondary_color = $request->secondary_color;
        $settings->address = $request->address;
        $settings->whatsapp_number = $request->whatsapp_number;
        $settings->google_analytics_id = $request->google_analytics_id;

        // Dynamic lists stored in JSON attributes
        $settings->phone_numbers = array_filter(explode(',', $request->phone_numbers));
        $settings->emails = array_filter(explode(',', $request->emails));
        $settings->emergency_contacts = array_filter(explode(',', $request->emergency_contacts));

        $settings->save();

        return back()->with('success', 'Hospital settings updated dynamically!');
    }

    /**
     * Update appointment status (pending, confirmed, completed, cancelled)
     */
    public function updateAppointmentStatus($id, Request $request)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->status = $request->status;
        $appointment->save();

        return back()->with('success', 'Appointment status updated to ' . ucfirst($request->status) . '!');
    }

    /**
     * Update contact enquiry status (read, unread)
     */
    public function updateEnquiryStatus($id, Request $request)
    {
        $enquiry = Enquiry::findOrFail($id);
        $enquiry->status = $request->status;
        $enquiry->save();

        return back()->with('success', 'Enquiry status marked as ' . ucfirst($request->status) . '!');
    }

    // ==========================================================
    // CRUD Actions for Doctors, Departments, Blogs, Notices, etc.
    // ==========================================================

    public function storeDoctor(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'qualification' => 'required|string',
            'experience' => 'required|integer',
            'specialty' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'biography' => 'nullable|string',
        ]);

        Doctor::create([
            'id' => 'doc_' . time(),
            'name' => $request->name,
            'qualification' => $request->qualification,
            'experience' => $request->experience,
            'specialty' => $request->specialty,
            'department_id' => $request->department_id,
            'biography' => $request->biography,
            'photo' => $request->photo ?? 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?auto=format&fit=crop&w=600&q=80',
            'languages' => array_filter(explode(',', $request->languages)),
            'consultation_timing' => [
                'days' => array_filter(explode(',', $request->days)),
                'time' => $request->time
            ]
        ]);

        return back()->with('success', 'Doctor registered successfully!');
    }

    public function storeDepartment(Request $request)
    {
        $request->validate([
            'id' => 'required|string|unique:departments,id',
            'name' => 'required|string',
            'overview' => 'required|string',
        ]);

        Department::create([
            'id' => Str::slug($request->id),
            'name' => $request->name,
            'overview' => $request->overview,
            'banner_image' => $request->banner_image ?? 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=1200&q=80',
            'symptoms' => array_filter(explode("\n", $request->symptoms)),
            'diagnosis' => array_filter(explode("\n", $request->diagnosis)),
            'treatments' => array_filter(explode("\n", $request->treatments)),
            'technology' => array_filter(explode("\n", $request->technology)),
        ]);

        return back()->with('success', 'Clinical specialty added!');
    }

    public function storeBlog(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'category_id' => 'required|exists:blog_categories,id',
            'author' => 'required|string',
        ]);

        Blog::create([
            'id' => 'b_' . time(),
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'content' => $request->content,
            'category_id' => $request->category_id,
            'author' => $request->author,
            'status' => 'published',
            'published_at' => now(),
            'featured_image' => $request->featured_image ?? 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=800&q=80',
            'tags' => array_filter(explode(',', $request->tags)),
        ]);

        return back()->with('success', 'Blog article published successfully!');
    }

    public function storeNotice(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'category' => 'required|string',
            'date' => 'required|date',
        ]);

        Notice::create([
            'id' => 'n_' . time(),
            'title' => $request->title,
            'content' => $request->content,
            'category' => $request->category,
            'date' => $request->date,
            'important' => $request->has('important'),
        ]);

        return back()->with('success', 'Notice published successfully!');
    }

    // ==========================================================
    // API Bulk Sync Endpoint (Mirrors Express POST /api/state)
    // ==========================================================
    public function postApiState(Request $request)
    {
        $state = $request->all();
        if (!$state || !is_array($state)) {
            return response()->json(['error' => 'Invalid state object'], 400);
        }

        try {
            DB::beginTransaction();

            // Clear and reload database collections to synchronize with JSON state upload
            if (isset($state['settings'])) {
                Setting::truncate();
                Setting::create($state['settings']);
            }

            if (isset($state['users']) && is_array($state['users'])) {
                User::truncate();
                foreach ($state['users'] as $u) {
                    // Retain existing hash or specify secure random hash
                    $u['password'] = $u['password'] ?? Hash::make(\Illuminate\Support\Str::random(32));
                    User::create($u);
                }
            }

            if (isset($state['departments']) && is_array($state['departments'])) {
                Department::truncate();
                foreach ($state['departments'] as $d) {
                    Department::create($d);
                }
            }

            if (isset($state['doctors']) && is_array($state['doctors'])) {
                Doctor::truncate();
                foreach ($state['doctors'] as $doc) {
                    Doctor::create($doc);
                }
            }

            if (isset($state['diagnostics']) && is_array($state['diagnostics'])) {
                Diagnostic::truncate();
                foreach ($state['diagnostics'] as $diag) {
                    Diagnostic::create($diag);
                }
            }

            if (isset($state['facilities']) && is_array($state['facilities'])) {
                Facility::truncate();
                foreach ($state['facilities'] as $fac) {
                    Facility::create($fac);
                }
            }

            if (isset($state['blogCategories']) && is_array($state['blogCategories'])) {
                BlogCategory::truncate();
                foreach ($state['blogCategories'] as $cat) {
                    BlogCategory::create($cat);
                }
            }

            if (isset($state['blogs']) && is_array($state['blogs'])) {
                Blog::truncate();
                foreach ($state['blogs'] as $b) {
                    Blog::create($b);
                }
            }

            if (isset($state['events']) && is_array($state['events'])) {
                Event::truncate();
                foreach ($state['events'] as $e) {
                    Event::create($e);
                }
            }

            if (isset($state['testimonials']) && is_array($state['testimonials'])) {
                Testimonial::truncate();
                foreach ($state['testimonials'] as $t) {
                    Testimonial::create($t);
                }
            }

            if (isset($state['gallery']) && is_array($state['gallery'])) {
                GalleryItem::truncate();
                foreach ($state['gallery'] as $g) {
                    GalleryItem::create($g);
                }
            }

            if (isset($state['notices']) && is_array($state['notices'])) {
                Notice::truncate();
                foreach ($state['notices'] as $n) {
                    Notice::create($n);
                }
            }

            if (isset($state['sliders']) && is_array($state['sliders'])) {
                Slider::truncate();
                foreach ($state['sliders'] as $s) {
                    Slider::create($s);
                }
            }

            if (isset($state['appointments']) && is_array($state['appointments'])) {
                Appointment::truncate();
                foreach ($state['appointments'] as $app) {
                    Appointment::create($app);
                }
            }

            if (isset($state['enquiries']) && is_array($state['enquiries'])) {
                Enquiry::truncate();
                foreach ($state['enquiries'] as $enq) {
                    Enquiry::create($enq);
                }
            }

            if (isset($state['subscribers']) && is_array($state['subscribers'])) {
                Subscriber::truncate();
                foreach ($state['subscribers'] as $sub) {
                    Subscriber::create($sub);
                }
            }

            if (isset($state['seoMeta']) && is_array($state['seoMeta'])) {
                SeoMeta::truncate();
                foreach ($state['seoMeta'] as $key => $meta) {
                    $meta['page_key'] = $key;
                    SeoMeta::create($meta);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Database state synchronized dynamically',
                'state' => $state
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Database synchronization failed',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
