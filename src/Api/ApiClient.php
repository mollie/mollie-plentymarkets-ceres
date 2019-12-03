<?php

namespace Mollie\Api;

use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Log\Loggable;

/**
 * Class ApiClient
 * @package Mollie\Api
 */
class ApiClient
{
    use Loggable;

    const PLUGIN_VERSION = '1.1.1';

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
     * @return array
     */
    public function getProfile()
    {
        return $this->processApiCall(
            'Mollie::GetProfile',
            [
                'pluginVersion' => self::PLUGIN_VERSION,
                'apiKey'        => $this->getApiKey()
            ]
        );
    }

    /**
     * @param array $orderData
     * @return array
     */
    public function createOrder($orderData)
    {
        return $this->processApiCall(
            'Mollie::CreateOrder',
            [
                'pluginVersion' => self::PLUGIN_VERSION,
                'apiKey'        => $this->getApiKey(),
                'orderData'     => $orderData
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
        return $this->processApiCall(
            'Mollie::CreateShipment',
            [
                'pluginVersion' => self::PLUGIN_VERSION,
                'apiKey'        => $this->getApiKey(),
                'orderId'       => (STRING)$orderId,
                'shipmentData'  => $shipmentData
            ]
        );
    }

    /**
     * @param string $orderId
     * @param bool $withPayments
     * @return array
     */
    public function getOrder($orderId, $withPayments = false)
    {
        return $this->processApiCall(
            'Mollie::GetOrder',
            [
                'pluginVersion' => self::PLUGIN_VERSION,
                'apiKey'        => $this->getApiKey(),
                'withPayments'  => $withPayments,
                'orderId'       => $orderId
            ]
        );
    }


    /**
     * @param string $orderId
     * @return array
     */
    public function cancelOrder($orderId)
    {
        return $this->processApiCall(
            'Mollie::CancelOrder',
            [
                'pluginVersion' => self::PLUGIN_VERSION,
                'apiKey'        => $this->getApiKey(),
                'orderId'       => $orderId
            ]
        );
    }

    /**
     * @param string $orderId
     * @param string $newOrderNumber
     * @return array
     */
    public function updateOrderNumber($orderId, $newOrderNumber)
    {
        return $this->processApiCall(
            'Mollie::UpdateOrder',
            [
                'pluginVersion'  => self::PLUGIN_VERSION,
                'apiKey'         => $this->getApiKey(),
                'orderId'        => $orderId,
                'newOrderNumber' => $newOrderNumber
            ]
        );
    }

    /**
     * @param string $paymentId
     * @param string $newOrderNumber
     * @return array
     */
    public function updateOrderNumberAtPayment($paymentId, $newOrderNumber)
    {
        return $this->processApiCall(
            'Mollie::UpdatePayment',
            [
                'pluginVersion'  => self::PLUGIN_VERSION,
                'apiKey'         => $this->getApiKey(),
                'paymentId'      => $paymentId,
                'newOrderNumber' => $newOrderNumber
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
        return $this->processApiCall(
            'Mollie::CreateRefund',
            [
                'pluginVersion' => self::PLUGIN_VERSION,
                'apiKey'        => $this->getApiKey(),
                'orderId'       => (STRING)$orderId,
                'refundData'    => $refundData
            ]
        );
    }

    /**
     * @return array
     */
    public function getAllAvailableMethods()
    {
        return $this->processApiCall(
            'Mollie::Methods',
            [
                'pluginVersion' => self::PLUGIN_VERSION,
                'apiKey'        => $this->getApiKey(),
                'resource'      => 'orders'
            ]
        );
    }

    /**
     * @param array $params
     * @return array
     */
    public function getMethods(array $params)
    {
        return $this->processApiCall(
            'Mollie::Methods',
            array_merge(
                [
                    'pluginVersion' => self::PLUGIN_VERSION,
                    'apiKey'        => $this->getApiKey(),
                    'resource'      => 'orders'
                ],
                $params
            )
        );
    }

    /**
     * @param string $libCall
     * @param array $params
     * @return array
     */
    private function processApiCall($libCall, $params)
    {
        $this->getLogger($libCall)->debug('Mollie::Debug.apiRequest', $params);
        $response = $this->libraryCallContract->call($libCall, $params);
        $this->getLogger($libCall)->debug('Mollie::Debug.apiResponse', $response);
        return $response;
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