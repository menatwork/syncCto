<?php

/**
 * This file is part of menatwork/synccto.
 *
 * (c) 2014-2018 MEN AT WORK.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    menatwork/synccto
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2014-2018 MEN AT WORK.
 * @license    https://github.com/menatwork/syncCto/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

$GLOBALS['TL_LANG']['XPL']['apiKey']['0']                   = array('ctoCommunication API Key', 'The API key encode the communication between two contao installations.<br /><br />The key is always generated while using the common settings. You have to copy and insert it in the specific field on the server.');
$GLOBALS['TL_LANG']['XPL']['security']['0']                 = array('Encryption engine', 'synCto is offering as default three types of synchronization. Two with encoding and one without.<br /><br />Just use the unencripted synchronization for internal projects. Otherwise attacks from outside are possible.');

$GLOBALS['TL_LANG']['XPL']['folder_blacklist']['0']         = array('Wildcards at the beginning of a path', 'Each entry starts at the root folder (TL_ROOT) of your contao installation. When adding the entry "assets/css", the folder "TL_ROOT/assets/css" will be ignored but not the folder "system/modules/example-extension/assets/css/".');
$GLOBALS['TL_LANG']['XPL']['folder_blacklist']['1']         = array('Wildcards at the end of a path','A * wildcard will be added as a default suffix for every entry. With the entry "assets/css", syncCto will also ignore the folder "assets/css3pie". If you just want to ignore a single folder, please make sure to put a "/" at the end of the entry.' );
$GLOBALS['TL_LANG']['XPL']['folder_blacklist']['2']         = array('Example 1: Ignore .idea folder', 'Folders can be ignored throughout the system with the * selector. For example, using the wildcard "*/.idea/" all folders named ".idea" will be ignored.');
$GLOBALS['TL_LANG']['XPL']['folder_blacklist']['3']         = array('Example 2: Prefixed folders', 'Folders with a prefix can be ignored by using the * selector. For example using the entry "backup/file_*", all folders starting with "backup/file_" will be ignored (backup/file_01, backup/file_02, backup/file_yesterday).');
$GLOBALS['TL_LANG']['XPL']['folder_blacklist']['4']         = array('Wildcards', 'Currently available wildcards: <br /> "?" - Any one character <br /> "*" - Any number of characters');
