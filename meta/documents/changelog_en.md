# Release Notes for Mollie

## 1.3.2

- UPDATE - The checkout process has been refactored. The payment process will be initialized after the order has been created successfully in plentymarkets.

## 1.3.1

- FIX - Card-token issues were fixed which caused an error message in the checkout.

## 1.3.0

- NEW - Payment methods can be deactivated for a specific plugin set by changing the plugin settings. This allows you to only use the Mollie event procedures.
- NEW - Mollie Components were added for credit card payments and can be activated in the plugin settings.

## 1.2.2

- FIX - The payment process could not be started for existing orders including a rebate.  This issue is solved now.
- FIX - Partial refunds could not be registered for orders including a rebate. This issue is solved now.
- NEW - The transfer of partial refunds and shipment notifications was refactored to extend the debugging of declined requests.

## 1.2.1

- NEW - New logs were added to have a better overview of the mollie api communication.
- FIX - When opening an unpaid order over a confirmation url, the payment process could not be initiated. This issue was solved.

## 1.2.0

- FIX - The basket gross amounts were transfered always, even for net amount baskets. This issue was fixed in this patch.
- NEW - ApplePay was added as a payment method.
- UPDATE - It's now possible to register partial refunds within the existing event procedure action.
- UPDATE - The loading of the email address was extended. In the case that the invoice address doens't contain an email, the email
of the contact is being used as a fallback.
- UPDATE - The update of the plentymarkets order id at mollie was extended. The order id will now be additional updated at mollie transactions.

## 1.1.1

- UPDATE - Phone number formatting refactored.

## 1.1.0

- UPDATE - Checkout process was changed.

## 1.0.6

- UPDATE - Phone number validation at credit card payments.

## 1.0.5

- FIX - Issues with business customers.
- FIX - Update of the payment button at the order confirmation page.

## 1.0.4

- FIX - Phone number formatting.

## 1.0.3

- FIX - Issues in combination with pre payment.

## 1.0.2

- FIX - Display Klarna payment method for guests.
- FIX - Display Payment button.

## 1.0.1

- Release of the Mollie plugin.