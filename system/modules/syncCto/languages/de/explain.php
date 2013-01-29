<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2013 
 * @package    syncCto
 * @license    GNU/LGPL 
 * @filesource
 */

$GLOBALS['TL_LANG']['XPL']['apiKey']['0']                   = array('ctoCommunication API Key', 'Der API Key ist für die Verschlüsselung bei der Synchronisation von zwei Contao-Installationen zuständig.<br /><br />Der Schlüssel wird immer in den allgemeinen Einstellungen des Clients generiert und muss von dort kopiert und in das dafür bereitgestellte Feld auf dem Server eingefügt werden.');
$GLOBALS['TL_LANG']['XPL']['security']['0']                 = array('Verschlüsselungs-Engine', 'syncCto bietet standardmäßig drei Arten der Synchronisation an. Zwei mit einer Verschlüsselung und eine ohne.<br /><br />Die Synchronisation ohne Verschlüsselung ist nur bei internen Projekten zu empfehlen da sonst Angriffe von außen nicht auszuschließen sind.');

?>
