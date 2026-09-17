<?php

return [

    /*
    |--------------------------------------------------------------------------
    | URL publique du site (liens absolus du flux Google Merchant Center)
    |--------------------------------------------------------------------------
    */
    'base_url' => rtrim(env('FEED_BASE_URL', env('APP_URL', 'https://dfpinteriores.com')), '/'),

    'feed_token' => env('MERCHANT_FEED_TOKEN', ''),

    'currency' => env('MERCHANT_CURRENCY', 'EUR'),
    'target_country' => env('MERCHANT_TARGET_COUNTRY', 'PT'),
    'store_brand' => env('MERCHANT_DEFAULT_BRAND', 'DFP Interiores'),
    'shipping_price' => (float) env('MERCHANT_SHIPPING_PRICE', 0),
    'return_days' => (int) env('MERCHANT_RETURN_DAYS', 14),

    /*
    |--------------------------------------------------------------------------
    | Règles de sélection (Google Merchant Center — marché Portugal)
    |--------------------------------------------------------------------------
    | Livraison : gratuite dans tout le Portugal.
    | Préparation : 1 jour. Transit : 0–2 jours. Total client : 1–3 jours.
    */
    'min_price' => 80.00,
    'free_shipping' => true,
    'target_items' => 980,

    'handling_time' => [
        'min' => (int) env('MERCHANT_HANDLING_MIN_DAYS', 1),
        'max' => (int) env('MERCHANT_HANDLING_MAX_DAYS', 1),
    ],
    'transit_time' => [
        'min' => (int) env('MERCHANT_SHIPPING_MIN_DAYS', 0),
        'max' => (int) env('MERCHANT_SHIPPING_MAX_DAYS', 2),
    ],

    'real_brands' => [
        'COLMED', 'MD', 'FUNDOS MD', 'SANTI D`ITALIA', 'MOLAFLEX', 'EMMA',
        'TEMPUR', 'ECOSLEEP', 'SEALY', 'SLEEP PRO', 'PIKOLIN',
    ],
];
