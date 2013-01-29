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
 * @copyright  MEN AT WORK 2012
 * @package    Language
 * @license    GNU/LGPL 
 * @filesource
 */

/**
 * List
 */

$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_1_show']['description_1']      = 'Get systemcheck from client.';

$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_1']['description_1']           = 'Checking client.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_1']['description_2']           = 'Purge temp folder.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_1']['description_3']           = 'Update the remote synchronization software.';

$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_2']['description_1']           = 'Checking and transferring MD5 list.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_2']['description_2']           = 'Search for deletable files.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_2']['description_3']           = 'Compare the files.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_2']['description_4']           = '%s new, %s modified, %s deleted and %s not deliverable file(s) are found.<br />This results in a size of %s new, %s changed und %s deleted files.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_2']['description_5']           = '%s large files are found.';

$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_3']['description_1']           = 'Process files.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_3']['description_2']           = '%s of %s file(s) have been processed.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_3']['description_3']           = 'Splitting large files.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_3']['description_4']           = 'Transferring large files.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_3']['description_5']           = 'Assemble large files.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_3']['description_7']           = '%s of %s large file(s) have been splitted.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_3']['description_6']           = '%s of %s large file(s) have been transferred.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_3']['description_8']           = '%s of %s large file(s) have been assembled.';

$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_1']           = 'Compare database.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_2']           = 'Creating sql scripts.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_3']           = 'Transferring sql scripts.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_4']           = 'The sql file was successfully transferred and imported.';

$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_5']['description_1']           = 'Import data.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_5']['description_2']           = 'Import configuration files.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_5']['description_3']           = ' file(s) skipped.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_5']['description_4']           = ' file(s) sent.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_5']['description_5']           = ' file(s) waiting.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_5']['description_6']           = 'Transferred files:';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_5']['description_7']           = 'Deleted files:';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_5']['description_8']           = 'Incorrect files:';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_5']['description_9']           = 'Deleted folders:';

$GLOBALS['TL_LANG']['tl_syncCto_sync']['abort']                             = 'Abort the synchronization and clean up the client.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['complete_client']                   = 'The synchronization of the %sclient%s was successfully completed.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['complete_server']                   = 'The synchronization of the %sserver%s was successfully completed.';

?>
