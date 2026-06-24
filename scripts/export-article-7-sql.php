<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$slug = $argv[1] ?? 'memahami-mqtt-esp32-kirim-data-sensor-broker';
$out  = $argv[2] ?? __DIR__ . '/article-7-production.sql';

$article = \App\Models\Article::where('slug', $slug)->with('tags')->first();

if (! $article) {
    fwrite(STDERR, "Article not found: {$slug}\n");
    exit(1);
}

$pdo = DB::connection()->getPdo();
$now = now()->format('Y-m-d H:i:s');

$fields = [
    'user_id'           => $article->user_id,
    'category_id'       => $article->category_id,
    'title'             => $article->title,
    'slug'              => $article->slug,
    'excerpt'           => $article->excerpt,
    'body'              => $article->body,
    'cover_image'       => $article->cover_image,
    'status'            => 'published',
    'is_featured'       => $article->is_featured ? 1 : 0,
    'views_count'       => 0,
    'read_time_minutes' => $article->read_time_minutes,
    'seo_title'         => $article->seo_title,
    'seo_description'   => $article->seo_description,
    'published_at'      => $now,
    'created_at'        => $now,
    'updated_at'        => $now,
];

$cols = implode(', ', array_keys($fields));
$vals = implode(', ', array_map(fn ($v) => $v === null ? 'NULL' : $pdo->quote($v), array_values($fields)));

$sql = "-- Artikel: {$article->title}\n";
$sql .= "-- Jalankan di phpMyAdmin production\n\n";
$sql .= "INSERT INTO articles ({$cols})\nVALUES ({$vals})\n";
$sql .= "ON DUPLICATE KEY UPDATE\n";
$sql .= "  title = VALUES(title),\n";
$sql .= "  body = VALUES(body),\n";
$sql .= "  excerpt = VALUES(excerpt),\n";
$sql .= "  status = VALUES(status),\n";
$sql .= "  seo_title = VALUES(seo_title),\n";
$sql .= "  seo_description = VALUES(seo_description),\n";
$sql .= "  published_at = VALUES(published_at),\n";
$sql .= "  updated_at = VALUES(updated_at);\n\n";

$sql .= "SET @article_id = (SELECT id FROM articles WHERE slug = " . $pdo->quote($slug) . " LIMIT 1);\n\n";
$sql .= "DELETE FROM article_tag WHERE article_id = @article_id;\n\n";

foreach ($article->tags->pluck('id') as $tagId) {
    $sql .= "INSERT INTO article_tag (article_id, tag_id) VALUES (@article_id, {$tagId});\n";
}

file_put_contents($out, $sql);

echo "SQL exported to {$out}\n";
echo "Tags: " . $article->tags->pluck('name')->implode(', ') . "\n";
echo "Read time: {$article->read_time_minutes} min\n";
