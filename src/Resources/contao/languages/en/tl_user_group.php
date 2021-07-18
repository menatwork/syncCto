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
 * Legends
 */
$GLOBALS['TL_LANG']['tl_user_group']['syncCto_legend']        = 'SyncCto - Client permissions';
$GLOBALS['TL_LANG']['tl_user_group']['syncCto_tables_legend'] = 'SyncCto - Allowed tables';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_user_group']['syncCto_clients']               = [
    'Allowed clients',
    'Here you can grant access to one or more clients.'
];
$GLOBALS['TL_LANG']['tl_user_group']['syncCto_clients_p']             = [
    'Client permissions',
    'Here you can define the client permissions.'
];
$GLOBALS['TL_LANG']['tl_user_group']['syncCto_sync_options']          = [
    'Allowed file operations',
    'Here you can choose allowed file operations'
];
$GLOBALS['TL_LANG']['tl_user_group']['syncCto_tables']                = [
    'Allowed tables',
    'Here you can grant access to the database tables.'
];
$GLOBALS['TL_LANG']['tl_user_group']['syncCto_useTranslatedNames']    = [
    'Use human readable table names',
    'Enable this checkbox to display the human readable table names instead of the technical titles (tl_content, ...)'
];
$GLOBALS['TL_LANG']['tl_user_group']['syncCto_force_dbafs_overwrite'] = [
    'Force DBAFS overwrite',
    'When enabled the db table tl_files for the dbafs will be overwritten.'
];
$GLOBALS['TL_LANG']['tl_user_group']['syncCto_hide_auto_sync']      = [
    'Hidden auto sync button',
    'When enabled the auto sync button in the client overview will be hidden.'
];
