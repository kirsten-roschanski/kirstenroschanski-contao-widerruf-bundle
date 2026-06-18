# Contao Widerruf Bundle

Diese Erweiterung stellt den Widerrufsprozess für Contao bereit.

## Notification Center: E-Mail-Textvorlagen

Die folgenden Vorlagen können im Notification Center als Nachrichtentexte verwendet werden.

### Verfügbare Platzhalter (Vorschlag)

Wenn du die Daten aus `tl_widerruf` als Tokens übergibst, sind diese Felder für die Mails sinnvoll:

- `##revocation_id##`
- `##status##`
- `##status_label##`
- `##consumer_name##`
- `##confirmation_email##`
- `##contract_reference##`
- `##order_uuid##`
- `##created_at##`
- `##status_changed_at##`

## Vorlage 1: Eingangsbestätigung (DE)

### Betreff

`Bestätigung deines Widerrufs (Vorgang ##revocation_id##)`

### Text

```text
Hallo ##consumer_name##,

wir bestätigen den Eingang deines Widerrufs.

Vorgangsnummer: ##revocation_id##
Vertragsbezug: ##contract_reference##
Bestell-UUID: ##order_uuid##
Eingegangen am: ##created_at##

Wir prüfen deinen Vorgang und informieren dich über den weiteren Verlauf.

Viele Grüße
Market Gardening Marburg
```

## Vorlage 2: Status-Update (DE)

### Betreff

`Status deines Widerrufs: ##status_label##`

### Text

```text
Hallo ##consumer_name##,

der Status deines Widerrufs wurde aktualisiert.

Neuer Status: ##status_label##
Vorgangsnummer: ##revocation_id##
Vertragsbezug: ##contract_reference##
Bestell-UUID: ##order_uuid##
Geändert am: ##status_changed_at##

Viele Grüße
Dein Widerrufs-Team
```

## Vorlage 3: Status "In Bearbeitung" (DE)

### Betreff

`Dein Widerruf wird bearbeitet (##revocation_id##)`

### Text

```text
Hallo ##consumer_name##,

wir haben deinen Widerruf erhalten und bearbeiten ihn aktuell.

Vorgangsnummer: ##revocation_id##
Vertragsbezug: ##contract_reference##

Wir melden uns, sobald dein Vorgang abgeschlossen ist.

Viele Grüße
Dein Widerrufs-Team
```

## Vorlage 4: Status "Erledigt" (DE)

### Betreff

`Dein Widerruf wurde abgeschlossen (##revocation_id##)`

### Text

```text
Hallo ##consumer_name##,

dein Widerruf wurde abgeschlossen.

Vorgangsnummer: ##revocation_id##
Vertragsbezug: ##contract_reference##
Bestell-UUID: ##order_uuid##

Falls du Rückfragen hast, antworte einfach auf diese E-Mail.

Viele Grüße
Dein Widerrufs-Team
```

## Vorlage 5: Status "Abgelehnt" (DE)

### Betreff

`Rückmeldung zu deinem Widerruf (##revocation_id##)`

### Text

```text
Hallo ##consumer_name##,

zu deinem Widerruf benötigen wir weitere Klärung bzw. konnten den Vorgang derzeit nicht positiv abschließen.

Vorgangsnummer: ##revocation_id##
Vertragsbezug: ##contract_reference##
Bestell-UUID: ##order_uuid##

Bitte antworte auf diese E-Mail, damit wir den Vorgang gemeinsam klären können.

Viele Grüße
Dein Widerrufs-Team
```

## Hinweise zur Einrichtung

1. Lege im Notification Center für jeden Status eine eigene Benachrichtigung an.
2. Hinterlege die passenden Tokens aus deinem Hook/Service, der die NC-Nachricht auslöst.
3. Verwende für Datumswerte ein einheitliches Format, z. B. `d.m.Y H:i`.
4. Nutze als Empfänger `##confirmation_email##`.
