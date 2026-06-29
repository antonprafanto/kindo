<?php

/**
 * Audit artikel #16 — Broker Mosquitto Pribadi + Autentikasi ESP32.
 * Usage: php scripts/audit-article16.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article16Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article16Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);
    $seeder = $ref->newInstanceWithoutConstructor();

    return $method->invoke($seeder);
}

echo "=== Audit Artikel #16 — Pass 1: Seeder & DB ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article16Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article12Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article7Seeder', '--force' => true]);

$article = Article::where('slug', $slug)->first();
$body = seederBody();

check($article !== null, 'Artikel ada di database setelah seed');
check($article?->status === 'published', 'Status published');
check($article?->published_at !== null, 'published_at terisi');
check($article?->category?->slug === 'networking', 'Kategori networking');
check($article?->is_featured === false, 'is_featured false');
check($article?->cover_image === null || $article->cover_image !== '', 'cover_image tidak di-wipe seeder');

$requiredTags = ['mqtt', 'mosquitto', 'iot', 'esp32', 'networking', 'linux'];
$articleTags = $article?->tags->pluck('slug')->all() ?? [];
foreach ($requiredTags as $tag) {
    check(in_array($tag, $articleTags, true), "Tag: {$tag}");
}

$requiredLinks = [
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                  => 'Artikel #7 MQTT',
    'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode' => 'Artikel #12 NVS/WiFiManager',
    'deep-sleep-esp32-sensor-dht22-hemat-baterai'                  => 'Artikel #11 deep sleep',
    'membaca-sensor-dht22-suhu-kelembaban-esp32'                   => 'Artikel #5 DHT22',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/' . $linkSlug), "Link internal: {$label}");
    check(Article::where('slug', $linkSlug)->exists(), "Target exists: {$linkSlug}");
}

check(str_contains($body, 'Jalur B'), 'Menyebut Jalur B infrastruktur');
check(str_contains($body, 'janji'), 'Link janji broker dari artikel #7');
check(
    str_contains($body, 'memahami-mqtt-esp32-kirim-data-sensor-broker') && str_contains($body, 'janji'),
    'Paragraf janji MQTT #7 eksplisit'
);
check(str_contains($body, 'pindah dari'), 'Referensi migrasi dari test.mosquitto (#12)');
check(str_contains($body, 'Yang Kamu Butuhkan'), 'Section daftar kebutuhan');
check(str_contains($body, 'MQTT Explorer'), 'Menyebut MQTT Explorer');
check(str_contains($body, '#11–#15'), 'Aturan fase broker #11–#15 vs #16+');
check(str_contains($body, 'test.mosquitto.org'), 'Membandingkan broker publik');
check(str_contains($body, 'per_listener_settings'), 'Config: per_listener_settings');
check(str_contains($body, 'allow_anonymous false'), 'Config: allow_anonymous false');
check(str_contains($body, 'mosquitto_passwd'), 'Perintah mosquitto_passwd');
check(str_contains($body, 'password_file'), 'Config: password_file');
check(str_contains($body, 'conf.d'), 'Troubleshooting konflik conf.d Mosquitto 2.x');
check(str_contains($body, 'systemctl'), 'Perintah systemctl');
check(str_contains($body, 'mosquitto_sub'), 'Uji coba mosquitto_sub');
check(str_contains($body, 'mosquitto_pub'), 'Uji coba mosquitto_pub');
check(str_contains($body, 'Broker bukan website'), 'Peringatan broker bukan website');
check(str_contains($body, 'kodingindonesia/esp32/dht22/data'), 'MQTT topic sensor konsisten');
check(str_contains($body, '{"suhu"'), 'Contoh payload JSON');
check(str_contains($body, 'Uji Coba ESP32'), 'Section uji coba ESP32 end-to-end');
check(str_contains($body, 'KindoESP32-Setup'), 'AP portal KindoESP32-Setup');
check(str_contains($body, 'setConfigPortalTimeout'), 'Kode: setConfigPortalTimeout');
check(str_contains($body, 'WiFiManagerParameter'), 'ESP32: WiFiManagerParameter MQTT');
check(str_contains($body, 'prefs.getString'), 'Kode: prefs.getString');
check(str_contains($body, 'prefs.putString'), 'Kode: prefs.putString');
check(str_contains($body, 'autoConnect'), 'Kode: wm.autoConnect');
check(str_contains($body, 'mqttClient.connect'), 'ESP32: mqttClient.connect dengan auth');
check(str_contains($body, 'mqtt_host'), 'NVS key mqtt_host');
check(str_contains($body, 'mqtt_user'), 'NVS key mqtt_user');
check(str_contains($body, 'mqtt_pass'), 'NVS key mqtt_pass');
check(str_contains($body, 'mqttClient.loop()'), 'mqttClient.loop() sebelum publish');
check(str_contains($body, 'setBufferSize(512)'), 'PubSubClient setBufferSize(512)');
check(str_contains($body, 'Publish OK'), 'Serial log Publish OK');
check(str_contains($body, 'dht.begin()'), 'DHT begin');
check(
    preg_match('/dht\.begin\(\)[\s\S]{0,120}delay\(2000\)/', $body),
    'delay(2000) setelah dht.begin() di kode'
);
check(str_contains($body, '#define DHT_PIN  4'), 'DHT GPIO 4');
check(str_contains($body, 'tzapu'), 'Library WiFiManager (tzapu)');
check(str_contains($body, "Nick O'Leary"), 'Library PubSubClient disebut');
check(str_contains($body, 'Raspberry Pi'), 'Opsi Raspberry Pi');
check(str_contains($body, 'VPS'), 'Opsi VPS');
check(str_contains($body, 'ufw'), 'Firewall UFW disebut');
check(str_contains($body, '2.4 GHz'), 'Peringatan WiFi 2.4 GHz');
check(str_contains($body, 'rc=5'), 'Troubleshooting MQTT rc=5 (auth)');
check(str_contains($body, 'Pro tip'), 'Pro tip (user atau topic)');
check(str_contains($body, 'Seri 2'), 'Menyebut Seri 2');
check(str_contains($body, 'artikel #17'), 'Teaser artikel #17 TLS');
check(str_contains($body, 'Subscriber Python'), 'Teaser artikel #18 Python');
check(str_contains($body, 'Home Assistant'), 'Teaser artikel #21 Home Assistant');
check(str_contains($body, 'BME280'), 'Teaser artikel #13 BME280');
check(str_contains($body, '8883'), 'Menyebut port TLS 8883 (teaser #17)');
check(! preg_match('/const char\*\s+mqttServer\s*=\s*"test\.mosquitto/', $body), 'ESP32 tidak hardcode test.mosquitto');
check(str_contains($body, 'language-bash'), 'Blok kode bash');
check(str_contains($body, 'language-arduino'), 'Blok kode Arduino');
check(str_contains($body, '<table>'), 'Ada tabel');
check(str_contains($body, 'rel="noopener"') || ! str_contains($body, 'target="_blank"'), 'Link eksternal aman');
check(! str_contains($body, 'shared hosting'), 'Tidak ada typo shared hosting');

$seoLen = mb_strlen($article?->seo_description ?? '');
check($seoLen >= 80 && $seoLen <= 160, "seo_description panjang OK ({$seoLen} char)");
check(mb_strlen($article?->seo_title ?? '') <= 70, 'seo_title ≤ 70 char');

$h2Count = preg_match_all('/<h2>/', $body);
check($h2Count >= 11, "Minimal 11 section H2 (ada {$h2Count})");
check($article?->read_time_minutes >= 7, 'read_time_minutes ≥ 7 menit');

echo "\n=== Pass 2: HTTP render lokal ===\n\n";

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/artikel/' . $slug, 'GET');
$response = $kernel->handle($request);
$html = (string) $response->getContent();

check($response->getStatusCode() === 200, 'GET artikel → 200');
check(str_contains($html, 'Mosquitto'), 'Judul/konten Mosquitto ter-render');
check(str_contains($html, 'application/ld+json'), 'JSON-LD schema ada');
check(str_contains($html, 'og:title'), 'OG meta ada');
check(str_contains($html, 'allow_anonymous'), 'Config allow_anonymous ter-render');
check(str_contains($html, 'KindoESP32-Setup'), 'Portal AP ter-render');
$kernel->terminate($request, $response);

echo "\n=== Pass 3: Konsistensi Seri 2 ===\n\n";

$a12 = Article::where('slug', 'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode')->first();
check($a12 !== null, 'Artikel #12 ada');
check(
    str_contains($a12?->body ?? '', 'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32'),
    'Artikel #12 backlink → artikel #16'
);

$a7 = Article::where('slug', 'memahami-mqtt-esp32-kirim-data-sensor-broker')->first();
check($a7 !== null, 'Artikel #7 ada');
check(
    str_contains($a7?->body ?? '', 'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32'),
    'Artikel #7 forward link → artikel #16'
);

echo "\n=== Post-deploy (manual) ===\n";
echo "○ Upload cover image via Filament\n";

if ($checkProduction) {
    echo "\n=== Pass 4: Production ===\n\n";
    $prodUrl = 'https://kodingindonesia.com/artikel/' . $slug;
    $code = trim((string) shell_exec('curl -sS --max-time 30 -o NUL -w "%{http_code}" ' . escapeshellarg($prodUrl)));
    check($code === '200', "Production HTTP {$code}");
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
