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
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['backup_legend'] = "Backup-Einstellungen";
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['filelist_legend'] = "Dateien und Ordner";
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['edit'] = 'Ein Backup der Dateien erstellen';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['backup_type'] = array("Art des Backups", "Hier können Sie die Art des Backups auswählen.");
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['backup_name'] = array("Dateiname", "Hier können Sie einen optionalen Namen zur eindeutigen Identifikation eingeben.");
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['filelist'] = array("Quelldateien", "Bitte wählen Sie eine Datei oder einen Ordner aus der Dateiübersicht.");

/**
 * Options
 */
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['option_full'] = 'Contao-Installation';
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['option_small'] = 'Persönliche Daten';

/**
 * List
 */
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['step1_help'] = "ZIP-Datei erstellen.";
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['step2_help'] = "Contao-Installation sichern.";
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['step3_help'] = "tl_files sichern.";
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['complete_help'] = "Backup erstellt unter";
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['download_backup'] = "Backup herunterladen.";

?>