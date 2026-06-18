<?php

declare(strict_types=1);

$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{widerruf_legend},widerruf_notification_status_change';

$GLOBALS['TL_DCA']['tl_settings']['fields']['widerruf_notification_status_change'] = [
    'exclude' => false,
    'inputType' => 'select',
    'options_callback' => [\Kirstenroschanski\ContaoWiderrufBundle\Content\NotificationOptionsProvider::class, 'getStatusChangeNotifications'],
    'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
    'sql' => "int unsigned NOT NULL default 0",
];
