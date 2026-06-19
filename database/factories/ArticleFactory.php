<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'zenn_article_id' => $this->faker->unique()->randomNumber(6),
            'title'           => $this->faker->sentence(),
            'slug'            => $this->faker->slug(),
            'liked_count'     => $this->faker->numberBetween(0, 500),
            'author_name'     => $this->faker->name(),
            'author_username' => $this->faker->userName(),
            'published_at'    => $this->faker->dateTimeBetween('-1 year', 'now'),
            'topic'           => $this->faker->randomElement(['laravel', 'nextjs']),
        ];
    }
}
