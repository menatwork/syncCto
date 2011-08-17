<?php

if ( !defined('TL_ROOT') )
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
$GLOBALS['TL_DCA']['tl_syncCto_clients_syncFrom'] = array(
    // Config
    'config' => array
        (
        'dataContainer' => 'File',
        'closed' => true,
    ),
    // Palettes
    'palettes' => array
        (
        'default' => '{sync_legend},sync_type;{confirm_db_import_legend},confirm_db_import',
    ),
    // Fields
    'fields' => array(
        'sync_type' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['sync_type'],
            'inputType' => 'select',
            'exclude' => true,
            'eval' => array('helpwizard' => true),
            'reference' => &$GLOBALS['TL_LANG']['SYC'],
            'options_callback' => array('SyncCtoCallback', 'getSyncType'),
        ),
        'confirm_db_import' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['confirm_db_import'],
            'inputType' => 'checkbox',
            'exclude' => true,
        )
    )
);
?>