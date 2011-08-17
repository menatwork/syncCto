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
 * @copyright  MEN AT WORK 2011 
 * @package    Language
 * @license    GNU/LGPL 
 * @filesource
 */
 
/**
 * Backups
 */
$GLOBALS['TL_LANG']['syncCto']['welcome_backup'] = "Manage backups";
$GLOBALS['TL_LANG']['syncCto']['title_make_backup'] = "Create backups";    
$GLOBALS['TL_LANG']['syncCto']['title_import_backup'] = "Import backups"; 
$GLOBALS['TL_LANG']['syncCto']['db_make_backup'] = array("Create database backup", "Here you can create a backup of the database."); 
$GLOBALS['TL_LANG']['syncCto']['file_make_backup'] = array("Create file backup", "Here you can create a backup of the files."); 
$GLOBALS['TL_LANG']['syncCto']['db_import_backup'] = array("Import database backup", "Here you can import a backup of the database."); 
$GLOBALS['TL_LANG']['syncCto']['file_import_backup'] = array("Import file backup", "Here you can import a backup of the files."); 

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['syncCto']['start_backup'] = "Start backup";
$GLOBALS['TL_LANG']['syncCto']['restore_backup'] = "Import backup";
$GLOBALS['TL_LANG']['syncCto']['sync_client'] = "Synchronize client";
$GLOBALS['TL_LANG']['syncCto']['sync_server'] = "Synchronize server";

/**
 * Headline
 */
$GLOBALS['TL_LANG']['syncCto']['check'] = "System check";
$GLOBALS['TL_LANG']['syncCto']['configuration'] = "PHP configuration";

/**
 * Table
 */
$GLOBALS['TL_LANG']['syncCto']['parameter'] = "Parameter";
$GLOBALS['TL_LANG']['syncCto']['value'] = "Value";
$GLOBALS['TL_LANG']['syncCto']['description'] = "Description";
$GLOBALS['TL_LANG']['syncCto']['on'] = "On";
$GLOBALS['TL_LANG']['syncCto']['off'] = "Off";
$GLOBALS['TL_LANG']['syncCto']['safemode'] = array("Safe mode", "Recommended setting is Off."); 
$GLOBALS['TL_LANG']['syncCto']['met'] = array("Maximum execution time", "Recommended setting is 30 or greater."); 
$GLOBALS['TL_LANG']['syncCto']['memory_limit'] = array("Memory limit", "Recommended setting is 128M or greater."); 
$GLOBALS['TL_LANG']['syncCto']['register_globals'] = array("Register globals", "Recommended setting is Off."); 
$GLOBALS['TL_LANG']['syncCto']['file_uploads'] = array("File uploads", "Recommended setting is On."); 
$GLOBALS['TL_LANG']['syncCto']['umf'] = array("Upload maximum filesize", "Recommended setting is 8M or greater.");
$GLOBALS['TL_LANG']['syncCto']['pms'] = array("Post maximum size", "Recommended setting is 8M or greater."); 
$GLOBALS['TL_LANG']['syncCto']['mit'] = array("Maximum input time", "Recommended setting is -1, 60 or greater."); 
$GLOBALS['TL_LANG']['syncCto']['dst'] = array("Default socket timeout", "Recommended setting is 30 or greater.");
$GLOBALS['TL_LANG']['syncCto']['fsocket'] = array("Fsockopen", "Recommended setting is On.");
$GLOBALS['TL_LANG']['syncCto']['fopen'] = array("Fopen", "Recommended setting is On.");
$GLOBALS['TL_LANG']['syncCto']['zip_archive'] = array("ZipArchive", "Recommended setting is On.");

/**
 * Text
 */
 
$GLOBALS['TL_LANG']['syncCto']['other_sync_issues'] = "Other known issues";
$GLOBALS['TL_LANG']['syncCto']['explanation_sync_issues'] = "Some server configuration settings are preventing the synchronization, which cannot be detected by the system check.";
$GLOBALS['TL_LANG']['syncCto']['known_issues'] = "Some known settings are:";
$GLOBALS['TL_LANG']['syncCto']['suhosin'] = "Suhosin is preventing the synchronisation";
$GLOBALS['TL_LANG']['syncCto']['max_request_len'] = "The MaxRequestLen is too low";

$GLOBALS['TL_LANG']['syncCto']['safemodehack'] = 'syncCto cannot be used because of missing write permissions.';

$GLOBALS['TL_LANG']['syncCto']['ok'] = "OK";
$GLOBALS['TL_LANG']['syncCto']['progress'] = "In Progress";
$GLOBALS['TL_LANG']['syncCto']['error'] = "Error";
$GLOBALS['TL_LANG']['syncCto']['skipped'] = "Skipped";
$GLOBALS['TL_LANG']['syncCto']['select_all_files'] = "Select all";
$GLOBALS['TL_LANG']['syncCto']['sync_info'] = "Please do not close the current window during the entire synchronization.";
$GLOBALS['TL_LANG']['syncCto']['run_time'] = "Run time: %s seconds";

$GLOBALS['TL_LANG']['syncCto']['size'] = 'Size of all files:';
$GLOBALS['TL_LANG']['syncCto']['new_file'] = 'New';
$GLOBALS['TL_LANG']['syncCto']['modified_file'] = 'Modified';
$GLOBALS['TL_LANG']['syncCto']['unknown_file'] = 'Unknown';
$GLOBALS['TL_LANG']['syncCto']['deleted_file'] = 'Deleted';
$GLOBALS['TL_LANG']['syncCto']['submit_files'] = 'Submit files';
$GLOBALS['TL_LANG']['syncCto']['delete_files'] = 'Delete files';

$GLOBALS['TL_LANG']['syncCto']['big_files'] = 'File(s) for transmission.';
$GLOBALS['TL_LANG']['syncCto']['skipped_files'] = 'Too big File(s) for transmission.';
$GLOBALS['TL_LANG']['syncCto']['ignored_files'] = 'Too big File(s) for processing.';

$GLOBALS['TL_LANG']['syncCto']['information_last_sync'] = 'The last synchronization was done at %s on %s by the User %s (%s).';

/**
 * Title attributes
 */
$GLOBALS['TL_LANG']['syncCto']['server_online'] = "Client ID %s is online";
$GLOBALS['TL_LANG']['syncCto']['server_missing'] = "syncCto is missing on the client ID %s";
$GLOBALS['TL_LANG']['syncCto']['server_offline'] = "Client ID %s is offline";

/**
 * Errors
 */
$GLOBALS['TL_LANG']['syncCto']['cant_open'] = "File %s can not be opened.";
$GLOBALS['TL_LANG']['syncCto']['file_not_exists'] = "File %s could not be created.";
$GLOBALS['TL_LANG']['syncCto']['zero_tables'] = "There are no sql tables for backup.";
$GLOBALS['TL_LANG']['syncCto']['table_dmg'] = "The file for the sql tables is corrupted.";
$GLOBALS['TL_LANG']['syncCto']['insert_dmg'] = "The file for the sql content is corrupted.";
$GLOBALS['TL_LANG']['syncCto']['missing_table_file'] = "The file for the sql table is missing.";
$GLOBALS['TL_LANG']['syncCto']['missing_insert_file'] = "The file for the sql content is missing.";
$GLOBALS['TL_LANG']['syncCto']['reading_table_file'] = "Could not read the sql tables.";
$GLOBALS['TL_LANG']['syncCto']['reading_insert_file'] = "Could not read the sql contents.";
$GLOBALS['TL_LANG']['syncCto']['unknown_error'] = "Unknown error.";
$GLOBALS['TL_LANG']['syncCto']['unknown_function'] = "Unknown function.";
$GLOBALS['TL_LANG']['syncCto']['unknown_method'] = "Unknown method.";
$GLOBALS['TL_LANG']['syncCto']['unknown_table'] = "Unknown table.";
$GLOBALS['TL_LANG']['syncCto']['no_backup_tables'] = "No tables to backup selected.";
$GLOBALS['TL_LANG']['syncCto']['no_backup_file'] = "No backup file selected.";
$GLOBALS['TL_LANG']['syncCto']['session_file_error'] = "Could not recovered the file list from the session.";
$GLOBALS['TL_LANG']['syncCto']['restore_session_tables'] = "Could not recovered the tables from the session.";
$GLOBALS['TL_LANG']['syncCto']['restore_session_zip_id'] = "Could not recovered the zip ID from the session.";
$GLOBALS['TL_LANG']['syncCto']['restore_session_zip_name'] = "Could not recovered the zip name from the session.";
$GLOBALS['TL_LANG']['syncCto']['unknown_backup_step'] = "Unknown step in the backup process.";
$GLOBALS['TL_LANG']['syncCto']['unknown_backup_error'] = "Unknown error in the backup process.";
$GLOBALS['TL_LANG']['syncCto']['unknown_restore_error'] = "Unknown error in the recovery process.";
$GLOBALS['TL_LANG']['syncCto']['maximum_filesize'] = "Large file(s):";
$GLOBALS['TL_LANG']['syncCto']['uploaded_files'] = "Transferred file(s):";
$GLOBALS['TL_LANG']['syncCto']['deleted_files_list'] = "Deleted file(s):";
$GLOBALS['TL_LANG']['syncCto']['maximum_rpc_calls'] = "Max trys for RPCCall reached with errors.";
$GLOBALS['TL_LANG']['syncCto']['maximum_rpc_logins'] = "Max trys for login.";
$GLOBALS['TL_LANG']['syncCto']['checksum_error'] = "Error checksum in file(s)";
$GLOBALS['TL_LANG']['syncCto']['upload_move_error'] = "Error by saving the file.";
$GLOBALS['TL_LANG']['syncCto']['unknown_response'] = "Unknown response from server.";
$GLOBALS['TL_LANG']['syncCto']['rpc_call_missing'] = "Missing RPC ID.";
$GLOBALS['TL_LANG']['syncCto']['rpc_data_missing'] = "Missing RPC Data";
$GLOBALS['TL_LANG']['syncCto']['rpc_unknown'] = "Unknown RPC call.";
?>