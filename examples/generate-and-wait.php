<?php
/**
 * Generate and wait example for SemanticPen PHP SDK
 * Demonstrates the one-step generation process
 */

require_once __DIR__ . '/../vendor/autoload.php';

use SemanticPen\SDK\SemanticPenClient;

// Replace with your actual API key
$apiKey = 'your-api-key-here';

// Initialize the client
$client = SemanticPenClient::create($apiKey);

try {
    echo "🚀 Generate and Wait Example\n\n";

    // Generate article and wait for completion in one call
    echo "📝 Generating article with real-time progress...\n\n";
    
    $article = $client->generateArticleAndWait('PHP Performance Optimization Techniques', [
        'generation' => [
            'projectName' => 'PHP Performance Guide'
        ],
        'polling' => [
            'interval' => 3, // Check every 3 seconds
            'maxAttempts' => 100, // Max ~5 minutes
            'onProgress' => function($attempt, $status) {
                $timestamp = date('H:i:s');
                echo "[{$timestamp}] Attempt {$attempt}: {$status}\n";
                
                switch (strtolower($status)) {
                    case 'queued':
                        echo "  📋 Article is queued for processing...\n";
                        break;
                    case 'processing':
                        echo "  ✍️  AI is writing your article...\n";
                        break;
                    case 'finished':
                        echo "  ✅ Article generation completed!\n";
                        break;
                    case 'failed':
                        echo "  ❌ Article generation failed!\n";
                        break;
                }
                echo "\n";
            }
        ]
    ]);

    echo "🎉 Article generation completed!\n\n";
    
    echo "📄 Article Details:\n";
    echo "   ID: {$article->id}\n";
    echo "   Title: {$article->title}\n";
    echo "   Status: {$article->status}\n";
    echo "   Word Count: {$article->word_count}\n";
    echo "   Target Keyword: {$article->target_keyword}\n";
    echo "   Project: {$article->project_name}\n";
    echo "   Created: {$article->created_at}\n";
    
    if ($article->meta_description) {
        echo "   Meta Description: {$article->meta_description}\n";
    }
    
    echo "\n📊 Content Statistics:\n";
    echo "   HTML Length: " . strlen($article->article_html) . " characters\n";
    echo "   Has Content: " . ($article->hasContent() ? 'Yes' : 'No') . "\n";
    echo "   Is Complete: " . ($article->isComplete() ? 'Yes' : 'No') . "\n";
    echo "   Has Error: " . ($article->hasError() ? 'Yes' : 'No') . "\n";

    // Save the article
    $filename = 'generated_article_' . date('Y-m-d_H-i-s') . '.html';
    file_put_contents($filename, $article->article_html);
    echo "\n💾 Article saved to: {$filename}\n";

    // Also save as plain text (strip HTML tags)
    $textFilename = 'generated_article_' . date('Y-m-d_H-i-s') . '.txt';
    $plainText = strip_tags($article->article_html);
    file_put_contents($textFilename, $plainText);
    echo "💾 Plain text version saved to: {$textFilename}\n";

    // Show first 200 characters of content
    echo "\n📖 Content Preview:\n";
    echo substr($plainText, 0, 200) . "...\n";

} catch (Exception $e) {
    echo "\n❌ Error occurred:\n";
    echo "   Type: " . get_class($e) . "\n";
    echo "   Message: {$e->getMessage()}\n";
    
    if (method_exists($e, 'getStatusCode')) {
        echo "   Status Code: {$e->getStatusCode()}\n";
    }
    
    if (method_exists($e, 'getField')) {
        echo "   Field: {$e->getField()}\n";
        echo "   Value: {$e->getValue()}\n";
    }
    
    if (method_exists($e, 'getRetryAfter')) {
        echo "   Retry After: {$e->getRetryAfter()} seconds\n";
    }
}

echo "\n🎉 Example completed!\n";