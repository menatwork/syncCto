<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

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
 * @package    syncCto
 * @license    LGPL
 * @filesource
 */

/**
 * Extend default palette
 */
$GLOBALS['TL_DCA']['tl_user']['palettes']['extend'] = str_replace('disable', '{syncCto_legend},syncCto_clients,syncCto_clients_p,syncCto_sync_options;{syncCto_tables_legend},syncCto_tables;{account_legend},disable', $GLOBALS['TL_DCA']['tl_user']['palettes']['extend']);
$GLOBALS['TL_DCA']['tl_user']['palettes']['custom'] = str_replace('disable,', '{syncCto_legend},syncCto_clients,syncCto_clients_p,syncCto_sync_options;{syncCto_tables_legend},syncCto_tables;{account_legend},disable', $GLOBALS['TL_DCA']['tl_user']['palettes']['custom']);

/**
 * Add fields to tl_user
 */
$GLOBALS['TL_DCA']['tl_user']['fields']['syncCto_clients'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_user']['syncCto_clients'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'foreignKey' => 'tl_synccto_clients.title',
    'eval' => array('multiple' => true)
);

$GLOBALS['TL_DCA']['tl_user']['fields']['syncCto_clients_p'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_user']['syncCto_clients_p'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'options' => array('create', 'edit', 'copy', 'delete', 'showExtern', 'syncTo',  'syncFrom'),
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => array('multiple' => true)
);

$GLOBALS['TL_DCA']['tl_user']['fields']['syncCto_sync_options'] = array
    (
    'label'     => &$GLOBALS['TL_LANG']['tl_user']['syncCto_sync_options'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'reference' => &$GLOBALS['TL_LANG']['SYC'],
    'options_callback' => array('SyncCtoHelper', 'getFileSyncOptions'),
    'eval'      => array('multiple' => true)
);

$GLOBALS['TL_DCA']['tl_user']['fields']['syncCto_tables'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_user']['syncCto_tables'],
    'inputType' => 'checkboxWizard',
    'exclude' => true,
    'eval' => array('multiple' => true),
    'options_callback' => array('SyncCtoHelper', 'databaseTables'),
);

?>