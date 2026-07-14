<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // articles.* は array ドライバでもテスト間(同一プロセス)で残るため、
        // 直前のテストの結果を次のテストが読んでしまわないようにクリアする
        Cache::flush();
    }

    public function test_index_shows_laravel_articles_by_default(): void
    {
        Article::factory()->count(3)->create(['topic' => 'laravel']);
        Article::factory()->count(2)->create(['topic' => 'nextjs']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('articles.index');
        $response->assertViewHas('topic', 'laravel');
        $response->assertViewHas('articles', fn ($articles) => count($articles) === 3);
    }

    public function test_index_shows_articles_for_a_newly_added_topic(): void
    {
        Article::factory()->count(3)->create(['topic' => 'laravel']);
        Article::factory()->count(2)->create(['topic' => 'react']);

        $response = $this->get('/?topic=react');

        $response->assertOk();
        $response->assertViewHas('topic', 'react');
        $response->assertViewHas('articles', fn ($articles) => count($articles) === 2);
    }

    public function test_unknown_topic_falls_back_to_laravel(): void
    {
        Article::factory()->create(['topic' => 'laravel']);

        $response = $this->get('/?topic=not-a-real-topic');

        $response->assertOk();
        $response->assertViewHas('topic', 'laravel');
    }

    public function test_view_exposes_all_configured_topics_for_the_nav(): void
    {
        $response = $this->get('/');

        $response->assertViewHas(
            'topics',
            fn ($topics) => array_keys($topics) === array_keys(config('topics')),
        );
    }

    public function test_articles_are_ordered_by_latest_published_at(): void
    {
        Article::factory()->create([
            'topic' => 'laravel',
            'title' => 'Old article',
            'published_at' => now()->subDays(5),
        ]);
        Article::factory()->create([
            'topic' => 'laravel',
            'title' => 'New article',
            'published_at' => now(),
        ]);

        $response = $this->get('/');

        $articles = $response->viewData('articles');
        $this->assertSame('New article', $articles[0]['title']);
    }
}
