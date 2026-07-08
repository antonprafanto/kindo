<?php

namespace Database\Seeders;

/**
 * Sumber tunggal data taxonomy UI/UX — dipakai migration, CategorySeeder, TagSeeder.
 */
class UiUxTaxonomy
{
    public static function category(): array
    {
        return [
            'name'        => 'UI/UX & Desain',
            'slug'        => 'ui-ux-desain',
            'description' => 'Desain antarmuka, pengalaman pengguna, wireframe, prototyping, usability, dan design system.',
            'color'       => '#9333EA',
            'sort_order'  => 5,
        ];
    }

    /**
     * @return list<array{slug: string, name: string}>
     */
    public static function tags(): array
    {
        return [
            ['slug' => 'ui-ux', 'name' => 'UI/UX'],
            ['slug' => 'figma', 'name' => 'Figma'],
            ['slug' => 'wireframe', 'name' => 'Wireframe'],
            ['slug' => 'prototyping', 'name' => 'Prototyping'],
            ['slug' => 'design-system', 'name' => 'Design System'],
            ['slug' => 'accessibility', 'name' => 'Accessibility'],
            ['slug' => 'usability', 'name' => 'Usability'],
            ['slug' => 'user-research', 'name' => 'User Research'],
            ['slug' => 'responsive-design', 'name' => 'Responsive Design'],
            ['slug' => 'ux-writing', 'name' => 'UX Writing'],
        ];
    }

    /**
     * @return list<string>
     */
    public static function tagSlugs(): array
    {
        return array_column(self::tags(), 'slug');
    }
}
