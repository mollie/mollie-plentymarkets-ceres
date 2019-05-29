<?php

namespace Mollie\Events;

use Mollie\Api\ApiClient;
use Mollie\Contracts\TransactionRepositoryContract;
use Mollie\Services\OrderUpdateService;
use Mollie\Traits\CanCheckMollieMethod;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Property\Models\OrderPropertyType;
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
     * ExecuteMolliePayment constructor.
     * @param FrontendSessionStorageFactoryContract $sessionStorageFactory
     * @param TransactionRepositoryContract $transactionRepository
     * @param ApiClient $apiClient
     * @param OrderUpdateService $orderUpdateService
     * @param OrderRepositoryContract $orderRepository
     */
    public function __construct(FrontendSessionStorageFactoryContract $sessionStorageFactory,
                                TransactionRepositoryContract $transactionRepository,
                                ApiClient $apiClient,
                                OrderUpdateService $orderUpdateService,
                                OrderRepositoryContract $orderRepository)
    {
        $this->sessionStorageFactory = $sessionStorageFactory;
        $this->transactionRepository = $transactionRepository;
        $this->orderRepository       = $orderRepository;
        $this->apiClient             = $apiClient;
        $this->orderUpdateService    = $orderUpdateService;
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
                    $mollieOrder = $this->apiClient->getOrder($this->transactionRepository->getTransaction()->mollieOrderId);
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

                    //assign order id to transaction
                    $this->transactionRepository->assignOrderId($event->getOrderId());

                    //set mollie id as external order id to order
                    $this->orderRepository->updateOrder(
                        [
                            'properties' => [
                                ['typeId' => OrderPropertyType::EXTERNAL_ORDER_ID, 'value' => $mollieOrder['id']]
                            ]
                        ],
                        $event->getOrderId()
                    );

                    //set orderId at mollie
                    $orderUpdateResponse = $this->apiClient->updateOrderNumber($mollieOrder['id'], $event->getOrderId());
                    $this->getLogger('updateOrderid')->debug(
                        'Mollie::Debug.mollieOrder',
                        $orderUpdateResponse
                    );
                    $this->orderUpdateService->setPaid($this->orderRepository->findOrderById($event->getOrderId()), $mollieOrder);
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