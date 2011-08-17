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
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['sync_legend'] = "Synchronisations-Einstellungen";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['filelist_legend'] = "Dateien und Ordner";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['table_recommend_legend'] = "Empfohlene Tabellen";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['table_none_recommend_legend'] = "Nicht empfohlene Tabellen";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['edit'] = 'Synchronisation des Clients';
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['back'] = 'Zurück';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['sync_type'] = array("Art der Synchronisation", "Hier können Sie die Art der Synchronisation auswählen.");
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['table_list_recommend'] = array("Empfohlene Tabellen", "Hier können Sie die empfohlenen Tabellen für die Synchronisation auswählen.");
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['table_list_none_recommend'] = array("Nicht empfohlene Tabellen", "Hier können die NICHT empfohlenen Tabellen für das Backup ausgewählt werden. Benutzung auf eigene Gefahr.");
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['filelist'] = array("Quelldateien", "Bitte wählen Sie eine Datei oder einen Ordner aus der Dateiübersicht.");

/**
 * List
 */
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step'] = "Schritt";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['complete'] = "Fertig!";

$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step1_help'] = "Client überprüfen.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step1_msg1'] = "Client für die Synchronisation vorbereiten.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step1_msg2'] = "Versionskonflikt.";

$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step2_help'] = "MD5-Checkliste überprüfen und transferieren.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step2_msg1'] = "Es wurden %s neue, %s veränderte, %s gelöschte und %s nicht zustellbare Datei(en) gefunden.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step2_msg2'] = "tl_files überprüfen.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step2_msg3'] = "Contao-Installation überprüfen.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step2_msg4'] = "Dateien erfolgreich überprüft. Lade Vergleichsliste.";

$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step3_help'] = "SQL-Scripte erstellen und transferieren.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step3_msg1'] = "SQL-Scripte importieren.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step3_msg2'] = "ZIP-Datei mit SQL-Scripten erstellen.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step3_msg3'] = "SQL-Backup erstellen.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step3_msg4'] = "ZIP-Datei überprüfen.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step3_msg5'] = "ZIP-Datei transferieren.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step3_msg6'] = "Datei %s erfolgreich zum Client transferiert und importiert.";

$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step4_help'] = "Große Dateien synchronisieren.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step4_msg1'] = "Große Dateien suchen.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step4_msg2'] = "Große Dateien transferieren.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step4_msg3'] = "%s von %s großen Datei(en) transferiert.";

$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step5_help'] = "Dateien synchronisieren.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step5_msg1'] = "Dateien transferieren.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step5_msg2'] = "Upload nicht erfolgreich. Funktion in der php.ini ist deaktiviert.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step5_msg3'] = "%s von %s Datei(en) synchronisiert.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step5_msg4'] = " Datei(en) übersprungen.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step5_msg5'] = "Dateien importieren.";
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step5_msg6'] = " Datei(en) übertragen.";

$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['complete_help'] = "Client erfolgreich synchronisiert.";

?>