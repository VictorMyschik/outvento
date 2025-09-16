<?php

return [
    'path' => [
        'name'       => env('REDOC_PATH_NAME', 'docs'),
        'url'        => env('REDOC_PATH_URL', 'api/docs'),
        'middleware' => [
            //\App\Http\Middleware\BasicAuthentication::class,
        ],
    ],

    'alfred' => [
        'enabled'    => env('REDOC_ALFRED', false),
        'project_id' => env('ALFRED_PROJECT_ID', null),
        'api_key'    => env('ALFRED_API_KEY', null),
    ],

    'openapi' => [
        'path' => env('REDOC_OPENAPI_PATH', 'http://travel.test/docs?api-docs.json')
    ],

    'config' => [
        'title'      => env('APP_NAME', 'ReDoc'),

        'search'     => false,

        /*
        |--------------------------------------------------------------------------
        | Hide Hostname
        |--------------------------------------------------------------------------
        |
        | If set, the protocol and hostname is not shown in the operation definition.
        |
        | Supported: "true", "false"
        |
        */
        'hostname'   => false,

        /*
        |--------------------------------------------------------------------------
        | Hide Loading
        |--------------------------------------------------------------------------
        |
        | Do not show loading animation. Useful for small docs.
        |
        | Supported: "true", "false"
        |
        */
        'loading'    => false,

        /*
        |--------------------------------------------------------------------------
        | Menu Toggle
        |--------------------------------------------------------------------------
        |
        | If true clicking second time on expanded menu item will collapse it.
        |
        | Default: true
        |
        | Supported: "true", "false"
        |
        */
        'menu'       => true,

        /*
        |--------------------------------------------------------------------------
        | Native Scrollbars
        |--------------------------------------------------------------------------
        |
        | Use native scrollbar for sidemenu instead of perfect-scroll
        | (scrolling performance optimization for big specs).
        |
        | Supported: "true", "false"
        |
        */
        'scrollbars' => true,

        /*
        |--------------------------------------------------------------------------
        | Untrusted Spec
        |--------------------------------------------------------------------------
        |
        | if set, the spec is considered untrusted and all HTML/markdown
        | is sanitized to prevent XSS. Disabled by default for performance reasons.
        | Enable this option if you work with untrusted user data!
        |
        | Supported: "true", "false"
        |
        */
        'trust'      => true,
    ]
];
