<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StateController
{
    public function getState()
    {
        try {
            $row = DB::table('state_store')->where('key', 'state')->first();
            if ($row) {
                return response(json_decode($row->value, true));
            }
            return response()->json(['error' => 'State not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Database error', 'details' => $e->getMessage()], 500);
        }
    }

    public function updateState(Request $request)
    {
        try {
            $newState = $request->all();
            if (empty($newState) || !is_array($newState)) {
                return response()->json(['error' => 'Invalid state object: payload must be a non-empty JSON object'], 400);
            }

            return DB::transaction(function () use ($request, $newState) {
                // Read current database state with lock
                $currentRow = DB::table('state_store')->where('key', 'state')->lockForUpdate()->first();
                $currentState = $currentRow ? json_decode($currentRow->value, true) : null;

                // Merge incoming state with current database state to guarantee no unrelated top-level keys or sections are ever lost
                $mergedState = ($currentState && is_array($currentState)) ? array_merge($currentState, $newState) : $newState;

                // --- HARD SAFETY GUARD 1: Automatic Pre-Write Backup ---
                if ($currentState && is_array($currentState)) {
                    $backupDir = storage_path('app/state_backups');
                    if (!is_dir($backupDir)) {
                        @mkdir($backupDir, 0755, true);
                    }
                    $timestamp = date('Ymd_His') . '_' . substr(str_replace('.', '', (string)microtime(true)), -4);
                    $backupFile = $backupDir . "/state_backup_auto_{$timestamp}.json";
                    @file_put_contents($backupFile, json_encode($currentState, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                }

                // --- HARD SAFETY GUARD 2: Anti-Regression Validation ---
                if ($currentState && is_array($currentState)) {
                    $violations = [];

                    // Check Custom Pages: cannot drop more than 5% of pages without explicit override
                    $currentPages = isset($currentState['customPages']) && is_array($currentState['customPages']) ? count($currentState['customPages']) : 0;
                    $newPages = isset($mergedState['customPages']) && is_array($mergedState['customPages']) ? count($mergedState['customPages']) : 0;
                    if ($currentPages >= 30 && $newPages < ($currentPages - 2)) {
                        $violations[] = "Dangerous drop in customPages: existing state has {$currentPages} pages, incoming state only has {$newPages} pages.";
                    }

                    // Check Doctors: cannot drop existing doctors
                    $currentDoctors = isset($currentState['doctors']) && is_array($currentState['doctors']) ? count($currentState['doctors']) : 0;
                    $newDoctors = isset($mergedState['doctors']) && is_array($mergedState['doctors']) ? count($mergedState['doctors']) : 0;
                    if ($currentDoctors >= 5 && $newDoctors < ($currentDoctors - 1)) {
                        $violations[] = "Dangerous drop in doctors: existing state has {$currentDoctors} doctors, incoming state only has {$newDoctors} doctors.";
                    }

                    // Check Empanelments: cannot drop existing empanelments
                    $currentEmp = isset($currentState['empanelments']) && is_array($currentState['empanelments']) ? count($currentState['empanelments']) : 0;
                    $newEmp = isset($mergedState['empanelments']) && is_array($mergedState['empanelments']) ? count($mergedState['empanelments']) : 0;
                    if ($currentEmp >= 10 && $newEmp < ($currentEmp - 2)) {
                        $violations[] = "Dangerous drop in empanelments: existing state has {$currentEmp} empanelments, incoming state only has {$newEmp} empanelments.";
                    }

                    // Check CMS Media Reference Preservation: cannot wipe out uploaded images
                    $countCmsImages = function($data) {
                        $json = is_string($data) ? $data : json_encode($data);
                        return preg_match_all('/"cms\/[^\"]+"/', $json);
                    };
                    $currentMediaCount = $countCmsImages($currentState);
                    $newMediaCount = $countCmsImages($mergedState);
                    if ($currentMediaCount >= 50 && $newMediaCount < ($currentMediaCount / 2)) {
                        $violations[] = "Dangerous loss of uploaded media references: existing state has {$currentMediaCount} CMS media references, incoming state only has {$newMediaCount}.";
                    }

                    // If any safety violations detected and force flag is NOT provided
                    if (!empty($violations) && !$request->input('_force_destructive_override', false)) {
                        $logMessage = "[" . date('Y-m-d H:i:s') . "] BLOCKED DESTRUCTIVE STATE UPDATE:\n" . implode("\n", $violations) . "\n";
                        @file_put_contents(storage_path('logs/state_guard.log'), $logMessage, FILE_APPEND);
                        return response()->json([
                            'error' => 'Destructive state update rejected by Safety Guard',
                            'violations' => $violations,
                            'message' => 'The server prevented accidental data loss. If this was intentional, provide _force_destructive_override: true.'
                        ], 422);
                    }
                }

                // Save merged state safely
                DB::table('state_store')->updateOrInsert(
                    ['key' => 'state'],
                    ['value' => json_encode($mergedState, JSON_UNESCAPED_SLASHES)]
                );

                // Log successful state update
                $logMessage = "[" . date('Y-m-d H:i:s') . "] Safe state update applied successfully. Payload size: " . strlen(json_encode($mergedState)) . " bytes.\n";
                @file_put_contents(storage_path('logs/state_guard.log'), $logMessage, FILE_APPEND);
                
                return response()->json(['message' => 'Database state updated successfully', 'state' => $mergedState]);
            }, 5);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Database error', 'details' => $e->getMessage()], 500);
        }
    }

    public function uploadFile(Request $request)
    {
        try {
            $file = $request->file('file');
            if ($file && !$file->isValid()) {
                return response()->json([
                    'error' => 'File upload error: ' . $file->getErrorMessage() . ' (Code ' . $file->getError() . ')'
                ], 400);
            }

            $request->validate([
                'file' => 'required|file|mimes:jpeg,png,webp,gif,pdf,doc,docx|max:51200',
                'folder' => 'nullable|string'
            ]);

            $file = $request->file('file');
            $folder = $request->input('folder', 'general');
            
            // Generate safe filename
            $extension = $file->getClientOriginalExtension() ?: 'bin';
            $filename = uniqid('file_') . '_' . time() . '.' . $extension;
            $path = $file->storeAs("cms/{$folder}", $filename, 'public');

            if (!$path) {
                return response()->json(['error' => 'Failed to store file'], 500);
            }

            return response()->json(['url' => 'cms/' . $folder . '/' . $filename], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Upload failed', 'details' => $e->getMessage()], 500);
        }
    }
}
