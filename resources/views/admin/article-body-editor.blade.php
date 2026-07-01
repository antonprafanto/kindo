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
        .hint kbd {
            display: inline-block;
            padding: .1rem .35rem;
            border-radius: 4px;
            border: 1px solid var(--border);
            background: #0f172a;
            font-size: .72rem;
            font-family: inherit;
        }
        .tox-tinymce { border: none !important; }
        .tox-editor-header--sticky {
            background: #111827 !important;
            border-bottom: 1px solid var(--border) !important;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .28);
        }
        /* Floating toolbar saat teks diseleksi (mirip Medium) */
        .tox-pop {
            animation: kindo-quickbar-in .16s ease-out;
        }
        @keyframes kindo-quickbar-in {
            from { opacity: 0; transform: translateY(6px) scale(.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .tox-pop .tox-toolbar,
        .tox-pop .tox-toolbar__overflow,
        .tox-pop .tox-toolbar__primary {
            background: #1f2937 !important;
            border: 1px solid #4b5563 !important;
            border-radius: 10px !important;
            box-shadow: 0 10px 28px rgba(0, 0, 0, .45) !important;
        }
        .tox-pop::before { border-bottom-color: #4b5563 !important; }
        .tox-pop.tox-pop--bottom::before { border-top-color: #4b5563 !important; }
        .tox-pop .tox-tbtn { color: #e5e7eb !important; }
        .tox-pop .tox-tbtn:hover { background: #374151 !important; }
        .tox-pop .tox-tbtn--enabled,
        .tox-pop .tox-tbtn--enabled:hover {
            background: #2979FF !important;
            color: #fff !important;
        }
        @media (max-width: 640px) {
            .wrap { padding-bottom: 5.5rem; }
            .actions {
                position: fixed;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 20;
                margin: 0;
                padding: .75rem 1rem calc(.75rem + env(safe-area-inset-bottom));
                background: rgba(17, 24, 39, .96);
                border-top: 1px solid var(--border);
                backdrop-filter: blur(8px);
            }
            .actions .hint { width: 100%; }
            .tox-pop .tox-tbtn {
                width: 2.5rem !important;
                height: 2.5rem !important;
            }
            .tox-pop .tox-toolbar__group {
                flex-wrap: nowrap;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <p class="meta"><a href="{{ $backUrl }}">← Kembali ke Edit Artikel</a></p>

        <h1>Edit Isi Artikel</h1>
        <p class="meta">{{ $article->title }}</p>

        <div class="notice">
            Editor visual seperti di Filament — judul, tag, kategori &amp; status tetap disimpan dari halaman edit utama.
            Blok teks yang disorot menampilkan menu format mengambang (bold, link, heading, kutipan) tanpa perlu scroll ke toolbar atas.
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
                <span class="hint">Sorot teks untuk menu format mengambang · Toolbar atas tetap saat scroll · <kbd>Ctrl</kbd>+<kbd>S</kbd> / <kbd>⌘</kbd>+<kbd>S</kbd> simpan</span>
            </div>
        </form>
    </div>

    <script>
        const initialHtml = @json(old('body_raw', $article->body));

        tinymce.init({
            selector: '#body',
            height: 560,
            min_height: 480,
            menubar: false,
            statusbar: true,
            branding: false,
            promotion: false,
            license_key: 'gpl',
            language: 'id',
            language_url: 'https://cdn.jsdelivr.net/npm/tinymce-i18n@26.6.8/langs7/id.js',
            skin: 'oxide-dark',
            content_css: 'dark',
            toolbar_sticky: true,
            toolbar_sticky_offset: 0,
            plugins: [
                'autolink', 'lists', 'link', 'table', 'code', 'codesample',
                'fullscreen', 'searchreplace', 'visualblocks', 'wordcount',
                'charmap', 'anchor', 'insertdatetime', 'hr', 'quickbars', 'autoresize',
            ],
            autoresize_bottom_margin: 32,
            autoresize_overflow_padding: 16,
            max_height: 1200,
            quickbars_selection_toolbar: [
                'bold italic underline strikethrough',
                '| quicklink',
                '| h2 h3 h4',
                '| blockquote',
            ].join(' '),
            quickbars_insert_toolbar: false,
            mobile: {
                toolbar_mode: 'scrolling',
                toolbar_sticky: true,
            },
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
                '::selection { background: rgba(41, 121, 255, 0.35); color: #f9fafb; }',
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

        const bodyForm = document.getElementById('body-form');

        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
                if (e.target.closest('.tox-dialog, .tox-dialog-wrap')) {
                    return;
                }
                e.preventDefault();
                bodyForm.requestSubmit();
            }
        });

        bodyForm.addEventListener('submit', function (e) {
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
