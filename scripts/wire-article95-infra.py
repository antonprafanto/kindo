# -*- coding: utf-8 -*-
"""Wire FS-25 / #95 infrastructure: route, DeployController, lang, blade, deploy.yml."""
from pathlib import Path

ROOT = Path(".")

# --- route ---
p = ROOT / "routes/web.php"
t = p.read_text(encoding="utf-8")
if "seed-article-95-draft" not in t:
    old = """Route::get('/deploy/seed-article-94-draft', [DeployController::class, 'seedArticle94Draft'])
    ->middleware('throttle:120,1')
    ->name('deploy.seed-article-94-draft');"""
    new = old + """

Route::get('/deploy/seed-article-95-draft', [DeployController::class, 'seedArticle95Draft'])
    ->middleware('throttle:120,1')
    ->name('deploy.seed-article-95-draft');"""
    if old not in t:
        raise SystemExit("route anchor missing")
    p.write_text(t.replace(old, new, 1), encoding="utf-8")
    print("route ok")
else:
    print("route exists")

# --- DeployController ---
p = ROOT / "app/Http/Controllers/DeployController.php"
t = p.read_text(encoding="utf-8")
if "seedArticle95Draft" not in t:
    method = r'''
    public function seedArticle95Draft(): Response
    {
        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article95Seeder',
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response('Article 95 draft seed failed: '.$e->getMessage(), 500);
        }

        $slug = 'fullstack-iot-pir-gerak';
        $article = Article::where('slug', $slug)->first();
        if (! $article) {
            report(new \RuntimeException('Article 95 missing after draft seed.'));

            return response('Article 95 not found after draft seed', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 95 refused to stay draft after seed.'));

            return response('Article 95 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 95 unexpectedly visible via published() scope.'));

            return response('Article 95 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#95 (ini)',
            'FS-25',
            'BUILDER',
            'FS25_pir_gerak',
            'GPIO 25',
            'GPIO 2',
            'Tidak perlu hari ini',
            'Cara pakai artikel ini',
            'fsiot-pir-checklist',
            'FS-19',
            'FS-14',
            'FS-26',
            '/belajar/fullstack-iot',
            'Analogi:',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Cara menguji perintah di atas',
            'settle',
            'kit-pir-hcsr501.jpg',
            'fs25-pir-breadboard.png',
            'fs25-pir-wiring.png',
            'Gambar utama',
            'Skema bantu',
            'AnalogReadSerial',
            'Baud: 115200',
            'Buka Arduino IDE dulu',
            'HC-SR501',
            'digitalRead',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 95 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 95 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 95 English fields are incomplete after draft seed.'));

            return response('Article 95 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#95 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'BUILDER',
            'FS25_pir_gerak',
            'GPIO 25',
            'GPIO 2',
            'fsiot-pir-checklist',
            'FS-26',
            'Common mistakes',
            'How to test the commands above',
            'Main figure',
            'Helper schematic',
            'fs25-pir-breadboard.png',
            'fs25-pir-wiring.png',
            'AnalogReadSerial',
            'Baud: 115200',
            'Open Arduino IDE first',
            'settle',
            'HC-SR501',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 95 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 95 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 95 seeded as draft (pre-launch B)', 200);
    }

'''
    anchor = "    private function runDuplicateBme280Cleanup(): void"
    if anchor not in t:
        raise SystemExit("DeployController anchor missing")
    p.write_text(t.replace(anchor, method + anchor, 1), encoding="utf-8")
    print("deploy controller ok")
else:
    print("deploy controller exists")

# --- lang ---
lang_blocks = [
    (
        "lang/id/ui.php",
        """        'fsiot_pir_badge' => 'Checklist PIR',
        'fsiot_pir_hint' => 'Centang tiap langkah IDE + PIR GPIO 25 + LED GPIO 2 + settle + Serial. Target: 10/10.',
        'fsiot_pir_check' => 'Cek kelengkapan',
        'fsiot_pir_retry' => 'Kosongkan lagi',
        'fsiot_pir_paper' => 'Versi catatan (kertas / tanpa klik)',
        'fsiot_pir_progress' => ':filled dari :total tercentang',
        'fsiot_pir_pass' => 'Lengkap :filled/:total — gerak tangan menggerakkan LED! Lanjut FS-26 (servo) saat modulnya terbit.',
        'fsiot_pir_incomplete' => 'Masih ada langkah yang belum dicentang. Cek wiring GPIO 25/2, settle, jumper H/L, atau Serial lagi.',
        'fsiot_pir_done' => 'Selesai',
        'fsiot_pir_todo' => 'Belum',
""",
    ),
    (
        "lang/en/ui.php",
        """        'fsiot_pir_badge' => 'PIR checklist',
        'fsiot_pir_hint' => 'Tick each IDE + PIR GPIO 25 + LED GPIO 2 + settle + Serial step. Target: 10/10.',
        'fsiot_pir_check' => 'Check completeness',
        'fsiot_pir_retry' => 'Clear again',
        'fsiot_pir_paper' => 'Paper version (no clicks)',
        'fsiot_pir_progress' => ':filled of :total checked',
        'fsiot_pir_pass' => 'Complete :filled/:total — hand motion drives the LED! Continue to FS-26 (servo) when that module publishes.',
        'fsiot_pir_incomplete' => 'Some steps are still unchecked. Recheck GPIO 25/2 wiring, settle time, H/L jumper, or Serial output.',
        'fsiot_pir_done' => 'Done',
        'fsiot_pir_todo' => 'Not yet',
""",
    ),
]
for lang, keys in lang_blocks:
    p = ROOT / lang
    t = p.read_text(encoding="utf-8")
    if "fsiot_pir_badge" not in t:
        marker = "        'fsiot_auto_todo' =>"
        idx = t.find(marker)
        if idx < 0:
            raise SystemExit(f"lang marker missing in {lang}")
        end = t.find("\n", idx)
        t = t[: end + 1] + keys + t[end + 1 :]
        p.write_text(t, encoding="utf-8")
        print("lang", lang, "ok")
    else:
        print("lang", lang, "exists")

# --- blade ---
p = ROOT / "resources/views/articles/show.blade.php"
t = p.read_text(encoding="utf-8")
if "initFsiotPirChecklist" not in t:
    t = t.replace(
        "    initFsiotAutoChecklist();\n});",
        "    initFsiotAutoChecklist();\n    initFsiotPirChecklist();\n});",
        1,
    )
    fn = r'''
function initFsiotPirChecklist() {
    initFsiotChecklistWidget({
        h2Id: 'fsiot-pir-checklist',
        listId: 'fsiot-pir-checklist-items',
        storagePrefix: 'fsiot-cl-95',
        idPrefix: 'fsiot-pir',
        minItems: 10,
        labels: {
            badge: @js(__('ui.articles.fsiot_pir_badge')),
            hint: @js(__('ui.articles.fsiot_pir_hint')),
            check: @js(__('ui.articles.fsiot_pir_check')),
            retry: @js(__('ui.articles.fsiot_pir_retry')),
            paper: @js(__('ui.articles.fsiot_pir_paper')),
            progress: @js(__('ui.articles.fsiot_pir_progress')),
            pass: @js(__('ui.articles.fsiot_pir_pass')),
            incomplete: @js(__('ui.articles.fsiot_pir_incomplete')),
            done: @js(__('ui.articles.fsiot_pir_done')),
            todo: @js(__('ui.articles.fsiot_pir_todo')),
        },
    });
}
'''
    t = t.replace(
        "function initFsiotAutoChecklist() {",
        fn + "\nfunction initFsiotAutoChecklist() {",
        1,
    )
    p.write_text(t, encoding="utf-8")
    print("blade ok")
else:
    print("blade exists")

# --- deploy.yml FTP ---
p = ROOT / ".github/workflows/deploy.yml"
t = p.read_text(encoding="utf-8")
if "fs25-pir-wiring.png" not in t:
    needle = "images/fsiot/fs24-otomasi-wiring.png images/fsiot/fs24-otomasi-breadboard.png images/fsiot/fs24-cover-otomasi.jpg \\"
    repl = needle.replace(
        "fs24-cover-otomasi.jpg \\",
        "fs24-cover-otomasi.jpg images/fsiot/kit-pir-hcsr501.jpg images/fsiot/fs25-pir-wiring.png images/fsiot/fs25-pir-breadboard.png images/fsiot/fs25-cover-pir.jpg \\",
    )
    if needle not in t:
        raise SystemExit("ftp allowlist needle missing")
    t = t.replace(needle, repl, 1)
    print("ftp allowlist ok")
else:
    print("ftp allowlist exists")

# --- deploy.yml seed step ---
if "seed-article-95-draft" not in t:
    seed = r'''
      - name: Seed article 95 draft via deploy hook (required, pre-launch B)
        run: |
          set +e
          for attempt in 1 2 3; do
            body="$(mktemp)"
            code=$(curl -sS --max-time 180 -o "$body" -w "%{http_code}" \
              -H "X-Deploy-Token: ${{ secrets.DEPLOY_HOOK_TOKEN }}" \
              "https://kodingindonesia.com/deploy/seed-article-95-draft" || echo "000")
            echo "seed-article-95-draft HTTP $code"
            sed -n '1,120p' "$body" || true
            [ "$code" = "200" ] && break
            sleep $((attempt * 5))
          done
          if [ "$code" != "200" ]; then
            echo "::error::seed-article-95-draft failed after retries (last HTTP $code)"
            exit 1
          fi
          code=$(curl -sS --max-time 30 -o /dev/null -w "%{http_code}" \
            "https://kodingindonesia.com/artikel/fullstack-iot-pir-gerak")
          echo "Article 95 public HTTP status (expect 404): $code"
          test "$code" = "404"

'''
    anchor = "      - name: Publish article 69 via deploy hook (required)"
    if anchor not in t:
        raise SystemExit("deploy seed anchor missing")
    t = t.replace(anchor, seed + anchor, 1)
    p.write_text(t, encoding="utf-8")
    print("deploy seed step ok")
else:
    p.write_text(t, encoding="utf-8")
    print("deploy seed step exists")

print("wire-article95 done")
