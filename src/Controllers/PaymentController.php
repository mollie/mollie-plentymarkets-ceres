<?php

namespace Mollie\Controllers;

use Mollie\Services\OrderUpdateService;
use Mollie\Services\OrderService;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;

/**
 * Class PaymentController
 * @package Mollie\Controllers
 */
class PaymentController extends Controller
{
    /**
     * @param OrderService $orderService
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function init(OrderService $orderService, Request $request)
    {
        return $orderService->prepareOrder($request->get('orderId'));
    }

    /**
     * @param OrderUpdateService $orderUpdateService
     * @param AuthHelper $authHelper
     * @param Request $request
     * @return string
     */
    public function webHook(OrderUpdateService $orderUpdateService, AuthHelper $authHelper, Request $request)
    {
        $authHelper->processUnguarded(function () use ($orderUpdateService, $request) {
            $orderUpdateService->updatePlentyOrder($request->get('id'));
        });
        return 'OK';
    }
}