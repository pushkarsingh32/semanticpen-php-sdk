<?php
/**
 * Simple test with only targetKeyword
 * Similar to the Node.js test-simple.js
 */

require_once __DIR__ . '/vendor/autoload.php';

use SemanticPen\SDK\SemanticPenClient;

function testSimple() {
    echo "🚀 Testing PHP SDK with only targetKeyword...\n\n";

    $client = SemanticPenClient::create('your-api-key-here', [
        'debug' => true
    ]);

    try {
        // Test with only required parameter
        echo "📝 Generating article with minimal params...\n";
        $result = $client->generateArticle('Simple test article PHP SDK');
        
        echo "✅ Generation successful!\n";
        echo "   Article ID: {$result['articleId']}\n";
        echo "   Project ID: {$result['projectId']}\n";
        echo "   Message: {$result['message']}\n";
        
        $articleId = $result['articleId'];
        
        // Check status a few times
        echo "\n⏳ Checking status periodically...\n";
        for ($i = 1; $i <= 8; $i++) {
            sleep(5); // Wait 5 seconds
            
            $article = $client->getArticle($articleId);
            echo "   Check {$i}: {$article->status} (progress: {$article->progress}%)\n";
            
            if ($article->status === 'finished') {
                echo "   ✅ Article completed successfully!\n";
                echo "   🎉 Has article_html: " . (!empty($article->article_html) ? 'Yes' : 'No') . "\n";
                if (!empty($article->article_html)) {
                    echo "   📏 Content length: " . strlen($article->article_html) . "\n";
                }
                break;
            } elseif ($article->status === 'failed') {
                echo "   ❌ Article failed\n";
                if (!empty($article->error_message)) {
                    echo "   Error details: {$article->error_message}\n";
                }
                break;
            }
        }
        
    } catch (Exception $e) {
        echo "\n❌ Error:\n";
        echo "   Message: {$e->getMessage()}\n";
        if (method_exists($e, 'getStatusCode')) {
            echo "   Status Code: {$e->getStatusCode()}\n";
        }
        if (method_exists($e, 'getDetails')) {
            echo "   Details: " . json_encode($e->getDetails()) . "\n";
        }
    }
}

testSimple();