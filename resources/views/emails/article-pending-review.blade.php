<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Artikel Menunggu Review</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #2D3748; background: #f5f5f0; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border: 2px solid #000; }
        .header { background: #FF7A2F; padding: 24px 32px; border-bottom: 2px solid #000; color: #fff; }
        .header h1 { margin: 0; font-size: 20px; }
        .body { padding: 32px; }
        .field { margin-bottom: 16px; }
        .label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #718096; }
        .value { font-size: 15px; margin-top: 4px; }
        .btn { display: inline-block; margin-top: 16px; padding: 12px 20px; background: #2979FF; color: #fff; text-decoration: none; font-weight: bold; border: 2px solid #000; }
        .footer { background: #2D3748; padding: 16px 32px; color: rgba(255,255,255,0.7); font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📤 Artikel Menunggu Review</h1>
        </div>
        <div class="body">
            <p>Sebuah artikel baru dikirim untuk ditinjau dan dipublikasikan.</p>
            <div class="field">
                <div class="label">Judul</div>
                <div class="value"><strong>{{ $article->title }}</strong></div>
            </div>
            <div class="field">
                <div class="label">Penulis</div>
                <div class="value">{{ $authorName }}</div>
            </div>
            <div class="field">
                <div class="label">Kategori</div>
                <div class="value">{{ $article->category?->name ?? '—' }}</div>
            </div>
            @include('emails.partials.btn', ['href' => $adminUrl, 'label' => 'Tinjau di Panel Admin →'])
            @if(!empty($previewUrl))
            <p style="margin-top: 12px;">
                @include('emails.partials.btn', ['href' => $previewUrl, 'label' => 'Lihat Pratinjau Artikel →'])
            </p>
            <p style="font-size: 12px; color: #718096; margin-top: 8px;">Link pratinjau berlaku {{ config('article.preview_ttl_days', 7) }} hari.</p>
            @endif
        </div>
        <div class="footer">Koding Indonesia — Editorial Workflow</div>
    </div>
</body>
</html>
