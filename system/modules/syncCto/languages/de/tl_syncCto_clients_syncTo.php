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
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['sync_legend']                         = 'Synchronisations-Einstellungen';
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['filelist_legend']                     = 'Dateien und Ordner';
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['table_recommend_legend']              = 'Empfohlene Tabellen';
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['table_none_recommend_legend']         = 'Nicht empfohlene Tabellen';
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['edit']                                = 'Synchronisation des Clients';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['sync_type']                           = array('Art der Synchronisation', 'Hier können Sie die Art der Synchronisation auswählen.');
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['database_tables_recommended']         = array('Empfohlene Tabellen', 'Hier können Sie die empfohlenen Tabellen für die Synchronisation auswählen.');
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['database_tables_none_recommended']    = array('Nicht empfohlene Tabellen', 'Hier können die NICHT empfohlenen Tabellen für das Backup ausgewählt werden. Benutzung auf eigene Gefahr.');
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['filelist']                            = array('Quelldateien', 'Bitte wählen Sie eine Datei oder einen Ordner aus der Dateiübersicht.');
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['purgeData']                           = array('Daten bereinigen', 'Bitte wählen Sie diese Option aus um die Daten auf dem Client zu bereinigen.');
?>