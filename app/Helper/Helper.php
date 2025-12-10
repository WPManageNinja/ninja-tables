<?php

namespace NinjaTables\App\Helper;

use NinjaTables\Framework\Support\Arr;

class Helper
{
    public static function isProviderActiveAndMatches($tableArray, $provider)
    {
        $tableProvider = Arr::get($tableArray, 'provider');

        if ($tableProvider !== $provider) {
            return false;
        }

        switch ($provider) {
            case 'wp_woo':
            case 'wp_woo_reviews':
                return defined('WC_PLUGIN_FILE') && WC_PLUGIN_FILE;

            case 'wp_fct':
                return defined('FLUENTCART_VERSION') && FLUENTCART_VERSION;

            case 'fluent-form':
                return defined('FLUENTFORM_VERSION') && FLUENTFORM_VERSION;

            default:
                return false;
        }
    }
}
