<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'        => 'ESP32 & Arduino',
                'slug'        => 'esp32-arduino',
                'description' => 'Tutorial dan proyek menggunakan ESP32, ESP8266, dan Arduino IDE untuk embedded system.',
                'color'       => '#2979FF',
                'sort_order'  => 1,
            ],
            [
                'name'        => 'IoT & Smart Device',
                'slug'        => 'iot-smart-device',
                'description' => 'Internet of Things: sensor, aktuator, protokol MQTT, dan integrasi cloud.',
                'color'       => '#FF7A2F',
                'sort_order'  => 2,
            ],
            [
                'name'        => 'Programming',
                'slug'        => 'programming',
                'description' => 'Dasar-dasar pemrograman, algoritma, dan berbagai bahasa pemrograman.',
                'color'       => '#2D3748',
                'sort_order'  => 3,
            ],
            [
                'name'        => 'Web Development',
                'slug'        => 'web-development',
                'description' => 'Tutorial pengembangan web: frontend, backend, database, dan deployment.',
                'color'       => '#1565C0',
                'sort_order'  => 4,
            ],
            [
                'name'        => 'Networking',
                'slug'        => 'networking',
                'description' => 'Jaringan komputer, protokol komunikasi, dan keamanan jaringan.',
                'color'       => '#E65100',
                'sort_order'  => 5,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
