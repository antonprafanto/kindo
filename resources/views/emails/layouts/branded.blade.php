<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Koding Indonesia')</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; line-height: 1.6; color: #2D3748; background: #f5f5f0; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border: 2px solid #000; }
        .header { padding: 24px 32px; border-bottom: 2px solid #000; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 800; letter-spacing: -0.01em; }
        .header p { margin: 4px 0 0; font-size: 13px; }
        .body { padding: 32px; font-size: 15px; }
        .footer { background: #2D3748; padding: 16px 32px; border-top: 2px solid #000; color: rgba(255,255,255,0.6); font-size: 12px; }
        .footer a { color: #82B1FF; }
        .field { margin-bottom: 20px; }
        .field-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #718096; margin-bottom: 4px; }
        .field-value { font-size: 15px; color: #2D3748; }
        .message-box { background: #f5f5f0; border: 2px solid #000; padding: 16px; font-size: 15px; line-height: 1.7; white-space: pre-wrap; }
        .divider { border: none; border-top: 2px solid #000; margin: 20px 0; }
        .muted { color: #718096; font-size: 13px; }
        @yield('styles')
    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="background: @yield('header_bg', '#2979FF'); color: @yield('header_color', '#ffffff');">
            <h1 style="color: @yield('header_color', '#ffffff');">@yield('header')</h1>
            @hasSection('subtitle')
            <p style="color: @yield('subtitle_color', 'rgba(255,255,255,0.85)');">@yield('subtitle')</p>
            @endif
        </div>
        <div class="body">
            @yield('content')
        </div>
        <div class="footer">
            @hasSection('footer')
                @yield('footer')
            @else
                <p>Koding Indonesia — Platform edukasi pemrograman berbahasa Indonesia</p>
                <p><a href="https://kodingindonesia.com">kodingindonesia.com</a></p>
            @endif
        </div>
    </div>
</body>
</html>
