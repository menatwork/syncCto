<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2013 
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
        '__selector__' => array('syncCto_custom_settings'),
        'default' => '{blacklist_legend:hide},syncCto_folder_blacklist,syncCto_file_blacklist;{whitelist_legend:hide},syncCto_folder_whitelist;{local_blacklist_legend},syncCto_local_blacklist;{tables_legend},syncCto_database_tables;{hidden_tables_legend:hide},syncCto_hidden_tables;{custom_legend:hide},syncCto_debug_mode,syncCto_custom_settings;'
    ),
    'subpalettes' => array
        (
        'syncCto_custom_settings' => 'syncCto_wait_timeout,syncCto_interactive_timeout',
    ),
    // Fields
    'fields' => array( 
        'syncCto_folder_blacklist' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['folder_blacklist'],
            'exclude' => true,
            'inputType' => 'multiColumnWizard',
            'eval' => array(
                'tl_class' => 'clr',
                'columnFields' => array(
                    'entries' => array(
                        'label' => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['folder_blacklist'],
                        'exclude' => true,
                        'inputType' => 'text',                        
                        'eval' => array('trailingSlash' => false, 'style' => 'width:595px', 'allowHtml' => false),
                        
                    )
                )
            ),
            'load_callback' => array(array('tl_syncCto_settings', 'loadBlacklistFolder')),
            'save_callback' => array(array('tl_syncCto_settings', 'saveMcwEntries'))
        ),
        'syncCto_file_blacklist' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['file_blacklist'],
            'exclude' => true,
            'inputType' => 'multiColumnWizard',
            'eval' => array(
                'tl_class' => 'clr',
                'columnFields' => array(
                    'entries' => array(
                        'label' => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['file_blacklist'],
                        'exclude' => true,
                        'inputType' => 'text',                        
                        'eval' => array('trailingSlash' => false, 'style' => 'width:595px', 'allowHtml' => false),
                        
                    )
                )
            ),
            'load_callback' => array(array('tl_syncCto_settings', 'loadBlacklistFile')),
            'save_callback' => array(array('tl_syncCto_settings', 'saveMcwEntries'))
        ),
        'syncCto_folder_whitelist' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['folder_whitelist'],
            'exclude' => true,
            'inputType' => 'multiColumnWizard',
            'eval' => array(
                'tl_class' => 'clr',
                'columnFields' => array(
                    'entries' => array(
                        'label' => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['folder_whitelist'],
                        'exclude' => true,
                        'inputType' => 'text',                        
                        'eval' => array('trailingSlash' => false, 'style' => 'width:595px', 'allowHtml' => false),
                        
                    )
                )
            ),
            'load_callback' => array(array('tl_syncCto_settings', 'loadWhitelistFolder')),
            'save_callback' => array(array('tl_syncCto_settings', 'saveMcwEntries'))
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
        'syncCto_custom_settings' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['custom_settings'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'eval' => array('submitOnChange' => true),
        ),
        'syncCto_wait_timeout' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['wait_timeout'],
            'inputType' => 'text',
            'exclude' => true,
            'eval' => array('tl_class' => 'w50'),
        ),
        'syncCto_interactive_timeout' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_settings']['interactive_timeout'],
            'inputType' => 'text',
            'exclude' => true,
            'eval' => array('tl_class' => 'w50'),
        )
    )
);

/**
 * Class for syncCto settings
 */
class tl_syncCto_settings extends Backend
{

    protected $objSyncCtoHelper;
    protected static $instance = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();
    }  
    
    /**
     * Load localconfig entries
     * 
     * @return array 
     */
    public function localconfigEntries()
    {
        return $this->objSyncCtoHelper->loadConfigs(SyncCtoEnum::LOADCONFIG_KEYS_ONLY);
    }

    /**
     * Load blacklist localconfig entries
     * 
     * @param string $strValue
     * @return array 
     */
    public function loadBlacklistLocalconfig($strValue)
    {
        return $this->objSyncCtoHelper->getBlacklistLocalconfig();
    }

    /**
     * Load blacklist folder
     * 
     * @param string $strValue
     * @return array 
     */
    public function loadBlacklistFolder($strValue)
    {        
        $arrList = array();
        foreach($this->objSyncCtoHelper->getBlacklistFolder() AS $key => $value)
        {
            $arrList[$key] = array('entries' => $value);
        }
        return $arrList;
    }
    
    /**
     * Save blacklist entries
     * 
     * @param string $strValue
     * @return string
     */
    public function saveMcwEntries($strValue)
    {
        $arrList = deserialize($strValue); 
        $arrSaveList = array();
        
        if(is_array($arrSaveList) && count($arrList) > 0)
        {        
            foreach($arrList AS $key => $arrValue)
            {
                foreach($arrValue AS $value)
                {
                    $arrSaveList[$key] = $value;
                }
            }
            return serialize($arrSaveList);
        }
        return serialize(array());
    }

    /**
     * Load blacklist entries
     * 
     * @param string $strValue
     * @return array
     */
    public function loadBlacklistFile($strValue)
    {
        $arrList = array();
        foreach($this->objSyncCtoHelper->getBlacklistFile() AS $key => $value)
        {
            $arrList[$key] = array('entries' => $value);
        }
        return $arrList;
    }

    /**
     * Load whitelist folder
     * 
     * @param string $strValue
     * @return array 
     */
    public function loadWhitelistFolder($strValue)
    {
        
        $arrList = array();
        foreach($this->objSyncCtoHelper->getWhitelistFolder() AS $key => $value)
        {
            $arrList[$key] = array('entries' => $value);
        }
        return $arrList;        
    }

    /**
     * Load hidden tables
     * 
     * @param string $strValue
     * @return array
     */
    public function loadTablesHidden($strValue)
    {
        return $this->objSyncCtoHelper->getTablesHidden();
    }

}

?>