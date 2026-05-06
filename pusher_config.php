<?php
// ── Configuration Pusher ──────────────────────────────────────────────
define('PUSHER_APP_ID',  '2150126');
define('PUSHER_KEY',     '43b4440459346a92371d');
define('PUSHER_SECRET',  '5a11530183601326ffd8');
define('PUSHER_CLUSTER', 'eu');

/**
 * Envoie un événement Pusher via l'API HTTP (sans SDK)
 */
function pusherTrigger($channel, $event, $data)
{
    $appId   = PUSHER_APP_ID;
    $key     = PUSHER_KEY;
    $secret  = PUSHER_SECRET;
    $cluster = PUSHER_CLUSTER;

    $host      = "api-{$cluster}.pusher.com";
    $path      = "/apps/{$appId}/events";
    $timestamp = time();
    $body      = json_encode([
        'name'     => $event,
        'channel'  => $channel,
        'data'     => json_encode($data),
    ]);

    $md5Body = md5($body);

    $stringToSign = "POST\n{$path}\n" .
        "auth_key={$key}" .
        "&auth_timestamp={$timestamp}" .
        "&auth_version=1.0" .
        "&body_md5={$md5Body}";

    $authSignature = hash_hmac('sha256', $stringToSign, $secret);

    $queryString = http_build_query([
        'auth_key'       => $key,
        'auth_timestamp' => $timestamp,
        'auth_version'   => '1.0',
        'body_md5'       => $md5Body,
        'auth_signature' => $authSignature,
    ]);

    $url = "https://{$host}{$path}?{$queryString}";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode === 200;
}
