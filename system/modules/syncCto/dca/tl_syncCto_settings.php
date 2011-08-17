<?php if (!defined('TL_ROOT'))
    die('You can not access this file directly!');

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
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */
$GLOBALS['TL_DCA']['tl_syncCto_settings'] = array(
    // Config
    'config' => array
        (
        'dataContainer' => 'File',
        'closed' => true,
        'notEditable' => true,
    ),
    // Palettes
    'palettes' => array
        (
        'default' => '{blacklist_legend:hide},syncCto_folder_blacklist,syncCto_file_blacklist;{whitelist_legend:hide},syncCto_folder_whitelist;{local_blacklist_legend},syncCto_local_blacklist;{hidden_tables_legend:hide},syncCto_table_hidden;{tables_legend},syncCto_table_list;{security_legend},syncCto_seckey;{debug_legend},syncCto_debug_filelist,syncCto_debug_log,syncCto_measurement_log;'
    ),
    // Fields
    'fields' => array(
        'syncCto_folder_blacklist' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['folder_blacklist'],
            'inputType' => 'textwizard',
            'eval' => array('trailingSlash' => false, 'style' => 'width:320px', 'allowHtml' => false),
            'load_callback' => array(array('SyncCtoCallback', 'loadcallFolderBlacklist')),
        ),
        'syncCto_file_blacklist' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['file_blacklist'],
            'inputType' => 'textwizard',
            'eval' => array('trailingSlash' => false, 'style' => 'width:320px', 'allowHtml' => false),
            'load_callback' => array(array('SyncCtoCallback', 'loadcallFileBlacklist')),
        ),
        'syncCto_folder_whitelist' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['folder_whitelist'],
            'inputType' => 'textwizard',
            'eval' => array('trailingSlash' => false, 'style' => 'width:320px', 'allowHtml' => false),
            'load_callback' => array(array('SyncCtoCallback', 'loadcallFolderWhitelist')),
        ),
        'syncCto_local_blacklist' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['local_blacklist'],
            'inputType' => 'checkboxWizard',
            'eval' => array('multiple' => true),
            'options_callback' => array('SyncCtoCallback', 'optioncallLocalConfig'),
            'load_callback' => array(array('SyncCtoCallback', 'loadcallLocalConfig')),
        ),
        'syncCto_table_hidden' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['table_hidden'],
            'inputType' => 'checkboxWizard',
            'eval' => array('multiple' => true),
            'options_callback' => array('SyncCtoCallback', 'optioncallHiddenTables'),
            'load_callback' => array(array('SyncCtoCallback', 'loadcallTableHidden')),
        ),
        'syncCto_table_list' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['table_list'],
            'inputType' => 'checkboxWizard',
            'eval' => array('multiple' => true),
            'options_callback' => array('SyncCtoCallback', 'optioncallTables'),
        ),
        'syncCto_seckey' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['seckey'],
            'inputType' => 'text',
            'eval' => array('minlength' => 32, 'maxlength' => 64),
            'save_callback' => array(array('SyncCtoCallback', 'savecallSecKey')),
        ),
        'syncCto_debug_log' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['debug_log'],
            'inputType' => 'checkbox',
        ),
        'syncCto_debug_filelist' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['debug_filelist'],
            'inputType' => 'checkbox',
        ),
        'syncCto_measurement_log' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['measurement_log'],
            'inputType' => 'checkbox',
        ),
    )
);
?>