<?php

namespace Mollie\Providers;

use Plenty\Plugin\RouteServiceProvider as PlentyRouteServiceProvider;
use Plenty\Plugin\Routing\ApiRouter;

/**
 * Class RouteServiceProvider
 * @package Mollie\Providers
 */
class RouteServiceProvider extends PlentyRouteServiceProvider
{
    /**
     * @param ApiRouter $apiRouter
     */
    public function map(ApiRouter $apiRouter)
    {
        $apiRouter->version(
            ['v1'],
            ['namespace' => 'Mollie\Controllers', 'middleware' => ['oauth']],
            function ($apiRouter) {

                // Shipping Settings
                $apiRouter->get('mollie/methods', 'SettingsController@getMethods');
                $apiRouter->put('mollie/methods/{id}/settings', 'SettingsController@saveMethodSetting');
            }
        );

        $apiRouter->version(
            ['v1'],
            ['namespace' => 'Mollie\Controllers'],
            function ($apiRouter) {

                //Frontend routes
                $apiRouter->get('mollie/init_payment', 'PaymentController@init');
                $apiRouter->post('mollie/webhook', 'PaymentController@webHook');
            }
        );
    }
}