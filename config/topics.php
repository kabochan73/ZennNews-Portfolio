<?php

// Zenn から記事を取得する対象トピック。
// トピックを追加する場合はここに1行足すだけで、取得コマンド・ナビゲーション・
// カラーテーマ・取得バッチ分けすべてに反映される。
// batch は zenn:fetch --batch= で絞り込む際のグループ番号(1〜2)。
return [
    'laravel'    => ['label' => 'Lara',       'color' => '#f97316', 'batch' => 1],
    'nextjs'     => ['label' => 'Next',       'color' => '#111827', 'batch' => 1],
    'aws'        => ['label' => 'AWS',        'color' => '#38bdf8', 'batch' => 1],
    'ai'         => ['label' => 'AI',         'color' => '#9333ea', 'batch' => 1],
    'web'        => ['label' => 'Web',        'color' => '#0ea5e9', 'batch' => 1],

    'react'      => ['label' => 'React',      'color' => '#22d3ee', 'batch' => 2],
    'typescript' => ['label' => 'TS',         'color' => '#3178c6', 'batch' => 2],
    'javascript' => ['label' => 'JS',         'color' => '#a16207', 'batch' => 2],
    'php'        => ['label' => 'PHP',        'color' => '#6366f1', 'batch' => 2],
    'database'   => ['label' => 'DB',         'color' => '#10b981', 'batch' => 2],
];
