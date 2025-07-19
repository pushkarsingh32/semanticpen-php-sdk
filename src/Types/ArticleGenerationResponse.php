<?php

namespace SemanticPen\SDK\Types;

/**
 * Article generation response data structure
 */
class ArticleGenerationResponse
{
    public $articleId;
    public $articleIds;
    public $projectId;
    public $message;
    public $error;

    public function __construct(array $data = [])
    {
        $this->articleId = $data['articleId'] ?? null;
        $this->articleIds = $data['articleIds'] ?? [];
        $this->projectId = $data['projectId'] ?? '';
        $this->message = $data['message'] ?? '';
        $this->error = $data['error'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'articleId' => $this->articleId,
            'articleIds' => $this->articleIds,
            'projectId' => $this->projectId,
            'message' => $this->message,
            'error' => $this->error,
        ];
    }

    public function hasError(): bool
    {
        return !empty($this->error);
    }

    public function getFirstArticleId(): ?string
    {
        if ($this->articleId) {
            return $this->articleId;
        }
        
        if (!empty($this->articleIds)) {
            return $this->articleIds[0];
        }
        
        return null;
    }
}