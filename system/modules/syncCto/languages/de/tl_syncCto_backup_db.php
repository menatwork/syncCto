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
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['table_recommend_legend'] = "Empfohlene Tabellen";
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['table_none_recommend_legend'] = "Nicht empfohlene Tabellen";
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['edit'] = 'Ein Backup der Datenbank erstellen';
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['back'] = 'Zurück';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['table_list_recommend'] = array("Empfohlene Tabellen", "Hier können Sie die empfohlenen Tabellen für das Backup auswählen.");
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['table_list_none_recommend'] = array("Nicht empfohlene Tabellen", "Hier können Sie die NICHT empfohlenen Tabellen für das Backup auswählen. Benutzung auf eigene Gefahr.");

/**
 * List
 */
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['ok'] = "OK";
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['progress'] = "In Bearbeitung";
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['error'] = "Fehler";
 
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['step1'] = "Schritt 1";
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['step2'] = "Schritt 2";
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['step3'] = "Schritt 3";
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['complete'] = "Fertig!";

$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['step1_help'] = "ZIP-Datei erstellen.";
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['step2_help'] = "SQL-Scripte und Inserts für syncCto erstellen.";
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['step3_help'] = "ZIP-Datei prüfen.";
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['complete_help'] = "Backup erstellt unter";
$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['download_backup'] = "Backup herunterladen.";

?>