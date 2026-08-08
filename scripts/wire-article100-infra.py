# -*- coding: utf-8 -*-
"""Wire FS-30 / #100 infrastructure: route, DeployController, lang, blade, deploy.yml."""
from pathlib import Path

ROOT = Path(".")

# --- route ---
p = ROOT / "routes/web.php"
t = p.read_text(encoding="utf-8")
if "seed-article-100-draft" not in t:
    old = """Route::get('/deploy/seed-article-99-draft', [DeployController::class, 'seedArticle99Draft'])
    ->middleware('throttle:120,1')
    ->name('deploy.seed-article-99-draft');"""
    new = old + """

Route::get('/deploy/seed-article-100-draft', [DeployController::class, 'seedArticle100Draft'])
    ->middleware('throttle:120,1')
    ->name('deploy.seed-article-100-draft');"""
    if old not in t:
        raise SystemExit("route anchor missing")
    p.write_text(t.replace(old, new, 1), encoding="utf-8")
    print("route ok")
else:
    print("route exists")

# --- DeployController ---
p = ROOT / "app/Http/Controllers/DeployController.php"
t = p.read_text(encoding="utf-8")
if "seedArticle100Draft" not in t:
    method = r'''
    public function seedArticle100Draft(): Response
    {
        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article100Seeder',
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response('Article 100 draft seed failed: '.$e->getMessage(), 500);
        }

        $slug = 'fullstack-iot-http-json';
        $article = Article::where('slug', $slug)->first();
        if (! $article) {
            report(new \RuntimeException('Article 100 missing after draft seed.'));

            return response('Article 100 not found after draft seed', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 100 refused to stay draft after seed.'));

            return response('Article 100 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 100 unexpectedly visible via published() scope.'));

            return response('Article 100 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#100 (ini)',
            'FS-30',
            'CONNECTED',
            'Tidak perlu hari ini',
            'Cara pakai artikel ini',
            'fsiot-http-checklist',
            'FS-29',
            'FS-14',
            '/belajar/fullstack-iot',
            'Analogi:',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Cara menguji perintah di atas',
            'HTTPClient',
            'FS30_http_get',
            'jsonplaceholder',
            'fs30-http-get.png',
            'Gambar utama',
            'Skema bantu',
            'Buka browser dulu',
            '115200',
            'YOUR_SSID',
            'HTTP 200',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 100 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 100 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 100 English fields are incomplete after draft seed.'));

            return response('Article 100 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#100 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'CONNECTED',
            'fsiot-http-checklist',
            'FS-29',
            'Common mistakes',
            'How to test the commands above',
            'Main figure',
            'Helper schematic',
            'fs30-http-get.png',
            'Open a browser first',
            'HTTPClient',
            'FS30_http_get',
            'jsonplaceholder',
            '115200',
            'HTTP 200',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 100 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 100 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 100 seeded as draft (pre-launch B)', 200);
    }

'''
    anchor = "        return response('Article 99 seeded as draft (pre-launch B)', 200);\n    }"
    if anchor not in t:
        raise SystemExit("DeployController 99 anchor missing")
    t = t.replace(anchor, anchor + "\n" + method, 1)
    p.write_text(t, encoding="utf-8")
    print("DeployController ok")
else:
    print("DeployController exists")

# --- lang ---
for lang, keys in [
    (
        "lang/id/ui.php",
        """
        'fsiot_http_badge' => 'Checklist HTTP & JSON',
        'fsiot_http_hint' => 'Centang browser JSON + Upload + HTTP 200 di Serial. Target: 10/10.',
        'fsiot_http_check' => 'Cek kelengkapan',
        'fsiot_http_retry' => 'Kosongkan lagi',
        'fsiot_http_paper' => 'Versi catatan (kertas / tanpa klik)',
        'fsiot_http_progress' => ':filled dari :total tercentang',
        'fsiot_http_pass' => 'Lengkap :filled/:total — HTTP 200 + JSON di Serial! Siap FS-31 saat terbit.',
        'fsiot_http_incomplete' => 'Masih ada langkah yang belum dicentang. Cek browser JSON, Wi-Fi, atau Serial 115200.',
        'fsiot_http_done' => 'Selesai',
        'fsiot_http_todo' => 'Belum',
""",
    ),
    (
        "lang/en/ui.php",
        """
        'fsiot_http_badge' => 'HTTP & JSON checklist',
        'fsiot_http_hint' => 'Tick browser JSON + Upload + HTTP 200 on Serial. Target: 10/10.',
        'fsiot_http_check' => 'Check completeness',
        'fsiot_http_retry' => 'Clear again',
        'fsiot_http_paper' => 'Paper version (no clicks)',
        'fsiot_http_progress' => ':filled of :total checked',
        'fsiot_http_pass' => 'Complete :filled/:total — HTTP 200 + JSON on Serial! Ready for FS-31 when it ships.',
        'fsiot_http_incomplete' => 'Some steps are still unchecked. Recheck browser JSON, Wi-Fi, or Serial 115200.',
        'fsiot_http_done' => 'Done',
        'fsiot_http_todo' => 'Not yet',
""",
    ),
]:
    p = ROOT / lang
    t = p.read_text(encoding="utf-8")
    if "fsiot_http_badge" not in t:
        needle = "        'fsiot_wifi_todo' =>"
        idx = t.find(needle)
        if idx < 0:
            raise SystemExit(f"lang anchor missing in {lang}")
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
if "initFsiotHttpChecklist" not in t:
    t = t.replace(
        "    initFsiotWifiChecklist();\n});",
        "    initFsiotWifiChecklist();\n    initFsiotHttpChecklist();\n});",
        1,
    )
    fn = """
function initFsiotHttpChecklist() {
    initFsiotChecklistWidget({
        h2Id: 'fsiot-http-checklist',
        listId: 'fsiot-http-checklist-items',
        storagePrefix: 'fsiot-cl-100',
        idPrefix: 'fsiot-http',
        minItems: 10,
        labels: {
            badge: @js(__('ui.articles.fsiot_http_badge')),
            hint: @js(__('ui.articles.fsiot_http_hint')),
            check: @js(__('ui.articles.fsiot_http_check')),
            retry: @js(__('ui.articles.fsiot_http_retry')),
            paper: @js(__('ui.articles.fsiot_http_paper')),
            progress: @js(__('ui.articles.fsiot_http_progress')),
            pass: @js(__('ui.articles.fsiot_http_pass')),
            incomplete: @js(__('ui.articles.fsiot_http_incomplete')),
            done: @js(__('ui.articles.fsiot_http_done')),
            todo: @js(__('ui.articles.fsiot_http_todo')),
        },
    });
}

"""
    t = t.replace(
        "function initFsiotWifiChecklist() {",
        fn + "function initFsiotWifiChecklist() {",
        1,
    )
    p.write_text(t, encoding="utf-8")
    print("blade ok")
else:
    print("blade exists")

# --- deploy.yml ---
p = ROOT / ".github/workflows/deploy.yml"
t = p.read_text(encoding="utf-8")
fs30 = (
    "images/fsiot/fs30-cover-http.jpg images/fsiot/fs30-cover-http.webp "
    "images/fsiot/fs30-tools-order.png images/fsiot/fs30-http-core.png "
    "images/fsiot/fs30-http-get.png images/fsiot/fs30-json-anatomy.png "
    "images/fsiot/fs30-status-codes.png images/fsiot/fs30-success-serial.png "
    "images/fsiot/fs30-browser-json.png"
)
if "fs30-http-get.png" not in t:
    old = (
        "images/fsiot/fs29-cover-wifi.jpg images/fsiot/fs29-cover-wifi.webp "
        "images/fsiot/fs29-tools-ide.png images/fsiot/fs29-wifi-core.png "
        "images/fsiot/fs29-wifi-station.png images/fsiot/fs29-band-2g4.png "
        "images/fsiot/fs29-modul-router.png images/fsiot/fs29-success-serial.png \\"
    )
    new = old.replace(
        "images/fsiot/fs29-success-serial.png \\",
        "images/fsiot/fs29-success-serial.png " + fs30 + " \\",
    )
    if old not in t:
        raise SystemExit("ftp allowlist anchor missing")
    t = t.replace(old, new, 1)
    print("ftp allowlist ok")
else:
    print("ftp allowlist exists")

if "id: curl100" not in t:
    curl100 = r'''
      - name: Upload critical #100 files via curl (pre-sync)
        id: curl100
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
          upload "database/seeders/Article100Seeder.php" "${APP}database/seeders/Article100Seeder.php" || failed=1
          upload "app/Http/Controllers/DeployController.php" "${APP}app/Http/Controllers/DeployController.php" || failed=1
          for img in fs30-cover-http.jpg fs30-cover-http.webp fs30-tools-order.png fs30-http-core.png \
            fs30-http-get.png fs30-json-anatomy.png fs30-status-codes.png fs30-success-serial.png; do
            upload "public/images/fsiot/$img" "${APP}public/images/fsiot/$img" || failed=1
            upload "public/images/fsiot/$img" "${PUB}images/fsiot/$img" || failed=1
          done
          if [ "$failed" -ne 0 ]; then
            echo "::warning::Some critical #100 curl uploads failed"
            exit 1
          fi
          echo "Critical #100 curl uploads OK"

'''
    marker = "          echo \"Critical #99 curl uploads OK\"\n\n      - name: Deploy build assets via FTP"
    if marker not in t:
        raise SystemExit("curl99 marker missing")
    t = t.replace(
        "          echo \"Critical #99 curl uploads OK\"\n\n      - name: Deploy build assets via FTP",
        "          echo \"Critical #99 curl uploads OK\"\n" + curl100 + "      - name: Deploy build assets via FTP",
        1,
    )
    t = t.replace(
        'if [ "${{ steps.curl98.outcome }}" = "success" ] || [ "${{ steps.curl99.outcome }}" = "success" ]; then\n            echo "::warning::Build asset FTP failed after retries — continuing because critical #98/#99 curl upload succeeded"',
        'if [ "${{ steps.curl98.outcome }}" = "success" ] || [ "${{ steps.curl99.outcome }}" = "success" ] || [ "${{ steps.curl100.outcome }}" = "success" ]; then\n            echo "::warning::Build asset FTP failed after retries — continuing because critical #98/#99/#100 curl upload succeeded"',
    )
    t = t.replace(
        'if [ "${{ steps.curl98.outcome }}" = "success" ] || [ "${{ steps.curl99.outcome }}" = "success" ]; then\n            echo "::warning::Main SamKirkland FTP failed after retries — continuing because critical #98/#99 curl upload succeeded"',
        'if [ "${{ steps.curl98.outcome }}" = "success" ] || [ "${{ steps.curl99.outcome }}" = "success" ] || [ "${{ steps.curl100.outcome }}" = "success" ]; then\n            echo "::warning::Main SamKirkland FTP failed after retries — continuing because critical #98/#99/#100 curl upload succeeded"',
    )
    print("curl100 ok")
else:
    print("curl100 exists")

if "seed-article-100-draft" not in t:
    seed100 = r'''
      - name: Seed article 100 draft via deploy hook (required, pre-launch B)
        if: always() && !cancelled()
        env:
          DEPLOY_HOOK_TOKEN: ${{ secrets.DEPLOY_HOOK_TOKEN }}
        run: |
          set -euo pipefail
          code="000"
          for i in 1 2 3 4 5 6 7 8; do
            code=$(curl -sS -o /tmp/seed100.out -w "%{http_code}" -H "X-Deploy-Token: $DEPLOY_HOOK_TOKEN" \
              "https://kodingindonesia.com/deploy/seed-article-100-draft" || echo "000")
            echo "seed-article-100-draft HTTP $code"
            if [ "$code" = "200" ]; then
              cat /tmp/seed100.out || true
              break
            fi
            sleep $((i * 5))
          done
          if [ "$code" != "200" ]; then
            echo "::error::seed-article-100-draft failed after retries (last HTTP $code)"
            cat /tmp/seed100.out || true
            exit 1
          fi
          pub=$(curl -sS -o /dev/null -w "%{http_code}" "https://kodingindonesia.com/artikel/fullstack-iot-http-json" || echo "000")
          echo "public article 100 HTTP $pub (expect 404 draft)"
          if [ "$pub" != "404" ]; then
            echo "::error::article 100 must stay unpublished (got HTTP $pub)"
            exit 1
          fi

'''
    anchor = "      - name: Publish article 69 via deploy hook (required)"
    if anchor not in t:
        raise SystemExit("publish 69 anchor missing")
    # insert after seed 99 block — find seed 99 then publish 69
    if "Seed article 99 draft" in t:
        t = t.replace(anchor, seed100 + anchor, 1)
    else:
        t = t.replace(anchor, seed100 + anchor, 1)
    print("seed100 step ok")
else:
    print("seed100 step exists")

p.write_text(t, encoding="utf-8")
print("wire-article100 done")
