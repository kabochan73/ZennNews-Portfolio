<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FetchZennArticlesTest extends TestCase
{
    use RefreshDatabase;

    private function makeArticleData(int $id, string $topic): array
    {
        return [
            'id' => $id,
            'title' => "Article {$id}",
            'slug' => "article-{$id}",
            'published_at' => now()->toISOString(),
            'user' => [
                'name' => 'Test User',
                'username' => 'testuser',
            ],
        ];
    }

    public function test_batch_option_limits_fetch_to_that_batchs_topics(): void
    {
        Http::fake([
            '*topicname=laravel*' => Http::response(['articles' => [
                $this->makeArticleData(1, 'laravel'),
            ], 'next_page' => null]),
            '*' => Http::response(['articles' => [], 'next_page' => null]),
        ]);

        $this->artisan('zenn:fetch', ['--batch' => 1])->assertSuccessful();

        $this->assertDatabaseHas('articles', ['zenn_article_id' => 1, 'topic' => 'laravel']);
        Http::assertNotSent(fn ($request) => str_contains((string) $request->url(), 'topicname=typescript'));
    }

    public function test_without_batch_option_fetches_every_configured_topic(): void
    {
        Http::fake([
            '*' => Http::response(['articles' => [], 'next_page' => null]),
        ]);

        $this->artisan('zenn:fetch')->assertSuccessful();

        Http::assertSentCount(count(config('topics')));
    }

    public function test_fetches_and_creates_articles(): void
    {
        Http::fake([
            '*topicname=laravel*' => Http::response(['articles' => [
                $this->makeArticleData(1, 'laravel'),
                $this->makeArticleData(2, 'laravel'),
            ], 'next_page' => null]),
            '*' => Http::response(['articles' => [], 'next_page' => null]),
        ]);

        $this->artisan('zenn:fetch', ['--batch' => 1])->assertSuccessful();

        $this->assertDatabaseCount('articles', 2);
        $this->assertDatabaseHas('articles', ['zenn_article_id' => 1, 'topic' => 'laravel']);
    }

    public function test_updates_existing_articles(): void
    {
        Article::factory()->create(['zenn_article_id' => 1, 'topic' => 'laravel', 'title' => 'Old title']);

        Http::fake([
            '*topicname=laravel*' => Http::response(['articles' => [
                array_merge($this->makeArticleData(1, 'laravel'), ['title' => 'Updated title']),
            ], 'next_page' => null]),
            '*' => Http::response(['articles' => [], 'next_page' => null]),
        ]);

        $this->artisan('zenn:fetch', ['--batch' => 1])->assertSuccessful();

        $this->assertDatabaseHas('articles', ['zenn_article_id' => 1, 'title' => 'Updated title']);
        $this->assertDatabaseCount('articles', 1);
    }

    public function test_skips_topic_on_api_failure(): void
    {
        Http::fake([
            '*topicname=laravel*' => Http::response(null, 500),
            '*topicname=nextjs*' => Http::response(['articles' => [
                $this->makeArticleData(3, 'nextjs'),
            ], 'next_page' => null]),
            '*' => Http::response(['articles' => [], 'next_page' => null]),
        ]);

        $this->artisan('zenn:fetch', ['--batch' => 1])->assertSuccessful();

        $this->assertDatabaseCount('articles', 1);
        $this->assertDatabaseHas('articles', ['topic' => 'nextjs']);
    }

    public function test_deletes_oldest_articles_when_exceeding_limit(): void
    {
        Article::factory()->count(51)->create([
            'topic' => 'laravel',
            'published_at' => now()->subYear(),
        ]);

        Http::fake([
            '*topicname=laravel*' => Http::response(['articles' => [
                $this->makeArticleData(9999, 'laravel'),
            ], 'next_page' => null]),
            '*' => Http::response(['articles' => [], 'next_page' => null]),
        ]);

        $this->artisan('zenn:fetch', ['--batch' => 1])->assertSuccessful();

        $this->assertDatabaseCount('articles', 50);
    }

    public function test_forgets_cache_for_refetched_topics(): void
    {
        Cache::put('articles.laravel', ['stale data'], now()->addDay());

        Http::fake([
            '*' => Http::response(['articles' => [], 'next_page' => null]),
        ]);

        $this->artisan('zenn:fetch', ['--batch' => 1])->assertSuccessful();

        $this->assertFalse(Cache::has('articles.laravel'));
    }
}
