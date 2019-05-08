# Mollie plugin für plentymarkets

Mit Mollie können Sie mit Hilfe einer einzelnen Integration Ihren Kundenstamm international mit Hilfe von allen großen Zahlungsarten erweitern. Sie müssen sich nicht wochenlang mit Papierkram oder Sicherheits-Prüfungen herumschlagen. 

Mollie verlangt keinen Mindestumsatz, keine strikten Verträge, keine versteckte Kosten. Bei Mollie zahlen Sie nur für erfolgreiche Transaktionen. Mehr Infos über dieses Preismodell finden Sie [hier](https://www.mollie.com/en/pricing/). Sie können sich über folgenden Link [registrieren](https://www.mollie.com/dashboard/signup/5543454?lang=de). Integrieren Sie alle großen Zahlungsarten in Ihr plentymarkets Frontend.
   

## Unterstützte Zahlungsarten von Mollie ##
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

## Einrichtung

### Grundeinstellungen

1. Öffnen Sie das Menü **Plugins » Plugin-Übersicht**.
2. Klicken Sie auf das **Mollie** Plugin.
3. Öffnen Sie die **Konfiguration**
4. Hinterlegen Sie Ihre Test- and Produktiv-API Schlüssel, wählen Sie ihren aktiven Modus aus und **Speichern** Sie die Einstellungen.

### Zahlungsarten einstellen

1. Öffnen Sie das Menü **Einstellungen » Aufträge » Zahlung » Mollie**.
2. Klicken Sie den **Such Button**.
3. Wählen Sie die Zahlungsart die Sie bearbeiten möchten.
4. Nehmen Sie die Einstellungen an der Zahlungsart vor und **Speichern** Sie die Einstellungen.

Die Zahlungsart wird nun in Ihrem plentymarkets System installiert. Sie ist nun in Ihrem Backend und Frontend sichtbar.

### Container

1. Öffnen Sie das Menü **CMS » Container-Verknüpfungen**.
2. Wählen Sie den gewünschten Content, der verknüpft werden soll.
3. Wählen Sie einen oder mehrere Container, in denen der zuvor gewählte Content dargestellt werden soll. Beachten Sie dazu die Erläuterungen in Tabelle 1.
4. **Speichern** Sie die Einstellungen.<br /> → Die Contents sind mit den Containern verknüpft.

<table>
<caption>Table 1: Container verknüpfen</caption>
	<thead>
	    <tr>
            <th>
                Content
            </th>
            <th>
                Erläuterung
            </th>
        </tr>
	</thead>
	<tbody>
		<tr>
        	<td>
        		<b>Payment button (Mollie)</b>
        	</td>
        	<td>
        	    Die Container <strong>Order confirmation: Additional payment information</strong> und <strong>My account: Additional payment information</strong> müssen verknüpft sein, um den Mollie Zahlen-Button im Checkout darzustellen.
            </td>
        </tr>
	</tbody>
</table>

### Ereignisaktionen

1. Öffnen Sie das Menü **System » Aufträge » Ereignisaktionen**.
2. Klicken Sie auf **Ereignisaktion hinzufügen**.<br />
→ Das Fenster **Neue Ereignisaktion erstellen** wird geöffnet.
3. Geben Sie einen Namen ein.
4. Wählen Sie das Ereignis gemäß Tabellen 2-4.
5. **Speichern** Sie die Einstellungen.
6. Nehmen Sie die Einstellungen gemäß Tabellen 2-4 vor.
7. Setzen Sie ein Häkchen bei **Aktiv**.
8. **Speichern** Sie die Einstellungen.

#### Versand bei mollie anmelden

<table>
   <thead>
    </tr>
      <th>
         Einstellung
      </th>
      <th>
         Option
      </th>
      <th>
         Auswahl
      </th>
    </tr>
   </thead>
   <tbody>
      <tr>
         <td><strong>Ereignis</strong></td>
         <td><strong>Das Ereignis wählen, nach dem der Versand angemeldet werden soll.</strong></td>
         <td></td>
      </tr>
      <tr>
         <td><strong>Filter 1</strong></td>
         <td><strong>Auftrag > Zahlungsart</strong></td>
         <td><strong>Plugin: mollie: *</strong></td>
      </tr>
      <tr>
        <td><strong>Aktion</strong></td>
        <td><strong>Plugin > Versand bei mollie anmelden</strong></td>
        <td></td>
      </tr>
    </tbody>
   <caption>
      Tab. 2: Ereignisaktion "Versand bei mollie anmelden"
   </caption>
</table>

#### Storno bei mollie anmelden

<table>
   <thead>
    </tr>
      <th>
         Einstellung
      </th>
      <th>
         Option
      </th>
      <th>
         Auswahl
      </th>
    </tr>
   </thead>
   <tbody>
      <tr>
         <td><strong>Ereignis</strong></td>
         <td><strong>Das Ereignis wählen, nach dem ein Storno angemeldet werden soll.</strong></td>
         <td></td>
      </tr>
      <tr>
         <td><strong>Filter 1</strong></td>
         <td><strong>Auftrag > Zahlungsart</strong></td>
         <td><strong>Plugin: mollie: *</strong></td>
      </tr>
      <tr>
        <td><strong>Aktion</strong></td>
        <td><strong>Plugin > Storno bei mollie anmelden</strong></td>
        <td></td>
      </tr>
    </tbody>
   <caption>
      Tab. 3: Ereignisaktion "Storno bei mollie anmelden"
   </caption>
</table>

#### Gutschrift bei mollie anmelden

<table>
   <thead>
    </tr>
      <th>
         Einstellung
      </th>
      <th>
         Option
      </th>
      <th>
         Auswahl
      </th>
    </tr>
   </thead>
   <tbody>
      <tr>
         <td><strong>Ereignis</strong></td>
         <td><strong>Das Ereignis wählen, nach dem eine Gutschrift angemeldet werden soll.</strong></td>
         <td></td>
      </tr>
      <tr>
         <td><strong>Filter 1</strong></td>
         <td><strong>Auftrag > Zahlungsart</strong></td>
         <td><strong>Plugin: mollie: *</strong></td>
      </tr>
      <tr>
        <td><strong>Aktion</strong></td>
        <td><strong>Plugin > Gutschrift bei mollie anmelden</strong></td>
        <td></td>
      </tr>
    </tbody>
   <caption>
      Tab. 4: Ereignisaktion "Gutschrift bei mollie anmelden"
   </caption>
</table>

