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
$GLOBALS['TL_LANG']['tl_synccto_clients']['new'] = array('Neuer Client');
$GLOBALS['TL_LANG']['tl_synccto_clients']['all'] = array('Mehrere bearbeiten');
$GLOBALS['TL_LANG']['tl_synccto_clients']['edit'] = array('Client bearbeiten', 'Client ID %s bearbeiten');
$GLOBALS['TL_LANG']['tl_synccto_clients']['copy'] = array('Client duplizieren', 'Client ID %s duplizieren');
$GLOBALS['TL_LANG']['tl_synccto_clients']['delete'] = array('Client löschen', 'Client ID %s löschen');
$GLOBALS['TL_LANG']['tl_synccto_clients']['show'] = array('Clientdetails', 'Details des Clients ID %s anzeigen');
$GLOBALS['TL_LANG']['tl_synccto_clients']['syncTo'] = array('Client synchronisieren', 'Client ID %s synchronisieren');
$GLOBALS['TL_LANG']['tl_synccto_clients']['syncFrom'] = array('Server synchronisieren', 'Server synchronisieren');
$GLOBALS['TL_LANG']['tl_synccto_clients']['syncFromConfirm'] = 'Soll der Server wirklich synchronisiert werden?';

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_synccto_clients']['title_legend'] = 'Client-Beschreibung';
$GLOBALS['TL_LANG']['tl_synccto_clients']['connection_label'] = 'Verbindungs-Einstellungen';
$GLOBALS['TL_LANG']['tl_synccto_clients']['user_label'] = 'Benutzerdaten';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_synccto_clients']['title'] = array('Titel', 'Hier können Sie den Titel des Clients eingeben.');
$GLOBALS['TL_LANG']['tl_synccto_clients']['id'] = array('ID', 'ID des Clients.');
$GLOBALS['TL_LANG']['tl_synccto_clients']['description'] = array('Beschreibung', 'Hier können Sie eine Kurzbeschreibung des Clients eingeben.');
$GLOBALS['TL_LANG']['tl_synccto_clients']['address'] = array('Domain', 'Bitte geben Sie die Domain ein.');
$GLOBALS['TL_LANG']['tl_synccto_clients']['path'] = array('Serverpfad', 'Bitte geben Sie den Pfad zur Installation ein, falls sich diese in einem Unterordner befindet.');
$GLOBALS['TL_LANG']['tl_synccto_clients']['port'] = array('Portnummer', 'Bitte geben Sie die Nummer des HTTP-Ports ein. Standard ist 80.');
$GLOBALS['TL_LANG']['tl_synccto_clients']['apikey'] = array("Verschlüsselungsschlüssel", "Der Schlüssel wird zur verschlüsselten Datenspeicherung verwendet.");
$GLOBALS['TL_LANG']['tl_synccto_clients']['codifyengine'] = array("Verschlüsselungs-Engine", "Wählen Sie bitte die Verschlüsselungs-Engine aus.");

?>