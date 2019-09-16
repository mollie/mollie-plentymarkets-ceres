# Release Notes für Mollie

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