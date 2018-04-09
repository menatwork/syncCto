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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2014-2018 MEN AT WORK.
 * @license    https://github.com/menatwork/syncCto/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace SyncCto\Contao\Config;

use Contao\Backend;
use SyncCto\Config\Enum;
use SyncCto\Helper\Helper;

/**
 * Class for syncCto settings
 */
class TableSettings extends Backend
{
    protected $objSyncCtoHelper;
    protected static $instance = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->objSyncCtoHelper = Helper::getInstance();
    }

    public function getHiddenTables()
    {
        // Get the data from the helper.
        $arrTables = Helper::getInstance()->hiddenTables();
        $arrReturn = array();

        // Check if a regex remove this entry from the list.
        foreach($arrTables as $strTable)
        {
            if(Helper::getInstance()->isTableHiddenByPlaceholder($strTable))
            {
                $arrReturn[$strTable] = \sprintf($GLOBALS['TL_LANG']['tl_syncCto_settings']['hide_by_regex'], $strTable);
            }
            else
            {
                $arrReturn[$strTable] = $strTable;
            }
        }

        // Return the values.
        return $arrReturn;
    }

    /**
     * @param string $strValue     Values.
     *
     * @param string $strConfigKey Name of the config key.
     *
     * @return string Return the cleaned array.
     */
    protected function saveMcwEntries($strValue, $strConfigKey)
    {
        $arrConfigEntries = $GLOBALS['SYC_CONFIG'][$strConfigKey];
        $arrList          = \deserialize($strValue);
        $arrSaveList      = array();

        if (\is_array($arrSaveList) && \count($arrList) > 0)
        {
            foreach ($arrList AS $key => $arrValue)
            {
                foreach ($arrValue AS $value)
                {
                    if(!\in_array($value, $arrConfigEntries))
                    {
                        $arrSaveList[$key] = $value;
                    }
                }
            }

            return \serialize($arrSaveList);
        }

        return \serialize(array());
    }

    /**
     * @param string $strValue     Values.
     *
     * @param string $strConfigKey Name of the config key.
     *
     * @return string Return the cleaned array.
     */
    protected function saveEntries($strValue, $strConfigKey)
    {
        $arrConfigEntries = $GLOBALS['SYC_CONFIG'][$strConfigKey];
        $arrList          = \deserialize($strValue);
        $arrSaveList      = array();

        if (\is_array($arrSaveList) && \count($arrList) > 0)
        {
            foreach ($arrList AS $key => $strValue)
            {
                if(!\in_array($strValue, $arrConfigEntries))
                {
                    $arrSaveList[$key] = $strValue;
                }
            }

            return \serialize($arrSaveList);
        }

        return \serialize(array());
    }

    /**
     * Load localconfig entries
     *
     * @return array
     */
    public function localconfigEntries()
    {
        // Get entries from localconfig.
        $arrLocalconfig = $this->objSyncCtoHelper->loadConfigs(Enum::LOADCONFIG_KEYS_ONLY);

        // Load all fields for tl_settings.
        if (empty($GLOBALS['TL_DCA']['tl_settings']))
        {
            $this->loadDataContainer('tl_settings');
        }
        $arrDcaFields = \array_keys($GLOBALS['TL_DCA']['tl_settings']['fields']);

        // Merge all.
        $arrReturn = \array_keys(\array_flip(\array_merge($arrLocalconfig, $arrDcaFields)));

        // Sort.
        \natcasesort($arrReturn);

        return \array_values($arrReturn);
    }

    /**
     * Load blacklist localconfig entries
     *
     * @param string $strValue
     *
     * @return array
     */
    public function loadBlacklistLocalconfig($strValue)
    {
        return $this->objSyncCtoHelper->getBlacklistLocalconfig();
    }

    /**
     * Load blacklist localconfig entries
     *
     * @param string $strValue
     *
     * @return array
     */
    public function saveBlacklistLocalconfig($strValue)
    {
        return $this->saveEntries($strValue, 'local_blacklist');
    }

    /**
     * Load blacklist folder
     *
     * @param string $strValue
     *
     * @return array
     */
    public function loadBlacklistFolder($strValue)
    {
        $arrList = array();
        foreach ($this->objSyncCtoHelper->getBlacklistFolder() AS $key => $value)
        {
            $arrList[$key] = array('entries' => $value);
        }
        return $arrList;
    }

    /**
     * Save blacklist entries
     *
     * @param string $strValue
     *
     * @return string
     */
    public function saveBlacklistFolder($strValue)
    {
        return $this->saveMcwEntries($strValue, 'folder_blacklist');
    }

    /**
     * Load blacklist entries
     *
     * @param string $strValue
     *
     * @return array
     */
    public function loadBlacklistFile($strValue)
    {
        $arrList = array();
        foreach ($this->objSyncCtoHelper->getBlacklistFile() AS $key => $value)
        {
            $arrList[$key] = array('entries' => $value);
        }
        return $arrList;
    }

    /**
     * Save blacklist entries
     *
     * @param string $strValue
     *
     * @return string
     */
    public function saveBlacklistFile($strValue)
    {
        return $this->saveMcwEntries($strValue, 'file_blacklist');
    }

    /**
     * Load whitelist folder
     *
     * @param string $strValue
     *
     * @return array
     */
    public function loadWhitelistFolder($strValue)
    {
        $arrList = array();
        foreach ($this->objSyncCtoHelper->getWhitelistFolder() AS $key => $value)
        {
            $arrList[$key] = array('entries' => $value);
        }
        return $arrList;
    }

    /**
     * Save blacklist entries
     *
     * @param string $strValue
     *
     * @return string
     */
    public function saveWhitelistFolder($strValue)
    {
        return $this->saveMcwEntries($strValue, 'folder_whitelist');
    }

    /**
     * Load hidden tables
     *
     * @param string $strValue
     *
     * @return array
     */
    public function loadTablesHidden($strValue)
    {
        return $this->objSyncCtoHelper->getTablesHidden();
    }

    /**
     * Load blacklist localconfig entries
     *
     * @param string $strValue
     *
     * @return array
     */
    public function saveTablesHidden($strValue)
    {
        return $this->saveEntries($strValue, 'table_hidden');
    }

    /**
     * Check if we have a valid value for the timeout.
     *
     * @param mixed $strValue Value from DC.
     *
     * @return int Default Value or the value from DC.
     */
    public function checkDefaulTimeoutValue($strValue)
    {
        if (empty($strValue) || $strValue < 1)
        {
            return 28000;
        }

        return $strValue;
    }

    /**
     * Check if we have a valid value for the query limit.
     *
     * @param mixed $strValue Value from DC.
     *
     * @return int Default Value or the value from DC.
     */
    public function checkDefaulQueryValue($strValue)
    {
        if (empty($strValue) || $strValue < 1)
        {
            return 500;
        }

        return $strValue;
    }
}
