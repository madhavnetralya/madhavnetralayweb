<?php

require __DIR__ . '/package/vendor/autoload.php';
$app = require_once __DIR__ . '/package/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "========================================================\n";
echo "VERIFYING UPDATED SMTP CONFIGURATION (DEPLOYMENT PACKAGE)\n";
echo "========================================================\n\n";

echo "MAIL_MAILER:       " . config('mail.default') . "\n";
echo "MAIL_HOST:         " . config('mail.mailers.smtp.host') . "\n";
echo "MAIL_PORT:         " . config('mail.mailers.smtp.port') . "\n";
echo "MAIL_USERNAME:     " . config('mail.mailers.smtp.username') . "\n";
echo "MAIL_PASSWORD:     " . (config('mail.mailers.smtp.password') ? '***configured***' : 'NOT SET') . "\n";
echo "MAIL_ENCRYPTION:   " . var_export(config('mail.mailers.smtp.encryption'), true) . "\n";
echo "MAIL_FROM_ADDRESS: " . config('mail.from.address') . "\n";
echo "MAIL_FROM_NAME:    " . config('mail.from.name') . "\n\n";

$pass = (
    config('mail.default') === 'smtp' &&
    config('mail.mailers.smtp.host') === 'mail.madhavnetralaya.org' &&
    (int)config('mail.mailers.smtp.port') === 587 &&
    config('mail.mailers.smtp.username') === 'no-reply@madhavnetralaya.org' &&
    config('mail.mailers.smtp.password') === 'GoGreen12345' &&
    (config('mail.mailers.smtp.encryption') === null || config('mail.mailers.smtp.encryption') === '')
);

if ($pass) {
    echo "[PASS] SMTP configuration matches all required parameters!\n";
    exit(0);
} else {
    echo "[FAIL] SMTP configuration mismatch.\n";
    exit(1);
}
