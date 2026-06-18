<?php

declare(strict_types=1);

namespace Kirstenroschanski\ContaoWiderrufBundle\Content;

use Contao\Config;
use Contao\DataContainer;
use Contao\Input;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WiderrufBackendCallbacks
{
    public function __construct(
        private readonly Connection $connection,
        private readonly MailerInterface $mailer,
    ) {
    }

    public function getStatusOptions(): array
    {
        return ['new', 'processing', 'resolved', 'rejected'];
    }

    public function onStatusSave(mixed $value, DataContainer $dc): mixed
    {
        $status = (string) $value;
        $recordId = (int) ($dc->id ?? 0);

        if ($recordId <= 0) {
            return $status;
        }

        $record = $this->connection->fetchAssociative('SELECT status, consumer_name, confirmation_email, contract_reference, order_uuid FROM tl_widerruf WHERE id = :id LIMIT 1', ['id' => $recordId]);

        if (!$record) {
            return $status;
        }

        if (($record['status'] ?? '') === $status) {
            return $status;
        }

        $now = time();

        $this->connection->update('tl_widerruf', ['status_changed_at' => $now], ['id' => $recordId]);

        $this->sendStatusMail(
            (string) ($record['confirmation_email'] ?? ''),
            (string) ($record['consumer_name'] ?? ''),
            (string) ($record['contract_reference'] ?? ''),
            (string) ($record['order_uuid'] ?? ''),
            $status
        );

        return $status;
    }

    public function renderLabel(array $row, string $label, DataContainer $dc, array $labels): string
    {
        $status = (string) ($row['status'] ?? 'new');
        $statusLabel = $this->getStatusLabel($status);
        $consumerName = StringUtil::specialchars((string) ($row['consumer_name'] ?? ''));
        $confirmationEmail = StringUtil::specialchars((string) ($row['confirmation_email'] ?? ''));
        $createdAt = !empty($row['created_at']) ? date('d.m.Y H:i', (int) $row['created_at']) : '-';
        $statusChangedAt = !empty($row['status_changed_at']) ? date('d.m.Y H:i', (int) $row['status_changed_at']) : '-';
        $orderUuid = StringUtil::specialchars((string) ($row['order_uuid'] ?? ''));

        return sprintf(
            '<div style="display:grid;gap:.35rem;padding:.35rem 0;">'
            .'<div style="display:flex;flex-wrap:wrap;gap:.5rem;align-items:center;">'
            .'<strong style="font-size:1rem;">#%s %s</strong>'
            .'<span style="display:inline-flex;align-items:center;border-radius:999px;padding:.2rem .65rem;background:%s;color:#fff;font-size:.75rem;font-weight:700;">%s</span>'
            .'</div>'
            .'<div style="color:#495261;">%s · %s</div>'
            .'<div style="color:#6b7280;font-size:.85rem;">UUID: %s · Erfasst: %s · Status geändert: %s</div>'
            .'</div>',
            (string) ($row['id'] ?? ''),
            $consumerName !== '' ? $consumerName : '-',
            $this->getStatusColor($status),
            StringUtil::specialchars($statusLabel),
            $consumerName !== '' ? $consumerName : '-',
            $confirmationEmail !== '' ? $confirmationEmail : '-',
            $orderUuid !== '' ? $orderUuid : '-',
            $createdAt,
            $statusChangedAt
        );
    }

    public function handleKeyAction(): void
    {
        $key = (string) Input::get('key');

        if ('export_csv' === $key) {
            $this->exportCsv();

            exit;
        }

        if ('export_json' === $key) {
            $this->exportJson();

            exit;
        }
    }

    private function exportCsv(): void
    {
        $rows = $this->fetchRows();
        $response = new StreamedResponse(function () use ($rows): void {
            $output = fopen('php://output', 'wb');

            fputcsv($output, ['ID', 'Status', 'Name', 'E-Mail', 'Vertragsbezug', 'Bestell-UUID', 'Erfasst', 'Status geändert'], ';');

            foreach ($rows as $row) {
                fputcsv($output, [
                    $row['id'] ?? '',
                    $this->getStatusLabel((string) ($row['status'] ?? 'new')),
                    $row['consumer_name'] ?? '',
                    $row['confirmation_email'] ?? '',
                    $row['contract_reference'] ?? '',
                    $row['order_uuid'] ?? '',
                    !empty($row['created_at']) ? date('d.m.Y H:i', (int) $row['created_at']) : '',
                    !empty($row['status_changed_at']) ? date('d.m.Y H:i', (int) $row['status_changed_at']) : '',
                ], ';');
            }

            fclose($output);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="widerrufe.csv"');
        $response->send();
    }

    private function exportJson(): void
    {
        $rows = $this->fetchRows();
        $response = new StreamedResponse(function () use ($rows): void {
            echo json_encode($rows, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_PRETTY_PRINT);
        });

        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="widerrufe.json"');
        $response->send();
    }

    private function fetchRows(): array
    {
        return $this->connection->fetchAllAssociative('SELECT id, status, consumer_name, confirmation_email, contract_reference, order_uuid, created_at, status_changed_at FROM tl_widerruf ORDER BY created_at DESC, id DESC');
    }

    private function sendStatusMail(string $to, string $name, string $contractReference, string $orderUuid, string $status): void
    {
        if ('' === trim($to)) {
            return;
        }

        $from = (string) (Config::get('adminEmail') ?: 'noreply@localhost');
        $statusLabel = $this->getStatusLabel($status);
        $body = implode("\n", [
            'Hallo '.$name.',',
            '',
            'der Status deines Widerrufs wurde aktualisiert.',
            'Neuer Status: '.$statusLabel,
            'Vertragsbezug: '.$contractReference,
            '' !== $orderUuid ? 'UUID: '.$orderUuid : 'UUID: -',
            '',
            'Viele Gruesse',
            'Dein Widerrufs-Team',
        ]);

        $email = (new Email())
            ->from($from)
            ->to($to)
            ->subject('Status deines Widerrufs: '.$statusLabel)
            ->text($body);

        $this->mailer->send($email);
    }

    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'processing' => 'In Bearbeitung',
            'resolved' => 'Erledigt',
            'rejected' => 'Abgelehnt',
            default => 'Neu',
        };
    }

    private function getStatusColor(string $status): string
    {
        return match ($status) {
            'processing' => '#b45309',
            'resolved' => '#166534',
            'rejected' => '#991b1b',
            default => '#1d4ed8',
        };
    }
}