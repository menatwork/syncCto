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
 * Communication Class
 *
 * Extends CtoCommunication witch special RPC-Requests
 */
class SyncCtoCommunicationClient extends \CtoCommunication\Serivces\Server
{
    /* -------------------------------------------------------------------------
     * Vars
     */

    // Singelton Pattern
    protected static $instance = null;
    // Objects
    protected $objSyncCtoFiles;
    protected $objSyncCtoHelper;
    // Vars
    protected $arrClientData = array();

    /* -------------------------------------------------------------------------
     * Core
     */

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // Objects
        $this->objSyncCtoFiles  = SyncCtoFiles::getInstance();
        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();
    }

    /**
     * Singelton Pattern
     *
     * @return SyncCtoCommunicationClient
     */
    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new SyncCtoCommunicationClient();
        }

        return self::$instance;
    }

    /**
     * Set client by id
     *
     * @param int $id
     *
     * @throws Exception
     */
    public function setClientBy($id)
    {
        // Load Client from database
        $objClient = \Database::getInstance()->prepare("SELECT * FROM tl_synccto_clients WHERE id = %s")
            ->limit(1)
            ->execute((int)$id);

        // Check if a client was loaded
        if ($objClient->numRows == 0)
        {
            throw new Exception($GLOBALS['TL_LANG']['ERR']['unknown_client']);
        }

        // Clean url
        $objClient->path = preg_replace("/\/\z/i", "", $objClient->path);
        $objClient->path = preg_replace("/ctoCommunication.php\z/i", "", $objClient->path);

        // Build path
        if ($objClient->path == "")
        {
            $strUrl = $objClient->address . ":" . $objClient->port . "/ctoCommunication.php";
        }
        else
        {
            $strUrl = $objClient->address . ":" . $objClient->port . $objClient->path . "/ctoCommunication.php";
        }

        $this->setClient($strUrl, $objClient->apikey, $objClient->codifyengine);

        if ($objClient->http_auth == true)
        {
            $this->strHTTPUser     = $objClient->http_username;
            $this->strHTTPPassword = \Encryption::decrypt($objClient->http_password);
        }

        // Set debug modus for ctoCom.
        if ($GLOBALS['TL_CONFIG']['syncCto_debug_mode'] == true)
        {
            $this->setDebug(true);
            $this->setMeasurement(true);

            $this->setFileDebug($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['debug'], "CtoComDebug.txt"));
            $this->setFileMeasurement($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['debug'], "CtoComMeasurement.txt"));
        }

        $this->arrClientData = array(
            "title"   => $objClient->title,
            "address" => $objClient->address,
            "path"    => $objClient->path,
            "port"    => $objClient->port
        );

        return $this->arrClientData;
    }

    /**
     * Return a list with basic client informations
     *
     * @return array {title, address, path, port}
     */
    public function getClientData()
    {
        return $this->arrClientData;
    }


    /* -------------------------------------------------------------------------
     * Security Function
     */

    /**
     * Disable the referrer check on the client
     *
     * @return boolean
     */
    public function referrerDisable()
    {
        return $this->run("CTOCOM_REFERRER_DISABLE");
    }

    /**
     * Enable the referrer check on the client
     *
     * @return boolean
     */
    public function referrerEnable()
    {
        return $this->run("CTOCOM_REFERRER_ENABLE");
    }

    /* -------------------------------------------------------------------------
     * Informations
     */

    /**
     * Get version from client syncCto
     *
     * @return string
     */
    public function getVersionSyncCto()
    {
        return $this->run("SYNCCTO_VERSION");
    }

    /**
     * Get version from client contao
     *
     * @return string
     */
    public function getVersionContao()
    {
        return $this->run("CONTAO_VERSION");
    }

    /**
     * Get version from client contao
     *
     * @return string
     */
    public function getVersionCtoCommunication()
    {
        return $this->run("CTOCOM_VERSION");
    }

    /**
     * Get parameter from client
     *
     * @return array
     */
    public function getClientParameter()
    {
        return $this->run("SYNCCTO_PARAMETER");
    }

    /**
     * Get informations for purgedata
     *
     * @return array
     */
    public function getPurgData()
    {
        return $this->run("SYNCCTO_GET_PURGEDATA");
    }

    /**
     * Set the attention flag from client
     *
     * @param boolean $booState
     */
    public function setAttentionFlag($booState)
    {
        $arrData = array(
            array(
                "name"  => "state",
                "value" => $booState,
            ),
        );

        return $this->run("SYNCCTO_SET_ATTENTION_FLAG", $arrData);
    }

    /**
     * Set the reffere flag from client
     *
     * @param boolean $booState
     */
    public function setDisplayErrors($booState)
    {
        $arrData = array(
            array(
                "name"  => "state",
                "value" => $booState,
            ),
        );

        return $this->run("SYNCCTO_SET_DISPLAY_ERRORS_FLAG", $arrData);
    }

    /* -------------------------------------------------------------------------
     * Maintance
     */

    /**
     * Clear tempfolder
     *
     * @return type
     */
    public function purgeTempFolder()
    {
        return $this->run("SYNCCTO_PURGETEMP");
    }

    public function purgeTempTables()
    {
        return $this->run("SYNCCTO_PURGETEMP_TABLES");
    }

    /**
     * Clean the cache.
     *
     * @return mixed
     */
    public function purgeCache()
    {
        return $this->run("SYNCCTO_PURGE_CACHE");
    }

    /**
     * Rebuild the cache.
     *
     * @return mixed
     */
    public function createCache()
    {
        return $this->run("SYNCCTO_CREATE_CACHE");
    }

    /**
     * Use the contao function for maintance
     *
     * @return boolean
     */
    public function runMaintenance($arrSettings)
    {
        $arrData = array(
            array(
                "name"  => "options",
                "value" => $arrSettings,
            )
        );

        return $this->run("SYNCCTO_MAINTENANCE", $arrData);
    }

    /**
     * Call the last operations on client side.
     *
     * @return array with information.
     */
    public function runFinalOperations()
    {
        return $this->run("SYNCCTO_EXECUTE_FINAL_OPERATIONS");
    }

    /* -------------------------------------------------------------------------
     * File Operations
     */

    /**
     * Compare a file list with the filesystem
     *
     * @param array $arrChecksumList
     *
     * @throws Exception
     *
     * @return array
     */
    public function runCecksumCompare($arrChecksumList, $blnDisableDbafsConflicts = false)
    {
        if (!is_array($arrChecksumList))
        {
            throw new Exception("File list is not a array.");
        }

        $strPath = $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "syncList.syncCto");
        $strMime = "application/octet-stream";

        $objFile = new File($strPath);
        $objFile->write(serialize($arrChecksumList));
        $objFile->close();

        $arrData = array(
            array(
                "name"  => "md5",
                "value" => md5_file(TL_ROOT . "/" . $strPath),
            ),
            array(
                "name"  => "file",
                "value" => md5($strPath),
            ),
            array(
                "name"  => "disable_dbafs_conflicts",
                "value" => $blnDisableDbafsConflicts
            ),
            array(
                "name"     => md5($strPath),
                "filename" => "syncList.syncCto",
                "filepath" => TL_ROOT . "/" . $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "syncList.syncCto"),
                "mime"     => $strMime,
            )
        );

        return $this->run("SYNCCTO_CHECKSUM_COMPARE", $arrData);
    }

    /**
     * Get a list with fileinformations from files
     *
     * @param a $fileList
     *
     * @return type
     */
    public function getChecksumFiles($arrFileList = null)
    {
        $arrData = array(
            array(
                "name"  => "fileList",
                "value" => $arrFileList,
            ),
        );

        // Set no codify engine
        $this->setCodifyEngine(SyncCtoEnum::CODIFY_EMPTY);

        return $this->run("SYNCCTO_CHECKSUM_FILES", $arrData);
    }

    /**
     * Get a list with files from contao core
     *
     * @return type
     */
    public function getChecksumCore()
    {
        // Set no codify engine
        $this->setCodifyEngine(SyncCtoEnum::CODIFY_EMPTY);

        return $this->run("SYNCCTO_CHECKSUM_CORE");
    }

    /**
     * Get a list with folder from contao core
     *
     * @return type
     */
    public function getChecksumFolderCore()
    {
        return $this->run("SYNCCTO_CHECKSUM_FOLDERS_CORE");
    }

    /**
     * Get a list with folder from contao core
     *
     * @return type
     */
    public function getChecksumFolderFiles()
    {
        return $this->run("SYNCCTO_CHECKSUM_FOLDERS_FILES");
    }

    /**
     * Check for deleted files
     *
     * @param array $arrFilelist
     *
     * @return array
     */
    public function checkDeleteFiles($arrChecksumList)
    {
        if (!is_array($arrChecksumList))
        {
            throw new Exception("Filelist is not a array.");
        }

        $strPath = $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "syncList.syncCto");
        $strMime = "application/octet-stream";

        $objFile = new File($strPath);
        $objFile->write(serialize($arrChecksumList));
        $objFile->close();

        $arrData = array(
            array(
                "name"  => "md5",
                "value" => md5_file(TL_ROOT . "/" . $strPath),
            ),
            array(
                "name"  => "file",
                "value" => md5($strPath),
            ),
            array(
                "name"     => md5($strPath),
                "filename" => "syncList.syncCto",
                "filepath" => TL_ROOT . "/" . $strPath,
                "mime"     => $strMime,
            )
        );

        return $this->run("SYNCCTO_CHECK_DELETE_FILE", $arrData);
    }

    /**
     * Check for deleted files
     *
     * @param array $arrFilelist
     *
     * @return array
     */
    public function searchDeleteFolders($arrChecksumList)
    {
        if (!is_array($arrChecksumList))
        {
            throw new Exception("Filelist is not a array.");
        }

        $strPath = $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "syncFolderList.syncCto");
        $strMime = "application/octet-stream";

        $objFile = new File($strPath);
        $objFile->write(serialize($arrChecksumList));
        $objFile->close();

        $arrData = array(
            array(
                "name"  => "md5",
                "value" => md5_file(TL_ROOT . "/" . $strPath),
            ),
            array(
                "name"  => "file",
                "value" => md5($strPath),
            ),
            array(
                "name"     => md5($strPath),
                "filename" => "syncFolderList.syncCto",
                "filepath" => TL_ROOT . "/" . $strPath,
                "mime"     => $strMime,
            )
        );

        return $this->run("SYNCCTO_SEARCH_DELETE_FOLDERS", $arrData);
    }

    /**
     * Send a file to the client
     *
     * @param string $strFile File + path. Start from TL_ROOT.
     *
     * @return bool [true|false]
     */
    public function sendFile($strFolder, $strFile, $strMD5 = "", $intTyp = 1, $strSplitname = "")
    {
        // 5 min. time out.
        @set_time_limit(3600);

        //Build path
        $strFilePath = $this->objSyncCtoHelper->standardizePath($strFolder, $strFile);

        // Check file exsist
        if (!file_exists(TL_ROOT . "/" . $strFilePath) || !is_file(TL_ROOT . "/" . $strFilePath))
        {
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['unknown_file'], array($strFilePath)));
        }

        // MD5 file hash
        if ($strMD5 == "")
        {
            $strMD5 = md5_file(TL_ROOT . "/" . $strFilePath);
        }

        // Contenttyp
        $strMime = "application/octet-stream";

        // Build array with informations
        $arrData = array(
            array(
                "name"     => $strMD5,
                "filename" => $strFile,
                "filepath" => TL_ROOT . "/" . $strFilePath,
                "mime"     => $strMime,
            ),
            array(
                "name"  => "metafiles",
                "value" => array(
                    $strMD5 => array(
                        "folder"    => $strFolder,
                        "file"      => $strFile,
                        "MD5"       => $strMD5,
                        "splitname" => $strSplitname,
                        "typ"       => $intTyp
                    )
                )
            ),
        );

        return $this->run("SYNCCTO_SEND_FILE", $arrData);
    }

    /**
     * Send a file to the client
     *
     * @param string $strFile File + path. Start from TL_ROOT.
     *
     * @return bool [true|false]
     */
    public function sendFileNewDestination($strSource, $strDestination, $strMD5 = "", $intTyp = 1, $strSplitname = "")
    {
        // 5 min. time out.
        @set_time_limit(3600);

        //Build path
        $strSource      = $this->objSyncCtoHelper->standardizePath($strSource);
        $strDestination = $this->objSyncCtoHelper->standardizePath($strDestination);

        // Check file exsist
        if (!file_exists(TL_ROOT . "/" . $strSource) || !is_file(TL_ROOT . "/" . $strSource))
        {
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['unknown_file'], array($strSource)));
        }

        // MD5 file hash
        if ($strMD5 == "")
        {
            $strMD5 = md5_file(TL_ROOT . "/" . $strSource);
        }

        // Contenttyp
        $strMime = "application/octet-stream";

        // Build array with informations
        $arrData = array(
            array(
                "name"     => $strMD5,
                "filename" => basename($strSource),
                "filepath" => TL_ROOT . "/" . $strSource,
                "mime"     => $strMime,
            ),
            array(
                "name"  => "metafiles",
                "value" => array(
                    $strMD5 => array(
                        "folder"    => dirname($strDestination),
                        "file"      => basename($strDestination),
                        "MD5"       => $strMD5,
                        "splitname" => $strSplitname,
                        "typ"       => $intTyp
                    )
                )
            ),
        );

        return $this->run("SYNCCTO_SEND_FILE", $arrData);
    }

    /**
     * Import files from temp folder to the target source. If we have tl_files the system
     * tries to solve problems with the dbafs data from contao.
     *
     * @param array   $arrFileList The list with all files.
     *
     * @param boolean $blnIsDbafs  If true the system tries to run the support for the Contao dbafs.
     *
     * @return array Return a array with information from the client.
     */
    public function runFileImport($arrFileList, $blnIsDbafs)
    {
        $arrData = array(
            array(
                "name"  => "filelist",
                "value" => $arrFileList,
            ),
            array(
                "name"  => "dbafs",
                "value" => $blnIsDbafs,
            ),
        );

        return $this->run("SYNCCTO_IMPORT_FILE", $arrData);
    }

    /**
     * Update the DBAFS
     *
     * @param array $arrFileList The list with all files.
     *
     * @return mixed
     */
    public function updateDbafs($arrFileList)
    {
        $arrData = array(
            array(
                "name"  => "filelist",
                "value" => $arrFileList,
            )
        );

        return $this->run("SYNCCTO_UPDATE_DBAFS", $arrData);
    }

    /**
     * Delete files
     *
     * @param array   $arrFileList The list with all files.
     *
     * @param boolean $blnIsDbafs  If true the system tries to run the support for the Contao dbafs.
     *
     * @return array Return a array with information from the client.
     */
    public function deleteFiles($arrFileList, $blnIsDbafs)
    {
        $arrData = array(
            array(
                "name"  => "filelist",
                "value" => $arrFileList,
            ),
            array(
                "name"  => "dbafs",
                "value" => $blnIsDbafs,
            ),
        );

        return $this->run("SYNCCTO_DELETE_FILE", $arrData);
    }

    /**
     * Build splitfiles back to one big file
     *
     * @param type $strSplitname
     * @param type $intSplitcount
     * @param type $strMovepath
     * @param type $strMD5
     *
     * @return type
     */
    public function buildSingleFile($strSplitname, $intSplitcount, $strMovepath, $strMD5)
    {
        $arrData = array(
            array(
                "name"  => "splitname",
                "value" => $strSplitname,
            ),
            array(
                "name"  => "splitcount",
                "value" => $intSplitcount,
            ),
            array(
                "name"  => "movepath",
                "value" => $strMovepath,
            ),
            array(
                "name"  => "md5",
                "value" => $strMD5,
            ),
        );

        return $this->run("SYNCCTO_REBUILD_SPLITFILE", $arrData);
    }

    /**
     * Build splitfiles back to one big file
     *
     * @param type $strSplitname
     * @param type $intSplitcount
     * @param type $strMovepath
     * @param type $strMD5
     *
     * @return type
     */
    public function runSplitFiles($strSrcFile, $strDesFolder, $strDesFile, $intSizeLimit)
    {
        $arrData = array(
            array(
                "name"  => "splitname",
                "value" => $strSrcFile,
            ),
            array(
                "name"  => "destfolder",
                "value" => $strDesFolder,
            ),
            array(
                "name"  => "destfile",
                "value" => $strDesFile,
            ),
            array(
                "name"  => "limit",
                "value" => $intSizeLimit,
            ),
        );

        return $this->run("SYNCCTO_SPLITFILE", $arrData);
    }

    /**
     * Get a file
     *
     * @param type   $strPath
     * @param string $strSavePath
     *
     * @return boolean
     */
    public function getFile($strPath, $strSavePath)
    {
        $arrData = array(
            array(
                "name"  => "path",
                "value" => $strPath,
            ),
        );

        $arrResult = $this->run("SYNCCTO_GET_FILE", $arrData);

        $objFile = new File($strSavePath);
        $objFile->write(base64_decode($arrResult["content"]));
        $objFile->close();

        if (md5_file(TL_ROOT . "/" . $strSavePath) != $arrResult["md5"])
        {
            throw new Exception($GLOBALS['TL_LANG']['ERR']['checksum_error']);
        }

        return true;
    }

    /**
     * Get a list or a string with path information from
     * syncCto.
     *
     * @param string $strName
     *
     * @return mixed
     */
    public function getPathList($strName = null)
    {
        $arrData = array(
            array(
                "name"  => "name",
                "value" => $strName,
            )
        );

        return $this->run("SYNCCTO_GET_PATHLIST", $arrData);
    }

    /**
     * Get for a list of files the DBAFS information. If the file in not in the DBAFS
     * add it to the DBAFS and than get the information.
     *
     * @param array $arrFiles List of files
     *
     * @return array Return the file list with the information from the dbafs.
     */
    public function getDbafsInformationFor($arrFiles)
    {
        $arrData = array(
            array(
                "name"  => "files",
                "value" => $arrFiles,
            )
        );

        return $this->run("SYNCCTO_DBAFS_INFORMATION", $arrData);
    }

    /* -------------------------------------------------------------------------
     * Database Operations
     */

    /**
     * Import a SQL zip
     *
     * @param type $filename
     *
     * @return type
     */
    public function runSQLImport($filename, $additionalSQL)
    {
        $arrData = array(
            array(
                "name"  => "filepath",
                "value" => $filename,
            ),
            array(
                "name"  => "additionalSQL",
                "value" => $additionalSQL,
            ),
        );

        return $this->run("SYNCCTO_IMPORT_DATABASE", $arrData);
    }

    public function runDatabaseDump($arrTables, $booTempFolder)
    {
        $arrData = array(
            array(
                "name"  => "tables",
                "value" => $arrTables,
            ),
            array(
                "name"  => "tempfolder",
                "value" => $booTempFolder,
            ),
        );

        return $this->run("SYNCCTO_RUN_DUMP", $arrData);
    }

    /**
     * Drop tables on client site
     *
     * @param array   $arrTables
     * @param boolean $blnBackup
     *
     * @return void
     */
    public function dropTable($arrTables, $blnBackup)
    {
        $arrData = array(
            array(
                "name"  => "tablelist",
                "value" => $arrTables,
            ),
            array(
                "name"  => "backup",
                "value" => $blnBackup,
            ),
        );

        return $this->run("SYNCCTO_DROP_TABLES", $arrData);
    }

    /**
     * Exceute SQL commands on client side
     *
     * @param array $arrSQL array([ID] => <br/>array("prepare" => [String(SQL)], "execute" => array([mix]) ) <br/>)
     *
     * @return array array([ID] => response)
     */
    public function executeSQL($arrSQL)
    {
        $arrData = array(
            array(
                "name"  => "sql",
                "value" => $arrSQL,
            )
        );

        return $this->run("SYNCCTO_EXECUTE_SQL", $arrData);
    }

    public function getClientTimestamp($arrTableList)
    {
        $arrData = array(
            array(
                "name"  => "TableList",
                "value" => $arrTableList,
            ),
        );

        return $this->run("SYNCCTO_TIMESTAMP", $arrData);
    }

    /**
     * Returns a list without the hidden tables
     *
     * @return array
     */
    public function getRecommendedTables()
    {
        return $this->run("SYNCCTO_RECOMMENDED_TABLES");
    }

    /**
     * Returns a list without the hidden tables
     *
     * @return array
     */
    public function getNoneRecommendedTables()
    {
        return $this->run("SYNCCTO_NONERECOMMENDED_TABLES");
    }


    /**
     * Returns a list without the hidden tables
     *
     * @return array
     */
    public function getHiddenTables()
    {
        return $this->run("SYNCCTO_HIDDEN_TABLES");
    }

    /**
     * Returns a list without the hidden tables
     *
     * @return array
     */
    public function getPreparedHiddenTablesPlaceholder()
    {
        return $this->run("SYNCCTO_HIDDEN_TABLES_PLACEHOLDER");
    }

    /* -------------------------------------------------------------------------
     * Config Operations
     */

    /**
     * Import localconfig
     *
     * @return type
     */
    public function runLocalConfigImport()
    {
        // Load blacklist for localconfig
        $arrConfigBlacklist = $this->objSyncCtoHelper->getBlacklistLocalconfig();
        // Load localconfig
        $arrConfig = $this->objSyncCtoHelper->loadConfigs(SyncCtoEnum::LOADCONFIG_KEY_VALUE);

        // Kick blacklist entries
        foreach ($arrConfig as $key => $value)
        {
            if (in_array($key, $arrConfigBlacklist))
            {
                unset($arrConfig[$key]);
            }
        }

        $arrData = array(
            array(
                "name"  => "configlist",
                "value" => $arrConfig,
            ),
        );

        return $this->run("SYNCCTO_IMPORT_CONFIG", $arrData);
    }

    public function getLocalConfig()
    {
        $arrData = array(
            array(
                "name"  => "ConfigBlacklist",
                "value" => $this->objSyncCtoHelper->getBlacklistLocalconfig(),
            ),
        );


        return $this->run("SYNCCTO_GET_CONFIG", $arrData);
    }

    public function getPhpFunctions()
    {
        return $this->run("SYNCCTO_GET_PHP_FUNCTIONS");
    }

    public function getProFunctions()
    {
        return $this->run("SYNCCTO_GET_PRO_FUNCTIONS");
    }

    public function getPhpConfigurations()
    {
        return $this->run("SYNCCTO_GET_PHP_CONFIGURATION");
    }

    public function getExtendedInformation($strDateFormate)
    {
        $arrData = array(
            array(
                "name"  => "DateFormate",
                "value" => $strDateFormate,
            ),
        );

        return $this->run("SYNCCTO_GET_EXTENDED_INFORMATIONS", $arrData);
    }

    /**
     * Create a file which contains the relative path
     *
     * @throws Exception
     * @return boolean
     */
    public function createPathconfig()
    {
        return $this->run("SYNCCTO_CREATE_PATHCONFIG");
    }

    /* -------------------------------------------------------------------------
     * Auto Updater
     */

    public function startAutoUpdater($strZipPath)
    {
        $arrData = array(
            array(
                "name"  => "zipfile",
                "value" => $strZipPath,
            ),
        );

        return $this->run("SYNCCTO_AUTO_UPDATE", $arrData);
    }

}

?>
