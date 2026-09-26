<?php

$dbFile = isset($argv[1]) ? $argv[1] : __DIR__ . '/package/database.db';
$pdo = new PDO('sqlite:' . $dbFile);
$stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Tables in database.db:\n";
print_r($tables);

if (in_array('users', $tables)) {
    $stmt = $pdo->query("SELECT id, name, username, email FROM users");
    echo "\nUsers in users table:\n";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
}

$stmt = $pdo->query("SELECT value FROM state_store WHERE key='state'");
$stateJson = $stmt->fetchColumn();
if ($stateJson) {
    $state = json_decode($stateJson, true);
    if (!empty($state['users'])) {
        echo "\nUsers in state_store json:\n";
        print_r($state['users']);
    }
}
