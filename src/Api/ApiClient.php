<?php

namespace Mollie\Api;

use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;
use Plenty\Plugin\ConfigRepository;

/**
 * Class ApiClient
 * @package Mollie\Api
 */
class ApiClient
{
    private $settings = [];

    /**
     * @var LibraryCallContract
     */
    private $libraryCallContract;

    /**
     * ApiClient constructor.
     * @param ConfigRepository $configRepository
     * @param LibraryCallContract $libraryCallContract
     */
    public function __construct(
        ConfigRepository $configRepository,
        LibraryCallContract $libraryCallContract
    )
    {
        $this->settings            = $configRepository->get('Mollie');
        $this->libraryCallContract = $libraryCallContract;
    }

    /**
     * @param array $orderData
     * @return array
     */
    public function createOrder($orderData)
    {
        return $this->libraryCallContract->call(
            'Mollie::CreateOrder', [
                'apiKey'    => $this->getApiKey(),
                'orderData' => $orderData
            ]
        );
    }

    /**
     * @param int $orderId
     * @param array $shipmentData
     * @return array
     */
    public function createShipment($orderId, $shipmentData)
    {
        return $this->libraryCallContract->call(
            'Mollie::CreateShipment', [
                'apiKey'       => $this->getApiKey(),
                'orderId'      => (STRING)$orderId,
                'shipmentData' => $shipmentData
            ]
        );
    }

    /**
     * @param string $orderId
     * @return array
     */
    public function getOrder($orderId)
    {
        return $this->libraryCallContract->call(
            'Mollie::GetOrder', [
                'apiKey'  => $this->getApiKey(),
                'orderId' => $orderId
            ]
        );
    }


    /**
     * @param string $orderId
     * @return array
     */
    public function cancelOrder($orderId)
    {
        return $this->libraryCallContract->call(
            'Mollie::CancelOrder', [
                'apiKey'  => $this->getApiKey(),
                'orderId' => $orderId
            ]
        );
    }

    /**
     * @param int $orderId
     * @param array $refundData
     * @return array
     */
    public function createRefund($orderId, $refundData)
    {
        return $this->libraryCallContract->call(
            'Mollie::CreateRefund', [
                'apiKey'       => $this->getApiKey(),
                'orderId'      => (STRING)$orderId,
                'refundData' => $refundData
            ]
        );
    }

    /**
     * @return array
     */
    public function getAllAvailableMethods()
    {
        return $this->libraryCallContract->call(
            'Mollie::Methods', [
                'apiKey'   => $this->getApiKey(),
                'resource' => 'orders'
            ]
        );
    }

    /**
     * @param array $params
     * @return array
     */
    public function getMethods(array $params)
    {
        return $this->libraryCallContract->call(
            'Mollie::Methods',
            array_merge(
                [
                    'apiKey'   => $this->getApiKey(),
                    'resource' => 'orders'
                ],
                $params
            )
        );
    }

    /**
     * @return string
     */
    private function getApiKey()
    {
        if (array_key_exists('isTestMode', $this->settings) && !$this->settings['isTestMode']) {
            return $this->settings['apiKeys']['productive'];
        }
        return $this->settings['apiKeys']['test'];
    }
}