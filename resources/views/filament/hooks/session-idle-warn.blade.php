@php
    $lifetimeMinutes = (int) config('session.lifetime', 120);
    $warnAfterMs = max(60_000, ($lifetimeMinutes - 20) * 60_000);
@endphp
<script>
(function () {
    var warnAfter = {{ $warnAfterMs }};
    var lifetimeMin = {{ $lifetimeMinutes }};
    var warned = false;
    var timer = null;

    function reset() {
        warned = false;
        if (timer) clearTimeout(timer);
        timer = setTimeout(showWarn, warnAfter);
    }

    function showWarn() {
        if (warned) return;
        warned = true;
        var bar = document.createElement('div');
        bar.id = 'kindo-session-idle-warn';
        bar.setAttribute('role', 'status');
        bar.style.cssText = 'position:fixed;bottom:1rem;right:1rem;z-index:9999;max-width:22rem;padding:0.85rem 1rem;background:#FFD600;color:#000;border:2px solid #000;box-shadow:4px 4px 0 #000;font:600 0.875rem/1.4 system-ui,sans-serif;';
        bar.innerHTML = 'Sesi akan kedaluwarsa dalam ~20 menit (total ' + lifetimeMin + ' menit). Gerakkan mouse atau tekan tombol untuk tetap aktif. <button type="button" style="margin-left:0.5rem;font-weight:800;text-decoration:underline;background:none;border:0;cursor:pointer;">Tutup</button>';
        bar.querySelector('button').addEventListener('click', function () { bar.remove(); });
        document.body.appendChild(bar);
    }

    ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(function (evt) {
        document.addEventListener(evt, reset, { passive: true });
    });
    reset();
})();
</script>
