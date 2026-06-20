<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            'esp32', 'esp8266', 'arduino', 'micropython',
            'mqtt', 'wifi', 'bluetooth', 'sensor',
            'led', 'relay', 'motor', 'servo',
            'php', 'laravel', 'javascript', 'python',
            'html', 'css', 'mysql', 'api',
            'iot', 'smarthome', 'wemos', 'nodemcu',
            'firebase', 'blynk', 'homeassistant',
        ];

        foreach ($tags as $name) {
            Tag::updateOrCreate(
                ['slug' => $name],
                ['name' => $name, 'slug' => $name]
            );
        }
    }
}
