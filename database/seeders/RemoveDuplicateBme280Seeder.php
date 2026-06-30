<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

/**
 * Hapus duplikat BME280 (#13) yang dibuat manual di Filament.
 * Canonical: i2c-esp32-sensor-bme280-suhu-tekanan-mqtt
 */
class RemoveDuplicateBme280Seeder extends Seeder
{
    public const CANONICAL_SLUG = 'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt';

    public const DUPLICATE_SLUG = 'protokol-i2c-di-esp32-sensor-bme280-suhu-kelembaban-tekanan';

    public function run(): void
    {
        $canonical = Article::withTrashed()->where('slug', self::CANONICAL_SLUG)->first();
        $duplicate = Article::withTrashed()->where('slug', self::DUPLICATE_SLUG)->first();

        if (! $canonical) {
            $this->command?->warn('Artikel canonical BME280 tidak ditemukan — skip cleanup.');

            return;
        }

        if ($canonical->trashed()) {
            $canonical->restore();
            $this->command?->info('Artikel canonical BME280 di-restore.');
        }

        if (! $duplicate) {
            $this->command?->info('Duplikat BME280 sudah tidak ada — skip.');

            return;
        }

        if (empty($canonical->cover_image) && ! empty($duplicate->cover_image)) {
            $canonical->cover_image = $duplicate->cover_image;
            $this->command?->info('Cover image dipindah dari duplikat ke canonical.');
        }

        if ($duplicate->views_count > 0) {
            $canonical->views_count = ($canonical->views_count ?? 0) + $duplicate->views_count;
        }

        $canonical->save();

        if (! $duplicate->trashed()) {
            $duplicate->delete();
            $this->command?->info('Duplikat BME280 di-soft-delete: ' . self::DUPLICATE_SLUG);
        } else {
            $this->command?->info('Duplikat BME280 sudah ter-soft-delete.');
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article13Seeder',
            '--force' => true,
        ]);

        $this->command?->info('✓ Article13Seeder dijalankan ulang pada canonical.');
    }
}
