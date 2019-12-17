<?php

namespace Mollie\Events;

use Mollie\Services\MollieComponentsService;
use Mollie\Services\OrderService;
use Mollie\Traits\CanCheckMollieMethod;
use Plenty\Modules\Payment\Events\Checkout\GetPaymentMethodContent;
use Plenty\Modules\Payment\Method\Models\PaymentMethod;
use Plenty\Plugin\ConfigRepository;
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
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * @var MollieComponentsService
     */
    private $mollieComponentsService;

    /**
     * BuildPaymentDetails constructor.
     * @param OrderService $orderService
     * @param ConfigRepository $configRepository
     * @param MollieComponentsService $mollieComponentsService
     */
    public function __construct(OrderService $orderService,
                                ConfigRepository $configRepository,
                                MollieComponentsService $mollieComponentsService)
    {
        $this->orderService            = $orderService;
        $this->configRepository        = $configRepository;
        $this->mollieComponentsService = $mollieComponentsService;
    }


    /**
     * @param GetPaymentMethodContent $getPaymentMethodContent
     */
    public function handle(GetPaymentMethodContent $getPaymentMethodContent)
    {
        $paymentMethod = $this->getMolliePaymentMethod($getPaymentMethodContent->getMop());
        if ($paymentMethod instanceof PaymentMethod) {
            try {

                if ($paymentMethod->paymentKey == 'creditcard' &&
                    $this->configRepository->get('Mollie.useMollieComponents') == 'true') {
                    //display mollie components

                    $getPaymentMethodContent->setType(GetPaymentMethodContent::RETURN_TYPE_HTML);
                    $getPaymentMethodContent->setValue($this->mollieComponentsService->getViewContent());
                } else {
                    //forward to mollie
                    $result = $this->orderService->preparePayment($getPaymentMethodContent->getMop());
                    /*$getPaymentMethodContent->setType(GetPaymentMethodContent::RETURN_TYPE_REDIRECT_URL);
                    $getPaymentMethodContent->setValue($result['_links']['checkout']['href']);*/

                    $getPaymentMethodContent->setType(GetPaymentMethodContent::RETURN_TYPE_CONTINUE);
                    
                }

            } catch (\Exception $exception) {
                $getPaymentMethodContent->setType(GetPaymentMethodContent::RETURN_TYPE_ERROR);
                $getPaymentMethodContent->setValue($exception->getMessage());
                $this->getLogger('creatingOrder')->logException($exception);
            }
        }
    }
}