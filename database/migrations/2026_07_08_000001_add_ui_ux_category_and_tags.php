<?php

use Database\Seeders\UiUxTaxonomy;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $category = [
            ...UiUxTaxonomy::category(),
            'deleted_at' => null,
            'updated_at' => $now,
        ];

        if (DB::table('categories')->where('slug', $category['slug'])->exists()) {
            DB::table('categories')->where('slug', $category['slug'])->update($category);
        } else {
            DB::table('categories')->insert([
                ...$category,
                'created_at' => $now,
            ]);
        }

        DB::table('categories')
            ->where('slug', 'networking')
            ->update(['sort_order' => 6, 'updated_at' => $now]);

        foreach (UiUxTaxonomy::tags() as $tag) {
            $values = [
                'name'       => $tag['name'],
                'deleted_at' => null,
                'updated_at' => $now,
            ];

            if (DB::table('tags')->where('slug', $tag['slug'])->exists()) {
                DB::table('tags')->where('slug', $tag['slug'])->update($values);
            } else {
                DB::table('tags')->insert([
                    ...$values,
                    'slug'       => $tag['slug'],
                    'created_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        //
    }
};
