<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

/**
 * Helper class for syncCto. Callback functions, small global helper functions.
 */
class SyncCtoHelper
{
    /* -------------------------------------------------------------------------
     * Vars
     */

    // instance
    protected static $instance = null;

    // Objects
    protected $objSyncCtoDatabase;

    /**
     * @var \FrontendUser|\BackendUser
     */
    protected $user;

    // Cache
    protected $arrPreparedBlacklistFolder;
    protected $arrPreparedBlacklistFiles;
    protected $arrPreparedHiddenTablePlaceholder;
    protected $strPreparedTlRoot = '';

    // Config
    protected $arrSearch = array("\\", ".", "^", "?", "*", "/");
    protected $arrReplace = array("\\\\", "\\.", "\\^", ".?", ".*", "\\/");

    /* -------------------------------------------------------------------------
     * Core
     */

    /**
     * Constructor
     */
    public function __construct()
    {
        // If we are in the Backend-Mode use the BackendUser.
        if(TL_MODE == 'FE'){
            $this->user = \FrontendUser::getInstance();
        } else{
            $this->user = \BackendUser::getInstance();
        }

        // Language
        \Controller::loadLanguageFile("default");
        \Controller::loadLanguageFile('tl_synccto_clients');

        // Instance a list for regex from the blacklist for folders.
        $this->arrPreparedBlacklistFolder = array();
        foreach ($this->getBlacklistFolder() as $key => $value)
        {
            $this->arrPreparedBlacklistFolder[$key] = str_replace($this->arrSearch, $this->arrReplace, $value);
        }

        // Instance a list for regex from the blacklist for files.
        $this->arrPreparedBlacklistFiles = array();
        foreach ($this->getBlacklistFile() as $key => $value)
        {
            $this->arrPreparedBlacklistFiles[$key] = str_replace($this->arrSearch, $this->arrReplace, $value);
        }

        // Instance a list for regex from the hidden table list.
        $this->arrPreparedHiddenTablePlaceholder = array();
        foreach ($this->getHiddenTablePlaceholder() as $key => $value)
        {
            $this->arrPreparedHiddenTablePlaceholder[$key] = str_replace($this->arrSearch, $this->arrReplace, $value);
        }

        // Replace some elements in TL_ROOT for regex.
        $this->strPreparedTlRoot = str_replace('\\', '\\\\', TL_ROOT);
    }

    /**
     * Returns the SyncCtoHelper
     * @return SyncCtoHelper
     */
    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /* -------------------------------------------------------------------------
     * Helper
     */

    /**
     * Parse size
     * @see http://us2.php.net/manual/en/function.ini-get.php#example-501
     *
     * @param string $size
     *
     * @return int|string
     */
    static public function parseSize($size)
    {
        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1]);
        switch ($last)
        {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $size *= 1024;
            case 'm':
                $size *= 1024;
            case 'k':
                $size *= 1024;
        }

        return $size;
    }

    /**
     * Parse the time and return the int-val or PHP_INT_MAX if the value is 0 or -1.
     *
     * @param int $time The time to be parsed.
     *
     * @return int The time parsed as int.
     */
    static public function parseRuntime($time)
    {
        $time = intval($time);
        if ($time == 0 || $time == -1) {
            return PHP_INT_MAX;
        }

        return $time;
    }

    /* -------------------------------------------------------------------------
     * Config
     */

    /**
     * Configuration merge functions
     *
     * @param array $arrLocalconfig
     * @param array $arrSyncCtoConfig
     *
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
        else
        {
            if (!is_array($arrLocalconfig) && is_array($arrSyncCtoConfig))
            {
                return $arrSyncCtoConfig;
            }
            else
            {
                return array();
            }
        }
    }

    /**
     * Get localconfig entries
     *
     * @param int $intTyp
     *
     * @throws Exception
     *
     * @return string
     */
    public function loadConfigs($intTyp = 1)
    {
        if ($intTyp != SyncCtoEnum::LOADCONFIG_KEYS_ONLY && $intTyp != SyncCtoEnum::LOADCONFIG_KEY_VALUE)
        {
            throw new Exception("Unknown type for " . __CLASS__ . " in function " . __FUNCTION__);
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
                else
                {
                    if ($intTyp == SyncCtoEnum::LOADCONFIG_KEY_VALUE)
                    {
                        $key           = str_replace(array("$", "GLOBALS['TL_CONFIG']['", "']"), array("", "", ""), $arrChunks[0]);
                        $arrData[$key] = $GLOBALS['TL_CONFIG'][$key];
                    }
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
        if (!file_exists(TL_ROOT . '/system/config/localconfig.php'))
        {
            throw new Exception('Missing localconfig.php');
        }

        // Check tmp
        if (!is_writable(TL_ROOT . '/system/tmp'))
        {
            throw new Exception('"/system/tmp" is not writable.');
        }

        // Write file
        try
        {
            $objFile = new File('system/config/pathconfig.php');

            // Check if we have the path
            if (TL_PATH === null || TL_PATH == "")
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

    /**
     * Return the TL_ROOT prepared for regex.
     *
     * @return string
     */
    public function getPreparedTlRoot()
    {
        return $this->strPreparedTlRoot;
    }


    public function getBlacklistFolder()
    {
        $arrLocalconfig   = deserialize($GLOBALS['TL_CONFIG']['syncCto_folder_blacklist']);
        $arrSyncCtoConfig = $GLOBALS['SYC_CONFIG']['folder_blacklist'];

        return $this->mergeConfigs($arrLocalconfig, $arrSyncCtoConfig);
    }

    /**
     * Get a prepared list for regex.
     *
     * @return array
     */
    public function getPreparedBlacklistFolder()
    {
        return $this->arrPreparedBlacklistFolder;
    }

    public function getBlacklistFile()
    {
        $arrLocalconfig   = deserialize($GLOBALS['TL_CONFIG']['syncCto_file_blacklist']);
        $arrSyncCtoConfig = $GLOBALS['SYC_CONFIG']['file_blacklist'];

        return $this->mergeConfigs($arrLocalconfig, $arrSyncCtoConfig);
    }

    /**
     * Get a prepared list for regex.
     *
     * @return array
     */
    public function getPreparedBlacklistFiles()
    {
        return $this->arrPreparedBlacklistFiles;
    }

    public function getWhitelistFolder()
    {
        $arrLocalconfig   = deserialize($GLOBALS['TL_CONFIG']['syncCto_folder_whitelist']);
        $arrSyncCtoConfig = $GLOBALS['SYC_CONFIG']['folder_whitelist'];

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

    /**
     * Return a list with the regex list of the hidden table.
     *
     * @return array A list with the entries from the localconfig.
     */
    public function getHiddenTablePlaceholder()
    {
        $arrReturn = array();

        // Get the entries from the loclconfig and add them to the list.
        $arrHiddenTableConfig = deserialize($GLOBALS['TL_CONFIG']['syncCto_hidden_tables_placeholder']);
        if(is_array($arrHiddenTableConfig) && count($arrHiddenTableConfig) != 0)
        {
            foreach ($arrHiddenTableConfig as$value)
            {
                $arrReturn[] =  $value['entries'];
            }
        }

        return $arrReturn;
    }

    /**
     * Return a list with the regex list of the hidden table.
     *
     * @return array A list with the entries from the localconfig.
     */
    public function getPreparedHiddenTablesPlaceholder()
    {
        return $this->arrPreparedHiddenTablePlaceholder;
    }

    /**
     * Check if the table is in the placeholder list.
     *
     * @param string $strTable The table name to check
     *
     * @return bool True => Hit in the Placeholder | False => No hit.
     */
    public function isTableHiddenByPlaceholder($strTable)
    {
        // Check if we have entries.
        if (count($this->arrPreparedHiddenTablePlaceholder) == 0)
        {
            return false;
        }

        // Run each and check it with the given name.
        foreach ($this->arrPreparedHiddenTablePlaceholder as $arrEntry)
        {
            if (preg_match('/^' . $arrEntry . '$/', $strTable))
            {
                return true;
            }
        }

        // No result.
        return false;
    }

    /* -------------------------------------------------------------------------
     * Callbacks
     */

    /**
     * Add the legend to the tmeplate.
     *
     * @param string $strContent  HTML Content.
     * @param string $strTemplate Name of template.
     *
     * @return string HTML content.
     */
    public function addLegend($strContent, $strTemplate)
    {
        // Check some vars if we have the overview.
        $strDo    = \Input::get('do');
        $strTable = \Input::get('table');
        $strAct   = \Input::get('act');

        if ($strDo == 'synccto_clients' && empty($strAct) && empty($strTable) && $strTemplate == 'be_main')
        {
            // Split on the form | globale btn
            $arrContent = explode('<div id="tl_buttons">', $strContent, 2);

            // Check if we have 2 elements.
            if (count($arrContent) != 2)
            {
                return $strContent;
            }

            // Get legend template.
            $objLegendTemplate = new BackendTemplate('be_syncCto_legend');

            // Build new html and return.
            $strReturn = $arrContent[0];
            $strReturn .= $objLegendTemplate->parse();
            $strReturn .= '<div id="tl_buttons">';
            $strReturn .= $arrContent[1];

            return $strReturn;
        }

        return $strContent;
    }

    /**
     * Check the required extensions and files for syncCto
     *
     * @param string $strContent
     * @param string $strTemplate
     *
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
                'DC_General'        => 'dc-general',
                'ZipArchiveCto'     => 'ZipArchiveCto'
            );

            // check for required extensions
            foreach ($arrRequiredExtensions as $key => $val)
            {
                if (!in_array($val, \ModuleLoader::getActive()))
                {
                    $_SESSION["TL_INFO"] = array_merge($_SESSION["TL_INFO"], array($val => 'Please install the required extension <strong>' . $key . '</strong>'));
                }
                else
                {
                    if (is_array($_SESSION["TL_INFO"]) && array_key_exists($val, $_SESSION["TL_INFO"]))
                    {
                        unset($_SESSION["TL_INFO"][$val]);
                    }
                }
            }

            // Check syncCtoPro, if not set remove triggers.
            if (!in_array('syncCtoPro', \ModuleLoader::getActive())
                && ($this->hasTrigger('tl_page') || $this->hasTrigger('tl_article') || $this->hasTrigger('tl_content'))
            )
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
     *
     * @return string
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
        if ($this->user->isAdmin)
        {
            return $GLOBALS['SYC_CONFIG']['sync_options'];
        }
        else
        {
            $arrUserSyncOptions = $this->user->syncCto_sync_options;

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
     * Get a list with all maintenance options
     * @return array
     */
    public function getMaintenanceOptions()
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
                    \Controller::log("Start executing TL_HOOK $callback[0] | $callback[1]", __CLASS__ . "|" . __FUNCTION__, TL_GENERAL);

                    $this->import($callback[0]);
                    $this->$callback[0]->$callback[1]();

                    \Controller::log("Finished executing TL_HOOK $callback[0] | $callback[1]", __CLASS__ . "|" . __FUNCTION__, TL_GENERAL);
                }
                catch (Exception $exc)
                {
                    $arrReturn [] = array(
                        'callback' => implode("|", $callback),
                        'info_msg' => "Error by: TL_HOOK $callback[0] | $callback[1] with Msg: " . $exc->getMessage()
                    );

                    \Controller::log("Error by: TL_HOOK $callback[0] | $callback[1] with Msg: " . $exc->getMessage(), __CLASS__ . "|" . __FUNCTION__, TL_ERROR);
                }
            }
        }

        return $arrReturn;
    }

    /**
     * Import a library and make it accessible by its name or an optional key
     *
     * @param string  $strClass The class name
     * @param string  $strKey   An optional key to store the object under
     * @param boolean $blnForce If true, existing objects will be overridden
     */
    protected function import($strClass, $strKey=null, $blnForce=false)
    {
        $strKey = $strKey ?: $strClass;

        if ($blnForce || !isset($this->arrObjects[$strKey]))
        {
            $this->arrObjects[$strKey] = (in_array('getInstance', get_class_methods($strClass))) ? call_user_func(array($strClass, 'getInstance')) : new $strClass();
        }
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
     *
     * @param string
     * @param integer
     * @param string
     *
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

        $intCharCount   = 0;
        $arrWords       = array();
        $arrChunks      = preg_split('/\s+/', $strString);
        $blnAddEllipsis = false;

        //first part
        foreach ($arrChunks as $chunkKey => $strChunk)
        {
            $intCharCount += utf8_strlen(\StringUtil::decodeEntities($strChunk));

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
        $arrWordsPt2  = array();

        // Second path
        foreach (array_reverse($arrChunks) as $strChunk)
        {
            $intCharCount += utf8_strlen(\StringUtil::decodeEntities($strChunk));

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
            // Make all directory separator to one type.
            $itPath = str_replace('\\', '/', $itPath);
            // Replace some chars.
            $itPath = preg_replace('?^' . str_replace('\\', '\\\\', TL_ROOT) . '?i', '', $itPath);
            // Explode all elements.
            $itPath = explode('/', $itPath);

            // Run each part and check some none valid elements.
            foreach ($itPath as $itFolder)
            {
                // Remove all elements we don't want.
                if ($itFolder === '' || $itFolder === null || $itFolder == "." || $itFolder == "..")
                {
                    continue;
                }

                $arrReturn[] = $itFolder;
            }
        }

        // Build the new path. Use the system directory separator.
        return implode(DIRECTORY_SEPARATOR, $arrReturn);
    }

    /**
     * Add the TL_Root and check if we need a / at the beginning.
     *
     * @param string $strPath The path.
     *
     * @return string The full path.
     */
    public function getFullPath($strPath)
    {
        if ( empty($strPath) )
        {
            return "";
        }

        // Check if we have a separator at the start.
        if ( stripos($strPath, DIRECTORY_SEPARATOR) === 0 )
        {
            return TL_ROOT . $strPath;
        }
        else
        {
            return TL_ROOT . DIRECTORY_SEPARATOR . $strPath;
        }
    }

    /**
     * Check if the path is part of the files.
     *
     * @param string $strPath The path to check.
     *
     * @return boolean
     */
    public function isPartOfFiles($strPath)
    {
        // Clean up the path.
        $strPath       = $this->standardizePath($strPath);
        $strUploadPath = $this->standardizePath($GLOBALS['TL_CONFIG']['uploadPath']);

        // Check the separator.
        if ( DIRECTORY_SEPARATOR == '/' )
        {
            return preg_match('/' . $strUploadPath . '\//i', $strPath);
        }
        else
        {
            return preg_match('/' . $strUploadPath . '\\\\/i', $strPath);
        }
    }


    /**
     * Returns a whole list of all tables in the database
     *
     * @return array
     */
    public function hiddenTables()
    {
        $arrTables = array();

        foreach (\Database::getInstance()->listTables() as $key => $value)
        {
            $arrTables[] = $value;
        }

        return $arrTables;
    }

    /**
     * Returns a list without the hidden tables and the placeholder.
     *
     * @return array
     */
    public function databaseTables()
    {
        $arrTables       = array();
        $arrTablesHidden = $this->getTablesHidden();

        foreach (\Database::getInstance()->listTables() as $key => $value)
        {
            // Check if table is a hidden one.
            if (in_array($value, $arrTablesHidden) || preg_match("/synccto_temp_.*/", $value))
            {
                continue;
            }

            // Check if is a hidden one by the placeholder.
            if($this->isTableHiddenByPlaceholder($value))
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

        $arrTablesPermission = $this->user->syncCto_tables;

        $arrTables = array();

        foreach ($this->databaseTables() as $key => $value)
        {
            if (in_array($value, $arrBlacklist) || preg_match("/synccto_temp_.*/", $value))
            {
                continue;
            }

            if (is_array($arrTablesPermission) && !in_array($value, $arrTablesPermission) && $this->user->isAdmin != true)
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

        $arrTablesPermission = $this->user->syncCto_tables;

        $arrTables = array();

        foreach ($this->databaseTables() as $key => $value)
        {
            if (!in_array($value, $arrBlacklist) || preg_match("/synccto_temp_.*/", $value))
            {
                continue;
            }

            if (is_array($arrTablesPermission) && !in_array($value, $arrTablesPermission) && $this->user->isAdmin != true)
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

        $arrTablesPermission = $this->user->syncCto_tables;

        $arrTables = array();

        foreach (\Database::getInstance()->listTables() as $key => $value)
        {
            if (!in_array($value, $arrBlacklist) && !in_array($value, $arrHiddenlist) || preg_match("/synccto_temp_.*/", $value))
            {
                continue;
            }

            if (is_array($arrTablesPermission) && !in_array($value, $arrTablesPermission) && $this->user->isAdmin != true)
            {
                continue;
            }

            $arrTables[$value] = $this->getTableMeta($value);
        }

        return $arrTables;
    }

    /**
     * Get Table meta
     *
     * @param string $strTableName Name of table.
     *
     * @internal param bool $booHashSame
     *
     * @return string
     */
    private function getTableMeta($strTableName)
    {
        // Count the entries.
        $intCount = \Database::getInstance()
            ->prepare("SELECT COUNT(*) as Count FROM $strTableName")
            ->execute()
            ->Count;

        // Try to build the id list.
        $arrIdParts = array();
        if (\Database::getInstance()->fieldExists('id', $strTableName))
        {
            $objIds = \Database::getInstance()
                ->prepare('SELECT id FROM ' . $strTableName, ' ORDER BY id ASC')
                ->execute();

            $intStart   = null;
            $intLast    = null;

            while ($objIds->next())
            {
                // Init first num.
                if ($intStart == null)
                {
                    $intStart = $objIds->id;
                    $intLast  = $objIds->id;
                    continue;
                }

                // Check if the next number is in line.
                if (($intLast + 1) == $objIds->id)
                {
                    $intLast++;
                }
                else
                {
                    $arrIdParts[] = array(
                        'start' => intval($intStart),
                        'end'   => intval($intLast),
                    );

                    $intStart = $objIds->id;
                    $intLast  = $objIds->id;
                }
            }

            $arrIdParts[] = array(
                'start' => intval($intStart),
                'end'   => intval($intLast),
            );
        }

        $arrTableMeta = array(
            'name'  => $strTableName,
            'count' => $intCount,
            'ids'   => $arrIdParts,
            'size'  => \Database::getInstance()->getSizeOf($strTableName)
        );

        return $arrTableMeta;
    }

    /**
     * Set styles for the given array recommended table data and return it as string
     *
     * @param array $arrTableMeta
     *
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
     * @param string /array $mixTableNames
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

        // Return array.
        $arrTimestamp = array();

        // Load all Tables.
        $arrTables = \Database::getInstance()->listTables();

        $objDBSchema = \Database::getInstance()
            ->prepare("SELECT TABLE_NAME, UPDATE_TIME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?")
            ->execute($GLOBALS['TL_CONFIG']['dbDatabase']);

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
            $objCount = \Database::getInstance()->prepare("SELECT COUNT(*) as count FROM $strTable")->execute();
            if ($objCount->count == 0)
            {
                $arrTimestamp[$strTable] = 0;
            }

            // Load all fields
            $arrFields   = array();
            $arrDBFields = \Database::getInstance()->listFields($strTable);

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
     *
     * @return array
     */
    public function importConfig($arrConfig)
    {
        $arrLocalConfig = $this->loadConfigs(SyncCtoEnum::LOADCONFIG_KEYS_ONLY);

        foreach ($arrConfig as $key => $value)
        {
            if ($key == "disableRefererCheck" && $value == true)
            {
                \Config::getInstance()->update("\$GLOBALS['TL_CONFIG']['ctoCom_disableRefererCheck']", true);
            }

            if (in_array($key, $arrLocalConfig))
            {
                \Config::getInstance()->update("\$GLOBALS['TL_CONFIG']['" . $key . "']", $value);
            }
            else
            {
                \Config::getInstance()->add("\$GLOBALS['TL_CONFIG']['" . $key . "']", $value);
            }
        }

        return true;
    }

    /**
     * Check for customer regular expression
     *
     * @param string $strRegexp
     *
     * @param string $varValue
     *
     * @param Widget $objWidget
     *
     * @return boolean
     */
    public function customRegexp($strRegexp, $varValue, Widget $objWidget)
    {
        switch ($strRegexp) {
            case 'colorRgb':
                if (!preg_match('/^([0-9a-f]{3}|[0-9a-f]{6})$/i', $varValue)) {
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
     *
     * @param array $arrData
     */
    public function checkSubmit($arrCheckSubmit, $arrData)
    {
        $arrPostUnset = array('FORM_SUBMIT', 'FORM_FIELDS', 'REQUEST_TOKEN', 'FORM_INPUTS', 'postUnset', 'error', 'redirectUrl');

        if (is_array($arrCheckSubmit['postUnset']))
        {
            $arrPostUnset = array_merge($arrPostUnset, $arrCheckSubmit['postUnset']);
        }

        foreach ($arrPostUnset AS $value)
        {
            if (array_key_exists($value, $arrData))
            {
                unset($arrData[$value]);
            }
        }

        foreach ($arrData AS $strKey => $value)
        {
            if (empty($value))
            {
                unset($arrData[$strKey]);
            }
        }

        if (count($arrData) > 0)
        {
            if (is_array($_SESSION["TL_ERROR"]))
            {
                if (array_key_exists($arrCheckSubmit['error']['key'], $_SESSION["TL_ERROR"]))
                {
                    unset($_SESSION["TL_ERROR"][$arrCheckSubmit['error']['key']]);
                }
            }

            \Backend::redirect($arrCheckSubmit['redirectUrl']);
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
        \Database::getInstance()->query($strQuery);

        // Drop Insert.
        $strQuery = "DROP TRIGGER IF EXISTS `" . $strTable . "_AfterInsertHashRefresh`";
        \Database::getInstance()->query($strQuery);

        // Drop Delete.
        $strQuery = "DROP TRIGGER IF EXISTS `" . $strTable . "_AfterDeleteHashRefresh`";
        \Database::getInstance()->query($strQuery);
    }

    /**
     * Check if a trigger is set on one of the tables.
     *
     * @return boolean True = we have some triggers | False = no trigger are set.
     */
    public function hasTrigger($strTable)
    {
        $arrTriggers = \Database::getInstance()->query('SHOW TRIGGERS')->fetchEach('Trigger');

        if (in_array($strTable . "_AfterUpdateHashRefresh", $arrTriggers))
        {
            return true;
        }

        if (in_array($strTable . "_AfterInsertHashRefresh", $arrTriggers))
        {
            return true;
        }

        if (in_array($strTable . "_AfterDeleteHashRefresh", $arrTriggers))
        {
            return true;
        }
    }

}
