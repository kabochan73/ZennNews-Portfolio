<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Support\Facades\Cache;

class ArticleController extends Controller
{
    public function index()
    {
        $topics = config('topics');
        $topic = request('topic', 'laravel');

        if (! array_key_exists($topic, $topics)) {
            $topic = 'laravel';
        }

        // 記事一覧はトピックごとにキャッシュし、zenn:fetch 実行時に破棄する
        // (Redisキャッシュはallowed_classes=falseでunserializeされるため、Eloquentモデルではなく
        //  プレーンな配列に変換してから保存する)
        $articles = Cache::remember(
            "articles.{$topic}",
            now()->addDay(),
            fn () => Article::where('topic', $topic)
                ->latest('published_at')
                ->get()
                ->map(fn (Article $article) => [
                    'title'        => $article->title,
                    'url'          => $article->url,
                    'author_name'  => $article->author_name,
                    'published_at' => $article->published_at->format('Y/m/d'),
                ])
                ->all(),
        );

        return view('articles.index', compact('articles', 'topic', 'topics'));
    }
}
