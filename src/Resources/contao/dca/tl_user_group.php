<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL 
 * @filesource
 */

/**
 * Extend default palette
 */
$parts = explode(';', $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default']);
if(is_array($parts)){
    \array_insert($parts, (count($parts) - 1), [
        '{syncCto_legend},syncCto_clients,syncCto_clients_p,syncCto_sync_options',
        '{syncCto_tables_legend},syncCto_tables'
    ]);
    $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default'] = implode(';', $parts);
}

/**
 * Add fields to tl_user_group
 */
$GLOBALS['TL_DCA']['tl_user_group']['fields']['syncCto_clients'] = array
(
    'label'                       => &$GLOBALS['TL_LANG']['tl_user']['syncCto_clients'],
    'exclude'                     => true,
    'inputType'                   => 'checkbox',
    'foreignKey'                  => 'tl_synccto_clients.title',
    'eval'                        => array('multiple' => true)
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['syncCto_clients_p'] = array
(
    'label'                       => &$GLOBALS['TL_LANG']['tl_user']['syncCto_clients_p'],
    'exclude'                     => true,
    'inputType'                   => 'checkbox',
    'options'                     => array('create', 'edit', 'copy', 'delete', 'showExtern', 'syncTo', 'syncFrom'),
    'reference'                   => &$GLOBALS['TL_LANG']['MSC'],
    'eval'                        => array('multiple' => true)
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['syncCto_sync_options'] = array
(
    'label'                       => &$GLOBALS['TL_LANG']['tl_user']['syncCto_sync_options'],
    'exclude'                     => true,
    'inputType'                   => 'checkbox',
    'reference'                   => &$GLOBALS['TL_LANG']['SYC'],
    'options_callback'            => array('SyncCtoHelper', 'getFileSyncOptions'),
    'eval'                        => array('multiple' => true)
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['syncCto_tables'] = array
(
    'label'                       => &$GLOBALS['TL_LANG']['tl_user']['syncCto_tables'],
    'inputType'                   => 'checkboxWizard',
    'exclude'                     => true,
    'eval'                        => array('multiple' => true),
    'options_callback'            => array('SyncCtoHelper', 'databaseTables'),
);
