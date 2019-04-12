<?php

namespace Mollie\Services;

use Mollie\Api\ApiClient;
use Mollie\Factories\ApiOrderFactory;
use Mollie\Traits\CanCheckMollieMethod;
use Plenty\Exceptions\ValidationException;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Property\Models\OrderProperty;
use Plenty\Modules\Order\Property\Models\OrderPropertyType;
use Plenty\Modules\Payment\Method\Models\PaymentMethod;
use Plenty\Plugin\Log\Loggable;

/**
 * Class OrderService
 * @package Mollie\Services
 */
class OrderService
{
    use Loggable, CanCheckMollieMethod;

    /**
     * @var OrderRepositoryContract
     */
    private $orderRepository;

    /**
     * @var ApiOrderFactory
     */
    private $apiOrderFactory;

    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * OrderService constructor.
     * @param OrderRepositoryContract $orderRepository
     * @param ApiOrderFactory $apiOrderFactory
     * @param ApiClient $apiClient
     */
    public function __construct(OrderRepositoryContract $orderRepository, ApiOrderFactory $apiOrderFactory, ApiClient $apiClient)
    {
        $this->orderRepository = $orderRepository;
        $this->apiOrderFactory = $apiOrderFactory;
        $this->apiClient       = $apiClient;
    }

    /**
     * @param int $orderId
     * @param null|int $mopId
     * @return array
     * @throws \Exception
     */
    public function prepareOrder($orderId, $mopId = null)
    {
        $order = $this->orderRepository->findOrderById($orderId);

        if ($order instanceof Order) {

            //get order payment method if not delivered
            if (is_null($mopId)) {
                $mopId = $order->methodOfPaymentId;
            }

            $paymentMethod = $this->getMolliePaymentMethod($mopId);
            if ($paymentMethod instanceof PaymentMethod) {
                try {

                    $externalOrderId = $this->getExternalOrderId($order);

                    if (!empty($externalOrderId)) {
                        $result = $this->apiClient->getOrder($externalOrderId);
                    } else {
                        //process payment
                        $orderData = $this->apiOrderFactory->buildOrderData($order, $paymentMethod->paymentKey);
                        $this->getLogger('creatingOrder')->debug('Mollie::Debug.createOrder', $orderData);
                        $result    = $this->apiClient->createOrder($orderData);
                    }

                    if (array_key_exists('error', $result)) {
                        $this->getLogger('creatingOrder')->error('Mollie::Debug.createOrderIssue', $result);
                        throw new \Exception($result['error']);
                    } else {
                        $this->orderRepository->updateOrder(
                            [
                                'properties' => [
                                    ['typeId' => OrderPropertyType::EXTERNAL_ORDER_ID, 'value' => $result['id']]
                                ]
                            ],
                            $order->id
                        );
                        return $result;
                    }
                } catch (ValidationException $exception) {
                    $this->getLogger('creatingOrder')->logException($exception);
                    throw new \Exception($exception->getMessage());
                }
            } else {
                $this->getLogger('creatingOrder')->error('PaymentMethodNotFound', ['orderId' => $orderId, 'mopId' => $mopId]);
            }
        }
    }

    /**
     * @param Order $order
     */
    public function cancelOrder(Order $order)
    {
        $externalOrderId = $this->getExternalOrderId($order);

        if (!empty($externalOrderId)) {
            $result = $this->apiClient->cancelOrder($externalOrderId);
            if (array_key_exists('error', $result)) {
                $this->getLogger('cancelOrder')->error('Mollie::Debug.cancelOrderIssue', $result);
            }
        }
    }

    /**
     * @param Order $order
     */
    public function createRefund(Order $order)
    {
        $externalOrderId = $this->getExternalOrderId($order);

        if (!empty($externalOrderId)) {
            $result = $this->apiClient->createRefund($externalOrderId, ['lines' => []]);
            if (array_key_exists('error', $result)) {
                $this->getLogger('createRefund')->error('Mollie::Debug.createRefundIssue', $result);
            }
        }
    }

    /**
     * @param Order $order
     * @return string
     */
    private function getExternalOrderId(Order $order)
    {
        $externalOrderId = '';
        foreach ($order->properties as $orderProperty) {
            if ($orderProperty instanceof OrderProperty) {
                if ($orderProperty->typeId == OrderPropertyType::EXTERNAL_ORDER_ID && !empty($orderProperty->value)) {
                    $externalOrderId = $orderProperty->value;
                }
            }
        }
        return $externalOrderId;
    }
}