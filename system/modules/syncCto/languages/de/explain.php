<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL 
 * @filesource
 */

$GLOBALS['TL_LANG']['XPL']['apiKey']['0']            = array('ctoCommunication API Key', 'Der API Key ist für die Verschlüsselung bei der Synchronisation von zwei Contao-Installationen zuständig.<br /><br />Der Schlüssel wird immer in den allgemeinen Einstellungen des Clients generiert und muss von dort kopiert und in das dafür bereitgestellte Feld auf dem Server eingefügt werden.');
$GLOBALS['TL_LANG']['XPL']['security']['0']          = array('Verschlüsselungs-Engine', 'syncCto bietet standardmäßig drei Arten der Synchronisation an. Zwei mit einer Verschlüsselung und eine ohne.<br /><br />Die Synchronisation ohne Verschlüsselung ist nur bei internen Projekten zu empfehlen da sonst Angriffe von außen nicht auszuschließen sind.');
$GLOBALS['TL_LANG']['XPL']['folder_blacklist']['0']  = array('Platzhalter am Anfang eines Pfades', 'Jeder Eintrag geht vom Root der Installation (TL_ROOT) aus. Bei dem Ordner "assets/css" wird der Pfad "TL_ROOT/assets/css/" ignoriert, aber z.B. nicht der Pfad unter "system/modules/example-extension/assets/css/".');
$GLOBALS['TL_LANG']['XPL']['folder_blacklist']['1']  = array('Platzhalter am Ende eines Pfades', 'Jeder Eintrag wird automatisch am Ende mit einem * versehen. Bei dem Platzhalter "assets/css", würde syncCto auch den Ordner unter "assets/css3pie" ignorieren. Daher sollte nach jedem explizitem Ordner ein "/" angeben werden.');
$GLOBALS['TL_LANG']['XPL']['folder_blacklist']['2']  = array('Beispiel 1: .idea Ordner ignorieren', 'Ordner können mit dem * Selektor systemweit ignoriert werden. Zum Beispiel werden mit dem Platzhalter */.idea/ alle Ordner mit dem Namen .idea ignoriert.');
$GLOBALS['TL_LANG']['XPL']['folder_blacklist']['3']  = array('Beispiel 2: Fortlaufende Nummerierung', 'Ordner mit fortlaufender Nummerierung können mit dem * Selektor ignoriert werden. Zum Beispiel Backupverzeichnisse wie "backup/file_" oder "backup/db_" werden dadurch ignoriert, sofern sie mit "file_" oder "db_" anfangen.');
$GLOBALS['TL_LANG']['XPL']['folder_blacklist']['4']  = array('Platzhalter', 'Derzeit verfügbare Platzhalter: <br /> "?" - Ein beliebiges Zeichen <br /> "*" - Beliebig viele Zeichen');