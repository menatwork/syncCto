<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2013 
 * @package    syncCto
 * @license    GNU/LGPL 
 * @filesource
 */

/**
 * Extend default palette
 */
$GLOBALS['TL_DCA']['tl_user']['palettes']['extend'] = str_replace('disable', '{syncCto_legend},syncCto_clients,syncCto_clients_p,syncCto_sync_options;{syncCto_tables_legend},syncCto_tables;{account_legend},disable', $GLOBALS['TL_DCA']['tl_user']['palettes']['extend']);
$GLOBALS['TL_DCA']['tl_user']['palettes']['custom'] = str_replace('disable,', '{syncCto_legend},syncCto_clients,syncCto_clients_p,syncCto_sync_options;{syncCto_tables_legend},syncCto_tables;{account_legend},disable', $GLOBALS['TL_DCA']['tl_user']['palettes']['custom']);
$GLOBALS['TL_DCA']['tl_user']['palettes']['login']  = str_replace('useCE', 'useCE,syncCto_useTranslatedNames', $GLOBALS['TL_DCA']['tl_user']['palettes']['login']);

/**
 * Add fields to tl_user
 */
$GLOBALS['TL_DCA']['tl_user']['fields']['syncCto_clients'] = array
(
    'label'                       => &$GLOBALS['TL_LANG']['tl_user']['syncCto_clients'],
    'exclude'                     => true,
    'inputType'                   => 'checkbox',
    'foreignKey'                  => 'tl_synccto_clients.title',
    'eval'                        => array('multiple' => true)
);

$GLOBALS['TL_DCA']['tl_user']['fields']['syncCto_clients_p'] = array
(
    'label'                       => &$GLOBALS['TL_LANG']['tl_user']['syncCto_clients_p'],
    'exclude'                     => true,
    'inputType'                   => 'checkbox',
    'options'                     => array('create', 'edit', 'copy', 'delete', 'showExtern', 'syncTo',  'syncFrom'),
    'reference'                   => &$GLOBALS['TL_LANG']['MSC'],
    'eval'                        => array('multiple' => true)
);

$GLOBALS['TL_DCA']['tl_user']['fields']['syncCto_sync_options'] = array
(
    'label'                       => &$GLOBALS['TL_LANG']['tl_user']['syncCto_sync_options'],
    'exclude'                     => true,
    'inputType'                   => 'checkbox',
    'reference'                   => &$GLOBALS['TL_LANG']['SYC'],
    'options_callback'            => array('SyncCtoHelper', 'getFileSyncOptions'),
    'eval'                        => array('multiple' => true)
);

$GLOBALS['TL_DCA']['tl_user']['fields']['syncCto_tables'] = array
(
    'label'                       => &$GLOBALS['TL_LANG']['tl_user']['syncCto_tables'],
    'inputType'                   => 'checkboxWizard',
    'exclude'                     => true,
    'eval'                        => array('multiple' => true),
    'options_callback'            => array('SyncCtoHelper', 'databaseTables'),
);

$GLOBALS['TL_DCA']['tl_user']['fields']['syncCto_useTranslatedNames'] = array
(
    'label'                       => &$GLOBALS['TL_LANG']['tl_user']['syncCto_useTranslatedNames'],
    'exclude'                     => true,
    'inputType'                   => 'checkbox',
    'eval'                        => array('tl_class' => 'w50')
);