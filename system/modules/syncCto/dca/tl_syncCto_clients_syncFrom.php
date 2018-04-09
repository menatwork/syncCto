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

$GLOBALS['TL_DCA']['tl_syncCto_clients_syncFrom'] = array
(
    // Config
    'config'      => array
    (
        'dataContainer' => 'General',
        'disableSubmit' => false
    ),
    // Palettes
    'palettes'    => array
    (
        '__selector__' => array('systemoperations_check'),
        'default'      => '{sync_legend},sync_options;{table_legend},database_check;{systemoperations_legend:hide},systemoperations_check,attentionFlag;',
    ),
    // Sub Palettes
    'subpalettes' => array
    (
        'systemoperations_check' => 'systemoperations_maintenance',
        'database_check'         => 'tl_files_check',
    ),
    // Fields
    'fields'      => array
    (
        'sync_options'                 => array
        (
            'label'            => $GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['sync_options'],
            'inputType'        => 'checkbox',
            'exclude'          => true,
            'reference'        => &$GLOBALS['TL_LANG']['SYC'],
            'options_callback' => array('SyncCtoHelper', 'getFileSyncOptions'),
            'eval'             => array
            (
                'multiple' => true
            ),
        ),
        'database_check'               => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['database_check'],
            'inputType' => 'checkbox',
            'exclude'   => true,
            'eval'      => array
            (
                'submitOnChange' => 'true'
            )
        ),
        'tl_files_check'               => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['tl_files_check'],
            'inputType' => 'checkbox',
            'exclude'   => true
        ),
        'systemoperations_check'       => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['systemoperations_check'],
            'inputType' => 'checkbox',
            'exclude'   => true,
            'eval'      => array
            (
                'submitOnChange' => true,
                'tl_class'       => 'clr'
            ),
        ),
        'systemoperations_maintenance' => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['systemoperations_maintenance'],
            'inputType'        => 'checkbox',
            'exclude'          => true,
            'reference'        => &$GLOBALS['TL_LANG']['SYC'],
            'eval'             => array
            (
                'multiple' => true,
                'checkAll' => true
            ),
            'options_callback' => array('SyncCtoHelper', 'getMaintenanceOptions'),
        ),
        'attentionFlag'                => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['attention_flag'],
            'inputType' => 'checkbox',
            'exclude'   => true
        )
    )
);
