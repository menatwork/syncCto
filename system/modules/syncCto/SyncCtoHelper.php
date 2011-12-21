<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

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

/**
 * Helper class for syncCto. Callbackfunction, small global helper function.
 */
class SyncCtoHelper extends Backend
{
    /* -------------------------------------------------------------------------
     * Vars
     */

    // instance
    protected static $instance = null;
    protected $BackendUser;

    /* -------------------------------------------------------------------------
     * Core
     */

    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->BackendUser = BackendUser::getInstance();

        parent::__construct();
    }

    /**
     * Returns the SyncCtoHelper
     * @return SyncCtoHelper
     */
    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new SyncCtoHelper();
        }

        return self::$instance;
    }

    /* -------------------------------------------------------------------------
     * Config
     */

    /**
     * Configuration merge functions
     * 
     * @param array $arrLocalconfig
     * @param array $arrSyncCtoConfig
     * @return array
     */
    private function mergeConfigs($arrLocalconfig, $arrSyncCtoConfig)
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

    /**
     * Get localconfig entries
     * 
     * @param int $intTyp
     * @return string
     */
    public function loadConfigs($intTyp = 1)
    {
        if ($intTyp != SyncCtoEnum::LOADCONFIG_KEYS_ONLY && $intTyp != SyncCtoEnum::LOADCONFIG_KEY_VALUE)
        {
            throw new Exception("Unknow typ for " . __CLASS__ . " in function " . __FUNCTION__);
        }

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

    /* -------------------------------------------------------------------------
     * Black and Whitelists
     */

    public function getBlacklistFolder()
    {
        $arrLocalconfig = deserialize($GLOBALS['TL_CONFIG']['syncCto_folder_blacklist']);
        $arrSyncCtoConfig = $GLOBALS['SYC_CONFIG']['folder_blacklist'];
        return $this->mergeConfigs($arrLocalconfig, $arrSyncCtoConfig);
    }

    public function getWhitelistFolder()
    {
        $arrLocalconfig = deserialize($GLOBALS['TL_CONFIG']['syncCto_folder_whitelist']);
        $arrSyncCtoConfig = $GLOBALS['SYC_CONFIG']['folder_whitelist'];
        return $this->mergeConfigs($arrLocalconfig, $arrSyncCtoConfig);
    }

    public function getBlacklistFile()
    {
        $arrLocalconfig = deserialize($GLOBALS['TL_CONFIG']['syncCto_file_blacklist']);
        $arrSyncCtoConfig = $GLOBALS['SYC_CONFIG']['file_blacklist'];
        return $this->mergeConfigs($arrLocalconfig, $arrSyncCtoConfig);
    }

    public function getBlacklistLocalconfig()
    {
        $arrLocalconfig = deserialize($GLOBALS['TL_CONFIG']['syncCto_local_blacklist']);
        $arrSyncCtoConfig = $GLOBALS['SYC_CONFIG']['local_blacklist'];
        return $this->mergeConfigs($arrLocalconfig, $arrSyncCtoConfig);
    }

    public function getTablesHidden()
    {
        $arrLocalconfig = deserialize($GLOBALS['TL_CONFIG']['syncCto_hidden_tables']);
        $arrSyncCtoConfig = $GLOBALS['SYC_CONFIG']['table_hidden'];
        return $this->mergeConfigs($arrLocalconfig, $arrSyncCtoConfig);
    }

    /**
     * Standardize path for folder
     * 
     * @return string the normalized path as String
     */
    public function standardizePath()
    {
        $arrPath = func_get_args();

        if (count($arrPath) == 0 || $arrPath == null || $arrPath == "")
        {
            return "";
        }

        $strVar = "";

        foreach ($arrPath as $itPath)
        {
            $itPath = str_replace(array(TL_ROOT, "\\"), array("", "/"), $itPath);
            $itPath = explode("/", $itPath);

            foreach ($itPath as $itFolder)
            {
                if ($itFolder == "" || $itFolder == "." || $itFolder == "..")
                {
                    continue;
                }

                $strVar .= "/" . $itFolder;
            }
        }

        return preg_replace("/^\//i", "", $strVar);
    }

    /**
     * Ping the current client status
     * 
     * @param string $strAction 
     */
    public function pingClientStatus($strAction)
    {
        if ($strAction == 'syncCtoPing')
        {
            // Set time limit for this function
            set_time_limit(5);
            
            if (strlen($this->Input->post('clientID')) != 0 && is_numeric($this->Input->post('clientID')))
            {
                try
                {
                    // Load Client from database
                    $objClient = $this->Database->prepare("SELECT * FROM tl_synccto_clients WHERE id = %s")
                            ->limit(1)
                            ->execute((int) $this->Input->post('clientID'));

                    // Check if a client was loaded
                    if ($objClient->numRows == 0)
                    {
                        echo "false";
                        exit();
                    }

                    // Clean link
                    $objClient->path = preg_replace("/\/\z/i", "", $objClient->path);
                    $objClient->path = preg_replace("/ctoCommunication.php\z/", "", $objClient->path);

                    // Build link
                    $strServer = $objClient->address . ":" . $objClient->port;

                    if ($objClient->path == "")
                    {

                        $strUrl = $objClient->address . ":" . $objClient->port . "/ctoCommunication.php?act=ping";
                    }
                    else
                    {
                        $strUrl = $objClient->address . ":" . $objClient->port . "/" . $objClient->path . "/ctoCommunication.php?act=ping";
                    }
                    
                    $intReturn = 0;

                    $objRequest = new RequestExtendedCached();
                    
                    // Set http auth
                    if ($objClient->http_auth == true)
                    {
                        $this->import("Encryption");

                        $objRequest->strHTTPUser = $objClient->http_username;
                        $objRequest->strHTTPPassword = $this->Encryption->decrypt($objClient->http_password);
                    }

                    // Check Server
                    $objRequest->send($strServer);
                    if ($objRequest->code == '200')
                    {
                        $intReturn = $intReturn + 1;
                    }

                    // Check page
                    $objRequest->send($strUrl);
                    if ($objRequest->code == '200')
                    {
                        $intReturn = $intReturn + 2;
                    }

                    echo $intReturn;
                }
                catch (Exception $exc)
                {
                    echo "false";
                }
            }
            else
            {
                echo "false";
            }

            exit();
        }
    }

    /**
     * Check the required extensions and files for syncCto
     * 
     * @param string $strContent
     * @param string $strTemplate
     * @return string
     */
    public function checkExtensions($strContent, $strTemplate)
    {
        if ($strTemplate == 'be_main')
        {
            if (!is_array($_SESSION["TL_INFO"]))
            {
                $_SESSION["TL_INFO"] = array();
            }

            // required extensions
            $arrRequiredExtensions = array(
                'ctoCommunication' => 'ctoCommunication',
                'textwizard' => 'textwizard',
                '3CFramework' => '3cframework'
            );

            // required files
            $arrRequiredFiles = array(
                'DC_Memory' => 'system/drivers/DC_Memory.php'
            );

            // check for required extensions
            foreach ($arrRequiredExtensions as  $key => $val)
            {
                if (!in_array($val, $this->Config->getActiveModules()))
                {
                    $_SESSION["TL_INFO"] = array_merge($_SESSION["TL_INFO"], array($val => 'Please install the required extension <strong>' . $key . '</strong>'));
                }
                else
                {
                    if (is_array($_SESSION["TL_INFO"]) && key_exists($val, $_SESSION["TL_INFO"]))
                    {
                        unset($_SESSION["TL_INFO"][$val]);
                    }
                }
            }

            // check for required files
            foreach ($arrRequiredFiles as $key => $val)
            {
                if (!file_exists(TL_ROOT . '/' . $val))
                {
                    $_SESSION["TL_INFO"] = array_merge($_SESSION["TL_INFO"], array($val => 'Please install the required file/extension <strong>' . $key . '</strong>'));
                }
                else
                {
                    if (is_array($_SESSION["TL_INFO"]) && key_exists($val, $_SESSION["TL_INFO"]))
                    {
                        unset($_SESSION["TL_INFO"][$val]);
                    }
                }
            }
        }

        return $strContent;
    }

    /**
     * Return all sync types as array
     * 
     * @return array 
     */
    public function getSyncType()
    {
        $groups = array();

        foreach ($GLOBALS['SYC_SYNC'] as $key => $value)
        {
            foreach ($value as $key2 => $value2)
            {
                $groups[$key][$value2] = $key2;
            }
        }

        return $groups;
    }

    /**
     * Return all backup types as array
     * 
     * @return array 
     */
    public function getBackupType()
    {
        $groups = array();

        foreach ($GLOBALS['SYC_BACKUP'] as $key => $value)
        {
            foreach ($value as $key2 => $value2)
            {
                $groups[$key][$value2] = $key2;
            }
        }

        return $groups;
    }

    /**
     * Returns a whole list of all tables in the database
     * 
     * @return array 
     */
    public function hiddenTables()
    {
        $arrTables = array();

        foreach ($this->Database->listTables() as $key => $value)
        {
            $arrTables[] = $value;
        }

        return $arrTables;
    }

    /**
     * Returns a list without the hidden tables
     * 
     * @return array 
     */
    public function databaseTables()
    {
        $arrTables = array();
        $arrTablesHidden = $this->getTablesHidden();

        foreach ($this->Database->listTables() as $key => $value)
        {
            // Check if table is a hidden one.
            if (in_array($value, $arrTablesHidden))
            {
                continue;
            }

            $arrTables[] = $value;
        }

        return $arrTables;
    }

    /**
     * Returns a list with recommended database tables
     *
     * @return array
     */
    public function databaseTablesRecommended()
    {
        // Recommended tables
        $arrBlacklist = deserialize($GLOBALS['TL_CONFIG']['syncCto_database_tables']);
        if (!is_array($arrBlacklist))
        {
            $arrBlacklist = array();
        }

        $arrTablesPermission = $this->BackendUser->syncCto_tables;

        $arrTables = array();

        foreach ($this->databaseTables() as $key => $value)
        {
            if (in_array($value, $arrBlacklist))
            {
                continue;
            }

            if (is_array($arrTablesPermission) && !in_array($value, $arrTablesPermission) && $this->BackendUser->isAdmin != true)
            {
                continue;
            }

            $objCount = $this->Database->prepare("SELECT COUNT(*) as Count FROM $value")->execute();
            $arrTables[$value] = $value . '<span style="color: #aaaaaa; padding-left: 3px;">(' . $this->getReadableSize($this->Database->getSizeOf($value)) . ', ' . vsprintf($GLOBALS['TL_LANG']['MSC']['entries'] , array($objCount->Count)) . ')</span>';
        }

        return $arrTables;
    }

    /**
     * Returns a list with none recommended database tables
     *
     * @return array
     */
    public function databaseTablesNoneRecommended()
    {
        // None recommended tables
        $arrBlacklist = deserialize($GLOBALS['TL_CONFIG']['syncCto_database_tables']);
        if (!is_array($arrBlacklist))
        {
            $arrBlacklist = array();
        }

        $arrTablesPermission = $this->BackendUser->syncCto_tables;

        $arrTables = array();

        foreach ($this->databaseTables() as $key => $value)
        {
            if (!in_array($value, $arrBlacklist))
            {
                continue;
            }

            if (is_array($arrTablesPermission) && !in_array($value, $arrTablesPermission) && $this->BackendUser->isAdmin != true)
            {
                continue;
            }
            
            $objCount = $this->Database->prepare("SELECT COUNT(*) as Count FROM $value")->execute();
            $arrTables[$value] = $value . '<span style="color: #aaaaaa; padding-left: 3px;">(' . $this->getReadableSize($this->Database->getSizeOf($value)) . ', ' . vsprintf($GLOBALS['TL_LANG']['MSC']['entries'] , array($objCount->Count)) . ')</span>';
        }

        return $arrTables;
    }

    /**
     * Returns a list with none recommended database tables
     *
     * @return array
     */
    public function databaseTablesNoneRecommendedWithHidden()
    {
        // None recommended tables
        $arrBlacklist = deserialize($GLOBALS['TL_CONFIG']['syncCto_database_tables']);
        if (!is_array($arrBlacklist))
        {
            $arrBlacklist = array();
        }
        
        $arrHiddenlist = deserialize($GLOBALS['SYC_CONFIG']['table_hidden']);
        if (!is_array($arrHiddenlist))
        {
            $arrHiddenlist = array();
        }

        $arrTablesPermission = $this->BackendUser->syncCto_tables;

        $arrTables = array();

        foreach ($this->Database->listTables() as $key => $value)
        {
            if (!in_array($value, $arrBlacklist) && !in_array($value, $arrHiddenlist))
            {
                continue;
            }

            if (is_array($arrTablesPermission) && !in_array($value, $arrTablesPermission) && $this->BackendUser->isAdmin != true)
            {
                continue;
            }

            $objCount = $this->Database->prepare("SELECT COUNT(*) as Count FROM $value")->execute();
            $arrTables[$value] = $value . '<span style="color: #aaaaaa; padding-left: 3px;">(' . $this->getReadableSize($this->Database->getSizeOf($value)) . ', ' . vsprintf($GLOBALS['TL_LANG']['MSC']['entries'], array($objCount->Count)) . ')</span>';
        }

        return $arrTables;
    }

    /**
     * Returns a list with recommended database tables
     * 
     * @return array 
     */
    public function getDatabaseTablesClient()
    {
        // Build communication class
        $objSyncCtoCommunicationClient = SyncCtoCommunicationClient::getInstance();
        $objSyncCtoCommunicationClient->setClientBy($this->Input->get("id"));

        try
        {
            // Start connection
            $objSyncCtoCommunicationClient->startConnection();
            // Get Tables
            $arrTablesClient = $objSyncCtoCommunicationClient->getDatabaseTables();
            // Stop connection
            $objSyncCtoCommunicationClient->stopConnection();

            // Recommended tables
            $arrBlacklist = deserialize($GLOBALS['SYC_CONFIG']['table_hidden']);
            if (!is_array($arrBlacklist))
            {
                $arrBlacklist = array();
            }

            $arrReturnTables = array();

            foreach ($arrTablesClient as $key => $value)
            {
                if (in_array($value, $arrBlacklist))
                {
                    continue;
                }

                $arrReturnTables[] = $value;
            }

            return $arrReturnTables;
        }
        catch (Exception $exc)
        {
            $_SESSION["TL_ERROR"][] = $exc->getMessage();
            return array();
        }
    }

    /**
     * Import configuration entries
     *
     * @param array $arrConfig
     * @return array 
     */
    public function importConfig($arrConfig)
    {
        $arrLocalConfig = $this->loadConfigs(SyncCtoEnum::LOADCONFIG_KEYS_ONLY);

        foreach ($arrConfig as $key => $value)
        {
            if (in_array($key, $arrLocalConfig))
            {
                $this->Config->update("\$GLOBALS['TL_CONFIG']['" . $key . "']", $value);
            }
            else
            {
                $this->Config->add("\$GLOBALS['TL_CONFIG']['" . $key . "']", $value);
            }
        }

        return true;
    }
    
    

}

?>