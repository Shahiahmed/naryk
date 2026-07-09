<?php

return [

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
         * FRHC comes first per the brief, but the endpoint does not return it
         * yet: Freedom Holding trades on Nasdaq, not KASE. Tickers missing
         * from the response are skipped rather than rendered empty.
         */
        'order' => ['FRHC', 'KSPI', 'KMGZ', 'HSBK', 'KCEL', 'KZAP', 'KEGC', 'AIRA', 'KZTO', 'CCBN', 'ASBN'],
    ],

];
