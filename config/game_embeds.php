<?php

return [
    /*
     * Only page elements deliberately marked by the owner of a SAME-ORIGIN game
     * may be removed. Never inject removal code into third-party content.
     */
    'owned_ad_selectors' => [
        '[data-owned-ad]',
        '.owned-game-ad',
    ],

    /*
     * Explicit, provider-documented ad-free integrations ONLY.
     *
     * Add an entry only after obtaining permission / verifying the contract and
     * its official query parameter. No third-party provider is presumed to offer
     * an ad-free mode, including the existing GameMonetize catalog.
     *
     * 'approved_ad_free_providers' => [
     *     'official.example.test' => [
     *         'verified' => true,
     *         'parameter' => 'official_ad_mode',
     *         'value' => 'off',
     *     ],
     * ],
     */
    'approved_ad_free_providers' => [],
];
