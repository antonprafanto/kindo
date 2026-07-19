@php($gaId = config('services.google_analytics.measurement_id'))
@if ($gaId && str_starts_with($gaId, 'G-'))
<script>
(function () {
    var KEY = 'kindo-ga-consent';
    var gaId = @json($gaId);

    function loadGa() {
        if (window.__kindoGaLoaded) return;
        window.__kindoGaLoaded = true;
        var s = document.createElement('script');
        s.async = true;
        s.src = 'https://www.googletagmanager.com/gtag/js?id=' + gaId;
        document.head.appendChild(s);
        window.dataLayer = window.dataLayer || [];
        function gtag(){ dataLayer.push(arguments); }
        window.gtag = gtag;
        gtag('js', new Date());
        gtag('config', gaId, { anonymize_ip: true });
    }

    function hideBar() {
        var bar = document.getElementById('ga-consent-bar');
        if (bar) bar.remove();
    }

    try {
        if (localStorage.getItem(KEY) === '1') {
            loadGa();
            return;
        }
        if (localStorage.getItem(KEY) === '0') {
            return;
        }
    } catch (e) {}

    document.addEventListener('DOMContentLoaded', function () {
        var bar = document.createElement('div');
        bar.id = 'ga-consent-bar';
        bar.setAttribute('role', 'dialog');
        bar.setAttribute('aria-label', 'Persetujuan analitik');
        bar.className = 'fixed bottom-0 inset-x-0 z-[90] border-t-2 border-black theme-paper p-4';
        bar.style.boxShadow = '0 -4px 0 #000';
        bar.innerHTML =
            '<div class="max-w-6xl mx-auto flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-6">' +
            '<p class="text-sm theme-body flex-1">Kami memakai Google Analytics anonim untuk memahami kunjungan. ' +
            '<a href="/kebijakan-privasi" class="underline font-semibold">Kebijakan Privasi</a></p>' +
            '<div class="flex gap-2 flex-shrink-0">' +
            '<button type="button" id="ga-consent-reject" class="btn-brutal btn-outline px-4 py-2 text-xs">Tolak</button>' +
            '<button type="button" id="ga-consent-accept" class="btn-brutal btn-primary px-4 py-2 text-xs">Setuju</button>' +
            '</div></div>';
        document.body.appendChild(bar);

        document.getElementById('ga-consent-accept').addEventListener('click', function () {
            try { localStorage.setItem(KEY, '1'); } catch (e) {}
            hideBar();
            loadGa();
        });
        document.getElementById('ga-consent-reject').addEventListener('click', function () {
            try { localStorage.setItem(KEY, '0'); } catch (e) {}
            hideBar();
        });
    });
})();
</script>
@endif
