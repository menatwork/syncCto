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
 * @copyright  MEN AT WORK 2012
 * @package    Language
 * @license    GNU/LGPL 
 * @filesource
 */

/**
 * Reference
 */
// Sync
$GLOBALS['TL_LANG']['SYC']['core']                          = 'Contao-Installation';
$GLOBALS['TL_LANG']['SYC']['core_change']                   = 'Veränderte Dateien';
$GLOBALS['TL_LANG']['SYC']['core_delete']                   = 'Gelöschte Dateien';
$GLOBALS['TL_LANG']['SYC']['user']                          = 'Persönliche Dateien (tl_files)';
$GLOBALS['TL_LANG']['SYC']['user_change']                   = 'Veränderte Dateien';
$GLOBALS['TL_LANG']['SYC']['user_delete']                   = 'Gelöschte Dateien';
$GLOBALS['TL_LANG']['SYC']['configfiles']                   = 'Konfigurationsdateien';
$GLOBALS['TL_LANG']['SYC']['localconfig_update']            = 'Alle Einträge aktualisieren';
$GLOBALS['TL_LANG']['SYC']['localconfig_errors']            = 'Fehlermeldungen anzeigen';
// Maintenance options  
$GLOBALS['TL_LANG']['SYC']['options']                       = 'Optionen';    
$GLOBALS['TL_LANG']['SYC']['search_index']                  = 'Suchindex neu aufbauen';    
$GLOBALS['TL_LANG']['SYC']['temp_tables']                   = 'Temporäre DB-Tabellen leeren';    
$GLOBALS['TL_LANG']['SYC']['temp_folders']                  = 'Temporäre Ordner leeren';    
$GLOBALS['TL_LANG']['SYC']['css_create']                    = 'CSS-Dateien neu erstellen';    
$GLOBALS['TL_LANG']['SYC']['xml_create']                    = 'XML-Dateien neu erstellen';   

/**
 * Back end modules
 */
$GLOBALS['TL_LANG']['MSC']['edit']                          = 'Bearbeiten';
$GLOBALS['TL_LANG']['MSC']['copy']                          = 'Duplizieren';
$GLOBALS['TL_LANG']['MSC']['syncTo']                        = 'Client synchronisieren';
$GLOBALS['TL_LANG']['MSC']['syncFrom']                      = 'Server synchronisieren';

$GLOBALS['TL_LANG']['MSC']['restore_backup']                = 'Backup einspielen';
$GLOBALS['TL_LANG']['MSC']['start_backup']                  = 'Backup starten';

/**
 * Text
 */
$GLOBALS['TL_LANG']['MSC']['ok']                            = 'OK';
$GLOBALS['TL_LANG']['MSC']['progress']                      = 'In Bearbeitung';
$GLOBALS['TL_LANG']['MSC']['error']                         = 'Fehler';
$GLOBALS['TL_LANG']['MSC']['skipped']                       = 'Übersprungen';
$GLOBALS['TL_LANG']['MSC']['skip']                          = 'Überspringen';
$GLOBALS['TL_LANG']['MSC']['popup']                         = 'Vergleichsliste öffnen';
$GLOBALS['TL_LANG']['MSC']['unknown_step']                  = 'Unbekannter Schritt';

/**
 * Headline
 */
$GLOBALS['TL_LANG']['MSC']['step']                          = 'Schritt';
$GLOBALS['TL_LANG']['MSC']['abort']                         = 'Abbruch!';
$GLOBALS['TL_LANG']['MSC']['complete']                      = 'Fertig!';
$GLOBALS['TL_LANG']['MSC']['comparelist']                   = 'Vergleichsliste';
$GLOBALS['TL_LANG']['MSC']['debug_mode']                    = 'Debugausgaben';

/**
 * Filelist
 */
$GLOBALS['TL_LANG']['MSC']['select_all_files']              = 'Alle Dateien auswählen';
$GLOBALS['TL_LANG']['MSC']['sync_info']                     = 'Bitte schließen Sie während der gesamten Synchronisation nicht das aktuelle Fenster.';
$GLOBALS['TL_LANG']['MSC']['state']                         = 'Status';
$GLOBALS['TL_LANG']['MSC']['filesize']                      = 'Dateigröße';
$GLOBALS['TL_LANG']['MSC']['file']                          = 'Datei';
$GLOBALS['TL_LANG']['MSC']['totalsize']                     = 'Größe aller Dateien:';
$GLOBALS['TL_LANG']['MSC']['new_file']                      = 'Neu';
$GLOBALS['TL_LANG']['MSC']['modified_file']                 = 'Verändert';
$GLOBALS['TL_LANG']['MSC']['unknown_file']                  = 'Unbekannt';
$GLOBALS['TL_LANG']['MSC']['deleted_file']                  = 'Gelöscht';
$GLOBALS['TL_LANG']['MSC']['big_files']                     = 'Groß';
$GLOBALS['TL_LANG']['MSC']['skipped_files']                 = 'Übersprungen';
$GLOBALS['TL_LANG']['MSC']['ignored_files']                 = 'Ignoriert';
$GLOBALS['TL_LANG']['MSC']['submit_files']                  = 'Dateien transferieren';
$GLOBALS['TL_LANG']['MSC']['delete_files']                  = 'Ausgewählte Dateien aus der Liste entfernen';
$GLOBALS['TL_LANG']['MSC']['last_sync']                     = 'Die letzte Synchronisierung wurde um %s am %s von %s (%s) durchgeführt.';
$GLOBALS['TL_LANG']['MSC']['disabled_cache']                = 'Aktuelle Einträge in der "initconfig.php" beeinträchtigen eine erfolgreiche Synchronisierung.';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['MSC']['abort_sync']                    = array('Abbrechen', 'In Arbeit');
$GLOBALS['TL_LANG']['MSC']['repeat_sync']                   = array('Wiederholen', 'In Arbeit');

/**
 * Debug mode
 */
$GLOBALS['TL_LANG']['MSC']['run_time']                      = 'Laufzeit: %s Sekunden';
$GLOBALS['TL_LANG']['MSC']['memory_limit']                  = 'Auslastung: %s';

/**
 * Errors
 */
$GLOBALS['TL_LANG']['ERR']['missing_file_selection']        = 'Es wurde(n) keine Datei(en) ausgewählt.';
$GLOBALS['TL_LANG']['ERR']['missing_file_information']      = 'Es fehlt entweder eine Datei oder die Information zu einer Datei.';
$GLOBALS['TL_LANG']['ERR']['unknown_file']                  = 'Die Datei %s konnte nicht gefunden werden.';
$GLOBALS['TL_LANG']['ERR']['unknown_file_in_zip']           = 'Die Datei %s konnte nicht in der ZIP Datei gefunden werden.';
$GLOBALS['TL_LANG']['ERR']['unknown_path']                  = 'Unbekannter Pfad.';
$GLOBALS['TL_LANG']['ERR']['cant_open']                     = 'Datei %s kann nicht geöffnet werden.';
$GLOBALS['TL_LANG']['ERR']['checksum_error']                = 'Fehler in den Prüfsummen.';
$GLOBALS['TL_LANG']['ERR']['cant_move_file']                = 'Fehler beim verschieben der Datei %s nach %s.';
$GLOBALS['TL_LANG']['ERR']['cant_delete_file']              = 'Fehler beim Löschen der Datei.';
$GLOBALS['TL_LANG']['ERR']['attention_headline']            = 'Achtung bei Änderungen am System';
$GLOBALS['TL_LANG']['ERR']['attention_text']                = 'Es werden zurzeit Aktualisierungen im Hintergrund durchgeführt, die dazu führen könnten das Änderungen auf dieser Installation überschrieben werden.';
$GLOBALS['TL_LANG']['ERR']['min_size_limit']                = 'Die minimale Dateigröße von s% wurde unterschritten.';

// Database
$GLOBALS['TL_LANG']['ERR']['missing_tables_selection']      = 'Es wurde(n) keine Tabell(en) ausgewählt.';

// Functions
$GLOBALS['TL_LANG']['ERR']['unknown_backup_method']         = 'Die gewählte Backup Methode ist nicht bekannt.';
$GLOBALS['TL_LANG']['ERR']['unknown_function']              = 'Unbekannte Funktion oder Methode.';
$GLOBALS['TL_LANG']['ERR']['64Bit_error']                   = 'Zahlen überlauf. Versuchen Sie bitte einen 64Bit Version von PHP.';

// Client
$GLOBALS['TL_LANG']['ERR']['maximum_filesize']              = 'Zu große Datei(en):';
$GLOBALS['TL_LANG']['ERR']['call_directly']                 = 'Versuchen Sie nicht die Synchronisation direkt aufzurufen.';
$GLOBALS['TL_LANG']['ERR']['client_set']                    = 'Kommunikation zwischen Server und Client fehlgeschlagen';
$GLOBALS['TL_LANG']['ERR']['unknown_client']                = 'Unbekannter Client.';

$GLOBALS['TL_LANG']['ERR']['referer']                       = 'Konnte die Referer-Überprüfung des Clients nicht deaktivieren.';
$GLOBALS['TL_LANG']['ERR']['version']                       = 'Versionskonflikt in %s. <br />Server: %s <br />Client: %s';
$GLOBALS['TL_LANG']['ERR']['upload_ini']                    = 'Upload nicht erfolgreich. Funktion ist in der php.ini deaktiviert.';
$GLOBALS['TL_LANG']['ERR']['rebuild']                       = 'Fehler beim Zusammenbau der Datei(en). Pfad: %s';
$GLOBALS['TL_LANG']['ERR']['send']                          = 'Fehler beim Versand der Datei(en).';

?>