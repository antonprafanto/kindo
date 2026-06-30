<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upload Cover Artikel — Koding Indonesia</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 32rem; margin: 2rem auto; padding: 0 1rem; line-height: 1.5; }
        label { display: block; font-weight: 600; margin-top: 1rem; }
        input[type="text"], input[type="file"] { width: 100%; margin-top: .25rem; }
        button { margin-top: 1.25rem; padding: .6rem 1rem; font-weight: 600; cursor: pointer; }
        .hint { color: #555; font-size: .9rem; }
    </style>
</head>
<body>
    <h1>Upload Cover Artikel</h1>
    <p class="hint">Form biasa (bukan Livewire) — untuk hosting yang memblokir admin Filament pada artikel berisi kode Arduino/MQTT.</p>

    <form method="post" action="{{ url('/deploy/upload-article-cover') }}?token={{ urlencode(request()->query('token', '')) }}" enctype="multipart/form-data">
        @csrf
        <label for="slug">Slug artikel</label>
        <input type="text" name="slug" id="slug" value="{{ old('slug', $slug) }}" required>

        <label for="cover">Gambar cover (JPG/PNG/WebP, maks. 4 MB, ideal 1200×630)</label>
        <input type="file" name="cover" id="cover" accept="image/jpeg,image/png,image/webp" required>

        <button type="submit">Upload Cover</button>
    </form>
</body>
</html>
