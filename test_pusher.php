<?php
session_start();
require_once 'config.php';
require_once 'pusher_config.php';

// Test curl
echo "curl enabled: " . (function_exists('curl_init') ? 'YES' : 'NO') . "<br>";

// Test Pusher trigger
$result = pusherTrigger('chat-projet-1', 'test', ['message' => 'Hello from PHP!']);
echo "Pusher trigger result: " . ($result ? 'SUCCESS' : 'FAILED') . "<br>";
