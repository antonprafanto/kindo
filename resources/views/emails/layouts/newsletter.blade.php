<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Koding Indonesia' }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #2D3748; background: #f5f5f0; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border: 2px solid #000; }
        .header { background: #2979FF; padding: 24px 32px; border-bottom: 2px solid #000; }
        .header h1 { color: #fff; margin: 0; font-size: 20px; font-weight: 800; }
        .header p { color: rgba(255,255,255,0.85); margin: 4px 0 0; font-size: 13px; }
        .body { padding: 32px; font-size: 15px; }
        .btn { display: inline-block; background: #2979FF; color: #fff !important; text-decoration: none; padding: 14px 28px; font-weight: 700; border: 2px solid #000; box-shadow: 4px 4px 0 #000; margin: 16px 0; }
        .footer { background: #2D3748; padding: 16px 32px; border-top: 2px solid #000; color: rgba(255,255,255,0.6); font-size: 12px; }
        .footer a { color: #82B1FF; }
        .muted { color: #718096; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $headerTitle ?? '📬 Koding Indonesia' }}</h1>
            <p>{{ $headerSubtitle ?? 'Newsletter' }}</p>
        </div>
        <div class="body">
            {{ $slot }}
        </div>
        <div class="footer">
            <p>Koding Indonesia — Platform edukasi pemrograman berbahasa Indonesia</p>
            <p><a href="https://kodingindonesia.com">kodingindonesia.com</a></p>
            @isset($unsubscribeUrl)
            <p style="margin-top:12px;"><a href="{{ $unsubscribeUrl }}">Berhenti berlangganan</a></p>
            @endisset
        </div>
    </div>
</body>
</html>
