<?php

return [

    /*
    |--------------------------------------------------------------------------
    | URL publique du site (liens absolus du flux Google Merchant Center)
    |--------------------------------------------------------------------------
    | Sert à construire les <link> et <image_link> du flux. Doit être le
    | domaine de production en https, indépendamment de APP_URL (qui peut
    | rester http://localhost en développement).
    */
    'base_url' => rtrim(env('FEED_BASE_URL', env('APP_URL', 'https://dfpinteriores.com')), '/'),

    /*
    |--------------------------------------------------------------------------
    | Règles de sélection (Google Merchant Center — marché Portugal)
    |--------------------------------------------------------------------------
    */
    'min_price' => 80.00,   // prix TTC minimum pour entrer dans le flux
    'free_shipping' => true,    // livraison gratuite pour tout le Portugal
    'target_items' => 980,     // nombre visé (jamais dépassé, jamais compensé)

    'handling_time' => ['min' => 1, 'max' => 2], // jours ouvrés de préparation
    'transit_time' => ['min' => 1, 'max' => 2], // jours ouvrés de transport

    /*
    | Marques fabricant réelles à conserver telles quelles. Toute autre valeur
    | (ancien nom scrappé « Feira dos Sofás », vide, générique) est remplacée
    | par le nom de la boutique.
    */
    'store_brand' => 'DFP Interiores',
    'real_brands' => [
        'COLMED', 'MD', 'FUNDOS MD', 'SANTI D`ITALIA', 'MOLAFLEX', 'EMMA',
        'TEMPUR', 'ECOSLEEP', 'SEALY', 'SLEEP PRO', 'PIKOLIN',
    ],
];
