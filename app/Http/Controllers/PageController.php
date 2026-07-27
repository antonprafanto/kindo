<?php

namespace App\Http\Controllers;

use App\Models\Article;

class PageController extends Controller
{
    public function about()
    {
        $articleCount = Article::published()->count();
        return view('about', compact('articleCount'));
    }

    public function privacy()
    {
        return view('privacy');
    }

    public function fullstackIot()
    {
        $phases = [
            [
                'code' => 'ZERO',
                'title' => 'Fondasi awam',
                'blurb' => 'Peta IoT, alat, listrik dasar, dan coding mini — tanpa Wi‑Fi dulu.',
                'modules' => 'FS-01 … FS-16',
            ],
            [
                'code' => 'BUILDER',
                'title' => 'Perangkat hidup',
                'blurb' => 'ESP32, GPIO, sensor, aktuator, dan bus I2C — automasi lokal.',
                'modules' => 'FS-17 … FS-28',
            ],
            [
                'code' => 'CONNECTED',
                'title' => 'Terhubung',
                'blurb' => 'Wi‑Fi, HTTP/JSON, MQTT, Mosquitto, dan rules ringan.',
                'modules' => 'FS-29 … FS-38',
            ],
            [
                'code' => 'FULLSTACK',
                'title' => 'Backend & dashboard',
                'blurb' => 'Python, SQLite, API Flask, chart, kontrol, dan alert.',
                'modules' => 'FS-39 … FS-48',
            ],
            [
                'code' => 'HERO',
                'title' => 'Production & capstone',
                'blurb' => 'Keamanan, daya, OTA, Git, dan proyek Stasiun Ruang Belajar.',
                'modules' => 'FS-49 … FS-56',
            ],
        ];

        return view('belajar.fullstack-iot', [
            'phases' => $phases,
            'trakteerUrl' => config('kindo.trakteer_tip_url'),
        ]);
    }
}
