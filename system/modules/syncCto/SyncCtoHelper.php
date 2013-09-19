<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2013 
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
        $this->import("String");

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
     * Contao 2.xx and 3.xx helper
     */
    
    /**
     * Check if we have a version from contao 3 or higher.
     * @return boolean
     */
    static public function isContao3()
    {
        return version_compare(VERSION, '3.0', '>=');
    }
    
    /**
     * Check if we have a version from contao 3 or higher.
     * @return boolean
     */
    static public function isContao31()
    {
        return version_compare(VERSION, '3.1', '>=');
    }

    /**
     * Check if we have a version from contao 2 or lower.
     * @return boolean
     */
    static public function isContao2()
    {
        return version_compare(VERSION, '3', '<');
    }

    /**
     * Check which DC General version is installed.
     * @return boolean
     */
    static public function isDcGeneralC3Version()
    {
        if (file_exists(TL_ROOT . '/system/modules/generalDriver/DcGeneral'))
        {
            return true;
        }

        return false;
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
            $arrLocalconfig   = array_filter($arrLocalconfig, 'strlen');
            $arrSyncCtoConfig = array_filter($arrSyncCtoConfig, 'strlen');

            return array_keys(array_flip(array_merge($arrLocalconfig, $arrSyncCtoConfig)));
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
	
	/**
	 * Store the relative path
	 *
	 * Only store this value if the temp directory is writable and the local
	 * configuration file exists, otherwise it will initialize a Files object and
	 * prevent the install tool from loading the Safe Mode Hack (see #3215).
	 * 
	 * @throws Exception
	 * @return boolean 
	 */
	public function createPathconfig()
	{
		// Check if we have the file
		if (file_exists(TL_ROOT . '/system/config/pathconfig.php'))
		{
			return true;
		}
		
		// Check localconfig
		if(!file_exists(TL_ROOT . '/system/config/localconfig.php'))
		{
			throw new Exception('Missing localconfig.php');
		}
		
		// Check tmp
		if(!is_writable(TL_ROOT . '/system/tmp'))
		{
			throw new Exception('"/system/tmp" is not writable.');
		}

		// Write file
		try
		{
			$objFile = new File('system/config/pathconfig.php');
			
			// Check if we have the path
			if(TL_PATH === null || TL_PATH == "")
			{	
				$objFile->write("<?php\n\n// Relative path to the installation\nreturn '" . preg_replace('/\/ctoCommunication.php\?.*$/i', '', Environment::getInstance()->requestUri) . "';\n");				
			}
			else
			{
				$objFile->write("<?php\n\n// Relative path to the installation\nreturn '" . TL_PATH . "';\n");
			}
			
			$objFile->close();
		}
		catch (Exception $e)
		{
			log_message($e->getMessage());
			throw $e;
		}
		
		// All done
		return true;
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
                    $arrReturn = array("success" => false, "value"   => 0, "error"   => "", "token"   => REQUEST_TOKEN);

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
                'ctoCommunication'  => 'ctoCommunication',
                'MultiColumnWizard' => 'multicolumnwizard',
                'generalDriver'     => 'generalDriver',
                'ZipArchiveCto'     => 'ZipArchiveCto'
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
            
            // Check syncCtoPro, if not set remove triggers.
            if(!in_array('syncCtoPro', $this->Config->getActiveModules()) 
                    && ($this->hasTrigger('tl_page') || $this->hasTrigger('tl_article') || $this->hasTrigger('tl_content')))
            {                
                $this->dropTrigger('tl_page');
                $this->dropTrigger('tl_article');
                $this->dropTrigger('tl_content');
            }
        }

        return $strContent;
    }

    /**
     * Insert a warning msg if the attention flag is active
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

            preg_match('/<div.*id=\"header\".*>/i', $strContent, $arrHeader);
            preg_match('{<div\s+id="header"\s*>((?:(?:(?!<div[^>]*>|</div>).)++|<div[^>]*>(?1)</div>)*)</div>}si', $strContent, $arrInnderDiv);
            $strNew        = $arrHeader[0] . $arrInnderDiv[1] . $objTemplate->parse() . '</div>';
            $strNewContent = preg_replace('{<div\s+id="header"\s*>((?:(?:(?!<div[^>]*>|</div>).)++|<div[^>]*>(?1)</div>)*)</div>}si', $strNew, $strContent, 1);
            if ($strNewContent == "" && $arrInnderDiv[1] != '' && $arrHeader[0] != '')
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
        if ($this->BackendUser->isAdmin)
        {
            return $GLOBALS['SYC_CONFIG']['sync_options'];
        }
        else
        {
            $arrUserSyncOptions = $this->BackendUser->syncCto_sync_options;

            $arrSyncOption = array();
            foreach ($GLOBALS['SYC_CONFIG']['sync_options'] AS $fileType => $arrValue)
            {
                foreach ($arrValue AS $strRight)
                {
                    if (in_array($strRight, $arrUserSyncOptions))
                    {
                        if (!array_key_exists($fileType, $arrSyncOption))
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

    /**
     * Execute some operations at last step
     */
    public function executeFinalOperations()
    {
        $arrReturn = array();

        // HOOK: do some last operations
        if (isset($GLOBALS['TL_HOOKS']['syncExecuteFinalOperations']) && is_array($GLOBALS['TL_HOOKS']['syncExecuteFinalOperations']))
        {
            foreach ($GLOBALS['TL_HOOKS']['syncExecuteFinalOperations'] as $callback)
            {
                try
                {
                    $this->import($callback[0]);
                    $this->$callback[0]->$callback[1]();
                }
                catch (Exception $exc)
                {
                    $arrReturn [] = array(
                        'callback' => implode("|", $callback),
                        'info_msg' => "Error by: TL_HOOK $callback[0] | $callback[1] with Msg: " . $exc->getMessage()
                    );
                    
                    $this->log("Error by: TL_HOOK $callback[0] | $callback[1] with Msg: " . $exc->getMessage(), __CLASS__ . "|" . __FUNCTION__, TL_ERROR);
                }
            }
        }

        return $arrReturn;
    }

    /* -------------------------------------------------------------------------
     * Helper Functions
     */

    /**
     * Shorten a string to a certain number of characters
     *
     * Shortens a string to a given number of characters preserving words
     * (therefore it might be a bit shorter or longer than the number of
     * characters specified). Stips all tags.
     * @param string
     * @param integer
     * @param string
     * @return string
     */
    public function substrCenter($strString, $intNumberOfChars, $strEllipsis = ' […] ')
    {
        $strString = preg_replace('/[\t\n\r]+/', ' ', $strString);
        $strString = strip_tags($strString);

        if (utf8_strlen($strString) <= $intNumberOfChars)
        {
            return $strString;
        }

        $intCharCount = 0;
        $arrWords     = array();
        $arrChunks      = preg_split('/\s+/', $strString);
        $blnAddEllipsis = false;

        //first part
        foreach ($arrChunks as $chunkKey => $strChunk)
        {
            $intCharCount += utf8_strlen($this->String->decodeEntities($strChunk));

            if ($intCharCount++ <= $intNumberOfChars / 2)
            {
                // if we add the whole word remove it from list
                unset($arrChunks[$chunkKey]);

                $arrWords[] = $strChunk;
                continue;
            }

            // If the first word is longer than $intNumberOfChars already, shorten it
            // with utf8_substr() so the method does not return an empty string.
            if (empty($arrWords))
            {
                $arrWords[] = utf8_substr($strChunk, 0, $intNumberOfChars / 2);
            }

            if ($strEllipsis !== false)
            {
                $blnAddEllipsis = true;
            }

            break;
        }

        // Backwards compatibility
        if ($strEllipsis === true)
        {
            $strEllipsis = ' […] ';
        }
        
        $intCharCount = 0;
        $arrWordsPt2 = array();

        // Second path
        foreach (array_reverse($arrChunks) as $strChunk)
        {
            $intCharCount += utf8_strlen($this->String->decodeEntities($strChunk));

            if ($intCharCount++ <= $intNumberOfChars / 2)
            {
                $arrWordsPt2[] = $strChunk;
                continue;
            }

            // If the first word is longer than $intNumberOfChars already, shorten it
            // with utf8_substr() so the method does not return an empty string.
            if (empty($arrWordsPt2))
            {
                $arrWordsPt2[] = utf8_substr($strChunk, utf8_strlen($strChunk) - ($intNumberOfChars / 2), utf8_strlen($strChunk));
            }
            break;
        }

        return implode(' ', $arrWords) . ($blnAddEllipsis ? $strEllipsis : '') . implode(' ', array_reverse($arrWordsPt2));
    }

    /**
     * Standardize path for folder
     * No TL_ROOT, No starting /
     * 
     * @return string the normalized path
     */
    public function standardizePath()
    {
        $arrPath = func_get_args();

        if (empty($arrPath))
        {
            return "";
        }

        $arrReturn = array();

        foreach ($arrPath as $itPath)
        {
            $itPath = str_replace('\\', '/', $itPath);
            $itPath = preg_replace('?^' . TL_ROOT . '?i', '', $itPath);
            $itPath = explode('/', $itPath);

            foreach ($itPath as $itFolder)
            {
                if ($itFolder === '' || $itFolder === null || $itFolder == "." || $itFolder == "..")
                {
                    continue;
                }
                
                $arrReturn[] = $itFolder;
            }
        }

        return implode('/', $arrReturn);
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
        $objCount = $this->Database->prepare("SELECT COUNT(*) as Count FROM $strTableName")->executeUncached();

        $arrTableMeta = array(
            'name'  => $strTableName,
            'count' => $objCount->Count,
            'size'  => $this->Database->getSizeOf($strTableName)
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
        $strTableName    = $arrTableMeta['name'];
        $intEntriesCount = $arrTableMeta['count'];
        $intEntriesSize  = $arrTableMeta['size'];

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
        while ($objDBSchema->next())
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

        if (is_array($arrCheckSubmit['postUnset']))
        {
            $arrPostUnset = array_merge($arrPostUnset, $arrCheckSubmit['postUnset']);
        }

        $arrPost = $_POST;

        foreach ($arrPostUnset AS $value)
        {
            if (array_key_exists($value, $arrPost))
            {
                unset($arrPost[$value]);
            }
        }

        if (count($arrPost) > 0)
        {
            if (is_array($_SESSION["TL_ERROR"]))
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
            if (!is_array($_SESSION["TL_ERROR"]))
            {
                $_SESSION["TL_ERROR"] = array();
            }

            if (!array_key_exists($arrCheckSubmit['error']['key'], $_SESSION["TL_ERROR"]))
            {
                $_SESSION["TL_ERROR"][$arrCheckSubmit['error']['key']] = $arrCheckSubmit['error']['message'];
            }
        }
    }
    
    /* -------------------------------------------------------------------------
     * Trigger functions
     */
    
    /**
     * Drop triggers for table XXX.
     * 
     * @param string $strTable
     */
    public function dropTrigger($strTable)
    {
        // Drop Update.
        $strQuery = "DROP TRIGGER IF EXISTS `" . $strTable . "_AfterUpdateHashRefresh`";
        $this->Database->query($strQuery);

        // Drop Insert.
        $strQuery = "DROP TRIGGER IF EXISTS `" . $strTable . "_AfterInsertHashRefresh`";
        $this->Database->query($strQuery);

        // Drop Delete.
        $strQuery = "DROP TRIGGER IF EXISTS `" . $strTable . "_AfterDeleteHashRefresh`";
        $this->Database->query($strQuery);
    }
    
    /**
     * Check if a trigger is set on one of the tables.
     * 
     * @return boolean True = we have some triggers | False = no trigger are set.
     */
    public function hasTrigger($strTable)
    {
        $arrTriggers = $this->Database->query('SHOW TRIGGERS')->fetchEach('Trigger');
        
        if(in_array($strTable . "_AfterUpdateHashRefresh", $arrTriggers))
        {
            return true;
        }
        
        if(in_array($strTable . "_AfterInsertHashRefresh", $arrTriggers))
        {
            return true;
        }
        
        if(in_array($strTable . "_AfterDeleteHashRefresh", $arrTriggers))
        {
            return true;
        }
    }

}

?>