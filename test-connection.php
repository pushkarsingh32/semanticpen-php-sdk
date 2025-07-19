<?php
/**
 * Test connection endpoint specifically
 */

require_once __DIR__ . '/vendor/autoload.php';

use SemanticPen\SDK\SemanticPenClient;

function testConnection() {
    echo "🔌 Testing connection endpoint...\n\n";

    $client = SemanticPenClient::create('your-api-key-here', [
        'debug' => true
    ]);

    try {
        // Test the connection endpoint directly
        echo "📡 Testing connection with debug...\n";
        $connected = $client->testConnection();
        echo "Connection result: " . ($connected ? 'true' : 'false') . "\n";
        
    } catch (Exception $e) {
        echo "❌ Connection test error:\n";
        echo "   Message: {$e->getMessage()}\n";
        if (method_exists($e, 'getStatusCode')) {
            echo "   Status Code: {$e->getStatusCode()}\n";
        }
        echo "   Full error: " . get_class($e) . "\n";
    }

    try {
        // Test with a real endpoint that we know works
        echo "\n📡 Testing with actual article generation as connection test...\n";
        
        // If we can generate an article, the connection is good
        $response = $client->generateArticle('Connection test PHP');
        
        echo "✅ Connection is working (article generation successful)\n";
        echo "   Article ID: {$response['articleId']}\n";
        
    } catch (Exception $e) {
        echo "❌ Real API test failed: {$e->getMessage()}\n";
    }
}

testConnection();