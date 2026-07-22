<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['slug' => 'esp32', 'name' => 'esp32'],
            ['slug' => 'esp8266', 'name' => 'esp8266'],
            ['slug' => 'arduino', 'name' => 'arduino'],
            ['slug' => 'micropython', 'name' => 'micropython'],
            ['slug' => 'mqtt', 'name' => 'mqtt'],
            ['slug' => 'wifi', 'name' => 'wifi'],
            ['slug' => 'bluetooth', 'name' => 'bluetooth'],
            ['slug' => 'sensor', 'name' => 'sensor'],
            ['slug' => 'led', 'name' => 'led'],
            ['slug' => 'relay', 'name' => 'relay'],
            ['slug' => 'motor', 'name' => 'motor'],
            ['slug' => 'servo', 'name' => 'servo'],
            ['slug' => 'php', 'name' => 'php'],
            ['slug' => 'laravel', 'name' => 'laravel'],
            ['slug' => 'javascript', 'name' => 'javascript'],
            ['slug' => 'python', 'name' => 'python'],
            ['slug' => 'oop', 'name' => 'oop'],
            ['slug' => 'oop-class', 'name' => 'oop-class'],
            ['slug' => 'inheritance', 'name' => 'inheritance'],
            ['slug' => 'composition', 'name' => 'composition'],
            ['slug' => 'encapsulation', 'name' => 'encapsulation'],
            ['slug' => 'polymorphism', 'name' => 'polymorphism'],
            ['slug' => 'abstraction', 'name' => 'abstraction'],
            ['slug' => 'dataclass', 'name' => 'dataclass'],
            ['slug' => 'design-pattern', 'name' => 'design-pattern'],
            ['slug' => 'html', 'name' => 'html'],
            ['slug' => 'css', 'name' => 'css'],
            ['slug' => 'mysql', 'name' => 'mysql'],
            ['slug' => 'api', 'name' => 'api'],
            ['slug' => 'iot', 'name' => 'iot'],
            ['slug' => 'smarthome', 'name' => 'smarthome'],
            ['slug' => 'wemos', 'name' => 'wemos'],
            ['slug' => 'nodemcu', 'name' => 'nodemcu'],
            ['slug' => 'firebase', 'name' => 'firebase'],
            ['slug' => 'blynk', 'name' => 'blynk'],
            ['slug' => 'homeassistant', 'name' => 'homeassistant'],
            ...UiUxTaxonomy::tags(),
        ];

        foreach ($tags as $tag) {
            Tag::updateOrCreate(
                ['slug' => $tag['slug']],
                ['name' => $tag['name'], 'slug' => $tag['slug']]
            );
        }
    }
}
