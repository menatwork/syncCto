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
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['table_recommend_legend'] = "Recommended tables";
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['table_none_recommend_legend'] = "Not recommended tables";
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['edit'] = 'Create a backup of the database';
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['back'] = 'Go back';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['table_list_recommend'] = array("Recommended tables", "Here you can select the recommended tables for backup.");
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['table_list_none_recommend'] = array("Not recommended tables", "Here you can select the not recommended tables for backup. Use at your own risk.");

/**
 * List
 */
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['ok'] = "OK";
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['progress'] = "In Progress";
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['error'] = "Error";
 
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['step1'] = "Step 1";
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['step2'] = "Step 2";
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['step3'] = "Step 3";
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['complete'] = "Done!";

$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['step1_help'] = "Create zip file.";
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['step2_help'] = "Create SQL scripts and syncCto inserts.";
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['step3_help'] = "Check zip file.";
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['complete_help'] = "Create database backup";
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['download_backup'] = "Download file backup.";

?>