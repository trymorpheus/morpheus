<?php
/**
 * Test webhook connectivity
 */

if ($argc < 2) {
    echo "Usage: php test-webhook.php YOUR_WEBHOOK_URL\n";
    exit(1);
}

$url = $argv[1];

echo "Testing webhook: $url\n\n";

// Check if curl is available
if (!function_exists('curl_init')) {
    echo "❌ ERROR: curl extension not available\n";
    exit(1);
}

echo "✅ curl extension available\n";

// Test payload
$payload = [
    'event' => 'test',
    'message' => 'Test from DynamicCRUD',
    'timestamp' => date('c')
];

echo "📦 Payload: " . json_encode($payload, JSON_PRETTY_PRINT) . "\n\n";

// Make request
echo "🔄 Sending request...\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer test-token'
]);

// Enable verbose output
curl_setopt($ch, CURLOPT_VERBOSE, true);
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

rewind($verbose);
$verboseLog = stream_get_contents($verbose);

curl_close($ch);

echo "\n📊 Results:\n";
echo "HTTP Code: $httpCode\n";
echo "Response: " . ($response ?: '(empty)') . "\n";

if ($error) {
    echo "❌ Error: $error\n";
} else {
    echo "✅ Request sent successfully\n";
}

if ($httpCode >= 200 && $httpCode < 300) {
    echo "\n✅ SUCCESS! Check webhook.site for the payload\n";
} else {
    echo "\n❌ FAILED! HTTP code: $httpCode\n";
    echo "\nVerbose log:\n$verboseLog\n";
}
