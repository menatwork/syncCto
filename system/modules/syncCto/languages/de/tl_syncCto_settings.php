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
$GLOBALS['TL_LANG']['tl_syncCto_settings']['edit'] = 'Die syncCto Konfiguration bearbeiten';
$GLOBALS['TL_LANG']['tl_syncCto_settings']['blacklist_legend'] = "Blacklist für Dateien und Ordner";
$GLOBALS['TL_LANG']['tl_syncCto_settings']['local_blacklist_legend'] = "Blacklist für localconfig.php Einträge";
$GLOBALS['TL_LANG']['tl_syncCto_settings']['whitelist_legend'] = "Whitelist für Root-Ordner";
$GLOBALS['TL_LANG']['tl_syncCto_settings']['hidden_tables_legend'] = "Versteckte Tabellen";
$GLOBALS['TL_LANG']['tl_syncCto_settings']['tables_legend'] = "Nicht empfohlene Tabellen";
$GLOBALS['TL_LANG']['tl_syncCto_settings']['security_legend'] = "Verschlüsselung";
$GLOBALS['TL_LANG']['tl_syncCto_settings']['debug_legend'] = "Debugmodus";

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_syncCto_settings']['folder_blacklist'] = array("Ordner-Blacklist", "Hier können Sie definieren welche Ordner bei der Synchronisation ignoriert werden sollen.");
$GLOBALS['TL_LANG']['tl_syncCto_settings']['file_blacklist'] = array("Datei-Blacklist", "Hier können Sie definieren welche Dateien bei der Synchronisation ignoriert werden sollen.");
$GLOBALS['TL_LANG']['tl_syncCto_settings']['folder_whitelist'] = array("Whitelist für Root-Ordner", "Hier können Sie definieren welche Root-Ordner bei der Synchronisation beachtet werden sollen.");
$GLOBALS['TL_LANG']['tl_syncCto_settings']['local_blacklist'] = array("localconfig.php", "Hier können Sie definieren welche localconfig.php Einträge nicht synchronisiert werden sollen.");
$GLOBALS['TL_LANG']['tl_syncCto_settings']['table_list'] = array("Nicht empfohlene Tabellen", "Hier können Sie definieren welche Datenbank-Tabellen Sie nicht für die Synchronisation empfehlen.");
$GLOBALS['TL_LANG']['tl_syncCto_settings']['table_hidden'] = array("Versteckte Tabellen", "Hier können Sie den Zugriff auf eine oder mehrere Datenbank-Tabellen für die Synchronisation festlegen.");
$GLOBALS['TL_LANG']['tl_syncCto_settings']['seckey'] = array("Verschlüsselungsschlüssel", "Der Schlüssel wird zur verschlüsselten Datenspeicherung verwendet.");
$GLOBALS['TL_LANG']['tl_syncCto_settings']['debug_mode'] = array("Debugmodus aktivieren", "Informationen zur Laufzeit und den übertragenen Dateien auf dem Bildschirm ausgeben.");
?>