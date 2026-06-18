<?php

declare(strict_types=1);

use Kirstenroschanski\ContaoWiderrufBundle\Model\RevocationModel;

$GLOBALS['BE_MOD']['content']['widerrufe'] = [
    'tables' => ['tl_widerruf'],
];

$GLOBALS['TL_MODELS']['tl_widerruf'] = RevocationModel::class;

// Notification Center (1.x compatible) custom notification types.
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['widerruf']['widerruf_form_submit'] = [
    'email_subject' => [
        'revocation_id',
        'consumer_name',
        'contract_reference',
        'order_uuid',
    ],
    'email_text' => [
        'revocation_id',
        'consumer_name',
        'confirmation_email',
        'contract_reference',
        'order_uuid',
        'created_at',
    ],
    'email_html' => [
        'revocation_id',
        'consumer_name',
        'confirmation_email',
        'contract_reference',
        'order_uuid',
        'created_at',
    ],
    'file_name' => [
        'revocation_id',
        'order_uuid',
    ],
    'file_content' => [
        'revocation_id',
        'consumer_name',
        'confirmation_email',
        'contract_reference',
        'order_uuid',
        'created_at',
    ],
];

$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['widerruf']['widerruf_status_change'] = [
    'email_subject' => [
        'revocation_id',
        'status',
        'status_label',
        'consumer_name',
        'contract_reference',
        'order_uuid',
    ],
    'email_text' => [
        'revocation_id',
        'status',
        'status_label',
        'consumer_name',
        'confirmation_email',
        'contract_reference',
        'order_uuid',
        'status_changed_at',
    ],
    'email_html' => [
        'revocation_id',
        'status',
        'status_label',
        'consumer_name',
        'confirmation_email',
        'contract_reference',
        'order_uuid',
        'status_changed_at',
    ],
    'file_name' => [
        'revocation_id',
        'order_uuid',
    ],
    'file_content' => [
        'revocation_id',
        'status',
        'status_label',
        'consumer_name',
        'confirmation_email',
        'contract_reference',
        'order_uuid',
        'status_changed_at',
    ],
];
