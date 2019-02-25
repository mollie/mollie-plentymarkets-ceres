<?php

namespace Mollie\Services;

use Mollie\Api\ApiClient;
use Mollie\Contracts\MethodSettingsRepositoryContract;
use Mollie\Models\Method;
use Mollie\Models\MethodsCacheAdapter;
use Plenty\Modules\Account\Address\Contracts\AddressRepositoryContract;
use Plenty\Modules\Account\Address\Models\Address;
use Plenty\Modules\Account\Address\Models\AddressRelationType;
use Plenty\Modules\Account\Contact\Contracts\ContactAddressRepositoryContract;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Modules\Frontend\Services\AccountService;
use Plenty\Modules\Frontend\Services\AgentService;
use Plenty\Plugin\CachingRepository;

/**
 * Class MethodService
 * @package Mollie\Services
 */
class MethodService
{
    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * @var MethodSettingsRepositoryContract
     */
    private $methodSettingsRepository;

    /**
     * To avoid multiple api requests
     * @var array
     */
    private $frontendMethodsCache = null;

    /**
     * @var CachingRepository
     */
    private $cachingRepository;

    /**
     * MethodService constructor.
     * @param ApiClient $apiClient
     * @param MethodSettingsRepositoryContract $methodSettingsRepository
     * @param CachingRepository $cachingRepository
     */
    public function __construct(ApiClient $apiClient,
                                MethodSettingsRepositoryContract $methodSettingsRepository,
                                CachingRepository $cachingRepository)
    {
        $this->apiClient                = $apiClient;
        $this->methodSettingsRepository = $methodSettingsRepository;
        $this->cachingRepository        = $cachingRepository;
    }

    /**
     * @return array
     */
    public function getMethodsForCheckout()
    {
        if (is_null($this->frontendMethodsCache)) {

            $filters = $this->buildFrontendFilters();


            //if basket amount is empty, try to load from cache
            if (!array_key_exists('amount', $filters)) {
                $key = substr(md5(json_encode($filters)), 0, 6);

                $cacheAdapter = $this->cachingRepository->remember(
                    'MollieFrontend_' . $key,
                    60,
                    function () {
                        /** @var MethodsCacheAdapter $cacheAdapter */
                        $cacheAdapter = pluginApp(MethodsCacheAdapter::class);

                        $methodSettingsMap = $this->methodSettingsRepository->getActiveMethodSettingsMap();
                        $methodsDataList   = $this->apiClient->getMethods($this->buildFrontendFilters());

                        $cacheAdapter->cachedMethods = $this->mergeSettingsIntoMethods($methodsDataList, $methodSettingsMap, true);
                        return $cacheAdapter;
                    }
                );

                if ($cacheAdapter instanceof MethodsCacheAdapter) {
                    $this->frontendMethodsCache = $cacheAdapter->cachedMethods;
                } else {
                    $this->frontendMethodsCache = [];
                }
            } else {
                $methodSettingsMap          = $this->methodSettingsRepository->getActiveMethodSettingsMap();
                $methodsDataList            = $this->apiClient->getMethods($this->buildFrontendFilters());
                $this->frontendMethodsCache = $this->mergeSettingsIntoMethods($methodsDataList, $methodSettingsMap, true);
            }
        }

        return $this->frontendMethodsCache;
    }

    /**
     * @return array
     */
    public function getMethodsForBackend()
    {
        $methodSettingsMap = $this->methodSettingsRepository->getMethodSettingsMap();
        $methodsDataList   = $this->apiClient->getAllAvailableMethods();

        return $this->mergeSettingsIntoMethods($methodsDataList, $methodSettingsMap);
    }

    /**
     * @param array $methodsDataList
     * @param array $methodSettingsMap
     * @param bool $filterActive
     * @return array
     */
    private function mergeSettingsIntoMethods($methodsDataList, $methodSettingsMap, $filterActive = false)
    {
        $methodsList = [];

        foreach ($methodsDataList as $methodsData) {
            if ($filterActive) {
                if (array_key_exists($methodsData['id'], $methodSettingsMap)) {
                    $methodSettings = $methodSettingsMap[$methodsData['id']];
                    if ($methodSettings->isActive) {
                        /** @var Method $method */
                        $method              = pluginApp(Method::class);
                        $method->id          = $methodsData['id'];
                        $method->settings    = $methodSettings;
                        $method->description = $methodsData['description'];
                        $method->images      = $methodsData['image'];

                        $methodsList[] = $method;
                    }
                }
            } else {
                /** @var Method $method */
                $method              = pluginApp(Method::class);
                $method->id          = $methodsData['id'];
                $method->settings    = array_key_exists($methodsData['id'], $methodSettingsMap) ? $methodSettingsMap[$methodsData['id']] : null;
                $method->description = $methodsData['description'];
                $method->images      = $methodsData['image'];

                $methodsList[] = $method;
            }
        }

        return $methodsList;
    }

    /**
     * @return array
     */
    private function buildFrontendFilters()
    {
        $filters = [];

        /** @var AccountService $accountService */
        $accountService = pluginApp(AccountService::class);

        /** @var AgentService $agentService */
        $agentService = pluginApp(AgentService::class);

        /** @var Checkout $checkout */
        $checkout = pluginApp(Checkout::class);

        /** @var BasketRepositoryContract $basketRepository */
        $basketRepository = pluginApp(BasketRepositoryContract::class);


        //billing address
        $billingAddress = null;

        if ($accountService->getAccountContactId()) {
            /** @var ContactAddressRepositoryContract $contactAddressRepository */
            $contactAddressRepository = pluginApp(ContactAddressRepositoryContract::class);
            $billingAddress           = $contactAddressRepository->getAddresses($accountService->getAccountContactId(), AddressRelationType::BILLING_ADDRESS)[0];
        } else {
            if ($checkout->getCustomerInvoiceAddressId()) {
                /** @var AddressRepositoryContract $addressRepository */
                $addressRepository = pluginApp(AddressRepositoryContract::class);
                $billingAddress    = $addressRepository->findAddressById($checkout->getCustomerInvoiceAddressId());
            }
        }

        if ($billingAddress instanceof Address) {
            $filters['billingCountry'] = $billingAddress->country->isoCode2;
        }

        //locale
        if (!empty($agentService->getLanguages())) {
            $filters['locale'] = $agentService->getLanguages()[0];
        }

        //amount
        $basket = $basketRepository->load();

        if ($basket->basketAmount > 0) {
            $filters['amount'] = [
                'value'    => number_format($basket->basketAmount, 2, '.', ''),
                'currency' => $basket->currency
            ];
        }

        return $filters;
    }
}