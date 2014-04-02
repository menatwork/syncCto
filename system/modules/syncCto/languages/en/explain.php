<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL 
 * @filesource
 */

$GLOBALS['TL_LANG']['XPL']['apiKey']['0']                   = array('ctoCommunication API Key', 'The API key encode the communication between two contao installations.<br /><br />The key is always generated while using the common settings. You have to copy and insert it in the specific field on the server.');
$GLOBALS['TL_LANG']['XPL']['security']['0']                 = array('Encryption engine', 'synCto is offering as default three types of synchronization. Two with encoding and one without.<br /><br />Just use the unencripted synchronization for internal projects. Otherwise attacks from outside are possible.');

$GLOBALS['TL_LANG']['XPL']['folder_blacklist']['0']         = array('Wildcards at the beginning of a path', 'Jeder Eintrag geht vom Root der Installation (TL_ROOT) aus. Bei dem Ordner "assets/css" wird der Pfad "TL_ROOT/assets/css/" ignoriert, aber z.B. nicht der Pfad unter "system/modules/example-extension/assets/css/".');
$GLOBALS['TL_LANG']['XPL']['folder_blacklist']['1']         = array('Wildcards at the beginning of a path', 'Jeder Eintrag wird automatisch am Ende mit einem * versehen. Bei dem Platzhalter "assets/css", würde syncCto auch den Ordner unter "assets/css3pie" ignorieren. Daher sollte nach jedem explizitem Ordner ein "/" angeben werden.');
$GLOBALS['TL_LANG']['XPL']['folder_blacklist']['2']         = array('Example 1: Ignore .idea folder', 'Ordner können mit dem * Selektor systemweit ignoriert werden. Zum Beispiel werden mit dem Platzhalter */.idea/ alle Ordner mit dem Namen .idea ignoriert.');
$GLOBALS['TL_LANG']['XPL']['folder_blacklist']['3']         = array('Example 2: Consecutive numbering', 'Ordner mit fortlaufender Nummerierung können mit dem * Selektor ignoriert werden. Zum Beispiel Backupverzeichnisse wie "backup/file_" oder "backup/db_" werden dadurch ignoriert, sofern sie mit "file_" oder "db_" anfangen.');
$GLOBALS['TL_LANG']['XPL']['folder_blacklist']['4']         = array('Wildcards', 'Currently available wildcards: <br /> "?" - Any one character <br /> "*" - Any number of characters');