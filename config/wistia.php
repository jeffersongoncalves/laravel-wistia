<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Wistia API Password
    |--------------------------------------------------------------------------
    |
    | The token used to authenticate Data API calls. Create one at:
    | https://my.wistia.com/account/api
    |
    | When this is null the client falls back to config('services.wistia.token').
    |
    */
    'token' => env('WISTIA_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The number of seconds to wait for a response before giving up.
    |
    */
    'timeout' => (int) env('WISTIA_TIMEOUT', 8),
];
