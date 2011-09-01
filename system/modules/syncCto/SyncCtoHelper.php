<?php

if (!defined('TL_ROOT'))
    die('You cannot access this file directly!');

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

class SyncCtoHelper extends Backend
{

    // instance
    protected static $instance = null;
    //-----
    protected $BackendUser;

    protected function __construct()
    {
        $this->BackendUser = BackendUser::getInstance();
        parent::__construct();
    }

    /**
     *
     * @return SyncCtoHelper 
     */
    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new SyncCtoHelper();

        return self::$instance;
    }

    /* -------------------------------------------------------------------------
     * Configuration merge functions
     */

    private function mergerConfigs($arrLocalconfig, $arrSyncCtoConfig)
    {
        if (is_array($arrLocalconfig) && is_array($arrSyncCtoConfig))
        {
            foreach ($arrLocalconfig as $value)
            {
                if (in_array($value, $arrSyncCtoConfig))
                    continue;

                if ($value == "")
                    continue;

                $arrSyncCtoConfig[] = $value;
            }

            return $arrSyncCtoConfig;
        }
        else if (!is_array($arrLocalconfig) && is_array($arrSyncCtoConfig))
        {
            return $arrSyncCtoConfig;
        }
        else
        {
            return array();
        }
    }

    //Configuration Merge Part 
    public function getBlacklistFolder()
    {
        $arrLocalconfig = deserialize($GLOBALS['TL_CONFIG']['syncCto_folder_blacklist']);
        $arrSyncCtoConfig = $GLOBALS['SYC_CONFIG']['folder_blacklist'];
        return $this->mergerConfigs($arrLocalconfig, $arrSyncCtoConfig);
    }

    public function getWhitelistFolder()
    {
        $arrLocalconfig = deserialize($GLOBALS['TL_CONFIG']['syncCto_folder_whitelist']);
        $arrSyncCtoConfig = $GLOBALS['SYC_CONFIG']['folder_whitelist'];
        return $this->mergerConfigs($arrLocalconfig, $arrSyncCtoConfig);
    }

    public function getBlacklistFile()
    {
        $arrLocalconfig = deserialize($GLOBALS['TL_CONFIG']['syncCto_file_blacklist']);
        $arrSyncCtoConfig = $GLOBALS['SYC_CONFIG']['file_blacklist'];
        return $this->mergerConfigs($arrLocalconfig, $arrSyncCtoConfig);
    }

    public function getBlacklistLocalconfig()
    {
        $arrLocalconfig = deserialize($GLOBALS['TL_CONFIG']['syncCto_local_blacklist']);
        $arrSyncCtoConfig = $GLOBALS['SYC_CONFIG']['local_blacklist'];
        return $this->mergerConfigs($arrLocalconfig, $arrSyncCtoConfig);
    }

    public function getTablesHidden()
    {
        $arrLocalconfig = deserialize($GLOBALS['TL_CONFIG']['syncCto_table_hidden']);
        $arrSyncCtoConfig = $GLOBALS['SYC_CONFIG']['table_hidden'];
        return $this->mergerConfigs($arrLocalconfig, $arrSyncCtoConfig);
    }

    public function loadConfig($intTyp = 1)
    {
        if ($intTyp != SyncCtoEnum::LOADCONFIG_KEYS_ONLY && $intTyp != SyncCtoEnum::LOADCONFIG_KEY_VALUE)
            throw new Exception("Unknow typ for " . __CLASS__ . " in function " . __FUNCTION__);

        // Read the local configuration file
        $strMode = 'top';
        $resFile = fopen(TL_ROOT . '/system/config/localconfig.php', 'rb');

        $arrData = array();

        while (!feof($resFile))
        {
            $strLine = fgets($resFile);
            $strTrim = trim($strLine);

            if ($strTrim == '?>')
            {
                continue;
            }

            if ($strTrim == '### INSTALL SCRIPT START ###')
            {
                $strMode = 'data';
                continue;
            }

            if ($strTrim == '### INSTALL SCRIPT STOP ###')
            {
                $strMode = 'bottom';
                continue;
            }

            if ($strMode == 'top')
            {
                $this->strTop .= $strLine;
            }
            elseif ($strMode == 'bottom')
            {
                $this->strBottom .= $strLine;
            }
            elseif ($strTrim != '')
            {
                $arrChunks = array_map('trim', explode('=', $strLine, 2));

                if ($intTyp == SyncCtoEnum::LOADCONFIG_KEYS_ONLY)
                {
                    $arrData[] = str_replace(array("$", "GLOBALS['TL_CONFIG']['", "']"), array("", "", ""), $arrChunks[0]);
                }
                else if ($intTyp == SyncCtoEnum::LOADCONFIG_KEY_VALUE)
                {
                    $key = str_replace(array("$", "GLOBALS['TL_CONFIG']['", "']"), array("", "", ""), $arrChunks[0]);
                    $arrData[$key] = $GLOBALS['TL_CONFIG'][$key];
                }
            }
        }

        fclose($resFile);

        return $arrData;
    }

    public function buildPath()
    {
        $arrPath = func_get_args();

        if (count($arrPath) == 0 || $arrPath == null || $arrPath == "")
            return TL_ROOT;

        $strVar = "";

        foreach ($arrPath as $itPath)
        {
            $itPath = str_replace(TL_ROOT, "", $itPath);
            $itPath = explode("/", $itPath);

            foreach ($itPath as $itFolder)
            {
                if ($itFolder == "" || $itFolder == "." || $itFolder == "..")
                    continue;

                $strVar .= "/" . $itFolder;
            }
        }

        return TL_ROOT . $strVar;
    }

    public function buildPathWoTL()
    {
        $arrPath = func_get_args();

        if (count($arrPath) == 0 || $arrPath == null || $arrPath == "")
            return "";

        $strVar = "";

        foreach ($arrPath as $itPath)
        {
            $itPath = str_replace(TL_ROOT, "", $itPath);
            $itPath = explode("/", $itPath);

            foreach ($itPath as $itFolder)
            {
                if ($itFolder == "" || $itFolder == "." || $itFolder == "..")
                    continue;

                $strVar .= "/" . $itFolder;
            }
        }

        return preg_replace("/^\//i", "", $strVar);
    }

    /* -------------------------------------------------------------------------
     * Ext. Session
     */

    /**
     * Extended the session with typ casting for array, boolean and mix types.
     * 
     * @param string $strName
     * @param mixed $mixVar 
     */
    public function setSession($strName, $mixVar)
    {
        if (is_array($mixVar))
            $this->Session->set($strName, serialize($mixVar));

        elseif ($mixVar === 0)
            return (int) 0;

        else
            $this->Session->set($strName, $mixVar);
    }

    /**
     * Extended the session with typ casting for array, boolean and mix types.
     * 
     * @param string $strName
     * @return mixed 
     */
    public function getSession($strName)
    {
        $mixVar = $this->Session->get($strName);

        if ($mixVar === FALSE || $mixVar == "b:0;")
            return FALSE;

        else if ($mixVar === TRUE || $mixVar == "b:1;")
            return TRUE;

        elseif (is_array(deserialize($mixVar)))
            return deserialize($mixVar);

        elseif ($mixVar === 0)
            return (int) 0;

        else
            return $mixVar;
    }

}

?>