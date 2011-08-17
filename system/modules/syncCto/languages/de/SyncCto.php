<?php

if (!defined('TL_ROOT'))
    die('You can not access this file directly!');

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
 * Backups
 */
$GLOBALS['TL_LANG']['syncCto']['welcome_backup'] = "Backups verwalten";
$GLOBALS['TL_LANG']['syncCto']['title_make_backup'] = "Backups erstellen";
$GLOBALS['TL_LANG']['syncCto']['title_import_backup'] = "Backups einspielen";
$GLOBALS['TL_LANG']['syncCto']['db_make_backup'] = array("Datenbank Backup erstellen", "Hier können Sie ein Backup der Datenbank erstellen.");
$GLOBALS['TL_LANG']['syncCto']['file_make_backup'] = array("Datei Backup erstellen", "Hier können Sie ein Backup der Dateien erstellen.");
$GLOBALS['TL_LANG']['syncCto']['db_import_backup'] = array("Datenbank Backup einspielen", "Hier können Sie ein Backup der Datenbank wieder einspielen.");
$GLOBALS['TL_LANG']['syncCto']['file_import_backup'] = array("Datei Backup einspielen", "Hier können Sie ein Backup der Dateien wieder einspielen.");

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['syncCto']['start_backup'] = "Backup starten";
$GLOBALS['TL_LANG']['syncCto']['restore_backup'] = "Backup einspielen";
$GLOBALS['TL_LANG']['syncCto']['sync_client'] = "Client synchronisieren";
$GLOBALS['TL_LANG']['syncCto']['sync_server'] = "Server synchronisieren";

/**
 * Headline
 */
$GLOBALS['TL_LANG']['syncCto']['check'] = "Systemcheck";
$GLOBALS['TL_LANG']['syncCto']['configuration'] = "PHP Konfiguration";

/**
 * Table
 */
$GLOBALS['TL_LANG']['syncCto']['parameter'] = "Parameter";
$GLOBALS['TL_LANG']['syncCto']['value'] = "Wert";
$GLOBALS['TL_LANG']['syncCto']['description'] = "Beschreibung";
$GLOBALS['TL_LANG']['syncCto']['on'] = "An";
$GLOBALS['TL_LANG']['syncCto']['off'] = "Aus";
$GLOBALS['TL_LANG']['syncCto']['safemode'] = array("Safe mode", "Die empfohlene Einstellung ist Aus.");
$GLOBALS['TL_LANG']['syncCto']['met'] = array("Maximum execution time", "Die empfohlene Einstellung ist 30 oder höher.");
$GLOBALS['TL_LANG']['syncCto']['memory_limit'] = array("Memory limit", "Die empfohlene Einstellung ist 128M oder höher.");
$GLOBALS['TL_LANG']['syncCto']['register_globals'] = array("Register globals", "Die empfohlene Einstellung ist Aus.");
$GLOBALS['TL_LANG']['syncCto']['file_uploads'] = array("File uploads", "Die empfohlene Einstellung ist An.");
$GLOBALS['TL_LANG']['syncCto']['umf'] = array("Upload maximum filesize", "Die empfohlene Einstellung ist 8M oder höher.");
$GLOBALS['TL_LANG']['syncCto']['pms'] = array("Post maximum size", "Die empfohlene Einstellung ist 8M oder höher.");
$GLOBALS['TL_LANG']['syncCto']['mit'] = array("Maximum input time", "Die empfohlene Einstellung ist -1, 60 oder höher.");
$GLOBALS['TL_LANG']['syncCto']['dst'] = array("Default socket timeout", "Die empfohlene Einstellung ist 30 oder höher.");
$GLOBALS['TL_LANG']['syncCto']['fsocket'] = array("Fsockopen", "Die empfohlene Einstellung ist An.");
$GLOBALS['TL_LANG']['syncCto']['fopen'] = array("Fopen", "Die empfohlene Einstellung ist An.");
$GLOBALS['TL_LANG']['syncCto']['zip_archive'] = array("ZipArchive", "Die empfohlene Einstellung ist An.");

/**
 * Text
 */
$GLOBALS['TL_LANG']['syncCto']['other_sync_issues'] = "Weitere Problemfälle";
$GLOBALS['TL_LANG']['syncCto']['explanation_sync_issues'] = "In machen Fällen kann eine Synchronisation trotz eines guten Systemchecks nicht ausgeführt werden.";
$GLOBALS['TL_LANG']['syncCto']['known_issues'] = "Folgende fehlerhafte Konfigurationen sind bekannt:";
$GLOBALS['TL_LANG']['syncCto']['suhosin'] = "Suhosin verhindert die Synchronisation";
$GLOBALS['TL_LANG']['syncCto']['max_request_len'] = "Der MaxRequestLen ist zu gering";

$GLOBALS['TL_LANG']['syncCto']['safemodehack'] = 'syncCto kann aufgrund fehlender Schreibrechte nicht ausgeführt werden.';

$GLOBALS['TL_LANG']['syncCto']['ok'] = "OK";
$GLOBALS['TL_LANG']['syncCto']['progress'] = "In Bearbeitung";
$GLOBALS['TL_LANG']['syncCto']['error'] = "Fehler";
$GLOBALS['TL_LANG']['syncCto']['skipped'] = "Übersprungen";
$GLOBALS['TL_LANG']['syncCto']['select_all_files'] = "Alle auswählen";
$GLOBALS['TL_LANG']['syncCto']['sync_info'] = "Bitte schließen Sie während der gesamten Synchronisation nicht das aktuelle Fenster.";
$GLOBALS['TL_LANG']['syncCto']['run_time'] = "Laufzeit: %s Sekunden";

$GLOBALS['TL_LANG']['syncCto']['size'] = 'Größe aller Dateien:';
$GLOBALS['TL_LANG']['syncCto']['new_file'] = 'Neu';
$GLOBALS['TL_LANG']['syncCto']['modified_file'] = 'Verändert';
$GLOBALS['TL_LANG']['syncCto']['unknown_file'] = 'Unbekannt';
$GLOBALS['TL_LANG']['syncCto']['deleted_file'] = 'Gelöscht';
$GLOBALS['TL_LANG']['syncCto']['submit_files'] = 'Dateien transferieren';
$GLOBALS['TL_LANG']['syncCto']['delete_files'] = 'Dateien entfernen';

$GLOBALS['TL_LANG']['syncCto']['big_files'] = 'Datei(en) zum übertragen.';
$GLOBALS['TL_LANG']['syncCto']['skipped_files'] = 'Folgende Datei(en) sind zu groß um sie zu übertragen.';
$GLOBALS['TL_LANG']['syncCto']['ignored_files'] = 'Folgende( Datei(en) sind zu groß um sie zu bearbeiten.';

$GLOBALS['TL_LANG']['syncCto']['information_last_sync'] = 'Die letzte Synchronisierung wurde um %s am %s vom User %s (%s) durchgeführt.';

/**
 * Title attributes
 */
$GLOBALS['TL_LANG']['syncCto']['server_online'] = "Client ID %s ist online";
$GLOBALS['TL_LANG']['syncCto']['server_missing'] = "syncCto fehlt auf dem Client ID %s";
$GLOBALS['TL_LANG']['syncCto']['server_offline'] = "Client ID %s ist offline";

/**
 * Codifyengines
 */
$GLOBALS['TL_LANG']['syncCto']['codifyengine_name']["Empty"] = "Keine verschlüsselung"; 
$GLOBALS['TL_LANG']['syncCto']['codifyengine_name']["Blow"] = "Blowfish"; 
$GLOBALS['TL_LANG']['syncCto']['codifyengine_name']["Mcrypt"] = "Mcrypt"; 

/**
 * Errors
 */
$GLOBALS['TL_LANG']['syncCto']['cant_open'] = "Datei %s kann nicht geöffnet werden.";
$GLOBALS['TL_LANG']['syncCto']['file_not_exists'] = "Datei %s konnte nicht erstellt werden.";
$GLOBALS['TL_LANG']['syncCto']['zero_tables'] = "Es wurden keine SQL-Tabellen für ein Backup gefunden.";
$GLOBALS['TL_LANG']['syncCto']['table_dmg'] = "Die Datei für die SQL-Tabellen ist beschädigt.";
$GLOBALS['TL_LANG']['syncCto']['insert_dmg'] = "Die Datei für den SQL-Inhalt ist beschädigt.";
$GLOBALS['TL_LANG']['syncCto']['missing_table_file'] = "Die Datei für die SQL-Tabellen fehlt.";
$GLOBALS['TL_LANG']['syncCto']['missing_insert_file'] = "Die Datei für die SQL-Inhalte fehlt.";
$GLOBALS['TL_LANG']['syncCto']['reading_table_file'] = "Die SQL-Tabellen konnten nicht gelesen werden.";
$GLOBALS['TL_LANG']['syncCto']['reading_insert_file'] = "Die SQL-Inhalte konnten nicht gelesen werden.";
$GLOBALS['TL_LANG']['syncCto']['unknown_error'] = "Unbekannter Fehler.";
$GLOBALS['TL_LANG']['syncCto']['unknown_function'] = "Unbekannte Funktion.";
$GLOBALS['TL_LANG']['syncCto']['unknown_method'] = "Unbekannte Methode.";
$GLOBALS['TL_LANG']['syncCto']['unknown_table'] = "Unbekannte Tabelle.";
$GLOBALS['TL_LANG']['syncCto']['no_backup_tables'] = "Keine Tabellen für das Backup ausgewählt.";
$GLOBALS['TL_LANG']['syncCto']['no_backup_file'] = "Keine Backupdatei ausgewählt.";
$GLOBALS['TL_LANG']['syncCto']['session_file_error'] = "Die Dateiliste konnte nicht aus der Session wiederhergestellt werden.";
$GLOBALS['TL_LANG']['syncCto']['restore_session_tables'] = "Die Tabellen konnten nicht aus der Session wiederhergestellt werden.";
$GLOBALS['TL_LANG']['syncCto']['restore_session_zip_id'] = "Die ZIP-ID konnte nicht aus der Session wiederhergestellt werden.";
$GLOBALS['TL_LANG']['syncCto']['restore_session_zip_name'] = "Der ZIP-Name konnte nicht aus der Session wiederhergestellt werden.";
$GLOBALS['TL_LANG']['syncCto']['unknown_backup_step'] = "Unbekannter Schritt im Backup-Prozess.";
$GLOBALS['TL_LANG']['syncCto']['unknown_backup_error'] = "Unbekannter Fehler im Backup-Prozess.";
$GLOBALS['TL_LANG']['syncCto']['unknown_restore_error'] = "Unbekannter Fehler im Wiederherstellungsprozess.";
$GLOBALS['TL_LANG']['syncCto']['maximum_filesize'] = "Zu große Datei(en):";
$GLOBALS['TL_LANG']['syncCto']['uploaded_files_list'] = "Übertragene Datei(en):";
$GLOBALS['TL_LANG']['syncCto']['deleted_files_list'] = "Gelöschte Datei(en):";
/* OK */$GLOBALS['TL_LANG']['syncCto']['rpc_maximum_calls'] = "Maximale Versuche für Übertragung erreicht. Versuchen Sie es späer nocheinmal.";
/* OK */$GLOBALS['TL_LANG']['syncCto']['rpc_maximum_logins'] = "Maximale Versuche für Anmeldung erreicht.";
$GLOBALS['TL_LANG']['syncCto']['checksum_error'] = "Fehler in den Prüfsummen.";
$GLOBALS['TL_LANG']['syncCto']['upload_move_error'] = "Fehler beim Speichern.";
$GLOBALS['TL_LANG']['syncCto']['unknown_response'] = "Unbekannte Antwort.";
/* DE */$GLOBALS['TL_LANG']['syncCto']['rpc_call_missing'] = "Fehlende RPC ID.";
/* OK */$GLOBALS['TL_LANG']['syncCto']['rpc_data_missing'] = "Fehlende Daten für RPC.";
/* OK */$GLOBALS['TL_LANG']['syncCto']['rpc_unknown'] = "Unbekannte RPC Anfrage.";
/* NE */$GLOBALS['TL_LANG']['syncCto']['rpc_unknown_exception'] = "Unbekannter Fehler.";
/* NE */$GLOBALS['TL_LANG']['syncCto']['rpc_missing_starttag'] = "Nicht verwertbare Antwort vom Client erhalten.";
/* NE */$GLOBALS['TL_LANG']['syncCto']['rpc_missing_endtag'] = "Nicht verwertbare Antwort vom Client erhalten.";
/* NE */$GLOBALS['TL_LANG']['syncCto']['rpc_answer_no_array'] = "Atwort vom Client ist kein Array.";
/* NE */$GLOBALS['TL_LANG']['syncCto']['codifyengine_unknown'] = "Konnte die angegebene Verschlüsselungs-Engine nicht finden: %s";


?>