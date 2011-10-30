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
 * Back end modules
 */

$GLOBALS['TL_LANG']['MSC']['edit'] = 'Bearbeiten';
$GLOBALS['TL_LANG']['MSC']['copy'] = 'Duplizieren';
$GLOBALS['TL_LANG']['MSC']['syncTo'] = 'Client synchronisieren';
$GLOBALS['TL_LANG']['MSC']['syncFrom'] = 'Server synchronisieren';

$GLOBALS['TL_LANG']['MSC']['restore_backup'] = 'Backup einspielen';
$GLOBALS['TL_LANG']['MSC']['start_backup'] = 'Backup starten';

$GLOBALS['TL_LANG']['SYC']['files'] = 'Datei-Synchronisation';
$GLOBALS['TL_LANG']['SYC']['backup'] = 'Backup-Kategorie';

$GLOBALS['TL_LANG']['SYC']['option_small'] = array('Persönliche Daten', 'Nur ausgewählte Dateien und Ordner innerhalb tl_files werden berücksichtigt.');
$GLOBALS['TL_LANG']['SYC']['option_full'] = array('Contao-Installation', 'Die komplette Contao-Installation, inklusive tl_files wird berücksichtigt.');

/** ----------------------------------------------------------------------------
 * Errors
 */

$GLOBALS['TL_LANG']['ERR']['sync_no_tables_select'] = "Es wurde(n) keine Tabell(en) ausgewählt.";
$GLOBALS['TL_LANG']['ERR']['sync_no_file_select'] = "Es wurde(n) keine Datei(en) ausgewählt.";
$GLOBALS['TL_LANG']['ERR']['sync_no_file_found'] = "Die Datei %s konnte nicht gefunden werden.";
$GLOBALS['TL_LANG']['ERR']['sync_unknown_backup_method'] = "Die gewählte Backup Methode ist nicht bekannt.";
$GLOBALS['TL_LANG']['ERR']['cant_open'] = "Datei %s kann nicht geöffnet werden.";
$GLOBALS['TL_LANG']['ERR']['file_not_exists'] = "Datei %s konnte nicht erstellt werden.";
$GLOBALS['TL_LANG']['ERR']['zero_tables'] = "Es wurden keine SQL-Tabellen für ein Backup gefunden.";
$GLOBALS['TL_LANG']['ERR']['table_dmg'] = "Die Datei für die SQL-Tabellen ist beschädigt.";
$GLOBALS['TL_LANG']['ERR']['insert_dmg'] = "Die Datei für den SQL-Inhalt ist beschädigt.";
$GLOBALS['TL_LANG']['ERR']['missing_table_file'] = "Die Datei für die SQL-Tabellen fehlt.";
$GLOBALS['TL_LANG']['ERR']['missing_insert_file'] = "Die Datei für die SQL-Inhalte fehlt.";
$GLOBALS['TL_LANG']['ERR']['reading_table_file'] = "Die SQL-Tabellen konnten nicht gelesen werden.";
$GLOBALS['TL_LANG']['ERR']['reading_insert_file'] = "Die SQL-Inhalte konnten nicht gelesen werden.";
$GLOBALS['TL_LANG']['ERR']['unknown_error'] = "Unbekannter Fehler.";
$GLOBALS['TL_LANG']['ERR']['unknown_function'] = "Unbekannte Funktion.";
$GLOBALS['TL_LANG']['ERR']['unknown_method'] = "Unbekannte Methode.";
$GLOBALS['TL_LANG']['ERR']['unknown_table'] = "Unbekannte Tabelle.";
$GLOBALS['TL_LANG']['ERR']['no_backup_tables'] = "Keine Tabellen für das Backup ausgewählt.";
$GLOBALS['TL_LANG']['ERR']['no_backup_file'] = "Keine Backupdatei ausgewählt.";
$GLOBALS['TL_LANG']['ERR']['session_file_error'] = "Die Dateiliste konnte nicht aus der Session wiederhergestellt werden.";
$GLOBALS['TL_LANG']['ERR']['restore_session_tables'] = "Die Tabellen konnten nicht aus der Session wiederhergestellt werden.";
$GLOBALS['TL_LANG']['ERR']['restore_session_zip_id'] = "Die ZIP-ID konnte nicht aus der Session wiederhergestellt werden.";
$GLOBALS['TL_LANG']['ERR']['restore_session_zip_name'] = "Der ZIP-Name konnte nicht aus der Session wiederhergestellt werden.";
$GLOBALS['TL_LANG']['ERR']['unknown_backup_step'] = "Unbekannter Schritt im Backup-Prozess.";
$GLOBALS['TL_LANG']['ERR']['unknown_backup_error'] = "Unbekannter Fehler im Backup-Prozess.";
$GLOBALS['TL_LANG']['ERR']['unknown_restore_error'] = "Unbekannter Fehler im Wiederherstellungsprozess.";
$GLOBALS['TL_LANG']['ERR']['maximum_filesize'] = "Zu große Datei(en):";
$GLOBALS['TL_LANG']['ERR']['uploaded_files_list'] = "Übertragene Datei(en):";
$GLOBALS['TL_LANG']['ERR']['deleted_files_list'] = "Gelöschte Datei(en):";
/* OK */$GLOBALS['TL_LANG']['ERR']['rpc_maximum_calls'] = "Maximale Versuche für Übertragung erreicht. Versuchen Sie es späer nocheinmal.";
/* OK */$GLOBALS['TL_LANG']['ERR']['rpc_maximum_logins'] = "Maximale Versuche für Anmeldung erreicht.";
$GLOBALS['TL_LANG']['ERR']['checksum_error'] = "Fehler in den Prüfsummen.";
$GLOBALS['TL_LANG']['ERR']['upload_move_error'] = "Fehler beim Speichern.";
$GLOBALS['TL_LANG']['ERR']['unknown_response'] = "Unbekannte Antwort.";
/* OK */$GLOBALS['TL_LANG']['ERR']['rpc_data_missing'] = "Fehlende Daten für RPC.";
/* OK */$GLOBALS['TL_LANG']['ERR']['rpc_unknown'] = "Unbekannte RPC Anfrage.";

/** ----------------------------------------------------------------------------
 * Filelist
 */

$GLOBALS['TL_LANG']['MSC']['select_all_files'] = "Alle auswählen";
$GLOBALS['TL_LANG']['MSC']['sync_info'] = "Bitte schließen Sie während der gesamten Synchronisation nicht das aktuelle Fenster.";

$GLOBALS['TL_LANG']['MSC']['totalsize'] = 'Größe aller Dateien:';
$GLOBALS['TL_LANG']['MSC']['new_file'] = 'Neu';
$GLOBALS['TL_LANG']['MSC']['modified_file'] = 'Verändert';
$GLOBALS['TL_LANG']['MSC']['unknown_file'] = 'Unbekannt';
$GLOBALS['TL_LANG']['MSC']['deleted_file'] = 'Gelöscht';
$GLOBALS['TL_LANG']['MSC']['submit_files'] = 'Alle Dateien transferieren';
$GLOBALS['TL_LANG']['MSC']['delete_files'] = 'Ausgewählte Dateien entfernen';

$GLOBALS['TL_LANG']['MSC']['big_files'] = 'Datei(en) zum Übertragen.';
$GLOBALS['TL_LANG']['MSC']['skipped_files'] = 'Folgende Datei(en) sind zu groß um sie zu übertragen.';
$GLOBALS['TL_LANG']['MSC']['ignored_files'] = 'Folgende Datei(en) sind zu groß um sie zu bearbeiten.';

$GLOBALS['TL_LANG']['MSC']['information_last_sync'] = 'Die letzte Synchronisierung wurde um %s am %s vom User %s (%s) durchgeführt.';

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