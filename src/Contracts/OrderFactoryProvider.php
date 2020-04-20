<?php

namespace Mollie\Contracts;

use Mollie\Helpers\LocaleHelper;
use Plenty\Modules\Account\Address\Models\Address;
use Plenty\Modules\Account\Address\Models\AddressOption;
use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Modules\Helper\Services\WebstoreHelper;

/**
 * Class OrderFactoryProvider
 * @package Mollie\Contracts
 */
abstract class OrderFactoryProvider
{
    /**
     * @param string $method
     * @param array $options
     * @return array
     */
    abstract public function buildOrder($method, $options = []);

    /**
     * @param Address $address
     * @param bool $isFirstName
     * @return string
     */
    protected function getName(Address $address, $isFirstName = true)
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
     * @param Address $billingAddress
     * @param Contact|null $contact
     * @return string
     */
    protected function getLocaleByOrder(Address $billingAddress, Contact $contact = null)
    {
        $lang = '';

        //1. Get lang by contact
        if ($contact instanceof Contact) {
            $lang = $contact->lang;
        }

        //2. Get lang by country
        if (empty($lang)) {
            $lang = $billingAddress->country->lang;
        }

        return LocaleHelper::buildLocale($lang, $billingAddress);
    }

    /**
     * @return string
     */
    protected function getDomain()
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

    /**
     * @param float $grossAmount
     * @param float $vatRate
     * @param int $quantity
     * @param float $discount
     * @return string
     */
    protected function calculateVatAmount($grossAmount, $vatRate, $quantity = 1, $discount = 0.00)
    {
        return number_format((($grossAmount-$discount) * $quantity) * ($vatRate / (100.0 + $vatRate)), 2, '.', '');
    }
}