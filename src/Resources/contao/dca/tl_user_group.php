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
if (is_array($parts)) {
    \Contao\ArrayUtil::arrayInsert($parts, (count($parts) - 1), [
        '{syncCto_legend},syncCto_clients,syncCto_clients_p,syncCto_sync_options,syncCto_force_dbafs_overwrite,syncCto_hide_auto_sync',
        '{syncCto_tables_legend},syncCto_tables'
    ]);
    $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default'] = implode(';', $parts);
}

/**
 * Add fields to tl_user_group
 */
$GLOBALS['TL_DCA']['tl_user_group']['fields']['syncCto_force_dbafs_overwrite'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_user_group']['syncCto_force_dbafs_overwrite'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'sql'       => 'varchar(32)'
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['syncCto_hide_auto_sync'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_user_group']['syncCto_hide_auto_sync'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'sql'       => 'varchar(32)'
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['syncCto_clients'] = [
    'label'      => &$GLOBALS['TL_LANG']['tl_user_group']['syncCto_clients'],
    'exclude'    => true,
    'inputType'  => 'checkbox',
    'foreignKey' => 'tl_synccto_clients.title',
    'eval'       => ['multiple' => true],
    'sql'        => 'blob NULL'
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['syncCto_clients_p'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_user_group']['syncCto_clients_p'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'options'   => ['create', 'edit', 'copy', 'delete', 'showExtern', 'syncTo', 'syncFrom'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval'      => ['multiple' => true],
    'sql'       => 'blob NULL'
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['syncCto_sync_options'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_user_group']['syncCto_sync_options'],
    'exclude'          => true,
    'inputType'        => 'checkbox',
    'reference'        => &$GLOBALS['TL_LANG']['SYC'],
    'options_callback' => ['SyncCtoHelper', 'getFileSyncOptions'],
    'eval'             => ['multiple' => true],
    'sql'              => 'blob NULL'
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['syncCto_tables'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_user_group']['syncCto_tables'],
    'inputType'        => 'checkboxWizard',
    'exclude'          => true,
    'options_callback' => ['SyncCtoHelper', 'databaseTables'],
    'eval'             => ['multiple' => true],
    'sql'              => 'blob NULL'
];
