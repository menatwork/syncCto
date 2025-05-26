<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_syncCto_restore_file'] = array
(
    // Config
    'config'     => array
    (
        'dataContainer' => 'General',
        'disableSubmit' => false
    ),
    'dca_config' => array
    (
        'data_provider' => array
        (
            'default' => array
            (
                'class'  => 'ContaoCommunityAlliance\DcGeneral\Data\NoOpDataProvider',
                'source' => 'tl_syncCto_restore_file'
            ),
        ),
    ),
    // Palettes
    'palettes'   => array
    (
        'default' => '{filelist_legend},filelist;',
    ),
    // Fields
    'fields'     => array
    (
        'filelist' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_syncCto_restore_file']['filelist'],
            'inputType' => 'fileTree',
            'eval'      => array
            (
                'files'      => true,
                'filesOnly'  => true,
                'fieldType'  => 'radio',
                'path'       => 'files/syncCto_backups/files',
                'extensions' => 'rar,zip'
            ),
        ),
    )
);
