<?php

namespace Mollie\Events;

use Mollie\Services\OrderService;
use Mollie\Traits\CanCheckMollieMethod;
use Plenty\Modules\Payment\Events\Checkout\ExecutePayment;
use Plenty\Modules\Payment\Method\Models\PaymentMethod;
use Plenty\Plugin\Log\Loggable;

/**
 * Class BuildPaymentDetails
 * @package Mollie\Events
 */
class BuildPaymentDetails
{
    use Loggable, CanCheckMollieMethod;

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
     * @param  ExecutePayment $event
     */
    public function handle(ExecutePayment $event)
    {
        $paymentMethod = $this->getMolliePaymentMethod($event->getMop());
        if ($paymentMethod instanceof PaymentMethod) {
            try {
                $result = $this->orderService->prepareOrder($event->getOrderId(), $event->getMop());
                $event->setType('redirectUrl');
                $event->setValue($result['_links']['checkout']['href']);
            } catch (\Exception $exception) {
                $event->setType('error');
                $event->setValue('Internal Error');
                $this->getLogger('creatingOrder')->logException($exception);
            }
        }
    }
}