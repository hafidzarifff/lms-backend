<?php

return [

    /*
    |--------------------------------------------------------------------------
    | E-Portal SSO (UIKA Bogor)
    |--------------------------------------------------------------------------
    |
    | Kredensial ini didapat dari admin panel E-Portal, menu "App Modules",
    | setelah LMS didaftarkan sebagai SSO Client di sana. Konvensi nama env
    | ini disamakan dengan sub-aplikasi UIKA lain (lihat SSO_INTEGRATION_GUIDE
    | milik E-Portal).
    |
    */

    'base_url' => env('SSO_BASE_URL', 'https://eportal.uika-bogor.ac.id'),

    'client_id' => env('SSO_CLIENT_ID'),
    'client_secret' => env('SSO_CLIENT_SECRET'),
    'module_id' => env('SSO_APP_MODULE_ID'),

];
