<?php

if (!defined('TL_ROOT'))
    die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  MEN AT WORK 2011 
 * @package    syncCto
 * @license    GNU/LGPL 
 * @filesource
 */
 
/**
 * Defines
 */
// Version
define("SYNCCTO_GET_VERSION", '1.1.0');

// Backup
define("SYNCCTO_SMALL", 1);
define("SYNCCTO_FULL", 2);

// Communication
define("SYNCCTO_COMMUNICATION_WAIT", 1);
define("SYNCCTO_COMMUNICATION_REPLAY", 3);

// File
define("FILE_ZIP", 'File-Backup.zip');


/**
 * Back end modules
 */
$i = array_search('profile', array_keys($GLOBALS['BE_MOD']));
$GLOBALS['BE_MOD'] = array_merge(
        array_slice($GLOBALS['BE_MOD'], 0, $i), array(
    'syncCto' => array(
        'syncCto_settings' => array(
            'tables' => array('tl_syncCto_settings'),
            'icon' => 'system/modules/syncCto/html/iconSettings.png'
        ),
        'synccto_clients' => array(
            'tables' => array('tl_synccto_clients', 'tl_syncCto_clients_syncTo', 'tl_syncCto_clients_syncFrom'),
            'icon' => 'system/modules/syncCto/html/iconClients.png',
            'callback' => 'SyncCtoModuleClient',
        ),
        'syncCto_backups' => array(
            'tables' => array('tl_syncCto_backup_file', 'tl_syncCto_backup_db', 'tl_syncCto_restore_file', 'tl_syncCto_restore_db'),
            'icon' => 'system/modules/syncCto/html/iconBackups.png',
            'callback' => 'SyncCtoModuleBackup',
        ),
        'syncCto_stats' => array(
            // 'tables' => array('tl_syncCto_settings'),
            'icon' => 'system/modules/syncCto/html/iconStats.png'
        ),
        'syncCto_check' => array(
            'icon' => 'system/modules/syncCto/html/iconCheck.png',
            'callback' => 'SyncCtoModuleCheck',
        )
    )
        ), array_slice($GLOBALS['BE_MOD'], $i)
);

$GLOBALS['TL_SYC'] = array_merge_recursive(array(
    'files' => array(
        'option_small' => SYNCCTO_SMALL,
        'option_full' => SYNCCTO_FULL,
    ),
        ), is_array($GLOBALS['TL_SYC']) ? $GLOBALS['TL_SYC'] : array()
);

/**
 * Hooks
 */
// Template
$GLOBALS['TL_HOOKS']['outputBackendTemplate'][] = array('SyncCtoCallback', 'outputBackendTemplate');

// Permissions
$GLOBALS['TL_PERMISSIONS'][] = 'syncCto_clients';
$GLOBALS['TL_PERMISSIONS'][] = 'syncCto_clients_p';
$GLOBALS['TL_PERMISSIONS'][] = 'syncCto_tables';

// Ajax
$GLOBALS['TL_HOOKS']['executePreActions'][]   = array('SyncCtoCallback', 'pingClientStatus');

/**
 * Callback is only used for overview screen
 */
if ($_GET['do'] == 'syncCto_backups' && strlen($_GET['table']) != 0 && strlen($_GET['act']) == 0)
{
    unset($GLOBALS['BE_MOD']['syncCto']['syncCto_backups']['callback']);
}

if (!($_GET['do'] == 'synccto_clients' && ($_GET['table'] == 'tl_syncCto_clients_syncTo' || $_GET['table'] == 'tl_syncCto_clients_syncFrom' ) && strlen($_GET['act']) != 0 ))
{
    unset($GLOBALS['BE_MOD']['syncCto']['synccto_clients']['callback']);
}

/**
 * Blacklists
 */
// Size limit for files in bytes, will be checked
$GLOBALS['syncCto']['size_limit'] = 104857600;
// Size limit for files in bytes, completely ignored
$GLOBALS['syncCto']['size_limit_ignore'] = 209715200;

// Tables
$GLOBALS['syncCto']['table_hidden'] = array(
    'tl_log',
    'tl_session',
    'tl_undo',
);

// Folders
$GLOBALS['syncCto']['folder_blacklist'] = array(
    'system/tmp*',
    'system/htm*',
    'system/logs*',
    'tl_files/syncCto_backups*',
);

// Files only Sync.
$GLOBALS['syncCto']['file_blacklist'] = array(
    "*.htaccess",
    "system/config/localconfig.php",
);

// Folders
$GLOBALS['syncCto']['local_blacklist'] = array(
    "websitePath",
    "installPassword",
    "encryptionKey",
    "dbDriver",
    "dbHost",
    "dbUser",
    "dbPass",
    "dbDatabase",
    "dbPconnect",
    "dbCharset",
    "dbPort",
    "syncCto_seckey",
);

/**
 * Whitelist
 */
// Folders
$GLOBALS['syncCto']['folder_whitelist'] = array(    
    'contao',
    'plugins',
    'system',
    'templates',
    'typolight',
);

/**
 * Global configuration
 */
// Folder path configuration
$GLOBALS['syncCto']['path']['db'] = "tl_files/syncCto_backups/database/";
$GLOBALS['syncCto']['path']['tmp'] = "system/tmp/";
$GLOBALS['syncCto']['path']['file'] = "tl_files/syncCto_backups/files/";
$GLOBALS['syncCto']['path']['debug'] = "tl_files/syncCto_backups/debug/";

// Timestamp for files
$GLOBALS['syncCto']['settings']['time_format'] = "Ymd_H-i-s";

/**
 * Codifyengines
 * You can change/remove/add the codifyengines here. 
 * 
 * --= Caution =--
 * -> You have to change the configuration on both systems.
 * -> Never change the Empty and Blow. Both are core engines.
 */
$GLOBALS["syncCto"]["codifyengine"] = array(
    "Empty" => array(
        "name" => &$GLOBALS['TL_LANG']['syncCto']['codifyengine_name']["Empty"],
        "classname" => "SyncCtoCodifyengineImpl_Empty",
        "folder" => "system/modules/syncCto",        
    ),
    "Mcrypt" => array(
        "name" => &$GLOBALS['TL_LANG']['syncCto']['codifyengine_name']["Mcrypt"],
        "classname" => "SyncCtoCodifyengineImpl_Empty",
        "folder" => "system/modules/syncCto",        
    ),
    "Blow" => array(
        "name" => &$GLOBALS['TL_LANG']['syncCto']['codifyengine_name']["Blow"],
        "classname" => "SyncCtoCodifyengineImpl_Empty",
        "folder" => "system/modules/syncCto",        
    ),
);

/**
 * CSS
 */
if (($_GET['do'] == 'syncCto_check')
        || (($_GET['do'] == 'group') && ($_GET['act'] == 'edit'))
        || (($_GET['do'] == 'user') && ($_GET['act'] == 'edit'))
        || (($_GET['do'] == 'syncCto_backups') && ($_GET['table'] == 'tl_syncCto_backup_db'))
        || (($_GET['do'] == 'synccto_clients') && ($_GET['table'] == 'tl_syncCto_clients_syncTo'))
        || (($_GET['do'] == 'synccto_clients') && ($_GET['table'] == 'tl_syncCto_clients_syncFrom')))
    $GLOBALS['TL_CSS'][] = 'system/modules/syncCto/html/syncCto.css';

if (($_GET['do'] == 'synccto_clients') && (strlen($_GET["act"]) == 0) && (strlen($_GET["table"]) == 0))
    $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/syncCto/html/syncCto.js';
?>
