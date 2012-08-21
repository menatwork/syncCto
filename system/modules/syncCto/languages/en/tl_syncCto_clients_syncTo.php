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
 * @copyright  MEN AT WORK 2012 
 * @package    Language
 * @license    GNU/LGPL 
 * @filesource
 */

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['sync_legend']                         = 'File synchronization';
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['table_legend']                        = 'Database synchronization';
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['systemoperations_legend']             = 'Maintenance';
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['edit']                                = 'Client synchronization';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['sync_options']                        = array('Synchronize files', 'Here you can select which files should be synchronized.');
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['database_check']                      = array('Synchronize database', 'Choose this option for database synchronization.');
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['systemoperations_check']              = array('Activate maintenance', 'Choose these options to activate the system maintenance.');
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['systemoperations_maintenance']        = array('Purge data', 'Please select the data you want to purge.');
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['attention_flag']                      = array('Deactivate warning notice', 'Choose this option to deactivate the syncronisation warning on the client.');
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['localconfig_error']                   = array('Display error messages', 'Print error messages to the screen (not recommended for productional sites).');

?>