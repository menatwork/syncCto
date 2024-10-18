<?php

use Contao\ArrayUtil;
use Contao\Input;
use Contao\StringUtil;

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

/**
 * Current syncCto version
 */
$GLOBALS['SYC_VERSION'] = '3.2.0';

/**
 * Back end modules
 */
$i = array_search(
    'system',
    array_keys($GLOBALS['BE_MOD'])
);

ArrayUtil::arrayInsert(
    $GLOBALS['BE_MOD'],
    $i + 1,
    [
        'syncCto' => [
            'syncCto_settings' => [
                'tables' => ['tl_syncCto_settings'],
                'icon'   => 'bundles/synccto/images/nav/iconSettings.png'
            ],
            'synccto_clients'  => [
                'tables'     => [
                    'tl_synccto_clients',
                    'tl_syncCto_clients_syncTo',
                    'tl_syncCto_clients_syncFrom',
                    'tl_syncCto_clients_showExtern'
                ],
                'icon'       => 'bundles/synccto/images/nav/iconClients.png',
                'callback'   => 'SyncCtoModuleClient',
                'stylesheet' => 'bundles/synccto/css/systemcheck.css',
            ],
            //        'syncCto_backups' => array
            //        (
            //            'tables' => array
            //            (
            //                'tl_syncCto_backup_file',
            //                'tl_syncCto_backup_db',
            //                'tl_syncCto_restore_file',
            //                'tl_syncCto_restore_db'
            //            ),
            //            'icon'              => 'bundles/synccto/images/nav/iconBackups.png',
            //            'callback'          => 'SyncCtoModuleBackup',
            //        ),
            'syncCto_check'    => [
                'icon'       => 'bundles/synccto/images/nav/iconCheck.png',
                'callback'   => 'SyncCtoModuleCheck',
                'stylesheet' => 'bundles/synccto/css/systemcheck.css',
                'style'      => 'hidden'
            ]
        ]
    ]
);

/**
 * Init the default config array if not set.
 */
if (!isset($GLOBALS['SYC_CONFIG'])) {
    $GLOBALS['SYC_CONFIG'] = [];
}

if (!isset($GLOBALS['SYC_CONFIG']['mime_types'])) {
    $GLOBALS['SYC_CONFIG']['mime_types'] = [];
}

if (!isset($GLOBALS['SYC_CONFIG']['folder_blacklist'])) {
    $GLOBALS['SYC_CONFIG']['folder_blacklist'] = [];
}

if (!isset($GLOBALS['SYC_CONFIG']['file_blacklist'])) {
    $GLOBALS['SYC_CONFIG']['file_blacklist'] = [];
}

if (!isset($GLOBALS['SYC_CONFIG']['local_blacklist'])) {
    $GLOBALS['SYC_CONFIG']['local_blacklist'] = [];
}

if (!isset($GLOBALS['SYC_CONFIG']['folder_whitelist'])) {
    $GLOBALS['SYC_CONFIG']['folder_whitelist'] = [];
}

if (!isset($GLOBALS['SYC_CONFIG']['maintance_options'])) {
    $GLOBALS['SYC_CONFIG']['maintance_options'] = [];
}

if (!isset($GLOBALS['SYC_CONFIG']['database_mapping'])) {
    $GLOBALS['SYC_CONFIG']['database_mapping'] = [];
}

if (!isset($GLOBALS['SYC_CONFIG']['syncCto_folder_blacklist'])) {
    $GLOBALS['SYC_CONFIG']['syncCto_folder_blacklist'] = '';
}

/**
 * Mime types
 */
$GLOBALS['SYC_CONFIG']['mime_types'] = array_merge(
    (array) $GLOBALS['SYC_CONFIG']['mime_types'],
    [
        // Application files
        'xl'    => ['application/excel', 'iconOFFICE.gif'],
        'xls'   => ['application/excel', 'iconOFFICE.gif'],
        'hqx'   => ['application/mac-binhex40', 'iconPLAIN.gif'],
        'cpt'   => ['application/mac-compactpro', 'iconPLAIN.gif'],
        'bin'   => ['application/macbinary', 'iconPLAIN.gif'],
        'doc'   => ['application/msword', 'iconOFFICE.gif'],
        'word'  => ['application/msword', 'iconOFFICE.gif'],
        'cto'   => ['application/octet-stream', 'iconCTO.gif'],
        'dms'   => ['application/octet-stream', 'iconPLAIN.gif'],
        'lha'   => ['application/octet-stream', 'iconPLAIN.gif'],
        'lzh'   => ['application/octet-stream', 'iconPLAIN.gif'],
        'exe'   => ['application/octet-stream', 'iconPLAIN.gif'],
        'class' => ['application/octet-stream', 'iconPLAIN.gif'],
        'so'    => ['application/octet-stream', 'iconPLAIN.gif'],
        'sea'   => ['application/octet-stream', 'iconPLAIN.gif'],
        'dll'   => ['application/octet-stream', 'iconPLAIN.gif'],
        'oda'   => ['application/oda', 'iconPLAIN.gif'],
        'pdf'   => ['application/pdf', 'iconPDF.gif'],
        'ai'    => ['application/postscript', 'iconPLAIN.gif'],
        'eps'   => ['application/postscript', 'iconPLAIN.gif'],
        'ps'    => ['application/postscript', 'iconPLAIN.gif'],
        'pps'   => ['application/powerpoint', 'iconOFFICE.gif'],
        'ppt'   => ['application/powerpoint', 'iconOFFICE.gif'],
        'smi'   => ['application/smil', 'iconPLAIN.gif'],
        'smil'  => ['application/smil', 'iconPLAIN.gif'],
        'mif'   => ['application/vnd.mif', 'iconPLAIN.gif'],
        'odc'   => ['application/vnd.oasis.opendocument.chart', 'iconOFFICE.gif'],
        'odf'   => ['application/vnd.oasis.opendocument.formula', 'iconOFFICE.gif'],
        'odg'   => ['application/vnd.oasis.opendocument.graphics', 'iconOFFICE.gif'],
        'odi'   => ['application/vnd.oasis.opendocument.image', 'iconOFFICE.gif'],
        'odp'   => ['application/vnd.oasis.opendocument.presentation', 'iconOFFICE.gif'],
        'ods'   => ['application/vnd.oasis.opendocument.spreadsheet', 'iconOFFICE.gif'],
        'odt'   => ['application/vnd.oasis.opendocument.text', 'iconOFFICE.gif'],
        'wbxml' => ['application/wbxml', 'iconPLAIN.gif'],
        'wmlc'  => ['application/wmlc', 'iconPLAIN.gif'],
        'dmg'   => ['application/x-apple-diskimage', 'iconRAR.gif'],
        'dcr'   => ['application/x-director', 'iconPLAIN.gif'],
        'dir'   => ['application/x-director', 'iconPLAIN.gif'],
        'dxr'   => ['application/x-director', 'iconPLAIN.gif'],
        'dvi'   => ['application/x-dvi', 'iconPLAIN.gif'],
        'gtar'  => ['application/x-gtar', 'iconRAR.gif'],
        'inc'   => ['application/x-httpd-php', 'iconPHP.gif'],
        'php'   => ['application/x-httpd-php', 'iconPHP.gif'],
        'php3'  => ['application/x-httpd-php', 'iconPHP.gif'],
        'php4'  => ['application/x-httpd-php', 'iconPHP.gif'],
        'php5'  => ['application/x-httpd-php', 'iconPHP.gif'],
        'phtml' => ['application/x-httpd-php', 'iconPHP.gif'],
        'phps'  => ['application/x-httpd-php-source', 'iconPHP.gif'],
        'js'    => ['application/x-javascript', 'iconJS.gif'],
        'psd'   => ['application/x-photoshop', 'iconPLAIN.gif'],
        'rar'   => ['application/x-rar', 'iconRAR.gif'],
        'fla'   => ['application/x-shockwave-flash', 'iconSWF.gif'],
        'swf'   => ['application/x-shockwave-flash', 'iconSWF.gif'],
        'sit'   => ['application/x-stuffit', 'iconRAR.gif'],
        'tar'   => ['application/x-tar', 'iconRAR.gif'],
        'tgz'   => ['application/x-tar', 'iconRAR.gif'],
        'xhtml' => ['application/xhtml+xml', 'iconPLAIN.gif'],
        'xht'   => ['application/xhtml+xml', 'iconPLAIN.gif'],
        'zip'   => ['application/zip', 'iconRAR.gif'],

        // Audio files
        'm4a'   => ['audio/x-m4a', 'iconAUDIO.gif'],
        'mp3'   => ['audio/mp3', 'iconAUDIO.gif'],
        'wma'   => ['audio/wma', 'iconAUDIO.gif'],
        'mpeg'  => ['audio/mpeg', 'iconAUDIO.gif'],
        'wav'   => ['audio/wav', 'iconAUDIO.gif'],
        'ogg'   => ['audio/ogg', 'iconAUDIO.gif'],
        'mid'   => ['audio/midi', 'iconAUDIO.gif'],
        'midi'  => ['audio/midi', 'iconAUDIO.gif'],
        'aif'   => ['audio/x-aiff', 'iconAUDIO.gif'],
        'aiff'  => ['audio/x-aiff', 'iconAUDIO.gif'],
        'aifc'  => ['audio/x-aiff', 'iconAUDIO.gif'],
        'ram'   => ['audio/x-pn-realaudio', 'iconAUDIO.gif'],
        'rm'    => ['audio/x-pn-realaudio', 'iconAUDIO.gif'],
        'rpm'   => ['audio/x-pn-realaudio-plugin', 'iconAUDIO.gif'],
        'ra'    => ['audio/x-realaudio', 'iconAUDIO.gif'],

        // Images
        'bmp'   => ['image/bmp', 'iconBMP.gif'],
        'gif'   => ['image/gif', 'iconGIF.gif'],
        'jpeg'  => ['image/jpeg', 'iconJPG.gif'],
        'jpg'   => ['image/jpeg', 'iconJPG.gif'],
        'jpe'   => ['image/jpeg', 'iconJPG.gif'],
        'png'   => ['image/png', 'iconTIF.gif'],
        'tiff'  => ['image/tiff', 'iconTIF.gif'],
        'tif'   => ['image/tiff', 'iconTIF.gif'],

        // Mailbox files
        'eml'   => ['message/rfc822', 'iconPLAIN.gif'],

        // Text files
        'asp'   => ['text/asp', 'iconPLAIN.gif'],
        'css'   => ['text/css', 'iconCSS.gif'],
        'html'  => ['text/html', 'iconHTML.gif'],
        'htm'   => ['text/html', 'iconHTML.gif'],
        'shtml' => ['text/html', 'iconHTML.gif'],
        'txt'   => ['text/plain', 'iconPLAIN.gif'],
        'text'  => ['text/plain', 'iconPLAIN.gif'],
        'log'   => ['text/plain', 'iconPLAIN.gif'],
        'rtx'   => ['text/richtext', 'iconPLAIN.gif'],
        'rtf'   => ['text/rtf', 'iconPLAIN.gif'],
        'xml'   => ['text/xml', 'iconPLAIN.gif'],
        'xsl'   => ['text/xml', 'iconPLAIN.gif'],

        // Videos
        'mp4'   => ['video/mp4', 'iconVIDEO.gif'],
        'm4v'   => ['video/x-m4v', 'iconVIDEO.gif'],
        'mov'   => ['video/mov', 'iconVIDEO.gif'],
        'wmv'   => ['video/wmv', 'iconVIDEO.gif'],
        'webm'  => ['video/webm', 'iconVIDEO.gif'],
        'qt'    => ['video/quicktime', 'iconVIDEO.gif'],
        'rv'    => ['video/vnd.rn-realvideo', 'iconVIDEO.gif'],
        'avi'   => ['video/x-msvideo', 'iconVIDEO.gif'],
        'ogv'   => ['video/ogg', 'iconVIDEO.gif'],
        'movie' => ['video/x-sgi-movie', 'iconVIDEO.gif']
    ]
);

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['executePreActions'][] = ['MenAtWork\SyncCto\Helper\Ping', 'pingClientStatus'];
$GLOBALS['TL_HOOKS']['parseBackendTemplate'][] = ['SyncCtoHelper', 'checkLockStatus'];
$GLOBALS['TL_HOOKS']['addCustomRegexp'][] = ['SyncCtoHelper', 'customRegexp'];
$GLOBALS['TL_HOOKS']['parseBackendTemplate'][] = ['SyncCtoHelper', 'addLegend'];
$GLOBALS['TL_HOOKS']['syncExecuteFinalOperations'][] = ['SyncCtoDatabaseUpdater', 'runAutoUpdate'];

/**
 * Permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'syncCto_clients';
$GLOBALS['TL_PERMISSIONS'][] = 'syncCto_clients_p';
$GLOBALS['TL_PERMISSIONS'][] = 'syncCto_sync_options';
$GLOBALS['TL_PERMISSIONS'][] = 'syncCto_tables';
$GLOBALS['TL_PERMISSIONS'][] = 'syncCto_force_dbafs_overwrite';
$GLOBALS['TL_PERMISSIONS'][] = 'syncCto_hide_auto_sync';

/**
 * Callbacks are only used for overview screen
 */
$strDo = Input::get("do");
$strTable = Input::get("table");
$strAct = Input::get("act");

if (
    $strDo == 'syncCto_backups'
    && $strTable != ''
    && ($strAct == 'edit' || $strAct == 'create')
//    && TL_MODE == 'BE'
) {
    unset($GLOBALS['BE_MOD']['syncCto']['syncCto_backups']['callback']);
}

if (
    $strDo == 'synccto_clients'
    && $strAct != 'start'
    && in_array(
        $strTable,
        [
            'tl_syncCto_clients_syncTo',
            'tl_syncCto_clients_syncFrom',
            'tl_syncCto_clients_showExtern',
            ''
        ]
    )
//    && TL_MODE == 'BE'
) {
    unset($GLOBALS['BE_MOD']['syncCto']['synccto_clients']['callback']);
}

/**
 * Include attention CSS
 */
if (isset($GLOBALS['TL_CONFIG']['syncCto_attentionFlag'])
    && $GLOBALS['TL_CONFIG']['syncCto_attentionFlag'] == true
) {
    $GLOBALS['TL_CSS'][] = 'bundles/synccto/css/attention.css';
}

// Size limit for files in bytes, will be checked
$GLOBALS['SYC_SIZE']['limit'] = 524288000;

// Size limit for files in bytes, completely ignored
$GLOBALS['SYC_SIZE']['limit_ignore'] = 838860800;

/**
 * Blacklists
 */

// Add some files to the blacklist for the DBAFS from contao.
if (isset($GLOBALS['TL_CONFIG']['fileSyncExclude'])) {
    $arrFileSyncExclude = StringUtil::trimsplit(',', $GLOBALS['TL_CONFIG']['fileSyncExclude']);
} else {
    $arrFileSyncExclude = [];
}
$arrFileSyncExclude[] = 'syncCto_backups/debug';
$GLOBALS['TL_CONFIG']['fileSyncExclude'] = implode(',', $arrFileSyncExclude);

// Tables
$GLOBALS['SYC_CONFIG']['table_hidden'] = array_merge(
    (array) $GLOBALS['SYC_CONFIG']['table_hidden'],
    [
        'tl_files',
        'tl_log',
        'tl_lock',
        'tl_cron',
        'tl_opt_in',
        'tl_opt_in_related',
        'tl_remember_me',
        'tl_session',
        'tl_search',
        'tl_search_index',
        'tl_undo',
        'tl_version',
        'tl_comments',
        'tl_comments_notify',
        'tl_synccto_clients',
        'tl_synccto_stats',
        'tl_trusted_device'
    ]
);

// Folders
$GLOBALS['SYC_CONFIG']['folder_blacklist'] = array_merge(
    (array) $GLOBALS['SYC_CONFIG']['folder_blacklist'],
    [
        '*/syncCto_backups/',
        '*/.git'
    ]
);

// Files only sync.
$GLOBALS['SYC_CONFIG']['file_blacklist'] = array_merge(
    (array) $GLOBALS['SYC_CONFIG']['file_blacklist'],
    [
        'TL_ROOT/.env',
        'TL_ROOT/.htaccess',
        'TL_ROOT/.htpasswd',
        'TL_ROOT/composer*',
        '.DS_Store',
        '.public',
        '.nosync'
    ]
);

// Local config
$GLOBALS['SYC_CONFIG']['local_blacklist'] = array_merge(
    (array) $GLOBALS['SYC_CONFIG']['local_blacklist'],
    [
        'ctoCom_APIKey',
        'ctoCom_disableRefererCheck',
        'ctoCom_responseLength',
        'ctoCom_handshake',
        'disableCron',
        'disableRefererCheck',
        'syncCto_debug_mode',
        'syncCto_attentionFlag',
        'syncCto_auto_db_updater'
    ]
);

/**
 * Whitelist
 */
$GLOBALS['SYC_CONFIG']['folder_whitelist'] = array_merge(
    (array) $GLOBALS['SYC_CONFIG']['folder_whitelist'],
    [
        'templates'
    ]
);

/**
 * Sync options
 */
// Core ! The core isn't possible since we have a compser system. We have to rewrite some functions here first.
$GLOBALS['SYC_CONFIG']['sync_options']['core'][] = 'core_change';
$GLOBALS['SYC_CONFIG']['sync_options']['core'][] = 'core_delete';
// User
$GLOBALS['SYC_CONFIG']['sync_options']['user'][] = 'user_change';
$GLOBALS['SYC_CONFIG']['sync_options']['user'][] = 'user_delete';
// User
$GLOBALS['SYC_CONFIG']['sync_options']['configfiles'][] = 'localconfig_update';

/**
 * Maintance options
 */
$GLOBALS['SYC_CONFIG']['maintance_options'] = array_merge(
    (array) $GLOBALS['SYC_CONFIG']['maintance_options'],
    [
        'temp_tables',
        'temp_folders',
        'xml_create',
    ]
);

/**
 * Global configuration
 */
$GLOBALS['SYC_PATH']['db'] = 'syncCto_backups/database/';
$GLOBALS['SYC_PATH']['file'] = 'syncCto_backups/files/';
$GLOBALS['SYC_PATH']['debug'] = 'syncCto_backups/debug/';
$GLOBALS['SYC_PATH']['tmp'] = "system/tmp/";

/**
 * Language mapping for database lookup
 */
$GLOBALS['SYC_CONFIG']['database_mapping'] = array_merge(
    (array) $GLOBALS['SYC_CONFIG']['database_mapping'],
    [
        'tl_module'              => 'modules',
        'tl_member_group'        => 'mgroup',
        'tl_user_group'          => 'group',
        'tl_repository_installs' => 'repository_manager',
        'tl_task'                => 'tasks',
        'tl_theme'               => 'themes',
        'tl_style_sheet'         => 'css'
    ]
);

/**
 * Folder/Files replacement
 */

// Default
$GLOBALS['SYC_CONFIG']['folder_file_replacement'][' '] = '_';

// Windows / Unix
$GLOBALS['SYC_CONFIG']['folder_file_replacement']['/'] = '-';

// Windows only
$GLOBALS['SYC_CONFIG']['folder_file_replacement']['\\'] = '-';
$GLOBALS['SYC_CONFIG']['folder_file_replacement'][':'] = '';
$GLOBALS['SYC_CONFIG']['folder_file_replacement']['*'] = '';
$GLOBALS['SYC_CONFIG']['folder_file_replacement']['?'] = '';
$GLOBALS['SYC_CONFIG']['folder_file_replacement']['"'] = '';
$GLOBALS['SYC_CONFIG']['folder_file_replacement']['<'] = '';
$GLOBALS['SYC_CONFIG']['folder_file_replacement']['>'] = '';
$GLOBALS['SYC_CONFIG']['folder_file_replacement']['|'] = '_';

/**
 * CtoCommunication RPC Calls
 */

// - Local Config --------------------------------------------------------------

// Import config
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_IMPORT_CONFIG"] =
    [
        "class"     => "SyncCtoHelper",
        "function"  => "importConfig",
        "typ"       => "POST",
        "parameter" => ["configlist"],
    ];

// Get config
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_GET_CONFIG"] =
    [
        "class"     => "SyncCtoRPCFunctions",
        "function"  => "getLocalConfig",
        "typ"       => "POST",
        "parameter" => ["ConfigBlacklist"],
    ];

// Get config
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_CREATE_PATHCONFIG"] =
    [
        "class"     => "SyncCtoHelper",
        "function"  => "createPathconfig",
        "typ"       => "GET",
        "parameter" => false,
    ];

// - Database ------------------------------------------------------------------

// Run table hashes
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_DATABASE_HASH"] =
    [
        "class"     => "\MenAtWork\SyncCto\Database\Diff",
        "function"  => "getHashForTables",
        "typ"       => "POST",
        "parameter" => ["tables"],
    ];

// Run Dump
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_RUN_DUMP"] =
    [
        "class"     => "SyncCtoDatabase",
        "function"  => "runDump",
        "typ"       => "POST",
        "parameter" => ["tables", "tempfolder"],
    ];

// Execute SQL
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_EXECUTE_SQL"] =
    [
        "class"     => "SyncCtoRPCFunctions",
        "function"  => "executeSQL",
        "typ"       => "POST",
        "parameter" => ["sql"],
    ];

// Load none recommended tables from client
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_NONERECOMMENDED_TABLES"] =
    [
        "class"     => "SyncCtoHelper",
        "function"  => "databaseTablesNoneRecommended",
        "typ"       => "POST",
        "parameter" => false,
    ];

// Load recommended tables from client
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_RECOMMENDED_TABLES"] =
    [
        "class"     => "SyncCtoHelper",
        "function"  => "databaseTablesRecommended",
        "typ"       => "POST",
        "parameter" => false,
    ];

// Load recommended tables from client
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_HIDDEN_TABLES"] =
    [
        "class"     => "SyncCtoHelper",
        "function"  => "getTablesHidden",
        "typ"       => "GET",
        "parameter" => false,
    ];

// Load recommended tables from client
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_HIDDEN_TABLES_PLACEHOLDER"] =
    [
        "class"     => "SyncCtoHelper",
        "function"  => "getPreparedHiddenTablesPlaceholder",
        "typ"       => "GET",
        "parameter" => false,
    ];

// Get client timestamp
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_TIMESTAMP"] =
    [
        "class"     => "SyncCtoHelper",
        "function"  => "getDatabaseTablesTimestamp",
        "typ"       => "POST",
        "parameter" => ["TableList"],
    ];

// Import a SQL Zip file into database
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_IMPORT_DATABASE"] =
    [
        "class"     => "SyncCtoDatabase",
        "function"  => "runRestore",
        "typ"       => "POST",
        "parameter" => ["filepath", "additionalSQL"],
    ];

// Drop tables
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_DROP_TABLES"] =
    [
        "class"     => "SyncCtoDatabase",
        "function"  => "dropTable",
        "typ"       => "POST",
        "parameter" => ["tablelist", "backup"],
    ];

// - Files ---------------------------------------------------------------------

// Check for deleted files
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_CHECK_DELETE_FILE"] =
    [
        "class"     => "SyncCtoRPCFunctions",
        "function"  => "checkDeleteFiles",
        "typ"       => "POST",
        "parameter" => ["md5", "file"],
    ];

// Delete a files on a list
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_DELETE_FILE"] =
    [
        "class"     => "SyncCtoFiles",
        "function"  => "deleteFiles",
        "typ"       => "POST",
        "parameter" => ["filelist", "dbafs"],
    ];

// Import files into contao file system
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_IMPORT_FILE"] =
    [
        "class"     => "SyncCtoFiles",
        "function"  => "moveTempFile",
        "typ"       => "POST",
        "parameter" => ["filelist", "dbafs"],
    ];

// Import files into contao file system
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_UPDATE_DBAFS"] =
    [
        "class"     => "SyncCtoFiles",
        "function"  => "updateDbafs",
        "typ"       => "POST",
        "parameter" => ["filelist"],
    ];

// Rebuild a split file
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_REBUILD_SPLITFILE"] =
    [
        "class"     => "SyncCtoFiles",
        "function"  => "rebuildSplitFiles",
        "typ"       => "POST",
        "parameter" => ["splitname", "splitcount", "movepath", "md5"],
    ];

// Split a file
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_SPLITFILE"] =
    [
        "class"     => "SyncCtoFiles",
        "function"  => "splitFiles",
        "typ"       => "POST",
        "parameter" => ["splitname", "destfolder", "destfile", "limit"],
    ];

// Send a file
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_SEND_FILE"] =
    [
        "class"     => "SyncCtoFiles",
        "function"  => "saveFiles",
        "typ"       => "POST",
        "parameter" => ["metafiles"],
    ];

// Get a file
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_GET_FILE"] =
    [
        "class"     => "SyncCtoFiles",
        "function"  => "getFile",
        "typ"       => "POST",
        "parameter" => ["path"],
    ];

// Compare 2 filelists
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_CHECKSUM_COMPARE"] =
    [
        "class"     => "SyncCtoRPCFunctions",
        "function"  => "runCecksumCompare",
        "typ"       => "POST",
        "parameter" => ["md5", "file", "disable_dbafs_conflicts"],
    ];

// Get filelist of contao core
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_CHECKSUM_CORE"] =
    [
        "class"     => "SyncCtoFiles",
        "function"  => "runChecksumCore",
        "typ"       => "GET",
        "parameter" => false,
    ];

// Get filelist of file
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_CHECKSUM_FILES"] =
    [
        "class"     => "SyncCtoFiles",
        "function"  => "runChecksumFiles",
        "typ"       => "POST",
        "parameter" => false,
    ];

// Get folderlist
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_CHECKSUM_FOLDERS_CORE"] =
    [
        "class"     => "SyncCtoFiles",
        "function"  => "runChecksumFolderCore",
        "typ"       => "POST",
        "parameter" => false,
    ];

// Get folderlist
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_CHECKSUM_FOLDERS_FILES"] =
    [
        "class"     => "SyncCtoFiles",
        "function"  => "runChecksumFolderFiles",
        "typ"       => "POST",
        "parameter" => false,
    ];

// Search folders which could delete
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_SEARCH_DELETE_FOLDERS"] =
    [
        "class"     => "SyncCtoRPCFunctions",
        "function"  => "searchDeleteFolders",
        "typ"       => "POST",
        "parameter" => ["md5", "file"],
    ];

// Run a file backup
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_FILEBACKUP"] =
    [
        "class"     => "SyncCtoFiles",
        "function"  => "runDump",
        "typ"       => "GET",
        "parameter" => false,
    ];

// Get information from the DBAFS for some files.
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_DBAFS_INFORMATION"] =
    [
        "class"     => "SyncCtoFiles",
        "function"  => "getDbafsInformationFor",
        "typ"       => "POST",
        "parameter" => ["files"],
    ];

// - Miscellaneous -------------------------------------------------------------

// Set displayErrors Flag
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_SET_DISPLAY_ERRORS_FLAG"] =
    [
        "class"     => "SyncCtoRPCFunctions",
        "function"  => "setDisplayErrors",
        "typ"       => "POST",
        "parameter" => ["state"],
    ];

// Set the attention flag
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_SET_ATTENTION_FLAG"] =
    [
        "class"     => "SyncCtoRPCFunctions",
        "function"  => "setAttentionFlag",
        "typ"       => "POST",
        "parameter" => ["state"],
    ];

// Clear temp folder
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_PURGETEMP"] =
    [
        "class"     => "SyncCtoFiles",
        "function"  => "purgeTemp",
        "typ"       => "GET",
        "parameter" => false,
    ];

// Contao Cache.
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_PURGE_CACHE"] =
    [
        "class"     => "SyncCtoContaoAutomator",
        "function"  => "purgeInternalCache",
        "typ"       => "GET",
        "parameter" => false,
    ];

// Contao Cache.
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_CREATE_CACHE"] =
    [
        "class"     => "SyncCtoContaoAutomator",
        "function"  => "createInternalCache",
        "typ"       => "GET",
        "parameter" => false,
    ];

// Run maintenance
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_MAINTENANCE"] =
    [
        "class"     => "SyncCtoFiles",
        "function"  => "runMaintenance",
        "typ"       => "POST",
        "parameter" => ["options"],
    ];

// Execute last step operations
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_EXECUTE_FINAL_OPERATIONS"] =
    [
        "class"     => "SyncCtoHelper",
        "function"  => "executeFinalOperations",
        "typ"       => "GET",
        "parameter" => false,
    ];

// - Informations --------------------------------------------------------------

$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_GET_PHP_CONFIGURATION"] =
    [
        "class"     => "SyncCtoModuleCheck",
        "function"  => "getPhpConfigurations",
        "typ"       => "get",
        "parameter" => null,
    ];

$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_GET_PHP_FUNCTIONS"] =
    [
        "class"     => "SyncCtoModuleCheck",
        "function"  => "getPhpFunctions",
        "typ"       => "get",
        "parameter" => null,
    ];

$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_GET_EXTENDED_INFORMATIONS"] =
    [
        "class"     => "SyncCtoModuleCheck",
        "function"  => "getExtendedInformation",
        "typ"       => "POST",
        "parameter" => ["DateFormate"],
    ];

$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_GET_PRO_FUNCTIONS"] =
    [
        "class"     => "SyncCtoModuleCheck",
        "function"  => "getMySqlFunctions",
        "typ"       => "get",
        "parameter" => null,
    ];

// Get folder path list
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_GET_PATHLIST"] =
    [
        "class"     => "SyncCtoRPCFunctions",
        "function"  => "getPathList",
        "typ"       => "POST",
        "parameter" => ["name"],
    ];

// Get SyncCto Version
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_VERSION"] =
    [
        "class"     => "SyncCtoRPCFunctions",
        "function"  => "getVersionSyncCto",
        "typ"       => "GET",
        "parameter" => false,
    ];

// Get a list of parameter
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_PARAMETER"] =
    [
        "class"     => "SyncCtoRPCFunctions",
        "function"  => "getClientParameter",
        "typ"       => "GET",
        "parameter" => false,
    ];
