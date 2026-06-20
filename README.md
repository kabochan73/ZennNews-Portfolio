# ZennNews

Zenn の技術記事（Laravel / Next.js / AWS）を自動取得して一覧表示するニュースアグリゲーターです。

## 概要

毎日午前9時（JST）に Zenn API から最新記事を取得し、トピックごとに最大50件をデータベースへ保存します。ユーザーはトップページでトピックを切り替えながら最新記事と人気記事ランキングを確認できます。

## 使用技術

| カテゴリ | 技術 |
|---|---|
| バックエンド | PHP 8.4 / Laravel 13 |
| フロントエンド | Blade / Tailwind CSS |
| データベース | PostgreSQL 17 |
| インフラ | Docker（nginx + PHP-FPM + supervisor） |
| デプロイ | Railway |
| テスト | PHPUnit 12 |

## 機能

- **トピック切り替え** — Laravel / Next.js / AWS の3トピックをワンクリックで切り替え
- **最新記事一覧** — 公開日時の新しい順に表示
- **人気記事ランキング** — いいね数の多い順 TOP 10 をサイドバーに表示
- **自動取得スケジューラー** — 毎日9時（JST）に Zenn API から記事を自動更新
- **上限管理** — トピックごとに最大50件を保存し、古い記事を自動削除

## アーキテクチャ

```
[Railway Cron Job]
       ↓ php artisan schedule:run（毎分）
[Laravel Scheduler]
       ↓ 毎日 09:00 JST
[zenn:fetch コマンド]
       ↓ Zenn API（3トピック × 25件）
[articles テーブル]（PostgreSQL）
       ↓
[ArticleController]
       ↓
[Blade View]（ユーザー）
```

## ローカル開発環境の構築

### 前提条件

- Docker / Docker Compose

### セットアップ

```bash
git clone https://github.com/kabochan73/ZennNews-Portfolio.git
cd ZennNews-Portfolio

# 環境変数の設定
cp .env.example .env  # ※ .env は .env.example を元に作成してください

# コンテナ起動
docker compose up -d

# 依存パッケージのインストール
docker compose exec app composer install
docker compose exec app npm install && npm run build

# マイグレーション
docker compose exec app php artisan migrate

# 記事の初期取得
docker compose exec app php artisan zenn:fetch
```

ブラウザで `http://localhost:8080` にアクセスしてください。

## テスト

```bash
docker compose exec app php artisan test
```

| テストスイート | 件数 | 内容 |
|---|---|---|
| Unit | 1 | Article モデルの URL 生成 |
| Feature | 8 | コントローラーのレスポンス・API 取得コマンド |

## ディレクトリ構成（主要ファイル）

```
app/
├── Console/Commands/FetchZennArticles.php  # Zenn API 取得コマンド
├── Http/Controllers/ArticleController.php  # 記事一覧コントローラー
├── Models/Article.php                      # Article モデル
└── View/Components/TopicTheme.php          # トピック別カラーテーマ
database/
└── migrations/..._create_articles_table.php
resources/views/articles/index.blade.php    # メインビュー
routes/
├── web.php                                 # ルーティング
└── console.php                             # スケジューラー定義
```
