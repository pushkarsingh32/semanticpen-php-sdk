<?php

namespace SemanticPen\SDK\Services;

use SemanticPen\SDK\Core\BaseClient;
use SemanticPen\SDK\Types\Article;
use SemanticPen\SDK\Types\ArticleGenerationRequest;
use SemanticPen\SDK\Types\ArticleGenerationResponse;
use SemanticPen\SDK\Exceptions\ValidationException;
use SemanticPen\SDK\Exceptions\SemanticPenException;

/**
 * Service for article generation and management
 */
class ArticleService extends BaseClient
{
    /**
     * Test API connection
     */
    public function testConnection(): bool
    {
        try {
            $this->get('/api/articles/00000000-0000-0000-0000-000000000000');
            return true;
        } catch (SemanticPenException $e) {
            if ($e->getStatusCode() === 404) {
                return true; // 404 means auth worked but article doesn't exist
            }
            if (in_array($e->getStatusCode(), [401, 403])) {
                return false; // Auth failed
            }
            return false; // Other errors
        }
    }

    /**
     * Generate articles from request
     */
    public function generateArticles(ArticleGenerationRequest $request): ArticleGenerationResponse
    {
        $this->validateGenerationRequest($request);

        $data = $request->toArray();
        $response = $this->post('/api/articles', $data);

        $result = new ArticleGenerationResponse($response);
        
        if ($result->hasError()) {
            throw new SemanticPenException($result->error);
        }

        return $result;
    }

    /**
     * Generate a single article
     */
    public function generateArticle(string $targetKeyword, string $projectName = null): array
    {
        $request = new ArticleGenerationRequest($targetKeyword, $projectName);
        $response = $this->generateArticles($request);

        $articleId = $response->getFirstArticleId();
        if (!$articleId) {
            throw new SemanticPenException('No articles were generated');
        }

        return [
            'articleId' => $articleId,
            'projectId' => $response->projectId,
            'message' => $response->message
        ];
    }

    /**
     * Generate multiple articles in bulk
     */
    public function generateBulkArticles(array $keywords, string $projectName = null): array
    {
        if (empty($keywords)) {
            throw new ValidationException('Keywords array cannot be empty');
        }

        $successful = [];
        $failed = [];

        try {
            $request = new ArticleGenerationRequest($keywords, $projectName);
            $response = $this->generateArticles($request);

            foreach ($response->articleIds as $index => $articleId) {
                $successful[] = [
                    'articleId' => $articleId,
                    'keyword' => $keywords[$index] ?? "keyword_{$index}"
                ];
            }
        } catch (SemanticPenException $e) {
            foreach ($keywords as $keyword) {
                $failed[] = [
                    'item' => $keyword,
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'successful' => $successful,
            'failed' => $failed,
            'total' => count($keywords),
            'successCount' => count($successful),
            'failureCount' => count($failed)
        ];
    }

    /**
     * Get article by ID
     */
    public function getArticle(string $articleId): Article
    {
        if (empty($articleId)) {
            throw new ValidationException('Article ID is required');
        }

        $response = $this->get("/api/articles/{$articleId}");
        return new Article($response);
    }

    /**
     * Get multiple articles by IDs
     */
    public function getArticles(array $articleIds): array
    {
        if (empty($articleIds)) {
            throw new ValidationException('Article IDs array cannot be empty');
        }

        $successful = [];
        $failed = [];

        foreach ($articleIds as $articleId) {
            try {
                $article = $this->getArticle($articleId);
                $successful[] = $article;
            } catch (SemanticPenException $e) {
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
     * Check if article is complete
     */
    public function isArticleComplete(string $articleId): bool
    {
        try {
            $article = $this->getArticle($articleId);
            return $article->hasContent() && 
                   !in_array(strtolower($article->status), ['queued', 'processing']);
        } catch (SemanticPenException $e) {
            if ($e->getStatusCode() === 404) {
                return false;
            }
            throw $e;
        }
    }

    /**
     * Get article status information
     */
    public function getArticleStatus(string $articleId): array
    {
        try {
            $article = $this->getArticle($articleId);
            
            return [
                'id' => $article->id,
                'status' => $article->status,
                'isComplete' => $this->isStatusComplete($article->status),
                'hasError' => $article->hasError(),
                'errorMessage' => $article->error_message
            ];
        } catch (SemanticPenException $e) {
            return [
                'id' => $articleId,
                'status' => 'unknown',
                'isComplete' => false,
                'hasError' => true,
                'errorMessage' => $e->getMessage()
            ];
        }
    }

    /**
     * Wait for article completion with polling
     */
    public function waitForArticle(string $articleId, array $options = []): Article
    {
        $interval = $options['interval'] ?? 5; // seconds
        $maxAttempts = $options['maxAttempts'] ?? 60; // ~5 minutes
        $onProgress = $options['onProgress'] ?? null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $article = $this->getArticle($articleId);
            
            if ($onProgress && is_callable($onProgress)) {
                $onProgress($attempt, $article->status);
            }

            if ($article->isComplete()) {
                return $article;
            }

            if ($attempt < $maxAttempts) {
                sleep($interval);
            }
        }

        throw new SemanticPenException("Article did not complete within {$maxAttempts} attempts");
    }

    private function validateGenerationRequest(ArticleGenerationRequest $request): void
    {
        if (empty($request->targetKeyword)) {
            throw new ValidationException('Target keyword is required');
        }

        if (is_string($request->targetKeyword)) {
            if (trim($request->targetKeyword) === '') {
                throw new ValidationException('Target keyword cannot be empty');
            }
        } elseif (is_array($request->targetKeyword)) {
            if (empty($request->targetKeyword)) {
                throw new ValidationException('Target keywords array cannot be empty');
            }
            
            foreach ($request->targetKeyword as $keyword) {
                if (!is_string($keyword) || trim($keyword) === '') {
                    throw new ValidationException('All keywords must be non-empty strings');
                }
            }
        } else {
            throw new ValidationException('Target keyword must be a string or array of strings');
        }
    }

    private function isStatusComplete(string $status): bool
    {
        return in_array(strtolower($status), ['finished', 'failed']);
    }
}