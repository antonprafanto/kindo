<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article11Seeder', '--force' => true]);

$article = \App\Models\Article::where('slug', 'deep-sleep-esp32-sensor-dht22-hemat-baterai')
    ->with('tags')
    ->first();

if (! $article) {
    fwrite(STDERR, "Article not found after seed\n");
    exit(1);
}

$pdo = DB::connection()->getPdo();
$now = now()->format('Y-m-d H:i:s');
$slug = $article->slug;

$sql = "-- Artikel #11: {$article->title}\n";
$sql .= "-- Jalankan di phpMyAdmin production (database kodingindonesia)\n";
$sql .= "-- Tag lookup by slug — aman walau ID beda dengan lokal\n\n";

$sql .= "INSERT INTO tags (name, slug, created_at, updated_at)\n";
$sql .= "VALUES ('deep-sleep', 'deep-sleep', '{$now}', '{$now}')\n";
$sql .= "ON DUPLICATE KEY UPDATE name = VALUES(name), updated_at = VALUES(updated_at);\n\n";

$sql .= "INSERT INTO articles (\n";
$sql .= "  user_id, category_id, title, slug, excerpt, body,\n";
$sql .= "  cover_image, status, is_featured, views_count,\n";
$sql .= "  read_time_minutes, seo_title, seo_description,\n";
$sql .= "  published_at, created_at, updated_at, deleted_at\n";
$sql .= ") VALUES (\n";
$sql .= '  (SELECT id FROM users ORDER BY id ASC LIMIT 1),'."\n";
$sql .= "  (SELECT id FROM categories WHERE slug = 'iot-smart-device' LIMIT 1),\n";
$sql .= '  '.$pdo->quote($article->title).",\n";
$sql .= '  '.$pdo->quote($slug).",\n";
$sql .= '  '.$pdo->quote($article->excerpt).",\n";
$sql .= '  '.$pdo->quote($article->body).",\n";
$sql .= "  NULL,\n";
$sql .= "  'published',\n";
$sql .= "  1,\n";
$sql .= "  0,\n";
$sql .= '  '.(int) $article->read_time_minutes.",\n";
$sql .= '  '.$pdo->quote($article->seo_title).",\n";
$sql .= '  '.$pdo->quote($article->seo_description).",\n";
$sql .= "  '{$now}',\n";
$sql .= "  '{$now}',\n";
$sql .= "  '{$now}',\n";
$sql .= "  NULL\n";
$sql .= ")\nON DUPLICATE KEY UPDATE\n";
$sql .= "  title = VALUES(title),\n";
$sql .= "  body = VALUES(body),\n";
$sql .= "  excerpt = VALUES(excerpt),\n";
$sql .= "  status = VALUES(status),\n";
$sql .= "  is_featured = VALUES(is_featured),\n";
$sql .= "  read_time_minutes = VALUES(read_time_minutes),\n";
$sql .= "  seo_title = VALUES(seo_title),\n";
$sql .= "  seo_description = VALUES(seo_description),\n";
$sql .= "  published_at = VALUES(published_at),\n";
$sql .= "  updated_at = VALUES(updated_at),\n";
$sql .= "  deleted_at = NULL;\n\n";

$sql .= "SET @article_id = (SELECT id FROM articles WHERE slug = '{$slug}' LIMIT 1);\n\n";
$sql .= "DELETE FROM article_tag WHERE article_id = @article_id;\n\n";

foreach ($article->tags->pluck('slug') as $tagSlug) {
    $sql .= "INSERT INTO article_tag (article_id, tag_id)\n";
    $sql .= "SELECT @article_id, id FROM tags WHERE slug = '{$tagSlug}' LIMIT 1;\n\n";
}

$sql .= "-- Setelah SQL: buka /deploy/clear-cache?token=... lalu verifikasi artikel\n";

file_put_contents(__DIR__.'/article-11-production.sql', $sql);
echo "SQL exported to scripts/article-11-production.sql\n";
echo "Read time: {$article->read_time_minutes} min\n";
echo 'Tags: '.$article->tags->pluck('slug')->implode(', ')."\n";
