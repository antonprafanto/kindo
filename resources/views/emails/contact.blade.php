<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Baru dari {{ $senderName }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #2D3748; background: #f5f5f0; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border: 2px solid #000; }
        .header { background: #2979FF; padding: 24px 32px; border-bottom: 2px solid #000; }
        .header h1 { color: #fff; margin: 0; font-size: 20px; font-weight: 800; letter-spacing: -0.01em; }
        .header p { color: rgba(255,255,255,0.85); margin: 4px 0 0; font-size: 13px; }
        .body { padding: 32px; }
        .field { margin-bottom: 20px; }
        .field-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #718096; margin-bottom: 4px; }
        .field-value { font-size: 15px; color: #2D3748; }
        .message-box { background: #f5f5f0; border: 2px solid #000; padding: 16px; font-size: 15px; line-height: 1.7; white-space: pre-wrap; }
        .footer { background: #2D3748; padding: 16px 32px; border-top: 2px solid #000; color: rgba(255,255,255,0.6); font-size: 12px; }
        .footer a { color: #82B1FF; }
        .divider { border: none; border-top: 2px solid #000; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📬 Pesan Baru Masuk</h1>
            <p>Koding Indonesia — Contact Form</p>
        </div>
        <div class="body">
            <div class="field">
                <div class="field-label">Dari</div>
                <div class="field-value"><strong>{{ $senderName }}</strong></div>
            </div>
            <div class="field">
                <div class="field-label">Email Pengirim</div>
                <div class="field-value"><a href="mailto:{{ $senderEmail }}">{{ $senderEmail }}</a></div>
            </div>
            <div class="field">
                <div class="field-label">Subjek</div>
                <div class="field-value"><strong>{{ $contactSubject }}</strong></div>
            </div>
            <hr class="divider">
            <div class="field">
                <div class="field-label">Pesan</div>
                <div class="message-box">{{ $messageBody }}</div>
            </div>
            <hr class="divider">
            <p style="font-size: 13px; color: #718096;">
                Balas email ini untuk langsung merespons ke <strong>{{ $senderEmail }}</strong>.
            </p>
        </div>
        <div class="footer">
            <p>Pesan ini dikirim dari formulir kontak di <a href="https://kodingindonesia.com">kodingindonesia.com</a></p>
        </div>
    </div>
</body>
</html>
