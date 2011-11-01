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
$GLOBALS['TL_LANG']['tl_syncCto_sync']['error']['client_set'] = "Kommunikation zwischen Server und Client fehlgeschlagen";
$GLOBALS['TL_LANG']['tl_syncCto_sync']['error']['referer'] = "Konnte die Referrer-Überprüfung des Clients nicht deaktivieren.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']['error']['version'] = "Versionskonflikt. Server: %s Client: %s";
$GLOBALS['TL_LANG']['tl_syncCto_sync']['error']['upload_ini'] = "Upload nicht erfolgreich. Funktion ist in der php.ini deaktiviert.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']['error']['rebuild'] = "Fehler beim Zusammenbau der Dateien. Pfad: %s";
$GLOBALS['TL_LANG']['tl_syncCto_sync']['error']['send'] = "Fehler beim Versand der Datei(en).";

/**
 * List
 */
// Step 1
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_1'] = "Vorbereitung des Clients.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_2'] = "Temporäre Ordner leeren.";

// Step 2
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1'] = "Abgleich und Versand der Vergleichslisten.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_2'] = "Nach löschbaren Dateien suchen.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_3'] = "Aufbereitung der Vergleichsliste.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_4'] = "Es wurden %s neue, %s veränderte, %s gelöschte und %s nicht zustellbare Datei(en) gefunden.";

// Step 3
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_1'] = "Große Dateien transferieren.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_2'] = "%s von %s großen Datei(en) verarbeitet.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_3'] = "Große Dateien zusammenbauen.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_4'] = "%s von %s großen Datei(en) wurden erfolgreich verarbeitet.";

// Step 4
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_4"]['description_1'] = "SQL-Scripte erstellen.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_4"]['description_2'] = "SQL-Scripte transferieren.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_4"]['description_3'] = "SQL-Scripte importieren.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_4"]['description_4'] = "SQL-Scripte wurden erfolgreich verarbeitet.";

// Step 5
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_1'] = "Dateien verarbeiten.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_2'] = "%s von %s Datei(en) transferiert.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_3'] = "Konfigurationsdateien importieren.";
$GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_4'] = " Datei(en) übersprungen.";

?>
