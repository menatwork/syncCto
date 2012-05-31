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
 * @package    Language
 * @license    GNU/LGPL 
 * @filesource
 */

/**
 * Reference
 */
// Sync
$GLOBALS['TL_LANG']['SYC']['core']                          = 'Contao Installation';
$GLOBALS['TL_LANG']['SYC']['core_change']                   = 'Modified files';
$GLOBALS['TL_LANG']['SYC']['core_delete']                   = 'Deleted files';
$GLOBALS['TL_LANG']['SYC']['user']                          = 'Personal data (tl_files)';
$GLOBALS['TL_LANG']['SYC']['user_change']                   = 'Modified files';
$GLOBALS['TL_LANG']['SYC']['user_delete']                   = 'Deleted files';
$GLOBALS['TL_LANG']['SYC']['configfiles']                   = 'Configuration files';
$GLOBALS['TL_LANG']['SYC']['localconfig_update']            = 'Update all entries';
$GLOBALS['TL_LANG']['SYC']['localconfig_errors']            = 'Display errors';
// Maintenance options  
$GLOBALS['TL_LANG']['SYC']['options']                       = 'Options';    
$GLOBALS['TL_LANG']['SYC']['search_index']                  = 'Rebuild the search index';    
$GLOBALS['TL_LANG']['SYC']['temp_tables']                   = 'Purge db tables';    
$GLOBALS['TL_LANG']['SYC']['temp_folders']                  = 'Purge temp files';    
$GLOBALS['TL_LANG']['SYC']['css_create']                    = 'Create CSS files';    
$GLOBALS['TL_LANG']['SYC']['xml_create']                    = 'Create XML files';   

/**
 * Back end modules
 */
$GLOBALS['TL_LANG']['MSC']['edit']                          = 'Edit';
$GLOBALS['TL_LANG']['MSC']['copy']                          = 'Dublicate';
$GLOBALS['TL_LANG']['MSC']['syncTo']                        = 'Synchronize client';
$GLOBALS['TL_LANG']['MSC']['syncFrom']                      = 'Synchronize server';

$GLOBALS['TL_LANG']['MSC']['restore_backup']                = 'Restore backup';
$GLOBALS['TL_LANG']['MSC']['start_backup']                  = 'Start backup';

/**
 * Text
 */
$GLOBALS['TL_LANG']['MSC']['ok']                            = 'OK';
$GLOBALS['TL_LANG']['MSC']['progress']                      = 'In progress';
$GLOBALS['TL_LANG']['MSC']['error']                         = 'Error';
$GLOBALS['TL_LANG']['MSC']['skipped']                       = 'Skipped';
$GLOBALS['TL_LANG']['MSC']['skip']                          = 'Skip';
$GLOBALS['TL_LANG']['MSC']['popup']                         = 'Open compare list';
$GLOBALS['TL_LANG']['MSC']['unknown_step']                  = 'Unknown step';

/**
 * Headline
 */
$GLOBALS['TL_LANG']['MSC']['step']                          = 'Step';
$GLOBALS['TL_LANG']['MSC']['abort']                         = "Abort!";
$GLOBALS['TL_LANG']['MSC']['complete']                      = 'Completed!';
$GLOBALS['TL_LANG']['MSC']['debug_mode']                    = 'Debug mode';

/**
 * Filelist
 */
$GLOBALS['TL_LANG']['MSC']['select_all_files']              = 'Select all files';
$GLOBALS['TL_LANG']['MSC']['sync_info']                     = 'Please don\'t close the current window, during the whole synchronization process.';
$GLOBALS['TL_LANG']['MSC']['state']                         = 'State';
$GLOBALS['TL_LANG']['MSC']['filesize']                      = 'Filesize';
$GLOBALS['TL_LANG']['MSC']['file']                          = 'File';
$GLOBALS['TL_LANG']['MSC']['totalsize']                     = 'Total size of files:';
$GLOBALS['TL_LANG']['MSC']['new_file']                      = 'New';
$GLOBALS['TL_LANG']['MSC']['modified_file']                 = 'Modified';
$GLOBALS['TL_LANG']['MSC']['unknown_file']                  = 'Unknown';
$GLOBALS['TL_LANG']['MSC']['deleted_file']                  = 'Deleted';
$GLOBALS['TL_LANG']['MSC']['big_files']                     = 'Groß';
$GLOBALS['TL_LANG']['MSC']['skipped_files']                 = 'Übersprungen';
$GLOBALS['TL_LANG']['MSC']['ignored_files']                 = 'Ignoriert';
$GLOBALS['TL_LANG']['MSC']['submit_files']                  = 'Submit files';
$GLOBALS['TL_LANG']['MSC']['delete_files']                  = 'Delete selected files from the list';
$GLOBALS['TL_LANG']['MSC']['last_sync']                     = 'Last synchronisation was made at %s on %s from %s (%s).';
$GLOBALS['TL_LANG']['MSC']['disabled_cache']                = 'Current entries in the "initconfig.php" disrupt a successful synchronization.';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['MSC']['abort_sync']                    = array("Abort", 'In progress');
$GLOBALS['TL_LANG']['MSC']['repeat_sync']                   = array("Repeat", 'In progress');

/**
 * Debug mode
 */
$GLOBALS['TL_LANG']['MSC']['run_time']                      = 'Running time: %s seconds';
$GLOBALS['TL_LANG']['MSC']['memory_limit']                  = 'Memory limit: %s';

/**
 * Errors
 */
$GLOBALS['TL_LANG']['ERR']['missing_file_selection']        = 'No file(s) is/are selected.';
$GLOBALS['TL_LANG']['ERR']['missing_file_information']      = 'Missing file or file information.';
$GLOBALS['TL_LANG']['ERR']['unknown_file']                  = 'Could not be found the file %s.';
$GLOBALS['TL_LANG']['ERR']['unknown_file_in_zip']           = 'Could not be found the file %s in the zip file.';
$GLOBALS['TL_LANG']['ERR']['unknown_path']                  = 'Unknown path.';
$GLOBALS['TL_LANG']['ERR']['cant_open']                     = 'Can not open file %s.';
$GLOBALS['TL_LANG']['ERR']['checksum_error']                = 'Checksum error.';
$GLOBALS['TL_LANG']['ERR']['cant_move_file']                = 'Can\'t move file from %s to %s.';
$GLOBALS['TL_LANG']['ERR']['cant_delete_file']              = 'Error deleting file.';
$GLOBALS['TL_LANG']['ERR']['attention_headline']            = 'Beware of changes to the system';
$GLOBALS['TL_LANG']['ERR']['attention_text']                = 'There is an updating process running in the background, which might overwrite your changes. Please wait until the update process is finished, before changing any content or settings of this installation.';
$GLOBALS['TL_LANG']['ERR']['min_size_limit']                = 'The minimum file size of %s has been reached.';

// Database
$GLOBALS['TL_LANG']['ERR']['missing_tables_selection']      = 'No table(s) is/are selected.';

// Functions
$GLOBALS['TL_LANG']['ERR']['unknown_backup_method']         = 'The choosen backup method is unknown.';
$GLOBALS['TL_LANG']['ERR']['unknown_function']              = 'Unknown function or method.';
$GLOBALS['TL_LANG']['ERR']['64Bit_error']                   = 'Number overflow. Please try a 64bit version from PHP.';

// Client
$GLOBALS['TL_LANG']['ERR']['maximum_filesize']              = 'To big file(s):';
$GLOBALS['TL_LANG']['ERR']['call_directly']                 = 'Do not try to run the synchronization directly.';
$GLOBALS['TL_LANG']['ERR']['client_set']                    = 'The communication between server and client is failed.';
$GLOBALS['TL_LANG']['ERR']['unknown_client']                = 'Unknown client.';
$GLOBALS['TL_LANG']['ERR']['referer']                       = 'The clients referer-check could not be deactivated.';
$GLOBALS['TL_LANG']['ERR']['version']                       = 'Version conflict in %s. <br />Server: %s <br />Client: %s';
$GLOBALS['TL_LANG']['ERR']['upload_ini']                    = 'No success with upload. Funktion is deactivated in the php.ini';
$GLOBALS['TL_LANG']['ERR']['rebuild']                       = 'Error rebuilding the file(s). Path: %s';
$GLOBALS['TL_LANG']['ERR']['send']                          = 'Error sending file(s).';

?>