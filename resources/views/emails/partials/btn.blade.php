@php
    $bg = $bg ?? '#2979FF';
    $margin = $margin ?? '16px 0';
@endphp
<a href="{{ $href }}"
   style="display:inline-block;margin:{{ $margin }};padding:12px 20px;background-color:{{ $bg }};color:#ffffff !important;-webkit-text-fill-color:#ffffff;text-decoration:none !important;font-weight:700;border:2px solid #000000;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.4;">
    <span style="color:#ffffff !important;-webkit-text-fill-color:#ffffff;text-decoration:none !important;">{{ $label }}</span>
</a>
