<?php

return [

    /*
     * Chemins sur lesquels CORS s'applique
     */
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',         // Nuxt.js en développement
        'http://localhost:3001',         // Port alternatif Nuxt
        'https://Kirefraistogo.com',     // Production
        'https://www.Kirefraistogo.com', // Production www
        'https://www.kirifrais.com', // Production www
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    /*
     * IMPORTANT : true obligatoire pour que les cookies/tokens fonctionnent
     */
    'supports_credentials' => true,

];
