# -*- coding: utf-8 -*-
"""Wire FS-29 / #99 infrastructure: route, DeployController, lang, blade, deploy.yml."""
from pathlib import Path

ROOT = Path(".")

# --- route ---
p = ROOT / "routes/web.php"
t = p.read_text(encoding="utf-8")
if "seed-article-99-draft" not in t:
    old = """Route::get('/deploy/seed-article-98-draft', [DeployController::class, 'seedArticle98Draft'])
    ->middleware('throttle:120,1')
    ->name('deploy.seed-article-98-draft');"""
    new = old + """

Route::get('/deploy/seed-article-99-draft', [DeployController::class, 'seedArticle99Draft'])
    ->middleware('throttle:120,1')
    ->name('deploy.seed-article-99-draft');"""
    if old not in t:
        raise SystemExit("route anchor missing")
    p.write_text(t.replace(old, new, 1), encoding="utf-8")
    print("route ok")
else:
    print("route exists")

# --- DeployController ---
p = ROOT / "app/Http/Controllers/DeployController.php"
t = p.read_text(encoding="utf-8")
if "seedArticle99Draft" not in t:
    method = r'''
    public function seedArticle99Draft(): Response
    {
        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article99Seeder',
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response('Article 99 draft seed failed: '.$e->getMessage(), 500);
        }

        $slug = 'fullstack-iot-wifi-dari-nol';
        $article = Article::where('slug', $slug)->first();
        if (! $article) {
            report(new \RuntimeException('Article 99 missing after draft seed.'));

            return response('Article 99 not found after draft seed', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 99 refused to stay draft after seed.'));

            return response('Article 99 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 99 unexpectedly visible via published() scope.'));

            return response('Article 99 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#99 (ini)',
            'FS-29',
            'CONNECTED',
            'Tidak perlu hari ini',
            'Cara pakai artikel ini',
            'fsiot-wifi-checklist',
            'FS-28',
            'FS-19',
            'FS-14',
            '/belajar/fullstack-iot',
            'Analogi:',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Cara menguji perintah di atas',
            'WiFi.begin',
            '2,4 GHz',
            'FS29_wifi_begin',
            'WL_CONNECTED',
            'fs29-wifi-station.png',
            'Gambar utama',
            'Skema bantu',
            'Buka Arduino IDE dulu',
            '115200',
            'YOUR_SSID',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 99 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 99 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 99 English fields are incomplete after draft seed.'));

            return response('Article 99 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#99 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'CONNECTED',
            'fsiot-wifi-checklist',
            'FS-28',
            'Common mistakes',
            'How to test the commands above',
            'Main figure',
            'Helper schematic',
            'fs29-wifi-station.png',
            'Open Arduino IDE first',
            'WiFi.begin',
            '2.4 GHz',
            'FS29_wifi_begin',
            'WL_CONNECTED',
            '115200',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 99 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 99 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 99 seeded as draft (pre-launch B)', 200);
    }

'''
    anchor = "        return response('Article 98 seeded as draft (pre-launch B)', 200);\n    }"
    if anchor not in t:
        raise SystemExit("DeployController 98 anchor missing")
    t = t.replace(anchor, anchor + "\n" + method, 1)
    p.write_text(t, encoding="utf-8")
    print("DeployController ok")
else:
    print("DeployController exists")

# --- lang ID ---
for lang, keys in [
    (
        "lang/id/ui.php",
        """
        'fsiot_wifi_badge' => 'Checklist Wi-Fi',
        'fsiot_wifi_hint' => 'Centang SSID 2,4 GHz + Upload + IP di Serial. Target: 10/10.',
        'fsiot_wifi_check' => 'Cek kelengkapan',
        'fsiot_wifi_retry' => 'Kosongkan lagi',
        'fsiot_wifi_paper' => 'Versi catatan (kertas / tanpa klik)',
        'fsiot_wifi_progress' => ':filled dari :total tercentang',
        'fsiot_wifi_pass' => 'Lengkap :filled/:total — ESP32 sudah punya IP! Siap FS-30 saat terbit.',
        'fsiot_wifi_incomplete' => 'Masih ada langkah yang belum dicentang. Cek SSID 2,4 GHz, password, atau Serial 115200.',
        'fsiot_wifi_done' => 'Selesai',
        'fsiot_wifi_todo' => 'Belum',
""",
    ),
    (
        "lang/en/ui.php",
        """
        'fsiot_wifi_badge' => 'Wi-Fi checklist',
        'fsiot_wifi_hint' => 'Tick 2.4 GHz SSID + Upload + IP on Serial. Target: 10/10.',
        'fsiot_wifi_check' => 'Check completeness',
        'fsiot_wifi_retry' => 'Clear again',
        'fsiot_wifi_paper' => 'Paper version (no clicks)',
        'fsiot_wifi_progress' => ':filled of :total checked',
        'fsiot_wifi_pass' => 'Complete :filled/:total — ESP32 has an IP! Ready for FS-30 when it ships.',
        'fsiot_wifi_incomplete' => 'Some steps are still unchecked. Recheck 2.4 GHz SSID, password, or Serial 115200.',
        'fsiot_wifi_done' => 'Done',
        'fsiot_wifi_todo' => 'Not yet',
""",
    ),
]:
    p = ROOT / lang
    t = p.read_text(encoding="utf-8")
    if "fsiot_wifi_badge" not in t:
        needle = "        'fsiot_i2c_todo' =>"
        idx = t.find(needle)
        if idx < 0:
            raise SystemExit(f"lang anchor missing in {lang}")
        # insert after the i2c_todo line
        end = t.find("\n", idx)
        line_end = t.find("\n", end + 1)  # skip value line if same - actually todo is one line
        # find full line
        line_end = t.find("\n", idx)
        insert_at = line_end + 1
        t = t[:insert_at] + keys + t[insert_at:]
        p.write_text(t, encoding="utf-8")
        print(f"{lang} ok")
    else:
        print(f"{lang} exists")

# --- blade ---
p = ROOT / "resources/views/articles/show.blade.php"
t = p.read_text(encoding="utf-8")
if "initFsiotWifiChecklist" not in t:
    t = t.replace(
        "    initFsiotI2cChecklist();\n});",
        "    initFsiotI2cChecklist();\n    initFsiotWifiChecklist();\n});",
        1,
    )
    fn = """
function initFsiotWifiChecklist() {
    initFsiotChecklistWidget({
        h2Id: 'fsiot-wifi-checklist',
        listId: 'fsiot-wifi-checklist-items',
        storagePrefix: 'fsiot-cl-99',
        idPrefix: 'fsiot-wifi',
        minItems: 10,
        labels: {
            badge: @js(__('ui.articles.fsiot_wifi_badge')),
            hint: @js(__('ui.articles.fsiot_wifi_hint')),
            check: @js(__('ui.articles.fsiot_wifi_check')),
            retry: @js(__('ui.articles.fsiot_wifi_retry')),
            paper: @js(__('ui.articles.fsiot_wifi_paper')),
            progress: @js(__('ui.articles.fsiot_wifi_progress')),
            pass: @js(__('ui.articles.fsiot_wifi_pass')),
            incomplete: @js(__('ui.articles.fsiot_wifi_incomplete')),
            done: @js(__('ui.articles.fsiot_wifi_done')),
            todo: @js(__('ui.articles.fsiot_wifi_todo')),
        },
    });
}

"""
    t = t.replace(
        "function initFsiotI2cChecklist() {",
        fn + "function initFsiotI2cChecklist() {",
        1,
    )
    p.write_text(t, encoding="utf-8")
    print("blade ok")
else:
    print("blade exists")

# --- deploy.yml FTP allowlist ---
p = ROOT / ".github/workflows/deploy.yml"
t = p.read_text(encoding="utf-8")
fs29 = (
    "images/fsiot/fs29-cover-wifi.jpg images/fsiot/fs29-cover-wifi.webp "
    "images/fsiot/fs29-tools-ide.png images/fsiot/fs29-wifi-core.png "
    "images/fsiot/fs29-wifi-station.png images/fsiot/fs29-band-2g4.png "
    "images/fsiot/fs29-modul-router.png images/fsiot/fs29-success-serial.png"
)
if "fs29-wifi-station.png" not in t:
    old = (
        "images/fsiot/fs28-cover-i2c.jpg images/fsiot/fs28-cover-i2c.webp "
        "images/fsiot/fs28-tools-ide.png images/fsiot/fs28-library-manager.png "
        "images/fsiot/fs28-i2c-breadboard.png images/fsiot/fs28-i2c-wiring.png "
        "images/fsiot/fs28-modul-kit.png images/fsiot/fs28-success-oled-serial.png \\"
    )
    new = old.replace(
        "images/fsiot/fs28-success-oled-serial.png \\",
        "images/fsiot/fs28-success-oled-serial.png " + fs29 + " \\",
    )
    if old not in t:
        raise SystemExit("ftp allowlist anchor missing")
    t = t.replace(old, new, 1)
    print("ftp allowlist ok")
else:
    print("ftp allowlist exists")

# curl99 critical upload after curl98 block
if "id: curl99" not in t:
    curl99 = r'''
      - name: Upload critical #99 files via curl (pre-sync)
        id: curl99
        continue-on-error: true
        run: |
          set +e
          PROTO="${{ steps.ftp.outputs.protocol }}"
          APP="${PROTO}://${{ secrets.FTP_SERVER }}${{ steps.ftp.outputs.dir }}"
          PUB="${PROTO}://${{ secrets.FTP_SERVER }}${{ steps.public.outputs.dir }}"
          AUTH="${{ secrets.FTP_USERNAME }}:${{ secrets.FTP_PASSWORD }}"
          upload() {
            local src="$1" dest="$2" a=1
            while [ "$a" -le 5 ]; do
              if curl -fsS --connect-timeout 30 --max-time 180 --ftp-pasv --ftp-create-dirs --user "$AUTH" -T "$src" "$dest"; then
                echo "OK $src -> $dest (attempt $a)"
                return 0
              fi
              echo "FAIL $src -> $dest (attempt $a)"
              sleep $((a * 20))
              a=$((a + 1))
            done
            return 1
          }
          failed=0
          upload "database/seeders/Article99Seeder.php" "${APP}database/seeders/Article99Seeder.php" || failed=1
          upload "app/Http/Controllers/DeployController.php" "${APP}app/Http/Controllers/DeployController.php" || failed=1
          for img in fs29-cover-wifi.jpg fs29-cover-wifi.webp fs29-tools-ide.png fs29-wifi-core.png \
            fs29-wifi-station.png fs29-band-2g4.png fs29-modul-router.png fs29-success-serial.png; do
            upload "public/images/fsiot/$img" "${APP}public/images/fsiot/$img" || failed=1
            upload "public/images/fsiot/$img" "${PUB}images/fsiot/$img" || failed=1
          done
          if [ "$failed" -ne 0 ]; then
            echo "::warning::Some critical #99 curl uploads failed"
            exit 1
          fi
          echo "Critical #99 curl uploads OK"

'''
    marker = "          echo \"Critical #98 curl uploads OK\"\n\n      - name: Deploy build assets via FTP"
    if marker not in t:
        raise SystemExit("curl98 marker missing")
    t = t.replace(
        "          echo \"Critical #98 curl uploads OK\"\n\n      - name: Deploy build assets via FTP",
        "          echo \"Critical #98 curl uploads OK\"\n" + curl99 + "      - name: Deploy build assets via FTP",
        1,
    )
    # soft-pass confirms if curl98 OR curl99
    t = t.replace(
        'if [ "${{ steps.curl98.outcome }}" = "success" ]; then\n            echo "::warning::Build asset FTP failed after retries — continuing because critical #98 curl upload succeeded"',
        'if [ "${{ steps.curl98.outcome }}" = "success" ] || [ "${{ steps.curl99.outcome }}" = "success" ]; then\n            echo "::warning::Build asset FTP failed after retries — continuing because critical #98/#99 curl upload succeeded"',
    )
    t = t.replace(
        'if [ "${{ steps.curl98.outcome }}" = "success" ]; then\n            echo "::warning::Main SamKirkland FTP failed after retries — continuing because critical #98 curl upload succeeded"',
        'if [ "${{ steps.curl98.outcome }}" = "success" ] || [ "${{ steps.curl99.outcome }}" = "success" ]; then\n            echo "::warning::Main SamKirkland FTP failed after retries — continuing because critical #98/#99 curl upload succeeded"',
    )
    print("curl99 ok")
else:
    print("curl99 exists")

# seed step after article 98
if "seed-article-99-draft" not in t:
    seed99 = r'''
      - name: Seed article 99 draft via deploy hook (required, pre-launch B)
        if: always() && !cancelled()
        env:
          DEPLOY_HOOK_TOKEN: ${{ secrets.DEPLOY_HOOK_TOKEN }}
        run: |
          set -euo pipefail
          code="000"
          for i in 1 2 3 4 5 6 7 8; do
            code=$(curl -sS -o /tmp/seed99.out -w "%{http_code}" -H "X-Deploy-Token: $DEPLOY_HOOK_TOKEN" \
              "https://kodingindonesia.com/deploy/seed-article-99-draft" || echo "000")
            echo "seed-article-99-draft HTTP $code"
            if [ "$code" = "200" ]; then
              cat /tmp/seed99.out || true
              break
            fi
            sleep $((i * 5))
          done
          if [ "$code" != "200" ]; then
            echo "::error::seed-article-99-draft failed after retries (last HTTP $code)"
            cat /tmp/seed99.out || true
            exit 1
          fi
          pub=$(curl -sS -o /dev/null -w "%{http_code}" "https://kodingindonesia.com/artikel/fullstack-iot-wifi-dari-nol" || echo "000")
          echo "public article 99 HTTP $pub (expect 404 draft)"
          if [ "$pub" != "404" ]; then
            echo "::error::article 99 must stay unpublished (got HTTP $pub)"
            exit 1
          fi

'''
    # insert after seed 98 block ends (before Publish article 69)
    anchor = "      - name: Publish article 69 via deploy hook (required)"
    if anchor not in t:
        raise SystemExit("publish 69 anchor missing")
    t = t.replace(anchor, seed99 + anchor, 1)
    print("seed99 step ok")
else:
    print("seed99 step exists")

p.write_text(t, encoding="utf-8")
print("wire-article99 done")
