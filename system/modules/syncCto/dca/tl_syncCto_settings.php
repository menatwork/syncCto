<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  MEN AT WORK 2011
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_syncCto_settings'] = array(
    // Config
    'config' => array
        (
        'dataContainer' => 'File',
        'closed' => true,
        'notEditable' => true,
    ),
    // Palettes
    'palettes' => array
        (
        'default' => '{blacklist_legend:hide},syncCto_folder_blacklist,syncCto_file_blacklist;{whitelist_legend:hide},syncCto_folder_whitelist;{local_blacklist_legend},syncCto_local_blacklist;{hidden_tables_legend:hide},syncCto_hidden_tables;{tables_legend},syncCto_database_tables;{debug_legend},syncCto_debug_mode;'
    ),
    // Fields
    'fields' => array(
        'syncCto_folder_blacklist' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['folder_blacklist'],
            'inputType' => 'textwizard',
            'exclude' => true,
            'eval' => array('trailingSlash' => false, 'style' => 'width:595px', 'allowHtml' => false),
            'load_callback' => array(array('tl_syncCto_settings', 'loadBlacklistFolder')),
        ),
        'syncCto_file_blacklist' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['file_blacklist'],
            'inputType' => 'textwizard',
            'exclude' => true,
            'eval' => array('trailingSlash' => false, 'style' => 'width:595px', 'allowHtml' => false),
            'load_callback' => array(array('tl_syncCto_settings', 'loadBlacklistFile')),
        ),
        'syncCto_folder_whitelist' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['folder_whitelist'],
            'inputType' => 'textwizard',
            'exclude' => true,
            'eval' => array('trailingSlash' => false, 'style' => 'width:595px', 'allowHtml' => false),
            'load_callback' => array(array('tl_syncCto_settings', 'loadWhitelistFolder')),
        ),
        'syncCto_local_blacklist' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['local_blacklist'],
            'inputType' => 'checkboxWizard',
            'exclude' => true,
            'eval' => array('multiple' => true),
            'options_callback' => array('tl_syncCto_settings', 'localconfigEntries'),
            'load_callback' => array(array('tl_syncCto_settings', 'loadBlacklistLocalconfig')),
        ),
        'syncCto_hidden_tables' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['hidden_tables'],
            'inputType' => 'checkboxWizard',
            'exclude' => true,
            'eval' => array('multiple' => true),
            'options_callback' => array('SyncCtoHelper', 'hiddenTables'),
            'load_callback' => array(array('tl_syncCto_settings', 'loadTablesHidden')),
        ),
        'syncCto_database_tables' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['database_tables'],
            'inputType' => 'checkboxWizard',
            'exclude' => true,
            'eval' => array('multiple' => true),
            'options_callback' => array('SyncCtoHelper', 'databaseTables'),
        ),
        'syncCto_debug_mode' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['debug_mode'],
            'inputType' => 'checkbox',
            'exclude' => true,
        ),
    )
);

class tl_syncCto_settings extends Backend
{

    protected $objSyncCtoHelper;
    protected static $instance = null;

    public function __construct()
    {
        parent::__construct();

        // Import SyncCto classes
        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();
    }

    /* -------------------------------------------------------------------------
     * Load call backs for syncCto settings
     */

    public function loadBlacklistLocalconfig($strValue)
    {
        return $this->objSyncCtoHelper->getBlacklistLocalconfig();
    }

    public function loadBlacklistFolder($strValue)
    {
        return $this->objSyncCtoHelper->getBlacklistFolder();
    }

    public function loadBlacklistFile($strValue)
    {
        return $this->objSyncCtoHelper->getBlacklistFile();
    }

    public function loadWhitelistFolder($strValue)
    {
        return $this->objSyncCtoHelper->getWhitelistFolder();
    }

    public function loadTablesHidden($strValue)
    {
        return $this->objSyncCtoHelper->getTablesHidden();
    }

    /* -------------------------------------------------------------------------
     * Load options for list
     */

    public function localconfigEntries()
    {      
        return $this->objSyncCtoHelper->loadConfigs(SyncCtoEnum::LOADCONFIG_KEYS_ONLY);
    }

}

?>