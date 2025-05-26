<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

use ContaoCommunityAlliance\DcGeneral\DC\General;

$GLOBALS['TL_DCA']['tl_syncCto_clients_showExtern'] = [
    'config'     => [
        'dataContainer' => General::class,
        'closed'        => true,
        'disableSubmit' => false
    ],
    'dca_config' => [
        'data_provider' => [
            'default' => [
                'class'  => 'ContaoCommunityAlliance\DcGeneral\Data\NoOpDataProvider',
                'source' => 'tl_syncCto_clients_showExtern'
            ],
        ],
    ],
    'palettes'   => [
        'default' => '',
    ],
];
