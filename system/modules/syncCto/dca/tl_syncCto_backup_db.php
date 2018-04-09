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

$GLOBALS['TL_DCA']['tl_syncCto_backup_db'] = array
(
    // Config
    'config' => array
    (
        'dataContainer'           => 'General',
        'disableSubmit'           => false
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
    'palettes' => array
    (
        'default'                 => '{table_recommend_legend},database_tables_recommended;{table_none_recommend_legend:hide},database_tables_none_recommended;'
    ),
    // Fields
    'fields' => array
    (
        'database_tables_recommended' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['database_tables_recommended'],
            'inputType'           => 'checkbox',
            'exclude'             => true,
            'eval'                => array('multiple' => true)
        ),
        'database_tables_none_recommended' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['database_tables_none_recommended'],
            'inputType'           => 'checkbox',
            'exclude'             => true,
            'eval'                => array('multiple' => true)
        )
    )
);
