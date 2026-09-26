<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MediaMigrateCommand extends Command
{
    protected $signature = 'media:migrate-base64 {--dry-run : Perform a dry run without modifying any data}';
    protected $description = 'Migrate Base64 encoded images from SQLite state_store to Laravel persistent file storage';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        $this->info("==================================================");
        $this->info("Base64 Media Migration — " . ($isDryRun ? "DRY RUN" : "ACTUAL MIGRATION"));
        $this->info("==================================================\n");

        $row = DB::table('state_store')->where('key', 'state')->first();
        if (!$row) {
            $this->error('State store not found.');
            return 1;
        }

        $state = json_decode($row->value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON in state_store.');
            return 1;
        }

        $results = [];
        $stats = [
            'total' => 0, 'jpeg' => 0, 'png' => 0, 'webp' => 0, 'gif' => 0, 'svg' => 0, 'other' => 0,
            'valid' => 0, 'invalid' => 0, 'estimated_bytes' => 0, 'created' => 0
        ];
        $hashes = [];
        $duplicates = 0;

        $this->scanObj($state, 'state', $results, $stats, $hashes, $duplicates, $isDryRun);

        $this->info(sprintf("Total images found: %d\n", $stats['total']));
        $this->info(sprintf("JPEG: %d, PNG: %d, WebP: %d, GIF: %d, SVG: %d", $stats['jpeg'], $stats['png'], $stats['webp'], $stats['gif'], $stats['svg']));
        $this->info(sprintf("Valid images: %d, Invalid images: %d", $stats['valid'], $stats['invalid']));
        $this->info(sprintf("Estimated storage: %s MB", number_format($stats['estimated_bytes'] / 1048576, 2)));
        $this->info(sprintf("Duplicate images: %d\n", $duplicates));
        $this->info(sprintf("Files created: %d\n", $stats['created']));

        $exampleCount = 0;
        foreach ($results as $res) {
            if ($res['status'] === 'READY' || $res['status'] === 'MIGRATED') {
                if ($exampleCount < 5) {
                    $this->info($res['path']);
                    $this->info("→ storage/app/public/" . $res['proposed_file'] . "\n");
                    $exampleCount++;
                }
            }
        }

        if (!$isDryRun && $stats['created'] > 0) {
            DB::table('state_store')->where('key', 'state')->update(['value' => json_encode($state)]);
            $this->info("Successfully updated database.db JSON state.\n");
        }

        $reportPath = storage_path('logs/media-migration-report.json');
        file_put_contents($reportPath, json_encode([
            'mode' => $isDryRun ? 'DRY_RUN' : 'ACTUAL',
            'summary' => $stats,
            'duplicates' => $duplicates,
            'details' => $results
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info("Detailed JSON report saved to: " . $reportPath);
        return 0;
    }

    private function scanObj(&$obj, $path, &$results, &$stats, &$hashes, &$duplicates, $isDryRun)
    {
        if (is_string($obj) && str_starts_with($obj, 'data:image/')) {
            $stats['total']++;
            if (preg_match('/^data:(image\/[a-zA-Z0-9\+\-]+);base64,(.+)$/', $obj, $matches)) {
                $mimeType = $matches[1];
                $base64Data = $matches[2];
                $decoded = base64_decode($base64Data, true);

                if ($decoded === false) {
                    $stats['invalid']++;
                    $results[] = ['path' => $path, 'mime_type' => $mimeType, 'status' => 'ERROR', 'error' => 'Invalid Base64'];
                    return;
                }

                $stats['estimated_bytes'] += strlen($decoded);
                $stats['valid']++;

                $ext = 'jpg';
                if (str_contains($mimeType, 'jpeg')) { $stats['jpeg']++; $ext = 'jpg'; }
                elseif (str_contains($mimeType, 'png')) { $stats['png']++; $ext = 'png'; }
                elseif (str_contains($mimeType, 'webp')) { $stats['webp']++; $ext = 'webp'; }
                elseif (str_contains($mimeType, 'gif')) { $stats['gif']++; $ext = 'gif'; }
                elseif (str_contains($mimeType, 'svg')) { $stats['svg']++; $ext = 'svg'; }
                else { $stats['other']++; $ext = 'bin'; }

                $hash = md5($decoded);
                if (isset($hashes[$hash])) $duplicates++;
                $hashes[$hash] = true;

                $folder = 'general';
                if (str_contains($path, 'homepage')) $folder = 'homepage';
                elseif (str_contains($path, 'career')) $folder = 'career';
                elseif (str_contains($path, 'gallery')) $folder = 'gallery';
                elseif (str_contains($path, 'settings')) $folder = 'settings';

                $safeName = str_replace(['.', '[', ']'], '-', $path);
                $safeName = preg_replace('/-+/', '-', $safeName);
                $safeName = trim($safeName, '-');
                $shortHash = substr($hash, 0, 6);
                $proposedPath = "cms/{$folder}/{$safeName}-{$shortHash}.{$ext}";
                
                $results[] = ['path' => $path, 'proposed_file' => $proposedPath, 'status' => $isDryRun ? 'READY' : 'MIGRATED'];

                if (!$isDryRun) {
                    Storage::disk('public')->put($proposedPath, $decoded);
                    $stats['created']++;
                    // Crucial: Update the actual JSON value reference!
                    $obj = $proposedPath; 
                }
            } else {
                $stats['invalid']++;
                $results[] = ['path' => $path, 'status' => 'ERROR', 'error' => 'Malformed URL'];
            }
        } elseif (is_array($obj)) {
            foreach ($obj as $key => $value) {
                // Must pass by reference to allow modification
                $this->scanObj($obj[$key], $path . (is_int($key) ? "[$key]" : ".$key"), $results, $stats, $hashes, $duplicates, $isDryRun);
            }
        }
    }
}
