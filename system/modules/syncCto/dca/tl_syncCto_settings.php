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

$GLOBALS['TL_DCA']['tl_syncCto_settings'] = array
(
    // Config
    'config' => array
    (
        'dataContainer'           => 'File',
        'closed'                  => true,
        'notEditable'             => true,
    ),
    // Palettes
    'palettes' => array
    (
        '__selector__'            => array('syncCto_custom_settings'),
        'default'                 => '{blacklist_legend},syncCto_folder_blacklist,syncCto_file_blacklist;{whitelist_legend},syncCto_folder_whitelist;{local_blacklist_legend},syncCto_local_blacklist;{tables_legend},syncCto_database_tables;{hidden_tables_legend:hide},syncCto_hidden_tables,syncCto_hidden_tables_placeholder;{custom_legend:hide},syncCto_debug_mode,syncCto_custom_settings,syncCto_auto_db_updater;'
    ),
    'subpalettes' => array
    (
        'syncCto_custom_settings' => 'syncCto_wait_timeout,syncCto_interactive_timeout,syncCto_db_query_limt',
    ),
    // Fields
    'fields' => array
    (
        'syncCto_folder_blacklist' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['folder_blacklist'],
            'exclude'             => true,
            'inputType'           => 'multiColumnWizard',
            'explanation'         => 'folder_blacklist',
            'eval' => array
            (
                'helpwizard'      => true,
                'tl_class'        => 'clr',
                'columnFields' => array
                (
                    'entries' => array
                    (
                        'label'           => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['folder_blacklist'],
                        'exclude'         => true,
                        'inputType'       => 'text',
                        'eval'            => array('style' => 'width:595px', 'allowHtml' => false)
                    )
                )
            ),
            'load_callback' => array
            (
                array('SyncCtoTableSettings', 'loadBlacklistFolder')
            ),
            'save_callback' => array
            (
                array('SyncCtoTableSettings', 'saveBlacklistFolder')
            )
        ),
        'syncCto_file_blacklist' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['file_blacklist'],
            'exclude'             => true,
            'inputType'           => 'multiColumnWizard',
            'eval' => array
            (
                'tl_class'        => 'clr',
                'columnFields' => array
                (
                    'entries' => array
                    (
                        'label'           => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['file_blacklist'],
                        'exclude'         => true,
                        'inputType'       => 'text',
                        'eval'            => array
                        (
                            'trailingSlash'      => false,
                            'style'              => 'width:595px',
                            'allowHtml'          => false
                        )
                    )
                )
            ),
            'load_callback' => array
            (
                array('SyncCtoTableSettings', 'loadBlacklistFile')
            ),
            'save_callback' => array
            (
                array('SyncCtoTableSettings', 'saveBlacklistFile')
            )
        ),
        'syncCto_folder_whitelist' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['folder_whitelist'],
            'exclude'             => true,
            'inputType'           => 'multiColumnWizard',
            'eval' => array
            (
                'tl_class'        => 'clr',
                'columnFields' => array
                (
                    'entries' => array
                    (
                        'label'           => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['folder_whitelist'],
                        'exclude'         => true,
                        'inputType'       => 'text',
                        'eval' => array
                        (
                            'trailingSlash'      => false,
                            'style'              => 'width:595px',
                            'allowHtml'          => false
                        ),

                    )
                )
            ),
            'load_callback' => array
            (
                array('SyncCtoTableSettings', 'loadWhitelistFolder')
            ),
            'save_callback' => array
            (
                array('SyncCtoTableSettings', 'saveWhitelistFolder')
            )
        ),
        'syncCto_local_blacklist' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['local_blacklist'],
            'inputType'           => 'checkboxWizard',
            'exclude'             => true,
            'eval'                => array('multiple' => true),
            'options_callback'    => array('SyncCtoTableSettings', 'localconfigEntries'),
            'load_callback'       => array
            (
                array('SyncCtoTableSettings', 'loadBlacklistLocalconfig')
            ),
            'save_callback' => array
            (
                array('SyncCtoTableSettings', 'saveBlacklistLocalconfig')
            )
        ),
        'syncCto_hidden_tables' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['hidden_tables'],
            'inputType'           => 'checkboxWizard',
            'exclude'             => true,
            'eval'                => array('multiple' => true),
            'options_callback'    => array('SyncCtoTableSettings', 'getHiddenTables'),
            'load_callback'       => array
            (
                array('SyncCtoTableSettings', 'loadTablesHidden')
            ),
            'save_callback' => array
            (
                array('SyncCtoTableSettings', 'saveTablesHidden')
            )
        ),
        'syncCto_hidden_tables_placeholder' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['hidden_tables_placeholder'],
            'exclude'             => true,
            'inputType'           => 'multiColumnWizard',
            'explanation'         => 'folder_blacklist',
            'eval' => array
            (
                'columnFields' => array
                (
                    'entries' => array
                    (
                        'label'           => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['hidden_tables_placeholder'],
                        'exclude'         => true,
                        'inputType'       => 'text',
                        'eval'            => array('trailingSlash' => false, 'style' => 'width:595px', 'allowHtml' => false)
                    )
                )
            )
        ),
        'syncCto_database_tables' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['database_tables'],
            'inputType'           => 'checkboxWizard',
            'exclude'             => true,
            'eval'                => array('multiple' => true),
            'options_callback'    => array('SyncCtoHelper', 'databaseTables')
        ),
        'syncCto_debug_mode' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['debug_mode'],
            'inputType'           => 'checkbox',
            'exclude'             => true
        ),
        'syncCto_custom_settings' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['custom_settings'],
            'inputType'           => 'checkbox',
            'exclude'             => true,
            'eval'                => array('submitOnChange' => true),
        ),
        'syncCto_wait_timeout' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['wait_timeout'],
            'inputType'           => 'text',
            'exclude'             => true,
            'load_callback'       => array
            (
                array('SyncCtoTableSettings', 'checkDefaulTimeoutValue')
            ),
            'eval' => array
            (
                'tl_class'        => 'w50',
                'rgxp'            => 'digit'
            ),
        ),
        'syncCto_interactive_timeout' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['interactive_timeout'],
            'inputType'           => 'text',
            'exclude'             => true,
            'load_callback'       => array
            (
                array('SyncCtoTableSettings', 'checkDefaulTimeoutValue')
            ),
            'eval' => array
            (
                'tl_class'        => 'w50',
                'rgxp'            => 'digit'
            ),
        ),
        'syncCto_db_query_limt' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['db_query_limt'],
            'inputType'           => 'text',
            'exclude'             => true,
            'load_callback'       => array
            (
                array('SyncCtoTableSettings', 'checkDefaulQueryValue')
            ),
            'eval' => array
            (
                'tl_class'        => 'w50',
                'rgxp'            => 'digit'
            ),
        ),
        'syncCto_auto_db_updater' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['auto_db_updater'],
            'inputType'           => 'checkbox',
            'exclude'             => true,
            'eval' => array
            (
                'tl_class'        => 'clr',
                'multiple'        => true,
            ),
            'reference'           => $GLOBALS['TL_LANG']['tl_syncCto_settings'],
            'options'   => array
            (
                'CREATE',
                'DROP',
                'ALTER_ADD',
                'ALTER_CHANGE',
                'ALTER_DROP',
            ),
        )
    )
);
