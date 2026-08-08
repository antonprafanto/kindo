<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article100Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-http-json';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach ([
            'fullstack-iot' => 'fullstack-iot',
            'iot' => 'iot',
            'esp32' => 'esp32',
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'            => $admin->id,
                'category_id'        => $iotCat->id,
                'title'              => 'HTTP & JSON bahasa manusia: URL, GET, status, kurung kurawal',
                'title_en'           => 'HTTP & JSON in plain language: URL, GET, status, curly braces',
                'excerpt'            => 'FS-30 / #100: paham pesan tersusun di internet. Browser dulu lihat JSON, lalu ESP32 HTTP GET — hasil di Serial Monitor 115200.',
                'excerpt_en'         => 'FS-30 / #100: understand structured messages on the internet. Browser first to see JSON, then ESP32 HTTP GET — result on Serial Monitor 115200.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'HTTP & JSON ESP32 bahasa manusia — Full Stack IoT #100',
                'seo_title_en'       => 'ESP32 HTTP & JSON in plain language — Full Stack IoT #100',
                'seo_description'    => 'HTTP GET, status 200/404/500, anatomi JSON. Browser dulu, lalu Arduino IDE + Serial. Modul FS-30 / #100.',
                'seo_description_en' => 'HTTP GET, status 200/404/500, JSON anatomy. Browser first, then Arduino IDE + Serial. Module FS-30 / #100.',
            ]
        );

        if ($article->published_at !== null || $article->status !== 'draft') {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id');
        $article->tags()->sync($tagIds);

        $srcWebp = public_path('images/fsiot/fs30-cover-http.webp');
        $srcJpg = public_path('images/fsiot/fs30-cover-http.jpg');
        if (is_file($srcWebp)) {
            $dest = 'articles/covers/fs30-cover-http.webp';
            \Illuminate\Support\Facades\Storage::disk('public')->put($dest, file_get_contents($srcWebp));
            $article->cover_image = $dest;
            $article->save();
        } elseif (is_file($srcJpg)) {
            $dest = 'articles/covers/fs30-cover-http.jpg';
            \Illuminate\Support\Facades\Storage::disk('public')->put($dest, file_get_contents($srcJpg));
            $article->cover_image = $dest;
            $article->save();
        }

        $this->command?->info('✓ Artikel #100 / FS-30 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function ideFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/fs11-ide-overview-cite.png" width="1280" height="720" alt="Arduino IDE 2 — Verify, Upload, dan Serial Monitor" loading="eager" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Arduino IDE 2</strong> — tempat menguji sintaks sketch hari ini (setelah kamu melihat JSON di browser). Urutan: buka IDE → isi SSID/sandi → <strong>Verify</strong> → <strong>Upload</strong> → <strong>Tools → Serial Monitor</strong> (Ctrl+Shift+M) baud <strong>115200</strong>. Board: <strong>ESP32 Dev Module</strong>.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:Ide-2-overview.png" rel="noopener noreferrer" target="_blank">Arduino IDE 2 overview</a> · Wikimedia Commons (CC BY-SA 3.0). Panduan: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Serial Monitor</a>. HTTP ESP32: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/http_client.html" rel="noopener noreferrer" target="_blank">Espressif — HTTPClient</a>.
  </figcaption>
</figure>
HTML;
    }

    private function ideFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/fs11-ide-overview-cite.png" width="1280" height="720" alt="Arduino IDE 2 — Verify, Upload, and Serial Monitor" loading="eager" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Arduino IDE 2</strong> — where you test today’s sketch syntax (after you viewed JSON in a browser). Order: open the IDE → put SSID/password → <strong>Verify</strong> → <strong>Upload</strong> → <strong>Tools → Serial Monitor</strong> (Ctrl+Shift+M) at baud <strong>115200</strong>. Board: <strong>ESP32 Dev Module</strong>.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:Ide-2-overview.png" rel="noopener noreferrer" target="_blank">Arduino IDE 2 overview</a> · Wikimedia Commons (CC BY-SA 3.0). Guide: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Serial Monitor</a>. ESP32 HTTP: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/http_client.html" rel="noopener noreferrer" target="_blank">Espressif — HTTPClient</a>.
  </figcaption>
</figure>
HTML;
    }

    private function toolsFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs30-tools-order.png" width="1400" height="720" alt="Urutan tools: browser, IDE, Upload, checklist" loading="eager" style="width:100%;height:auto;max-height:640px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Urutan meja kerja:</strong> buka browser (lihat JSON) → buka Arduino IDE → Upload sketch → centang checklist. Sumber gambar: diagram buatan Koding Indonesia (FS-30).
  </figcaption>
</figure>
HTML;
    }

    private function toolsFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs30-tools-order.png" width="1400" height="720" alt="Tool order: browser, IDE, Upload, checklist" loading="eager" style="width:100%;height:auto;max-height:640px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Desk order:</strong> open a browser (see JSON) → open Arduino IDE → Upload the sketch → tick the checklist. Image source: diagram by Koding Indonesia (FS-30).
  </figcaption>
</figure>
HTML;
    }

    private function coreFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs30-http-core.png" width="1200" height="560" alt="HTTPClient sudah di core ESP32 — tiga langkah" loading="eager" style="width:100%;height:auto;max-height:480px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Intinya:</strong> <code>#include &lt;HTTPClient.h&gt;</code> sudah di core board ESP32 — <strong>tidak perlu</strong> Library Manager / ArduinoJson hari ini. Sumber gambar: diagram Koding Indonesia (FS-30). API: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/http_client.html" rel="noopener noreferrer" target="_blank">Espressif HTTPClient</a>.
  </figcaption>
</figure>
HTML;
    }

    private function coreFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs30-http-core.png" width="1200" height="560" alt="HTTPClient is in the ESP32 core — three steps" loading="eager" style="width:100%;height:auto;max-height:480px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>In short:</strong> <code>#include &lt;HTTPClient.h&gt;</code> ships with the ESP32 board core — <strong>no</strong> Library Manager / ArduinoJson step today. Image source: diagram by Koding Indonesia (FS-30). API: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/http_client.html" rel="noopener noreferrer" target="_blank">Espressif HTTPClient</a>.
  </figcaption>
</figure>
HTML;
    }

    private function mainFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs30-http-get.png" width="1200" height="720" alt="Gambar utama — ESP32 HTTP GET lalu JSON di Serial" loading="eager" style="width:100%;height:auto;max-height:640px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Gambar utama — ESP32 minta data lewat HTTP GET.</strong> Mirip membuka URL di browser: ESP32 mengirim permintaan · server membalas status + isi · kita baca di <strong>Serial Monitor</strong> baud <strong>115200</strong>.
    <br>Sumber gambar: diagram Koding Indonesia (FS-30). Acuan: <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Overview" rel="noopener noreferrer" target="_blank">MDN — HTTP</a> · demo API: <a href="https://jsonplaceholder.typicode.com/" rel="noopener noreferrer" target="_blank">JSONPlaceholder</a>.
  </figcaption>
</figure>
HTML;
    }

    private function mainFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs30-http-get.png" width="1200" height="720" alt="Main figure — ESP32 HTTP GET then JSON on Serial" loading="eager" style="width:100%;height:auto;max-height:640px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Main figure — ESP32 asks for data with HTTP GET.</strong> Like opening a URL in a browser: the ESP32 sends a request · the server replies with a status + body · we read it on the <strong>Serial Monitor</strong> at baud <strong>115200</strong>.
    <br>Image source: diagram by Koding Indonesia (FS-30). Refs: <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Overview" rel="noopener noreferrer" target="_blank">MDN — HTTP</a> · demo API: <a href="https://jsonplaceholder.typicode.com/" rel="noopener noreferrer" target="_blank">JSONPlaceholder</a>.
  </figcaption>
</figure>
HTML;
    }

    private function schemaFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs30-json-anatomy.png" width="1200" height="640" alt="Skema bantu — anatomi JSON sederhana" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Skema bantu — anatomi JSON.</strong> Kurung kurawal <code>{ }</code> membungkus pasangan <strong>kunci: nilai</strong>. Teks pakai tanda kutip. Jangan takut — hari ini cukup mengenal bentuknya.
    <br>Sumber gambar: diagram Koding Indonesia (FS-30). Konsep: <a href="https://www.json.org/json-en.html" rel="noopener noreferrer" target="_blank">json.org</a> · <a href="https://developer.mozilla.org/en-US/docs/Learn/JavaScript/Objects/JSON" rel="noopener noreferrer" target="_blank">MDN — JSON</a>.
  </figcaption>
</figure>
HTML;
    }

    private function schemaFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs30-json-anatomy.png" width="1200" height="640" alt="Helper schematic — simple JSON anatomy" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Helper schematic — JSON anatomy.</strong> Curly braces <code>{ }</code> wrap <strong>key: value</strong> pairs. Text uses quotes. Don’t fear it — today just recognize the shape.
    <br>Image source: diagram by Koding Indonesia (FS-30). Concepts: <a href="https://www.json.org/json-en.html" rel="noopener noreferrer" target="_blank">json.org</a> · <a href="https://developer.mozilla.org/en-US/docs/Learn/JavaScript/Objects/JSON" rel="noopener noreferrer" target="_blank">MDN — JSON</a>.
  </figcaption>
</figure>
HTML;
    }

    private function statusFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs30-status-codes.png" width="1200" height="560" alt="Kode status HTTP 200, 404, 500" loading="eager" style="width:100%;height:auto;max-height:480px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Kode status.</strong> <strong>200</strong> = berhasil · <strong>404</strong> = alamat tidak ada · <strong>500</strong> = server bermasalah. Sumber gambar: diagram Koding Indonesia (FS-30). Acuan: <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Status" rel="noopener noreferrer" target="_blank">MDN — HTTP status</a>.
  </figcaption>
</figure>
HTML;
    }

    private function statusFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs30-status-codes.png" width="1200" height="560" alt="HTTP status codes 200, 404, 500" loading="eager" style="width:100%;height:auto;max-height:480px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Status codes.</strong> <strong>200</strong> = success · <strong>404</strong> = address missing · <strong>500</strong> = server problem. Image source: diagram by Koding Indonesia (FS-30). Ref: <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Status" rel="noopener noreferrer" target="_blank">MDN — HTTP status</a>.
  </figcaption>
</figure>
HTML;
    }

    private function successFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs30-success-serial.png" width="1200" height="520" alt="Sukses: Serial tampilkan 200 + JSON" loading="eager" style="width:100%;height:auto;max-height:480px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Intinya:</strong> sukses = Serial menampilkan <strong>HTTP 200</strong> dan teks berisi <code>{</code> … <code>}</code>. Sumber gambar: diagram Koding Indonesia (FS-30).
  </figcaption>
</figure>
HTML;
    }

    private function successFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs30-success-serial.png" width="1200" height="520" alt="Success: Serial shows 200 + JSON" loading="eager" style="width:100%;height:auto;max-height:480px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>In short:</strong> success = Serial prints <strong>HTTP 200</strong> and text containing <code>{</code> … <code>}</code>. Image source: diagram by Koding Indonesia (FS-30).
  </figcaption>
</figure>
HTML;
    }

    private function analogySvgId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <svg viewBox="0 0 720 200" width="100%" height="auto" role="img" aria-label="Analogi HTTP GET seperti pesan pos">
    <rect x="20" y="20" width="200" height="120" rx="12" fill="#FFF8E1" stroke="#F9A825" stroke-width="3"/>
    <text x="120" y="70" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#1a1a1a">ESP32 = pengirim</text>
    <text x="120" y="105" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" fill="#333">tulis alamat (URL)</text>
    <rect x="260" y="20" width="200" height="120" rx="12" fill="#E8EAF6" stroke="#3949AB" stroke-width="3"/>
    <text x="360" y="70" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#1a1a1a">Server = kantor</text>
    <text x="360" y="105" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" fill="#333">baca &amp; balas</text>
    <rect x="500" y="20" width="200" height="120" rx="12" fill="#C8E6C9" stroke="#2E7D32" stroke-width="3"/>
    <text x="600" y="70" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#1a1a1a">JSON = isi surat</text>
    <text x="600" y="105" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" fill="#333">rapi &amp; terbaca mesin</text>
    <text x="360" y="175" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" fill="#1a1a1a">Analogi: HTTP GET seperti meminta fotokopi dokumen dari kantor pos digital</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Intinya:</strong> URL = alamat · GET = “tolong kirimkan” · JSON = isi surat yang rapi. Sumber gambar: diagram Koding Indonesia (FS-30).
  </figcaption>
</figure>
HTML;
    }

    private function analogySvgEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <svg viewBox="0 0 720 200" width="100%" height="auto" role="img" aria-label="HTTP GET analogy like postal mail">
    <rect x="20" y="20" width="200" height="120" rx="12" fill="#FFF8E1" stroke="#F9A825" stroke-width="3"/>
    <text x="120" y="70" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#1a1a1a">ESP32 = sender</text>
    <text x="120" y="105" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" fill="#333">writes the address (URL)</text>
    <rect x="260" y="20" width="200" height="120" rx="12" fill="#E8EAF6" stroke="#3949AB" stroke-width="3"/>
    <text x="360" y="70" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#1a1a1a">Server = office</text>
    <text x="360" y="105" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" fill="#333">reads &amp; replies</text>
    <rect x="500" y="20" width="200" height="120" rx="12" fill="#C8E6C9" stroke="#2E7D32" stroke-width="3"/>
    <text x="600" y="70" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#1a1a1a">JSON = letter body</text>
    <text x="600" y="105" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" fill="#333">neat &amp; machine-readable</text>
    <text x="360" y="175" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" fill="#1a1a1a">Analogy: HTTP GET is like asking a digital post office for a photocopy</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>In short:</strong> URL = address · GET = “please send” · JSON = a neat letter body. Image source: diagram by Koding Indonesia (FS-30).
  </figcaption>
</figure>
HTML;
    }

    private function sketchCode(): string
    {
        return <<<'CPP'
#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <HTTPClient.h>

const char* WIFI_SSID = "YOUR_SSID";
const char* WIFI_PASS = "YOUR_PASS";

// Demo publik (JSON sederhana). Lab: setInsecure = belajar saja, bukan produksi.
const char* DEMO_URL = "https://jsonplaceholder.typicode.com/todos/1";

void setup() {
  Serial.begin(115200);
  delay(500);
  Serial.println("FS30_http_get ready");

  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);
  Serial.print("Menghubungkan Wi-Fi");
  unsigned long t0 = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - t0 < 20000UL) {
    delay(400);
    Serial.print(".");
  }
  Serial.println();
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("Wi-Fi gagal. Selesaikan FS-29 dulu.");
    return;
  }
  Serial.print("Wi-Fi OK, IP: ");
  Serial.println(WiFi.localIP());

  WiFiClientSecure client;
  client.setInsecure(); // lab belajar — verifikasi sertifikat dibahas belakangan

  HTTPClient http;
  Serial.print("GET ");
  Serial.println(DEMO_URL);
  if (! http.begin(client, DEMO_URL)) {
    Serial.println("http.begin gagal");
    return;
  }

  int code = http.GET();
  Serial.print("HTTP ");
  Serial.println(code);
  if (code > 0) {
    String payload = http.getString();
    Serial.println(payload);
  } else {
    Serial.println("Permintaan gagal (cek Wi-Fi / URL / SSL).");
  }
  http.end();
}

void loop() {
  // sekali di setup cukup untuk FS-30
}
CPP;
    }

    private function body(): string
    {
        $ide = $this->ideFigureId();
        $tools = $this->toolsFigureId();
        $core = $this->coreFigureId();
        $main = $this->mainFigureId();
        $schema = $this->schemaFigureId();
        $status = $this->statusFigureId();
        $success = $this->successFigureId();
        $analogy = $this->analogySvgId();
        $code = $this->sketchCode();

        return <<<HTML
<h2>Pendahuluan — pesan tersusun di internet</h2>
<p>Artikel ini adalah <strong>#100 (ini)</strong> · modul <strong>FS-30</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em> (fase <strong>CONNECTED</strong>). Setelah FS-29 (ESP32 punya IP Wi-Fi), hari ini kita paham cara <strong>meminta data</strong> lewat internet: <strong>HTTP</strong> dan bentuk balasan <strong>JSON</strong>.</p>
<p><strong>Analogi:</strong> URL = alamat kantor · GET = “tolong kirimkan salinan” · JSON = isi surat yang rapi · status 200 = “sudah dikirim”.</p>
{$analogy}
<p><strong>Glosarium singkat:</strong></p>
<ul>
<li><strong>URL</strong> = alamat lengkap di internet (contoh <code>https://…/todos/1</code>).</li>
<li><strong>HTTP GET</strong> = meminta data (bukan mengirim formulir panjang).</li>
<li><strong>JSON</strong> = catatan berpasangan <em>nama: nilai</em> di dalam <code>{ }</code>.</li>
<li><strong>Status 200 / 404 / 500</strong> = berhasil / alamat tidak ada / server bermasalah.</li>
<li><strong>Header</strong> = keterangan tambahan di “amplop” (hari ini cukup tahu ada; belum wajib diutak-atik).</li>
</ul>
<p><strong>Prasyarat:</strong> FS-29 (Wi-Fi + IP di Serial) · FS-14 (Upload + Serial Monitor).</p>
<p><strong>Cara pakai artikel ini (urutan kerja):</strong></p>
<ol>
<li><strong>Buka browser dulu</strong> — buka URL demo JSON di bawah (bukan Laragon).</li>
<li>Kenali bentuk <code>{ }</code> dan pasangan kunci:nilai.</li>
<li><strong>Buka Arduino IDE</strong> → sketch <code>FS30_http_get</code> → isi SSID/sandi → <strong>Verify</strong> → <strong>Upload</strong>.</li>
<li>Buka <strong>Tools → Serial Monitor</strong> (Ctrl+Shift+M) baud <strong>115200</strong> — cari <code>HTTP 200</code> + teks JSON.</li>
<li>Centang checklist 10/10 di browser artikel.</li>
</ol>
<p><strong>Tidak perlu hari ini:</strong> Library Manager ekstra, ArduinoJson, MQTT, Flask, Laragon, <code>php artisan</code>, web server di ESP32. Alat hari ini: <strong>browser</strong> + <strong>Arduino IDE</strong> + <strong>Upload</strong> + ESP32 + Wi-Fi + <strong>Serial Monitor</strong>.</p>

<h2>Persiapan — buka &amp; siapkan ini dulu</h2>
<p><strong>Urutan meja kerja:</strong> browser (lihat JSON) → IDE → Upload → Serial Monitor → checklist.</p>
{$tools}
<p><strong>Latihan mata (wajib sebelum kode):</strong> buka di browser (Chrome/Edge/Firefox):</p>
<p><a href="https://jsonplaceholder.typicode.com/todos/1" rel="noopener noreferrer" target="_blank">https://jsonplaceholder.typicode.com/todos/1</a></p>
<p>Kamu harus melihat teks berisi <code>{</code>, <code>"id"</code>, dan <code>}</code>. Itu JSON. Sumber demo: <a href="https://jsonplaceholder.typicode.com/" rel="noopener noreferrer" target="_blank">JSONPlaceholder</a> (API uji publik).</p>
{$ide}
{$core}
<ul>
<li>ESP32 sudah punya IP (lulus FS-29) · board <strong>ESP32 Dev Module</strong> · kabel USB <strong>data</strong>.</li>
<li><code>WiFi.h</code> + <code>HTTPClient.h</code> sudah di core — tidak perlu Library Manager.</li>
<li>Siapkan Serial Monitor baud <strong>115200</strong>.</li>
</ul>
<p><strong>Alat yang dipakai hari ini:</strong> browser, Arduino IDE, Upload, ESP32, Wi-Fi, Serial Monitor.</p>
<p><strong>Tidak dipakai hari ini:</strong> Laragon, <code>php artisan</code>, ArduinoJson, MQTT, microSD.</p>

<h2>HTTP &amp; JSON (bahasa manusia)</h2>
{$main}
{$schema}
{$status}
<p><strong>Blok konsep:</strong></p>
<ul>
<li><strong>GET</strong> = “tolong kirimkan data ini” · <strong>POST</strong> = “tolong terima data yang saya kirim” (POST dibahas lebih dalam belakangan; hari ini fokus GET).</li>
<li>ESP32 memanggil URL → server menjawab <strong>kode status</strong> + <strong>isi</strong> (sering JSON).</li>
<li>Hari ini kita <strong>mencetak teks mentah</strong> ke Serial — belum wajib “mengurai” JSON dengan library.</li>
</ul>
<p><strong>Intinya:</strong> sukses hari ini = <strong>HTTP 200 + teks JSON di Serial Monitor</strong>, sama bentuknya dengan yang kamu lihat di browser.</p>

<h2>Praktik — sketch FS30_http_get</h2>
<p>Tujuan: ESP32 terhubung Wi-Fi, melakukan <strong>HTTP GET</strong> ke URL demo, mencetak kode status + isi JSON ke Serial Monitor.</p>
<ol>
<li><strong>Buka browser</strong> dulu — pastikan URL demo masih menampilkan JSON.</li>
<li><strong>Buka Arduino IDE</strong> (bukan Laragon).</li>
<li><strong>File → New Sketch</strong> → <strong>Simpan sebagai</strong> <code>FS30_http_get</code>.</li>
<li>Ganti isi dengan kode di bawah — ganti <code>YOUR_SSID</code> / <code>YOUR_PASS</code>.</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → tunggu <em>Done uploading</em>.</li>
<li>Buka <strong>Tools → Serial Monitor</strong> (Ctrl+Shift+M) baud <strong>115200</strong>.</li>
</ol>
<pre><code class="language-cpp">{$code}</code></pre>
<p><strong>Cara menguji perintah di atas:</strong> uji di <strong>Arduino IDE + Upload + Serial Monitor</strong>. Bukan perintah Laragon / terminal web. Sukses jika muncul <code>HTTP 200</code> dan teks dengan <code>{</code>…<code>}</code>. Catatan lab: <code>setInsecure()</code> dipakai agar belajar HTTPS tidak macet di sertifikat — untuk produksi nanti diperketat.</p>
{$success}

<h2 id="fsiot-http-checklist">Praktik — checklist HTTP &amp; JSON</h2>
<p>Centang setiap langkah setelah kamu lakukan di meja. Target: <strong>10/10</strong>.</p>
<ul id="fsiot-http-checklist-items">
<li>Browser sudah dibuka — URL demo menampilkan JSON</li>
<li>Paham: JSON memakai { } dan pasangan kunci:nilai</li>
<li>Paham singkat: 200 / 404 / 500</li>
<li>Arduino IDE terbuka sebelum menulis kode</li>
<li>Wi-Fi masih jalan (prasyarat FS-29)</li>
<li>Sketch disimpan sebagai FS30_http_get</li>
<li>YOUR_SSID / YOUR_PASS sudah diganti</li>
<li>Upload berhasil — Done uploading</li>
<li>Serial Monitor 115200 menampilkan HTTP 200 + JSON</li>
<li>Sadar: ini bekal FS-31 (web server lokal) saat terbit</li>
</ul>
<p><strong>Cara menguji checklist:</strong> centang di browser setelah praktik di browser + IDE + board. Tidak perlu <code>php artisan</code>.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Takut JSON / salah tanda kutip.</strong> Lihat dulu di browser; di Serial cukup pastikan ada <code>{</code> dan <code>}</code>.</li>
<li><strong>Wi-Fi belum siap.</strong> Kembali ke FS-29 sampai IP muncul.</li>
<li><strong>Menguji di Laragon.</strong> Sketch hanya jalan di board lewat IDE Upload.</li>
<li><strong>Baud salah.</strong> Pakai 115200 di Serial Monitor.</li>
<li><strong>HTTP -1 / gagal SSL.</strong> Pastikan URL HTTPS benar; sketch lab memakai <code>setInsecure()</code>.</li>
<li><strong>404.</strong> Cek ejaan URL demo.</li>
<li><strong>Mengira harus pasang ArduinoJson hari ini.</strong> Belum — cetak teks mentah sudah cukup.</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Intinya:</strong> kalau Serial menampilkan <strong>200 + JSON</strong>, FS-30 selesai — kamu sudah “bicara” HTTP seperti browser.</p>
<p>Langkah berikutnya di fase <strong>CONNECTED</strong> adalah <strong>FS-31</strong> (ESP32 web server lokal: pantau sensor di browser) saat modulnya terbit. Soft bridge saja — belum hardlink artikel.</p>
<p>Daftar modul lengkap: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $ide = $this->ideFigureEn();
        $tools = $this->toolsFigureEn();
        $core = $this->coreFigureEn();
        $main = $this->mainFigureEn();
        $schema = $this->schemaFigureEn();
        $status = $this->statusFigureEn();
        $success = $this->successFigureEn();
        $analogy = $this->analogySvgEn();
        $code = $this->sketchCode();

        return <<<HTML
<h2>Introduction — structured messages on the internet</h2>
<p>This is <strong>#100 (this article)</strong> · module <strong>FS-30</strong> on the <em>Full Stack IoT Developer — From Zero</em> track (phase <strong>CONNECTED</strong>). After FS-29 (ESP32 has a Wi-Fi IP), today we learn how to <strong>request data</strong> over the internet: <strong>HTTP</strong> and the reply shape called <strong>JSON</strong>.</p>
<p><strong>Analogy:</strong> URL = office address · GET = “please send a copy” · JSON = a neat letter body · status 200 = “sent successfully”.</p>
{$analogy}
<p><strong>Short glossary:</strong></p>
<ul>
<li><strong>URL</strong> = full internet address (e.g. <code>https://…/todos/1</code>).</li>
<li><strong>HTTP GET</strong> = ask for data (not sending a long form).</li>
<li><strong>JSON</strong> = a note of <em>name: value</em> pairs inside <code>{ }</code>.</li>
<li><strong>Status 200 / 404 / 500</strong> = success / missing address / server problem.</li>
<li><strong>Header</strong> = extra notes on the “envelope” (enough to know it exists today).</li>
</ul>
<p><strong>Prerequisites:</strong> FS-29 (Wi-Fi + IP on Serial) · FS-14 (Upload + Serial Monitor).</p>
<p><strong>How to use this article (work order):</strong></p>
<ol>
<li><strong>Open a browser first</strong> — open the demo JSON URL below (not Laragon).</li>
<li>Recognize <code>{ }</code> and key:value pairs.</li>
<li><strong>Open Arduino IDE</strong> → sketch <code>FS30_http_get</code> → fill SSID/password → <strong>Verify</strong> → <strong>Upload</strong>.</li>
<li>Open <strong>Tools → Serial Monitor</strong> (Ctrl+Shift+M) at baud <strong>115200</strong> — look for <code>HTTP 200</code> + JSON text.</li>
<li>Tick the 10/10 checklist in the article browser.</li>
</ol>
<p><strong>Not needed today:</strong> extra Library Manager, ArduinoJson, MQTT, Flask, Laragon, <code>php artisan</code>, an ESP32 web server. Today's tools: <strong>browser</strong> + <strong>Arduino IDE</strong> + <strong>Upload</strong> + ESP32 + Wi-Fi + <strong>Serial Monitor</strong>.</p>

<h2>Preparation — open &amp; prepare these first</h2>
<p><strong>Desk order:</strong> browser (see JSON) → IDE → Upload → Serial Monitor → checklist.</p>
{$tools}
<p><strong>Eye practice (required before code):</strong> open in a browser (Chrome/Edge/Firefox):</p>
<p><a href="https://jsonplaceholder.typicode.com/todos/1" rel="noopener noreferrer" target="_blank">https://jsonplaceholder.typicode.com/todos/1</a></p>
<p>You should see text with <code>{</code>, <code>"id"</code>, and <code>}</code>. That is JSON. Demo source: <a href="https://jsonplaceholder.typicode.com/" rel="noopener noreferrer" target="_blank">JSONPlaceholder</a> (public test API).</p>
{$ide}
{$core}
<ul>
<li>ESP32 already has an IP (FS-29 done) · board <strong>ESP32 Dev Module</strong> · <strong>data</strong> USB cable.</li>
<li><code>WiFi.h</code> + <code>HTTPClient.h</code> are in the core — no Library Manager.</li>
<li>Prepare Serial Monitor at baud <strong>115200</strong>.</li>
</ul>
<p><strong>Tools used today:</strong> browser, Arduino IDE, Upload, ESP32, Wi-Fi, Serial Monitor.</p>
<p><strong>Not used today:</strong> Laragon, <code>php artisan</code>, ArduinoJson, MQTT, microSD.</p>

<h2>HTTP &amp; JSON (plain language)</h2>
{$main}
{$schema}
{$status}
<p><strong>Concept block:</strong></p>
<ul>
<li><strong>GET</strong> = “please send this data” · <strong>POST</strong> = “please accept the data I send” (POST later; today focus on GET).</li>
<li>The ESP32 calls a URL → the server answers with a <strong>status code</strong> + <strong>body</strong> (often JSON).</li>
<li>Today we <strong>print raw text</strong> to Serial — no need to “parse” JSON with a library yet.</li>
</ul>
<p><strong>In short:</strong> success today = <strong>HTTP 200 + JSON text on Serial Monitor</strong>, same shape you saw in the browser.</p>

<h2>Practice — sketch FS30_http_get</h2>
<p>Goal: ESP32 joins Wi-Fi, performs an <strong>HTTP GET</strong> to the demo URL, prints the status code + JSON body to Serial Monitor.</p>
<ol>
<li><strong>Open a browser</strong> first — confirm the demo URL still shows JSON.</li>
<li><strong>Open Arduino IDE</strong> (not Laragon).</li>
<li><strong>File → New Sketch</strong> → <strong>Save as</strong> <code>FS30_http_get</code>.</li>
<li>Replace the contents with the code below — change <code>YOUR_SSID</code> / <code>YOUR_PASS</code>.</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → wait for <em>Done uploading</em>.</li>
<li>Open <strong>Tools → Serial Monitor</strong> (Ctrl+Shift+M) at baud <strong>115200</strong>.</li>
</ol>
<pre><code class="language-cpp">{$code}</code></pre>
<p><strong>How to test the commands above:</strong> test in <strong>Arduino IDE + Upload + Serial Monitor</strong>. Not Laragon / a web terminal. Success = <code>HTTP 200</code> and text with <code>{</code>…<code>}</code>. Lab note: <code>setInsecure()</code> keeps HTTPS learning from stalling on certificates — tighten this later for production.</p>
{$success}

<h2 id="fsiot-http-checklist">Practice — HTTP &amp; JSON checklist</h2>
<p>Tick each step after you do it at the desk. Target: <strong>10/10</strong>.</p>
<ul id="fsiot-http-checklist-items">
<li>Browser opened — demo URL shows JSON</li>
<li>I know: JSON uses { } and key:value pairs</li>
<li>I know briefly: 200 / 404 / 500</li>
<li>Arduino IDE was open before writing code</li>
<li>Wi-Fi still works (FS-29 prerequisite)</li>
<li>Sketch saved as FS30_http_get</li>
<li>YOUR_SSID / YOUR_PASS replaced</li>
<li>Upload succeeded — Done uploading</li>
<li>Serial Monitor 115200 shows HTTP 200 + JSON</li>
<li>I know: this prepares FS-31 (local web server) when it ships</li>
</ul>
<p><strong>How to test the checklist:</strong> tick in the browser after practice in the browser + IDE + board. No <code>php artisan</code>.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Fear of JSON / wrong quotes.</strong> Look in the browser first; on Serial just confirm <code>{</code> and <code>}</code>.</li>
<li><strong>Wi-Fi not ready.</strong> Return to FS-29 until an IP appears.</li>
<li><strong>Testing in Laragon.</strong> The sketch only runs on the board via IDE Upload.</li>
<li><strong>Wrong baud.</strong> Use 115200 on Serial Monitor.</li>
<li><strong>HTTP -1 / SSL fail.</strong> Check the HTTPS URL; the lab sketch uses <code>setInsecure()</code>.</li>
<li><strong>404.</strong> Check the demo URL spelling.</li>
<li><strong>Thinking ArduinoJson is required today.</strong> Not yet — printing raw text is enough.</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> if Serial shows <strong>200 + JSON</strong>, FS-30 is done — you can “speak” HTTP like a browser.</p>
<p>Next in <strong>CONNECTED</strong> is <strong>FS-31</strong> (ESP32 local web server: watch a sensor in the browser) when that module ships. Soft bridge only — no hard article link yet.</p>
<p>Full module list: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }
}
