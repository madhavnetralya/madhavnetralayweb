<?php
$db = new SQLite3(__DIR__ . '/package/database.db');
$res = $db->query("SELECT value FROM state_store WHERE key='state'");
$row = $res->fetchArray(SQLITE3_ASSOC);
$state = json_decode($row['value'], true);

echo "=== 1. WEBSITE SETTINGS IN DATABASE ===\n";
$settings = $state['settings'] ?? [];
print_r($settings);

echo "\n=== 2. SEO META IN DATABASE ===\n";
$seoMeta = $state['seoMeta'] ?? [];
print_r($seoMeta);

echo "\n=== 3. SOCIAL MEDIA LINKS ===\n";
$socialLinks = $settings['socialLinks'] ?? ($state['socialLinks'] ?? []);
print_r($socialLinks);

echo "\n=== 4. CUSTOM PAGES (POLICIES, DISCLAIMERS, LEGAL) ===\n";
$customPages = $state['customPages'] ?? [];
$policyPages = [];
foreach ($customPages as $p) {
    $slug = strtolower($p['slug'] ?? $p['id'] ?? '');
    $title = strtolower($p['title'] ?? '');
    if (strpos($slug, 'privacy') !== false || strpos($slug, 'terms') !== false || 
        strpos($slug, 'disclaimer') !== false || strpos($slug, 'cookie') !== false ||
        strpos($title, 'privacy') !== false || strpos($title, 'terms') !== false ||
        strpos($title, 'disclaimer') !== false || strpos($title, 'cookie') !== false) {
        $policyPages[] = [
            'id' => $p['id'] ?? '',
            'title' => $p['title'] ?? '',
            'slug' => $p['slug'] ?? '',
            'hasContent' => !empty($p['content']) || !empty($p['blocks']) || !empty($p['cards'])
        ];
    }
}
print_r($policyPages);
