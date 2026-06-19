<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'zenn_article_id',
        'title',
        'slug',
        'liked_count',
        'author_name',
        'author_username',
        'published_at',
        'topic',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'liked_count' => 'integer',
    ];
}
