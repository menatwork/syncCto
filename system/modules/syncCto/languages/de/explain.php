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

$GLOBALS['TL_LANG']['XPL']['apiKey']['0']                   = array('ctoCommunication API Key', 'Der API Key ist für die Verschlüsselung bei der Synchronisation von zwei Contao-Installationen zuständig.<br /><br />Der Schlüssel wird immer in den allgemeinen Einstellungen des Clients generiert und muss von dort kopiert und in das dafür bereitgestellte Feld auf dem Server eingefügt werden.');
$GLOBALS['TL_LANG']['XPL']['security']['0']                 = array('Verschlüsselungs-Engine', 'syncCto bietet standardmäßig drei Arten der Synchronisation an. Zwei mit einer Verschlüsselung und eine ohne.<br /><br />Die Synchronisation ohne Verschlüsselung ist nur bei internen Projekten zu empfehlen da sonst Angriffe von außen nicht auszuschließen sind.');
 
$GLOBALS['TL_LANG']['XPL']['syncCto_database']['0']        = array('Schwarz hinterlegte Einträge', 'Diese Einträge konnten nicht geprüft werden. Entweder weil noch keinen synchronisation vorgenommen wurde oder weil ein Fehler aufgetretten ist.');
$GLOBALS['TL_LANG']['XPL']['syncCto_database'][1]        = array('Grün hinterlegte Einträge', 'Diese Einträge sind seit der letzten synchronisation unverändert.');
$GLOBALS['TL_LANG']['XPL']['syncCto_database'][2]        = array('Rot hinterlegte Einträge', 'Diese Einträge sind seit der letzten synchronisation verändert wurden.');

?>
