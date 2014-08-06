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
 * Reference
 */
$GLOBALS['TL_LANG']['SYC']['core']                          = 'Contao installation';
$GLOBALS['TL_LANG']['SYC']['core_change']                   = 'Modified data';
$GLOBALS['TL_LANG']['SYC']['core_delete']                   = 'Deleted data';
$GLOBALS['TL_LANG']['SYC']['user']                          = 'File manager (files)';
$GLOBALS['TL_LANG']['SYC']['user_change']                   = 'Modified data';
$GLOBALS['TL_LANG']['SYC']['user_delete']                   = 'Deleted data';
$GLOBALS['TL_LANG']['SYC']['configfiles']                   = 'Configuration files';
$GLOBALS['TL_LANG']['SYC']['localconfig_update']            = 'Update all entries';
$GLOBALS['TL_LANG']['SYC']['localconfig_errors']            = 'Display errors';
$GLOBALS['TL_LANG']['SYC']['temp_tables']                   = 'Database cache';
$GLOBALS['TL_LANG']['SYC']['temp_folders']                  = 'File cache';
$GLOBALS['TL_LANG']['SYC']['xml_create']                    = 'XML files';

/**
 * Back end modules
 */
$GLOBALS['TL_LANG']['MSC']['edit']                          = 'Edit';
$GLOBALS['TL_LANG']['MSC']['copy']                          = 'Duplicate';
$GLOBALS['TL_LANG']['MSC']['showExtern']                    = 'Client System check';
$GLOBALS['TL_LANG']['MSC']['sync']                          = 'Synchronize';
$GLOBALS['TL_LANG']['MSC']['syncAll']                       = 'Overwrite system';
$GLOBALS['TL_LANG']['MSC']['syncTo']                        = 'Synchronize client';
$GLOBALS['TL_LANG']['MSC']['syncFrom']                      = 'Synchronize server';

/**
 * Text
 */
$GLOBALS['TL_LANG']['MSC']['skip']                          = 'Skip';
$GLOBALS['TL_LANG']['MSC']['popup']                         = 'Open compare list';
$GLOBALS['TL_LANG']['MSC']['unknown_step']                  = 'Unknown step';
$GLOBALS['TL_LANG']['MSC']['last_sync']                     = 'Last synchronisation was made at %s on %s from %s (%s).';
$GLOBALS['TL_LANG']['MSC']['disabled_cache']                = 'Current entries in the "initconfig.php" disrupt a successfully synchronization.';

/**
 * Headline
 */
$GLOBALS['TL_LANG']['MSC']['step']                          = 'Step';
$GLOBALS['TL_LANG']['MSC']['substep']                       = 'Substep';
$GLOBALS['TL_LANG']['MSC']['abort']                         = "Abort!";
$GLOBALS['TL_LANG']['MSC']['complete']                      = 'Completed!';
$GLOBALS['TL_LANG']['MSC']['debug_mode']                    = 'Debug mode';

/**
 * Filelist
 */
$GLOBALS['TL_LANG']['MSC']['state']                         = 'State';
$GLOBALS['TL_LANG']['MSC']['fileTime']                      = 'Timestamp';
$GLOBALS['TL_LANG']['MSC']['file']                          = 'File';
$GLOBALS['TL_LANG']['MSC']['totalsize']                     = 'Total size of files:';
$GLOBALS['TL_LANG']['MSC']['skipped']                       = 'Skipped';
$GLOBALS['TL_LANG']['MSC']['ignored']                       = 'Ignored';
$GLOBALS['TL_LANG']['MSC']['dbafs_conflict']                = 'Conflict';
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
$GLOBALS['TL_LANG']['MSC']['difference_new']                = 'New';
$GLOBALS['TL_LANG']['MSC']['difference_deleted']            = 'Deleted';
$GLOBALS['TL_LANG']['MSC']['skipped_files']                 = ' skipped files.';
$GLOBALS['TL_LANG']['MSC']['pattern']                       = 'Pattern (Regex)';
$GLOBALS['TL_LANG']['MSC']['select']                        = 'Select';
$GLOBALS['TL_LANG']['MSC']['unselect']                      = 'Unselect';
$GLOBALS['TL_LANG']['MSC']['toggle']                        = 'Toggle selection';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['MSC']['abort_sync']                    = array('Abort', 'In progress');
$GLOBALS['TL_LANG']['MSC']['repeat_sync']                   = array('Repeat', 'In progress');
$GLOBALS['TL_LANG']['MSC']['next_sync']                     = array('Next client', 'In progress');

/**
 * Debug mode
 */
$GLOBALS['TL_LANG']['MSC']['run_time']                      = 'Running time: %s seconds';
$GLOBALS['TL_LANG']['MSC']['memory_limit']                  = 'Memory limit: %s';

/**
 * DBFAS
 */
$GLOBALS['TL_LANG']['MSC']['dbafs_all_green']               = 'The DBAFS import seems to have no problems.';
$GLOBALS['TL_LANG']['ERR']['dbafs_error']                   = 'There are some problems in the DBAFS:';
$GLOBALS['TL_LANG']['ERR']['dbafs_uuid_conflict']           = 'Conflict in the DBFAS from Contao. Renamed the original file.';
$GLOBALS['TL_LANG']['ERR']['dbafs_uuid_conflict_rename']    = 'Conflict in the DBFAS from Contao. Renamed the original file to _%s.';
$GLOBALS['TL_LANG']['ERR']['dbafs_diff_data']               = 'The source and destination files have different metadata.';

/**
 * Errors
 */
$GLOBALS['TL_LANG']['ERR']['missing_file_folder']           = 'Missing file/folder "%s".';
$GLOBALS['TL_LANG']['ERR']['missing_file_selection']        = 'No file(s) is/are selected.';
$GLOBALS['TL_LANG']['ERR']['missing_file_information']      = 'Missing file or file information.';
$GLOBALS['TL_LANG']['ERR']['unknown_file']                  = 'The file %s could not be found.';
$GLOBALS['TL_LANG']['ERR']['unknown_path']                  = 'Unknown path.';
$GLOBALS['TL_LANG']['ERR']['cant_open']                     = 'Can\'t open file %s.';
$GLOBALS['TL_LANG']['ERR']['checksum_error']                = 'Checksum error.';
$GLOBALS['TL_LANG']['ERR']['cant_move_file']                = 'Can\'t move the file from %s to %s.';
$GLOBALS['TL_LANG']['ERR']['cant_move_files']               = 'Can\'t move the files.';
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
$GLOBALS['TL_LANG']['ERR']['no_functions']                  = 'There are no options for the synchronization.';
$GLOBALS['TL_LANG']['ERR']['pattern']                       = 'Invalid pattern (regular expression?).';

