<?php

return [

    /*
    |--------------------------------------------------------------------------
    | E-Portal SSO (UIKA Bogor)
    |--------------------------------------------------------------------------
    |
    | Kredensial ini didapat dari admin panel E-Portal, menu "App Modules",
    | setelah LMS didaftarkan sebagai SSO Client di sana.
    |
    | Endpoint SSO E-Portal ada di belakang WAF (nginx-waf/ModSecurity) dan
    | di-expose lewat prefix "/eportal-api" (bukan "/api" langsung), yang
    | secara internal diteruskan ke rute Laravel E-Portal yang sendirinya
    | sudah berawalan "/api" (lihat eportal-locations.conf) — sehingga path
    | lengkapnya menjadi "/eportal-api/api/sso/...".
    |
    */

    'base_url' => env('SSO_BASE_URL', 'https://eportal.uika-bogor.ac.id/eportal-api'),

    'client_id' => env('SSO_CLIENT_ID'),
    'client_secret' => env('SSO_CLIENT_SECRET'),
    'module_id' => env('SSO_APP_MODULE_ID'),

];
