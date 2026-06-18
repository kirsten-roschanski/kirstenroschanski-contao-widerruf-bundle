<?php

declare(strict_types=1);

$GLOBALS['TL_DCA']['tl_content']['palettes']['widerruf'] = '{type_legend},type,headline,title;{widerruf_legend},widerruf_notification_form_submit,widerruf_success_message;{protected_legend:hide},protected;{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['fields']['widerruf_notification_form_submit'] = [
    'exclude' => false,
    'inputType' => 'select',
    'options_callback' => [\Kirstenroschanski\ContaoWiderrufBundle\Content\NotificationOptionsProvider::class, 'getFormSubmitNotifications'],
    'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
    'sql' => "int unsigned NOT NULL default 0",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['widerruf_success_message'] = [
    'exclude' => false,
    'inputType' => 'text',
    'eval' => ['maxlength' => 255, 'tl_class' => 'clr long'],
    'sql' => "varchar(255) NOT NULL default ''",
];
