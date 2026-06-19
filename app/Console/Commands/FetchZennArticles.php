<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

#[Signature('zenn:fetch')]
#[Description('Fetch latest articles from Zenn API and save to database')]
class FetchZennArticles extends Command
{
    // Zenn API のエンドポイント
    private const BASE_URL = 'https://zenn.dev/api/articles';

    // 取得対象のトピック
    private const TOPICS = ['laravel', 'nextjs'];

    // 1回のフェッチでトピックごとに取得する件数
    private const FETCH_LIMIT = 25;

    // DBに保存するトピックごとの最大件数
    private const STORE_LIMIT_PER_TOPIC = 50;

    public function handle(): int
    {
        $this->info('Fetching articles from Zenn...');

        $created = 0;
        $updated = 0;

        foreach (self::TOPICS as $topic) {
            // Zenn API からトピックの最新記事を取得
            $response = Http::get(self::BASE_URL, [
                'topicname' => $topic,
                'order'     => 'latest',
                'count'     => self::FETCH_LIMIT,
            ]);

            if ($response->failed()) {
                Log::error('Zenn API request failed', ['topic' => $topic]);
                continue;
            }

            $articles = $response->json('articles', []);

            foreach ($articles as $article) {
                // zenn_article_id をキーに、既存なら更新・なければ新規作成
                $result = Article::updateOrCreate(
                    ['zenn_article_id' => $article['id']],
                    [
                        'title'           => $article['title'],
                        'slug'            => $article['slug'],
                        'liked_count'     => $article['liked_count'] ?? 0,
                        'author_name'     => $article['user']['name'] ?? '',
                        'author_username' => $article['user']['username'] ?? '',
                        'published_at'    => $article['published_at'],
                        'topic'           => $topic,
                    ],
                );

                $result->wasRecentlyCreated ? $created++ : $updated++;
            }
        }

        // 全トピックの古い記事を削除
        foreach (self::TOPICS as $topic) {
            $this->deleteOldArticles($topic);
        }

        $this->info("Done. Created: {$created}, Updated: {$updated}");

        return self::SUCCESS;
    }

    // トピックごとに上限を超えた分の古い記事を削除する
    private function deleteOldArticles(string $topic): void
    {
        $total = Article::where('topic', $topic)->count();

        if ($total <= self::STORE_LIMIT_PER_TOPIC) {
            return;
        }

        $deleteCount = $total - self::STORE_LIMIT_PER_TOPIC;

        // published_at の古い順に削除
        Article::where('topic', $topic)
            ->orderBy('published_at')
            ->limit($deleteCount)
            ->get()
            ->each->delete();

        $this->info("Deleted {$deleteCount} old {$topic} article(s).");
    }
}
