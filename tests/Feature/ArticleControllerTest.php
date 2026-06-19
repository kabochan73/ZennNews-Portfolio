<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_shows_laravel_articles_by_default(): void
    {
        Article::factory()->count(3)->create(['topic' => 'laravel']);
        Article::factory()->count(2)->create(['topic' => 'nextjs']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('articles.index');
        $response->assertViewHas('topic', 'laravel');
        $response->assertViewHas('articles', fn ($articles) => $articles->count() === 3);
    }

    public function test_index_shows_nextjs_articles_when_topic_is_nextjs(): void
    {
        Article::factory()->count(3)->create(['topic' => 'laravel']);
        Article::factory()->count(2)->create(['topic' => 'nextjs']);

        $response = $this->get('/?topic=nextjs');

        $response->assertOk();
        $response->assertViewHas('topic', 'nextjs');
        $response->assertViewHas('articles', fn ($articles) => $articles->count() === 2);
    }

    public function test_popular_sidebar_shows_top_10_by_liked_count(): void
    {
        Article::factory()->count(15)->create(['topic' => 'laravel']);

        $response = $this->get('/');

        $response->assertViewHas('popular', fn ($popular) => $popular->count() === 10);
    }

    public function test_articles_are_ordered_by_latest_published_at(): void
    {
        $old = Article::factory()->create(['topic' => 'laravel', 'published_at' => now()->subDays(5)]);
        $new = Article::factory()->create(['topic' => 'laravel', 'published_at' => now()]);

        $response = $this->get('/');

        $articles = $response->viewData('articles');
        $this->assertTrue($articles->first()->id === $new->id);
    }
}
