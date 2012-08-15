<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

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
 * @copyright  MEN AT WORK 2012 
 * @package    syncCto
 * @license    GNU/LGPL 
 * @filesource
 */

$objInput = Input::getInstance();

/**
 * Current syncCto version
 */
$GLOBALS['SYC_VERSION'] = '2.2.0';

/**
 * Back end modules
 */
$i = array_search('profile', array_keys($GLOBALS['BE_MOD']));
$GLOBALS['BE_MOD'] = array_merge(
        array_slice($GLOBALS['BE_MOD'], 0, $i), array(
    'syncCto' => array(
        'syncCto_settings' => array(
            'tables' => array('tl_syncCto_settings'),
            'icon' => 'system/modules/syncCto/html/icons/nav/iconSettings.png'
        ),
        'synccto_clients' => array(
            'tables' => array('tl_synccto_clients', 'tl_syncCto_clients_syncTo', 'tl_syncCto_clients_syncFrom', 'tl_syncCto_clients_showExtern' ),
            'icon' => 'system/modules/syncCto/html/icons/nav/iconClients.png',
            'callback' => 'SyncCtoModuleClient',
            'stylesheet' => 'system/modules/syncCto/html/css/systemcheck.css',
        ),
        'syncCto_backups' => array(
            'tables' => array('tl_syncCto_backup_file', 'tl_syncCto_backup_db', 'tl_syncCto_restore_file', 'tl_syncCto_restore_db'),
            'icon' => 'system/modules/syncCto/html/icons/nav/iconBackups.png',
            'callback' => 'SyncCtoModuleBackup',
        ),
        'syncCto_check' => array(
            'icon' => 'system/modules/syncCto/html/icons/nav/iconCheck.png',
            'callback' => 'SyncCtoModuleCheck',
            'stylesheet' => 'system/modules/syncCto/html/css/systemcheck.css',
        )
    )
        ), array_slice($GLOBALS['BE_MOD'], $i)
);

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['executePreActions'][]     = array('SyncCtoHelper', 'pingClientStatus');
$GLOBALS['TL_HOOKS']['parseBackendTemplate'][]  = array('SyncCtoHelper', 'checkExtensions');
$GLOBALS['TL_HOOKS']['parseBackendTemplate'][]  = array('SyncCtoHelper', 'checkLockStatus');
$GLOBALS['TL_HOOKS']['addCustomRegexp'][]       = array('SyncCtoHelper', 'customRegexp');

/**
 * Permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'syncCto_clients';
$GLOBALS['TL_PERMISSIONS'][] = 'syncCto_clients_p';
$GLOBALS['TL_PERMISSIONS'][] = 'syncCto_sync_options';
$GLOBALS['TL_PERMISSIONS'][] = 'syncCto_tables';

/**
 * Callbacks are only used for overview screen
 */
$strDo = $objInput->get("do");
$strTable = $objInput->get("table");
$strAct = $objInput->get("act");

if ($strDo == 'syncCto_backups' && $strTable != '' && ($strAct == '' || $strAct == 'edit') && TL_MODE == 'BE')
{
    unset($GLOBALS['BE_MOD']['syncCto']['syncCto_backups']['callback']);
}

if ($strDo == 'synccto_clients' && $strAct != 'start' && in_array($strTable, array('tl_syncCto_clients_syncTo', 'tl_syncCto_clients_syncFrom', 'tl_syncCto_clients_showExtern', '')) && TL_MODE == 'BE')
{
    unset($GLOBALS['BE_MOD']['syncCto']['synccto_clients']['callback']);
}

/**
 * Include attention CSS
 */
if($GLOBALS['TL_CONFIG']['syncCto_attentionFlag'] == true)
{
    $GLOBALS['TL_CSS'][] = 'system/modules/syncCto/html/css/attention.css';
}

// Size limit for files in bytes, will be checked
$GLOBALS['SYC_SIZE']['limit'] = 524288000;
// Size limit for files in bytes, completely ignored
$GLOBALS['SYC_SIZE']['limit_ignore'] = 838860800;

/**
 * Blacklists
 */
// Tables
$GLOBALS['SYC_CONFIG']['table_hidden'] = array(
    'tl_log',
    'tl_lock',
    'tl_session',
    'tl_undo',
    'tl_version',
    'tl_synccto_clients',
    'tl_ctocom_cache',
    'tl_requestcache',
);

// Folders
$GLOBALS['SYC_CONFIG']['folder_blacklist'] = array(
    'system/html',
    'system/logs',
    'system/scripts',
    'system/tmp',
    '*/syncCto_backups/*',
);

// Files only sync.
$GLOBALS['SYC_CONFIG']['file_blacklist'] = array(
    '.htaccess',
    '*/localconfig.php',
);

// Folders
$GLOBALS['SYC_CONFIG']['local_blacklist'] = array(   
    'websitePath',
    'installPassword',
    'disableRefererCheck',
    'encryptionKey',
    'dbDriver',
    'dbHost',
    'dbUser',
    'dbPass',
    'dbDatabase',
    'dbPconnect',
    'dbCharset',
    'dbPort',
    'displayErrors',
    'ctoCom_APIKey',
    'ctoCom_disableRefererCheck',
    'ctoCom_responseLength',
    'ctoCom_handshake',
    'syncCto_debug_mode',
    'syncCto_attentionFlag'
);

/**
 * Whitelist
 */
$GLOBALS['SYC_CONFIG']['folder_whitelist'] = array(
    'contao',
    'plugins',
    'system',
    'templates',
    'typolight',
);

/**
 * Sync options
 */
$GLOBALS['SYC_CONFIG']['sync_options'] = array(
    'core' => array(
        'core_change',
        'core_delete',
    ),
    'user' => array(
        'user_change',
        'user_delete',
    ),
    'configfiles' => array(
        'localconfig_update',
        'localconfig_errors',
    )
);

/**
 * Maintance options
 */
$GLOBALS['SYC_CONFIG']['maintance_options'] = array(
    'temp_tables',
    'temp_folders',
    'css_create',
    'xml_create',
);

/**
 * Global configuration
 */
$GLOBALS['SYC_PATH']['db'] = $GLOBALS['TL_CONFIG']['uploadPath'] . '/syncCto_backups/database/';
$GLOBALS['SYC_PATH']['file'] = $GLOBALS['TL_CONFIG']['uploadPath'] . '/syncCto_backups/files/';
$GLOBALS['SYC_PATH']['debug'] = $GLOBALS['TL_CONFIG']['uploadPath'] . '/syncCto_backups/debug/';
$GLOBALS['SYC_PATH']['tmp'] = "system/tmp/";

/**
 * CtoCommunication RPC Calls
 */

// - Local Config --------------------------------------------------------------

// Import config
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_IMPORT_CONFIG"] = array(
    "class" => "SyncCtoHelper",
    "function" => "importConfig",
    "typ" => "POST",
    "parameter" => array("configlist"),
);

// Get config
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_GET_CONFIG"] = array(
    "class" => "SyncCtoRPCFunctions",
    "function" => "getLocalConfig",
    "typ" => "POST",
    "parameter" => array("ConfigBlacklist"),
);

// - Database ------------------------------------------------------------------

// Run Dump
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_RUN_DUMP"] = array(
    "class" => "SyncCtoDatabase",
    "function" => "runDump",
    "typ" => "POST",
    "parameter" => array("tables", "tempfolder"),
);

// Execute SQL
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_EXECUTE_SQL"] = array(
    "class" => "SyncCtoRPCFunctions",
    "function" => "executeSQL",
    "typ" => "POST",
    "parameter" => array("sql"),
);

// Load none recommended tables from client
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_NONERECOMMENDED_TABLES"] = array(
    "class" => "SyncCtoHelper",
    "function" => "databaseTablesNoneRecommended",
    "typ" => "POST",
    "parameter" => false,
);

// Load recommended tables from client
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_RECOMMENDED_TABLES"] = array(
    "class" => "SyncCtoHelper",
    "function" => "databaseTablesRecommended",
    "typ" => "POST",
    "parameter" => false,
);

// Load recommended tables from client
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_HIDDEN_TABLES"] = array(
    "class" => "SyncCtoHelper",
    "function" => "getTablesHidden",
    "typ" => "POST",
    "parameter" => false,
);

// Get client timestamp
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_TIMESTAMP"] = array(
    "class" => "SyncCtoHelper",
    "function" => "getDatabaseTablesTimestamp",
    "typ" => "POST",
    "parameter" => array("TableList"),
);

// Import a SQL Zip file into database
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_IMPORT_DATABASE"] = array(
    "class" => "SyncCtoDatabase",
    "function" => "runRestore",
    "typ" => "POST",
    "parameter" => array("filepath"),
);

// Drop tables
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_DROP_TABLES"] = array(
    "class" => "SyncCtoDatabase",
    "function" => "dropTable",
    "typ" => "POST",
    "parameter" => array("tablelist", "backup"),
);

// - Files ---------------------------------------------------------------------

// Check for deleted files
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_CHECK_DELETE_FILE"] = array(
    "class" => "SyncCtoRPCFunctions",
    "function" => "checkDeleteFiles",
    "typ" => "POST",
    "parameter" => array("md5", "file"),
);

// Delete a files on a list
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_DELETE_FILE"] = array(
    "class" => "SyncCtoFiles",
    "function" => "deleteFiles",
    "typ" => "POST",
    "parameter" => array("filelist"),
);

// Import files into contao file system
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_IMPORT_FILE"] = array(
    "class" => "SyncCtoFiles",
    "function" => "moveTempFile",
    "typ" => "POST",
    "parameter" => array("filelist"),
);

// Rebuild a split file
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_REBUILD_SPLITFILE"] = array(
    "class" => "SyncCtoFiles",
    "function" => "rebuildSplitFiles",
    "typ" => "POST",
    "parameter" => array("splitname", "splitcount", "movepath", "md5"),
);

// Split a file
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_SPLITFILE"] = array(
    "class" => "SyncCtoFiles",
    "function" => "splitFiles",
    "typ" => "POST",
    "parameter" => array("splitname", "destfolder", "destfile", "limit"),
);

// Send a file
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_SEND_FILE"] = array(
    "class" => "SyncCtoFiles",
    "function" => "saveFiles",
    "typ" => "POST",
    "parameter" => array("metafiles"),
);

// Get a file
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_GET_FILE"] = array(
    "class" => "SyncCtoFiles",
    "function" => "getFile",
    "typ" => "POST",
    "parameter" => array("path"),
);

// Compare 2 filelists
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_CHECKSUM_COMPARE"] = array(
    "class" => "SyncCtoRPCFunctions",
    "function" => "runCecksumCompare",
    "typ" => "POST",
    "parameter" => array("md5", "file"),
);

// Get filelist of contao core
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_CHECKSUM_CORE"] = array(
    "class" => "SyncCtoFiles",
    "function" => "runChecksumCore",
    "typ" => "GET",
    "parameter" => FALSE,
);

// Get filelist of file
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_CHECKSUM_FILES"] = array(
    "class" => "SyncCtoFiles",
    "function" => "runChecksumFiles",
    "typ" => "POST",
    "parameter" => FALSE,
);

// Get filelist of file
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_CHECKSUM_FOLDERS"] = array(
    "class" => "SyncCtoFiles",
    "function" => "runChecksumFolders",
    "typ" => "POST",
    "parameter" => array("files"),
);

// Run a file backup
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_FILEBACKUP"] = array(
    "class" => "SyncCtoFiles",
    "function" => "runDump",
    "typ" => "GET",
    "parameter" => FALSE,
);

// - Miscellaneous -------------------------------------------------------------

// Set displayErrors Flag
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_SET_DISPLAY_ERRORS_FLAG"] = array(
    "class" => "SyncCtoRPCFunctions",
    "function" => "setDisplayErrors",
    "typ" => "POST",
    "parameter" => array("state"),
);

// Set the attention flag
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_SET_ATTENTION_FLAG"] = array(
    "class" => "SyncCtoRPCFunctions",
    "function" => "setAttentionFlag",
    "typ" => "POST",
    "parameter" => array("state"),
);

// Clear temp folder
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_PURGETEMP"] = array(
    "class" => "SyncCtoFiles",
    "function" => "purgeTemp",
    "typ" => "GET",
    "parameter" => FALSE,
);

// Run maintenance
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_MAINTENANCE"] = array(
    "class" => "SyncCtoFiles",
    "function" => "runMaintenance",
    "typ" => "POST",
    "parameter" => array("options"),
);

// - Informations --------------------------------------------------------------

$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_GET_PHP_CONFIGURATION"] = array(
    "class" => "SyncCtoModuleCheck",
    "function" => "getPhpConfigurations",
    "typ" => "get",
    "parameter" => null,
);

$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_GET_PHP_FUNCTIONS"] = array(
    "class" => "SyncCtoModuleCheck",
    "function" => "getPhpFunctions",
    "typ" => "get",
    "parameter" => null,
);

// Get folder path list
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_GET_PATHLIST"] = array(
    "class" => "SyncCtoRPCFunctions",
    "function" => "getPathList",
    "typ" => "POST",
    "parameter" => array("name"),
);

// Get SyncCto Version 
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_VERSION"] = array(
    "class" => "SyncCtoRPCFunctions",
    "function" => "getVersionSyncCto",
    "typ" => "GET",
    "parameter" => FALSE,
);

// Get a list of parameter
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_PARAMETER"] = array(
    "class" => "SyncCtoRPCFunctions",
    "function" => "getClientParameter",
    "typ" => "GET",
    "parameter" => FALSE,
);

?>