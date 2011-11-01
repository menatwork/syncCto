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
 * Legends
 */
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['sync_legend'] = "Synchronization settings";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['confirm_db_import_legend'] = "Database settings";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['edit'] = 'Server synchronization';
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['back'] = 'Go back';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['sync_type'] = array("Type of synchronization", "Here you can select the type of synchronization.");
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['confirm_db_import'] = array("Import database", "Here you can define whether a database should be import.");

/**
 * List
 */
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step'] = "Step";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['complete'] = "Done!";

$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step1_help'] = "Checking server.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step1_msg1'] = "Preparing server for synchronization.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step1_msg2'] = "Not the same syncCto version.";

$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step2_help'] = "Checking and transferring MD5 list";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step2_msg1'] = "%s new and %s modified file(s) are found.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step2_msg2'] = "Checking files.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step2_msg3'] = "Checking contao installation.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step2_msg4'] = "Files successfully verified. Loading compare list.";

$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step3_help'] = "Creating and transferring sql scripts.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step3_msg1'] = "Importing sql scripts.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step3_msg2'] = "Creating zip file.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step3_msg3'] = "Creating sql backup.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step3_msg4'] = "Verifing zip file.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step3_msg5'] = "Transferring zip file.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step3_msg6'] = "File %s was successfully transferred to the server and imported.";

$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step4_help'] = "Synchronizing large files.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step4_msg1'] = "Searching large files.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step4_msg2'] = "Transferring large files.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step4_msg3'] = "%s of %s large file(s) have been transferred.";

$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step5_help'] = "Synchronizing files.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step5_msg1'] = "Transferring files.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step5_msg2'] = "Upload failed. Function is disabled in php.ini.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step5_msg3'] = "%s of %s files were transferred.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step5_msg4'] = " File(s) will be skipped.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step5_msg5'] = "Importing files.";

$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['complete_help'] = "Server successfully synchronized.";

?>