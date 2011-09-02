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
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['sync_legend'] = "Synchronisations-Einstellungen";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['confirm_db_import_legend'] = "Datenbank-Einstellungen";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['edit'] = 'Synchronisation des Servers';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['sync_type'] = array("Art der Synchronisation", "Hier können Sie die Art der Synchronisation auswählen.");
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['confirm_db_import'] = array("Datenbank importieren", "Hier können Sie definieren ob ein Datenbank-Import erfolgen soll.");

/**
 * List
 */
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step1_help'] = "Server überprüfen.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step1_msg1'] = "Server für die Synchronsisation vorbereiten.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step1_msg2'] = "Versionskonflikt.";

$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step2_help'] = "MD5-Checkliste überprüfen und transferieren.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step2_msg1'] = "Es wurden %s neue und %s veränderte Datei(en) gefunden.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step2_msg2'] = "tl_files überprüfen.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step2_msg3'] = "Contao-Installation überprüfen.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step2_msg4'] = "Dateien erfolgreich überprüft. Lade Vergleichsliste.";

$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step3_help'] = "SQL-Scripte erstellen und transferieren.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step3_msg1'] = "SQL-Scripte importieren.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step3_msg2'] = "ZIP-Datei mit SQL-Scripten erstellen.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step3_msg3'] = "SQL-Backup erstellen.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step3_msg4'] = "ZIP-Datei überprüfen.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step3_msg5'] = "ZIP-Datei transferieren.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step3_msg6'] = "Datei %s erfolgreich zum Server transferiert und importiert.";

$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step4_help'] = "Große Dateien synchronisieren.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step4_msg1'] = "Große Dateien suchen.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step4_msg2'] = "Große Dateien transferieren.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step4_msg3'] = "%s von %s großen Datei(en) transferiert.";

$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step5_help'] = "Dateien synchronisieren.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step5_msg1'] = "Dateien transferieren.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step5_msg2'] = "Upload nicht erfolgreich. Funktion in der php.ini ist deaktiviert.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step5_msg3'] = "%s von %s Datei(en) transferiert.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step5_msg4'] = " Datei(en) übersprungen.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['step5_msg5'] = "Dateien importieren.";

$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['complete_help'] = "Server erfolgreich synchronisiert.";

?>