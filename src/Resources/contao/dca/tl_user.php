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
$GLOBALS['TL_DCA']['tl_user']['palettes']['login'] = str_replace(
    'useCE', 'useCE,syncCto_useTranslatedNames',
    $GLOBALS['TL_DCA']['tl_user']['palettes']['login']
);

foreach (['extend', 'custom'] as $palette) {
    $parts = explode(';', $GLOBALS['TL_DCA']['tl_user']['palettes'][$palette]);
    if (is_array($parts)) {
        \array_insert($parts, (count($parts) - 1), [
            '{syncCto_legend},syncCto_clients,syncCto_clients_p,syncCto_sync_options,syncCto_force_dbafs_overwrite,syncCto_hide_auto_sync',
            '{syncCto_tables_legend},syncCto_tables'
        ]);
        $GLOBALS['TL_DCA']['tl_user']['palettes'][$palette] = implode(';', $parts);
    }
}

/**
 * Add fields to tl_user
 */
$GLOBALS['TL_DCA']['tl_user']['fields']['syncCto_force_dbafs_overwrite'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_user']['syncCto_force_dbafs_overwrite'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'sql'       => 'varchar(32)'
];

$GLOBALS['TL_DCA']['tl_user']['fields']['syncCto_hide_auto_sync'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_user']['syncCto_hide_auto_sync'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'sql'       => 'varchar(32)'
];

$GLOBALS['TL_DCA']['tl_user']['fields']['syncCto_clients'] = array
(
    'label'      => &$GLOBALS['TL_LANG']['tl_user']['syncCto_clients'],
    'exclude'    => true,
    'inputType'  => 'checkbox',
    'foreignKey' => 'tl_synccto_clients.title',
    'eval'       => array('multiple' => true),
    'sql'        => 'blob NULL'
);

$GLOBALS['TL_DCA']['tl_user']['fields']['syncCto_clients_p'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_user']['syncCto_clients_p'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'options'   => array('create', 'edit', 'copy', 'delete', 'showExtern', 'syncTo', 'syncFrom'),
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval'      => array('multiple' => true),
    'sql'       => 'blob NULL'
);

$GLOBALS['TL_DCA']['tl_user']['fields']['syncCto_sync_options'] = array
(
    'label'            => &$GLOBALS['TL_LANG']['tl_user']['syncCto_sync_options'],
    'exclude'          => true,
    'inputType'        => 'checkbox',
    'reference'        => &$GLOBALS['TL_LANG']['SYC'],
    'options_callback' => array('SyncCtoHelper', 'getFileSyncOptions'),
    'eval'             => array('multiple' => true),
    'sql'              => 'blob NULL'
);

$GLOBALS['TL_DCA']['tl_user']['fields']['syncCto_tables'] = array
(
    'label'            => &$GLOBALS['TL_LANG']['tl_user']['syncCto_tables'],
    'inputType'        => 'checkboxWizard',
    'exclude'          => true,
    'eval'             => array('multiple' => true),
    'options_callback' => array('SyncCtoHelper', 'databaseTables'),
    'sql'              => 'blob NULL'
);

$GLOBALS['TL_DCA']['tl_user']['fields']['syncCto_useTranslatedNames'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_user']['syncCto_useTranslatedNames'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => array('tl_class' => 'w50'),
    'sql'       => 'char(1) NOT NULL default \'\''
);

// Overwrite the session and make it a little bit bigger.
if(
    !empty($GLOBALS['TL_DCA']['tl_user']['fields']['session']['sql'])
    && $GLOBALS['TL_DCA']['tl_user']['fields']['session']['sql'] == "blob NULL"
){
    $GLOBALS['TL_DCA']['tl_user']['fields']['session']['sql'] =  "mediumblob NULL";
}
