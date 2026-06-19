<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FetchZennArticlesTest extends TestCase
{
    use RefreshDatabase;

    private function makeArticleData(int $id, string $topic): array
    {
        return [
            'id'           => $id,
            'title'        => "Article {$id}",
            'slug'         => "article-{$id}",
            'liked_count'  => 10,
            'published_at' => now()->toISOString(),
            'user'         => [
                'name'     => 'Test User',
                'username' => 'testuser',
            ],
        ];
    }

    public function test_fetches_and_creates_articles(): void
    {
        Http::fake([
            '*topicname=laravel*' => Http::response(['articles' => [
                $this->makeArticleData(1, 'laravel'),
                $this->makeArticleData(2, 'laravel'),
            ], 'next_page' => null]),
            '*topicname=nextjs*' => Http::response(['articles' => [
                $this->makeArticleData(3, 'nextjs'),
            ], 'next_page' => null]),
        ]);

        $this->artisan('zenn:fetch')->assertSuccessful();

        $this->assertDatabaseCount('articles', 3);
        $this->assertDatabaseHas('articles', ['zenn_article_id' => 1, 'topic' => 'laravel']);
        $this->assertDatabaseHas('articles', ['zenn_article_id' => 3, 'topic' => 'nextjs']);
    }

    public function test_updates_existing_articles(): void
    {
        Article::factory()->create(['zenn_article_id' => 1, 'liked_count' => 5, 'topic' => 'laravel']);

        Http::fake([
            '*topicname=laravel*' => Http::response(['articles' => [
                array_merge($this->makeArticleData(1, 'laravel'), ['liked_count' => 999]),
            ], 'next_page' => null]),
            '*topicname=nextjs*' => Http::response(['articles' => [], 'next_page' => null]),
        ]);

        $this->artisan('zenn:fetch')->assertSuccessful();

        $this->assertDatabaseHas('articles', ['zenn_article_id' => 1, 'liked_count' => 999]);
        $this->assertDatabaseCount('articles', 1);
    }

    public function test_skips_topic_on_api_failure(): void
    {
        Http::fake([
            '*topicname=laravel*' => Http::response(null, 500),
            '*topicname=nextjs*'  => Http::response(['articles' => [
                $this->makeArticleData(3, 'nextjs'),
            ], 'next_page' => null]),
        ]);

        $this->artisan('zenn:fetch')->assertSuccessful();

        $this->assertDatabaseCount('articles', 1);
        $this->assertDatabaseHas('articles', ['topic' => 'nextjs']);
    }

    public function test_deletes_oldest_articles_when_exceeding_limit(): void
    {
        // 上限(50件)を超えるよう既存記事を51件作成
        Article::factory()->count(51)->create([
            'topic'        => 'laravel',
            'published_at' => now()->subYear(),
        ]);

        Http::fake([
            '*topicname=laravel*' => Http::response(['articles' => [
                $this->makeArticleData(9999, 'laravel'),
            ], 'next_page' => null]),
            '*topicname=nextjs*' => Http::response(['articles' => [], 'next_page' => null]),
        ]);

        $this->artisan('zenn:fetch')->assertSuccessful();

        $this->assertDatabaseCount('articles', 50);
    }
}
