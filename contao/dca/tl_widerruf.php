<?php

declare(strict_types=1);

$GLOBALS['TL_DCA']['tl_widerruf'] = [
    'config' => [
        'dataContainer' => \Contao\DC_Table::class,
        'enableVersioning' => true,
        'onload_callback' => [
            [\Kirstenroschanski\ContaoWiderrufBundle\Content\WiderrufBackendCallbacks::class, 'handleKeyAction'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'created_at' => 'index',
                'status' => 'index',
                'order_id' => 'index',
                'order_uuid' => 'index',
                'confirmation_email' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => 1,
            'fields' => ['created_at'],
            'flag' => 6,
            'panelLayout' => 'search,limit;filter',
        ],
        'label' => [
            'label_callback' => [\Kirstenroschanski\ContaoWiderrufBundle\Content\WiderrufBackendCallbacks::class, 'renderLabel'],
        ],
        'operations' => [
            'edit' => ['href' => 'act=edit', 'icon' => 'edit.svg'],
            'show' => ['href' => 'act=show', 'icon' => 'show.svg'],
            'delete' => ['href' => 'act=delete', 'icon' => 'delete.svg', 'attributes' => 'onclick="if(!confirm(\'Soll der Datensatz wirklich geloescht werden?\'))return false;Backend.getScrollOffset()"'],
        ],
        'global_operations' => [
            'export_csv' => [
                'label' => &$GLOBALS['TL_LANG']['tl_widerruf']['export_csv'],
                'href' => 'key=export_csv',
                'icon' => 'table.svg',
            ],
            'export_json' => [
                'label' => &$GLOBALS['TL_LANG']['tl_widerruf']['export_json'],
                'href' => 'key=export_json',
                'icon' => 'files.svg',
            ],
        ],
    ],
    'palettes' => [
        'default' => '{status_legend},status,status_changed_at;{revocation_legend},created_at,order_id,order_uuid,consumer_name,contract_reference,confirmation_email,scope_type,scope_details,selected_items;{meta_legend},request_source_uuid,request_payload',
    ],
    'fields' => [
        'id' => ['sql' => 'int unsigned NOT NULL auto_increment'],
        'pid' => ['sql' => 'int unsigned NOT NULL default 0'],
        'tstamp' => ['sql' => 'int unsigned NOT NULL default 0'],
        'sorting' => ['sql' => 'int unsigned NOT NULL default 0'],
        'created_at' => ['sql' => 'int unsigned NOT NULL default 0'],
        'status' => [
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options' => ['new', 'processing', 'resolved', 'rejected'],
            'reference' => &$GLOBALS['TL_LANG']['tl_widerruf']['status_options'],
            'eval' => ['chosen' => true, 'mandatory' => true, 'tl_class' => 'w50'],
            'save_callback' => [[\Kirstenroschanski\ContaoWiderrufBundle\Content\WiderrufBackendCallbacks::class, 'onStatusSave']],
            'sql' => "varchar(16) NOT NULL default 'new'",
        ],
        'status_changed_at' => [
            'exclude' => true,
            'sorting' => true,
            'flag' => 6,
            'inputType' => 'text',
            'eval' => ['readonly' => true, 'disabled' => true, 'tl_class' => 'w50'],
            'sql' => 'int unsigned NOT NULL default 0',
        ],
        'order_id' => ['sql' => 'int unsigned NOT NULL default 0'],
        'order_uuid' => ['sql' => "varchar(36) NOT NULL default ''"],
        'consumer_name' => ['sql' => "varchar(255) NOT NULL default ''"],
        'contract_reference' => ['sql' => "varchar(255) NOT NULL default ''"],
        'confirmation_email' => ['sql' => "varchar(255) NOT NULL default ''"],
        'scope_type' => ['sql' => "varchar(16) NOT NULL default 'full'"],
        'scope_details' => ['sql' => 'text NULL'],
        'selected_items' => ['sql' => 'text NULL'],
        'request_source_uuid' => ['sql' => "char(1) NOT NULL default ''"],
        'request_payload' => ['sql' => 'text NULL'],
    ],
];