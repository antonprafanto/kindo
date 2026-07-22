<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

/**
 * Tombstone #53 — user menghapus artikel HTTP/REST Flask-era dari prod (22 Jul 2026).
 * Seeder ini memastikan slug lama tidak di-publish ulang oleh deploy hook
 * sampai #53 di-rewrite (rencana: jembatan OOP PHP / Laravel path).
 */
class Article53Seeder extends Seeder
{
    public function run(): void
    {
        $slug = 'http-rest-kontrak-stub-flask-oop';

        $existing = Article::withTrashed()->where('slug', $slug)->first();

        if (! $existing) {
            $this->command?->info('✓ Artikel #53 slug lama tidak ada di DB (sudah bersih).');

            return;
        }

        $existing->status = 'draft';
        $existing->is_featured = false;
        $existing->save();

        if (! $existing->trashed()) {
            $existing->delete();
        }

        $this->command?->info('✓ Artikel #53 di-unpublish + soft-delete (menunggu rewrite Seri 4).');
    }
}
