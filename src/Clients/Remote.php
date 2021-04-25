<?php


namespace MenAtWork\SyncCto\Clients;


use MenAtWork\CtoCommunicationBundle\Controller\Server;

class Remote implements IRemote
{
    use TraitClient;

    /**
     * @var string
     */
    private $url;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $apikey;

    /**
     * @var string
     */
    private $codifyEngine;

    /**
     * @var string
     */
    private $http_username;

    /**
     * @var string
     */
    private $http_password;

    /**
     * @var Server
     */
    private $client = null;

    /**
     * @inheritDoc
     */
    public function setUrl(string $url): IRemote
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setPort(int $port): IRemote
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setApiKey(string $apikey): IRemote
    {
        $this->apikey = $apikey;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setCodifyEngine(string $codifyEngine): IRemote
    {
        $this->codifyEngine = $codifyEngine;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setHttpAuth(string $http_username, string $http_password): IRemote
    {
        $this->http_username = $http_username;
        $this->http_password = $http_password;

        return $this;
    }

    /**
     * Setup the client data for ctoCom.
     *
     * @return void
     */
    protected function setupClient(): void
    {
        $this->client = new Server();
        $this->client->setClient($this->url, $this->apikey, $this->codifyEngine);
        if (!empty($this->http_username)) {
            $this->client->setHttpAuth($this->http_username, $this->http_password);
        }
    }

    /**
     * Run the command. See the Server of ctoCom.
     *
     * @param string  $rpc     The RPC name.
     *
     * @param array   $arrData A list of post data.
     *
     * @param boolean $isGET   Flag if use the GET instead of POST.
     *
     * @return mixed
     *
     * @throws \Exception
     */
    protected function run($rpc, $arrData = array(), $isGET = false)
    {
        // Check if we have the client if so run.
        if ($this->client === null) {
            $this->setupClient();
        }

        return $this->client->run($rpc, $arrData, $isGET);
    }

    /* -------------------------------------------------------------------------
     * Server core functions.
     */

    /**
     * @inheritDoc
     */
    public function startConnection()
    {
        // Check if we have the client if so run.
        if ($this->client === null) {
            $this->setupClient();
        }

        $this->client->startConnection();
    }

    /* -------------------------------------------------------------------------
     * Security Function.
     */

    /**
     * @inheritDoc
     */
    public function referrerDisable()
    {
        return $this->run("CTOCOM_REFERRER_DISABLE");
    }

    /**
     * @inheritDoc
     */
    public function referrerEnable()
    {
        return $this->run("CTOCOM_REFERRER_ENABLE");
    }

    /* -------------------------------------------------------------------------
     * Informations
     */

    /**
     * @inheritDoc
     */
    public function getVersionSyncCto()
    {
        return $this->run("SYNCCTO_VERSION");
    }

    /**
     * @inheritDoc
     */
    public function getVersionContao()
    {
        return $this->run("CONTAO_VERSION");
    }

    /**
     * @inheritDoc
     */
    public function getVersionCtoCommunication()
    {
        return $this->run("CTOCOM_VERSION");
    }

    /**
     * @inheritDoc
     */
    public function getClientParameter()
    {
        return $this->run("SYNCCTO_PARAMETER");
    }

    /**
     * @inheritDoc
     */
    public function getPurgData()
    {
        return $this->run("SYNCCTO_GET_PURGEDATA");
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
     */
    public function purgeTempFolder()
    {
        return $this->run("SYNCCTO_PURGETEMP");
    }

    /**
     * @inheritDoc
     */
    public function purgeTempTables()
    {
        return $this->run("SYNCCTO_PURGETEMP_TABLES");
    }

    /**
     * @inheritDoc
     */
    public function purgeCache()
    {
        return $this->run("SYNCCTO_PURGE_CACHE");
    }

    /**
     * @inheritDoc
     */
    public function createCache()
    {
        return $this->run("SYNCCTO_CREATE_CACHE");
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function runFinalOperations()
    {
        return $this->run("SYNCCTO_EXECUTE_FINAL_OPERATIONS");
    }

    /* -------------------------------------------------------------------------
     * File Operations
     */

    /**
     * @inheritDoc
     */
    public function runCecksumCompare($arrChecksumList, $blnDisableDbafsConflicts = false)
    {
        if (!is_array($arrChecksumList)) {
            throw new Exception("File list is not a array.");
        }

        $strPath = $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "syncList.syncCto");
        $strMime = "application/octet-stream";

        \Contao\File::putContent($strPath, serialize($arrChecksumList));

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
                "filepath" => TL_ROOT . "/" . $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'],
                        "syncList.syncCto"),
                "mime"     => $strMime,
            )
        );

        return $this->run("SYNCCTO_CHECKSUM_COMPARE", $arrData);
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function getChecksumCore()
    {
        // Set no codify engine
        $this->setCodifyEngine(SyncCtoEnum::CODIFY_EMPTY);

        return $this->run("SYNCCTO_CHECKSUM_CORE");
    }

    /**
     * @inheritDoc
     */
    public function getChecksumFolderCore()
    {
        return $this->run("SYNCCTO_CHECKSUM_FOLDERS_CORE");
    }

    /**
     * @inheritDoc
     */
    public function getChecksumFolderFiles()
    {
        return $this->run("SYNCCTO_CHECKSUM_FOLDERS_FILES");
    }

    /**
     * @inheritDoc
     */
    public function checkDeleteFiles($arrChecksumList)
    {
        if (!is_array($arrChecksumList)) {
            throw new Exception("Filelist is not a array.");
        }

        $strPath = $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "syncList.syncCto");
        $strMime = "application/octet-stream";

        \Contao\File::putContent($strPath, serialize($arrChecksumList));

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
     * @inheritDoc
     */
    public function searchDeleteFolders($arrChecksumList)
    {
        if (!is_array($arrChecksumList)) {
            throw new Exception("Filelist is not a array.");
        }

        $strPath = $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "syncFolderList.syncCto");
        $strMime = "application/octet-stream";

        \Contao\File::putContent($strPath, serialize($arrChecksumList));

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
     * @inheritDoc
     */
    public function sendFile($strFolder, $strFile, $strMD5 = "", $intTyp = 1, $strSplitname = "")
    {
        // 5 min. time out.
        @set_time_limit(3600);

        //Build path
        $strFilePath = $this->objSyncCtoHelper->standardizePath($strFolder, $strFile);

        // Check file exsist
        if (!file_exists(TL_ROOT . "/" . $strFilePath) || !is_file(TL_ROOT . "/" . $strFilePath)) {
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['unknown_file'], array($strFilePath)));
        }

        // MD5 file hash
        if ($strMD5 == "") {
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
     * @inheritDoc
     */
    public function sendFileNewDestination($strSource, $strDestination, $strMD5 = "", $intTyp = 1, $strSplitname = "")
    {
        // 5 min. time out.
        @set_time_limit(3600);

        //Build path
        $strSource      = $this->objSyncCtoHelper->standardizePath($strSource);
        $strDestination = $this->objSyncCtoHelper->standardizePath($strDestination);

        // Check file exsist
        if (!file_exists(TL_ROOT . "/" . $strSource) || !is_file(TL_ROOT . "/" . $strSource)) {
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['unknown_file'], array($strSource)));
        }

        // MD5 file hash
        if ($strMD5 == "") {
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
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
        \Contao\File::putContent($strSavePath, base64_decode($arrResult["content"]));
        if (md5_file(TL_ROOT . "/" . $strSavePath) != $arrResult["md5"]) {
            throw new Exception($GLOBALS['TL_LANG']['ERR']['checksum_error']);
        }

        return true;
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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

    /**
     * @inheritDoc
     */
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
     * @inheritDoc
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
     * @inheritDoc
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

    /**
     * @inheritDoc
     */
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
     * @inheritDoc
     */
    public function getRecommendedTables()
    {
        return $this->run("SYNCCTO_RECOMMENDED_TABLES");
    }

    /**
     * @inheritDoc
     */
    public function getNoneRecommendedTables()
    {
        return $this->run("SYNCCTO_NONERECOMMENDED_TABLES");
    }


    /**
     * @inheritDoc
     */
    public function getHiddenTables()
    {
        return $this->run("SYNCCTO_HIDDEN_TABLES");
    }

    /**
     * @inheritDoc
     */
    public function getPreparedHiddenTablesPlaceholder()
    {
        return $this->run("SYNCCTO_HIDDEN_TABLES_PLACEHOLDER");
    }

    /**
     * @inheritDoc
     */
    public function getTablesHash($tables)
    {
        return $this->run("SYNCCTO_DATABASE_HASH", $tables);
    }

    /* -------------------------------------------------------------------------
     * Config Operations
     */

    /**
     * @inheritDoc
     */
    public function runLocalConfigImport()
    {
        // Load blacklist for localconfig
        $arrConfigBlacklist = $this->objSyncCtoHelper->getBlacklistLocalconfig();
        // Load localconfig
        $arrConfig = $this->objSyncCtoHelper->loadConfigs(SyncCtoEnum::LOADCONFIG_KEY_VALUE);

        // Kick blacklist entries
        foreach ($arrConfig as $key => $value) {
            if (in_array($key, $arrConfigBlacklist)) {
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

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
    public function getPhpFunctions()
    {
        return $this->run("SYNCCTO_GET_PHP_FUNCTIONS");
    }

    /**
     * @inheritDoc
     */
    public function getProFunctions()
    {
        return $this->run("SYNCCTO_GET_PRO_FUNCTIONS");
    }

    /**
     * @inheritDoc
     */
    public function getPhpConfigurations()
    {
        return $this->run("SYNCCTO_GET_PHP_CONFIGURATION");
    }

    /**
     * @inheritDoc
     */
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
     * @inheritDoc
     */
    public function createPathconfig()
    {
        return $this->run("SYNCCTO_CREATE_PATHCONFIG");
    }

    /* -------------------------------------------------------------------------
     * Auto Updater
     */

    /**
     * @inheritDoc
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