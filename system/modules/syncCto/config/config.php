<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL 
 * @filesource
 */

$objInput = \Input::getInstance();

/**
 * Current syncCto version
 */
$GLOBALS['SYC_VERSION'] = '2.6.0';

/**
 * Back end modules
 */
$i = array_search('system', array_keys($GLOBALS['BE_MOD']));
array_insert($GLOBALS['BE_MOD'], $i + 1, array
(
    'syncCto' => array
    (
        'syncCto_settings' => array
        (
            'tables'            => array('tl_syncCto_settings'),
            'icon'              => 'system/modules/syncCto/assets/images/nav/iconSettings.png'
        ),
        'synccto_clients' => array
        (
            'tables' => array
            (
                'tl_synccto_clients',
                'tl_syncCto_clients_syncTo',
                'tl_syncCto_clients_syncFrom',
                'tl_syncCto_clients_showExtern'
            ),
            'icon'              => 'system/modules/syncCto/assets/images/nav/iconClients.png',
            'callback'          => 'SyncCtoModuleClient',
            'stylesheet'        => 'system/modules/syncCto/assets/css/systemcheck.css',
        ),
        'syncCto_backups' => array
        (
            'tables' => array
            (
                'tl_syncCto_backup_file',
                'tl_syncCto_backup_db',
                'tl_syncCto_restore_file',
                'tl_syncCto_restore_db'
            ),
            'icon'              => 'system/modules/syncCto/assets/images/nav/iconBackups.png',
            'callback'          => 'SyncCtoModuleBackup',
        ),
        'syncCto_check' => array
        (
            'icon'              => 'system/modules/syncCto/assets/images/nav/iconCheck.png',
            'callback'          => 'SyncCtoModuleCheck',
            'stylesheet'        => 'system/modules/syncCto/assets/css/systemcheck.css',
        )
    )
));

/**
 * Mime types
 */
$GLOBALS['SYC_CONFIG']['mime_types'] = array_merge((array) $GLOBALS['SYC_CONFIG']['mime_types'],
    array
    (
        // Application files
        'xl'    => array('application/excel', 'iconOFFICE.gif'),
        'xls'   => array('application/excel', 'iconOFFICE.gif'),
        'hqx'   => array('application/mac-binhex40', 'iconPLAIN.gif'),
        'cpt'   => array('application/mac-compactpro', 'iconPLAIN.gif'),
        'bin'   => array('application/macbinary', 'iconPLAIN.gif'),
        'doc'   => array('application/msword', 'iconOFFICE.gif'),
        'word'  => array('application/msword', 'iconOFFICE.gif'),
        'cto'   => array('application/octet-stream', 'iconCTO.gif'),
        'dms'   => array('application/octet-stream', 'iconPLAIN.gif'),
        'lha'   => array('application/octet-stream', 'iconPLAIN.gif'),
        'lzh'   => array('application/octet-stream', 'iconPLAIN.gif'),
        'exe'   => array('application/octet-stream', 'iconPLAIN.gif'),
        'class' => array('application/octet-stream', 'iconPLAIN.gif'),
        'so'    => array('application/octet-stream', 'iconPLAIN.gif'),
        'sea'   => array('application/octet-stream', 'iconPLAIN.gif'),
        'dll'   => array('application/octet-stream', 'iconPLAIN.gif'),
        'oda'   => array('application/oda', 'iconPLAIN.gif'),
        'pdf'   => array('application/pdf', 'iconPDF.gif'),
        'ai'    => array('application/postscript', 'iconPLAIN.gif'),
        'eps'   => array('application/postscript', 'iconPLAIN.gif'),
        'ps'    => array('application/postscript', 'iconPLAIN.gif'),
        'pps'   => array('application/powerpoint', 'iconOFFICE.gif'),
        'ppt'   => array('application/powerpoint', 'iconOFFICE.gif'),
        'smi'   => array('application/smil', 'iconPLAIN.gif'),
        'smil'  => array('application/smil', 'iconPLAIN.gif'),
        'mif'   => array('application/vnd.mif', 'iconPLAIN.gif'),
        'odc'   => array('application/vnd.oasis.opendocument.chart', 'iconOFFICE.gif'),
        'odf'   => array('application/vnd.oasis.opendocument.formula', 'iconOFFICE.gif'),
        'odg'   => array('application/vnd.oasis.opendocument.graphics', 'iconOFFICE.gif'),
        'odi'   => array('application/vnd.oasis.opendocument.image', 'iconOFFICE.gif'),
        'odp'   => array('application/vnd.oasis.opendocument.presentation', 'iconOFFICE.gif'),
        'ods'   => array('application/vnd.oasis.opendocument.spreadsheet', 'iconOFFICE.gif'),
        'odt'   => array('application/vnd.oasis.opendocument.text', 'iconOFFICE.gif'),
        'wbxml' => array('application/wbxml', 'iconPLAIN.gif'),
        'wmlc'  => array('application/wmlc', 'iconPLAIN.gif'),
        'dmg'   => array('application/x-apple-diskimage', 'iconRAR.gif'),
        'dcr'   => array('application/x-director', 'iconPLAIN.gif'),
        'dir'   => array('application/x-director', 'iconPLAIN.gif'),
        'dxr'   => array('application/x-director', 'iconPLAIN.gif'),
        'dvi'   => array('application/x-dvi', 'iconPLAIN.gif'),
        'gtar'  => array('application/x-gtar', 'iconRAR.gif'),
        'inc'   => array('application/x-httpd-php', 'iconPHP.gif'),
        'php'   => array('application/x-httpd-php', 'iconPHP.gif'),
        'php3'  => array('application/x-httpd-php', 'iconPHP.gif'),
        'php4'  => array('application/x-httpd-php', 'iconPHP.gif'),
        'php5'  => array('application/x-httpd-php', 'iconPHP.gif'),
        'phtml' => array('application/x-httpd-php', 'iconPHP.gif'),
        'phps'  => array('application/x-httpd-php-source', 'iconPHP.gif'),
        'js'    => array('application/x-javascript', 'iconJS.gif'),
        'psd'   => array('application/x-photoshop', 'iconPLAIN.gif'),
        'rar'   => array('application/x-rar', 'iconRAR.gif'),
        'fla'   => array('application/x-shockwave-flash', 'iconSWF.gif'),
        'swf'   => array('application/x-shockwave-flash', 'iconSWF.gif'),
        'sit'   => array('application/x-stuffit', 'iconRAR.gif'),
        'tar'   => array('application/x-tar', 'iconRAR.gif'),
        'tgz'   => array('application/x-tar', 'iconRAR.gif'),
        'xhtml' => array('application/xhtml+xml', 'iconPLAIN.gif'),
        'xht'   => array('application/xhtml+xml', 'iconPLAIN.gif'),
        'zip'   => array('application/zip', 'iconRAR.gif'),

        // Audio files
        'm4a'   => array('audio/x-m4a', 'iconAUDIO.gif'),
        'mp3'   => array('audio/mp3', 'iconAUDIO.gif'),
        'wma'   => array('audio/wma', 'iconAUDIO.gif'),
        'mpeg'  => array('audio/mpeg', 'iconAUDIO.gif'),
        'wav'   => array('audio/wav', 'iconAUDIO.gif'),
        'ogg'   => array('audio/ogg', 'iconAUDIO.gif'),
        'mid'   => array('audio/midi', 'iconAUDIO.gif'),
        'midi'  => array('audio/midi', 'iconAUDIO.gif'),
        'aif'   => array('audio/x-aiff', 'iconAUDIO.gif'),
        'aiff'  => array('audio/x-aiff', 'iconAUDIO.gif'),
        'aifc'  => array('audio/x-aiff', 'iconAUDIO.gif'),
        'ram'   => array('audio/x-pn-realaudio', 'iconAUDIO.gif'),
        'rm'    => array('audio/x-pn-realaudio', 'iconAUDIO.gif'),
        'rpm'   => array('audio/x-pn-realaudio-plugin', 'iconAUDIO.gif'),
        'ra'    => array('audio/x-realaudio', 'iconAUDIO.gif'),

        // Images
        'bmp'   => array('image/bmp', 'iconBMP.gif'),
        'gif'   => array('image/gif', 'iconGIF.gif'),
        'jpeg'  => array('image/jpeg', 'iconJPG.gif'),
        'jpg'   => array('image/jpeg', 'iconJPG.gif'),
        'jpe'   => array('image/jpeg', 'iconJPG.gif'),
        'png'   => array('image/png', 'iconTIF.gif'),
        'tiff'  => array('image/tiff', 'iconTIF.gif'),
        'tif'   => array('image/tiff', 'iconTIF.gif'),

        // Mailbox files
        'eml'   => array('message/rfc822', 'iconPLAIN.gif'),

        // Text files
        'asp'   => array('text/asp', 'iconPLAIN.gif'),
        'css'   => array('text/css', 'iconCSS.gif'),
        'html'  => array('text/html', 'iconHTML.gif'),
        'htm'   => array('text/html', 'iconHTML.gif'),
        'shtml' => array('text/html', 'iconHTML.gif'),
        'txt'   => array('text/plain', 'iconPLAIN.gif'),
        'text'  => array('text/plain', 'iconPLAIN.gif'),
        'log'   => array('text/plain', 'iconPLAIN.gif'),
        'rtx'   => array('text/richtext', 'iconPLAIN.gif'),
        'rtf'   => array('text/rtf', 'iconPLAIN.gif'),
        'xml'   => array('text/xml', 'iconPLAIN.gif'),
        'xsl'   => array('text/xml', 'iconPLAIN.gif'),

        // Videos
        'mp4'   => array('video/mp4', 'iconVIDEO.gif'),
        'm4v'   => array('video/x-m4v', 'iconVIDEO.gif'),
        'mov'   => array('video/mov', 'iconVIDEO.gif'),
        'wmv'   => array('video/wmv', 'iconVIDEO.gif'),
        'webm'  => array('video/webm', 'iconVIDEO.gif'),
        'qt'    => array('video/quicktime', 'iconVIDEO.gif'),
        'rv'    => array('video/vnd.rn-realvideo', 'iconVIDEO.gif'),
        'avi'   => array('video/x-msvideo', 'iconVIDEO.gif'),
        'ogv'   => array('video/ogg', 'iconVIDEO.gif'),
        'movie' => array('video/x-sgi-movie', 'iconVIDEO.gif')
    )
);

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['executePreActions'][]          = array('SyncCtoHelper', 'pingClientStatus');
$GLOBALS['TL_HOOKS']['parseBackendTemplate'][]       = array('SyncCtoHelper', 'checkExtensions');
$GLOBALS['TL_HOOKS']['parseBackendTemplate'][]       = array('SyncCtoHelper', 'checkLockStatus');
$GLOBALS['TL_HOOKS']['addCustomRegexp'][]            = array('SyncCtoHelper', 'customRegexp');
$GLOBALS['TL_HOOKS']['parseBackendTemplate'][]       = array('SyncCtoHelper', 'addLegend');
$GLOBALS['TL_HOOKS']['syncExecuteFinalOperations'][] = array('SyncCtoDatabaseUpdater', 'runAutoUpdate');

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
$strDo    = $objInput->get("do");
$strTable = $objInput->get("table");
$strAct   = $objInput->get("act");

if ($strDo == 'syncCto_backups' && $strTable != '' && ($strAct == 'edit' || $strAct == 'create') && TL_MODE == 'BE')
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
    $GLOBALS['TL_CSS'][] = 'system/modules/syncCto/assets/css/attention.css';
}

// Size limit for files in bytes, will be checked
$GLOBALS['SYC_SIZE']['limit'] = 524288000;

// Size limit for files in bytes, completely ignored
$GLOBALS['SYC_SIZE']['limit_ignore'] = 838860800;

/**
 * Blacklists
 */

// Add some files to the blacklist for the DBAFS from contao.
$arrFileSyncExclude = trimsplit(',', $GLOBALS['TL_CONFIG']['fileSyncExclude']);
$arrFileSyncExclude[] = 'syncCto_backups/debug';
$GLOBALS['TL_CONFIG']['fileSyncExclude'] = implode(',', $arrFileSyncExclude);

// Tables
$GLOBALS['SYC_CONFIG']['table_hidden'] = array_merge( (array) $GLOBALS['SYC_CONFIG']['table_hidden'], array
(
    'tl_files',
    'tl_log',
    'tl_lock',
    'tl_cron',
    'tl_session',
    'tl_search',
    'tl_search_index',
    'tl_undo',
    'tl_version',
    'tl_comments',
    'tl_comments_notify',
    'tl_synccto_clients',
    'tl_synccto_stats'
));

// Folders
$GLOBALS['SYC_CONFIG']['folder_blacklist'] = array_merge( (array) $GLOBALS['SYC_CONFIG']['folder_blacklist'], array
(
    'assets/css/',
    'assets/images/',
    'assets/js/',
    'composer/cache/',
    'system/cache/',
    'system/backup/',
    'system/html/',
    'system/logs/',
    'system/scripts/',
    'system/tmp/',
    '*/syncCto_backups/',
    '*/.git'
));

// Files only sync.
$GLOBALS['SYC_CONFIG']['file_blacklist'] = array_merge( (array) $GLOBALS['SYC_CONFIG']['file_blacklist'], array
(
    'TL_ROOT/.htaccess',
    'TL_ROOT/.htpasswd',
    'localconfig.php',
    'pathconfig.php',
    'system/cron/cron.txt',
    '.DS_Store'
));

// Local config
$GLOBALS['SYC_CONFIG']['local_blacklist'] = array_merge( (array) $GLOBALS['SYC_CONFIG']['local_blacklist'], array
(
    'websitePath',
    'websiteTitle',
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
    'dbSocket',
    'displayErrors',
    'debugMode',
    'maintenanceMode',
    'ctoCom_APIKey',
    'ctoCom_disableRefererCheck',
    'ctoCom_responseLength',
    'ctoCom_handshake',
    'syncCto_debug_mode',
    'syncCto_attentionFlag',
    'syncCto_auto_db_updater',
    'liveUpdateId',
    'disableCron',
    'enableSearch'
));

/**
 * Whitelist
 */
$GLOBALS['SYC_CONFIG']['folder_whitelist'] = array_merge( (array) $GLOBALS['SYC_CONFIG']['folder_whitelist'], array
(
    'assets',
    'composer',
    'contao',
    'plugins',
    'share',
    'system',
    'templates',
    'typolight',
));

/**
 * Sync options
 */
// Core
$GLOBALS['SYC_CONFIG']['sync_options']['core'][] =  'core_change';
$GLOBALS['SYC_CONFIG']['sync_options']['core'][] =  'core_delete';
// User
$GLOBALS['SYC_CONFIG']['sync_options']['user'][] =  'user_change';
$GLOBALS['SYC_CONFIG']['sync_options']['user'][] =  'user_delete';
// User
$GLOBALS['SYC_CONFIG']['sync_options']['configfiles'][] =  'localconfig_update';

/**
 * Maintance options
 */
$GLOBALS['SYC_CONFIG']['maintance_options'] = array_merge( (array) $GLOBALS['SYC_CONFIG']['maintance_options'], array
(
    'temp_tables',
    'temp_folders',
    'xml_create',
));

/**
 * Global configuration
 */
$GLOBALS['SYC_PATH']['db']    = $GLOBALS['TL_CONFIG']['uploadPath'] . '/syncCto_backups/database/';
$GLOBALS['SYC_PATH']['file']  = $GLOBALS['TL_CONFIG']['uploadPath'] . '/syncCto_backups/files/';
$GLOBALS['SYC_PATH']['debug'] = $GLOBALS['TL_CONFIG']['uploadPath'] . '/syncCto_backups/debug/';
$GLOBALS['SYC_PATH']['tmp']   = "system/tmp/";

/**
 * Language mapping for database lookup
 */
$GLOBALS['SYC_CONFIG']['database_mapping'] = array_merge( (array) $GLOBALS['SYC_CONFIG']['database_mapping'], array
(
    'tl_module'                 => 'modules',
    'tl_member_group'           => 'mgroup',
    'tl_user_group'             => 'group',
    'tl_repository_installs'    => 'repository_manager',
    'tl_task'                   => 'tasks',
    'tl_theme'                  => 'themes',
    'tl_style_sheet'            => 'css'
));

/**
 * Folder/Files replacement
 */

// Default
$GLOBALS['SYC_CONFIG']['folder_file_replacement'][' ']  = '_';

// Windows / Unix
$GLOBALS['SYC_CONFIG']['folder_file_replacement']['/']  = '-';

// Windows only
$GLOBALS['SYC_CONFIG']['folder_file_replacement']['\\'] = '-';
$GLOBALS['SYC_CONFIG']['folder_file_replacement'][':']  = '';
$GLOBALS['SYC_CONFIG']['folder_file_replacement']['*']  = '';
$GLOBALS['SYC_CONFIG']['folder_file_replacement']['?']  = '';
$GLOBALS['SYC_CONFIG']['folder_file_replacement']['"']  = '';
$GLOBALS['SYC_CONFIG']['folder_file_replacement']['<']  = '';
$GLOBALS['SYC_CONFIG']['folder_file_replacement']['>']  = '';
$GLOBALS['SYC_CONFIG']['folder_file_replacement']['|']  = '_';

/**
 * CtoCommunication RPC Calls
 */

// - Local Config --------------------------------------------------------------

// Import config
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_IMPORT_CONFIG"] = array
(
    "class"              => "SyncCtoHelper",
    "function"           => "importConfig",
    "typ"                => "POST",
    "parameter"          => array("configlist"),
);

// Get config
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_GET_CONFIG"] = array
(
    "class"              => "SyncCtoRPCFunctions",
    "function"           => "getLocalConfig",
    "typ"                => "POST",
    "parameter"          => array("ConfigBlacklist"),
);

// Get config
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_CREATE_PATHCONFIG"] = array
(
    "class"              => "SyncCtoHelper",
    "function"           => "createPathconfig",
    "typ"                => "GET",
    "parameter"          => false,
);

// - Database ------------------------------------------------------------------

// Run Dump
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_RUN_DUMP"] = array
(
    "class"              => "SyncCtoDatabase",
    "function"           => "runDump",
    "typ"                => "POST",
    "parameter"          => array("tables", "tempfolder"),
);

// Execute SQL
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_EXECUTE_SQL"] = array
(
    "class"              => "SyncCtoRPCFunctions",
    "function"           => "executeSQL",
    "typ"                => "POST",
    "parameter"          => array("sql"),
);

// Load none recommended tables from client
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_NONERECOMMENDED_TABLES"] = array
(
    "class"              => "SyncCtoHelper",
    "function"           => "databaseTablesNoneRecommended",
    "typ"                => "POST",
    "parameter"          => false,
);

// Load recommended tables from client
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_RECOMMENDED_TABLES"] = array
(
    "class"              => "SyncCtoHelper",
    "function"           => "databaseTablesRecommended",
    "typ"                => "POST",
    "parameter"          => false,
);

// Load recommended tables from client
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_HIDDEN_TABLES"] = array
(
    "class"              => "SyncCtoHelper",
    "function"           => "getTablesHidden",
    "typ"                => "GET",
    "parameter"          => false,
);

// Load recommended tables from client
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_HIDDEN_TABLES_PLACEHOLDER"] = array
(
    "class"              => "SyncCtoHelper",
    "function"           => "getPreparedHiddenTablesPlaceholder",
    "typ"                => "GET",
    "parameter"          => false,
);

// Get client timestamp
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_TIMESTAMP"] = array
(
    "class"              => "SyncCtoHelper",
    "function"           => "getDatabaseTablesTimestamp",
    "typ"                => "POST",
    "parameter"          => array("TableList"),
);

// Import a SQL Zip file into database
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_IMPORT_DATABASE"] = array
(
    "class"              => "SyncCtoDatabase",
    "function"           => "runRestore",
    "typ"                => "POST",
    "parameter"          => array("filepath", "additionalSQL"),
);

// Drop tables
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_DROP_TABLES"] = array
(
    "class"              => "SyncCtoDatabase",
    "function"           => "dropTable",
    "typ"                => "POST",
    "parameter"          => array("tablelist", "backup"),
);

// - Files ---------------------------------------------------------------------

// Check for deleted files
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_CHECK_DELETE_FILE"] = array
(
    "class"              => "SyncCtoRPCFunctions",
    "function"           => "checkDeleteFiles",
    "typ"                => "POST",
    "parameter"          => array("md5", "file"),
);

// Delete a files on a list
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_DELETE_FILE"] = array
(
    "class"              => "SyncCtoFiles",
    "function"           => "deleteFiles",
    "typ"                => "POST",
    "parameter"          => array("filelist", "dbafs"),
);

// Import files into contao file system
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_IMPORT_FILE"] = array
(
    "class"              => "SyncCtoFiles",
    "function"           => "moveTempFile",
    "typ"                => "POST",
    "parameter"          => array("filelist", "dbafs"),
);

// Import files into contao file system
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_UPDATE_DBAFS"] = array
(
    "class"              => "SyncCtoFiles",
    "function"           => "updateDbafs",
    "typ"                => "POST",
    "parameter"          => array("filelist"),
);

// Rebuild a split file
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_REBUILD_SPLITFILE"] = array
(
    "class"              => "SyncCtoFiles",
    "function"           => "rebuildSplitFiles",
    "typ"                => "POST",
    "parameter"          => array("splitname", "splitcount", "movepath", "md5"),
);

// Split a file
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_SPLITFILE"] = array
(
    "class"              => "SyncCtoFiles",
    "function"           => "splitFiles",
    "typ"                => "POST",
    "parameter"          => array("splitname", "destfolder", "destfile", "limit"),
);

// Send a file
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_SEND_FILE"] = array
(
    "class"              => "SyncCtoFiles",
    "function"           => "saveFiles",
    "typ"                => "POST",
    "parameter"          => array("metafiles"),
);

// Get a file
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_GET_FILE"] = array
(
    "class"              => "SyncCtoFiles",
    "function"           => "getFile",
    "typ"                => "POST",
    "parameter"          => array("path"),
);

// Compare 2 filelists
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_CHECKSUM_COMPARE"] = array
(
    "class"              => "SyncCtoRPCFunctions",
    "function"           => "runCecksumCompare",
    "typ"                => "POST",
    "parameter"          => array("md5", "file", "disable_dbafs_conflicts"),
);

// Get filelist of contao core
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_CHECKSUM_CORE"] = array
(
    "class"              => "SyncCtoFiles",
    "function"           => "runChecksumCore",
    "typ"                => "GET",
    "parameter"          => FALSE,
);

// Get filelist of file
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_CHECKSUM_FILES"] = array
(
    "class"              => "SyncCtoFiles",
    "function"           => "runChecksumFiles",
    "typ"                => "POST",
    "parameter"          => FALSE,
);

// Get folderlist
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_CHECKSUM_FOLDERS_CORE"] = array
(
    "class"              => "SyncCtoFiles",
    "function"           => "runChecksumFolderCore",
    "typ"                => "POST",
    "parameter"          => FALSE,
);

// Get folderlist
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_CHECKSUM_FOLDERS_FILES"] = array
(
    "class"              => "SyncCtoFiles",
    "function"           => "runChecksumFolderFiles",
    "typ"                => "POST",
    "parameter"          => FALSE,
);

// Search folders which could delete
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_SEARCH_DELETE_FOLDERS"] = array
(
    "class"              => "SyncCtoRPCFunctions",
    "function"           => "searchDeleteFolders",
    "typ"                => "POST",
    "parameter"          => array("md5", "file"),
);

// Run a file backup
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_FILEBACKUP"] = array
(
    "class"              => "SyncCtoFiles",
    "function"           => "runDump",
    "typ"                => "GET",
    "parameter"          => FALSE,
);

// Get information from the DBAFS for some files.
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_DBAFS_INFORMATION"] = array
(
    "class"              => "SyncCtoFiles",
    "function"           => "getDbafsInformationFor",
    "typ"                => "POST",
    "parameter"          => array("files"),
);

// - Miscellaneous -------------------------------------------------------------

// Set displayErrors Flag
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_SET_DISPLAY_ERRORS_FLAG"] = array
(
    "class"              => "SyncCtoRPCFunctions",
    "function"           => "setDisplayErrors",
    "typ"                => "POST",
    "parameter"          => array("state"),
);

// Set the attention flag
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_SET_ATTENTION_FLAG"] = array
(
    "class"              => "SyncCtoRPCFunctions",
    "function"           => "setAttentionFlag",
    "typ"                => "POST",
    "parameter"          => array("state"),
);

// Clear temp folder
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_PURGETEMP"] = array
(
    "class"              => "SyncCtoFiles",
    "function"           => "purgeTemp",
    "typ"                => "GET",
    "parameter"          => FALSE,
);

// Run maintenance
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_MAINTENANCE"] = array
(
    "class"              => "SyncCtoFiles",
    "function"           => "runMaintenance",
    "typ"                => "POST",
    "parameter"          => array("options"),
);

// Execute last step operations
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_EXECUTE_FINAL_OPERATIONS"] = array
(
    "class"              => "SyncCtoHelper",
    "function"           => "executeFinalOperations",
    "typ"                => "GET",
    "parameter"          => FALSE,
);

// - Informations --------------------------------------------------------------

$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_GET_PHP_CONFIGURATION"] = array
(
    "class"              => "SyncCtoModuleCheck",
    "function"           => "getPhpConfigurations",
    "typ"                => "get",
    "parameter"          => null,
);

$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_GET_PHP_FUNCTIONS"] = array
(
    "class"              => "SyncCtoModuleCheck",
    "function"           => "getPhpFunctions",
    "typ"                => "get",
    "parameter"          => null,
);

$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_GET_EXTENDED_INFORMATIONS"] = array
(
    "class"              => "SyncCtoModuleCheck",
    "function"           => "getExtendedInformation",
    "typ"                => "POST",
    "parameter"          => array("DateFormate"),
);

$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_GET_PRO_FUNCTIONS"] = array
(
    "class"              => "SyncCtoModuleCheck",
    "function"           => "getMySqlFunctions",
    "typ"                => "get",
    "parameter"          => null,
);

// Get folder path list
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_GET_PATHLIST"] = array
(
    "class"              => "SyncCtoRPCFunctions",
    "function"           => "getPathList",
    "typ"                => "POST",
    "parameter"          => array("name"),
);

// Get SyncCto Version 
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_VERSION"] = array
(
    "class"              => "SyncCtoRPCFunctions",
    "function"           => "getVersionSyncCto",
    "typ"                => "GET",
    "parameter"          => FALSE,
);

// Get a list of parameter
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_PARAMETER"] = array
(
    "class"              => "SyncCtoRPCFunctions",
    "function"           => "getClientParameter",
    "typ"                => "GET",
    "parameter"          => FALSE,
);