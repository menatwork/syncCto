<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_syncCto_backup_db'] = array
(
    // Config
    'config'     => array
    (
        'dataContainer'    => \ContaoCommunityAlliance\DcGeneral\DC\General::class,
        'disableSubmit'    => false,
        'enableVersioning' => false
    ),
    'dca_config' => array
    (
        'data_provider' => array
        (
            'default' => array
            (
                'class'  => 'ContaoCommunityAlliance\DcGeneral\Data\NoOpDataProvider',
                'source' => 'tl_syncCto_backup_db'
            ),
        ),
    ),
    // Palettes
    'palettes'   => array
    (
        'default' => '{table_recommend_legend},database_tables_recommended;{table_none_recommend_legend:hide},database_tables_none_recommended;'
    ),
    // Fields
    'fields'     => array
    (
        'database_tables_recommended'      => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['database_tables_recommended'],
            'inputType' => 'checkbox',
            'exclude'   => true,
            'eval'      => array('multiple' => true)
        ),
        'database_tables_none_recommended' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['database_tables_none_recommended'],
            'inputType' => 'checkbox',
            'exclude'   => true,
            'eval'      => array('multiple' => true)
        )
    )
);
