<?php

namespace Mollie\Factories\Providers;

use Mollie\Contracts\OrderFactoryProvider;
use Mollie\Helpers\PhoneHelper;
use Mollie\Traits\CanCorrectAmountDifferences;
use Plenty\Modules\Account\Address\Contracts\AddressRepositoryContract;
use Plenty\Modules\Account\Address\Models\Address;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Basket\Models\BasketItem;
use Plenty\Modules\Frontend\Services\AccountService;
use Plenty\Modules\Frontend\Services\VatService;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Plenty\Modules\Item\Item\Contracts\ItemRepositoryContract;

/**
 * Class CheckoutProvider
 * @package Mollie\Factories\Providers
 */
class CheckoutProvider extends OrderFactoryProvider
{
    use CanCorrectAmountDifferences;

    /**
     * @param string $method
     * @param array $options
     * @return array
     */
    public function buildOrder($method, $options = [])
    {
        /** @var PhoneHelper $phoneHelper */
        $phoneHelper = pluginApp(PhoneHelper::class);

        /** @var BasketRepositoryContract $basketRepository */
        $basketRepository = pluginApp(BasketRepositoryContract::class);

        /** @var AddressRepositoryContract $addressRepository */
        $addressRepository = pluginApp(AddressRepositoryContract::class);

        /** @var FrontendSessionStorageFactoryContract $sessionStorage */
        $sessionStorage = pluginApp(FrontendSessionStorageFactoryContract::class);

        /** @var AccountService $accountService */
        $accountService = pluginApp(AccountService::class);

        /** @var ItemRepositoryContract $itemContract */
        $itemContract = pluginApp(ItemRepositoryContract::class);

        /** @var ContactRepositoryContract $contactRepository */
        $contactRepository = pluginApp(ContactRepositoryContract::class);

        /** @var VatService $vatService */
        $vatService = pluginApp(VatService::class);

        $domain = $this->getDomain();

        $basket = $basketRepository->load();

        $deliveryAddressId = $basket->customerShippingAddressId;
        $billingAddressId  = $basket->customerInvoiceAddressId;

        if (is_null($deliveryAddressId) || $deliveryAddressId == -99) {
            $deliveryAddressId = $billingAddressId;
        }

        /** @var Address $billingAddress */
        $billingAddress = $addressRepository->findAddressById($billingAddressId);

        /** @var Address $deliveryAddress */
        $deliveryAddress = $addressRepository->findAddressById($deliveryAddressId);

        $email = $billingAddress->email;

        //if the billing address doesn't contain an email, use contact mail instead
        if (empty($email)) {
            $contactId = $accountService->getAccountContactId();

            if (!empty($contactId) && $contactId > 0) {
                $contact = $contactRepository->findContactById($contactId);

                if ($contact instanceof Contact) {
                    $email = $contact->email;
                }
            }
        }

        $isNet = false;
        if (!count($vatService->getCurrentTotalVats())) {
            $isNet = true;
        }

        $orderData = [
            'amount'          => [
                'currency' => $basket->currency,
                'value'    => number_format($isNet ? $basket->basketAmountNet : $basket->basketAmount, 2, '.', ''),
            ],
            'billingAddress'  => [
                'organizationName' => $billingAddress->companyName,
                'streetAndNumber'  => $billingAddress->street . ' ' . $billingAddress->houseNumber,
                'city'             => $billingAddress->town,
                'region'           => $billingAddress->state->name,
                'postalCode'       => (STRING)$billingAddress->postalCode,
                'country'          => $billingAddress->country->isoCode2,
                'title'            => $billingAddress->title,
                'givenName'        => $this->getName($billingAddress),
                'familyName'       => $this->getName($billingAddress, false),
                'email'            => $email,
            ],
            'shippingAddress' => [
                'organizationName' => $deliveryAddress->companyName,
                'streetAndNumber'  => $deliveryAddress->street . ' ' . $deliveryAddress->houseNumber,
                'streetAdditional' => $deliveryAddress->additional,
                'city'             => $deliveryAddress->town,
                'region'           => $deliveryAddress->state->name,
                'postalCode'       => (STRING)$deliveryAddress->postalCode,
                'country'          => $deliveryAddress->country->isoCode2,
                'title'            => $deliveryAddress->title,
                'givenName'        => $this->getName($deliveryAddress),
                'familyName'       => $this->getName($deliveryAddress, false),
                'email'            => $email,
            ],
            'metadata'        => [
                'orderId'       => null,
                'transactionId' => $options['transactionId']
            ],
            'locale'          => $this->getLocaleByOrder($billingAddress),
            'orderNumber'     => $options['transactionId'],
            'redirectUrl'     => $domain . '/mollie/check',
            'webhookUrl'      => $domain . '/rest/mollie/webhook',
            'method'          => $method,
            'lines'           => [],
        ];

        if (array_key_exists('ccToken', $options) && !empty($options['ccToken'])) {
            $orderData['payment']['cardToken'] = $options['ccToken'];
        }

        $phone = $phoneHelper->correctPhone($billingAddress->phone, $billingAddress->country->isoCode2);
        if ($phone !== false) {
            $orderData['billingAddress']['phone'] = $phone;
        }

        if (!empty($billingAddress->birthday)) {
            $orderData['consumerDateOfBirth'] = date('Y-m-d', $billingAddress->birthday);
        }


        $vatRate = 0.00;
        foreach ($basket->basketItems as $basketItem) {
            if ($basketItem instanceof BasketItem) {

                $basketItemPrice = $basketItem->price + $basketItem->attributeTotalMarkup;
                if ($isNet) {
                    $basketItemPrice = round($basketItemPrice * 100 / (100.0 + $basketItem->vat), 2);
                }

                /** @var \Plenty\Modules\Item\Item\Models\Item $item */
                $item = $itemContract->show($basketItem->itemId, ['*'], $sessionStorage->getLocaleSettings()->language);

                /** @var \Plenty\Modules\Item\Item\Models\ItemText $itemText */
                $itemText = $item->texts;

                $discount = 0.00;
                if ($basket->basketRebate > 0.00) {
                    $discount = $basketItemPrice * ($basket->basketRebate / 100);
                }

                $vatRate = max($basketItem->vat, $vatRate);
                $line    = [
                    'sku'            => (STRING)$basketItem->variationId,
                    'name'           => $itemText->first()->name1,
                    'type'           => 'physical',
                    //'productUrl'
                    //'imageUrl' => $orderItem->itemVariationI
                    'quantity'       => $basketItem->quantity,
                    'vatRate'        => $isNet ? '0.00' : number_format($basketItem->vat, 2, '.', ''),
                    'unitPrice'      => [
                        'currency' => $basket->currency,
                        'value'    => number_format($basketItemPrice, 2, '.', ''),
                    ],
                    'totalAmount'    => [
                        'currency' => $basket->currency,
                        'value'    => number_format(($basketItemPrice-$discount) * $basketItem->quantity, 2, '.', ''),
                    ],
                    'discountAmount' => [
                        'currency' => $basket->currency,
                        'value'    => number_format($discount * $basketItem->quantity, 2, '.', ''),
                    ],
                    'vatAmount'      => [
                        'currency' => $basket->currency,
                        'value'    => $isNet ? '0.00' : $this->calculateVatAmount($basketItemPrice, $basketItem->vat, $basketItem->quantity, $discount),
                    ]
                ];

                $orderData['lines'][] = $line;
            }
        }

        $shippingAmount = $basket->shippingAmount;
        if ($isNet) {
            $shippingAmount = $basket->shippingAmountNet;
        }

        //shippingcosts
        $orderData['lines'][] = [
            'sku'            => '0',
            'name'           => 'Shipping',
            'type'           => 'shipping_fee',
            //'productUrl'
            //'imageUrl' => $orderItem->itemVariationI
            'quantity'       => 1,
            'vatRate'        => $isNet ? '0.00' : number_format($vatRate, 2, '.', ''),
            'unitPrice'      => [
                'currency' => $basket->currency,
                'value'    => number_format($shippingAmount, 2, '.', ''),
            ],
            'totalAmount'    => [
                'currency' => $basket->currency,
                'value'    => number_format($shippingAmount, 2, '.', ''),
            ],
            'discountAmount' => [
                'currency' => $basket->currency,
                'value'    => $basket->shippingDeleteByCoupon ? number_format(-1 * $shippingAmount, 2, '.', '') : '0.00',
            ],
            'vatAmount'      => [
                'currency' => $basket->currency,
                'value'    => $isNet ? '0.00' : $this->calculateVatAmount($basket->shippingAmount, $vatRate),
            ]
        ];

        if ($basket->couponDiscount != 0.00) {

            $couponDiscount = $basket->couponDiscount;
            if ($isNet) {
                $couponDiscount = round($basket->couponDiscount * 100 / (100.0 + $vatRate), 2);
            }

            //coupon
            $orderData['lines'][] = [
                'sku'            => '0',
                'name'           => 'Coupon: ' . $basket->couponCode,
                'type'           => 'discount',
                //'productUrl'
                //'imageUrl' => $orderItem->itemVariationI
                'quantity'       => 1,
                'vatRate'        => $isNet ? '0.00' : number_format($vatRate, 2, '.', ''),
                'unitPrice'      => [
                    'currency' => $basket->currency,
                    'value'    => number_format($couponDiscount, 2, '.', ''),
                ],
                'totalAmount'    => [
                    'currency' => $basket->currency,
                    'value'    => number_format($couponDiscount, 2, '.', ''),
                ],
                'discountAmount' => [
                    'currency' => $basket->currency,
                    'value'    => '0.00',
                ],
                'vatAmount'      => [
                    'currency' => $basket->currency,
                    'value'    => $isNet ? '0.00' : $this->calculateVatAmount($basket->couponDiscount, $vatRate),
                ]
            ];
        }

        //correct amounts
        $amountsTotal = 0.00;
        foreach ($orderData['lines'] as $orderLineData) {
            $amountsTotal += $orderLineData['totalAmount']['value'];
        }

        $oldAmount                                     = $orderData['lines'][0]['totalAmount']['value'];
        $orderData['lines'][0]['totalAmount']['value'] = $this->correctAmount(
            $amountsTotal,
            $orderData['amount']['value'],
            $orderData['lines'][0]['totalAmount']['value']
        );

        if ($oldAmount != $orderData['lines'][0]['totalAmount']['value'] && !$isNet) {

            //recalculate vat Amount because the total has changed
            $orderData['lines'][0]['totalAmount']['vatAmount'] = $this->calculateVatAmount(
                $orderData['lines'][0]['totalAmount']['value'],
                $orderData['lines'][0]['vatRate'],
                $orderData['lines'][0]['quantity']
            );
        }

        return $orderData;
    }
}