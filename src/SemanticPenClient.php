<?php

namespace SemanticPen\SDK;

use SemanticPen\SDK\Services\ArticleService;
use SemanticPen\SDK\Types\Article;
use SemanticPen\SDK\Types\ArticleGenerationRequest;

/**
 * Main SemanticPen SDK client
 */
class SemanticPenClient
{
    private $articleService;

    public function __construct(array $config = [])
    {
        $this->articleService = new ArticleService($config);
    }

    /**
     * Static factory method for easy instantiation
     */
    public static function create(string $apiKey, array $options = []): self
    {
        $config = array_merge(['apiKey' => $apiKey], $options);
        return new self($config);
    }

    /**
     * Test connection to SemanticPen API
     */
    public function testConnection(): bool
    {
        return $this->articleService->testConnection();
    }

    /**
     * Generate a single article
     */
    public function generateArticle(string $targetKeyword, array $options = []): array
    {
        $projectName = $options['projectName'] ?? null;
        return $this->articleService->generateArticle($targetKeyword, $projectName);
    }

    /**
     * Generate multiple articles
     */
    public function generateArticles(array $keywords, array $options = []): array
    {
        $projectName = $options['projectName'] ?? null;
        return $this->articleService->generateBulkArticles($keywords, $projectName);
    }

    /**
     * Generate article and wait for completion
     */
    public function generateArticleAndWait(string $targetKeyword, array $options = []): Article
    {
        $generation = $options['generation'] ?? [];
        $polling = $options['polling'] ?? [];

        $result = $this->generateArticle($targetKeyword, $generation);
        return $this->waitForArticle($result['articleId'], $polling);
    }

    /**
     * Get article by ID
     */
    public function getArticle(string $articleId): Article
    {
        return $this->articleService->getArticle($articleId);
    }

    /**
     * Get multiple articles by IDs
     */
    public function getArticles(array $articleIds): array
    {
        return $this->articleService->getArticles($articleIds);
    }

    /**
     * Check if article is complete
     */
    public function isArticleComplete(string $articleId): bool
    {
        return $this->articleService->isArticleComplete($articleId);
    }

    /**
     * Get article status
     */
    public function getArticleStatus(string $articleId): array
    {
        return $this->articleService->getArticleStatus($articleId);
    }

    /**
     * Wait for article completion
     */
    public function waitForArticle(string $articleId, array $options = []): Article
    {
        return $this->articleService->waitForArticle($articleId, $options);
    }

    /**
     * Wait for multiple articles to complete
     */
    public function waitForArticles(array $articleIds, array $options = []): array
    {
        $successful = [];
        $failed = [];

        foreach ($articleIds as $articleId) {
            try {
                $article = $this->waitForArticle($articleId, $options);
                $successful[] = $article;
            } catch (\Exception $e) {
                $failed[] = [
                    'item' => $articleId,
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'successful' => $successful,
            'failed' => $failed,
            'total' => count($articleIds),
            'successCount' => count($successful),
            'failureCount' => count($failed)
        ];
    }

    /**
     * Get the underlying article service
     */
    public function getArticleService(): ArticleService
    {
        return $this->articleService;
    }
}