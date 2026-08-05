# -*- coding: utf-8 -*-
"""Wire FS-26 / #96 infrastructure: route, DeployController, lang, blade, deploy.yml."""
from pathlib import Path

ROOT = Path(".")

# --- route ---
p = ROOT / "routes/web.php"
t = p.read_text(encoding="utf-8")
if "seed-article-96-draft" not in t:
    old = """Route::get('/deploy/seed-article-95-draft', [DeployController::class, 'seedArticle95Draft'])
    ->middleware('throttle:120,1')
    ->name('deploy.seed-article-95-draft');"""
    new = old + """

Route::get('/deploy/seed-article-96-draft', [DeployController::class, 'seedArticle96Draft'])
    ->middleware('throttle:120,1')
    ->name('deploy.seed-article-96-draft');"""
    if old not in t:
        raise SystemExit("route anchor missing")
    p.write_text(t.replace(old, new, 1), encoding="utf-8")
    print("route ok")
else:
    print("route exists")

# --- DeployController ---
p = ROOT / "app/Http/Controllers/DeployController.php"
t = p.read_text(encoding="utf-8")
if "seedArticle96Draft" not in t:
    method = r'''
    public function seedArticle96Draft(): Response
    {
        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article96Seeder',
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response('Article 96 draft seed failed: '.$e->getMessage(), 500);
        }

        $slug = 'fullstack-iot-servo-pwm';
        $article = Article::where('slug', $slug)->first();
        if (! $article) {
            report(new \RuntimeException('Article 96 missing after draft seed.'));

            return response('Article 96 not found after draft seed', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 96 refused to stay draft after seed.'));

            return response('Article 96 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 96 unexpectedly visible via published() scope.'));

            return response('Article 96 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#96 (ini)',
            'FS-26',
            'BUILDER',
            'FS26_servo_sudut',
            'GPIO 13',
            'ESP32Servo',
            'Tidak perlu hari ini',
            'Cara pakai artikel ini',
            'fsiot-servo-checklist',
            'FS-20',
            'FS-14',
            'FS-27',
            '/belajar/fullstack-iot',
            'Analogi:',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Cara menguji perintah di atas',
            'Library Manager',
            'kit-servo-sg90.jpg',
            'fs26-servo-wiring.png',
            'Gambar utama',
            'AnalogReadSerial',
            'Baud: 115200',
            'Buka Arduino IDE dulu',
            'SG90',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 96 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 96 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 96 English fields are incomplete after draft seed.'));

            return response('Article 96 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#96 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'BUILDER',
            'FS26_servo_sudut',
            'GPIO 13',
            'ESP32Servo',
            'fsiot-servo-checklist',
            'FS-27',
            'Common mistakes',
            'How to test the commands above',
            'Main figure',
            'fs26-servo-wiring.png',
            'Library Manager',
            'AnalogReadSerial',
            'Baud: 115200',
            'Open Arduino IDE first',
            'SG90',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 96 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 96 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 96 seeded as draft (pre-launch B)', 200);
    }

'''
    anchor = "    public function runDuplicateBme280Cleanup()"
    # Prefer insert after seedArticle95Draft end — find unique marker
    marker = "        return response('Article 95 seeded as draft (pre-launch B)', 200);\n    }"
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
if "fsiot_servo_badge" not in t:
    block = """        'fsiot_pir_todo' => 'Belum',
        'fsiot_servo_badge' => 'Checklist servo',
        'fsiot_servo_hint' => 'Centang tiap langkah IDE + Library ESP32Servo + GPIO 13 + Serial sudut. Target: 10/10.',
        'fsiot_servo_check' => 'Cek kelengkapan',
        'fsiot_servo_retry' => 'Kosongkan lagi',
        'fsiot_servo_paper' => 'Versi catatan (kertas / tanpa klik)',
        'fsiot_servo_progress' => ':filled dari :total tercentang',
        'fsiot_servo_pass' => 'Lengkap :filled/:total — lengan mengikuti sudut! Lanjut FS-27 (bus) saat modulnya terbit.',
        'fsiot_servo_incomplete' => 'Masih ada langkah yang belum dicentang. Cek library ESP32Servo, wiring 5V/GPIO 13, atau Serial lagi.',
        'fsiot_servo_done' => 'Selesai',
        'fsiot_servo_todo' => 'Belum',
"""
    if "'fsiot_pir_todo' => 'Belum'," not in t:
        raise SystemExit("lang id pir_todo missing")
    t = t.replace("'fsiot_pir_todo' => 'Belum',", block, 1)
    p.write_text(t, encoding="utf-8")
    print("lang id ok")
else:
    print("lang id exists")

# --- lang EN ---
p = ROOT / "lang/en/ui.php"
t = p.read_text(encoding="utf-8")
if "fsiot_servo_badge" not in t:
    # find pir_todo in EN
    needle = None
    for cand in ["'fsiot_pir_todo' => 'Not yet',", "'fsiot_pir_todo' => 'Todo',", "'fsiot_pir_todo' => 'Pending',"]:
        if cand in t:
            needle = cand
            break
    if not needle:
        # read around pir
        idx = t.find("fsiot_pir_todo")
        raise SystemExit(f"lang en pir_todo missing near {t[idx:idx+80]!r}")
    block = needle + """
        'fsiot_servo_badge' => 'Servo checklist',
        'fsiot_servo_hint' => 'Tick each IDE step + ESP32Servo library + GPIO 13 + angle Serial. Target: 10/10.',
        'fsiot_servo_check' => 'Check completeness',
        'fsiot_servo_retry' => 'Clear again',
        'fsiot_servo_paper' => 'Paper version (no clicks)',
        'fsiot_servo_progress' => ':filled of :total checked',
        'fsiot_servo_pass' => 'Complete :filled/:total — the arm follows the angle! Continue to FS-27 (buses) when that module ships.',
        'fsiot_servo_incomplete' => 'Some steps are still unchecked. Recheck ESP32Servo, 5V/GPIO 13 wiring, or Serial.',
        'fsiot_servo_done' => 'Done',
        'fsiot_servo_todo' => 'Not yet',
"""
    # Keep same todo word as pir
    todo_val = needle.split("=>")[1].strip().rstrip(",")
    block = block.replace("'Not yet',", todo_val + "," if "'fsiot_servo_todo'" in block else todo_val)
    # simpler: rebuild
    todo_word = needle.split("=>", 1)[1].strip()
    block = f"""{needle}
        'fsiot_servo_badge' => 'Servo checklist',
        'fsiot_servo_hint' => 'Tick each IDE step + ESP32Servo library + GPIO 13 + angle Serial. Target: 10/10.',
        'fsiot_servo_check' => 'Check completeness',
        'fsiot_servo_retry' => 'Clear again',
        'fsiot_servo_paper' => 'Paper version (no clicks)',
        'fsiot_servo_progress' => ':filled of :total checked',
        'fsiot_servo_pass' => 'Complete :filled/:total — the arm follows the angle! Continue to FS-27 (buses) when that module ships.',
        'fsiot_servo_incomplete' => 'Some steps are still unchecked. Recheck ESP32Servo, 5V/GPIO 13 wiring, or Serial.',
        'fsiot_servo_done' => 'Done',
        'fsiot_servo_todo' => {todo_word}
"""
    t = t.replace(needle, block, 1)
    p.write_text(t, encoding="utf-8")
    print("lang en ok")
else:
    print("lang en exists")

# --- blade ---
p = ROOT / "resources/views/articles/show.blade.php"
t = p.read_text(encoding="utf-8")
if "initFsiotServoChecklist" not in t:
    if "initFsiotPirChecklist();" not in t:
        raise SystemExit("blade call site missing")
    t = t.replace("initFsiotPirChecklist();", "initFsiotPirChecklist();\n    initFsiotServoChecklist();", 1)
    fn = """
function initFsiotServoChecklist() {
    initFsiotChecklistWidget({
        h2Id: 'fsiot-servo-checklist',
        listId: 'fsiot-servo-checklist-items',
        storagePrefix: 'fsiot-cl-96',
        idPrefix: 'fsiot-servo',
        minItems: 10,
        labels: {
            badge: @js(__('ui.articles.fsiot_servo_badge')),
            hint: @js(__('ui.articles.fsiot_servo_hint')),
            check: @js(__('ui.articles.fsiot_servo_check')),
            retry: @js(__('ui.articles.fsiot_servo_retry')),
            paper: @js(__('ui.articles.fsiot_servo_paper')),
            progress: @js(__('ui.articles.fsiot_servo_progress')),
            pass: @js(__('ui.articles.fsiot_servo_pass')),
            incomplete: @js(__('ui.articles.fsiot_servo_incomplete')),
            done: @js(__('ui.articles.fsiot_servo_done')),
            todo: @js(__('ui.articles.fsiot_servo_todo')),
        },
    });
}

"""
    if "function initFsiotPirChecklist()" not in t:
        raise SystemExit("blade pir fn missing")
    t = t.replace("function initFsiotPirChecklist()", fn + "function initFsiotPirChecklist()", 1)
    p.write_text(t, encoding="utf-8")
    print("blade ok")
else:
    print("blade exists")

# --- deploy.yml FTP ---
p = ROOT / ".github/workflows/deploy.yml"
t = p.read_text(encoding="utf-8")
if "fs26-servo-wiring.png" not in t:
    needle = "images/fsiot/kit-pir-hcsr501.jpg images/fsiot/fs25-pir-wiring.png images/fsiot/fs25-pir-breadboard.png images/fsiot/fs25-cover-pir.jpg \\"
    repl = needle.replace(
        "fs25-cover-pir.jpg \\",
        "fs25-cover-pir.jpg images/fsiot/kit-servo-sg90.jpg images/fsiot/fs26-servo-wiring.png images/fsiot/fs26-cover-servo.jpg \\",
    )
    if needle not in t:
        raise SystemExit("ftp allowlist needle missing")
    t = t.replace(needle, repl, 1)
    print("ftp allowlist ok")
else:
    print("ftp allowlist exists")

if "seed-article-96-draft" not in t:
    step = '''
      - name: Seed article 96 draft via deploy hook (required, pre-launch B)
        run: |
          set +e
          for attempt in 1 2 3; do
            body="$(mktemp)"
            code=$(curl -sS --max-time 180 -o "$body" -w "%{http_code}" \\
              -H "X-Deploy-Token: ${{ secrets.DEPLOY_HOOK_TOKEN }}" \\
              "https://kodingindonesia.com/deploy/seed-article-96-draft" || echo "000")
            echo "seed-article-96-draft HTTP $code"
            sed -n '1,120p' "$body" || true
            [ "$code" = "200" ] && break
            sleep $((attempt * 5))
          done
          if [ "$code" != "200" ]; then
            echo "::error::seed-article-96-draft failed after retries (last HTTP $code)"
            exit 1
          fi
          code=$(curl -sS --max-time 30 -o /dev/null -w "%{http_code}" \\
            "https://kodingindonesia.com/artikel/fullstack-iot-servo-pwm")
          echo "Article 96 public HTTP status (expect 404): $code"
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
