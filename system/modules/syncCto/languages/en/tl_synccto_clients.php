<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2013 
 * @package    syncCto
 * @license    GNU/LGPL 
 * @filesource
 */
 
/**
 * List operation
 */
$GLOBALS['TL_LANG']['tl_synccto_clients']['new']                = array('New client', 'New client');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['all']                = array('Edit multiple');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['edit']               = array('Edit client', 'Edit client ID %s');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['copy']               = array('Duplicate client', 'Duplicate client ID %s');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['delete']             = array('Delete client', 'Delete client ID %s');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['show']               = array('Client details', 'Shows the details of client ID %s');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['showExtern']         = array('System check', 'Shows the systemcheck of client ID %s');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['syncTo']             = array('Synchronize client', 'Synchronize client ID %s');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['syncFrom']           = array('Synchronize server', 'Synchronize server');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['syncFromConfirm']    = 'Do you really want to synchronize the server? Data are being loaded by the client.';

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_synccto_clients']['title_legend']       = 'Client description';
$GLOBALS['TL_LANG']['tl_synccto_clients']['connection_legend']  = 'Connection settings';
$GLOBALS['TL_LANG']['tl_synccto_clients']['apikey_legend']      = 'Encryption';
$GLOBALS['TL_LANG']['tl_synccto_clients']['expert_legend']      = 'Expert settings';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_syncCto_clients']['title']              = array('Title', 'Please enter the title of the client.');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['id']                 = array('ID', 'Client ID.');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['description']        = array('Description', 'Here you can enter a short description of the client.');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['address']            = array('Domain', 'Please enter the complete address to the contao installation.');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['path']               = array('Server path', 'Please enter the path to the installation, if it is located in a subfolder.');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['port']               = array('Port number', 'Please enter the number of the HTTP port. Default is 80.');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['apikey']             = array('ctoCommunication API key', 'This key ensures the communication between the contao installations.');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['codifyengine']       = array('Encryption', 'Please choose the encryption engine.');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['http_auth']          = array('Activate HTTP Authentication', 'Please choose this option to active the HTTP Authentication');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['http_username']      = array('Username', 'Please enter the username for authentication.');
$GLOBALS['TL_LANG']['tl_syncCto_clients']['http_password']      = array('Password', 'Please enter the password for authentication.');