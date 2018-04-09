<?php

/**
 * This file is part of menatwork/synccto.
 *
 * (c) 2014-2018 MEN AT WORK.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    menatwork/synccto
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2014-2018 MEN AT WORK.
 * @license    https://github.com/menatwork/syncCto/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

/**
 * Table tl_synccto_stats
 */
$GLOBALS['TL_DCA']['tl_synccto_stats'] = array
(

    // Config
    'config' => array
    (
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary'
            )
        )
    ),

    // Fields
    'fields' => array
    (
        'id'              => array(
            'sql'                      => 'int(10) unsigned NOT NULL auto_increment'
        ),
        'tstamp'          => array(
            'sql'                      => "int(10) unsigned NOT NULL default '0'"
        ),
        'client_id'       => array(
            'sql'                      => "varchar(64) NOT NULL default ''"
        ),
        'sync_user'       => array(
            'sql'                      => "varchar(64) NOT NULL default ''"
        ),
        'sync_start'      => array(
            'sql'                      => "int(10) unsigned NOT NULL default '0'"
        ),
        'sync_end'        => array(
            'sql'                      => "int(10) unsigned NOT NULL default '0'"
        ),
        'sync_abort'      => array(
            'sql'                      => "int(10) unsigned NOT NULL default '0'"
        ),
        'sync_abort_step' => array(
            'sql'                      => "varchar(10) NOT NULL default ''"
        ),
        'sync_direction'  => array(
            'sql'                      => "int(10) unsigned NOT NULL default '0'"
        ),
        'sync_options'    => array(
            'sql'                      => "blob NULL"
        )
    )
);
