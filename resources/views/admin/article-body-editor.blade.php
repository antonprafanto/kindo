<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Isi — {{ $article->title }}</title>
    <script src="https://cdn.jsdelivr.net/npm/tinymce@7.6.1/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        :root {
            --bg: #111827;
            --surface: #1f2937;
            --ink: #f9fafb;
            --muted: #9ca3af;
            --primary: #2979FF;
            --border: #374151;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", system-ui, sans-serif;
            background: var(--bg);
            color: var(--ink);
            line-height: 1.5;
        }
        .wrap { max-width: 58rem; margin: 0 auto; padding: 1.25rem 1rem 2.5rem; }
        h1 { font-size: 1.25rem; font-weight: 700; margin: 0 0 .2rem; }
        .meta { color: var(--muted); font-size: .875rem; margin-bottom: 1rem; }
        .meta a { color: var(--primary); text-decoration: none; font-weight: 600; }
        .meta a:hover { text-decoration: underline; }
        .notice {
            background: #1e3a5f;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: .65rem .9rem;
            margin-bottom: 1rem;
            font-size: .85rem;
            color: #dbeafe;
        }
        .success, .error {
            border-radius: 8px;
            padding: .65rem .9rem;
            margin-bottom: 1rem;
            font-size: .875rem;
        }
        .success { background: #14532d; color: #dcfce7; border: 1px solid #166534; }
        .error { background: #7f1d1d; color: #fecaca; border: 1px solid #991b1b; }
        label { display: block; font-weight: 600; font-size: .875rem; margin-bottom: .5rem; }
        .editor-shell {
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
            background: var(--surface);
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: .6rem;
            margin-top: 1rem;
            align-items: center;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            padding: .55rem 1rem;
            font-weight: 600;
            font-size: .875rem;
            text-decoration: none;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            color: var(--ink);
            background: var(--surface);
            box-shadow: inset 0 0 0 1px var(--border);
        }
        .btn-primary { background: var(--primary); color: #fff; box-shadow: none; }
        .btn:disabled { opacity: .6; cursor: wait; }
        .btn:hover:not(:disabled) { filter: brightness(1.06); }
        .hint { color: var(--muted); font-size: .8rem; }
        .tox-tinymce { border: none !important; }
    </style>
</head>
<body>
    <div class="wrap">
        <p class="meta"><a href="{{ $backUrl }}">← Kembali ke Edit Artikel</a></p>

        <h1>Edit Isi Artikel</h1>
        <p class="meta">{{ $article->title }}</p>

        <div class="notice">
            Editor visual seperti di Filament — judul, tag, kategori &amp; status tetap disimpan dari halaman edit utama.
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

            <label for="body">Isi Artikel</label>
            <div class="editor-shell">
                <textarea id="body">{{ old('body_raw', $article->body) }}</textarea>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary" id="save-btn">Simpan Isi Artikel</button>
                <a href="{{ $backUrl }}" class="btn">Batal</a>
                <span class="hint">Toolbar mirip Filament: heading, bold, list, kutipan, blok kode, tabel, link.</span>
            </div>
        </form>
    </div>

    <script>
        const initialHtml = @json(old('body_raw', $article->body));

        tinymce.init({
            selector: '#body',
            height: 560,
            menubar: false,
            statusbar: true,
            branding: false,
            promotion: false,
            license_key: 'gpl',
            skin: 'oxide-dark',
            content_css: 'dark',
            plugins: [
                'autolink', 'lists', 'link', 'table', 'code', 'codesample',
                'fullscreen', 'searchreplace', 'visualblocks', 'wordcount',
                'charmap', 'anchor', 'insertdatetime', 'hr',
            ],
            toolbar: [
                'undo redo | blocks | bold italic underline strikethrough',
                '| alignleft aligncenter alignright',
                '| bullist numlist | blockquote codesample',
                '| link table hr | code fullscreen',
            ].join(' '),
            block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4',
            codesample_languages: [
                { text: 'Arduino/C++', value: 'cpp' },
                { text: 'Bash', value: 'bash' },
                { text: 'Python', value: 'python' },
                { text: 'JSON', value: 'json' },
                { text: 'HTML/XML', value: 'markup' },
            ],
            content_style: [
                'body { font-family: system-ui, sans-serif; font-size: 16px; line-height: 1.65; color: #e5e7eb; padding: 12px 16px; }',
                'h2,h3,h4 { color: #f9fafb; margin-top: 1.25em; }',
                'blockquote { border-left: 4px solid #2979FF; margin: 1em 0; padding: .5em 1em; background: #1e3a5f; }',
                'code { background: #374151; padding: 2px 6px; border-radius: 4px; font-size: .9em; }',
                'pre { background: #0f172a; padding: 1em; border-radius: 6px; overflow-x: auto; }',
                'a { color: #60a5fa; }',
                'table { border-collapse: collapse; width: 100%; }',
                'td, th { border: 1px solid #4b5563; padding: 8px; }',
            ].join(' '),
            setup(editor) {
                editor.on('init', () => {
                    if (initialHtml) {
                        editor.setContent(initialHtml);
                    }
                });
            },
        });

        document.getElementById('body-form').addEventListener('submit', function (e) {
            e.preventDefault();

            const editor = tinymce.get('body');
            if (!editor) {
                return;
            }

            const raw = editor.getContent();
            if (!raw.replace(/<[^>]*>/g, '').trim()) {
                alert('Isi artikel tidak boleh kosong.');
                return;
            }

            const bytes = new TextEncoder().encode(raw);
            let binary = '';
            bytes.forEach(b => { binary += String.fromCharCode(b); });
            document.getElementById('body_b64').value = btoa(binary);

            const btn = document.getElementById('save-btn');
            btn.disabled = true;
            btn.textContent = 'Menyimpan…';

            e.target.submit();
        });
    </script>
</body>
</html>
