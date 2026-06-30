<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Isi — {{ $article->title }}</title>
    <style>
        :root {
            --bg: #0f0f0f;
            --surface: #1a1a2e;
            --ink: #f7f7f5;
            --muted: #a0aec0;
            --primary: #2979FF;
            --border: #000;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", system-ui, sans-serif;
            background: var(--bg);
            color: var(--ink);
            line-height: 1.5;
        }
        .wrap { max-width: 56rem; margin: 0 auto; padding: 1.5rem 1rem 3rem; }
        h1 { font-size: 1.35rem; margin: 0 0 .25rem; }
        .meta { color: var(--muted); font-size: .9rem; margin-bottom: 1rem; }
        .notice {
            background: #1e3a5f;
            border: 2px solid var(--border);
            padding: .75rem 1rem;
            margin-bottom: 1rem;
            font-size: .9rem;
        }
        .success {
            background: #14532d;
            border: 2px solid var(--border);
            padding: .75rem 1rem;
            margin-bottom: 1rem;
            font-size: .9rem;
        }
        .error {
            background: #7f1d1d;
            border: 2px solid var(--border);
            padding: .75rem 1rem;
            margin-bottom: 1rem;
            font-size: .9rem;
        }
        label { display: block; font-weight: 700; margin-bottom: .5rem; }
        textarea {
            width: 100%;
            min-height: 70vh;
            font-family: ui-monospace, "Cascadia Code", Consolas, monospace;
            font-size: 13px;
            line-height: 1.45;
            padding: 1rem;
            border: 2px solid var(--border);
            background: var(--surface);
            color: var(--ink);
            resize: vertical;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
            margin-top: 1rem;
            align-items: center;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            padding: .65rem 1.1rem;
            font-weight: 700;
            font-size: .9rem;
            text-decoration: none;
            border: 2px solid var(--border);
            cursor: pointer;
            color: var(--ink);
            background: var(--surface);
        }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn:hover { filter: brightness(1.08); }
        .hint { color: var(--muted); font-size: .85rem; }
    </style>
</head>
<body>
    <div class="wrap">
        <p class="meta"><a href="{{ $backUrl }}" style="color:var(--primary)">← Kembali ke Edit Artikel</a></p>

        <h1>Edit Isi Artikel</h1>
        <p class="meta">{{ $article->title }}</p>

        <div class="notice">
            Halaman ini menyimpan isi artikel <strong>tanpa Livewire</strong> (payload di-encode) agar tidak diblokir WAF hosting Rumahweb.
            Judul, tag, kategori, dan status tetap disimpan dari halaman Filament.
        </div>

        @if (session('body_saved'))
            <div class="success">Isi artikel berhasil disimpan.</div>
        @endif

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="post" action="{{ route('filament.admin.articles.isi.update', $article) }}" id="body-form">
            @csrf
            <input type="hidden" name="body_b64" id="body_b64">

            <label for="body">HTML isi artikel</label>
            <textarea id="body" name="body_raw">{{ old('body_raw', $article->body) }}</textarea>

            <div class="actions">
                <button type="submit" class="btn btn-primary">Simpan Isi Artikel</button>
                <a href="{{ $backUrl }}" class="btn">Batal</a>
                <span class="hint">Tip: paste dari Word/Google Docs boleh — simpan lalu cek pratinjau di Filament.</span>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('body-form').addEventListener('submit', function (e) {
            const raw = document.getElementById('body').value;
            const bytes = new TextEncoder().encode(raw);
            let binary = '';
            bytes.forEach(function (b) { binary += String.fromCharCode(b); });
            document.getElementById('body_b64').value = btoa(binary);
            document.getElementById('body').removeAttribute('name');
        });
    </script>
</body>
</html>
