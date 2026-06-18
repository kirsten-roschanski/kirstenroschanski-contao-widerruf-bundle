<?php

declare(strict_types=1);

$GLOBALS['TL_LANG']['tl_widerruf']['revocation_legend'] = 'Revocation';
$GLOBALS['TL_LANG']['tl_widerruf']['status_legend'] = 'Status';
$GLOBALS['TL_LANG']['tl_widerruf']['meta_legend'] = 'Technical data';
$GLOBALS['TL_LANG']['tl_widerruf']['status_options'] = [
	'new' => 'New',
	'processing' => 'In progress',
	'resolved' => 'Resolved',
	'rejected' => 'Rejected',
];
$GLOBALS['TL_LANG']['tl_widerruf']['status_changed_at'] = ['Status changed', 'Time of last status change'];
$GLOBALS['TL_LANG']['tl_widerruf']['export_csv'] = ['Export CSV', 'Download revocations as CSV'];
$GLOBALS['TL_LANG']['tl_widerruf']['export_json'] = ['Export JSON', 'Download revocations as JSON'];