<?php

namespace Mollie\Services;

use Mollie\Api\ApiClient;
use Plenty\Modules\Comment\Contracts\CommentRepositoryContract;
use Plenty\Modules\Comment\Models\Comment;
use Plenty\Modules\Frontend\Contracts\CurrencyExchangeRepositoryContract;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Payment\Contracts\PaymentOrderRelationRepositoryContract;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Modules\Payment\Models\Payment;
use Plenty\Plugin\Log\Loggable;

/**
 * Class OrderUpdateService
 * @package Mollie\Services
 */
class OrderUpdateService
{
    use Loggable;

    /**
     * @var CommentRepositoryContract
     */
    private $commentRepository;

    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * @var OrderRepositoryContract
     */
    private $orderRepository;

    /**
     * @var PaymentRepositoryContract
     */
    private $paymentRepository;

    /**
     * @var PaymentOrderRelationRepositoryContract
     */
    private $paymentOrderRelationRepository;

    /**
     * OrderUpdateService constructor.
     * @param CommentRepositoryContract $commentRepository
     * @param ApiClient $apiClient
     * @param OrderRepositoryContract $orderRepository
     * @param PaymentRepositoryContract $paymentRepository
     * @param PaymentOrderRelationRepositoryContract $paymentOrderRelationRepository
     */
    public function __construct(
        CommentRepositoryContract $commentRepository,
        ApiClient $apiClient,
        OrderRepositoryContract $orderRepository,
        PaymentRepositoryContract $paymentRepository,
        PaymentOrderRelationRepositoryContract $paymentOrderRelationRepository)
    {
        $this->commentRepository              = $commentRepository;
        $this->apiClient                      = $apiClient;
        $this->orderRepository                = $orderRepository;
        $this->paymentRepository              = $paymentRepository;
        $this->paymentOrderRelationRepository = $paymentOrderRelationRepository;
    }

    /**
     * @param string $mollieOrderId
     * @throws \Exception
     */
    public function updatePlentyOrder($mollieOrderId)
    {
        $this->getLogger('updatePlentyOrder')->debug('Mollie::Debug.webhook', $mollieOrderId);
        $plentyOrder = $this->orderRepository->findOrderByExternalOrderId($mollieOrderId);
        if ($plentyOrder instanceof Order) {
            $mollieOrder = $this->apiClient->getOrder($mollieOrderId);

            $this->getLogger('updatePlentyOrder')->debug('Mollie::Debug.mollieOrder', $mollieOrder);

            if ($mollieOrder['metadata']['orderId'] != $plentyOrder->id) {
                throw new \Exception('Orders don\'t match');
            }

            if (($mollieOrder['status'] == 'paid' || $mollieOrder['status'] == 'authorized') && $plentyOrder->paymentStatus == 'unpaid') {
                $this->setPaid($plentyOrder, $mollieOrder);
            } elseif($mollieOrder['status'] == 'paid' && $plentyOrder->paymentStatus == 'paid' && $mollieOrder['amountRefunded']['value'] > 0){
                //TODO create negative payment
            }else {
                $this->commentRepository->createComment(
                    [
                        'referenceType'       => Comment::REFERENCE_TYPE_ORDER,
                        'referenceValue'      => $plentyOrder->id,
                        'text'                => 'Payment status update by mollie: ' . $mollieOrder['status'],
                        'isVisibleForContact' => true
                    ]
                );
            }
        }
    }

    /**
     * @param Order $plentyOrder
     * @param array $mollieOrder
     */
    public function setPaid(Order $plentyOrder, $mollieOrder)
    {
        $this->commentRepository->createComment(
            [
                'referenceType'       => Comment::REFERENCE_TYPE_ORDER,
                'referenceValue'      => $plentyOrder->id,
                'text'                => 'Order was authorized by mollie to be shipped',
                'isVisibleForContact' => true
            ]
        );

        $paymentObject = $this->createPaymentObject($plentyOrder, $mollieOrder);
        $this->getLogger('payment')->debug('Mollie::Debug.webhook', $paymentObject);

        $payment = $this->paymentRepository->createPayment($paymentObject);
        if($payment instanceof Payment){
            $this->paymentOrderRelationRepository->createOrderRelation($payment, $plentyOrder);
        }
    }

    /**
     * @param Order $order
     * @param array $mollieOrder
     * @return Payment
     */
    private function createPaymentObject(Order $order, $mollieOrder)
    {
        /** @var Payment $payment */
        $payment = pluginApp(Payment::class);

        $payment->mopId = $order->methodOfPaymentId;

        $payment->transactionType = $mollieOrder['status'] == 'paid' ?
            Payment::TRANSACTION_TYPE_BOOKED_POSTING :
            Payment::TRANSACTION_TYPE_PROVISIONAL_POSTING;

        $payment->status = $mollieOrder['status'] == 'paid' ?
            Payment::STATUS_APPROVED :
            Payment::STATUS_APPROVED;

        $payment->currency   = $mollieOrder['amount']['currency'];
        $payment->amount     = $mollieOrder['amount']['value'];
        $payment->receivedAt = $mollieOrder['paidAt'];

        try {
            /** @var CurrencyExchangeRepositoryContract $currencyService */
            $currencyService = pluginApp(CurrencyExchangeRepositoryContract::class);

            $defaultCurrency = $currencyService->getDefaultCurrency();
            if ($payment->currency != $defaultCurrency) {
                $payment->exchangeRatio = $currencyService->getExchangeRatioByCurrency($payment->currency);
                $payment->amount        = round($currencyService->convertToDefaultCurrency($payment->currency, $payment->amount, $payment->exchangeRatio), 2);
            }
        } catch (\Exception $cEx) {

        }
        $payment->type = 'credit';

        if ($payment->status == 1) {
            $payment->unaccountable = 0;
        }

        return $payment;
    }
}