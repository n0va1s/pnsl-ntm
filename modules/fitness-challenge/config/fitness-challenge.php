<?php

return [
    'enabled' => env('FEATURE_CHALLENGES_ADDON', env('FEATURE_FITNESS_CHALLENGE', false)),
    'media' => [
        'disk' => env('CHALLENGES_MEDIA_DISK', env('FITNESS_CHALLENGE_MEDIA_DISK', env('FILESYSTEM_DISK', 'local'))),
        'require_manual_review' => env('CHALLENGES_REQUIRE_MANUAL_MEDIA_REVIEW', env('FITNESS_CHALLENGE_REQUIRE_MANUAL_MEDIA_REVIEW', true)),
        'max_image_kb' => 10240,
        'max_video_kb' => 51200,
        'allowed_image_mimes' => ['image/jpeg', 'image/png', 'image/webp'],
        'allowed_video_mimes' => ['video/mp4'],
        'blocked_terms' => [
            'nude',
            'nudes',
            'nudez',
            'pelado',
            'pelada',
            'porn',
            'porno',
            'pornografia',
            'sexo',
            'sex',
            'xxx',
            'erotico',
            'erótico',
        ],
    ],
];
