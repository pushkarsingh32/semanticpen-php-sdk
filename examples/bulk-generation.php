<?php
/**
 * Bulk article generation example for SemanticPen PHP SDK
 */

require_once __DIR__ . '/../vendor/autoload.php';

use SemanticPen\SDK\SemanticPenClient;

// Replace with your actual API key
$apiKey = 'your-api-key-here';

// Initialize the client
$client = SemanticPenClient::create($apiKey);

try {
    echo "🚀 Bulk Article Generation Example\n\n";

    // Define keywords for bulk generation
    $keywords = [
        'Laravel best practices 2024',
        'Symfony vs Laravel comparison',
        'PHP 8 new features and improvements',
        'Modern PHP development workflow'
    ];

    echo "📝 Generating {count($keywords)} articles...\n";
    
    // Generate all articles at once
    $result = $client->generateArticles($keywords, [
        'projectName' => 'PHP Development Blog Series'
    ]);

    echo "✅ Bulk generation started!\n";
    echo "   Successful: {$result['successCount']}\n";
    echo "   Failed: {$result['failureCount']}\n\n";

    if ($result['failureCount'] > 0) {
        echo "❌ Failed articles:\n";
        foreach ($result['failed'] as $failed) {
            echo "   - {$failed['item']}: {$failed['error']}\n";
        }
        echo "\n";
    }

    if ($result['successCount'] > 0) {
        echo "⏳ Waiting for articles to complete...\n";
        
        // Get article IDs
        $articleIds = array_column($result['successful'], 'articleId');
        
        // Wait for all articles to complete
        $completed = $client->waitForArticles($articleIds, [
            'interval' => 5,
            'maxAttempts' => 60
        ]);

        echo "\n📊 Completion Summary:\n";
        echo "   Completed: {$completed['successCount']}\n";
        echo "   Failed: {$completed['failureCount']}\n\n";

        // Process completed articles
        if ($completed['successCount'] > 0) {
            echo "📄 Completed Articles:\n";
            
            // Create articles directory if it doesn't exist
            if (!is_dir('articles')) {
                mkdir('articles');
            }
            
            foreach ($completed['successful'] as $index => $article) {
                echo "   " . ($index + 1) . ". {$article->title}\n";
                echo "      Word Count: {$article->word_count}\n";
                echo "      Status: {$article->status}\n";
                
                // Save article to file
                $filename = 'articles/' . preg_replace('/[^a-zA-Z0-9]/', '_', $article->title) . '.html';
                file_put_contents($filename, $article->article_html);
                echo "      Saved to: {$filename}\n\n";
            }
            
            echo "💾 All articles saved to 'articles/' directory\n";
        }

        // Report any failures
        if ($completed['failureCount'] > 0) {
            echo "\n❌ Articles that failed to complete:\n";
            foreach ($completed['failed'] as $failed) {
                echo "   - Article ID {$failed['item']}: {$failed['error']}\n";
            }
        }
    }

} catch (Exception $e) {
    echo "\n❌ Error occurred:\n";
    echo "   Message: {$e->getMessage()}\n";
    
    if (method_exists($e, 'getStatusCode')) {
        echo "   Status Code: {$e->getStatusCode()}\n";
    }
}

echo "\n🎉 Bulk generation example completed!\n";