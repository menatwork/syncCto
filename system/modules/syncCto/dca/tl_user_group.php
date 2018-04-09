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
 * @author     Patrick Kahl <kahl.patrick@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2014-2018 MEN AT WORK.
 * @license    https://github.com/menatwork/syncCto/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

/**
 * Extend default palette
 */
$GLOBALS['TL_DCA']['tl_user_group']['palettes']['default'] = \str_replace('alexf;', '{syncCto_legend},syncCto_clients,syncCto_clients_p,syncCto_sync_options;{syncCto_tables_legend},syncCto_tables;{alexf_legend},alexf;', $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default']);

/**
 * Add fields to tl_user_group
 */
$GLOBALS['TL_DCA']['tl_user_group']['fields']['syncCto_clients'] = array
(
    'label'                       => &$GLOBALS['TL_LANG']['tl_user']['syncCto_clients'],
    'exclude'                     => true,
    'inputType'                   => 'checkbox',
    'foreignKey'                  => 'tl_synccto_clients.title',
    'eval'                        => array('multiple' => true),
    'sql'                         => 'blob NULL'
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['syncCto_clients_p'] = array
(
    'label'                       => &$GLOBALS['TL_LANG']['tl_user']['syncCto_clients_p'],
    'exclude'                     => true,
    'inputType'                   => 'checkbox',
    'options'                     => array('create', 'edit', 'copy', 'delete', 'showExtern', 'syncTo', 'syncFrom'),
    'reference'                   => &$GLOBALS['TL_LANG']['MSC'],
    'eval'                        => array('multiple' => true),
    'sql'                         => 'blob NULL'
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['syncCto_sync_options'] = array
(
    'label'                       => &$GLOBALS['TL_LANG']['tl_user']['syncCto_sync_options'],
    'exclude'                     => true,
    'inputType'                   => 'checkbox',
    'reference'                   => &$GLOBALS['TL_LANG']['SYC'],
    'options_callback'            => array('SyncCtoHelper', 'getFileSyncOptions'),
    'eval'                        => array('multiple' => true),
    'sql'                         => 'blob NULL'
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['syncCto_tables'] = array
(
    'label'                       => &$GLOBALS['TL_LANG']['tl_user']['syncCto_tables'],
    'inputType'                   => 'checkboxWizard',
    'exclude'                     => true,
    'eval'                        => array('multiple' => true),
    'options_callback'            => array('SyncCtoHelper', 'databaseTables'),
    'sql'                         => 'blob NULL'
);
