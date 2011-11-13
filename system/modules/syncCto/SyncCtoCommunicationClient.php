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
 * Communication Class
 * 
 * Extends CtoCommunication witch special RPC-Requests
 */
class SyncCtoCommunicationClient extends CtoCommunication
{
    /* -------------------------------------------------------------------------
     * Vars
     */

    // Singelton Pattern
    protected static $instance = null;
    // Objects
    protected $objSyncCtoFiles;
    protected $objSyncCtoHelper;

    /* -------------------------------------------------------------------------
     * Core
     */

    /**
     * Constructor
     */
    function __construct()
    {
        parent::__construct();

        // Objects
        $this->objSyncCtoFiles = SyncCtoFiles::getInstance();
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
     * @throws Exception
     */
    public function setClientBy($id)
    {
        // Load Client from database
        $objClient = $this->Database->prepare("SELECT * FROM tl_synccto_clients WHERE id = %s")
                ->limit(1)
                ->execute((int) $id);

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
            $strUrl = $objClient->address . ":" . $objClient->port . "/" . $objClient->path . "/ctoCommunication.php";
        }

        $this->setClient($strUrl, $objClient->codifyengine);
        $this->setApiKey($objClient->apikey);

        // Set debug modus for ctoCom.
        if ($GLOBALS['TL_CONFIG']['syncCto_debug_mode'] == true)
        {
            $this->activateDebug = true;
            $this->activateMeasurement = true;
        }
        
        return array(
            "title" => $objClient->title,
            "address" => $objClient->address,
            "path" => $objClient->path,
            "port" => $objClient->port
        );
    }

    /* -------------------------------------------------------------------------
     * Security Function
     */

    /**
     * Disable the referrer check on the client
     * 
     * @return boolean 
     */
    public function refererDisable()
    {
        return $this->runServer("CTOCOM_REFERER_DISABLE");
    }

    /**
     * Enable the refferer check on the client
     * 
     * @return boolean 
     */
    public function refererEnable()
    {
        return $this->runServer("CTOCOM_REFERER_ENABLE");
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
        return $this->runServer("SYNCCTO_VERSION");
    }

    /**
     * Get version from client contao
     *
     * @return string
     */
    public function getVersionContao()
    {
        return $this->runServer("CONTAO_VERSION");
    }

    /**
     * Get version from client contao
     *
     * @return string
     */
    public function getVersionCtoCommunication()
    {
        return $this->runServer("CTOCOM_VERSION");
    }

    /**
     * Get parameter from client
     *
     * @return array
     */
    public function getClientParameter()
    {
        return $this->runServer("SYNCCTO_PARAMETER");
    }

    /* -------------------------------------------------------------------------
     * File Operations
     */

    /**
     * Clear tempfolder
     * 
     * @return type 
     */
    public function purgeTemp()
    {
        return $this->runServer("SYNCCTO_PURGETEMP");
    }

    /**
     * Compare a filelist with the filesystem
     * 
     * @param array $arrChecksumList
     * @return array 
     */
    public function runCecksumCompare($arrChecksumList)
    {
        $arrData = array(
            array(
                "name" => "checksumlist",
                "value" => $arrChecksumList,
            ),
        );

        $this->setCodifyEngine(SyncCtoEnum::CODIFY_EMPTY);
        return $this->runServer("SYNCCTO_CHECKSUM_COMPARE", $arrData);
    }

    /**
     * Get a list with fileinformations from files
     * 
     * @param a $fileList
     * @return type 
     */
    public function getChecksumFiles($arrFileList = NULL)
    {
        $arrData = array(
            array(
                "name" => "fileList",
                "value" => $arrFileList,
            ),
        );

        // Set no codify engine
        $this->setCodifyEngine(SyncCtoEnum::CODIFY_EMPTY);
        
        return $this->runServer("SYNCCTO_CHECKSUM_FILES");
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
        
        return $this->runServer("SYNCCTO_CHECKSUM_CORE");
    }
    
    /**
     * Check for deleted files
     * 
     * @param array $arrFilelist
     * @return array 
     */
    public function checkDeleteFiles($arrFilelist)
    {
         $arrData = array(
            array(
                "name" => "filelist",
                "value" => $arrFilelist,
            ),
        );

        // Set no codify engine
        $this->setCodifyEngine(SyncCtoEnum::CODIFY_EMPTY);
        
        return $this->runServer("SYNCCTO_CHECK_DELETE_FILE");
    }

    /**
     * Send a file to the client
     *
     * @param string $strFile File + path. Start from TL_ROOT.
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
                "name" => $strMD5,
                "filename" => $strFile,
                "filepath" => TL_ROOT . "/" . $strFilePath,
                "mime" => $strMime,
            ),
            array(
                "name" => "metafiles",
                "value" => array(
                    $strMD5 => array(
                        "folder" => $strFolder,
                        "file" => $strFile,
                        "MD5" => $strMD5,
                        "splitname" => $strSplitname,
                        "typ" => $intTyp
                    )
                )
            ),
        );

        return $this->runServer("SYNCCTO_SEND_FILE", $arrData);
    }

    /**
     * Import files from tempfolder
     * 
     * @param array $arrFilelist
     * @return array 
     */
    public function runFileImport($arrFilelist)
    {
        $arrData = array(
            array(
                "name" => "filelist",
                "value" => $arrFilelist,
            ),
        );

        return $this->runServer("SYNCCTO_IMPORT_FILE", $arrData);
    }

    /**
     * Delete files
     * 
     * @param array $arrFilelist
     * @return array 
     */
    public function deleteFiles($arrFilelist)
    {
        $arrData = array(
            array(
                "name" => "filelist",
                "value" => $arrFilelist,
            )
        );

        return $this->runServer("SYNCCTO_DELETE_FILE", $arrData);
    }

    /**
     * Build splitfiles back to one big file
     * 
     * @param type $strSplitname
     * @param type $intSplitcount
     * @param type $strMovepath
     * @param type $strMD5
     * @return type 
     */
    public function buildSingleFile($strSplitname, $intSplitcount, $strMovepath, $strMD5)
    {
        $arrData = array(
            array(
                "name" => "splitname",
                "value" => $strSplitname,
            ),
            array(
                "name" => "splitcount",
                "value" => $intSplitcount,
            ),
            array(
                "name" => "movepath",
                "value" => $strMovepath,
            ),
            array(
                "name" => "md5",
                "value" => $strMD5,
            ),
        );

        return $this->runServer("SYNCCTO_REBUILD_SPLITFILE", $arrData);
    }

    /* -------------------------------------------------------------------------
     * Database Operations
     */

    /**
     * Import a SQL zip
     * 
     * @param type $filename
     * @return type 
     */
    public function runSQLImport($filename)
    {
        $arrData = array(
            array(
                "name" => "filepath",
                "value" => $filename,
            ),
        );

        return $this->runServer("SYNCCTO_IMPORT_DATABASE", $arrData);
    }
    
    public function runDatabaseDump($arrTables, $booTempFolder)
    {
         $arrData = array(
            array(
                "name" => "tables",
                "value" => $arrTables,
            ),
            array(
                "name" => "tempfolder",
                "value" => $booTempFolder,
            ),
        );
         
        return $this->runServer("SYNCCTO_RUN_DUMP", $arrData);
    }
    
     /**
     * Returns a list without the hidden tables
     * 
     * @return array 
     */
    public function getDatabaseTables()
    {
        return $this->runServer("CTO_DATABASE_LISTTABLES");
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
                unset($arrConfig[$key]);
        }

        $arrData = array(
            array(
                "name" => "configlist",
                "value" => $arrConfig,
            ),
        );

        return $this->runServer("SYNCCTO_IMPORT_CONFIG", $arrData);
    }

    /*
     * -------------------------------------------------------------------------
     * -------------------------------------------------------------------------
     * 
     * OLD
     * 
     * -------------------------------------------------------------------------
     * -------------------------------------------------------------------------
     */

    /**
     * Get a file
     * 
     * @param type $strPath
     * @param string $strSavePath
     * @return type 
     */
    public function getFile($strPath, $strSavePath)
    {
        @set_time_limit(3600);

        $arrData = array(
            array(
                "name" => "path",
                "value" => $strPath,
            ),
        );

        $result = $this->runServer("RPC_FILE_GET", $arrData);

        if (file_exists(TL_ROOT . $strSavePath))
        {
            $strVar = TL_ROOT . $strSavePath;
            unset($strVar);
        }

        $arrSavePathPart = explode("/", $strSavePath);
        array_pop($arrSavePathPart);
        $strVar = "";

        foreach ($arrSavePathPart as $itFolder)
        {
            $strVar .= "/" . $itFolder;

            if (!file_exists(TL_ROOT . $strVar))
                mkdir(TL_ROOT . $strVar);
        }

        $strSavePath = TL_ROOT . $strSavePath;

        $fpFile = fopen($strSavePath, 'a+');
        fwrite($fpFile, $result[RPC_FILE_GET]);
        fclose($fpFile);

        if (md5_file($strSavePath) != $result["md5"])
            throw new Exception("MD5 Hash Error.");

        return true;
    }

}

?>