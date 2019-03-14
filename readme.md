# Mollie plugin for plentymarkets

With Mollie, you can accept payments and donations online and expand your customer base internationally with support for all major payment methods through a single integration. No need to spend weeks on paperwork or security compliance procedures. No more lost conversions because you don’t support a shopper’s favourite payment method or because they don’t feel safe. We made our products and API expansive, intuitive, and safe for merchants, customers and developers alike. 

Mollie requires no minimum costs, no fixed contracts, no hidden costs. At Mollie you only pay for successful transactions. More about this pricing model can be found [here](https://www.mollie.com/en/pricing/). You can create an account [here](https://www.mollie.com/dashboard/signup/5543454?lang=en). The Mollie plentymarkets plugin quickly integrates all major payment methods ready-made into your plentymarkets frontend.
   

## Supported Mollie Payment Methods ##
- Credit card

- Klarna

- PayPal

- SOFORT Banking

- Giropay

- EPS

- Postepay

- Carte Bancaire

- iDEAL

- Bancontact

- Belfius Pay Button

- ING Home'Pay

- KBC/CBC Pay button

- paysafecard

- SEPA bank transfer

- Giftcards 

## Setup

### Basic plugin setup

1. Go to **Plugins » Plugin overview**.
2. Click on the **Mollie** plugin.
3. Open the **Configuration**.
4. Add your test- and productive api keys, choose your current active mode and click the **Save** button.

### Payment settings

1. Go to **Settings » Orders » Payments » Mollie**.
2. Click on the **Search button**.
3. Click on the payment method you want to configure.
4. Edit the payment settings and click the **Save** button.

The payment method will now be installed in your plentymarkets system. It will be visible and your backend and frontend.

### Containers

1. Go to **CMS » Container links**.
2. Select the content that should be linked.
3. Select one or more containers in which the previously selected content should be displayed. Pay attention to the information provided in table 1.
4. **Save** the settings.<br />→ The content is linked to the containers.

<table>
<caption>Table 1: Linking template containers</caption>
	<thead>
	    <tr>
            <th>
                Content
            </th>
            <th>
                Explanation
            </th>
        </tr>
	</thead>
	<tbody>
		<tr>
        	<td>
        		<b>Payment button (Mollie)</b>
        	</td>
        	<td>
        	    The containers <strong>Order confirmation: Additional payment information</strong> and <strong>My account: Additional payment information</strong> have to be linked to display the payment Mollie payment button in the checkout.
            </td>
        </tr>
	</tbody>
</table>