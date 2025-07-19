<?php

namespace SemanticPen\SDK\Types;

/**
 * Article generation request data structure
 */
class ArticleGenerationRequest
{
    public $targetKeyword;
    public $projectName;

    public function __construct($targetKeyword, string $projectName = null)
    {
        $this->targetKeyword = $targetKeyword;
        $this->projectName = $projectName;
    }

    public function toArray(): array
    {
        $data = [
            'targetKeyword' => $this->targetKeyword,
        ];

        if ($this->projectName !== null) {
            $data['projectName'] = $this->projectName;
        }

        return $data;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['targetKeyword'] ?? $data['target_keyword'] ?? '',
            $data['projectName'] ?? $data['project_name'] ?? null
        );
    }
}