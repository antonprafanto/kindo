# -*- coding: utf-8 -*-
"""Wire FS-28 / #98 infrastructure: route, DeployController, lang, blade, deploy.yml."""
from pathlib import Path

ROOT = Path(".")

# --- route ---
p = ROOT / "routes/web.php"
t = p.read_text(encoding="utf-8")
if "seed-article-98-draft" not in t:
    old = """Route::get('/deploy/seed-article-97-draft', [DeployController::class, 'seedArticle97Draft'])
    ->middleware('throttle:120,1')
    ->name('deploy.seed-article-97-draft');"""
    new = old + """

Route::get('/deploy/seed-article-98-draft', [DeployController::class, 'seedArticle98Draft'])
    ->middleware('throttle:120,1')
    ->name('deploy.seed-article-98-draft');"""
    if old not in t:
        raise SystemExit("route anchor missing")
    p.write_text(t.replace(old, new, 1), encoding="utf-8")
    print("route ok")
else:
    print("route exists")

# --- DeployController ---
p = ROOT / "app/Http/Controllers/DeployController.php"
t = p.read_text(encoding="utf-8")
if "seedArticle98Draft" not in t:
    method = r'''
    public function seedArticle98Draft(): Response
    {
        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article98Seeder',
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response('Article 98 draft seed failed: '.$e->getMessage(), 500);
        }

        $slug = 'fullstack-iot-i2c-bme280-oled';
        $article = Article::where('slug', $slug)->first();
        if (! $article) {
            report(new \RuntimeException('Article 98 missing after draft seed.'));

            return response('Article 98 not found after draft seed', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 98 refused to stay draft after seed.'));

            return response('Article 98 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 98 unexpectedly visible via published() scope.'));

            return response('Article 98 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#98 (ini)',
            'FS-28',
            'BUILDER',
            'Tidak perlu hari ini',
            'Cara pakai artikel ini',
            'fsiot-i2c-checklist',
            'FS-27',
            'FS-21',
            'FS-14',
            'FS-17',
            '/belajar/fullstack-iot',
            'Analogi:',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Cara menguji perintah di atas',
            'BME280',
            'OLED',
            'GPIO 21',
            'GPIO 22',
            'FS28_bme280_oled',
            'Adafruit',
            'fs28-i2c-wiring.png',
            'Gambar utama',
            'Buka Arduino IDE dulu',
            'Library Manager',
            '115200',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 98 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 98 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 98 English fields are incomplete after draft seed.'));

            return response('Article 98 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#98 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'BUILDER',
            'fsiot-i2c-checklist',
            'FS-27',
            'Common mistakes',
            'How to test the commands above',
            'Main figure',
            'fs28-i2c-wiring.png',
            'Open Arduino IDE first',
            'BME280',
            'OLED',
            'GPIO 21',
            'GPIO 22',
            'FS28_bme280_oled',
            'Library Manager',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 98 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 98 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 98 seeded as draft (pre-launch B)', 200);
    }

'''
    marker = "        return response('Article 97 seeded as draft (pre-launch B)', 200);\n    }"
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
if "fsiot_i2c_badge" not in t:
    needle = "'fsiot_bus_todo' => 'Belum',"
    if needle not in t:
        raise SystemExit("lang id bus_todo missing")
    block = needle + """
        'fsiot_i2c_badge' => 'Checklist I2C',
        'fsiot_i2c_hint' => 'Centang wiring SDA/SCL + library + OLED selaras Serial. Target: 10/10.',
        'fsiot_i2c_check' => 'Cek kelengkapan',
        'fsiot_i2c_retry' => 'Kosongkan lagi',
        'fsiot_i2c_paper' => 'Versi catatan (kertas / tanpa klik)',
        'fsiot_i2c_progress' => ':filled dari :total tercentang',
        'fsiot_i2c_pass' => 'Lengkap :filled/:total — I2C hidup di layar! Siap gate BUILDER / FS-29 saat terbit.',
        'fsiot_i2c_incomplete' => 'Masih ada langkah yang belum dicentang. Cek wiring 21/22, library, atau alamat 0x76/0x3C.',
        'fsiot_i2c_done' => 'Selesai',
        'fsiot_i2c_todo' => 'Belum',
"""
    t = t.replace(needle, block, 1)
    p.write_text(t, encoding="utf-8")
    print("lang id ok")
else:
    print("lang id exists")

# --- lang EN ---
p = ROOT / "lang/en/ui.php"
t = p.read_text(encoding="utf-8")
if "fsiot_i2c_badge" not in t:
    needle = "'fsiot_bus_todo' => 'Not yet',"
    if needle not in t:
        raise SystemExit("lang en bus_todo missing")
    block = needle + """
        'fsiot_i2c_badge' => 'I2C checklist',
        'fsiot_i2c_hint' => 'Tick SDA/SCL wiring + libraries + OLED matching Serial. Target: 10/10.',
        'fsiot_i2c_check' => 'Check completeness',
        'fsiot_i2c_retry' => 'Clear again',
        'fsiot_i2c_paper' => 'Paper version (no clicks)',
        'fsiot_i2c_progress' => ':filled of :total checked',
        'fsiot_i2c_pass' => 'Complete :filled/:total — I2C is live on screen! Ready for the BUILDER gate / FS-29 when it ships.',
        'fsiot_i2c_incomplete' => 'Some steps are still unchecked. Recheck wiring 21/22, libraries, or addresses 0x76/0x3C.',
        'fsiot_i2c_done' => 'Done',
        'fsiot_i2c_todo' => 'Not yet',
"""
    t = t.replace(needle, block, 1)
    p.write_text(t, encoding="utf-8")
    print("lang en ok")
else:
    print("lang en exists")

# --- blade ---
p = ROOT / "resources/views/articles/show.blade.php"
t = p.read_text(encoding="utf-8")
if "initFsiotI2cChecklist" not in t:
    if "initFsiotBusChecklist();" not in t:
        raise SystemExit("blade call site missing")
    t = t.replace("initFsiotBusChecklist();", "initFsiotBusChecklist();\n    initFsiotI2cChecklist();", 1)
    fn = """
function initFsiotI2cChecklist() {
    initFsiotChecklistWidget({
        h2Id: 'fsiot-i2c-checklist',
        listId: 'fsiot-i2c-checklist-items',
        storagePrefix: 'fsiot-cl-98',
        idPrefix: 'fsiot-i2c',
        minItems: 10,
        labels: {
            badge: @js(__('ui.articles.fsiot_i2c_badge')),
            hint: @js(__('ui.articles.fsiot_i2c_hint')),
            check: @js(__('ui.articles.fsiot_i2c_check')),
            retry: @js(__('ui.articles.fsiot_i2c_retry')),
            paper: @js(__('ui.articles.fsiot_i2c_paper')),
            progress: @js(__('ui.articles.fsiot_i2c_progress')),
            pass: @js(__('ui.articles.fsiot_i2c_pass')),
            incomplete: @js(__('ui.articles.fsiot_i2c_incomplete')),
            done: @js(__('ui.articles.fsiot_i2c_done')),
            todo: @js(__('ui.articles.fsiot_i2c_todo')),
        },
    });
}
"""
    # insert function before initFsiotBusChecklist definition or after it
    anchor = "function initFsiotBusChecklist() {"
    if anchor not in t:
        raise SystemExit("blade fn anchor missing")
    t = t.replace(anchor, fn + "\n" + anchor, 1)
    p.write_text(t, encoding="utf-8")
    print("blade ok")
else:
    print("blade exists")

# --- deploy.yml FTP assets ---
p = ROOT / ".github/workflows/deploy.yml"
t = p.read_text(encoding="utf-8")
assets = (
    "images/fsiot/fs28-cover-i2c.jpg images/fsiot/fs28-cover-i2c.webp "
    "images/fsiot/fs28-tools-ide.png images/fsiot/fs28-library-manager.png "
    "images/fsiot/fs28-i2c-wiring.png images/fsiot/fs28-modul-kit.png "
    "images/fsiot/fs28-success-oled-serial.png"
)
if "fs28-i2c-wiring.png" not in t:
    needle = "images/fsiot/kit-spi-bus.png \\"
    if needle not in t:
        raise SystemExit("deploy ftp needle missing")
    t = t.replace(needle, needle + "\n            " + assets + " \\", 1)
    print("deploy ftp ok")
else:
    print("deploy ftp exists")

# --- deploy.yml seed step ---
if "seed-article-98-draft" not in t:
    # Find seed-97 step block end and append similar
    marker = 'seed-article-97-draft failed after retries'
    idx = t.find(marker)
    if idx < 0:
        raise SystemExit("seed-97 marker missing")
    # find end of that step (next "- name:" after idx)
    rest = t[idx:]
    # insert after the whole seed-97 step: look for next "      - name:" after this step's content
    # Simpler: append after the public 404 check for 97
    slug97 = "fullstack-iot-bus-uart-i2c-spi"
    block97_end_hint = f"artikel/{slug97}"
    pos = t.find(block97_end_hint)
    if pos < 0:
        raise SystemExit("slug97 verify missing")
    # find end of that YAML step: next line starting with "      - name:"
    after = t.find("\n      - name:", pos)
    if after < 0:
        raise SystemExit("next step after 97 missing")
    seed98 = '''
      - name: Seed article 98 draft via deploy hook (required, pre-launch B)
        env:
          DEPLOY_HOOK_TOKEN: ${{ secrets.DEPLOY_HOOK_TOKEN }}
        run: |
          set -euo pipefail
          code="000"
          for i in 1 2 3 4 5; do
            code=$(curl -sS -o /tmp/seed98.out -w "%{http_code}" -H "X-Deploy-Token: $DEPLOY_HOOK_TOKEN" \\
              "https://kodingindonesia.com/deploy/seed-article-98-draft" || echo "000")
            echo "seed-article-98-draft HTTP $code"
            if [ "$code" = "200" ]; then
              cat /tmp/seed98.out || true
              break
            fi
            sleep $((i * 5))
          done
          if [ "$code" != "200" ]; then
            echo "::error::seed-article-98-draft failed after retries (last HTTP $code)"
            cat /tmp/seed98.out || true
            exit 1
          fi
          pub=$(curl -sS -o /dev/null -w "%{http_code}" "https://kodingindonesia.com/artikel/fullstack-iot-i2c-bme280-oled" || echo "000")
          echo "public article 98 HTTP $pub (expect 404 draft)"
          if [ "$pub" != "404" ]; then
            echo "::error::article 98 must stay unpublished (got HTTP $pub)"
            exit 1
          fi
'''
    t = t[:after] + seed98 + t[after:]
    p.write_text(t, encoding="utf-8")
    print("deploy seed step ok")
else:
    p.write_text(t, encoding="utf-8")
    print("deploy seed exists")

print("wire-article98 done")
