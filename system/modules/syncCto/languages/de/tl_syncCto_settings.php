<?php

/**
 * This file is part of menatwork/synccto.
 *
 * (c) 2014-2018 MEN AT WORK.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    menatwork/synccto
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @author     Patrick Kahl <kahl.patrick@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2014-2018 MEN AT WORK.
 * @license    https://github.com/menatwork/syncCto/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_syncCto_settings']['edit']                      = 'Die syncCto Konfiguration bearbeiten';
$GLOBALS['TL_LANG']['tl_syncCto_settings']['blacklist_legend']          = 'Dateien und Ordner auschließen';
$GLOBALS['TL_LANG']['tl_syncCto_settings']['local_blacklist_legend']    = 'localconfig.php Einträge auschließen';
$GLOBALS['TL_LANG']['tl_syncCto_settings']['whitelist_legend']          = 'Erlaubte Ordner im Stammverzeichnis';
$GLOBALS['TL_LANG']['tl_syncCto_settings']['hidden_tables_legend']      = 'Versteckte Tabellen';
$GLOBALS['TL_LANG']['tl_syncCto_settings']['tables_legend']             = 'Nicht empfohlene Tabellen';
$GLOBALS['TL_LANG']['tl_syncCto_settings']['security_legend']           = 'Verschlüsselung';
$GLOBALS['TL_LANG']['tl_syncCto_settings']['custom_legend']             = 'Experten-Einstellungen';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_syncCto_settings']['folder_blacklist']          = array('Ordner', 'Hier können Sie definieren welche Ordner bei der Synchronisation ignoriert werden sollen.');
$GLOBALS['TL_LANG']['tl_syncCto_settings']['file_blacklist']            = array('Dateien', 'Hier können Sie definieren welche Dateien bei der Synchronisation ignoriert werden sollen.');
$GLOBALS['TL_LANG']['tl_syncCto_settings']['folder_whitelist']          = array('Erlaubte Ordner', 'Hier können Sie definieren welche Root-Ordner bei der Synchronisation beachtet werden sollen.');
$GLOBALS['TL_LANG']['tl_syncCto_settings']['local_blacklist']           = array('localconfig.php', 'Hier können Sie definieren welche localconfig.php Einträge nicht synchronisiert werden sollen.');
$GLOBALS['TL_LANG']['tl_syncCto_settings']['database_tables']           = array('Nicht empfohlene Tabellen', 'Hier können Sie definieren welche Datenbank-Tabellen Sie nicht für die Synchronisation empfehlen.');
$GLOBALS['TL_LANG']['tl_syncCto_settings']['hidden_tables']             = array('Versteckte Tabellen', 'Hier können Sie den Zugriff auf eine oder mehrere Datenbank-Tabellen für die Synchronisation festlegen.');
$GLOBALS['TL_LANG']['tl_syncCto_settings']['hidden_tables_placeholder'] = array('Versteckte Tabellen - Platzhalter', 'Hier können Sie den Zugriff auf temporäre Datenbank-Tabellen, die regelmäßig angelegt und gelöscht werden, (z.B. Tabimporter) festlegen.');
$GLOBALS['TL_LANG']['tl_syncCto_settings']['debug_mode']                = array('Debugmodus aktivieren', 'Informationen zur Laufzeit und den übertragenen Dateien während der Synchronisation anzeigen.');
$GLOBALS['TL_LANG']['tl_syncCto_settings']['custom_settings']           = array('Experten-Einstellungen aktivieren', 'Klicken Sie hier, wenn Sie wissen was Sie tun.');
$GLOBALS['TL_LANG']['tl_syncCto_settings']['wait_timeout']              = array('"wait_timeout" konfigurieren', 'Mehr Informationen: http://goo.gl/rC5Y4');
$GLOBALS['TL_LANG']['tl_syncCto_settings']['interactive_timeout']       = array('"interactive_timeout" konfigurieren', 'Mehr Informationen: http://goo.gl/VHxRK');
$GLOBALS['TL_LANG']['tl_syncCto_settings']['db_query_limt']             = array('Abfragelimit der Datenbank', 'Hier können Sie festlegen, wie viele Datensätze auf einmal aus der Datenbank geladen werden. Korrigieren Sie das Limit nach unten, wenn ein "500 Server Error" beim DB-Backup/Synchronisation entsteht.');
$GLOBALS['TL_LANG']['tl_syncCto_settings']['auto_db_updater']           = array('Automatische Aktualisierung der Datenbank', 'Hier können Sie auswählen, welche Aktion der DB Update nach einer Synchronisation ausführen soll.');

/**
 * Updater
 */
$GLOBALS['TL_LANG']['tl_syncCto_settings']['CREATE']                    = 'Neue Tabellen anlegen';
$GLOBALS['TL_LANG']['tl_syncCto_settings']['ALTER_ADD']                 = 'Neue Spalten anlegen';
$GLOBALS['TL_LANG']['tl_syncCto_settings']['ALTER_CHANGE']              = 'Bestehende Spalten ändern';
$GLOBALS['TL_LANG']['tl_syncCto_settings']['ALTER_DROP']                = 'Bestehende Spalten löschen';
$GLOBALS['TL_LANG']['tl_syncCto_settings']['DROP']                      = 'Bestehende Tabellen löschen';

/**
 * Miscellaneous
 */
$GLOBALS['TL_LANG']['tl_syncCto_settings']['hide_by_regex']             = "%s <span style='color: #999; display:inline;'>(Temporäre Datenbank-Tabellen)</span>";
