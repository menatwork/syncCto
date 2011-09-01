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
 * Back end modules
 */
$GLOBALS['TL_LANG']['MSC']['edit'] = 'Edit';
$GLOBALS['TL_LANG']['MSC']['copy'] = 'Duplicate';
$GLOBALS['TL_LANG']['MSC']['syncTo'] = 'Synchronize client';
$GLOBALS['TL_LANG']['MSC']['syncFrom'] = 'Synchronize server';

$GLOBALS['TL_LANG']['MSC']['restore_backup'] = 'Restore backup';
$GLOBALS['TL_LANG']['MSC']['start_backup'] = 'Start backup';

$GLOBALS['TL_LANG']['SYC']['files'] = 'File synchronization';
$GLOBALS['TL_LANG']['SYC']['backup'] = 'Backup category';
$GLOBALS['TL_LANG']['SYC']['option_small'] = array('Personal data', 'Just selected files and folders within tl_files are considered.');
$GLOBALS['TL_LANG']['SYC']['option_full'] = array('Contao installation', 'The complete contao installation, including tl_files is considered..');

/**
 * Errors
 */
$GLOBALS['TL_LANG']['ERR']['cant_open'] = "File %s can not be opened.";
$GLOBALS['TL_LANG']['ERR']['file_not_exists'] = "File %s could not be created.";
$GLOBALS['TL_LANG']['ERR']['zero_tables'] = "There are no sql tables for backup.";
$GLOBALS['TL_LANG']['ERR']['table_dmg'] = "The file for the sql tables is corrupted.";
$GLOBALS['TL_LANG']['ERR']['insert_dmg'] = "The file for the sql content is corrupted.";
$GLOBALS['TL_LANG']['ERR']['missing_table_file'] = "The file for the sql table is missing.";
$GLOBALS['TL_LANG']['ERR']['missing_insert_file'] = "The file for the sql content is missing.";
$GLOBALS['TL_LANG']['ERR']['reading_table_file'] = "Could not read the sql tables.";
$GLOBALS['TL_LANG']['ERR']['reading_insert_file'] = "Could not read the sql contents.";
$GLOBALS['TL_LANG']['ERR']['unknown_error'] = "Unknown error.";
$GLOBALS['TL_LANG']['ERR']['unknown_function'] = "Unknown function.";
$GLOBALS['TL_LANG']['ERR']['unknown_method'] = "Unknown method.";
$GLOBALS['TL_LANG']['ERR']['unknown_table'] = "Unknown table.";
$GLOBALS['TL_LANG']['ERR']['no_backup_tables'] = "No tables to backup selected.";
$GLOBALS['TL_LANG']['ERR']['no_backup_file'] = "No backup file selected.";
$GLOBALS['TL_LANG']['ERR']['session_file_error'] = "Could not recovered the file list from the session.";
$GLOBALS['TL_LANG']['ERR']['restore_session_tables'] = "Could not recovered the tables from the session.";
$GLOBALS['TL_LANG']['ERR']['restore_session_zip_id'] = "Could not recovered the zip ID from the session.";
$GLOBALS['TL_LANG']['ERR']['restore_session_zip_name'] = "Could not recovered the zip name from the session.";
$GLOBALS['TL_LANG']['ERR']['unknown_backup_step'] = "Unknown step in the backup process.";
$GLOBALS['TL_LANG']['ERR']['unknown_backup_error'] = "Unknown error in the backup process.";
$GLOBALS['TL_LANG']['ERR']['unknown_restore_error'] = "Unknown error in the recovery process.";
$GLOBALS['TL_LANG']['ERR']['maximum_filesize'] = "Large file(s):";
$GLOBALS['TL_LANG']['ERR']['uploaded_files'] = "Transferred file(s):";
$GLOBALS['TL_LANG']['ERR']['deleted_files_list'] = "Deleted file(s):";
$GLOBALS['TL_LANG']['ERR']['maximum_rpc_calls'] = "Max trys for RPCCall reached with errors.";
$GLOBALS['TL_LANG']['ERR']['maximum_rpc_logins'] = "Max trys for login.";
$GLOBALS['TL_LANG']['ERR']['checksum_error'] = "Error checksum in file(s)";
$GLOBALS['TL_LANG']['ERR']['upload_move_error'] = "Error by saving the file.";
$GLOBALS['TL_LANG']['ERR']['unknown_response'] = "Unknown response from server.";
$GLOBALS['TL_LANG']['ERR']['rpc_call_missing'] = "Missing RPC ID.";
$GLOBALS['TL_LANG']['ERR']['rpc_data_missing'] = "Missing RPC Data";
$GLOBALS['TL_LANG']['ERR']['rpc_unknown'] = "Unknown RPC call.";

?>