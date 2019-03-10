<?php

namespace Mollie\Helpers;

use Plenty\Modules\Account\Address\Models\Address;

/**
 * Class LocaleHelper
 * @package Mollie\Helpers
 */
class LocaleHelper
{
    /**
     * @param string $lang
     * @param Address $address
     * @return string
     */
    public static function buildLocale($lang, Address $address = null)
    {
        if (strpos($lang, '_') !== false) {
            return $lang;
        }

        $matrix = [
            'en' => [
                'en_US'
            ],

            'nl' => [
                'nl_NL',
                'nl_BE'
            ],

            'fr' => [
                'fr_FR',
                'fr_BE'
            ],

            'de' => [
                'de_DE',
                'de_AT',
                'de_CH'
            ],

            'es' => ['es_ES'],
            'ca' => ['ca_ES'],
            'pt' => ['pt_PT'],
            'it' => ['it_IT'],
            'nb' => ['nb_NO'],
            'sv' => ['sv_SE'],
            'fi' => ['fi_FI'],
            'da' => ['da_DK'],
            'is' => ['is_IS'],
            'hu' => ['hu_HU'],
            'pl' => ['pl_PL'],
            'lv' => ['lv_LV'],
            'lt' => ['lt_LT'],
        ];

        //Fallback to en
        if (empty($lang) || !array_key_exists($lang, $matrix)) {
            $lang = 'en';
        }

        $locale = '';
        if ($address instanceof Address) {
            $locale = strtolower($lang) . '_' . strtoupper($address->country->isoCode2);
        }

        if (!in_array($locale, $matrix[$lang])) {
            $locale = $matrix[$lang][0];
        }

        return $locale;
    }
}