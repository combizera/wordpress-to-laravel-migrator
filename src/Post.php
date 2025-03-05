<?php

namespace Combizera\WpMigration;

class Post
{
    public function __construct
    (
        public int $userId,
        public array $categories,
        public string $title,
        public string $slug,
        public string $content,
        public int $isPublished,
        public string $createdAt,
        public string $updatedAt
    )
    {
        //
    }
}
