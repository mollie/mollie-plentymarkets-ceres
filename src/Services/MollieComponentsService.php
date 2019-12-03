<?php

namespace Mollie\Services;

use Mollie\Api\ApiClient;
use Mollie\Helpers\LocaleHelper;
use Mollie\PaymentMethods\PaymentCreditCard;
use Plenty\Modules\Account\Address\Contracts\AddressRepositoryContract;
use Plenty\Modules\Account\Address\Models\Address;
use Plenty\Modules\Account\Address\Models\AddressRelationType;
use Plenty\Modules\Account\Contact\Contracts\ContactAddressRepositoryContract;
use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Modules\Frontend\Services\AccountService;
use Plenty\Modules\Frontend\Services\AgentService;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Templates\Twig;

/**
 * Class MollieComponentsService
 * @package Mollie\Services
 */
class MollieComponentsService
{
    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * @var Twig
     */
    private $twig;

    private $settings = [];

    /**
     * MollieComponentsService constructor.
     * @param ApiClient $apiClient
     * @param ConfigRepository $configRepository
     * @param Twig $twig
     */
    public function __construct(ApiClient $apiClient,
                                ConfigRepository $configRepository,
                                Twig $twig)
    {
        $this->settings  = $configRepository->get('Mollie');
        $this->apiClient = $apiClient;
        $this->twig      = $twig;
    }

    /**
     * @return string
     */
    public function getViewContent()
    {

        $profileData = $this->apiClient->getProfile();
        if (is_array($profileData) && !empty($profileData['id'])) {
            /** @var FrontendSessionStorageFactoryContract $sessionStorage */
            $sessionStorage = pluginApp(FrontendSessionStorageFactoryContract::class);

            /** @var PaymentCreditCard $paymentMethod */
            $paymentMethod = pluginApp(PaymentCreditCard::class);

            return $this->twig->render(
                'Mollie::MollieComponents',
                [
                    'profileId' => $profileData['id'],
                    'locale'    => $this->getLocale(),
                    'testmode'  => (array_key_exists('isTestMode', $this->settings) && !$this->settings['isTestMode']) ? 'false' : 'true',
                    'label'     => $paymentMethod->getName($sessionStorage->getLocaleSettings()->language)
                ]
            );
        }
    }

    /**
     * @return string|null
     */
    public function getLocale()
    {
        /** @var Checkout $checkout */
        $checkout = pluginApp(Checkout::class);

        /** @var AccountService $accountService */
        $accountService = pluginApp(AccountService::class);

        /** @var AgentService $agentService */
        $agentService = pluginApp(AgentService::class);

        $billingAddress = null;
        if ($checkout->getCustomerInvoiceAddressId()) {
            /** @var AddressRepositoryContract $addressRepository */
            $addressRepository = pluginApp(AddressRepositoryContract::class);
            $billingAddress    = $addressRepository->findAddressById($checkout->getCustomerInvoiceAddressId());
        } elseif ($accountService->getAccountContactId()) {
            /** @var ContactAddressRepositoryContract $contactAddressRepository */
            $contactAddressRepository = pluginApp(ContactAddressRepositoryContract::class);
            $billingAddress           = $contactAddressRepository->getAddresses($accountService->getAccountContactId(), AddressRelationType::BILLING_ADDRESS)[0];
        }

        if (!empty($agentService->getLanguages())) {
            return LocaleHelper::buildLocale(
                $agentService->getLanguages()[0],
                $billingAddress instanceof Address ? $billingAddress : null
            );
        }
        return null;
    }

}