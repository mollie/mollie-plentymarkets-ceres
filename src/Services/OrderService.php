<?php

namespace Mollie\Services;

use Mollie\Api\ApiClient;
use Mollie\Contracts\TransactionRepositoryContract;
use Mollie\Factories\ApiOrderFactory;
use Mollie\Models\Transaction;
use Mollie\Traits\CanCheckMollieMethod;
use Mollie\Traits\CanHandleTransactionId;
use Plenty\Exceptions\ValidationException;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Models\OrderAmount;
use Plenty\Modules\Order\Models\OrderItem;
use Plenty\Modules\Order\Models\OrderItemAmount;
use Plenty\Modules\Order\Models\OrderItemType;
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
    use Loggable, CanCheckMollieMethod, CanHandleTransactionId;

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
     * @param null|int $mopId
     * @param string $ccToken
     * @return array
     * @throws \Exception
     */
    public function preparePayment($mopId = null, $ccToken = '')
    {
        /** @var TransactionRepositoryContract $transactionRepository */
        $transactionRepository = pluginApp(TransactionRepositoryContract::class);

        $transaction = $transactionRepository->createTransaction();

        if ($transaction instanceof Transaction) {

            $paymentMethod = $this->getMolliePaymentMethod($mopId);
            if ($paymentMethod instanceof PaymentMethod) {

                $transactionId = $this->wrapTransactionId($transaction->id);

                $result = $this->apiClient->getOrder($transactionId);
                if (array_key_exists('error', $result)) {
                    //create order
                    $orderData = $this->apiOrderFactory->buildOrder($paymentMethod->paymentKey, ['transactionId' => $transactionId, 'ccToken' => $ccToken]);
                    $result    = $this->apiClient->createOrder($orderData);

                    if (array_key_exists('error', $result)) {
                        $this->getLogger('creatingOrder')->error('Mollie::Debug.createOrderIssue', ['request' => $orderData??[], 'result' => $result]);
                        throw new \Exception($result['error']);
                    }
                }

                $transactionRepository->assignMollieOrderId($result['id']);

                return $result;
            } else {
                $this->getLogger('creatingOrder')->error('PaymentMethodNotFound', ['mopId' => $mopId]);
            }
        }
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

                    $result = [];
                    //check if order already exists and still can be used to be paid
                    if (!empty($externalOrderId)) {
                        $mollieOrder = $this->apiClient->getOrder($externalOrderId);
                        if (!array_key_exists('error', $mollieOrder)) {
                            if ($mollieOrder['status'] == 'created') {
                                $result = $mollieOrder;
                            } elseif (!in_array($mollieOrder['status'], ['expired', 'canceled'])) {
                                throw new \Exception('Order can not be paid');
                            }
                        }
                    }

                    if (empty($result)) {
                        //process payment
                        $orderData = $this->apiOrderFactory->buildOrder($paymentMethod->paymentKey, ['order' => $order]);
                        $result    = $this->apiClient->createOrder($orderData);
                    }

                    if (array_key_exists('error', $result)) {
                        $this->getLogger('creatingOrder')->error('Mollie::Debug.createOrderIssue', ['request' => $orderData??[], 'result' => $result]);
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
            $mollieOrder = $this->apiClient->getOrder($externalOrderId);

            /** @var OrderAmount $orderAmount */
            $orderAmount = $order->amount;

            if ($mollieOrder['amount'] == number_format($orderAmount->invoiceTotal, 2, '.', '')) {
                //full refund
                $result = $this->apiClient->createRefund($externalOrderId, ['lines' => []]);
            } else {
                //partial refund

                $lines = [];
                foreach ($order->orderItems as $orderItem) {
                    /** @var OrderItem $orderItem */

                    foreach ($mollieOrder['lines'] as $mollieOrderLine) {
                        if ($mollieOrderLine['sku'] == $orderItem->itemVariationId) {

                            /** @var OrderItemAmount $amount */
                            $amount = $orderItem->amount;

                            $discountAvailable = $amount->priceOriginalGross != $amount->priceGross;
                            $orderLine         = [
                                'id'       => $mollieOrderLine['id'],
                                'quantity' => $orderItem->quantity
                            ];

                            if ($discountAvailable || $orderItem->typeId == OrderItemType::TYPE_SHIPPING_COSTS) {
                                $orderLine['amount'] = [
                                    'value'    => number_format($amount->priceGross * $orderItem->quantity, 2, '.', ''),
                                    'currency' => $amount->currency
                                ];


                            }

                            $lines[] = $orderLine;
                        }
                    }
                }

                $result = $this->apiClient->createRefund($externalOrderId, ['lines' => $lines]);
            }


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