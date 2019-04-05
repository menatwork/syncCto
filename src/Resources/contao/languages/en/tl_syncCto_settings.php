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
$GLOBALS['TL_LANG']['tl_syncCto_settings']['edit']                      = 'Edit the syncCto configuration';
$GLOBALS['TL_LANG']['tl_syncCto_settings']['blacklist_legend']          = 'Files and folders blacklist';
$GLOBALS['TL_LANG']['tl_syncCto_settings']['local_blacklist_legend']    = 'localconfig.php blacklist';
$GLOBALS['TL_LANG']['tl_syncCto_settings']['whitelist_legend']          = 'Whitelist for root folders';
$GLOBALS['TL_LANG']['tl_syncCto_settings']['hidden_tables_legend']      = 'Hidden tables';
$GLOBALS['TL_LANG']['tl_syncCto_settings']['tables_legend']             = 'Not recommended tables';
$GLOBALS['TL_LANG']['tl_syncCto_settings']['security_legend']           = 'Encryption';
$GLOBALS['TL_LANG']['tl_syncCto_settings']['custom_legend']             = 'Expert settings';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_syncCto_settings']['folder_blacklist']          = array('Folder', 'Here you can define which folders should be ignored for synchronization.');
$GLOBALS['TL_LANG']['tl_syncCto_settings']['file_blacklist']            = array('File', 'Here you can define which files should be ignored for synchronization.');
$GLOBALS['TL_LANG']['tl_syncCto_settings']['folder_whitelist']          = array('Allowd root folders', 'Here you can define which root folder for synchronization should be considered.');
$GLOBALS['TL_LANG']['tl_syncCto_settings']['local_blacklist']           = array('localconfig.php', 'Here you can define which localconfig.php entries should not be synchronized.');
$GLOBALS['TL_LANG']['tl_syncCto_settings']['database_tables']           = array('Not recommended tables', 'Here you can define which database tables you do not recommend for the synchronization.');
$GLOBALS['TL_LANG']['tl_syncCto_settings']['hidden_tables']             = array('Hidden tables', 'Here you can grant access to one or more database tables for the synchronization.');
$GLOBALS['TL_LANG']['tl_syncCto_settings']['hidden_tables_placeholder'] = array('Hidden tables - Placeholder', 'Here you can grant access to temporary database tables, that are periodical created and deleted (eg Tabimporter).');
$GLOBALS['TL_LANG']['tl_syncCto_settings']['debug_mode']                = array('Activate debug mode', 'Print information like runtime or synchronized during the synchronization');
$GLOBALS['TL_LANG']['tl_syncCto_settings']['custom_settings']           = array('Activate expert settings', 'Click here if you know what you are doing.');
$GLOBALS['TL_LANG']['tl_syncCto_settings']['wait_timeout']              = array('Configure "wait_timeout"', 'More informationen: http://goo.gl/rC5Y4');
$GLOBALS['TL_LANG']['tl_syncCto_settings']['interactive_timeout']       = array('Configure "interactive_timeout"', 'More informationen: http://goo.gl/VHxRK');
$GLOBALS['TL_LANG']['tl_syncCto_settings']['db_query_limt']             = array('Database query limit', 'Here you can define how many records will be loaded from the database at once. If you encounter a "500 server error" when synchronizing the database, you should set the limit to a lower value.');
$GLOBALS['TL_LANG']['tl_syncCto_settings']['auto_db_updater']           = array('Automatic updating of the database', 'Here you can choose the actions for the automatic database update, after the synchronization is finished.');

/**
 * Updater
 */
$GLOBALS['TL_LANG']['tl_syncCto_settings']['CREATE']                    = 'Create new tables';
$GLOBALS['TL_LANG']['tl_syncCto_settings']['ALTER_ADD']                 = 'Add new columns';
$GLOBALS['TL_LANG']['tl_syncCto_settings']['ALTER_CHANGE']              = 'Change existing columns';
$GLOBALS['TL_LANG']['tl_syncCto_settings']['ALTER_DROP']                = 'Drop existing columns';
$GLOBALS['TL_LANG']['tl_syncCto_settings']['DROP']                      = 'Drop existing tables';

/**
 * Miscellaneous
 */
$GLOBALS['TL_LANG']['tl_syncCto_settings']['hide_by_regex']             = "%s <span style='color: #999; display:inline;'>(Temporary database tables)</span>";
