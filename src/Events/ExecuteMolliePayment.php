<?php

namespace Mollie\Events;

use Mollie\Api\ApiClient;
use Mollie\Contracts\TransactionRepositoryContract;
use Mollie\Services\OrderUpdateService;
use Mollie\Traits\CanCheckMollieMethod;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Payment\Events\Checkout\ExecutePayment;
use Plenty\Modules\Payment\Method\Models\PaymentMethod;
use Plenty\Plugin\Log\Loggable;

/**
 * Class ExecuteMolliePayment
 * @package Mollie\Events
 */
class ExecuteMolliePayment
{
    use Loggable, CanCheckMollieMethod;

    /**
     * @var FrontendSessionStorageFactoryContract
     */
    private $sessionStorageFactory;

    /**
     * @var TransactionRepositoryContract
     */
    private $transactionRepository;

    /**
     * @var OrderRepositoryContract
     */
    private $orderRepository;

    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * @var OrderUpdateService
     */
    private $orderUpdateService;

    /**
     * @var AuthHelper
     */
    private $authHelper;

    /**
     * ExecuteMolliePayment constructor.
     * @param FrontendSessionStorageFactoryContract $sessionStorageFactory
     * @param TransactionRepositoryContract $transactionRepository
     * @param ApiClient $apiClient
     * @param AuthHelper $authHelper
     * @param OrderUpdateService $orderUpdateService
     * @param OrderRepositoryContract $orderRepository
     */
    public function __construct(FrontendSessionStorageFactoryContract $sessionStorageFactory,
                                TransactionRepositoryContract $transactionRepository,
                                ApiClient $apiClient,
                                AuthHelper $authHelper,
                                OrderUpdateService $orderUpdateService,
                                OrderRepositoryContract $orderRepository)
    {
        $this->sessionStorageFactory = $sessionStorageFactory;
        $this->transactionRepository = $transactionRepository;
        $this->orderRepository       = $orderRepository;
        $this->apiClient             = $apiClient;
        $this->orderUpdateService    = $orderUpdateService;
        $this->authHelper            = $authHelper;
    }

    /**
     * @param ExecutePayment $event
     */
    public function handle(ExecutePayment $event)
    {
        $paymentMethod = $this->getMolliePaymentMethod($event->getMop());
        if ($paymentMethod instanceof PaymentMethod) {
            try {
                //check if transaction exists
                if ($this->transactionRepository->openTransactionExists()) {

                    //check if mollie order still exists
                    $mollieOrder = $this->apiClient->getOrder($this->transactionRepository->getTransaction()->mollieOrderId, true);
                    if (array_key_exists('error', $mollieOrder)) {
                        $this->getLogger('executePayment')->error(
                            'Mollie::Debug.transactionIdDoesNotMatch',
                            [
                                'order'         => $event->getOrderId(),
                                'mollieOrderId' => $this->transactionRepository->getTransaction()->mollieOrderId
                            ]
                        );
                        $event->setType('error');
                        $event->setValue('Internal Error');
                        return;
                    }
                    $event->setType('redirectUrl');
                    $event->setValue($mollieOrder['_links']['checkout']['href']);
                  
                    $this->transactionRepository->assignOrderId($event->getOrderId());

                } else {
                    $this->getLogger('executePayment')->error(
                        'Mollie::Debug.transactionIdDoesNotMatch',
                        [
                            'order'   => $event->getOrderId(),
                            'session' => $this->transactionRepository->getTransactionId()
                        ]
                    );
                    $event->setType('error');
                    $event->setValue('Internal Error');
                }
            } catch (\Exception $exception) {
                $event->setType('error');
                $event->setValue('Internal Error');
                $this->getLogger('executePayment')->logException($exception);
            }
        }
    }
}