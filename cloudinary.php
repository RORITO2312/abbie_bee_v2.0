<?php
// ===== CLOUDINARY CONFIG =====
define('CLOUDINARY_CLOUD_NAME', 'dbsrsow8h');
define('CLOUDINARY_API_KEY',    '453323517767657');
define('CLOUDINARY_API_SECRET', 'jCTaFTxC7_l5rxwdXQLQwttUAqU');

function uploadToCloudinary(string $tmpPath, string $folder = 'abbie_bee'): string {
    $timestamp = time();
    $params    = "folder=$folder&timestamp=$timestamp" . CLOUDINARY_API_SECRET;
    $signature = sha1($params);

    $url = 'https://api.cloudinary.com/v1_1/' . CLOUDINARY_CLOUD_NAME . '/image/upload';

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => [
            'file'      => new CURLFile($tmpPath),
            'api_key'   => CLOUDINARY_API_KEY,
            'timestamp' => $timestamp,
            'folder'    => $folder,
            'signature' => $signature,
        ],
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    return $data['secure_url'] ?? '';
}