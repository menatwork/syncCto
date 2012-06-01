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
 * @copyright  MEN AT WORK 2012
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
                if ($value == "" || in_array($value, $arrSyncCtoConfig))
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
                    $arrReturn = array("success" => false, "value" => 0, "error" => "", "token" => REQUEST_TOKEN);

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
                'MultiColumnWizard' => 'multicolumnwizard',
                '3CFramework' => '3cframework'
            );

            // required files
            $arrRequiredFiles = array(
                'DC_Memory' => 'system/drivers/DC_Memory.php',
                'ZipArchiveCto' => 'system/libraries/ZipArchiveCto.php'
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
        if($this->BackendUser->isAdmin)
        {
            return $GLOBALS['SYC_CONFIG']['sync_options'];
        }
        else
        {
            $arrUserSyncOptions = $this->BackendUser->syncCto_sync_options;
            
            $arrSyncOption = array();
            foreach($GLOBALS['SYC_CONFIG']['sync_options'] AS $fileType => $arrValue)
            {
                foreach($arrValue AS $strRight)
                {
                    if(in_array($strRight, $arrUserSyncOptions))
                    {
                        if(!array_key_exists($fileType, $arrSyncOption))
                        {
                            $arrSyncOption[$fileType] = array();
                        }
                        
                        $arrSyncOption[$fileType][] = $strRight;
                    }                            
                }
            }       
            return $arrSyncOption;
        }            
    }

    /**
     * Get a list with all maintance options
     * @return array 
     */
    public function getMaintanceOptions()
    {       
        return $GLOBALS['SYC_CONFIG']['maintance_options'];
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
            if (in_array($value, $arrBlacklist) || preg_match("/synccto_temp_.*/", $value))
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
            if (!in_array($value, $arrBlacklist) || preg_match("/synccto_temp_.*/", $value))
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
    private function getTableMeta($strTableName)
    {
        $objCount = $this->Database->prepare("SELECT COUNT(*) as Count FROM $strTableName")->execute();
        
        $arrTableMeta = array(
            'name' => $strTableName,
            'count' => $objCount->Count,
            'size' => $this->Database->getSizeOf($strTableName)
        );
        
        return $arrTableMeta;
    }
    
    /**
     * Set styles for the given array recommended table data and return it as string
     * 
     * @param array $arrTableMeta
     * @return string 
     */
    public function getStyledTableMeta($arrTableMeta)
    {
        $strTableName = $arrTableMeta['name'];
        $intEntriesCount = $arrTableMeta['count'];
        $intEntriesSize = $arrTableMeta['size'];
        
        $strColor = '666966';

        $strReturn = '<span style="color: #' . $strColor . '; padding-left: 3px;">';
        $strReturn .= $strTableName;
        $strReturn .= '<span style="color:#a3a3a3;padding-left: 3px;">';
        $strReturn .= '(' . $this->getReadableSize($intEntriesSize) . ', ' . vsprintf($GLOBALS['TL_LANG']['MSC']['entries'], array($intEntriesCount)) . ')';
        $strReturn .= '</span>';
        $strReturn .= '</span>';
        return $strReturn;        
    }    

    /**
     * Return a list with all timestamps form tables
     * 
     * @param string/array $mixTableNames 
     */
    public function getDatabaseTablesTimestamp($mixTableNames = array())
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
        $arrTimestamp = array();

        // Load all Tables
        $arrTables = $this->Database->listTables();
        
        $objDBSchema = $this->Database->prepare("SELECT TABLE_NAME, UPDATE_TIME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?")->executeUncached($GLOBALS['TL_CONFIG']['dbDatabase']);
        
        $arrDBSchema = array();
        while($objDBSchema->next())
        {
            $arrDBSchema[$objDBSchema->TABLE_NAME] = strtotime($objDBSchema->UPDATE_TIME);
        }
        
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
                $arrTimestamp[$strTable] = 0;
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

            $arrTimestamp[$strTable] = $arrDBSchema[$strTable];
        }

        if (!is_array($mixTableNames))
        {
            return $arrTimestamp[$mixTableNames];
        }
        else
        {
            return $arrTimestamp;
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
    
    /**
     * Check if the post of the submited form is empty and set error or unset error 
     * 
     * @param array $arrCheckSubmit 
     */
    public function checkSubmit($arrCheckSubmit)
    {   
        $arrPostUnset = array('FORM_SUBMIT', 'FORM_FIELDS', 'REQUEST_TOKEN');
        
        if(is_array($arrCheckSubmit['postUnset']))
        {
            $arrPostUnset = array_merge($arrPostUnset, $arrCheckSubmit['postUnset']);
        }
        
        $arrPost = $_POST;
        
        foreach($arrPostUnset AS $value)
        {
            if(array_key_exists($value, $arrPost))
            {
                unset($arrPost[$value]);
            }
        }
        
        if(count($arrPost) > 0)
        {
            if(is_array($_SESSION["TL_ERROR"]))
            {
                if (array_key_exists($arrCheckSubmit['error']['key'], $_SESSION["TL_ERROR"]))
                {
                    unset($_SESSION["TL_ERROR"][$arrCheckSubmit['error']['key']]);
                }
            }
            $this->redirect($arrCheckSubmit['redirectUrl']);
        }
        else
        {
            if(!is_array($_SESSION["TL_ERROR"]))
            {
                $_SESSION["TL_ERROR"] = array();
            }
            
            if(!array_key_exists($arrCheckSubmit['error']['key'], $_SESSION["TL_ERROR"]))
            {
                $_SESSION["TL_ERROR"][$arrCheckSubmit['error']['key']] = $arrCheckSubmit['error']['message'];
            }
        }
    }
    
}

?>