<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_syncCto_stats'] = array
(
    'config'     => [
        'dataContainer'    => DC_Table::class,
        'enableVersioning' => false,
        'sql'              => [
            'keys' => [
                'id'        => 'primary',
                'tstamp'    => 'index',
                'client_id' => 'index',
            ]
        ]
    ],
    'list'       => [],
    'operations' => [],
    'fields'     => [
        'id'              => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment'
        ],
        'tstamp'          => [
            'sql' => 'int(10) unsigned NOT NULL default \'0\''
        ],
        'client_id'       => [
            'sql' => 'varchar(64) NOT NULL default \'\''
        ],
        'sync_user'       => [
            'sql' => 'varchar(64) NOT NULL default \'\''
        ],
        'sync_start'      => [
            'sql' => 'int(10) unsigned NOT NULL default \'0\''
        ],
        'sync_end'        => [
            'sql' => 'int(10) unsigned NOT NULL default \'0\''
        ],
        'sync_abort'      => [
            'sql' => 'int(10) unsigned NOT NULL default \'0\''
        ],
        'sync_abort_step' => [
            'sql' => 'varchar(10) NOT NULL default \'\''
        ],
        'sync_direction'  => [
            'sql' => 'int(10) unsigned NOT NULL default \'0\''
        ],
        'sync_options'    => [
            'sql' => 'blob NULL'
        ],
    ]
);
