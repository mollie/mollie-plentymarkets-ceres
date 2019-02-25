<?php

namespace Mollie\PaymentMethods;

use Mollie\Models\Method;
use Mollie\Models\MethodSetting;
use Mollie\Services\MethodService;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodService;

/**
 * Class GenericMolliePayment
 * @package Mollie\PaymentMethods
 */
abstract class GenericMolliePayment extends PaymentMethodService
{
    /**
     * @var Method
     */
    private $method = null;

    /**
     * @var MethodService
     */
    private $methodService;

    /**
     * GenericMolliePayment constructor.
     * @param MethodService $methodService
     */
    public function __construct(MethodService $methodService)
    {
        $this->methodService = $methodService;
    }

    /**
     * @return string
     */
    abstract protected function getId(): string;

    /**
     * @return Method
     */
    private function getMethod()
    {
        if (!$this->method instanceof Method) {
            $methods = $this->methodService->getMethodsForCheckout();

            foreach ($methods as $method) {
                if ($method instanceof Method) {
                    if ($method->id == $this->getId()) {
                        $this->method = $method;
                        break;
                    }
                }
            }
        }

        return $this->method;
    }

    /**
     * @param string $lang
     * @return string
     */
    public function getName($lang)
    {
        $method = $this->getMethod();
        if ($method instanceof Method) {
            if ($method->settings instanceof MethodSetting) {
                if (array_key_exists($lang, $method->settings->names) && !empty($method->settings->names[$lang])) {
                    return $method->settings->names[$lang];
                } elseif ($lang != 'en' && array_key_exists('en', $method->settings->names) && !empty($method->settings->names['en'])) {
                    return $method->settings->names['en'];
                }
            }
            return $method->description;
        }
        return '';
    }

    /**
     * Check whether PayPal Express is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        $method = $this->getMethod();
        if ($method instanceof Method) {
            if ($method->settings instanceof MethodSetting) {
                return $method->settings->isActive;
            }
        }
        return false;
    }

    /**
     * Check if a express checkout for this payment method is available
     *
     * @return bool
     */
    public function isExpressCheckout(): bool
    {
        return false;
    }

    /**
     * Check if it is allowed to switch to this payment method
     *
     * @param int $orderId
     * @return bool
     */
    public function isSwitchableTo($orderId)
    {
        return true;
    }

    /**
     * Check if it is allowed to switch from this payment method
     *
     * @param int $orderId
     * @return bool
     */
    public function isSwitchableFrom($orderId)
    {
        return true;
    }

    /**
     * Get the path of the icon
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->getMethod()->images['size2x'];
    }
}
