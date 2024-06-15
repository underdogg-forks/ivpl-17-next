<?php

if ( ! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use Money\Currencies\ISOCurrencies;

/**
 * @return array
 */
function get_currencies(): array
{
    //retrieve the available currencies
    $currencies = new ISOCurrencies();
    $ISOCurrencies = [];
    foreach ($currencies as $currency) {
        $ISOCurrencies[$currency->getCode()] = $currency->getCode();
    }

    return $ISOCurrencies;
}
