# Mollie plugin for plentymarkets

With Mollie, you can accept payments and donations online and expand your customer base internationally with support for all major payment methods through a single integration. No need to spend weeks on paperwork or security compliance procedures. No more lost conversions because you don’t support a shopper’s favourite payment method or because they don’t feel safe. We made our products and API expansive, intuitive, and safe for merchants, customers and developers alike. 

Mollie requires no minimum costs, no fixed contracts, no hidden costs. At Mollie you only pay for successful transactions. More about this pricing model can be found [here](https://www.mollie.com/en/pricing/). You can create an account [here](https://www.mollie.com/dashboard/signup/5543454?lang=de). The Mollie plentymarkets plugin quickly integrates all major payment methods ready-made into your plentymarkets frontend.
   

## Supported Mollie Payment Methods ##
- iDEAL

- Creditcard

- CartaSi & Cartes Bancaires

- Bancontact

- Belfius Pay Button

- ING HomePay

- KBC/CBC-Betaalknop

- SOFORT Banking

- EPS

- Giropay

- PayPal

- Bitcoin

- Paysafecard

- Klarna

- SEPA bank transfer

- Giftcards 

## Setup

### Basic plugin setup

1. Go to **Plugins » Plugin overview**.
2. Click on the **Mollie** plugin.
3. Open the **Configuration**.
4. Add your test- and productive api keys, choose your current active mode and click the **Save** button.
5. Select **Payments in set webstores inactive** if you want to use only the event procedures in this plugin set.
6. Select **Use Mollie Components** if you want to use Mollie Components for credit card payments.

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

### Event procedures

1. Go to **System » Orders » Event procedures**.
2. Click on **Add event procedure**. </br> → The **Create new event procedure** window opens.
3. Enter the name.
4. Select the event listed in tables 2-4.
5. **Save** the settings.
6. Pay attention to the explanations given in tables 2-4 and carry out the settings as desired.
7. Place a check mark next to the option **Active**.
8. **Save** the settings.

#### Register shipment at mollie

<table>
	<thead>
	    <tr>
            <th>
                Setting
            </th>
            <th>
                Option
            </th>
            <th>
                Selection
            </th>
        </tr>
	</thead>
	<tbody>
        <tr>
            <td><strong>Event</strong></td>
            <td><strong>Select the event to trigger a shipment.</strong></td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Filter 1</strong></td>
            <td><strong>Order &gt; Payment method</strong></td>
            <td><strong>Plugin: mollie: *</strong></td>
        </tr>
        <tr>
            <td><strong>Procedure</strong></td>
            <td><strong>Plugin &gt; Register shipment at mollie</strong></td>
            <td>&nbsp;</td>
      </tr>
    </tbody>
	<caption>
		Table 2: Event procedure "Register shipment at mollie"
	</caption>
</table>

#### Register cancellation at mollie

<table>
	<thead>
	    <tr>
            <th>
                Setting
            </th>
            <th>
                Option
            </th>
            <th>
                Selection
            </th>
        </tr>
	</thead>
	<tbody>
        <tr>
            <td><strong>Event</strong></td>
            <td><strong>Select the event to trigger a shipment.</strong></td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Filter 1</strong></td>
            <td><strong>Order &gt; Payment method</strong></td>
            <td><strong>Plugin: mollie: *</strong></td>
        </tr>
        <tr>
            <td><strong>Procedure</strong></td>
            <td><strong>Plugin &gt; Register cancellation at mollie</strong></td>
            <td>&nbsp;</td>
      </tr>
    </tbody>
	<caption>
		Table 3: Event procedure "Register cancellation at mollie"
	</caption>
</table>

#### Register refund at mollie

<table>
	<thead>
	    <tr>
            <th>
                Setting
            </th>
            <th>
                Option
            </th>
            <th>
                Selection
            </th>
        </tr>
	</thead>
	<tbody>
        <tr>
            <td><strong>Event</strong></td>
            <td><strong>Select the event to trigger a shipment.</strong></td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Filter 1</strong></td>
            <td><strong>Order &gt; Payment method</strong></td>
            <td><strong>Plugin: mollie: *</strong></td>
        </tr>
        <tr>
            <td><strong>Procedure</strong></td>
            <td><strong>Plugin &gt; Register refund at mollie</strong></td>
            <td>&nbsp;</td>
      </tr>
    </tbody>
	<caption>
		Table 3: Event procedure "Register refund at mollie"
	</caption>
</table>