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
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['backup_legend'] = "Backup settings";
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['filelist_legend'] = "Files and folders";
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['edit'] = 'Create a backup of the files';
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['back'] = 'Go back';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['backupType'] = array("Type of backup", "Here you can select the type of backup.");
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['backupName'] = array("Filename", "Here you can enter an optional name for identification.");
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['filelist'] = array("Source files", "Please select a file or folder from the files directory.");

/**
 * Options
 */
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['option_full'] = 'Contao installation';
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['option_small'] = 'Personal data';

/**
 * List
 */
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['ok'] = "OK";
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['progress'] = "In Progress";
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['error'] = "Error";
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['skipped'] = "Skipped";

$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['step1'] = "Step 1";
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['step2'] = "Step 2";
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['step3'] = "Step 3";
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['step4'] = "Step 4";
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['complete'] = "Done!";

$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['step1_help'] = "Create zip file.";
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['step2_help'] = "Save contao installation.";
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['step3_help'] = "Save tl_files.";
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['complete_help'] = "Create file backup";
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['download_backup'] = "Download file backup.";
?>