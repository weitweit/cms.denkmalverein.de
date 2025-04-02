<?php

require_once __DIR__ . "/../plugins/kirby3-dotenv/global.php";
loadenv([
    "dir" => realpath(__DIR__ . "/../../"),
    "file" => ".env",
]);

return [
    "debug" => true,
    "panel.install" => true,
    "error" => "z-error",
    "languages" => true,
    "frontendUrl" => env("FRONTEND_URL"),
    "auth" => [
        "methods" => ["password", "password-reset"],
    ],
    'api' => [
        'basicAuth' => true,
        'allowInsecure' => true
    ],
    "tobimori.seo.robots.active" => false,
    "tobimori.seo.robots.indicator" => false,
    "tobimori.seo.canonicalBase" => env("FRONTEND_URL"),
    "api.allowImpersonation" => true,
    'bnomei.janitor.secret' => fn() => env('JANITOR_SECRET'),
    "weitweit.blocks.images" => [
        "image-text" => [
            "image" => [
                "width" => 720,
                "height" => 780,
                "manipulation" => "crop",
            ],
        ],
    ],
];
