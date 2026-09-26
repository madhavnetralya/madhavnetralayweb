<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Dynamic robots.txt with staging protection
Route::get('/robots.txt', function () {
    $env = config('app.env', 'production');
    if ($env !== 'production') {
        $content = "User-agent: *\nDisallow: /\n";
    } else {
        $sitemapUrl = url('/sitemap.xml');
        $content = "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /api/\n\nSitemap: {$sitemapUrl}\n";
    }
    return response($content, 200, ['Content-Type' => 'text/plain']);
});

// Dynamic sitemap.xml covering core static pages + active CMS custom pages
Route::get('/sitemap.xml', function () {
    $baseUrl = rtrim(config('app.url', url('/')), '/');
    if (empty($baseUrl) || $baseUrl === 'http://localhost') {
        $baseUrl = 'https://www.madhavnetralaya.org';
    }

    $staticRoutes = [
        ['path' => '/', 'changefreq' => 'daily', 'priority' => '1.0'],
        ['path' => '/about', 'changefreq' => 'weekly', 'priority' => '0.8'],
        ['path' => '/departments', 'changefreq' => 'weekly', 'priority' => '0.9'],
        ['path' => '/doctors', 'changefreq' => 'weekly', 'priority' => '0.9'],
        ['path' => '/diagnostics', 'changefreq' => 'monthly', 'priority' => '0.8'],
        ['path' => '/events', 'changefreq' => 'weekly', 'priority' => '0.8'],
        ['path' => '/blogs', 'changefreq' => 'weekly', 'priority' => '0.8'],
        ['path' => '/contact', 'changefreq' => 'monthly', 'priority' => '0.8'],
        ['path' => '/faq', 'changefreq' => 'monthly', 'priority' => '0.7'],
        ['path' => '/careers', 'changefreq' => 'weekly', 'priority' => '0.7'],
        ['path' => '/outreach-camps', 'changefreq' => 'weekly', 'priority' => '0.8'],
        ['path' => '/low-vision-rehab-images', 'changefreq' => 'monthly', 'priority' => '0.7'],
        ['path' => '/hospital-location', 'changefreq' => 'monthly', 'priority' => '0.8'],
        ['path' => '/eye-donation', 'changefreq' => 'monthly', 'priority' => '0.8'],
        ['path' => '/pledge', 'changefreq' => 'monthly', 'priority' => '0.8'],
        ['path' => '/doctor-availability', 'changefreq' => 'daily', 'priority' => '0.8'],
    ];

    $today = date('Y-m-d');
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach ($staticRoutes as $r) {
        $loc = htmlspecialchars($baseUrl . $r['path'], ENT_XML1);
        $xml .= "  <url>\n";
        $xml .= "    <loc>{$loc}</loc>\n";
        $xml .= "    <lastmod>{$today}</lastmod>\n";
        $xml .= "    <changefreq>{$r['changefreq']}</changefreq>\n";
        $xml .= "    <priority>{$r['priority']}</priority>\n";
        $xml .= "  </url>\n";
    }

    // Dynamic CMS custom pages
    try {
        $row = DB::table('state_store')->where('key', 'state')->first();
        if ($row && !empty($row->value)) {
            $state = json_decode($row->value, true);
            if (!empty($state['customPages']) && is_array($state['customPages'])) {
                foreach ($state['customPages'] as $page) {
                    if (isset($page['enabled']) && $page['enabled'] === false) continue;
                    $slug = !empty($page['slug']) ? trim($page['slug'], '/') : (isset($page['id']) ? trim($page['id'], '/') : '');
                    if (empty($slug)) continue;

                    $loc = htmlspecialchars($baseUrl . '/' . $slug, ENT_XML1);
                    $lastmod = !empty($page['date']) ? date('Y-m-d', strtotime($page['date'])) : $today;
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$loc}</loc>\n";
                    $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
                    $xml .= "    <changefreq>monthly</changefreq>\n";
                    $xml .= "    <priority>0.7</priority>\n";
                    $xml .= "  </url>\n";
                }
            }
        }
    } catch (\Throwable $e) {
        // Fallback without breaking sitemap output
    }

    $xml .= '</urlset>';

    return response($xml, 200, ['Content-Type' => 'application/xml']);
});

// Blade Admin Gateway Routes
Route::prefix('admin')->group(function () {
    Route::get('/login', [\App\Http\Controllers\AdminController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [\App\Http\Controllers\AdminController::class, 'login'])->name('admin.login.submit');
    Route::get('/forgot-password', [\App\Http\Controllers\AdminController::class, 'showForgotPassword'])->name('admin.password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\AdminController::class, 'sendResetLink'])->name('admin.password.email')->middleware('throttle:5,15');
    Route::get('/reset-password', [\App\Http\Controllers\AdminController::class, 'showPasswordReset'])->name('admin.password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\AdminController::class, 'handlePasswordReset'])->name('admin.password.update')->middleware('throttle:10,15');
    Route::post('/logout', [\App\Http\Controllers\AdminController::class, 'logout'])->name('admin.logout');
});

// React SPA fallback
Route::get('/{any}', function () {
    $indexPath = public_path('index.html');
    if (!file_exists($indexPath)) {
        return response('Application bundle index.html not found.', 500);
    }
    $response = response(file_get_contents($indexPath))
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');

    if (config('app.env') !== 'production') {
        $response->header('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }

    return $response;
})->where('any', '.*');
