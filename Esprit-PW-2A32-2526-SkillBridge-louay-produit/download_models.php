<?php
$modelsDir = __DIR__ . '/views/assets/models';
$jsDir = __DIR__ . '/views/assets/js';

if (!is_dir($modelsDir)) mkdir($modelsDir, 0777, true);
if (!is_dir($jsDir)) mkdir($jsDir, 0777, true);

$baseUrl = 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights/';
$files = [
    'tiny_face_detector_model-weights_manifest.json',
    'tiny_face_detector_model-shard1',
    'face_landmark_68_model-weights_manifest.json',
    'face_landmark_68_model-shard1',
    'face_recognition_model-weights_manifest.json',
    'face_recognition_model-shard1',
    'face_recognition_model-shard2'
];

foreach ($files as $file) {
    echo "Downloading $file...\n";
    $content = file_get_contents($baseUrl . $file);
    if ($content) {
        file_put_contents($modelsDir . '/' . $file, $content);
    } else {
        echo "Failed to download $file\n";
    }
}

echo "Downloading face-api.min.js...\n";
$jsUrl = 'https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js';
$jsContent = file_get_contents($jsUrl);
if ($jsContent) {
    file_put_contents($jsDir . '/face-api.min.js', $jsContent);
} else {
    echo "Failed to download face-api.min.js\n";
}

echo "Done.\n";
