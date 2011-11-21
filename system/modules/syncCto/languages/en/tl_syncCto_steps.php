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
 * List
 */

// Step 1
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_1']['description_1'] = 'Checking client.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_1']['description_2'] = 'Purge temp folder.';

// Step 2
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_2']['description_1'] = 'Checking and transferring MD5 list.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_2']['description_2'] = 'Search for deletable files.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_2']['description_3'] = 'Loading compare list.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_2']['description_4'] = '%s new, %s modified, %s deleted and %s not deliverable file(s) are found.';

// Step 3
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_3']['description_1'] = 'Process large files.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_3']['description_2'] = '%s of %s large file(s) have been processed.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_3']['description_3'] = 'Assemble large files.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_3']['description_4'] = '%s of %s large file(s) have been transferred.';

// Step 4
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_1'] = 'Creating sql scripts.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_2'] = 'Transferring sql scripts.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_3'] = 'Importing sql scripts.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_4'] = 'The sql file was successfully transferred and imported.';

// Step 5
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_5']['description_1'] = 'Process files.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_5']['description_2'] = '%s of %s files were transferred.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_5']['description_3'] = 'Import configuration files.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_5']['description_4'] = ' file(s) will be skipped.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_5']['description_5'] = ' file(s) sent.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_5']['description_6'] = ' file(s) waiting.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_5']['description_7'] = 'Transferred files:';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_5']['description_8'] = 'Deleted files:';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_5']['description_9'] = 'Incorrect files:';

?>
