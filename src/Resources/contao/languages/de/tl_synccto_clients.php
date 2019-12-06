<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL 
 * @filesource
 */
 
/**
 * List operation
 */
$GLOBALS['TL_LANG']['tl_synccto_clients']['new']                = array('Neuer Client', 'Einen neuen Client anlegen');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['syncToAll']          = array('Alle synchronisieren', 'Alle Clients auf einmal synchronisieren');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['all']                = array('Mehrere bearbeiten', 'Mehrere Datensätze auf einmal bearbeiten');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['edit']               = array('Client bearbeiten', 'Client ID %s bearbeiten');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['copy']               = array('Client duplizieren', 'Client ID %s duplizieren');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['delete']             = array('Client löschen', 'Client ID %s löschen');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['show']               = array('Clientdetails', 'Details des Clients ID %s anzeigen');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['showExtern']         = array('Systemcheck', 'Systemcheck des Clients ID %s anzeigen');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['syncTo']             = array('Client synchronisieren', 'Client ID %s synchronisieren');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['syncFrom']           = array('Server synchronisieren', 'Server synchronisieren');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['syncFromConfirm']    = 'Soll der Server wirklich synchronisiert werden? Es können dabei Dateien überschrieben werden, die sich derzeit in der Bearbeitung befinden.';

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_synccto_clients']['client_legend']      = 'Client-Einstellungen';
$GLOBALS['TL_LANG']['tl_synccto_clients']['connection_legend']  = 'Verbindungs-Einstellungen';
$GLOBALS['TL_LANG']['tl_synccto_clients']['expert_legend']      = 'Experten-Einstellungen';
$GLOBALS['TL_LANG']['tl_synccto_clients']['legend']             = 'Legende: ';

/**
 * Legend ping state
 */
$GLOBALS['TL_LANG']['tl_synccto_clients']['state']['gray']      = 'Das System konnte noch nicht geprüft werden.';
$GLOBALS['TL_LANG']['tl_synccto_clients']['state']['red']       = 'Das System ist nicht erreichbar.';
$GLOBALS['TL_LANG']['tl_synccto_clients']['state']['blue']      = 'Die Extensions ctoCommunication/syncCto sind nicht installiert.';
$GLOBALS['TL_LANG']['tl_synccto_clients']['state']['orange']    = 'Der ctoCommunication API-Key ist nicht korrekt.';
$GLOBALS['TL_LANG']['tl_synccto_clients']['state']['green']     = 'Alle Systeme sind online.';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_syncCto_clients']['title']              = array('Titel', 'Hier können Sie den Titel des Clients eingeben.');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['id']                 = array('ID', 'ID des Clients.');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['address']            = array('Domain', 'Bitte geben Sie die Domain ein.');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['port']               = array('Portnummer', 'Bitte geben Sie die Nummer des HTTP-Ports ein. Standard ist 80.');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['apikey']             = array('ctoCommunication API Key', 'Dieser Schlüssel sichert die Kommunikation zwischen den Contao-Installationen ab.');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['codifyengine']       = array('Verschlüsselungs-Engine', 'Wählen Sie bitte die Verschlüsselungs-Engine aus.');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['http_auth']          = array('HTTP-Authentifizierung aktivieren', 'Wählen Sie diese Option, wenn Sie die HTTP-Anmeldung aktivieren möchten.');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['http_username']      = array('Benutzername', 'Geben Sie hier bitte den Benutzernamen zur Anmeldung ein.');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['http_password']      = array('Passwort', 'Geben Sie hier bitte das Passwort zur Anmeldung ein.');