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
    // Objects   
    protected $objSyncCtoDatabase;
    // Vars
    protected $arrDbColorLimits;

    /* -------------------------------------------------------------------------
     * Core
     */

    /**
     * Constructor
     */
    protected function __construct()
    {
        // Import
        $this->import("BackendUser");

        // Parent
        parent::__construct();

        // Language
        $this->loadLanguageFile("default");

        // Load color limits for db
        $this->arrDbColorLimits = array();
        $arrEntriesCount = array();

        if ($GLOBALS['TL_CONFIG']['syncCto_colored_db_view'] != "")
        {
            $this->arrDbColorLimits = deserialize($GLOBALS['TL_CONFIG']['syncCto_colored_db_view']);

            if (is_array($this->arrDbColorLimits))
            {
                foreach ($this->arrDbColorLimits as $key => $value)
                {
                    $arrEntriesCount[$key] = $value['entries'];
                }
            }
        }
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
                if ($value == "" || in_array($value, $arrSyncCtoConfig) )
                {
                    continue;
                }

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
        $arrLocalconfig   = deserialize($GLOBALS['TL_CONFIG']['syncCto_folder_blacklist']);
        $arrSyncCtoConfig = $GLOBALS['SYC_CONFIG']['folder_blacklist'];
        return $this->mergeConfigs($arrLocalconfig, $arrSyncCtoConfig);
    }

    public function getWhitelistFolder()
    {
        $arrLocalconfig   = deserialize($GLOBALS['TL_CONFIG']['syncCto_folder_whitelist']);
        $arrSyncCtoConfig = $GLOBALS['SYC_CONFIG']['folder_whitelist'];
        return $this->mergeConfigs($arrLocalconfig, $arrSyncCtoConfig);
    }

    public function getBlacklistFile()
    {
        $arrLocalconfig   = deserialize($GLOBALS['TL_CONFIG']['syncCto_file_blacklist']);
        $arrSyncCtoConfig = $GLOBALS['SYC_CONFIG']['file_blacklist'];
        return $this->mergeConfigs($arrLocalconfig, $arrSyncCtoConfig);
    }

    public function getBlacklistLocalconfig()
    {
        $arrLocalconfig   = deserialize($GLOBALS['TL_CONFIG']['syncCto_local_blacklist']);
        $arrSyncCtoConfig = $GLOBALS['SYC_CONFIG']['local_blacklist'];
        return $this->mergeConfigs($arrLocalconfig, $arrSyncCtoConfig);
    }

    public function getTablesHidden()
    {
        $arrLocalconfig   = deserialize($GLOBALS['TL_CONFIG']['syncCto_hidden_tables']);
        $arrSyncCtoConfig = $GLOBALS['SYC_CONFIG']['table_hidden'];
        return $this->mergeConfigs($arrLocalconfig, $arrSyncCtoConfig);
    }

    /* -------------------------------------------------------------------------
     * Callbacks
     */

    /**
     * Ping the current client status
     * 
     * @param string $strAction 
     */
    public function pingClientStatus($strAction)
    {
        if ($strAction == 'syncCtoPing')
        {
            if (strlen($this->Input->post('clientID')) != 0 && is_numeric($this->Input->post('clientID')))
            {
                // Set time limit for this function
                set_time_limit(10);

                try
                {
                    if (version_compare(VERSION . '.' . BUILD, '2.10.0', '<'))
                    {
                        $arrReturn = array("success" => false, "value" => 0, "error" => "", "token" => "");
                    }
                    else
                    {
                        $arrReturn = array("success" => false, "value" => 0, "error" => "", "token" => REQUEST_TOKEN);
                    }

                    // Load Client from database
                    $objClient = $this->Database->prepare("SELECT * FROM tl_synccto_clients WHERE id = %s")
                            ->limit(1)
                            ->execute((int) $this->Input->post('clientID'));

                    // Check if a client was loaded
                    if ($objClient->numRows == 0)
                    {
                        $arrReturn["success"] = false;
                        $arrReturn["error"]   = "Unknown client";
                        echo json_encode($arrReturn);
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

                        $objRequest->username = $objClient->http_username;
                        $objRequest->password = $this->Encryption->decrypt($objClient->http_password);
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

                    $arrReturn["success"] = true;
                    $arrReturn["value"]   = $intReturn;
                }
                catch (Exception $exc)
                {
                    $arrReturn["success"] = false;
                    $arrReturn["error"]   = $exc->getMessage() . $exc->getFile() . " on " . $exc->getLine();
                }
            }
            else
            {
                $arrReturn["success"] = false;
                $arrReturn["error"]   = "Missing client id.";
            }

            echo json_encode($arrReturn);
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
            foreach ($arrRequiredExtensions as $key => $val)
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
     * Insert a warning msg if SycnFrom was activate
     * 
     * @param string $strContent
     * @param string $strTemplate
     * @return stinrg 
     */
    public function checkLockStatus($strContent, $strTemplate)
    {
        if ($strTemplate == 'be_main' && $GLOBALS['TL_CONFIG']['syncCto_attentionFlag'] == true)
        {
            $objTemplate = new BackendTemplate("be_syncCto_attention");

            $strNewContent = preg_replace("/<div.*id=\"container\".*>/", $objTemplate->parse() . "\n$0", $strContent, 1);

            if ($strNewContent == "")
            {
                return $strContent;
            }
            else
            {
                $strContent = $strNewContent;
            }
        }

        return $strContent;
    }

    /**
     * Get a list with all file synchronization options
     * @return array 
     */
    public function getFileSyncOptions()
    {
        $arrReturn = array(
            "core" => array(
                "core_change" => "core_change",
                "core_delete" => "core_delete"
            ),
            "user" => array(
                "user_change" => "user_change",
                "user_delete" => "user_delete"
            ),
            "configfiles" => array(
                "localconfig_update" => "localconfig_update",
                "localconfig_errors" => "localconfig_errors",
                "localconfig_refererCheck" => "localconfig_refererCheck",
            )
        );

        return $arrReturn;
    }

    /**
     * Get a list with all maintance options
     * @return array 
     */
    public function getMaintanceOptions()
    {
        $arrReturn = array(
            "temp_tables" => "temp_tables",
            "temp_folders" => "temp_folders",
            "css_create" => "css_create",
            "xml_create" => "xml_create",
        );

        return $arrReturn;
    }

    /* -------------------------------------------------------------------------
     * Helper Functions
     */

    /**
     * Standardize path for folder
     * No TL_ROOT, No starting /
     * 
     * @return string the normalized path
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
            if (in_array($value, $arrTablesHidden) || preg_match("/synccto_temp_.*/", $value))
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
     * @param array $arrHashLast A Hash list for checking hash
     * @return array
     */
    public function databaseTablesRecommended($arrHashLast = null)
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
            if (in_array($value, $arrBlacklist) || preg_match("/synccto_temp_.*/", $value))
            {
                continue;
            }

            if (is_array($arrTablesPermission) && !in_array($value, $arrTablesPermission) && $this->BackendUser->isAdmin != true)
            {
                continue;
            }

            if ($arrHashLast != null && is_array($arrHashLast))
            {
                if ($arrHashLast[$value] == $this->getDatabaseTablesHash($value))
                {
                    $arrTables["tables_no_changes"][$value] = $this->getTableMeta($value, true);
                }
                else
                {
                    $arrTables["tables_changes"][$value] = $this->getTableMeta($value, false);
                }
            }
            else
            {
                $arrTables[$value] = $this->getTableMeta($value);
            }
        }

        if ($arrHashLast != null && is_array($arrHashLast))
        {
            if (count($arrTables["tables_changes"]) == 0 || count($arrTables["tables_no_changes"]) == 0)
            {
                if (count($arrTables["tables_no_changes"]) == 0)
                {
                    return $arrTables["tables_changes"];
                }
                else
                {
                    return $arrTables["tables_no_changes"];
                }
            }
            else
            {
                return $arrTables;
            }
        }
        else
        {
            return $arrTables;
        }
    }

    /**
     * Returns a list with none recommended database tables
     *
     * @param array $arrHashLast A Hash list for checking hash
     * @return array
     */
    public function databaseTablesNoneRecommended($arrHashLast = null)
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
            if (!in_array($value, $arrBlacklist) || preg_match("/synccto_temp_.*/", $value))
            {
                continue;
            }

            if (is_array($arrTablesPermission) && !in_array($value, $arrTablesPermission) && $this->BackendUser->isAdmin != true)
            {
                continue;
            }

            if ($arrHashLast != null && is_array($arrHashLast))
            {
                if ($arrHashLast[$value] == $this->getDatabaseTablesHash($value))
                {
                    $arrTables["tables_no_changes"][$value] = $this->getTableMeta($value, true);
                }
                else
                {
                    $arrTables["tables_changes"][$value] = $this->getTableMeta($value, false);
                }
            }
            else
            {
                $arrTables[$value] = $this->getTableMeta($value);
            }
        }

        if ($arrHashLast != null && is_array($arrHashLast))
        {
            if (count($arrTables["tables_changes"]) == 0 || count($arrTables["tables_no_changes"]) == 0)
            {
                if (count($arrTables["tables_no_changes"]) == 0)
                {
                    return $arrTables["tables_changes"];
                }
                else
                {
                    return $arrTables["tables_no_changes"];
                }
            }
            else
            {
                return $arrTables;
            }
        }
        else
        {
            return $arrTables;
        }
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
            if (!in_array($value, $arrBlacklist) && !in_array($value, $arrHiddenlist) || preg_match("/synccto_temp_.*/", $value))
            {
                continue;
            }

            if (is_array($arrTablesPermission) && !in_array($value, $arrTablesPermission) && $this->BackendUser->isAdmin != true)
            {
                continue;
            }

            $arrTables[$value] = $this->getTableMeta($value);
        }

        return $arrTables;
    }

    /**
     * Get Table Meta
     * 
     * @param string $strTableName
     * @param boolean $booHashSame
     * @return string 
     */
    private function getTableMeta($strTableName, $booHashSame = null)
    {
        $objCount  = $this->Database->prepare("SELECT COUNT(*) as Count FROM $strTableName")->execute();
        $intEntriesCount = $objCount->Count;
        $intEntriesSize = $this->Database->getSizeOf($strTableName);
        
        $strColor = 'AAA';

        if($GLOBALS['TL_CONFIG']['syncCto_custom_settings'])
        {
            $booBreakLoop = FALSE;
            foreach($this->arrDbColorLimits AS $arrColorLimits)
            {
                switch ($arrColorLimits['unit'])
                {
                    case 'kb':
                        if(($intEntriesSize / 1000) > $arrColorLimits['entries'])
                        {             
                            $booBreakLoop = TRUE;                        
                        }
                        break;

                    case 'mb':
                        if(($intEntriesSize / 1000 / 1000) > $arrColorLimits['entries'])
                        {
                            $booBreakLoop = TRUE;                        
                        }                    
                        break;

                    case 'entries':                    
                        if ($intEntriesCount > $arrColorLimits['entries'])
                        {                     
                            $booBreakLoop = TRUE;
                        }                    
                        break;
                }

                if($booBreakLoop == TRUE)
                {
                    if($arrColorLimits != '')
                    {
                        $strColor = $arrColorLimits['color'];
                    }
                    break;
                }
            }
        }
        $strReturn  = '<span style="color: #' . $strColor . '; padding-left: 3px;">';
        $strReturn .= $strTableName;
        $strReturn .= '<span style="padding-left: 3px;">';
        $strReturn .= '(' . $this->getReadableSize($intEntriesSize) . ', ' . vsprintf($GLOBALS['TL_LANG']['MSC']['entries'], array($intEntriesCount)) . ')';
        $strReturn .= '</span>';
        $strReturn .= '</span>';     
        return $strReturn;
               
//        if ($booHashSame === FALSE)
//        {
//            $strReturn .= '<span style="padding-left: 3px;">' . $strTableName . '</span>';
//        }
//        else if ($booHashSame === TRUE)
//        {
//            $strReturn .= '<span style="padding-left: 3px;">' . $strTableName . '</span>';
//        }
//        else
//        {
//            $strReturn .= $strTableName;
//        }
    }

    /**
     * Return a list with all hashes form tables
     * 
     * @param string/array $mixTableNames 
     */
    public function getDatabaseTablesHash($mixTableNames = array())
    {
        // If we have only a string for tablenames set it as array
        if (!is_array($mixTableNames))
        {
            $arrTableNames = array($mixTableNames);
        }
        else
        {
            $arrTableNames = $mixTableNames;
        }

        // Return array
        $arrHash = array();

        // Load all Tables
        $arrTables = $this->Database->listTables();

        foreach ($arrTables as $strTable)
        {
            // Skip hidden tables
            if (in_array($strTable, $GLOBALS['SYC_CONFIG']['table_hidden']))
            {
                continue;
            }

            // Check if we search some special tables
            if (is_array($arrTableNames) && count($arrTableNames) != 0 && !in_array($strTable, $arrTableNames))
            {
                continue;
            }

            // Check if we have rows in table
            $objCount = $this->Database->prepare("SELECT COUNT(*) as count FROM $strTable")->execute();
            if ($objCount->count == 0)
            {
                $arrHash[$strTable] = 0;
            }

            // Load all fields
            $arrFields = array();
            $arrDBFields = $this->Database->listFields($strTable);

            foreach ($arrDBFields as $arrField)
            {
                // Skip field primary for contao 2.10 >
                if ($arrField['name'] == "PRIMARY")
                {
                    break;
                }

                $arrFields[] = $arrField['name'];
            }

            // Build hash
            $strSQL   = "SELECT MD5(GROUP_CONCAT( CONCAT_WS('#', `" . implode("`, `", $arrFields) . "`) SEPARATOR '##' )) as hash  FROM $strTable";
            $objQuery = $this->Database->prepare($strSQL)->execute();

            $arrHash[$strTable] = $objQuery->hash;
        }

        if (!is_array($mixTableNames))
        {
            return $arrHash[$mixTableNames];
        }
        else
        {
            return $arrHash;
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
            if ($key == "disableRefererCheck" && $value == true)
            {
                $this->Config->update("\$GLOBALS['TL_CONFIG']['ctoCom_disableRefererCheck']", true);
            }

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
    
    /**
     * Check for customer regular expression
     * 
     * @param type $strRegexp
     * @param type $varValue
     * @param Widget $objWidget
     * @return boolean 
     */
    public function customRegexp($strRegexp, $varValue, Widget $objWidget)
    {
        switch ($strRegexp)
        {
            case 'colorRgb':
                if (!preg_match('/^([0-9a-f]{3}|[0-9a-f]{6})$/i', $varValue))
                {
                    $objWidget->addError('Field ' . $objWidget->label . ' should be a color RGB code.');
                }

                return true;
                break;
        }
        return false;
    }
    
    /* -------------------------------------------------------------------------
     * Remote Calls
     */

    /**
     * Returns a list with recommended database tables
     * 
     * @return array 
     */
    public function getRecommendedDatabaseTablesClient()
    {
        // Build communication class
        $objSyncCtoCommunicationClient = SyncCtoCommunicationClient::getInstance();
        $objSyncCtoCommunicationClient->setClientBy($this->Input->get("id"));

        try
        {
            // Start connection
            $objSyncCtoCommunicationClient->startConnection();
            // Get Tables
            $arrTablesClient = $objSyncCtoCommunicationClient->getRecommendedTables();

            // Stop connection
            $objSyncCtoCommunicationClient->stopConnection();

            // Check if we have a array 
            if (!is_array($arrTablesClient))
            {
                $arrTablesClient = array();
            }

            return $arrTablesClient;
        }
        catch (Exception $exc)
        {
            $_SESSION["TL_ERROR"][] = $exc->getMessage();
            return array();
        }
    }

    /**
     * Returns a list with none recommended database tables
     * 
     * @return array 
     */
    public function getNoneRecommendedDatabaseTablesClient()
    {
        // Build communication class
        $objSyncCtoCommunicationClient = SyncCtoCommunicationClient::getInstance();
        $objSyncCtoCommunicationClient->setClientBy($this->Input->get("id"));

        try
        {
            // Start connection
            $objSyncCtoCommunicationClient->startConnection();
            // Get Tables
            $arrTablesClient = $objSyncCtoCommunicationClient->getNoneRecommendedTables();

            // Stop connection
            $objSyncCtoCommunicationClient->stopConnection();

            // Check if we have a array 
            if (!is_array($arrTablesClient))
            {
                $arrTablesClient = array();
            }

            return $arrTablesClient;
        }
        catch (Exception $exc)
        {
            $_SESSION["TL_ERROR"][] = $exc->getMessage();
            return array();
        }
    }

}

?>