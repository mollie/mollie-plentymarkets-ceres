<?php

namespace Mollie\Controllers;

use Mollie\Contracts\TransactionRepositoryContract;
use Mollie\Helpers\CeresHelper;
use Mollie\Services\OrderService;
use Mollie\Services\OrderUpdateService;
use Mollie\Traits\CanHandleTransactionId;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Translation\Translator;

/**
 * Class PaymentController
 * @package Mollie\Controllers
 */
class PaymentController extends Controller
{
    use CanHandleTransactionId, Loggable;

    /**
     * @param OrderService $orderService
     * @param AuthHelper $authHelper
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function reInit(OrderService $orderService, AuthHelper $authHelper, Request $request)
    {
        return $authHelper->processUnguarded(function () use ($orderService, $request) {
            return $orderService->prepareOrder($request->get('orderId'));
        });
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
     * @param CeresHelper $ceresHelper
     * @param Translator $translator
     * @param TransactionRepositoryContract $transactionRepository
     * @return mixed
     */
    public function checkPayment(FrontendSessionStorageFactoryContract $frontendSessionStorageFactory,
                                 Response $response,
                                 CeresHelper $ceresHelper,
                                 Translator $translator,
                                 TransactionRepositoryContract $transactionRepository)
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
                    ['transactionId' => $transactionRepository->getTransactionId()]
                );

                $ceresHelper->pushNotification($translator->trans('Mollie::Errors.notPaid'));
                return $response->redirectTo($lang . '/checkout');
            }

        } catch (\Exception $exception) {
            $this->getLogger('checkPayment')->error(
                'Mollie::Debug.transactionIdDoesNotMatch',
                [
                    'transactionId' => $transactionRepository->getTransactionId(),
                    'session'       => $transactionRepository->getTransactionId()
                ]
            );


            $ceresHelper->pushNotification($translator->trans('Mollie::Errors.failed'));
            return $response->redirectTo($lang . '/checkout');
        }
    }

    /**
     * @param FrontendSessionStorageFactoryContract $frontendSessionStorageFactory
     * @return array
     */
    public function activateApplePay(FrontendSessionStorageFactoryContract $frontendSessionStorageFactory)
    {
        $frontendSessionStorageFactory->getPlugin()->setValue('mollie_apple_pay_active', true);
        return [];
    }

    /**
     * @param FrontendSessionStorageFactoryContract $frontendSessionStorageFactory
     * @param Request $request
     * @param Response $response
     * @param Checkout $checkout
     * @param CeresHelper $ceresHelper
     * @param Translator $translator
     * @param OrderService $orderService
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function createOrderByCreditCard(FrontendSessionStorageFactoryContract $frontendSessionStorageFactory,
                                            Request $request,
                                            Response $response,
                                            Checkout $checkout,
                                            CeresHelper $ceresHelper,
                                            Translator $translator,
                                            OrderService $orderService)
    {
        $lang   = $frontendSessionStorageFactory->getLocaleSettings()->language;
        $result = $orderService->preparePayment($checkout->getPaymentMethodId(), $request->get('mollie-cc-token'));
        if (array_key_exists('error', $result) || empty($result['_links']['checkout']['href'])) {
            $ceresHelper->pushNotification($translator->trans('Mollie::Errors.failed'));
            return $response->redirectTo($lang . '/checkout');
        } else {
            return $response->redirectTo($result['_links']['checkout']['href']);
        }
    }
}