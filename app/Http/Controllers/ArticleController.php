<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\View\Components\TopicTheme;

class ArticleController extends Controller
{
    public function index()
    {
        $topic = request('topic', 'laravel');
        $theme = new TopicTheme($topic);

        // 最新記事一覧
        $articles = Article::where('topic', $topic)
            ->latest('published_at')
            ->get();

        // いいね数順TOP10（サイドバー用）
        $popular = Article::where('topic', $topic)
            ->orderByDesc('liked_count')
            ->take(10)
            ->get();

        return view('articles.index', compact('articles', 'popular', 'topic', 'theme'));
    }
}
