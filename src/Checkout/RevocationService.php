<?php

declare(strict_types=1);

namespace Kirstenroschanski\ContaoWiderrufBundle\Checkout;

use Contao\Config;
use Contao\System;
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
        $notificationId = max(0, (int) ($payload['notification_id'] ?? 0));
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
            $revocationId,
            $notificationId,
            $orderUuid,
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
        int $revocationId,
        int $notificationId,
        string $orderUuid,
    ): void {
        if ('' === trim($to)) {
            return;
        }

        if ($this->sendConfirmationViaNotificationCenter($to, $name, $contractReference, $scopeType, $scopeDetails, $selectedItems, $revocationId, $notificationId, $orderUuid)) {
            return;
        }

        $from = $this->resolveSenderAddress();

        if (null === $from) {
            return;
        }

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

        try {
            $this->mailer->send($email);
        } catch (\Throwable) {
            // Do not fail the revocation submit if SMTP sender policy rejects the message.
        }
    }

    private function resolveSenderAddress(): ?string
    {
        $sender = trim((string) Config::get('adminEmail'));

        if ('' === $sender) {
            return null;
        }

        if (!filter_var($sender, \FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        if (str_ends_with(strtolower($sender), '@localhost')) {
            return null;
        }

        return $sender;
    }

    private function sendConfirmationViaNotificationCenter(
        string $to,
        string $name,
        string $contractReference,
        string $scopeType,
        string $scopeDetails,
        array $selectedItems,
        int $revocationId,
        int $notificationId,
        string $orderUuid,
    ): bool {
        $notificationCenterClass = 'Terminal42\\NotificationCenterBundle\\NotificationCenter';

        if (!class_exists($notificationCenterClass)) {
            return false;
        }

        if ($notificationId <= 0) {
            $notificationId = (int) $this->connection->fetchOne(
                'SELECT id FROM tl_nc_notification WHERE type = :type ORDER BY id ASC LIMIT 1',
                ['type' => 'widerruf_form_submit']
            );
        }

        if ($notificationId <= 0) {
            return false;
        }

        try {
            $container = System::getContainer();

            if (!$container->has($notificationCenterClass)) {
                return false;
            }

            $notificationCenter = $container->get($notificationCenterClass);

            if (!method_exists($notificationCenter, 'sendNotification')) {
                return false;
            }

            $notificationCenter->sendNotification($notificationId, [
                'revocation_id' => (string) $revocationId,
                'consumer_name' => $name,
                'confirmation_email' => $to,
                'contract_reference' => $contractReference,
                'order_uuid' => $orderUuid,
                'created_at' => date('d.m.Y H:i', time()),
            ]);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
