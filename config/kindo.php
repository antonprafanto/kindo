<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Trakteer — dukungan / tip
    |--------------------------------------------------------------------------
    */
    'trakteer_tip_url' => env('TRAKTEER_TIP_URL', 'https://trakteer.id/limitless7/tip'),

    /*
    |--------------------------------------------------------------------------
    | Full Stack IoT path — public unlock
    |--------------------------------------------------------------------------
    |
    | false (default, pre-launch B): guests see Coming soon; admin (login)
    | sees the full path page for UI/UX review.
    | true: everyone sees the full path page (rilis jalur).
    |
    */
    'fsiot_public' => (bool) env('FSIOT_PUBLIC', false),

];
