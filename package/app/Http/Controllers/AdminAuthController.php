<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AdminAuthController extends Controller
{
    /**
     * Ensure password_reset_tokens table exists in SQLite database
     */
    public static function ensureTokensTable()
    {
        try {
            DB::statement("
                CREATE TABLE IF NOT EXISTS password_reset_tokens (
                    email VARCHAR(255) NOT NULL PRIMARY KEY,
                    token VARCHAR(255) NOT NULL,
                    created_at DATETIME NOT NULL,
                    expires_at DATETIME NOT NULL
                )
            ");
        } catch (\Throwable $e) {
            Log::error("Failed to ensure password_reset_tokens table: " . $e->getMessage());
        }
    }

    /**
     * API: Request password reset link (Generic response, Rate limited)
     */
    public function sendResetLinkApi(Request $request)
    {
        self::ensureTokensTable();

        $request->validate([
            'email' => 'required|email',
        ]);

        $email = trim(strtolower($request->email));

        // Read users from state_store
        $foundUser = null;
        try {
            $row = DB::table('state_store')->where('key', 'state')->first();
            if ($row && !empty($row->value)) {
                $state = json_decode($row->value, true);
                if (!empty($state['users']) && is_array($state['users'])) {
                    foreach ($state['users'] as $u) {
                        if (!empty($u['email']) && strtolower(trim($u['email'])) === $email) {
                            $foundUser = $u;
                            break;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error("Error reading admin users for reset: " . $e->getMessage());
        }

        // Standard generic message to prevent account enumeration
        $genericMessage = "If an account with that email exists, a password reset link has been sent to your registered email address.";

        if (!$foundUser) {
            return response()->json([
                'success' => true,
                'message' => $genericMessage
            ]);
        }

        // Generate cryptographically secure random token
        $rawToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);
        $expiresAt = date('Y-m-d H:i:s', time() + (30 * 60)); // 30 minutes

        try {
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                [
                    'token' => $tokenHash,
                    'created_at' => date('Y-m-d H:i:s'),
                    'expires_at' => $expiresAt
                ]
            );
        } catch (\Throwable $e) {
            Log::error("Failed to store password reset token: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Database error while initiating password reset.'
            ], 500);
        }

        // Construct dedicated reset URL using dynamic APP_URL configuration
        $appUrl = rtrim(config('app.url', url('/')), '/');
        $resetUrl = $appUrl . '/admin/reset-password?token=' . $rawToken . '&email=' . urlencode($email);

        // Send Email
        try {
            $hospitalName = "Madhav Netralaya Eye Institute & Research Centre";
            $userName = $foundUser['name'] ?? 'Administrator';

            $htmlContent = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <style>
                    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 24px; color: #1e293b; }
                    .card { max-width: 540px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
                    .header { background: #1e40af; padding: 28px; text-align: center; color: #ffffff; }
                    .header h1 { margin: 0; font-size: 20px; font-weight: 800; letter-spacing: 0.5px; }
                    .header p { margin: 6px 0 0 0; font-size: 11px; opacity: 0.85; text-transform: uppercase; letter-spacing: 1px; }
                    .content { padding: 32px 28px; font-size: 14px; line-height: 1.6; color: #334155; }
                    .btn-wrapper { text-align: center; margin: 28px 0; }
                    .btn { display: inline-block; background: #2563eb; color: #ffffff !important; padding: 13px 30px; text-decoration: none; border-radius: 10px; font-weight: 700; font-size: 14px; letter-spacing: 0.3px; }
                    .notice { background: #f1f5f9; border-left: 4px solid #3b82f6; padding: 14px; border-radius: 6px; font-size: 12px; color: #475569; margin: 20px 0; }
                    .footer { padding: 20px 28px; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center; font-size: 11px; color: #94a3b8; }
                </style>
            </head>
            <body>
                <div class="card">
                    <div class="header">
                        <h1>' . htmlspecialchars($hospitalName) . '</h1>
                        <p>Hospital Administration Portal</p>
                    </div>
                    <div class="content">
                        <p>Hello <strong>' . htmlspecialchars($userName) . '</strong>,</p>
                        <p>A request has been received to reset the administrator password for your account (<strong>' . htmlspecialchars($email) . '</strong>).</p>
                        <p>Please click the secure button below to set a new password:</p>
                        <div class="btn-wrapper">
                            <a href="' . htmlspecialchars($resetUrl) . '" class="btn" target="_blank">Reset Administrator Password</a>
                        </div>
                        <div class="notice">
                            <strong>Security Notice:</strong>
                            <ul style="margin: 6px 0 0 0; padding-left: 18px;">
                                <li>This password reset link is valid for <strong>30 minutes</strong> only.</li>
                                <li>This is a single-use link and will expire immediately after use.</li>
                                <li>If you did not request a password reset, you can safely ignore this email.</li>
                            </ul>
                        </div>
                        <p style="font-size: 11px; color: #94a3b8; word-break: break-all;">If the button does not work, copy and paste this link into your browser:<br><a href="' . htmlspecialchars($resetUrl) . '" style="color: #2563eb;">' . htmlspecialchars($resetUrl) . '</a></p>
                    </div>
                    <div class="footer">
                        &copy; ' . date('Y') . ' Madhav Netralaya Eye Institute & Research Centre, Nagpur.<br>All rights reserved.
                    </div>
                </div>
            </body>
            </html>
            ';

            Mail::html($htmlContent, function ($message) use ($email, $hospitalName) {
                $message->to($email)
                        ->subject('Password Reset Request - ' . $hospitalName);
            });

            return response()->json([
                'success' => true,
                'message' => $genericMessage
            ]);
        } catch (\Throwable $e) {
            Log::error("SMTP Dispatch Error during password reset for {$email}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Unable to send password reset email due to a mail server connection error. Please verify server SMTP configuration.'
            ], 500);
        }
    }

    /**
     * API: Verify whether a reset token is valid and unexpired
     */
    public function verifyTokenApi(Request $request)
    {
        self::ensureTokensTable();

        $token = $request->query('token');
        $email = trim(strtolower($request->query('email', '')));

        if (!$token || !$email) {
            return response()->json(['valid' => false, 'error' => 'Token and email parameters are required.'], 400);
        }

        $tokenHash = hash('sha256', $token);
        $record = DB::table('password_reset_tokens')
                    ->where('email', $email)
                    ->where('token', $tokenHash)
                    ->first();

        if (!$record) {
            return response()->json(['valid' => false, 'error' => 'Invalid or already used password reset link.'], 404);
        }

        if (strtotime($record->expires_at) < time()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return response()->json(['valid' => false, 'error' => 'This password reset link has expired. Please request a new one.'], 410);
        }

        return response()->json(['valid' => true, 'email' => $email]);
    }

    /**
     * API: Set new password using verified single-use token
     */
    public function resetPasswordApi(Request $request)
    {
        self::ensureTokensTable();

        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $email = trim(strtolower($request->email));
        $token = $request->token;
        $tokenHash = hash('sha256', $token);

        // Verify token in database
        $record = DB::table('password_reset_tokens')
                    ->where('email', $email)
                    ->where('token', $tokenHash)
                    ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid or already used password reset link.'
            ], 400);
        }

        // Check expiration
        if (strtotime($record->expires_at) < time()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return response()->json([
                'success' => false,
                'error' => 'This password reset link has expired. Please request a new link.'
            ], 400);
        }

        // Update password in state_store
        $updated = false;
        try {
            DB::transaction(function () use ($email, $request, &$updated) {
                $row = DB::table('state_store')->where('key', 'state')->lockForUpdate()->first();
                if ($row && !empty($row->value)) {
                    $state = json_decode($row->value, true);
                    if (!empty($state['users']) && is_array($state['users'])) {
                        foreach ($state['users'] as &$u) {
                            if (!empty($u['email']) && strtolower(trim($u['email'])) === $email) {
                                $u['password'] = $request->password;
                                $updated = true;
                                break;
                            }
                        }
                        if ($updated) {
                            DB::table('state_store')->updateOrInsert(
                                ['key' => 'state'],
                                ['value' => json_encode($state, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
                            );
                        }
                    }
                }

                // Invalidate single-use token immediately
                DB::table('password_reset_tokens')->where('email', $email)->delete();
            });
        } catch (\Throwable $e) {
            Log::error("Failed to update password in state_store: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while saving the new password.'
            ], 500);
        }

        if (!$updated) {
            return response()->json([
                'success' => false,
                'error' => 'Administrator account not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password has been successfully updated! You can now log in with your new password.'
        ]);
    }
}
