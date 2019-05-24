<?php

namespace Mollie\Events;

use Mollie\Services\OrderService;
use Mollie\Traits\CanCheckMollieMethod;
use Plenty\Modules\Payment\Events\Checkout\GetPaymentMethodContent;
use Plenty\Modules\Payment\Method\Models\PaymentMethod;
use Plenty\Plugin\Log\Loggable;

/**
 * Class PrePayment
 * @package Mollie\Events
 */
class PreparePayment
{
    use CanCheckMollieMethod, Loggable;

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * BuildPaymentDetails constructor.
     * @param OrderService $orderService
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }


    /**
     * @param GetPaymentMethodContent $getPaymentMethodContent
     */
    public function handle(GetPaymentMethodContent $getPaymentMethodContent)
    {
        $paymentMethod = $this->getMolliePaymentMethod($getPaymentMethodContent->getMop());
        if ($paymentMethod instanceof PaymentMethod) {
            try {
                $result = $this->orderService->preparePayment($getPaymentMethodContent->getMop());
                $getPaymentMethodContent->setType('redirectUrl');
                $getPaymentMethodContent->setValue($result['_links']['checkout']['href']);
            } catch (\Exception $exception) {
                $getPaymentMethodContent->setType('error');
                $getPaymentMethodContent->setValue('Internal Error');
                $this->getLogger('creatingOrder')->logException($exception);
            }
        }
    }
}