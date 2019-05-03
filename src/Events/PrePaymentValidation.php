<?php

namespace Mollie\Events;

use Mollie\Traits\CanCheckMollieMethod;
use Plenty\Modules\Account\Address\Contracts\AddressRepositoryContract;
use Plenty\Modules\Account\Address\Models\Address;
use Plenty\Modules\Account\Address\Models\AddressRelationType;
use Plenty\Modules\Account\Contact\Contracts\ContactAddressRepositoryContract;
use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Modules\Frontend\Services\AccountService;
use Plenty\Modules\Payment\Events\Checkout\GetPaymentMethodContent;
use Plenty\Modules\Payment\Method\Models\PaymentMethod;
use Plenty\Plugin\Translation\Translator;

/**
 * Class PrePaymentValidation
 * @package Mollie\Events
 */
class PrePaymentValidation
{
    use CanCheckMollieMethod;

    /**
     * @param GetPaymentMethodContent $getPaymentMethodContent
     */
    public function handle(GetPaymentMethodContent $getPaymentMethodContent)
    {
        $paymentMethod = $this->getMolliePaymentMethod($getPaymentMethodContent->getMop());
        if ($paymentMethod instanceof PaymentMethod) {

            if ($paymentMethod->paymentKey == 'creditcard') {

                /** @var Translator $translator */
                $translator = pluginApp(Translator::class);

                /**
                 * @var Checkout $checkout
                 */
                $checkout = pluginApp(Checkout::class);

                /**
                 * @var AccountService $accountService
                 */
                $accountService = pluginApp(AccountService::class);

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

                if (!$billingAddress instanceof Address || empty($billingAddress->phone)) {
                    $getPaymentMethodContent
                        ->setType(GetPaymentMethodContent::RETURN_TYPE_ERROR)
                        ->setValue($translator->trans('Mollie::Errors.numberMissing'));
                }
            }
        }
    }
}