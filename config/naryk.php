<?php

return [

    /*
     * The settings table records `news` as the post prefix, and 8000 indexed
     * articles already live under it. Both the route and Post::url() read this
     * value so they cannot drift apart; the admin's Permalinks tab shows what
     * the old site stored but does not move the URLs.
     */
    'post_prefix' => env('NARYK_POST_PREFIX', 'news'),

    'feed' => [
        // "15 материалдан кейін «тағы да» деген кнопка керек"
        'per_page' => 15,
        // "Баннер ортадағы бағанда үшінші материалдан кейін тұрады"
        'banner_after' => 3,
    ],

    /*
     * Category slugs the side columns read from. The expert-opinion category
     * exists; the special-projects one has to be created in the admin panel,
     * and the column stays empty until it is.
     */
    'columns' => [
        'special_projects' => 'arnayy-jobalar',
        'expert_opinions' => 'mamandar-pikiri',
    ],

    'placements' => [
        'feed' => 'home-horizontal',
        'sidebar_top' => 'sidebar-right-top',
        'sidebar_bottom' => 'sidebar-right-bottom',
    ],

    'quotes' => [
        'endpoint' => env('NARYK_QUOTES_URL', 'https://apps.naryk.kz/get-sum'),
        'ttl' => 60,

        /*
         * The keys are the endpoint's own, which is why Freedom is FRHC_KZ:
         * asking for FRHC matched nothing and the ticker quietly dropped it.
         * Anything missing from the response is skipped rather than rendered
         * empty.
         */
        'order' => ['FRHC_KZ', 'KSPI', 'KMGZ', 'HSBK', 'KCEL', 'KZAP', 'KEGC', 'AIRA', 'KZTO', 'CCBN', 'ASBN'],

        /* What the reader sees, where it differs from the endpoint's key. */
        'labels' => ['FRHC_KZ' => 'FRHC'],

        /*
         * Freedom alone is quoted in dollars, so it alone is marked. The brief
         * is explicit that the others stay as they are.
         */
        'currency' => ['FRHC_KZ' => 'USD'],
    ],

];
