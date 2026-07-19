<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Komentar baru menunggu moderasi</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #2D3748; background: #f5f5f0; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border: 2px solid #000;">
        <div style="background: #2979FF; padding: 24px 32px; border-bottom: 2px solid #000;">
            <h1 style="margin: 0; color: #fff; font-size: 20px; font-weight: 800;">Komentar baru</h1>
            <p style="margin: 4px 0 0; color: rgba(255,255,255,.85); font-size: 13px;">Menunggu moderasi</p>
        </div>
        <div style="padding: 32px;">
            <p><strong>Artikel:</strong> {{ $articleTitle }}</p>
            <p><strong>Dari:</strong> {{ $authorName }} &lt;{{ $authorEmail }}&gt;</p>
            <div style="background: #f5f5f0; border: 2px solid #000; padding: 16px; white-space: pre-wrap; margin: 16px 0;">{{ $commentBody }}</div>
            <p><a href="{{ $adminUrl }}" style="display: inline-block; padding: 10px 16px; background: #FFD600; color: #000; font-weight: 700; text-decoration: none; border: 2px solid #000;">Buka di panel →</a></p>
        </div>
    </div>
</body>
</html>
