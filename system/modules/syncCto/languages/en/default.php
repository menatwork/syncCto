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
$GLOBALS['TL_LANG']['SYC']['core']                          = 'Contao Installation';
$GLOBALS['TL_LANG']['SYC']['core_change']                   = 'Modified files';
$GLOBALS['TL_LANG']['SYC']['core_delete']                   = 'Deleted files';
$GLOBALS['TL_LANG']['SYC']['user']                          = 'Personal data (tl_files)';
$GLOBALS['TL_LANG']['SYC']['user_change']                   = 'Modified files';
$GLOBALS['TL_LANG']['SYC']['user_delete']                   = 'Deleted files';
$GLOBALS['TL_LANG']['SYC']['configfiles']                   = 'Configuration files';
$GLOBALS['TL_LANG']['SYC']['localconfig_update']            = 'Update all entries';
$GLOBALS['TL_LANG']['SYC']['localconfig_errors']            = 'Display errors';  
$GLOBALS['TL_LANG']['SYC']['temp_tables']                   = 'Database tables';    
$GLOBALS['TL_LANG']['SYC']['temp_folders']                  = 'Temp files';    
$GLOBALS['TL_LANG']['SYC']['css_create']                    = 'CSS files';    
$GLOBALS['TL_LANG']['SYC']['xml_create']                    = 'XML files';   

/**
 * Back end modules
 */
$GLOBALS['TL_LANG']['MSC']['edit']                          = 'Edit';
$GLOBALS['TL_LANG']['MSC']['copy']                          = 'Dublicate';
$GLOBALS['TL_LANG']['MSC']['showExtern']                    = 'Client Systemcheck';
$GLOBALS['TL_LANG']['MSC']['sync']                          = 'Synchronize';
$GLOBALS['TL_LANG']['MSC']['syncTo']                        = 'Synchronize client';
$GLOBALS['TL_LANG']['MSC']['syncFrom']                      = 'Synchronize server';

/**
 * Text
 */
$GLOBALS['TL_LANG']['MSC']['skip']                          = 'Skip';
$GLOBALS['TL_LANG']['MSC']['popup']                         = 'Open compare list';
$GLOBALS['TL_LANG']['MSC']['unknown_step']                  = 'Unknown step';
$GLOBALS['TL_LANG']['MSC']['last_sync']                     = 'Last synchronisation was made at %s on %s from %s (%s).';
$GLOBALS['TL_LANG']['MSC']['disabled_cache']                = 'Current entries in the "initconfig.php" disrupt a successful synchronization.';

/**
 * Headline
 */
$GLOBALS['TL_LANG']['MSC']['step']                          = 'Step';
$GLOBALS['TL_LANG']['MSC']['substep']                       = 'Substep';
$GLOBALS['TL_LANG']['MSC']['abort']                         = "Abort!";
$GLOBALS['TL_LANG']['MSC']['complete']                      = 'Completed!';
$GLOBALS['TL_LANG']['MSC']['comparelist']                   = 'Compare list';
$GLOBALS['TL_LANG']['MSC']['debug_mode']                    = 'Debug mode';

/**
 * Filelist
 */
$GLOBALS['TL_LANG']['MSC']['state']                         = 'State';
$GLOBALS['TL_LANG']['MSC']['file']                          = 'File';               
$GLOBALS['TL_LANG']['MSC']['totalsize']                     = 'Total size of files:';
$GLOBALS['TL_LANG']['MSC']['skipped']                       = 'Skipped';
$GLOBALS['TL_LANG']['MSC']['ignored']                       = 'Ignored';
$GLOBALS['TL_LANG']['MSC']['client']                        = 'Client';
$GLOBALS['TL_LANG']['MSC']['server']                        = 'Server';
$GLOBALS['TL_LANG']['MSC']['sync_target']                   = 'Target';
$GLOBALS['TL_LANG']['MSC']['sync_source']                   = 'Source';
$GLOBALS['TL_LANG']['MSC']['difference']                    = 'Difference';
$GLOBALS['TL_LANG']['MSC']['recom_tables']                  = 'Recommended database tables';
$GLOBALS['TL_LANG']['MSC']['nonrecom_tables']               = 'Not recommended database tables';
$GLOBALS['TL_LANG']['MSC']['normal_files']                  = 'Normal files';
$GLOBALS['TL_LANG']['MSC']['big_files']                     = 'Big files';
$GLOBALS['TL_LANG']['MSC']['changed']                       = 'Changed';
$GLOBALS['TL_LANG']['MSC']['unchanged']                     = 'Unchanged';
$GLOBALS['TL_LANG']['MSC']['both_changed']                  = 'Both changed';

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
$GLOBALS['TL_LANG']['ERR']['unknown_file']                  = 'The file %s could not be found.';
$GLOBALS['TL_LANG']['ERR']['unknown_path']                  = 'Unknown path.';
$GLOBALS['TL_LANG']['ERR']['cant_open']                     = 'Can\'t open file %s.';
$GLOBALS['TL_LANG']['ERR']['checksum_error']                = 'Checksum error.';
$GLOBALS['TL_LANG']['ERR']['cant_move_file']                = 'Can\'t move the file from %s to %s.';
$GLOBALS['TL_LANG']['ERR']['cant_delete_file']              = 'Error deleting file.';
$GLOBALS['TL_LANG']['ERR']['attention_headline']            = 'Beware of changes to the system';
$GLOBALS['TL_LANG']['ERR']['attention_text']                = 'The contao installation is currently in <strong>maintenance mode of syncCto</strong>. Any changes made in this installation may be overwritten by the master installation. This message can only be removed by synchronization by the master installation.';
$GLOBALS['TL_LANG']['ERR']['min_size_limit']                = 'The minimum file size of %s has been reached.';
$GLOBALS['TL_LANG']['ERR']['cant_extract_file']             = 'Failed to extract the files.';
$GLOBALS['TL_LANG']['ERR']['missing_tables']                = 'No table(s) is/are selected.';
$GLOBALS['TL_LANG']['ERR']['unknown_function']              = 'Unknown function or method.';
$GLOBALS['TL_LANG']['ERR']['64Bit_error']                   = 'Number overflow. Please try a 64bit version from PHP.';
$GLOBALS['TL_LANG']['ERR']['maximum_filesize']              = 'To big file(s):';
$GLOBALS['TL_LANG']['ERR']['call_directly']                 = 'Do not try to run the synchronization directly.';
$GLOBALS['TL_LANG']['ERR']['client_set']                    = 'The communication between server and client is failed.';
$GLOBALS['TL_LANG']['ERR']['unknown_client']                = 'Unknown client.';
$GLOBALS['TL_LANG']['ERR']['referer']                       = 'The clients referer-check could not be deactivated.';
$GLOBALS['TL_LANG']['ERR']['version']                       = 'Version conflict in %s. <br />Server: %s <br />Client: %s';
$GLOBALS['TL_LANG']['ERR']['upload_ini']                    = 'No success with upload. Funktion is deactivated in the php.ini';
$GLOBALS['TL_LANG']['ERR']['rebuild']                       = 'Error rebuilding the file(s). Path: %s';

?>