<?php

namespace Mollie\Factories;

use Mollie\Helpers\LocaleHelper;
use Mollie\Helpers\PhoneHelper;
use Mollie\Helpers\TrackingURLHelper;
use Plenty\Modules\Account\Address\Models\Address;
use Plenty\Modules\Account\Address\Models\AddressOption;
use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Modules\Helper\Services\WebstoreHelper;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Models\OrderAmount;
use Plenty\Modules\Order\Models\OrderItem;
use Plenty\Modules\Order\Models\OrderItemAmount;
use Plenty\Modules\Order\Models\OrderItemType;
use Plenty\Modules\Order\Shipping\Contracts\ParcelServicePresetRepositoryContract;
use Plenty\Modules\Order\Shipping\Package\Contracts\OrderShippingPackageRepositoryContract;
use Plenty\Modules\Order\Shipping\Package\Models\OrderShippingPackage;

/**
 * Class ApiOrderFactory
 * @package Mollie\Factories
 */
class ApiOrderFactory
{
    /**
     * @param Order $order
     * @param string $method
     * @return array
     */
    public function buildOrderData(Order $order, $method)
    {
        /** @var PhoneHelper $phoneHelper */
        $phoneHelper = pluginApp(PhoneHelper::class);

        /** @var OrderAmount $orderAmount */
        $orderAmount = $order->amount;

        /** @var Address $billingAddress */
        $billingAddress = $order->billingAddress;

        /** @var Address $deliveryAddress */
        $deliveryAddress = $order->deliveryAddress;

        $domain = $this->getDomain();

        $orderData = [
            'amount'          => [
                'currency' => $orderAmount->currency,
                'value'    => number_format($orderAmount->invoiceTotal, 2, '.', ''),
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
                'email'            => $billingAddress->email,
                'phone'            => $phoneHelper->correctPhone($billingAddress->phone, $billingAddress->country->isoCode2),
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
                'email'            => $deliveryAddress->email,
            ],
            'metadata'        => [
                'orderId' => $order->id
            ],
            'locale'          => $this->getLocaleByOrder($order),
            'orderNumber'     => (STRING)$order->id,
            'redirectUrl'     => $domain . '/confirmation/' . $order->id,
            'webhookUrl'      => $domain . '/rest/mollie/webhook',
            'method'          => $method,
            'lines'           => [],
        ];

        if (!empty($billingAddress->birthday)) {
            $orderData['consumerDateOfBirth'] = date('Y-m-d', $billingAddress->birthday);
        }


        foreach ($order->orderItems as $orderItem) {
            if ($orderItem instanceof OrderItem) {
                /** @var OrderItemAmount $amount */
                $amount = $orderItem->amount;
                $line   = [
                    'sku'            => (STRING)$orderItem->itemVariationId,
                    'name'           => $orderItem->orderItemName,
                    //'productUrl'
                    //'imageUrl' => $orderItem->itemVariationI
                    'quantity'       => $orderItem->quantity,
                    'vatRate'        => number_format($orderItem->vatRate, 2, '.', ''),
                    'unitPrice'      => [
                        'currency' => $amount->currency,
                        'value'    => number_format($amount->priceGross, 2, '.', ''),
                    ],
                    'totalAmount'    => [
                        'currency' => $amount->currency,
                        'value'    => number_format($amount->priceGross * $orderItem->quantity, 2, '.', ''),
                    ],
                    'discountAmount' => [
                        'currency' => $amount->currency,
                        'value'    => number_format(($amount->priceOriginalGross - $amount->priceGross) * $orderItem->quantity, 2, '.', ''),
                    ],
                    'vatAmount'      => [
                        'currency' => $amount->currency,
                        'value'    => number_format(($amount->priceGross - $amount->priceNet) * $orderItem->quantity, 2, '.', ''),
                    ]
                ];

                if ($orderItem->typeId == OrderItemType::TYPE_SHIPPING_COSTS) {
                    $line['type'] = 'shipping_fee';
                } elseif ($orderItem->typeId == OrderItemType::TYPE_GIFT_CARD) {
                    $line['type'] = 'gift_card';
                } elseif ($orderItem->typeId == OrderItemType::TYPE_PROMOTIONAL_COUPON) {
                    $line['type'] = 'discount';
                } elseif ($orderItem->typeId == OrderItemType::TYPE_DEPOSIT) {
                    $line['type'] = 'store_credit';
                } elseif ($orderItem->typeId == OrderItemType::TYPE_PAYMENT_SURCHARGE) {
                    $line['type'] = 'surcharge';
                } else {
                    //TODO check for digital goods
                    $line['type'] = 'physical';
                }

                $orderData['lines'][] = $line;
            }
        }

        return $orderData;
    }

    /**
     * @param Address $address
     * @param bool $isFirstName
     * @return string
     */
    private function getName(Address $address, $isFirstName = true)
    {
        $firstName = $address->firstName;
        $lastName  = $address->lastName;

        if (empty($firstName)) {
            foreach ($address->options as $addressOption) {
                if ($addressOption instanceof AddressOption) {
                    if ($addressOption->typeId == AddressOption::TYPE_CONTACT_PERSON && !empty($addressOption->value)) {
                        $parts     = explode(' ', $addressOption->value);
                        $firstName = array_shift($parts);
                        $lastName  = implode(' ', $parts);
                    }
                }
            }
        }

        if ($isFirstName) {
            return $firstName;
        }
        return $lastName;
    }

    /**
     * @param Order $order
     * @return array
     */
    public function buildShipmentData(Order $order)
    {
        $shipment = [
            'lines' => []
        ];

        $trackingNumber = '';

        /** @var OrderShippingPackageRepositoryContract $orderShippingPackageRepository */
        $orderShippingPackageRepository = pluginApp(OrderShippingPackageRepositoryContract::class);


        foreach ($orderShippingPackageRepository->listOrderShippingPackages($order->id) as $orderShippingPackage) {
            if ($orderShippingPackage instanceof OrderShippingPackage) {
                $trackingNumber = $orderShippingPackage->packageNumber;
                break;
            }
        }

        if (!empty($trackingNumber)) {
            /** @var ParcelServicePresetRepositoryContract $parcelServicePresetRepository */
            $parcelServicePresetRepository = pluginApp(ParcelServicePresetRepositoryContract::class);

            $parcelServicePreset = $parcelServicePresetRepository->getPresetById($order->shippingProfileId);

            $shipment['tracking'] = [
                'code'    => $trackingNumber,
                'carrier' => $parcelServicePreset->parcelService->backendName
            ];

            if (!empty($parcelServicePreset->parcelService->trackingUrl)) {
                /** @var TrackingURLHelper $trackingURLHelper */
                $trackingURLHelper = pluginApp(TrackingURLHelper::class);

                $shipment['tracking']['url'] = $trackingURLHelper->generateURL(
                    $parcelServicePreset->parcelService->trackingUrl,
                    $trackingNumber,
                    $order->deliveryAddress->postalCode
                );
            }

            return $shipment;
        }

        return $shipment;
    }

    /**
     * @param Order $order
     * @return string
     */
    private function getLocaleByOrder(Order $order)
    {
        $lang = '';

        //1. Get lang by contact
        $contact = $order->contactReceiver;
        if ($contact instanceof Contact) {
            $lang = $contact->lang;
        }

        //2. Get lang by country
        if (empty($lang)) {
            $lang = $order->billingAddress->country->lang;
        }

        return LocaleHelper::buildLocale($lang, $order->billingAddress);
    }

    /**
     * @return string
     */
    private function getDomain()
    {
        /** @var WebstoreHelper $webstoreHelper */
        $webstoreHelper = pluginApp(WebstoreHelper::class);

        /** @var \Plenty\Modules\System\Models\WebstoreConfiguration $webstoreConfig */
        $webstoreConfig = $webstoreHelper->getCurrentWebstoreConfiguration();

        $domain = $webstoreConfig->domainSsl;
        if ($domain == 'http://dbmaster.plenty-showcase.de' || $domain == 'http://dbmaster-beta7.plentymarkets.eu' || $domain == 'http://dbmaster-stable7.plentymarkets.eu') {
            $domain = 'https://master.plentymarkets.com';
        }

        return $domain;
    }
}