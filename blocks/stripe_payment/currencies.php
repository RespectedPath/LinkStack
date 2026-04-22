<?php

// Single source of truth for:
//   - the five pinned-top currencies
//   - the full Stripe-supported currency list
//   - the zero-decimal currency set (Stripe expects amounts in whole
//     units instead of hundredths for these)
//   - a small symbol lookup used by both admin form and public display
//   - the stripe_amount_to_smallest_unit() conversion helper
//
// Required once by both blocks/stripe_payment/handler.php and
// app/Http/Controllers/StripePaymentController.php so the amount
// math stays identical on write and read.

if (!function_exists('stripe_payment_pinned_currencies')) {
    function stripe_payment_pinned_currencies(): array
    {
        // Order matters — this is the pinned order shown at the top of
        // the admin currency dropdown.
        return [
            'usd' => 'US Dollar',
            'eur' => 'Euro',
            'gbp' => 'British Pound',
            'cad' => 'Canadian Dollar',
            'aud' => 'Australian Dollar',
        ];
    }
}

if (!function_exists('stripe_payment_all_currencies')) {
    /**
     * Every currency Stripe supports for Checkout at time of writing.
     * Codes are lowercase (matches Stripe API). Names in English.
     * Kept in alphabetical order by code so the admin dropdown's
     * "all currencies" section is self-sorted.
     */
    function stripe_payment_all_currencies(): array
    {
        return [
            'aed' => 'UAE Dirham',
            'afn' => 'Afghan Afghani',
            'all' => 'Albanian Lek',
            'amd' => 'Armenian Dram',
            'ang' => 'Netherlands Antillean Guilder',
            'aoa' => 'Angolan Kwanza',
            'ars' => 'Argentine Peso',
            'aud' => 'Australian Dollar',
            'awg' => 'Aruban Florin',
            'azn' => 'Azerbaijani Manat',
            'bam' => 'Bosnia-Herzegovina Convertible Mark',
            'bbd' => 'Barbadian Dollar',
            'bdt' => 'Bangladeshi Taka',
            'bgn' => 'Bulgarian Lev',
            'bif' => 'Burundian Franc',
            'bmd' => 'Bermudan Dollar',
            'bnd' => 'Brunei Dollar',
            'bob' => 'Bolivian Boliviano',
            'brl' => 'Brazilian Real',
            'bsd' => 'Bahamian Dollar',
            'bwp' => 'Botswanan Pula',
            'byn' => 'Belarusian Ruble',
            'bzd' => 'Belize Dollar',
            'cad' => 'Canadian Dollar',
            'cdf' => 'Congolese Franc',
            'chf' => 'Swiss Franc',
            'clp' => 'Chilean Peso',
            'cny' => 'Chinese Yuan',
            'cop' => 'Colombian Peso',
            'crc' => 'Costa Rican Colón',
            'cve' => 'Cape Verdean Escudo',
            'czk' => 'Czech Koruna',
            'djf' => 'Djiboutian Franc',
            'dkk' => 'Danish Krone',
            'dop' => 'Dominican Peso',
            'dzd' => 'Algerian Dinar',
            'egp' => 'Egyptian Pound',
            'etb' => 'Ethiopian Birr',
            'eur' => 'Euro',
            'fjd' => 'Fijian Dollar',
            'fkp' => 'Falkland Islands Pound',
            'gbp' => 'British Pound',
            'gel' => 'Georgian Lari',
            'gip' => 'Gibraltar Pound',
            'gmd' => 'Gambian Dalasi',
            'gnf' => 'Guinean Franc',
            'gtq' => 'Guatemalan Quetzal',
            'gyd' => 'Guyanaese Dollar',
            'hkd' => 'Hong Kong Dollar',
            'hnl' => 'Honduran Lempira',
            'htg' => 'Haitian Gourde',
            'huf' => 'Hungarian Forint',
            'idr' => 'Indonesian Rupiah',
            'ils' => 'Israeli New Shekel',
            'inr' => 'Indian Rupee',
            'isk' => 'Icelandic Króna',
            'jmd' => 'Jamaican Dollar',
            'jpy' => 'Japanese Yen',
            'kes' => 'Kenyan Shilling',
            'kgs' => 'Kyrgystani Som',
            'khr' => 'Cambodian Riel',
            'kmf' => 'Comorian Franc',
            'krw' => 'South Korean Won',
            'kyd' => 'Cayman Islands Dollar',
            'kzt' => 'Kazakhstani Tenge',
            'lak' => 'Laotian Kip',
            'lbp' => 'Lebanese Pound',
            'lkr' => 'Sri Lankan Rupee',
            'lrd' => 'Liberian Dollar',
            'lsl' => 'Lesotho Loti',
            'mad' => 'Moroccan Dirham',
            'mdl' => 'Moldovan Leu',
            'mga' => 'Malagasy Ariary',
            'mkd' => 'Macedonian Denar',
            'mmk' => 'Myanma Kyat',
            'mnt' => 'Mongolian Tugrik',
            'mop' => 'Macanese Pataca',
            'mur' => 'Mauritian Rupee',
            'mvr' => 'Maldivian Rufiyaa',
            'mwk' => 'Malawian Kwacha',
            'mxn' => 'Mexican Peso',
            'myr' => 'Malaysian Ringgit',
            'mzn' => 'Mozambican Metical',
            'nad' => 'Namibian Dollar',
            'ngn' => 'Nigerian Naira',
            'nio' => 'Nicaraguan Córdoba',
            'nok' => 'Norwegian Krone',
            'npr' => 'Nepalese Rupee',
            'nzd' => 'New Zealand Dollar',
            'pab' => 'Panamanian Balboa',
            'pen' => 'Peruvian Sol',
            'pgk' => 'Papua New Guinean Kina',
            'php' => 'Philippine Peso',
            'pkr' => 'Pakistani Rupee',
            'pln' => 'Polish Złoty',
            'pyg' => 'Paraguayan Guarani',
            'qar' => 'Qatari Rial',
            'ron' => 'Romanian Leu',
            'rsd' => 'Serbian Dinar',
            'rub' => 'Russian Ruble',
            'rwf' => 'Rwandan Franc',
            'sar' => 'Saudi Riyal',
            'sbd' => 'Solomon Islands Dollar',
            'scr' => 'Seychellois Rupee',
            'sek' => 'Swedish Krona',
            'sgd' => 'Singapore Dollar',
            'shp' => 'Saint Helena Pound',
            'sle' => 'Sierra Leonean Leone',
            'sos' => 'Somali Shilling',
            'srd' => 'Surinamese Dollar',
            'std' => 'São Tomé and Príncipe Dobra',
            'szl' => 'Swazi Lilangeni',
            'thb' => 'Thai Baht',
            'tjs' => 'Tajikistani Somoni',
            'top' => 'Tongan Paʻanga',
            'try' => 'Turkish Lira',
            'ttd' => 'Trinidad and Tobago Dollar',
            'twd' => 'New Taiwan Dollar',
            'tzs' => 'Tanzanian Shilling',
            'uah' => 'Ukrainian Hryvnia',
            'ugx' => 'Ugandan Shilling',
            'usd' => 'US Dollar',
            'uyu' => 'Uruguayan Peso',
            'uzs' => 'Uzbekistan Som',
            'vnd' => 'Vietnamese Dong',
            'vuv' => 'Vanuatu Vatu',
            'wst' => 'Samoan Tala',
            'xaf' => 'Central African CFA Franc',
            'xcd' => 'East Caribbean Dollar',
            'xof' => 'West African CFA Franc',
            'xpf' => 'CFP Franc',
            'yer' => 'Yemeni Rial',
            'zar' => 'South African Rand',
            'zmw' => 'Zambian Kwacha',
        ];
    }
}

if (!function_exists('stripe_payment_zero_decimal_currencies')) {
    /**
     * Stripe currencies where the "amount" field on the API is in
     * whole units rather than hundredths. For these, a JPY ¥100 tip
     * is sent to Stripe as unit_amount: 100, not 10000.
     * Source: https://stripe.com/docs/currencies#zero-decimal
     */
    function stripe_payment_zero_decimal_currencies(): array
    {
        return [
            'bif', 'clp', 'djf', 'gnf', 'jpy', 'kmf', 'krw',
            'mga', 'pyg', 'rwf', 'ugx', 'vnd', 'vuv',
            'xaf', 'xof', 'xpf',
        ];
    }
}

if (!function_exists('stripe_payment_currency_symbol')) {
    /**
     * Displayed next to amount inputs and next to formatted prices on
     * the public page. For currencies without a dedicated glyph we fall
     * back to the uppercase ISO code — "AED 10.00" reads clearly enough.
     */
    function stripe_payment_currency_symbol(string $code): string
    {
        $symbols = [
            'usd' => '$',   'cad' => 'CA$', 'aud' => 'A$',  'nzd' => 'NZ$',
            'eur' => '€',   'gbp' => '£',   'jpy' => '¥',   'cny' => '¥',
            'inr' => '₹',   'krw' => '₩',   'rub' => '₽',   'brl' => 'R$',
            'chf' => 'CHF', 'mxn' => '$',   'sgd' => 'S$',  'hkd' => 'HK$',
            'sek' => 'kr',  'nok' => 'kr',  'dkk' => 'kr',  'pln' => 'zł',
            'zar' => 'R',   'ils' => '₪',   'try' => '₺',   'thb' => '฿',
            'twd' => 'NT$', 'vnd' => '₫',   'php' => '₱',   'idr' => 'Rp',
            'myr' => 'RM',
        ];
        $c = strtolower($code);
        return $symbols[$c] ?? strtoupper($c);
    }
}

if (!function_exists('stripe_payment_amount_to_smallest_unit')) {
    /**
     * Convert a human-entered decimal amount (e.g. 5.00, 12.50) to
     * Stripe's smallest-unit integer for the given currency.
     *   - zero-decimal currencies → round to whole units (1 JPY = 1)
     *   - everything else         → multiply by 100 (5.00 USD = 500)
     */
    function stripe_payment_amount_to_smallest_unit(float $amount, string $currency): int
    {
        $c = strtolower($currency);
        if (in_array($c, stripe_payment_zero_decimal_currencies(), true)) {
            return (int) round($amount);
        }
        return (int) round($amount * 100);
    }
}

if (!function_exists('stripe_payment_format_smallest_unit')) {
    /**
     * Inverse of the above — turn a smallest-unit integer into a
     * human-readable string like "5.00" or "12" (for zero-decimal).
     */
    function stripe_payment_format_smallest_unit(int $smallest, string $currency): string
    {
        $c = strtolower($currency);
        if (in_array($c, stripe_payment_zero_decimal_currencies(), true)) {
            return (string) $smallest;
        }
        return number_format($smallest / 100, 2, '.', '');
    }
}
