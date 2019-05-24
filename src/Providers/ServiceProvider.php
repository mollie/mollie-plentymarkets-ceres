<?php

namespace Mollie\Providers;

use Mollie\Api\ApiClient;
use Mollie\Contracts\MethodSettingsRepositoryContract;
use Mollie\Contracts\TransactionRepositoryContract;
use Mollie\Events\ExecuteMolliePayment;
use Mollie\Events\PreparePayment;
use Mollie\Factories\ApiOrderFactory;
use Mollie\Factories\Providers\CheckoutProvider;
use Mollie\Factories\Providers\OrderProvider;
use Mollie\PaymentMethods\PaymentBancontact;
use Mollie\PaymentMethods\PaymentBanktransfer;
use Mollie\PaymentMethods\PaymentBelfius;
use Mollie\PaymentMethods\PaymentBitcoin;
use Mollie\PaymentMethods\PaymentCreditCard;
use Mollie\PaymentMethods\PaymentDirectDebit;
use Mollie\PaymentMethods\PaymentEPS;
use Mollie\PaymentMethods\PaymentGiftcard;
use Mollie\PaymentMethods\PaymentGiropay;
use Mollie\PaymentMethods\PaymentIdeal;
use Mollie\PaymentMethods\PaymentIngHomepay;
use Mollie\PaymentMethods\PaymentKBC;
use Mollie\PaymentMethods\PaymentKlarnaPaylater;
use Mollie\PaymentMethods\PaymentKlarnaSliceIt;
use Mollie\PaymentMethods\PaymentPaypal;
use Mollie\PaymentMethods\PaymentPaySafeCard;
use Mollie\PaymentMethods\PaymentSofort;
use Mollie\Procedures\OrderCanceled;
use Mollie\Procedures\OrderShipped;
use Mollie\Procedures\RefundCreated;
use Mollie\Repositories\MethodSettingsRepository;
use Mollie\Repositories\TransactionRepository;
use Mollie\Services\MethodService;
use Plenty\Modules\Basket\Events\Basket\AfterBasketChanged;
use Plenty\Modules\Basket\Events\Basket\AfterBasketCreate;
use Plenty\Modules\Basket\Events\BasketItem\AfterBasketItemAdd;
use Plenty\Modules\EventProcedures\Services\Entries\ProcedureEntry;
use Plenty\Modules\EventProcedures\Services\EventProceduresService;
use Plenty\Modules\Frontend\Events\FrontendCustomerAddressChanged;
use Plenty\Modules\Frontend\Events\FrontendLanguageChanged;
use Plenty\Modules\Frontend\Events\FrontendShippingCountryChanged;
use Plenty\Modules\Payment\Events\Checkout\ExecutePayment;
use Plenty\Modules\Payment\Events\Checkout\GetPaymentMethodContent;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodContainer;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\ServiceProvider as PlentyServiceProvider;

/**
 * Class ServiceProvider
 * @package Mollie\Providers
 */
class ServiceProvider extends PlentyServiceProvider
{
    private $paymentMethods = [
        'bancontact'     => PaymentBancontact::class,
        'banktransfer'   => PaymentBanktransfer::class,
        'belfius'        => PaymentBelfius::class,
        'bitcoin'        => PaymentBitcoin::class,
        'creditcard'     => PaymentCreditCard::class,
        'directdebit'    => PaymentDirectDebit::class,
        'eps'            => PaymentEPS::class,
        'giftcard'       => PaymentGiftcard::class,
        'giropay'        => PaymentGiropay::class,
        'ideal'          => PaymentIdeal::class,
        'inghomepay'     => PaymentIngHomepay::class,
        'kbc'            => PaymentKBC::class,
        'klarnapaylater' => PaymentKlarnaPaylater::class,
        'klarnasliceit'  => PaymentKlarnaSliceIt::class,
        'paypal'         => PaymentPaypal::class,
        'paysafecard'    => PaymentPaySafeCard::class,
        'sofort'         => PaymentSofort::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->getApplication()->register(RouteServiceProvider::class);

        $this->registerApi();
        $this->registerMethodServices();
        $this->registerPaymentMethods();
    }

    /**
     * @param PaymentMethodContainer $paymentMethodContainer
     * @param Dispatcher $dispatcher
     */
    public function boot(PaymentMethodContainer $paymentMethodContainer, Dispatcher $dispatcher)
    {
        foreach ($this->paymentMethods as $methodId => $paymentMethodClass) {
            //register payment service
            $paymentMethodContainer->register(
                'Mollie::' . $methodId,
                $paymentMethodClass,
                [
                    AfterBasketChanged::class,
                    AfterBasketItemAdd::class,
                    AfterBasketCreate::class,
                    FrontendLanguageChanged::class,
                    FrontendCustomerAddressChanged::class,
                    FrontendShippingCountryChanged::class
                ]
            );
        }

        // Listen for the event that gets the payment method content
        $dispatcher->listen(GetPaymentMethodContent::class, PreparePayment::class);
        $dispatcher->listen(ExecutePayment::class, ExecuteMolliePayment::class);

        //listen to Ceres/IO events to register resources
        $dispatcher->listen(
            'IO.Resources.Import',
            function ($resourceContainer) {

                /** @noinspection PhpUndefinedMethodInspection */
                $resourceContainer->addScriptTemplate('Mollie::Scripts');

                /** @noinspection PhpUndefinedMethodInspection */
                $resourceContainer->addStyleTemplate('Mollie::Styles');
            }
        );

        $this->bootEventProcedures();
    }

    /**
     * Register payment methods
     */
    private function registerPaymentMethods()
    {
        foreach ($this->paymentMethods as $paymentMethodClass) {
            $this->getApplication()->singleton($paymentMethodClass);
        }
    }

    /**
     * Register the API services
     */
    private function registerApi()
    {
        $this->getApplication()->singleton(ApiClient::class);
    }

    /**
     * Register method services
     */
    private function registerMethodServices()
    {
        $this->getApplication()->singleton(ApiOrderFactory::class);
        $this->getApplication()->singleton(CheckoutProvider::class);
        $this->getApplication()->singleton(OrderProvider::class);

        $this->getApplication()->singleton(MethodSettingsRepositoryContract::class, MethodSettingsRepository::class);
        $this->getApplication()->singleton(MethodService::class);
        $this->getApplication()->singleton(TransactionRepositoryContract::class, TransactionRepository::class);
    }

    /**
     * Boot event procedures
     */
    private function bootEventProcedures()
    {
        /** @var EventProceduresService $eventProceduresService */
        $eventProceduresService = pluginApp(EventProceduresService::class);

        $eventProceduresService->registerProcedure(
            'Mollie',
            ProcedureEntry::EVENT_TYPE_ORDER,
            [
                'de' => 'Versand bei mollie anmelden',
                'en' => 'Register shipment at mollie'
            ],
            OrderShipped::class . '@run'
        );

        $eventProceduresService->registerProcedure(
            'Mollie',
            ProcedureEntry::EVENT_TYPE_ORDER,
            [
                'de' => 'Storno bei mollie anmelden',
                'en' => 'Register cancellation at mollie'
            ],
            OrderCanceled::class . '@run'
        );

        $eventProceduresService->registerProcedure(
            'Mollie',
            ProcedureEntry::EVENT_TYPE_ORDER,
            [
                'de' => 'Gutschrift bei mollie anmelden',
                'en' => 'Register refund at mollie'
            ],
            RefundCreated::class . '@run'
        );
    }
}