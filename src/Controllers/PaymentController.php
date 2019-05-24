<?php

namespace Mollie\Controllers;

use Mollie\Contracts\TransactionRepositoryContract;
use Mollie\Repositories\TransactionRepository;
use Mollie\Services\OrderService;
use Mollie\Services\OrderUpdateService;
use Mollie\Traits\CanHandleTransactionId;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Log\Loggable;

/**
 * Class PaymentController
 * @package Mollie\Controllers
 */
class PaymentController extends Controller
{
    use CanHandleTransactionId, Loggable;

    /**
     * @param OrderService $orderService
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function reInit(OrderService $orderService, Request $request)
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

    /**
     * @param FrontendSessionStorageFactoryContract $frontendSessionStorageFactory
     * @param Response $response
     * @param TransactionRepositoryContract $transactionRepository
     * @param $transactionId
     * @return mixed
     */
    public function checkPayment(FrontendSessionStorageFactoryContract $frontendSessionStorageFactory, Response $response, TransactionRepositoryContract $transactionRepository)
    {
        $lang = $frontendSessionStorageFactory->getLocaleSettings()->language;

        try {
            if ($transactionRepository->isTransactionPaid()) {
                //redirect to order creation
                return $response->redirectTo($lang . '/place-order');

            } else {
                //check payment status
                $this->getLogger('checkPayment')->error(
                    'Mollie::Debug.transactionWasNotPaid',
                    ['transactionId' => $transactionId]
                );

                return $response->redirectTo($lang . '/checkout');
            }

        } catch (\Exception $exception) {
            $this->getLogger('checkPayment')->error(
                'Mollie::Debug.transactionIdDoesNotMatch',
                [
                    'transactionId' => $transactionId,
                    'session'       => $transactionRepository->getTransactionId()
                ]
            );

            return $response->redirectTo($lang . '/checkout');
        }
    }
}