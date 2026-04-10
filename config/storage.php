<?php

return [
    // disk => [paths]
    'users'         => [
        'disk'   => 'users',
        'avatar' => '',
    ],
    'travels'       => [
        'disk'      => 'travels',
        'media'     => 'media',
        'resources' => 'resources',
    ],
    'news_logo'     => 'news_logo',
    'constructor'   => [
        'news'     => 'news',
        'blogs'    => 'blogs',
        'articles' => 'articles',
    ],
    'conversations' => [
        'disk'             => 'conversations',
        'max_upload_files' => 10,
    ],
    'albums'        => [
        'disk'             => 'albums',
        'max_upload_files' => 10,
        'resize'           => [
            'variants' => [
                'preview' => 200,
                'medium'  => 800,
                'large'   => 1600,
            ],
            'quality'  => 80,
        ],
    ],
];
