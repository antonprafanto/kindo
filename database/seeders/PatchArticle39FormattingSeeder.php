<?php

namespace Database\Seeders;

use App\Models\Article;
use Database\Seeders\Support\Seri2ArticleLinks;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Patch formatting artikel #39 (capstone greenhouse) — feedback UX:
 * 1. Tabel MQTT & Perbandingan Jalur: kolom artikel jadi hyperlink (#13, capstone, dll.)
 * 2. FAQ: dipisah per pertanyaan (h3 + p), bukan satu paragraf panjang.
 */
class PatchArticle39FormattingSeeder extends Seeder
{
  private const SLUG = 'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt';

  public function run(): void
  {
    $article = Article::where('slug', self::SLUG)->first();
    if ($article === null) {
      throw new RuntimeException('Artikel #39 tidak ditemukan — slug: ' . self::SLUG);
    }

    $body = $article->body;

    $body = $this->replaceSection(
      $body,
      '<h2>Hierarki Topic MQTT Greenhouse</h2>',
      '<h2>Perbandingan Jalur yang Digabung</h2>',
      $this->mqttTopicSection(),
    );

    $body = $this->replaceSection(
      $body,
      '<h2>Perbandingan Jalur yang Digabung</h2>',
      '<h2>Checklist Uji Coba End-to-End</h2>',
      $this->perbandinganJalurSection(),
    );

    $body = $this->replaceSection(
      $body,
      '<h2>FAQ — Pertanyaan Umum Greenhouse IoT</h2>',
      '<h2>Penutup — Terima Kasih Mengikuti Seri 2!</h2>',
      $this->faqSection(),
    );

    $article->body = $body;
    $article->save();

    $this->command?->info('✓ Artikel #39 diformat ulang: tabel artikel + FAQ terpisah.');
  }

  private function replaceSection(string $body, string $startMarker, string $endMarker, string $replacement): string
  {
    $start = strpos($body, $startMarker);
    if ($start === false) {
      throw new RuntimeException("Marker tidak ditemukan: {$startMarker}");
    }

    $end = strpos($body, $endMarker, $start);
    if ($end === false) {
      throw new RuntimeException("Marker akhir tidak ditemukan: {$endMarker}");
    }

    return substr($body, 0, $start) . $replacement . substr($body, $end);
  }

  private function mqttTopicSection(): string
  {
    $l = Seri2ArticleLinks::class;

    return <<<HTML
<h2>Hierarki Topic MQTT Greenhouse</h2>
<p>Konvensi Seri 2 — semua node memakai prefix <code>kodingindonesia/esp32/</code>:</p>
<table>
<thead>
<tr><th>Topic</th><th>Arah</th><th>Payload</th><th>Sumber artikel</th></tr>
</thead>
<tbody>
<tr>
<td><code>.../bme280/data</code></td>
<td>publish</td>
<td><code>{"suhu":28.1,"kelembaban":72.0,"tekanan":1008.5,"unix":1782977400}</code></td>
<td>{$l::link(13)}</td>
</tr>
<tr>
<td><code>.../tanah/data</code></td>
<td>publish</td>
<td><code>{"kelembaban_tanah":42,"unix":1782977400}</code></td>
<td>{$l::link(35)}</td>
</tr>
<tr>
<td><code>.../cahaya/data</code></td>
<td>publish</td>
<td><code>{"cahaya_percent":78,"unix":1782977400}</code></td>
<td>{$l::link(35)}</td>
</tr>
<tr>
<td><code>.../pompa/kontrol</code></td>
<td>subscribe</td>
<td><code>ON</code> / <code>OFF</code> / <code>AUTO</code></td>
<td><a href="/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">capstone</a></td>
</tr>
<tr>
<td><code>.../pir/gerak</code></td>
<td>publish</td>
<td><code>{"gerak":true,"lampu":"ON"}</code></td>
<td>{$l::link(24)}</td>
</tr>
<tr>
<td><code>.../servo/sudut</code></td>
<td>subscribe</td>
<td><code>120</code> (derajat flap)</td>
<td>{$l::link(33)}</td>
</tr>
</tbody>
</table>
<p>Timestamp <code>unix: 1782977400</code> = <code>2026-07-02T14:30:00</code> UTC — konsisten dengan {$l::link(34, 'NTP (#34)')} di seluruh Seri 2.</p>
<p><strong>Catatan topic pompa:</strong> di {$l::link(35, '#35')}, relay pompa masih memakai <code>kodingindonesia/esp32/lampu/kontrol</code>. Di capstone ini kita pakai <code>.../pompa/kontrol</code> agar irigasi terpisah dari lampu koridor PIR (<code>.../lampu/kontrol</code>) — pola relay tetap sama seperti {$l::link(8, '#8')}.</p>

HTML;
  }

  private function perbandinganJalurSection(): string
  {
    $l = Seri2ArticleLinks::class;

    return <<<HTML
<h2>Perbandingan Jalur yang Digabung</h2>
<table>
<thead>
<tr><th>Jalur Seri 2</th><th>Artikel</th><th>Peran di Greenhouse</th></tr>
</thead>
<tbody>
<tr>
<td>A — Hardware</td>
<td>{$l::links([11, 13, 24, 35])}</td>
<td>Sensor + deep sleep + PIR</td>
</tr>
<tr>
<td>B — Infrastruktur</td>
<td>{$l::links([16, 17, 18, 19])}</td>
<td>Broker, TLS, histori, Grafana</td>
</tr>
<tr>
<td>C — Smart home</td>
<td>{$l::links([21, 23])}</td>
<td>HA entity + Node-RED otomasi</td>
</tr>
<tr>
<td>D — Jarak jauh</td>
<td>{$l::links([26, 28])}</td>
<td>LoRa node kebun luas (opsional)</td>
</tr>
<tr>
<td>E — Tooling</td>
<td>{$l::links([29, 31])}</td>
<td>PlatformIO monorepo + FreeRTOS</td>
</tr>
<tr>
<td>Tier 2</td>
<td>{$l::links([33, 37, 38])}</td>
<td>Servo flap, SD backup, HTTPS alert</td>
</tr>
</tbody>
</table>

HTML;
  }

  private function faqSection(): string
  {
    $l = Seri2ArticleLinks::class;

    return <<<HTML
<h2>FAQ — Pertanyaan Umum Greenhouse IoT</h2>

<h3>Apakah bisa pakai satu ESP32 untuk semua sensor dan relay?</h3>
<p>Ya untuk prototipe skala kecil — gateway utama di artikel ini sudah menggabungkan BME280 + soil + LDR. Untuk produksi, pisahkan node aktuator agar reset sensor tidak mematikan pompa secara tidak sengaja.</p>

<h3>Broker test.mosquitto.org cukup untuk greenhouse?</h3>
<p>Tidak untuk data produksi — pakai {$l::link(16, 'broker sendiri (#16)')}. Broker publik cocok hanya untuk uji coba 15 menit pertama.</p>

<h3>Soil moisture resistif vs capacitive?</h3>
<p>Capacitive lebih awet di tanah basah — sudah dibahas di {$l::link(35, '#35')}. Probe resistif murah tapi korosi dalam berminggu-minggu.</p>

<h3>Berapa lama baterai node deep sleep?</h3>
<p>Dengan 18650 3000 mAh dan wake tiap 15 menit, estimasi 2–4 minggu — tergantung durasi WiFi connect. Optimasi: kurangi payload JSON, matikan LED board.</p>

<h3>Apakah Seri 2 berakhir setelah artikel ini?</h3>
<p>Ya — 29/29 artikel inti selesai. Topik Fase 3 (MicroPython, GPRS, Zigbee) akan ditulis jika ada permintaan komunitas.</p>

HTML;
  }
}
