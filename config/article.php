<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Article preview link TTL (days)
    |--------------------------------------------------------------------------
    |
    | Signed preview URLs expire after this many days. Contributors and admins
    | can regenerate a fresh link from the Filament article edit page.
    |
    */

    'preview_ttl_days' => (int) env('ARTICLE_PREVIEW_TTL_DAYS', 7),

];
