# -*- coding: utf-8 -*-
"""Wire Gate BUILDER infrastructure: route, DeployController, lang, blade, deploy.yml."""
from pathlib import Path

ROOT = Path(".")

# --- route ---
p = ROOT / "routes/web.php"
t = p.read_text(encoding="utf-8")
if "seed-gate-builder-draft" not in t:
    old = """Route::get('/deploy/seed-article-100-draft', [DeployController::class, 'seedArticle100Draft'])
    ->middleware('throttle:120,1')
    ->name('deploy.seed-article-100-draft');"""
    new = old + """

Route::get('/deploy/seed-gate-builder-draft', [DeployController::class, 'seedGateBuilderDraft'])
    ->middleware('throttle:120,1')
    ->name('deploy.seed-gate-builder-draft');"""
    if old not in t:
        raise SystemExit("route anchor missing")
    p.write_text(t.replace(old, new, 1), encoding="utf-8")
    print("route ok")
else:
    print("route exists")

# --- DeployController ---
p = ROOT / "app/Http/Controllers/DeployController.php"
t = p.read_text(encoding="utf-8")
if "seedGateBuilderDraft" not in t:
    method = r'''
    public function seedGateBuilderDraft(): Response
    {
        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\ArticleGateBuilderSeeder',
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response('Gate BUILDER draft seed failed: '.$e->getMessage(), 500);
        }

        $slug = 'fullstack-iot-gate-builder';
        $article = Article::where('slug', $slug)->first();
        if (! $article) {
            report(new \RuntimeException('Gate BUILDER missing after draft seed.'));

            return response('Gate BUILDER not found after draft seed', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Gate BUILDER refused to stay draft after seed.'));

            return response('Gate BUILDER must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Gate BUILDER unexpectedly visible via published() scope.'));

            return response('Gate BUILDER leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            'Gate BUILDER (ini)',
            'CONNECTED',
            'fsiot-kuis-matching',
            'fsiot-kuis-kunci',
            'fsiot-gate-builder-checklist',
            '12/15',
            'Tidak perlu hari ini',
            'Cara pakai artikel ini',
            'Cara menguji',
            'FS-28',
            'FS-29',
            '/belajar/fullstack-iot',
            'Analogi:',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Buka browser',
            'Histeresis',
            'fs-gate-builder-criteria.png',
            'Gambar utama',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Gate BUILDER body missing: '.implode(', ', $missingBody)));

            return response('Gate BUILDER body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            return response('Gate BUILDER EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            'BUILDER gate (this article)',
            'CONNECTED',
            'fsiot-kuis-matching',
            'fsiot-kuis-kunci',
            'fsiot-gate-builder-checklist',
            '12/15',
            'Not needed today',
            'How to use this article',
            'How to test',
            'FS-28',
            'FS-29',
            'Analogy:',
            'In short:',
            'Common mistakes',
            'Open a browser',
            'Hysteresis',
            'Main figure',
            'fs-gate-builder-criteria.png',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            return response('Gate BUILDER EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Gate BUILDER seeded as draft (pre-launch B)', 200);
    }

'''
    anchor = "        return response('Article 100 seeded as draft (pre-launch B)', 200);\n    }"
    if anchor not in t:
        raise SystemExit("DeployController 100 anchor missing")
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
        'fsiot_gate_builder_badge' => 'Checklist Gate BUILDER',
        'fsiot_gate_builder_hint' => 'Centang setelah kuis ≥12/15 + foto wiring. Target: 10/10.',
        'fsiot_gate_builder_check' => 'Cek kelengkapan',
        'fsiot_gate_builder_retry' => 'Kosongkan lagi',
        'fsiot_gate_builder_paper' => 'Versi catatan (kertas / tanpa klik)',
        'fsiot_gate_builder_progress' => ':filled dari :total tercentang',
        'fsiot_gate_builder_pass' => 'Lengkap :filled/:total — Gate BUILDER lulus! Siap CONNECTED / FS-29 saat terbit.',
        'fsiot_gate_builder_incomplete' => 'Masih ada langkah yang belum dicentang. Cek skor kuis (≥12/15) atau foto wiring.',
        'fsiot_gate_builder_done' => 'Selesai',
        'fsiot_gate_builder_todo' => 'Belum',
""",
    ),
    (
        "lang/en/ui.php",
        """
        'fsiot_gate_builder_badge' => 'BUILDER gate checklist',
        'fsiot_gate_builder_hint' => 'Tick after quiz ≥12/15 + wiring photo. Target: 10/10.',
        'fsiot_gate_builder_check' => 'Check completeness',
        'fsiot_gate_builder_retry' => 'Clear again',
        'fsiot_gate_builder_paper' => 'Paper version (no clicks)',
        'fsiot_gate_builder_progress' => ':filled of :total checked',
        'fsiot_gate_builder_pass' => 'Complete :filled/:total — BUILDER gate passed! Ready for CONNECTED / FS-29 when it ships.',
        'fsiot_gate_builder_incomplete' => 'Some steps are still unchecked. Recheck quiz score (≥12/15) or wiring photo.',
        'fsiot_gate_builder_done' => 'Done',
        'fsiot_gate_builder_todo' => 'Not yet',
""",
    ),
]:
    p = ROOT / lang
    t = p.read_text(encoding="utf-8")
    if "fsiot_gate_builder_badge" not in t:
        needle = "        'fsiot_http_todo' =>"
        idx = t.find(needle)
        if idx < 0:
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

# Soft update i2c pass / wifi — point at gate
for lang, old, new in [
    (
        "lang/id/ui.php",
        "'fsiot_i2c_pass' => 'Lengkap :filled/:total — I2C hidup di layar! Siap gate BUILDER / FS-29 saat terbit.',",
        "'fsiot_i2c_pass' => 'Lengkap :filled/:total — I2C hidup di layar! Lanjut Gate BUILDER (kuis naik fase) saat terbit.',",
    ),
    (
        "lang/en/ui.php",
        "'fsiot_i2c_pass' => 'Complete :filled/:total — I2C is live on screen! Ready for the BUILDER gate / FS-29 when it ships.',",
        "'fsiot_i2c_pass' => 'Complete :filled/:total — I2C is live on screen! Next: BUILDER gate (phase-up quiz) when it ships.',",
    ),
]:
    p = ROOT / lang
    t = p.read_text(encoding="utf-8")
    if old in t:
        p.write_text(t.replace(old, new, 1), encoding="utf-8")
        print(f"{lang} i2c_pass updated")
    else:
        print(f"{lang} i2c_pass skip")

# --- blade ---
p = ROOT / "resources/views/articles/show.blade.php"
t = p.read_text(encoding="utf-8")
if "initFsiotGateBuilderChecklist" not in t:
    t = t.replace(
        "    initFsiotHttpChecklist();\n});",
        "    initFsiotHttpChecklist();\n    initFsiotGateBuilderChecklist();\n});",
        1,
    )
    fn = """
function initFsiotGateBuilderChecklist() {
    initFsiotChecklistWidget({
        h2Id: 'fsiot-gate-builder-checklist',
        listId: 'fsiot-gate-builder-checklist-items',
        storagePrefix: 'fsiot-cl-gate-builder',
        idPrefix: 'fsiot-gate-builder',
        minItems: 10,
        labels: {
            badge: @js(__('ui.articles.fsiot_gate_builder_badge')),
            hint: @js(__('ui.articles.fsiot_gate_builder_hint')),
            check: @js(__('ui.articles.fsiot_gate_builder_check')),
            retry: @js(__('ui.articles.fsiot_gate_builder_retry')),
            paper: @js(__('ui.articles.fsiot_gate_builder_paper')),
            progress: @js(__('ui.articles.fsiot_gate_builder_progress')),
            pass: @js(__('ui.articles.fsiot_gate_builder_pass')),
            incomplete: @js(__('ui.articles.fsiot_gate_builder_incomplete')),
            done: @js(__('ui.articles.fsiot_gate_builder_done')),
            todo: @js(__('ui.articles.fsiot_gate_builder_todo')),
        },
    });
}

"""
    t = t.replace(
        "function initFsiotHttpChecklist() {",
        fn + "function initFsiotHttpChecklist() {",
        1,
    )
    p.write_text(t, encoding="utf-8")
    print("blade ok")
else:
    print("blade exists")

# --- deploy.yml ---
p = ROOT / ".github/workflows/deploy.yml"
t = p.read_text(encoding="utf-8")
gate_assets = (
    "images/fsiot/fs-gate-builder-cover.jpg images/fsiot/fs-gate-builder-cover.webp "
    "images/fsiot/fs-gate-builder-tools.png images/fsiot/fs-gate-builder-criteria.png "
    "images/fsiot/fs-gate-builder-success.png"
)
if "fs-gate-builder-criteria.png" not in t:
    old = "images/fsiot/fs30-browser-json.png \\"
    if old not in t:
        old = "images/fsiot/fs30-success-serial.png images/fsiot/fs30-browser-json.png \\"
    if "fs30-browser-json.png" in t and "fs-gate-builder" not in t:
        t = t.replace(
            "images/fsiot/fs30-browser-json.png \\",
            "images/fsiot/fs30-browser-json.png " + gate_assets + " \\",
            1,
        )
        print("ftp allowlist ok")
    else:
        raise SystemExit("ftp allowlist anchor missing")
else:
    print("ftp allowlist exists")

if "id: curl_gate_builder" not in t:
    curl = r'''
      - name: Upload critical Gate BUILDER files via curl (pre-sync)
        id: curl_gate_builder
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
          upload "database/seeders/ArticleGateBuilderSeeder.php" "${APP}database/seeders/ArticleGateBuilderSeeder.php" || failed=1
          upload "app/Http/Controllers/DeployController.php" "${APP}app/Http/Controllers/DeployController.php" || failed=1
          for img in fs-gate-builder-cover.jpg fs-gate-builder-cover.webp fs-gate-builder-tools.png \
            fs-gate-builder-criteria.png fs-gate-builder-success.png; do
            upload "public/images/fsiot/$img" "${APP}public/images/fsiot/$img" || failed=1
            upload "public/images/fsiot/$img" "${PUB}images/fsiot/$img" || failed=1
          done
          if [ "$failed" -ne 0 ]; then
            echo "::warning::Some critical Gate BUILDER curl uploads failed"
            exit 1
          fi
          echo "Critical Gate BUILDER curl uploads OK"

'''
    marker = "          echo \"Critical #100 curl uploads OK\"\n\n      - name: Deploy build assets via FTP"
    if marker not in t:
        raise SystemExit("curl100 marker missing")
    t = t.replace(
        "          echo \"Critical #100 curl uploads OK\"\n\n      - name: Deploy build assets via FTP",
        "          echo \"Critical #100 curl uploads OK\"\n" + curl + "      - name: Deploy build assets via FTP",
        1,
    )
    # soft-pass includes gate curl
    t = t.replace(
        'if [ "${{ steps.curl98.outcome }}" = "success" ] || [ "${{ steps.curl99.outcome }}" = "success" ] || [ "${{ steps.curl100.outcome }}" = "success" ]; then\n            echo "::warning::Build asset FTP failed after retries — continuing because critical #98/#99/#100 curl upload succeeded"',
        'if [ "${{ steps.curl98.outcome }}" = "success" ] || [ "${{ steps.curl99.outcome }}" = "success" ] || [ "${{ steps.curl100.outcome }}" = "success" ] || [ "${{ steps.curl_gate_builder.outcome }}" = "success" ]; then\n            echo "::warning::Build asset FTP failed after retries — continuing because critical #98/#99/#100/gate curl upload succeeded"',
    )
    t = t.replace(
        'if [ "${{ steps.curl98.outcome }}" = "success" ] || [ "${{ steps.curl99.outcome }}" = "success" ] || [ "${{ steps.curl100.outcome }}" = "success" ]; then\n            echo "::warning::Main SamKirkland FTP failed after retries — continuing because critical #98/#99/#100 curl upload succeeded"',
        'if [ "${{ steps.curl98.outcome }}" = "success" ] || [ "${{ steps.curl99.outcome }}" = "success" ] || [ "${{ steps.curl100.outcome }}" = "success" ] || [ "${{ steps.curl_gate_builder.outcome }}" = "success" ]; then\n            echo "::warning::Main SamKirkland FTP failed after retries — continuing because critical #98/#99/#100/gate curl upload succeeded"',
    )
    print("curl_gate_builder ok")
else:
    print("curl_gate_builder exists")

if "seed-gate-builder-draft" not in t:
    seed = r'''
      - name: Seed Gate BUILDER draft via deploy hook (required, pre-launch B)
        if: always() && !cancelled()
        env:
          DEPLOY_HOOK_TOKEN: ${{ secrets.DEPLOY_HOOK_TOKEN }}
        run: |
          set -euo pipefail
          code="000"
          for i in 1 2 3 4 5 6 7 8; do
            code=$(curl -sS -o /tmp/seed_gate_builder.out -w "%{http_code}" -H "X-Deploy-Token: $DEPLOY_HOOK_TOKEN" \
              "https://kodingindonesia.com/deploy/seed-gate-builder-draft" || echo "000")
            echo "seed-gate-builder-draft HTTP $code"
            if [ "$code" = "200" ]; then
              cat /tmp/seed_gate_builder.out || true
              break
            fi
            sleep $((i * 5))
          done
          if [ "$code" != "200" ]; then
            echo "::error::seed-gate-builder-draft failed after retries (last HTTP $code)"
            cat /tmp/seed_gate_builder.out || true
            exit 1
          fi
          pub=$(curl -sS -o /dev/null -w "%{http_code}" "https://kodingindonesia.com/artikel/fullstack-iot-gate-builder" || echo "000")
          echo "public Gate BUILDER HTTP $pub (expect 404 draft)"
          if [ "$pub" != "404" ]; then
            echo "::error::Gate BUILDER must stay unpublished (got HTTP $pub)"
            exit 1
          fi

'''
    anchor = "      - name: Publish article 69 via deploy hook (required)"
    if anchor not in t:
        raise SystemExit("publish 69 anchor missing")
    t = t.replace(anchor, seed + anchor, 1)
    print("seed gate step ok")
else:
    print("seed gate step exists")

p.write_text(t, encoding="utf-8")
print("wire-gate-builder done")
