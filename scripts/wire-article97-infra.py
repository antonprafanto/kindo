# -*- coding: utf-8 -*-
"""Wire FS-27 / #97 infrastructure: route, DeployController, lang, blade, deploy.yml."""
from pathlib import Path

ROOT = Path(".")

# --- route ---
p = ROOT / "routes/web.php"
t = p.read_text(encoding="utf-8")
if "seed-article-97-draft" not in t:
    old = """Route::get('/deploy/seed-article-96-draft', [DeployController::class, 'seedArticle96Draft'])
    ->middleware('throttle:120,1')
    ->name('deploy.seed-article-96-draft');"""
    new = old + """

Route::get('/deploy/seed-article-97-draft', [DeployController::class, 'seedArticle97Draft'])
    ->middleware('throttle:120,1')
    ->name('deploy.seed-article-97-draft');"""
    if old not in t:
        raise SystemExit("route anchor missing")
    p.write_text(t.replace(old, new, 1), encoding="utf-8")
    print("route ok")
else:
    print("route exists")

# --- DeployController ---
p = ROOT / "app/Http/Controllers/DeployController.php"
t = p.read_text(encoding="utf-8")
if "seedArticle97Draft" not in t:
    method = r'''
    public function seedArticle97Draft(): Response
    {
        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article97Seeder',
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response('Article 97 draft seed failed: '.$e->getMessage(), 500);
        }

        $slug = 'fullstack-iot-bus-uart-i2c-spi';
        $article = Article::where('slug', $slug)->first();
        if (! $article) {
            report(new \RuntimeException('Article 97 missing after draft seed.'));

            return response('Article 97 not found after draft seed', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 97 refused to stay draft after seed.'));

            return response('Article 97 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 97 unexpectedly visible via published() scope.'));

            return response('Article 97 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#97 (ini)',
            'FS-27',
            'BUILDER',
            'Tidak perlu hari ini',
            'Cara pakai artikel ini',
            'fsiot-bus-checklist',
            'FS-17',
            'FS-14',
            'FS-28',
            '/belajar/fullstack-iot',
            'Analogi:',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Cara menguji pemahaman di atas',
            'UART',
            'I2C',
            'SPI',
            'fs27-bus-compare.png',
            'Gambar utama',
            'Buka artikel ini di browser',
            'GPIO 21',
            'GPIO 22',
            'BME280',
            'OLED',
            'microSD',
            'kit-i2c-bus.png',
            'kit-spi-bus.png',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 97 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 97 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 97 English fields are incomplete after draft seed.'));

            return response('Article 97 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#97 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'BUILDER',
            'fsiot-bus-checklist',
            'FS-28',
            'Common mistakes',
            'How to test the understanding above',
            'Main figure',
            'fs27-bus-compare.png',
            'Open this article in the browser',
            'UART',
            'I2C',
            'SPI',
            'GPIO 21',
            'GPIO 22',
            'kit-i2c-bus.png',
            'kit-spi-bus.png',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 97 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 97 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 97 seeded as draft (pre-launch B)', 200);
    }

'''
    marker = "        return response('Article 96 seeded as draft (pre-launch B)', 200);\n    }"
    if marker not in t:
        raise SystemExit("DeployController insert marker missing")
    t = t.replace(marker, marker + "\n" + method, 1)
    p.write_text(t, encoding="utf-8")
    print("deploy controller ok")
else:
    print("deploy controller exists")

# --- lang ID ---
p = ROOT / "lang/id/ui.php"
t = p.read_text(encoding="utf-8")
if "fsiot_bus_badge" not in t:
    needle = "'fsiot_servo_todo' => 'Belum',"
    if needle not in t:
        raise SystemExit("lang id servo_todo missing")
    block = needle + """
        'fsiot_bus_badge' => 'Checklist bus',
        'fsiot_bus_hint' => 'Centang tiap keputusan UART/I2C/SPI + OLED/BME280/microSD. Target: 10/10.',
        'fsiot_bus_check' => 'Cek kelengkapan',
        'fsiot_bus_retry' => 'Kosongkan lagi',
        'fsiot_bus_paper' => 'Versi catatan (kertas / tanpa klik)',
        'fsiot_bus_progress' => ':filled dari :total tercentang',
        'fsiot_bus_pass' => 'Lengkap :filled/:total — kamu bisa pilih bus! Lanjut FS-28 (I2C) saat modulnya terbit.',
        'fsiot_bus_incomplete' => 'Masih ada langkah yang belum dicentang. Cek analogi UART/I2C/SPI atau tabel keputusan lagi.',
        'fsiot_bus_done' => 'Selesai',
        'fsiot_bus_todo' => 'Belum',
"""
    t = t.replace(needle, block, 1)
    p.write_text(t, encoding="utf-8")
    print("lang id ok")
else:
    print("lang id exists")

# --- lang EN ---
p = ROOT / "lang/en/ui.php"
t = p.read_text(encoding="utf-8")
if "fsiot_bus_badge" not in t:
    needle = "'fsiot_servo_todo' => 'Not yet',"
    if needle not in t:
        # try find actual
        idx = t.find("fsiot_servo_todo")
        raise SystemExit(f"lang en servo_todo missing near {t[idx:idx+80]!r}")
    block = needle + """
        'fsiot_bus_badge' => 'Bus checklist',
        'fsiot_bus_hint' => 'Tick each UART/I2C/SPI decision + OLED/BME280/microSD. Target: 10/10.',
        'fsiot_bus_check' => 'Check completeness',
        'fsiot_bus_retry' => 'Clear again',
        'fsiot_bus_paper' => 'Paper version (no clicks)',
        'fsiot_bus_progress' => ':filled of :total checked',
        'fsiot_bus_pass' => 'Complete :filled/:total — you can choose a bus! Continue to FS-28 (I2C) when that module ships.',
        'fsiot_bus_incomplete' => 'Some steps are still unchecked. Recheck UART/I2C/SPI analogies or the decision table.',
        'fsiot_bus_done' => 'Done',
        'fsiot_bus_todo' => 'Not yet',
"""
    t = t.replace(needle, block, 1)
    p.write_text(t, encoding="utf-8")
    print("lang en ok")
else:
    print("lang en exists")

# --- blade ---
p = ROOT / "resources/views/articles/show.blade.php"
t = p.read_text(encoding="utf-8")
if "initFsiotBusChecklist" not in t:
    if "initFsiotServoChecklist();" not in t:
        raise SystemExit("blade call site missing")
    t = t.replace("initFsiotServoChecklist();", "initFsiotServoChecklist();\n    initFsiotBusChecklist();", 1)
    fn = """
function initFsiotBusChecklist() {
    initFsiotChecklistWidget({
        h2Id: 'fsiot-bus-checklist',
        listId: 'fsiot-bus-checklist-items',
        storagePrefix: 'fsiot-cl-97',
        idPrefix: 'fsiot-bus',
        minItems: 10,
        labels: {
            badge: @js(__('ui.articles.fsiot_bus_badge')),
            hint: @js(__('ui.articles.fsiot_bus_hint')),
            check: @js(__('ui.articles.fsiot_bus_check')),
            retry: @js(__('ui.articles.fsiot_bus_retry')),
            paper: @js(__('ui.articles.fsiot_bus_paper')),
            progress: @js(__('ui.articles.fsiot_bus_progress')),
            pass: @js(__('ui.articles.fsiot_bus_pass')),
            incomplete: @js(__('ui.articles.fsiot_bus_incomplete')),
            done: @js(__('ui.articles.fsiot_bus_done')),
            todo: @js(__('ui.articles.fsiot_bus_todo')),
        },
    });
}

"""
    if "function initFsiotServoChecklist()" not in t:
        raise SystemExit("blade servo fn missing")
    t = t.replace("function initFsiotServoChecklist()", fn + "function initFsiotServoChecklist()", 1)
    p.write_text(t, encoding="utf-8")
    print("blade ok")
else:
    print("blade exists")

# --- deploy.yml FTP ---
p = ROOT / ".github/workflows/deploy.yml"
t = p.read_text(encoding="utf-8")
assets = "images/fsiot/fs27-cover-bus.jpg images/fsiot/fs27-bus-compare.png images/fsiot/fs27-decision-table.png images/fsiot/fs27-tools-browser.png images/fsiot/kit-i2c-bus.png images/fsiot/kit-spi-bus.png"
if "fs27-bus-compare.png" not in t:
    needle = "images/fsiot/fs26-library-manager.png images/fsiot/fs26-servo-timing.png \\"
    if needle not in t:
        # try alternate
        needle = "images/fsiot/fs26-cover-servo.jpg images/fsiot/fs26-library-manager.png images/fsiot/fs26-servo-timing.png \\"
        if needle not in t:
            raise SystemExit("ftp allowlist needle missing")
    t = t.replace(needle, needle.replace(" \\", " " + assets + " \\"), 1)
    print("ftp allowlist ok")
else:
    print("ftp allowlist exists")

if "seed-article-97-draft" not in t:
    step = '''
      - name: Seed article 97 draft via deploy hook (required, pre-launch B)
        run: |
          set +e
          for attempt in 1 2 3; do
            body="$(mktemp)"
            code=$(curl -sS --max-time 180 -o "$body" -w "%{http_code}" \\
              -H "X-Deploy-Token: ${{ secrets.DEPLOY_HOOK_TOKEN }}" \\
              "https://kodingindonesia.com/deploy/seed-article-97-draft" || echo "000")
            echo "seed-article-97-draft HTTP $code"
            sed -n '1,120p' "$body" || true
            [ "$code" = "200" ] && break
            sleep $((attempt * 5))
          done
          if [ "$code" != "200" ]; then
            echo "::error::seed-article-97-draft failed after retries (last HTTP $code)"
            exit 1
          fi
          code=$(curl -sS --max-time 30 -o /dev/null -w "%{http_code}" \\
            "https://kodingindonesia.com/artikel/fullstack-iot-bus-uart-i2c-spi")
          echo "Article 97 public HTTP status (expect 404): $code"
          test "$code" = "404"

'''
    anchor = "      - name: Publish article 69 via deploy hook (required)"
    if anchor not in t:
        raise SystemExit("deploy seed insert anchor missing")
    t = t.replace(anchor, step + anchor, 1)
    p.write_text(t, encoding="utf-8")
    print("deploy seed step ok")
else:
    p.write_text(t, encoding="utf-8")
    print("deploy seed step exists")

print("ALL DONE")
