<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UiUxTaxonomyMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_ui_ux_taxonomy_exists_after_migrations(): void
    {
        $this->assertDatabaseHas('categories', [
            'slug' => 'ui-ux-desain',
            'name' => 'UI/UX & Desain',
        ]);

        $this->assertDatabaseHas('tags', [
            'slug' => 'ui-ux',
            'name' => 'UI/UX',
        ]);

        $this->assertDatabaseHas('tags', [
            'slug' => 'responsive-design',
            'name' => 'Responsive Design',
        ]);

        $this->assertDatabaseHas('tags', [
            'slug' => 'ux-writing',
            'name' => 'UX Writing',
        ]);
    }

    public function test_ui_ux_migration_updates_networking_sort_order(): void
    {
        DB::table('categories')->insert([
            'name'        => 'Networking',
            'slug'        => 'networking',
            'description' => 'Jaringan komputer.',
            'color'       => '#E65100',
            'sort_order'  => 5,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $migration = require database_path('migrations/2026_07_08_000001_add_ui_ux_category_and_tags.php');
        $migration->up();

        $this->assertDatabaseHas('categories', [
            'slug'       => 'networking',
            'sort_order' => 6,
        ]);
    }

    public function test_ui_ux_migration_preserves_created_at_on_repeat(): void
    {
        DB::table('categories')->where('slug', 'ui-ux-desain')->update([
            'created_at' => '2026-01-01 00:00:00',
        ]);

        $migration = require database_path('migrations/2026_07_08_000001_add_ui_ux_category_and_tags.php');
        $migration->up();

        $this->assertDatabaseHas('categories', [
            'slug'       => 'ui-ux-desain',
            'created_at' => '2026-01-01 00:00:00',
        ]);
    }
}
