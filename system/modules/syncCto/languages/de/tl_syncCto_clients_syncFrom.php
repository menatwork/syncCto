<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
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
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['sync_legend']             = 'Datei-Synchronisation';
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['table_legend']            = 'Datenbank-Synchronisation';
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['systemoperations_legend'] = 'Systemwartung';
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['edit']                    = 'Synchronisation des Servers';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['sync_type']                         = array('Dateien synchronisieren', 'Wählen Sie bitte aus, welche Dateien synchronisiert werden sollen.');
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['database_check']                    = array('Datenbank synchronisieren', 'Wählen Sie diese Option, wenn Sie die Datenbank synchronisieren wollen.');
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['database_tables_recommended']       = array('Empfohlene Tabellen', 'Hier können Sie die empfohlenen Tabellen für die Synchronisation auswählen.');
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['database_tables_none_recommended']  = array('Nicht empfohlene Tabellen', 'Hier können Sie die NICHT empfohlenen Tabellen für die Synchronisation auswählen. Benutzung auf eigene Gefahr.');
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['systemoperations_check']            = array('Systemwartung aktivieren', 'Wählen Sie diese Option, wenn Sie die Datenbank synchronisieren wollen.');
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['systemoperations_maintenance']      = array('Server bereinigen', 'Hier können Sie die Systembereinigung auf dem Server konfigurieren.');
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['attention_flag']                    = array('Warnhinweis aktivieren', 'Wählen Sie diese Option, wenn der Warnhinweis auf dem Client aktiviert werden soll.');