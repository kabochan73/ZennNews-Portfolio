<?php

namespace Tests\Unit;

use App\Models\Article;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    public function test_url_attribute_returns_correct_zenn_url(): void
    {
        $article = new Article([
            'author_username' => 'johndoe',
            'slug'            => 'my-laravel-article',
        ]);

        $this->assertSame(
            'https://zenn.dev/johndoe/articles/my-laravel-article',
            $article->url,
        );
    }
}
