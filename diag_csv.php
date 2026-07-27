<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$path = __DIR__ . '/storage/app/mangled.csv';
$reader = IOFactory::createReaderForFile($path);
$spreadsheet = $reader->load($path);
$rows = $spreadsheet->getActiveSheet()->toArray(null, true, false, false);

echo "=== RAW PARSED (PhpSpreadsheet) ===" . PHP_EOL;
foreach ($rows as $i => $r) {
    echo "row{$i} cols=" . count($r) . " :: " . json_encode($r, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}

echo PHP_EOL . "=== AFTER normalize (re-split first cell if it contains comma) ===" . PHP_EOL;
$norm = array_map(function ($r) {
    $first = $r[0] ?? null;
    if (is_string($first) && str_contains($first, ',')) {
        return str_getcsv($first, ',', '"');
    }
    return $r;
}, $rows);
foreach ($norm as $i => $r) {
    echo "row{$i} cols=" . count($r) . " :: " . json_encode($r, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
