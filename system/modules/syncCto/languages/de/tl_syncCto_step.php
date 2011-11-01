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

/** ----------------------------------------------------------------------------
 * Steps
 */

/**
 * Texte
 */
$GLOBALS['TL_LANG']['tl_syncCto_steps']['ok'] = "OK";
$GLOBALS['TL_LANG']['tl_syncCto_steps']['progress'] = "In Bearbeitung";
$GLOBALS['TL_LANG']['tl_syncCto_steps']['error'] = "Fehler";
$GLOBALS['TL_LANG']['tl_syncCto_steps']['skipped'] = "Übersprungen";
$GLOBALS['TL_LANG']['tl_syncCto_steps']['unknown_step'] = "Unbekannter Schritt";

/**
 * Headline
 */
$GLOBALS['TL_LANG']['tl_syncCto_steps']['step'] = "Schritt";
$GLOBALS['TL_LANG']['tl_syncCto_steps']['complete'] = "Fertig!";
$GLOBALS['TL_LANG']['tl_syncCto_steps']['debug_mode'] = "Debugausgaben";

/**
 * Debug mode
 */
$GLOBALS['TL_LANG']['tl_syncCto_steps']['run_time'] = "Laufzeit: %s Sekunden";
$GLOBALS['TL_LANG']['tl_syncCto_steps']['memory_limit'] = "Auslastung: %s";

/** ----------------------------------------------------------------------------
 * SyncTo & SyncFrom language arrays
 */

/**
 * Errors
 */
$GLOBALS['TL_LANG']['tl_syncCto_sync']['error']['call_directly'] = "Versuchen Sie nicht die Synchronisation direkt aufzurufen.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']['error']['client_set'] = "Konnte nicht Client setzten.";
// Step 1
$GLOBALS['TL_LANG']['tl_syncCto_sync']['error_step_1']['referer'] = "Konnte die Referer-Überprüfung auf Client Seite nicht deaktivieren.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']['error_step_1']['version'] = "Versionskonflikt. Server: %s Client: %s";
// Step 3
$GLOBALS['TL_LANG']['tl_syncCto_sync']['error_step_3']['upload_ini'] = "Upload nicht erfolgreich. Funktion ist in der php.ini deaktiviert.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']['error_step_3']['rebuild'] = "Fehler beim zusammenbauen der Dateien auf der Clientseite. Pfad: %s";
// Step 4
$GLOBALS['TL_LANG']['tl_syncCto_sync']['error_step_4']['send'] = "Fehler beim senden der SQL Datei.";
// Step 5
$GLOBALS['TL_LANG']['tl_syncCto_sync']['error_step_5']['upload_ini'] = "Upload nicht erfolgreich. Funktion ist in der php.ini deaktiviert.";

/**
 * List
 */
// Step 1
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_1'] = "Client vorbereitung.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_2'] = "Temporäre Ordner leeren.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_3'] = "Client vorbereitung";
// Step 2
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1'] = "Datenabgleich. Vergleichsliste erstellen.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_2'] = "Vergleichsliste zum Client schicken.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_3'] = "Nach löschbaren Dateien suchen.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_4'] = "Aufbereitung der Datenlisten.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_5'] = "Vergleichslisten";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_6'] = "Es wurden %s neue, %s veränderte, %s gelöschte und %s nicht zustellbare Datei(en) gefunden.";
// Step 3
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_1'] = "Große Dateien synchronisieren.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_2'] = "Große Dateien transferieren.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_3'] = "%s von %s großen Datei(en) zerteilt.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_4'] = "%s von %s großen Datei(en) gesendet.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_5'] = "Große Dateien zusammenbauen.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_6'] = "%s von %s großen Datei(en) zusammenbauen.";
// Step 4
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_4"]['description_1'] = "SQL-Scripte erstellen.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_4"]['description_2'] = "SQL-Scripte senden.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_4"]['description_3'] = "SQL-Scripte importieren.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_4"]['description_4'] = "SQL-Scripte import.";
// Step 5
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_1'] = "Dateien synchronisieren.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_2'] = "%s von %s Datei(en) synchronisiert.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_3'] = "Dateien importieren.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_4'] = "Dateien löschen.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_5'] = "Konfigurations Importieren.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_6'] = "Dateien synchronisieren.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_7'] = "%s von %s Datei(en) übertragen.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_8'] = " Datei(en) übersprungen.";
?>
