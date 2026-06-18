<?php

declare(strict_types=1);

use Kirstenroschanski\ContaoWiderrufBundle\Model\RevocationModel;

$GLOBALS['BE_MOD']['kirstenroschanski']['widerrufe'] = [
    'tables' => ['tl_widerruf'],
];

$GLOBALS['TL_MODELS']['tl_widerruf'] = RevocationModel::class;
