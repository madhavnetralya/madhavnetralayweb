<?php
$dbPath = __DIR__ . '/package/database.db';
if (!file_exists($dbPath)) {
    echo "Database file not found at {$dbPath}\n";
    exit(1);
}

$db = new SQLite3($dbPath);
$res = $db->query("SELECT value FROM state_store WHERE key='state'");
$row = $res->fetchArray(SQLITE3_ASSOC);
if ($row) {
    $state = json_decode($row['value'], true);
    if (isset($state['users']) && is_array($state['users'])) {
        echo "Found " . count($state['users']) . " user(s) in state_store:\n";
        foreach ($state['users'] as $u) {
            $username = $u['username'] ?? 'N/A';
            $role = $u['role'] ?? 'N/A';
            $email = $u['email'] ?? 'N/A';
            $hasPass = !empty($u['password']) ? 'YES' : 'NO';
            echo "- Username: {$username} | Role: {$role} | Email: {$email} | HasPassword: {$hasPass}\n";
        }
    } else {
        echo "No 'users' key in state_store.\n";
    }
} else {
    echo "No state row in state_store.\n";
}
