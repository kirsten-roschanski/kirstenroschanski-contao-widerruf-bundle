<?php

declare(strict_types=1);

namespace Kirstenroschanski\ContaoWiderrufBundle\Checkout;

use Contao\Config;
use Doctrine\DBAL\Connection;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class RevocationService
{
    public function __construct(
        private readonly Connection $connection,
        private readonly MailerInterface $mailer,
    ) {
    }

    public function submitRevocation(array $payload): array
    {
        $consumerName = trim((string) ($payload['consumer_name'] ?? ''));
        $contractReference = trim((string) ($payload['contract_reference'] ?? ''));
        $confirmationEmail = trim((string) ($payload['confirmation_email'] ?? ''));
        $scopeType = 'full';
        $scopeDetails = '';
        $orderUuid = trim((string) ($payload['order_uuid'] ?? ''));
        $selectedItems = [];

        if ('' === $consumerName || '' === $contractReference || '' === $confirmationEmail) {
            throw new \InvalidArgumentException('Bitte Name, Vertragsangaben und E-Mail ausfüllen.');
        }

        if (!filter_var($confirmationEmail, \FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Bitte eine gültige E-Mail-Adresse angeben.');
        }

        $time = time();

        $this->connection->insert('tl_widerruf', [
            'tstamp' => $time,
            'created_at' => $time,
            'status' => 'new',
            'status_changed_at' => $time,
            'order_id' => 0,
            'order_uuid' => $orderUuid,
            'consumer_name' => $consumerName,
            'contract_reference' => $contractReference,
            'confirmation_email' => $confirmationEmail,
            'scope_type' => $scopeType,
            'scope_details' => $scopeDetails,
            'selected_items' => [] === $selectedItems ? null : implode("\n", $selectedItems),
            'request_source_uuid' => '' !== $orderUuid ? '1' : '',
            'request_payload' => json_encode($payload, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES),
        ]);

        $revocationId = (int) $this->connection->lastInsertId();

        $this->sendConfirmationMail(
            $confirmationEmail,
            $consumerName,
            $contractReference,
            $scopeType,
            $scopeDetails,
            $selectedItems,
            $revocationId
        );

        return [
            'revocation_id' => $revocationId,
        ];
    }

    private function sendConfirmationMail(
        string $to,
        string $name,
        string $contractReference,
        string $scopeType,
        string $scopeDetails,
        array $selectedItems,
        int $revocationId
    ): void {
        $from = (string) (Config::get('adminEmail') ?: 'noreply@localhost');
        $scopeLabel = 'full' === $scopeType ? 'gesamte Bestellung' : 'Teilwiderruf';
        $itemsText = [] === $selectedItems ? '-' : implode("\n", $selectedItems);
        $detailsText = '' === $scopeDetails ? '-' : $scopeDetails;

        $body = implode("\n", [
            'Hallo '.$name.',',
            '',
            'wir bestätigen den Eingang deines Widerrufs.',
            'Vorgangsnummer: '.$revocationId,
            'Vertragsbezug: '.$contractReference,
            'Widerrufsumfang: '.$scopeLabel,
            'Ausgewählte Positionen:',
            $itemsText,
            'Weitere Angaben:',
            $detailsText,
            '',
            'Ein Grund für den Widerruf ist nicht erforderlich.',
            '',
            'Viele Grüße',
            'Market Gardening Marburg',
        ]);

        $email = (new Email())
            ->from($from)
            ->to($to)
            ->subject('Bestätigung deines Widerrufs')
            ->text($body);

        $this->mailer->send($email);
    }
}
