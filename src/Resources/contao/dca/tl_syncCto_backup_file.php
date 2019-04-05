<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL 
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_syncCto_backup_file'] = array
(
    // Config
    'config' => array
    (
        'dataContainer'           => 'General',
        'disableSubmit'           => false,
        'enableVersioning'        => false
    ),
    'dca_config' => array
    (
        'data_provider' => array
        (
            'default' => array
            (
                'class'  => 'ContaoCommunityAlliance\DcGeneral\Data\NoOpDataProvider',
                'source' => 'tl_syncCto_backup_file'
            ),
        ),
    ),
    // Palettes
    'palettes' => array
    (
        '__selector__'            => array('user_files'),
        'default'                 => '{filelist_legend},core_files,user_files;{backup_legend},backup_name;',
    ),
    // Sub Palettes
    'subpalettes' => array
    (
        'user_files' => 'filelist'
    ),
    // Fields
    'fields'     => array
    (
        'core_files' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['core_files'],
            'inputType'           => 'checkbox',
            'exclude'             => true
        ),
        'user_files' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['user_files'],
            'inputType'           => 'checkbox',
            'exclude'             => true,
            'eval'                => array('submitOnChange' => true),
        ),
        'filelist' => array
            (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['filelist'],
            'exclude'             => true,
            'inputType'           => 'fileTree',
            'eval' => array
            (
                'fieldType'       => 'checkbox',
                'files'           => true,
                'filesOnly'       => false,
                'tl_class'        => 'clr',
                'multiple'        => true
            ),
        ),
        'backup_name' => array
            (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['backup_name'],
            'inputType'           => 'text',
            'exclude'             => true,
            'eval'                => array('maxlength' => 32),
        ),
    )
);
