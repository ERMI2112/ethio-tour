<?php

/**
 * Refresh the curated public image set from Wikimedia Commons.
 *
 * Run from the project root with: php database/download_images.php
 * Add --refresh to replace existing files. The application never downloads
 * images at request time; this is a deliberate data-maintenance command.
 */
$baseDir = dirname(__DIR__).'/public/images';
$refresh = in_array('--refresh', $argv ?? [], true);

$images = [
    'destinations/gondar-hero.jpg' => ['file' => 'ET Gondar asv2018-02 img03 Fasil Ghebbi.jpg', 'description' => 'Fasil Ghebbi, Gondar'],
    'destinations/hero-ethiopia.jpg' => ['file' => 'ET Gondar asv2018-02 img03 Fasil Ghebbi.jpg', 'description' => 'Ethiopian heritage hero'],
    'destinations/bahir-dar-hero.jpg' => ['file' => 'ET Amhara asv2018-02 img068 Lake Tana at Bahir Dar.jpg', 'description' => 'Lake Tana at Bahir Dar'],
    'destinations/lalibela-hero.jpg' => ['file' => 'Rock-Hewn Churches, Lalibela Ethiopia (2).jpg', 'description' => 'Rock-hewn churches, Lalibela'],
    'attractions/fasil-ghebbi.jpg' => ['file' => 'ET Gondar asv2018-02 img03 Fasil Ghebbi.jpg', 'description' => 'Fasil Ghebbi Royal Enclosure'],
    'attractions/debre-berhan-selassie.jpg' => ['file' => 'ET Gondar asv2018-02 img42 Debre Berhan Selassie.jpg', 'description' => 'Debre Berhan Selassie Church'],
    'attractions/fasilides-bath.jpg' => ['file' => 'Fasilides Bath 06 (cropped).jpg', 'description' => "Fasilides' Bath"],
    'attractions/kuskuam.jpg' => ['file' => 'Kuskuam Palace 092018 - 1.jpg', 'description' => 'Kuskuam Royal Complex'],
    'events/timkat-festival.jpg' => ['file' => '01 - Priests dancing for Timkat.jpg', 'description' => 'Timkat celebration'],
    'events/meskel-festival.jpg' => ['file' => 'Meskel, Efiopiya bayramı 26-Sep-2024 05.jpg', 'description' => 'Meskel celebration'],
];

$context = stream_context_create([
    'http' => [
        'header' => "User-Agent: EthioTour-curated-assets/1.0\r\n",
        'timeout' => 30,
    ],
]);

$downloaded = 0;
$skipped = 0;
$failed = 0;

foreach ($images as $path => $info) {
    $fullPath = $baseDir.'/'.$path;
    $directory = dirname($fullPath);

    if (! is_dir($directory)) {
        mkdir($directory, 0755, true);
    }

    if (! $refresh && is_file($fullPath) && filesize($fullPath) > 1000) {
        echo "SKIP (exists): {$path}\n";
        $skipped++;

        continue;
    }

    $source = 'https://commons.wikimedia.org/wiki/Special:FilePath/'.rawurlencode($info['file']);
    $content = @file_get_contents($source, false, $context);

    if ($content === false || strlen($content) < 1000) {
        echo "FAIL: {$path} — {$info['description']}\n";
        $failed++;

        continue;
    }

    file_put_contents($fullPath, $content);
    echo "OK: {$path} — {$info['description']}\n";
    $downloaded++;
}

echo "\nDone: {$downloaded} downloaded, {$skipped} skipped, {$failed} failed.\n";
