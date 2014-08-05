<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL 
 * @filesource
 */

$GLOBALS['TL_LANG']['ERR']['64Bit_error']                = 'Zahlenüberlauf. Versuchen Sie bitte einen 64Bit Version von PHP.';
$GLOBALS['TL_LANG']['ERR']['attention_headline']         = 'Achtung bei Änderungen am System';
$GLOBALS['TL_LANG']['ERR']['attention_text']             = 'Contao befindet sich momentan im <strong>syncCto Wartungsmodus</strong>. In der Master-Installation werden Änderungen durchgeführt, die Ihre Änderungen überschreiben könnten. Diese Meldung kann nur durch eine erneute Synchronisation durch die Master-Installation entfernt werden.';
$GLOBALS['TL_LANG']['ERR']['call_directly']              = 'Versuchen Sie nicht die Synchronisation direkt aufzurufen.';
$GLOBALS['TL_LANG']['ERR']['cant_delete_file']           = 'Fehler beim Löschen der Datei.';
$GLOBALS['TL_LANG']['ERR']['cant_extract_file']          = 'Fehler beim Entpacken der Dateien.';
$GLOBALS['TL_LANG']['ERR']['cant_move_file']             = 'Fehler beim Verschieben der Datei %s nach %s.';
$GLOBALS['TL_LANG']['ERR']['cant_move_files']            = 'Die Dateien kann nicht verschoben werden.';
$GLOBALS['TL_LANG']['ERR']['cant_open']                  = 'Datei %s kann nicht geöffnet werden.';
$GLOBALS['TL_LANG']['ERR']['checksum_error']             = 'Fehler in den Prüfsummen.';
$GLOBALS['TL_LANG']['ERR']['client_set']                 = 'Kommunikation zwischen Server und Client fehlgeschlagen';
$GLOBALS['TL_LANG']['ERR']['dbafs_error']                = 'Es sind Probleme im DBAFS aufgetreten:';
$GLOBALS['TL_LANG']['ERR']['dbafs_uuid_conflict']        = 'Konflikt im DBFAS von Contao. Die Originaldatei wurde umbenannt.';
$GLOBALS['TL_LANG']['ERR']['dbafs_uuid_conflict_rename'] = 'Konflikt im DBFAS von Contao. Die Originaldatei wurde in _%s umbenannt.';
$GLOBALS['TL_LANG']['ERR']['maximum_filesize']           = 'Zu große Datei(en):';
$GLOBALS['TL_LANG']['ERR']['min_size_limit']             = 'Die minimale Dateigröße von %s wurde unterschritten.';
$GLOBALS['TL_LANG']['ERR']['missing_file_folder']        = 'Datei/Ordner "%s" nicht vorhanden.';
$GLOBALS['TL_LANG']['ERR']['missing_file_information']   = 'Es fehlt entweder eine Datei oder die Information zu einer Datei.';
$GLOBALS['TL_LANG']['ERR']['missing_file_selection']     = 'Es wurde(n) keine Datei(en) ausgewählt.';
$GLOBALS['TL_LANG']['ERR']['missing_tables']             = 'Es wurde(n) keine Tabell(en) gefunden.';
$GLOBALS['TL_LANG']['ERR']['no_functions']               = 'Es wurden keine Optionen für die Synchronisation ausgewählt.';
$GLOBALS['TL_LANG']['ERR']['rebuild']                    = 'Fehler beim Zusammenbau der Datei(en). Pfad: %s';
$GLOBALS['TL_LANG']['ERR']['referer']                    = 'Konnte die Referer-Überprüfung des Clients nicht deaktivieren.';
$GLOBALS['TL_LANG']['ERR']['unknown_client']             = 'Unbekannter Client.';
$GLOBALS['TL_LANG']['ERR']['unknown_file']               = 'Die Datei %s konnte nicht gefunden werden.';
$GLOBALS['TL_LANG']['ERR']['unknown_function']           = 'Unbekannte Funktion oder Methode.';
$GLOBALS['TL_LANG']['ERR']['unknown_path']               = 'Unbekannter Pfad.';
$GLOBALS['TL_LANG']['ERR']['upload_ini']                 = 'Upload nicht erfolgreich. Funktion ist in der php.ini deaktiviert.';
$GLOBALS['TL_LANG']['ERR']['version']                    = 'Versionskonflikt in %s. <br />Server: %s <br />Client: %s';
$GLOBALS['TL_LANG']['MSC']['abort']                      = 'Abbruch!';
$GLOBALS['TL_LANG']['MSC']['abort_sync']['0']            = 'Abbrechen';
$GLOBALS['TL_LANG']['MSC']['abort_sync']['1']            = 'In Arbeit';
$GLOBALS['TL_LANG']['MSC']['big_files']                  = 'Große Dateien';
$GLOBALS['TL_LANG']['MSC']['both_changed']               = 'Beide geändert';
$GLOBALS['TL_LANG']['MSC']['changed']                    = 'Geändert';
$GLOBALS['TL_LANG']['MSC']['client']                     = 'Client';
$GLOBALS['TL_LANG']['MSC']['complete']                   = 'Fertig!';
$GLOBALS['TL_LANG']['MSC']['copy']                       = 'Duplizieren';
$GLOBALS['TL_LANG']['MSC']['dbafs_all_green']            = 'Der Import ins DBAFS konnte ohne Probleme durchgeführt werden.';
$GLOBALS['TL_LANG']['MSC']['dbafs_conflict']             = 'Konflikt';
$GLOBALS['TL_LANG']['MSC']['debug_mode']                 = 'Debugausgaben';
$GLOBALS['TL_LANG']['MSC']['difference']                 = 'Differenz';
$GLOBALS['TL_LANG']['MSC']['difference_deleted']         = 'Gelöscht';
$GLOBALS['TL_LANG']['MSC']['difference_new']             = 'Neu';
$GLOBALS['TL_LANG']['MSC']['disabled_cache']             = 'Aktuelle Einträge in der "initconfig.php" beeinträchtigen eine erfolgreiche Synchronisierung.';
$GLOBALS['TL_LANG']['MSC']['edit']                       = 'Bearbeiten';
$GLOBALS['TL_LANG']['MSC']['file']                       = 'Datei';
$GLOBALS['TL_LANG']['MSC']['fileTime']                   = 'Zeitstempel';
$GLOBALS['TL_LANG']['MSC']['ignored']                    = 'Ignoriert';
$GLOBALS['TL_LANG']['MSC']['last_sync']                  = 'Die letzte Synchronisierung wurde um %s am %s von %s (%s) durchgeführt.';
$GLOBALS['TL_LANG']['MSC']['memory_limit']               = 'Auslastung: %s';
$GLOBALS['TL_LANG']['MSC']['next_sync']['0']             = 'Nächster Client';
$GLOBALS['TL_LANG']['MSC']['next_sync']['1']             = 'In Arbeit';
$GLOBALS['TL_LANG']['MSC']['nonrecom_tables']            = 'Nicht empfohlene Datenbank-Tabellen';
$GLOBALS['TL_LANG']['MSC']['normal_files']               = 'Normale Dateien';
$GLOBALS['TL_LANG']['MSC']['popup']                      = 'Vergleichsliste öffnen';
$GLOBALS['TL_LANG']['MSC']['recom_tables']               = 'Empfohlene Datenbank-Tabellen';
$GLOBALS['TL_LANG']['MSC']['repeat_sync']['0']           = 'Wiederholen';
$GLOBALS['TL_LANG']['MSC']['repeat_sync']['1']           = 'In Arbeit';
$GLOBALS['TL_LANG']['MSC']['run_time']                   = 'Laufzeit: %s Sekunden';
$GLOBALS['TL_LANG']['MSC']['server']                     = 'Server';
$GLOBALS['TL_LANG']['MSC']['showExtern']                 = 'Client Systemcheck';
$GLOBALS['TL_LANG']['MSC']['skip']                       = 'Überspringen';
$GLOBALS['TL_LANG']['MSC']['skipped']                    = 'Übersprungen';
$GLOBALS['TL_LANG']['MSC']['state']                      = 'Status';
$GLOBALS['TL_LANG']['MSC']['step']                       = 'Schritt';
$GLOBALS['TL_LANG']['MSC']['substep']                    = 'Zwischenschritt';
$GLOBALS['TL_LANG']['MSC']['sync']                       = 'Synchronisieren';
$GLOBALS['TL_LANG']['MSC']['syncAll']                    = 'System überschreiben';
$GLOBALS['TL_LANG']['MSC']['syncFrom']                   = 'Server synchronisieren';
$GLOBALS['TL_LANG']['MSC']['syncTo']                     = 'Client synchronisieren';
$GLOBALS['TL_LANG']['MSC']['sync_source']                = 'Quelle';
$GLOBALS['TL_LANG']['MSC']['sync_target']                = 'Ziel';
$GLOBALS['TL_LANG']['MSC']['totalsize']                  = 'Größe aller Dateien:';
$GLOBALS['TL_LANG']['MSC']['unchanged']                  = 'Nicht geändert';
$GLOBALS['TL_LANG']['MSC']['unknown_step']               = 'Unbekannter Schritt';
$GLOBALS['TL_LANG']['SYC']['configfiles']                = 'Konfigurationsdateien';
$GLOBALS['TL_LANG']['SYC']['core']                       = 'Contao-Installation';
$GLOBALS['TL_LANG']['SYC']['core_change']                = 'Veränderte Daten';
$GLOBALS['TL_LANG']['SYC']['core_delete']                = 'Gelöschte Daten';
$GLOBALS['TL_LANG']['SYC']['localconfig_errors']         = 'Fehlermeldungen anzeigen';
$GLOBALS['TL_LANG']['SYC']['localconfig_update']         = 'Alle Einträge aktualisieren';
$GLOBALS['TL_LANG']['SYC']['temp_folders']               = 'Datei-Cache';
$GLOBALS['TL_LANG']['SYC']['temp_tables']                = 'Datenbank-Cache';
$GLOBALS['TL_LANG']['SYC']['user']                       = 'Dateiverwaltung (files)';
$GLOBALS['TL_LANG']['SYC']['user_change']                = 'Veränderte Daten';
$GLOBALS['TL_LANG']['SYC']['user_delete']                = 'Gelöschte Daten';
$GLOBALS['TL_LANG']['SYC']['xml_create']                 = 'XML-Dateien';
$GLOBALS['TL_LANG']['MSC']['pattern']                    = 'Pattern (Regex)';
$GLOBALS['TL_LANG']['MSC']['select']                     = 'Auswählen';
$GLOBALS['TL_LANG']['MSC']['unselect']                   = 'Abwählen';
$GLOBALS['TL_LANG']['MSC']['toggle']                     = 'Auswahl umkehren';

