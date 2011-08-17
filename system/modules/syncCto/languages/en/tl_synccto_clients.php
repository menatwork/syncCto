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
 
/**
 * List operation
 */
$GLOBALS['TL_LANG']['tl_synccto_clients']['new'] = array('New client');
$GLOBALS['TL_LANG']['tl_synccto_clients']['all'] = array('Edit multiple');
$GLOBALS['TL_LANG']['tl_synccto_clients']['edit'] = array('Edit client', 'Edit client ID %s');
$GLOBALS['TL_LANG']['tl_synccto_clients']['copy'] = array('Duplicate client', 'Duplicate client ID %s');
$GLOBALS['TL_LANG']['tl_synccto_clients']['delete'] = array('Delete client', 'Delete client ID %s');
$GLOBALS['TL_LANG']['tl_synccto_clients']['show'] = array('Client details', 'Show the details of client ID %s');
$GLOBALS['TL_LANG']['tl_synccto_clients']['syncTo'] = array('Synchronize client', 'Synchronize client ID %s');
$GLOBALS['TL_LANG']['tl_synccto_clients']['syncFrom'] = array('Synchronize server', 'Synchronize server');
$GLOBALS['TL_LANG']['tl_synccto_clients']['syncFromConfirm'] = 'Do you really want to synchronize the server?';

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_synccto_clients']['title_legend'] = 'Client description';
$GLOBALS['TL_LANG']['tl_synccto_clients']['connection_label'] = 'Connection settings';
$GLOBALS['TL_LANG']['tl_synccto_clients']['user_label'] = 'User data';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_synccto_clients']['title'] = array('Title', 'Please enter the title of the client.');
$GLOBALS['TL_LANG']['tl_synccto_clients']['id'] = array('ID', 'Client ID.');
$GLOBALS['TL_LANG']['tl_synccto_clients']['description'] = array('Description', 'Here you can enter a short description of the client.');
$GLOBALS['TL_LANG']['tl_synccto_clients']['address'] = array('Address', 'Please enter the complete address to the contao installation.');
$GLOBALS['TL_LANG']['tl_synccto_clients']['path'] = array('Path', 'Please enter the path to the file synccto.php.');
$GLOBALS['TL_LANG']['tl_synccto_clients']['port'] = array('Port number', 'Please enter the number of the HTTP port. Default is 80.');
$GLOBALS['TL_LANG']['tl_synccto_clients']['username'] = array('Username', 'Please enter your username.');
$GLOBALS['TL_LANG']['tl_synccto_clients']['password'] = array('Password', 'Please enter the password of the user.');
$GLOBALS['TL_LANG']['tl_synccto_clients']['seckey'] = array("Encryption key", "The key is used for the encrypted data storage.");

?>