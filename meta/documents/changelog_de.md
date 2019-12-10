# Release Notes für Mollie

## 1.3.1

- FIX - Es wurde ein Problem mit der Übermittlung von Card-Tokens behoben.

## 1.3.0

- NEW - Die Zahlungsarten können nun über eine Einstellung für ein PluginSet deaktiviert werden. Dadurch kann man das Plugin nur für Ereignisaktionen nutzen.
- NEW - Mollie Components wurden für Kreditkarten Zahlungen eingebaut und können über die Einstellungen aktiviert werden.


## 1.2.2

- FIX - Es gab ein Problem mit den Zahlungsvorgang für bestehende Aufträge mit enthaltenen Rabatten auszuführen. Dieses Problem wurde behoben.
- FIX - Teilgutschriften konnten für Aufträge die einen Rabatt enthalten nicht registriert werden. Dieser Fehler wurde behoben.
- NEW - Die Übermittlung von Teilgutschriften und Versandmeldungen wurde überarbeitet um abgelehnte Anmeldungen besser nachvollziehen zu können.


## 1.2.1

- NEW - Es wurden neue logs eingebaut um die Kommunikation mit der Mollie-API besser nachvollziehen zu können.
- FIX - Es gab ein Problem die Zahlung erneut auszuführen, wenn der Auftrag über den Bestellbestätigungslink geöffnet wurde. Dieser fehler wurde nun behoben.

## 1.2.0

- FIX - Netto Warenkörbe wurden fälschlicherweise dennoch mit Brutto-Beträgen an Mollie übertragen. Dieser Fehler wurde nun behoben.
- NEW - ApplePay wurde als mögliche Zahlungsart hinzugefügt.
- UPDATE - Es können nun Teilgutschriften an Mollie über die bestehende Ereignis-Aktion übermittelt werden.
- UPDATE - Die Ermittlung der E-Mail Adresse wurde erweitert. Sollte die Rechnungsadresse keine E-Mail beinhalten, wird nun alternativ die E-Mail
des Kontakts übertragen.
- UPDATE - Die Übermittlung der plentymarkets Auftrags Id wurde erweitert. Die Auftrags Id wird nun zusätzlich bei Transaktionen aktualisiert.

## 1.1.1

- UPDATE - Telefonnummern-Formattierung wurde überarbeitet.

## 1.1.0

- UPDATE - Checkoutprozess wurde umgebaut.

## 1.0.6

- UPDATE - Telefonnummer Prüfung bei Kreditkarte.

## 1.0.5

- FIX - Probleme bei Firmenkunden.
- FIX - Aktualisierung des Kauf-Buttons auf der Bestellbestätigungs-Seite.

## 1.0.4

- FIX - Telefonnummer Formatierung.

## 1.0.3

- FIX - Probleme in Verbindung mit Vorkasse.

## 1.0.2

- FIX - Darstellung der Klarna Zahlungsart für Gäste.
- FIX - Darstellung des `Bezahlen` Button.

## 1.0.1

- Release des Mollie Plugins.