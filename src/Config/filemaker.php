<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Claris Filemaker login credentials
    |--------------------------------------------------------------------------
    |
    | Here we specify the login credentials to connect to a
    | given Claris Filemaker solution using the REST API
    |
    */
    'host'     => env('FILEMAKER_HOST', 'localhost'),
    'database' => env('FILEMAKER_DATABASE', 'database'),
    'user'     => env('FILEMAKER_USER', 'fmuser'),
    'password' => env('FILEMAKER_PASSWORD', 'fmpass'),
];
