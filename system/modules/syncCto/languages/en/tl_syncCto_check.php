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
 * Headline
 */
$GLOBALS['TL_LANG']['tl_syncCto_check']['check']                    = 'System check';
$GLOBALS['TL_LANG']['tl_syncCto_check']['configuration']            = 'PHP configuration';
 
/**
 * Table
 */
$GLOBALS['TL_LANG']['tl_syncCto_check']['parameter']                = 'Parameter';
$GLOBALS['TL_LANG']['tl_syncCto_check']['value']                    = 'Value';
$GLOBALS['TL_LANG']['tl_syncCto_check']['description']              = 'Description';
$GLOBALS['TL_LANG']['tl_syncCto_check']['on']                       = 'On';
$GLOBALS['TL_LANG']['tl_syncCto_check']['off']                      = 'Off';
$GLOBALS['TL_LANG']['tl_syncCto_check']['safemode']                 = array('Safe mode', 'Recommended setting is Off.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['met']                      = array('Maximum execution time', 'Recommended setting is 30 or greater.'); 
$GLOBALS['TL_LANG']['tl_syncCto_check']['memory_limit']             = array('Memory limit', 'Recommended setting is 128,0 MB or greater.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['register_globals']         = array('Register globals', 'Recommended setting is Off.'); 
$GLOBALS['TL_LANG']['tl_syncCto_check']['file_uploads']             = array('File uploads', 'Recommended setting is On.'); 
$GLOBALS['TL_LANG']['tl_syncCto_check']['umf']                      = array('Upload maximum filesize', 'Recommended setting is 8,0 MB or greater.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['pms']                      = array('Post maximum size', 'Recommended setting is 8,0 MB or greater.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['mit']                      = array('Maximum input time', 'Recommended setting is -1, 60 or greater.'); 
$GLOBALS['TL_LANG']['tl_syncCto_check']['dst']                      = array('Default socket timeout', 'Recommended setting is 30 or greater.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['fsocket']                  = array('Fsockopen', 'Recommended setting is On.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['fopen']                    = array('Fopen', 'Recommended setting is On.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['zip_archive']              = array('ZipArchive', 'Recommended setting is On.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['suhosin']                  = array('Suhosin', 'Recommended setting is Off.');

/**
 * Text
 */
$GLOBALS['TL_LANG']['tl_syncCto_check']['other_sync_issues']        = 'Other known issues';
$GLOBALS['TL_LANG']['tl_syncCto_check']['explanation_sync_issues']  = 'Some server configuration settings are preventing the synchronization, which cannot be detected by the system check.';
$GLOBALS['TL_LANG']['tl_syncCto_check']['known_issues']             = 'Some known settings are:';
$GLOBALS['TL_LANG']['tl_syncCto_check']['suhosin_issue']            = 'Suhosin is preventing the synchronization';
$GLOBALS['TL_LANG']['tl_syncCto_check']['mrl_issue']                = 'The MaxRequestLen is too low';

$GLOBALS['TL_LANG']['tl_syncCto_check']['safemodehack']             = 'syncCto cannot be used because of missing write permissions.';

?>