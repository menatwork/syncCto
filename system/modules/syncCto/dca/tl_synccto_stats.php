<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
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
