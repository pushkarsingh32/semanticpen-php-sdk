# SemanticPen - AI Article Writer & SEO Blog Generator PHP SDK

Professional AI Article Writer SDK for automated content creation, SEO blog writing, and AI-powered article generation. Build PHP applications with intelligent content automation.

🔗 **[SemanticPen.com](https://www.semanticpen.com/)** - AI-Powered Content Creation Platform  
📚 **[API Documentation](https://www.semanticpen.com/api-documentation)** - Complete API Reference

[![Packagist Version](https://img.shields.io/packagist/v/semanticpen/sdk)](https://packagist.org/packages/semanticpen/sdk)
[![PHP Version](https://img.shields.io/packagist/php-v/semanticpen/sdk)](https://packagist.org/packages/semanticpen/sdk)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

## Features

- 🤖 **AI Article Writer**: Generate high-quality, SEO-optimized articles from keywords using advanced AI
- 📊 **SEO Blog Generator**: Create SEO-friendly blog content with automated optimization
- 🚀 **Content Automation**: Streamline your content creation workflow with AI-powered writing
- ⚡ **Fast Article Generation**: Modern async API for quick content creation
- 🎯 **PHP 7.4+ Support**: Full compatibility with modern PHP versions
- 🔄 **Real-time Status**: Monitor article generation progress with automatic polling
- 🛠️ **Zero Configuration**: Works out of the box with intelligent defaults
- 🔒 **Enterprise Security**: Built-in API key management and secure authentication
- 📝 **Multiple Formats**: Support for various content types and article structures
- 🌐 **Scalable**: Handle single articles or bulk content generation

## Installation

```bash
composer require semanticpen/sdk
```

Get started with AI-powered article writing in seconds!

## Quick Start

### Basic Usage - Submit Articles for Generation

```php
<?php
require_once 'vendor/autoload.php';

use SemanticPen\SDK\SemanticPenClient;

// Initialize the client with just your API key
$client = SemanticPenClient::create('your-api-key');

// Step 1: Submit article for generation (returns immediately)
$result = $client->generateArticle('Future of artificial intelligence');
echo "Article generation started with ID: {$result['articleId']}\n";
echo "Project ID: {$result['projectId']}\n";

// Step 2: Check article status
$status = $client->getArticleStatus($result['articleId']);
echo "Current status: {$status['status']}\n"; // 'queued', 'processing', 'finished', or 'failed'

// Step 3: Wait for completion and retrieve article
$isReady = $client->isArticleComplete($result['articleId']);
if ($isReady) {
    $article = $client->getArticle($result['articleId']);
    echo "Title: {$article->title}\n";
    echo "Content: " . substr($article->article_html, 0, 200) . "...\n";
}
```

### Multiple Article Generation

```php
// Submit multiple articles at once
$keywords = [
    'Machine learning in healthcare',
    'AI-powered diagnosis tools',
    'Future of telemedicine'
];

$result = $client->generateArticles($keywords);
echo "Started generating {$result['successCount']} articles\n";

// Check status of all articles
foreach ($result['successful'] as $item) {
    $status = $client->getArticleStatus($item['articleId']);
    echo "Article \"{$item['keyword']}\": {$status['status']}\n";
}
```

## API Reference

### Client Initialization

```php
// Simple initialization (recommended)
$client = SemanticPenClient::create('your-api-key');

// Advanced configuration (optional)
$client = new SemanticPenClient([
    'apiKey' => 'your-api-key',
    'baseUrl' => 'https://www.semanticpen.com', // default
    'timeout' => 30, // default: 30 seconds
    'debug' => false // default: false, set to true for debug logging
]);
```

### Article Generation

#### Generate Single Article

```php
// Basic generation
$result = $client->generateArticle('keyword');

// With options (Note: only basic options are supported)
$result = $client->generateArticle('keyword', [
    'projectName' => 'My Project'
]);

// Advanced: Generate and wait for completion (blocking operation)
$article = $client->generateArticleAndWait('keyword', [
    'generation' => [
        'projectName' => 'My Project'
    ],
    'polling' => [
        'interval' => 5, // poll every 5 seconds
        'maxAttempts' => 60, // max 5 minutes
        'onProgress' => function($attempt, $status) {
            echo "Attempt {$attempt}: {$status}\n";
        }
    ]
]);
```

#### Generate Multiple Articles

```php
// Multiple article generation
$keywords = ['keyword1', 'keyword2', 'keyword3'];
$result = $client->generateArticles($keywords);

// Access results
foreach ($result['successful'] as $item) {
    echo "Generated article {$item['articleId']} for \"{$item['keyword']}\"\n";
}

foreach ($result['failed'] as $item) {
    echo "Failed to generate article for \"{$item['item']}\": {$item['error']}\n";
}
```

### Article Retrieval

```php
// Get single article
$article = $client->getArticle('article-id');

// Get multiple articles
$result = $client->getArticles(['id1', 'id2', 'id3']);

// Check article status
$status = $client->getArticleStatus('article-id');
echo $status['isComplete'] ? 'Complete' : 'In Progress';
echo $status['hasError'] ? 'Has Error' : 'No Errors';

// Check if article is complete
$isComplete = $client->isArticleComplete('article-id');
```

### Status Polling

```php
// Wait for article completion
$article = $client->waitForArticle('article-id');

// Wait with custom polling configuration
$article = $client->waitForArticle('article-id', [
    'interval' => 3, // 3 seconds
    'maxAttempts' => 100, // ~5 minutes
    'onProgress' => function($attempt, $status) {
        echo "Polling attempt {$attempt}: {$status}\n";
    }
]);

// Wait for multiple articles
$result = $client->waitForArticles(['id1', 'id2']);
echo "{$result['successCount']} articles completed successfully\n";
```

### Advanced Usage

#### One-Step Generation (Blocking Operation)

For simpler workflows, you can generate and wait for completion in one call:

```php
// Generate article and wait for completion (blocking)
$article = $client->generateArticleAndWait('AI in healthcare', [
    'generation' => [
        'projectName' => 'Healthcare Articles'
    ],
    'polling' => [
        'interval' => 3, // poll every 3 seconds
        'maxAttempts' => 100, // max ~5 minutes
        'onProgress' => function($attempt, $status) {
            echo "Polling attempt {$attempt}: {$status}\n";
        }
    ]
]);

echo $article->title . "\n";
echo $article->article_html . "\n";
```

## Error Handling

The SDK provides comprehensive error handling with specific exception types:

```php
use SemanticPen\SDK\Exceptions\AuthenticationException;
use SemanticPen\SDK\Exceptions\ValidationException;
use SemanticPen\SDK\Exceptions\RateLimitException;
use SemanticPen\SDK\Exceptions\NetworkException;

try {
    $article = $client->generateArticleAndWait('keyword');
} catch (AuthenticationException $e) {
    echo "Invalid API key or authentication failed\n";
} catch (ValidationException $e) {
    echo "Invalid input: {$e->getField()} = {$e->getValue()}\n";
} catch (RateLimitException $e) {
    echo "Rate limit exceeded, retry after: {$e->getRetryAfter()}\n";
} catch (NetworkException $e) {
    echo "Network error: {$e->getStatusCode()}\n";
} catch (Exception $e) {
    echo "General error: {$e->getMessage()}\n";
}
```

## Configuration Options

### Polling Configuration

```php
// Custom polling settings
$article = $client->waitForArticle('article-id', [
    'interval' => 5,        // seconds between status checks
    'maxAttempts' => 60,    // maximum polling attempts
    'onProgress' => function($attempt, $status) {
        echo "Attempt {$attempt}: {$status}\n";
    }
]);
```

### Client Configuration

```php
$client = new SemanticPenClient([
    'apiKey' => 'your-api-key',      // Required: Your SemanticPen API key
    'baseUrl' => 'https://www.semanticpen.com', // Optional: API base URL
    'timeout' => 30,                 // Optional: Request timeout in seconds
    'debug' => false                 // Optional: Enable debug logging
]);
```

### Article Status Values

Articles go through these status stages during generation:

- `queued`: Article is waiting to be processed
- `processing`: Article is currently being generated
- `finished`: Article generation completed successfully
- `failed`: Article generation failed with an error

### Error Types

The SDK provides specific exception types for better error handling:

- `AuthenticationException`: Invalid API key or authentication issues
- `ValidationException`: Invalid input parameters
- `RateLimitException`: API rate limit exceeded
- `NetworkException`: Network or HTTP errors

## Examples

### Simple Article Generation

```php
<?php
require_once 'vendor/autoload.php';

use SemanticPen\SDK\SemanticPenClient;

$client = SemanticPenClient::create('your-api-key');

try {
    // Generate and wait for article
    $article = $client->generateArticleAndWait('Best PHP frameworks 2024');
    
    echo "Title: {$article->title}\n";
    echo "Word Count: {$article->word_count}\n";
    echo "Status: {$article->status}\n";
    
    // Save to file
    file_put_contents('article.html', $article->article_html);
    echo "Article saved to article.html\n";
    
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}
```

### Bulk Article Generation

```php
<?php
require_once 'vendor/autoload.php';

use SemanticPen\SDK\SemanticPenClient;

$client = SemanticPenClient::create('your-api-key');

$keywords = [
    'Laravel best practices',
    'Symfony vs Laravel comparison',
    'PHP 8 new features'
];

try {
    // Generate all articles
    $result = $client->generateArticles($keywords, [
        'projectName' => 'PHP Blog Series'
    ]);
    
    echo "Generated {$result['successCount']} articles\n";
    
    // Wait for all to complete
    $articleIds = array_column($result['successful'], 'articleId');
    $completed = $client->waitForArticles($articleIds);
    
    // Save all articles
    foreach ($completed['successful'] as $article) {
        $filename = 'articles/' . preg_replace('/[^a-zA-Z0-9]/', '_', $article->title) . '.html';
        file_put_contents($filename, $article->article_html);
        echo "Saved: {$filename}\n";
    }
    
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}
```

### Custom Polling with Progress

```php
<?php
require_once 'vendor/autoload.php';

use SemanticPen\SDK\SemanticPenClient;

$client = SemanticPenClient::create('your-api-key');

try {
    // Submit article
    $result = $client->generateArticle('Advanced PHP patterns');
    $articleId = $result['articleId'];
    
    echo "Article submitted with ID: {$articleId}\n";
    
    // Wait with custom progress reporting
    $article = $client->waitForArticle($articleId, [
        'interval' => 3,
        'maxAttempts' => 100,
        'onProgress' => function($attempt, $status) {
            $timestamp = date('H:i:s');
            echo "[{$timestamp}] Attempt {$attempt}: {$status}\n";
            
            if ($status === 'processing') {
                echo "  Article is being written...\n";
            }
        }
    ]);
    
    echo "Article completed!\n";
    echo "Title: {$article->title}\n";
    echo "Word Count: {$article->word_count}\n";
    
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}
```

## API Requirements

- Valid SemanticPen API key (get one at [SemanticPen.com](https://www.semanticpen.com))
- Active paid subscription (view pricing at [SemanticPen.com](https://www.semanticpen.com))
- PHP 7.4 or higher
- cURL extension enabled
- JSON extension enabled
- For complete API reference, visit [API Documentation](https://www.semanticpen.com/api-documentation)

## License

MIT License - see [LICENSE](./LICENSE) file for details.

## Support

- 📖 [API Documentation](https://www.semanticpen.com/api-documentation) - Complete API Reference
- 🐛 [Issue Tracker](https://github.com/semanticpen/php-sdk/issues)
- 💬 [Support](mailto:contact@semanticpen.com)
- 🌐 [SemanticPen.com](https://www.semanticpen.com) - AI Article Writer Platform

## Contributing

We welcome contributions! Please see our [Contributing Guide](./CONTRIBUTING.md) for details.

---

Built with ❤️ by the SemanticPen team