@php($gaId = config('services.google_analytics.measurement_id'))
@if ($gaId && str_starts_with($gaId, 'G-'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $gaId }}', { anonymize_ip: true });
    </script>
@endif
