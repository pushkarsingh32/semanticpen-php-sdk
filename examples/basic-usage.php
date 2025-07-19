<?php
/**
 * Basic usage example for SemanticPen PHP SDK
 */

require_once __DIR__ . '/../vendor/autoload.php';

use SemanticPen\SDK\SemanticPenClient;

// Replace with your actual API key
$apiKey = 'your-api-key-here';

// Initialize the client
$client = SemanticPenClient::create($apiKey, [
    'debug' => true // Enable debug output
]);

try {
    echo "🚀 Testing SemanticPen PHP SDK\n\n";

    // Test connection
    echo "📡 Testing connection...\n";
    if ($client->testConnection()) {
        echo "✅ Connection successful!\n\n";
    } else {
        echo "❌ Connection failed!\n";
        exit(1);
    }

    // Generate a single article
    echo "📝 Generating article...\n";
    $result = $client->generateArticle('Best PHP frameworks for 2024', [
        'projectName' => 'PHP Tutorial Series'
    ]);

    echo "✅ Article generation started!\n";
    echo "   Article ID: {$result['articleId']}\n";
    echo "   Project ID: {$result['projectId']}\n";
    echo "   Message: {$result['message']}\n\n";

    $articleId = $result['articleId'];

    // Check status periodically
    echo "⏳ Checking status...\n";
    for ($i = 1; $i <= 8; $i++) {
        sleep(5); // Wait 5 seconds
        
        $status = $client->getArticleStatus($articleId);
        echo "   Check {$i}: {$status['status']}\n";
        
        if ($status['status'] === 'finished') {
            echo "   ✅ Article completed successfully!\n\n";
            
            // Get the completed article
            $article = $client->getArticle($articleId);
            echo "📄 Article Details:\n";
            echo "   Title: {$article->title}\n";
            echo "   Word Count: {$article->word_count}\n";
            echo "   Status: {$article->status}\n";
            echo "   Has Content: " . ($article->hasContent() ? 'Yes' : 'No') . "\n";
            
            if ($article->article_html) {
                echo "   Content Length: " . strlen($article->article_html) . " characters\n";
                // Save to file
                file_put_contents('generated_article.html', $article->article_html);
                echo "   💾 Article saved to generated_article.html\n";
            }
            break;
            
        } elseif ($status['status'] === 'failed') {
            echo "   ❌ Article generation failed\n";
            if ($status['errorMessage']) {
                echo "   Error: {$status['errorMessage']}\n";
            }
            break;
        }
    }

} catch (Exception $e) {
    echo "\n❌ Error occurred:\n";
    echo "   Message: {$e->getMessage()}\n";
    
    if (method_exists($e, 'getStatusCode')) {
        echo "   Status Code: {$e->getStatusCode()}\n";
    }
    
    if (method_exists($e, 'getDetails') && !empty($e->getDetails())) {
        echo "   Details: " . json_encode($e->getDetails()) . "\n";
    }
}

echo "\n🎉 Example completed!\n";