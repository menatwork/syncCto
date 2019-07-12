<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL 
 * @filesource
 */

$GLOBALS['TL_LANG']['XPL']['apiKey']['0']['0']           = 'ctoCommunication API-Key';
$GLOBALS['TL_LANG']['XPL']['apiKey']['0']['1']           = 'Der API-Key ist für die Verschlüsselung bei der Synchronisation von zwei Contao-Installationen zuständig.<br /><br />Der Schlüssel wird immer in den allgemeinen Einstellungen des Clients generiert und muss von dort kopiert und in das dafür bereitgestellte Feld auf dem Server eingefügt werden.';
$GLOBALS['TL_LANG']['XPL']['folder_blacklist']['0']['0'] = 'Platzhalter am Anfang eines Pfades';
$GLOBALS['TL_LANG']['XPL']['folder_blacklist']['0']['1'] = 'Jeder Eintrag geht vom Root der Installation (TL_ROOT) aus. Bei dem Ordner "assets/css" wird der Pfad "TL_ROOT/assets/css/" ignoriert, aber z.B. nicht der Pfad unter "system/modules/example-extension/assets/css/".';
$GLOBALS['TL_LANG']['XPL']['folder_blacklist']['1']['0'] = 'Platzhalter am Ende eines Pfades';
$GLOBALS['TL_LANG']['XPL']['folder_blacklist']['1']['1'] = 'Jeder Eintrag wird automatisch am Ende mit einem * versehen. Bei dem Platzhalter "assets/css", würde syncCto auch den Ordner unter "assets/css3pie" ignorieren. Daher sollte nach jedem explizitem Ordner ein "/" angeben werden.';
$GLOBALS['TL_LANG']['XPL']['folder_blacklist']['2']['0'] = 'Beispiel 1: .idea Ordner ignorieren';
$GLOBALS['TL_LANG']['XPL']['folder_blacklist']['2']['1'] = 'Ordner können mit dem * Selektor systemweit ignoriert werden. Zum Beispiel werden mit dem Platzhalter */.idea/ alle Ordner mit dem Namen .idea ignoriert.';
$GLOBALS['TL_LANG']['XPL']['folder_blacklist']['3']['0'] = 'Beispiel 2: Ordner mit festem Schema';
$GLOBALS['TL_LANG']['XPL']['folder_blacklist']['3']['1'] = 'Ordner mit einem festen Schema können mit dem * Selektor ignoriert werden. Bei dem Beispieleintrag "backup/file_*" werden alle file_ Ordner (backup/file_01, backup/file02, backup/file_heute) ignoriert. ';
$GLOBALS['TL_LANG']['XPL']['folder_blacklist']['4']['0'] = 'Platzhalter';
$GLOBALS['TL_LANG']['XPL']['folder_blacklist']['4']['1'] = 'Derzeit verfügbare Platzhalter: <br /> "?" - Ein beliebiges Zeichen <br /> "*" - Beliebig viele Zeichen';
$GLOBALS['TL_LANG']['XPL']['security']['0']['0']         = 'Verschlüsselungs-Engine';
$GLOBALS['TL_LANG']['XPL']['security']['0']['1']         = 'syncCto bietet standardmäßig drei Arten der Synchronisation an. Zwei mit einer Verschlüsselung und eine ohne.<br /><br />Die Synchronisation ohne Verschlüsselung ist nur bei internen Projekten zu empfehlen, da sonst Angriffe von außen nicht auszuschließen sind.';

