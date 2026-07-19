<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artikel perlu revisi</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #2D3748; background: #f5f5f0; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border: 2px solid #000;">
        <div style="background: #FFD600; padding: 24px 32px; border-bottom: 2px solid #000;">
            <h1 style="margin: 0; font-size: 20px; font-weight: 800;">Artikel perlu revisi</h1>
            <p style="margin: 4px 0 0; font-size: 13px;">Koding Indonesia</p>
        </div>
        <div style="padding: 32px;">
            <p>Halo <strong>{{ $authorName }}</strong>,</p>
            <p>Artikel <strong>{{ $article->title }}</strong> dikembalikan ke Draft dengan catatan berikut:</p>
            <div style="background: #fff3cd; border: 2px solid #000; padding: 16px; white-space: pre-wrap; margin: 16px 0;">{{ $reviewNotes }}</div>
            <p><a href="{{ $editUrl }}" style="display: inline-block; padding: 10px 16px; background: #2979FF; color: #fff; font-weight: 700; text-decoration: none; border: 2px solid #000;">Buka artikel di panel →</a></p>
            <p style="font-size: 13px; color: #718096;">Setelah diperbaiki, kirim ulang dengan status <strong>Menunggu Review</strong>.</p>
        </div>
    </div>
</body>
</html>
