<?php
$file = __DIR__ . '/../src/components/AdminPanel.tsx';
$content = file_get_contents($file);

// Extract imports from lucide-react
preg_match('/import\s*\{([^}]+)\}\s*from\s*[\'"]lucide-react[\'"]/', $content, $match);
$imported = [];
if ($match) {
    $items = explode(',', $match[1]);
    foreach ($items as $item) {
        $item = trim($item);
        if ($item) {
            $parts = preg_split('/\s+as\s+/', $item);
            $imported[] = trim(end($parts));
        }
    }
}

// Find all JSX components used: <IconName
preg_match_all('/<([A-Z][a-zA-Z0-9]+)/', $content, $matches);
$usedComponents = array_unique($matches[1]);

$missing = [];
$builtInOrOther = [
    'React', 'InspirationBanner', 'AdminCareerCMS', 'AdminVisualNavCMS', 'AdminHomepageCMS',
    'AdminUpcomingEventsCMS', 'AdminPressReleasesCMS', 'AdminDoctorAvailabilityCMS', 'AdminFaqCMS',
    'AdminCertificateCMS', 'RichHtmlEditor', 'AdminPanel'
];

foreach ($usedComponents as $comp) {
    if (!in_array($comp, $imported) && !in_array($comp, $builtInOrOther)) {
        $missing[] = $comp;
    }
}

echo "Imported from lucide-react: " . count($imported) . " icons.\n";
echo "Used components: " . count($usedComponents) . " components.\n";
echo "Missing imports from lucide-react: " . implode(', ', $missing) . "\n";
