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
    'destinations/bahir-dar-lake-tana-shore.jpg' => ['file' => 'ET Amhara asv2018-02 img088 Lake Tana at Bahir Dar.jpg', 'description' => 'Lake Tana shore, Bahir Dar'],
    'destinations/bahir-dar-blue-nile-falls.jpg' => ['file' => 'ET Bahir Dar asv2018-02 img17 Tis Issat.jpg', 'description' => 'Blue Nile Falls near Bahir Dar'],
    'destinations/bahir-dar-lakefront.jpg' => ['file' => 'View from Shore of Lake Tana - Bahir Dar - Ethiopia - 02 (8677069911).jpg', 'description' => 'Lake Tana lakefront, Bahir Dar'],
    'destinations/lalibela-bete-giyorgis.jpg' => ['file' => 'Bete Giyorgis 03.jpg', 'description' => 'Bete Giyorgis, Lalibela'],
    'destinations/lalibela-saint-george-sunset.jpg' => ['file' => 'Ethiopia - sunset at Church of Saint George, Lalibela 01.jpg', 'description' => 'Church of Saint George at sunset, Lalibela'],
    'destinations/lalibela-rock-churches-detail.jpg' => ['file' => 'Rock-Hewn Churches, Lalibela Ethiopia (6).jpg', 'description' => 'Rock-hewn church detail, Lalibela'],
    'attractions/fasil-ghebbi.jpg' => ['file' => 'ET Gondar asv2018-02 img03 Fasil Ghebbi.jpg', 'description' => 'Fasil Ghebbi Royal Enclosure'],
    'attractions/fasil-ghebbi-courtyard.jpg' => ['file' => 'ET Gondar asv2018-02 img18 Fasil Ghebbi.jpg', 'description' => 'Fasil Ghebbi courtyard'],
    'attractions/fasil-ghebbi-royal-enclosure.jpg' => ['file' => 'Fasil Ghebbi (6821473537).jpg', 'description' => 'Fasil Ghebbi royal enclosure'],
    'attractions/debre-berhan-selassie.jpg' => ['file' => 'ET Gondar asv2018-02 img42 Debre Berhan Selassie.jpg', 'description' => 'Debre Berhan Selassie Church'],
    'attractions/debre-berhan-selassie-exterior.jpg' => ['file' => 'Church of Debra Berhan Selassie 01.jpg', 'description' => 'Debre Berhan Selassie Church exterior'],
    'attractions/debre-berhan-selassie-wall.jpg' => ['file' => 'Gondar Debre Berhan Selassie Church Wall 1 (28424756451).jpg', 'description' => 'Debre Berhan Selassie Church wall art'],
    'attractions/fasilides-bath.jpg' => ['file' => 'Fasilides Bath 06 (cropped).jpg', 'description' => "Fasilides' Bath"],
    'attractions/fasilides-bath-pavilion.jpg' => ['file' => 'Fasilides Bath 03.jpg', 'description' => "Fasilides' Bath pavilion"],
    'attractions/fasilides-bath-tower.jpg' => ['file' => 'Fasilides Bath - Tower.jpg', 'description' => "Fasilides' Bath tower"],
    'attractions/kuskuam.jpg' => ['file' => 'Kuskuam Palace 092018 - 1.jpg', 'description' => 'Kuskuam Royal Complex'],
    'attractions/kuskuam-palace-2.jpg' => ['file' => 'Kuskuam Palace 092018 - 2.jpg', 'description' => 'Kuskuam Royal Complex ruins'],
    'attractions/kuskuam-palace-3.jpg' => ['file' => 'Kuskuam Palace 092018 - 3.jpg', 'description' => 'Kuskuam Royal Complex palace'],
    'attractions/kuskuam-palace-4.jpg' => ['file' => 'Kuskuam Palace 092018 - 4.jpg', 'description' => 'Kuskuam Royal Complex stonework'],
    'events/timkat-festival.jpg' => ['file' => '01 - Priests dancing for Timkat.jpg', 'description' => 'Timkat celebration'],
    'events/meskel-festival.jpg' => ['file' => 'Meskel, Efiopiya bayramı 26-Sep-2024 05.jpg', 'description' => 'Meskel celebration'],
];

function httpGet(string $url): ?string
{
    $handle = curl_init($url);
    curl_setopt_array($handle, [
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => ['Accept: application/json, image/avif, image/webp, image/*;q=0.8, */*;q=0.5'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 40,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; EthioTourMediaCuration/1.0)',
    ]);

    $body = curl_exec($handle);
    $status = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
    curl_close($handle);

    return is_string($body) && $status >= 200 && $status < 300 ? $body : null;
}

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

    // Wikimedia's thumbnail endpoint is intentionally used for a web-ready
    // local copy. It avoids request-time remote images and large original files.
    $source = 'https://commons.wikimedia.org/wiki/Special:FilePath/'.rawurlencode($info['file']).'?width=1600';
    $content = httpGet($source);

    if ($content === false || strlen($content) < 1000) {
        echo "FAIL: {$path} — {$info['description']}\n";
        $failed++;

        continue;
    }

    file_put_contents($fullPath, $content);
    echo "OK: {$path} — {$info['description']}\n";
    $downloaded++;

    // Be a polite client of a shared public media service.
    usleep(750000);
}

echo "\nDone: {$downloaded} downloaded, {$skipped} skipped, {$failed} failed.\n";
