<?php

namespace SemanticPen\SDK\Types;

/**
 * Article data structure
 */
class Article
{
    public $id;
    public $title;
    public $status;
    public $progress;
    public $article_html;
    public $content;
    public $target_keyword;
    public $project_name;
    public $created_at;
    public $updated_at;
    public $error_message;
    public $word_count;
    public $meta_description;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? '';
        $this->title = $data['title'] ?? '';
        $this->status = $data['status'] ?? '';
        $this->progress = $data['progress'] ?? 0;
        $this->article_html = $data['article_html'] ?? '';
        $this->content = $data['content'] ?? '';
        $this->target_keyword = $data['target_keyword'] ?? '';
        $this->project_name = $data['project_name'] ?? '';
        $this->created_at = $data['created_at'] ?? '';
        $this->updated_at = $data['updated_at'] ?? '';
        $this->error_message = $data['error_message'] ?? '';
        $this->word_count = $data['word_count'] ?? 0;
        $this->meta_description = $data['meta_description'] ?? '';
    }

    public function isComplete(): bool
    {
        return in_array(strtolower($this->status), ['finished', 'failed']);
    }

    public function hasError(): bool
    {
        return strtolower($this->status) === 'failed';
    }

    public function hasContent(): bool
    {
        return !empty($this->article_html) || !empty($this->content);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'progress' => $this->progress,
            'article_html' => $this->article_html,
            'content' => $this->content,
            'target_keyword' => $this->target_keyword,
            'project_name' => $this->project_name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'error_message' => $this->error_message,
            'word_count' => $this->word_count,
            'meta_description' => $this->meta_description,
        ];
    }
}