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
 * List operation
 */
$GLOBALS['TL_LANG']['tl_synccto_clients']['new']                = array('Neuer Client', 'Neuer Client');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['all']                = array('Mehrere bearbeiten');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['edit']               = array('Client bearbeiten', 'Client ID %s bearbeiten');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['copy']               = array('Client duplizieren', 'Client ID %s duplizieren');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['delete']             = array('Client löschen', 'Client ID %s löschen');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['show']               = array('Clientdetails', 'Details des Clients ID %s anzeigen');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['syncTo']             = array('Client synchronisieren', 'Client ID %s synchronisieren');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['syncFrom']           = array('Server synchronisieren', 'Server synchronisieren');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['syncFromConfirm']    = 'Soll der Server wirklich synchronisiert werden? Es werden Daten vom Client geladen.';

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_synccto_clients']['title_legend']       = 'Client-Beschreibung';
$GLOBALS['TL_LANG']['tl_synccto_clients']['connection_legend']  = 'Verbindungs-Einstellungen';
$GLOBALS['TL_LANG']['tl_synccto_clients']['apikey_legend']      = 'Verschlüsselung';
$GLOBALS['TL_LANG']['tl_synccto_clients']['auth_legend']        = 'HTTP-Authentifizierung';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_syncCto_clients']['title']              = array('Titel', 'Hier können Sie den Titel des Clients eingeben.');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['id']                 = array('ID', 'ID des Clients.');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['description']        = array('Beschreibung', 'Hier können Sie eine Kurzbeschreibung des Clients eingeben.');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['address']            = array('Domain', 'Bitte geben Sie die Domain ein.');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['path']               = array('Serverpfad', 'Bitte geben Sie den Pfad zur Installation ein, falls sich diese in einem Unterordner befindet.');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['port']               = array('Portnummer', 'Bitte geben Sie die Nummer des HTTP-Ports ein. Standard ist 80.');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['apikey']             = array('ctoCommunication API Key', 'Dieser Schlüssel sichert die Kommunikation zwischen den Contao-Installationen ab.');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['codifyengine']       = array('Verschlüsselungs-Engine', 'Wählen Sie bitte die Verschlüsselungs-Engine aus.');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['http_auth']          = array('HTTP-Authentifizierung aktivieren', 'Wählen Sie diese Option, wenn Sie die HTTP-Anmeldung aktivieren möchten.');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['http_username']      = array('Benutzername', 'Geben Sie hier bitte den Benutzernamen zur Anmeldung ein.');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['http_password']      = array('Passwort', 'Geben Sie hier bitte das Passwort zur Anmeldung ein.');

?>