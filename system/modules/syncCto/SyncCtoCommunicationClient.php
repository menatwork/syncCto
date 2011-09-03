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
class SyncCtoCommunicationClient extends CtoCommunication
{

    protected static $instance = null;
    //-------
    protected $objSyncCtoFiles;
    protected $objSyncCtoHelper;

    /**
     * Constructor
     */
    function __construct()
    {
        parent::__construct();

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
            self::$instance = new SyncCtoCommunicationClient();

        return self::$instance;
    }

    public function setClientBy($id)
    {
        // Load Client from database
        $objClient = $this->Database->prepare("SELECT * FROM tl_synccto_clients WHERE id = %s")
                ->limit(1)
                ->execute((int) $id);

        // Check if a client was loaded
        if ($objClient->numRows == 0)
            throw new Exception("Unknown Client.");

        $strUrl = $objClient->address . ":" . $objClient->port . "/" . $objClient->path . "/ctoCommunication.php";

        $this->setClient($strUrl, $objClient->codifyengine);
        $this->setApiKey($objClient->apikey);
    }

    /* -------------------------------------------------------------------------
     * Security Function
     */

    /**
     * Disable the refferer check on the client
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

    public function purgeTemp()
    {
        return $this->runServer("SYNCCTO_PURGETEMP");
    }

    /*
     * -------------------------------------------------------------------------
     * -------------------------------------------------------------------------
     * 
     * ALT
     * 
     * -------------------------------------------------------------------------
     * -------------------------------------------------------------------------
     */

    public function sendChecksumList($arrChecksumList)
    {
        $arrData = array(
            array(
                "name" => "RPC_CHECKSUM_CHECK",
                "value" => $arrChecksumList,
            ),
        );

        $this->setCodifyEngine(SyncCtoEnum::CODIFY_EMPTY);
        return $this->runServer("RPC_CHECKSUM_CHECK", $arrData);
    }

    /**
     * Send a file to the client
     *
     * @param string $strFile File + pacht. Start from TL_ROOT. ".." will be killed :)
     * @return bool [true|false]
     */
    public function sendFile($strFolder, $strFile, $strMD5 = "", $intTyp = 1, $strSplitname = "")
    {
        // 5 min. time out.
        @set_time_limit(3600);

        // Build folder path
        $mixFolder = explode("/", $strFolder);
        $strFolder = "";
        foreach ($mixFolder as $value)
        {
            if ($value == "")
                continue;

            $strFolder .= "/" . $value;
        }

        $strFilePath = TL_ROOT . $strFolder . "/" . $strFile;

        // Check file exsist
        if (!file_exists($strFilePath) || !is_file($strFilePath))
            throw new Exception("Given file doesn't exists or is not a file. Path: " . $strFilePath);

        // MD5 file hash
        if ($strMD5 == "")
            $strMD5 = md5_file($strFilePath);

        // Contenttyp
        $strMime = "application/octet-stream";

        // Read file
        $fh = fopen($strFilePath, 'rb') or die('Can´t open ' . $this->myfilepfad);

        if ($fh === FALSE)
            throw new Exception("Error by reading file.");

        while (!feof($fh))
        {
            $content .= fread($fh, 10);
        }

        $arrData = array(
            array(
                "name" => $strMD5,
                "filename" => basename($strFilePath),
                "value" => $content,
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

        fclose($fh);

        unset($fh);
        unset($content);

        return $this->runServer("RPC_FILE", $arrData);
    }

    public function startSQLImport($filename)
    {
        $arrData = array(
            array(
                "name" => "RPC_RUN_SQL",
                "value" => $filename,
            ),
        );

        return $this->runServer("RPC_RUN_SQL", $arrData);
    }

    public function startFileImport($arrFilelist)
    {
        $arrData = array(
            array(
                "name" => "RPC_RUN_FILE",
                "value" => $arrFilelist,
            ),
        );

        return $this->runServer("RPC_RUN_FILE", $arrData);
    }

    public function startLocalConfigImport()
    {
        $arrConfigBlacklist = $this->objSyncCtoHelper->getBlacklistLocalconfig();
        $arrConfig = $this->objSyncCtoHelper->loadConfig(SyncCtoEnum::LOADCONFIG_KEY_VALUE);

        foreach ($arrConfig as $key => $value)
        {
            if (in_array($key, $arrConfigBlacklist))
                unset($arrConfig[$key]);
        }

        $arrData = array(
            array(
                "name" => "RPC_RUN_LOCALCONFIG",
                "value" => $arrConfig,
            ),
        );

        return $this->runServer(RPC_RUN_LOCALCONFIG, $arrData);
    }

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

        return $this->runServer("RPC_RUN_SPLITFILE", $arrData);
    }

    public function getChecksumTlfiles()
    {
        $this->setCodifyEngine(SyncCtoEnum::CODIFY_EMPTY);
        return $this->runServer("RPC_CHECKSUM_TLFILES");
    }

    public function getChecksumCore()
    {
        $this->setCodifyEngine(SyncCtoEnum::CODIFY_EMPTY);
        return $this->runServer("RPC_CHECKSUM_CORE");
    }

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

    public function runClientSQLZip()
    {
        return $this->runServer("RPC_SQL_ZIP");
    }

    public function runClientSQLScript($strName, $arrTables)
    {
        $arrData = array(
            array(
                "name" => "name",
                "value" => $strName,
            ),
            array(
                "name" => "tables",
                "value" => $arrTables,
            ),
        );


        return $this->runServer("RPC_SQL_SCRIPT", $arrData);
    }

    public function runClientSQLSyncscript($strName, $arrTables)
    {
        $arrData = array(
            array(
                "name" => "name",
                "value" => $strName,
            ),
            array(
                "name" => "tables",
                "value" => $arrTables,
            ),
        );

        return $this->runServer("RPC_SQL_SYNCSCRIPT", $arrData);
    }

    public function runClientSQLCheck($strName)
    {
        $arrData = array(
            array(
                "name" => "name",
                "value" => $strName,
            ),
            array(
                "name" => "tables",
                "value" => $arrTables,
            ),
        );

        return $this->runServer("RPC_SQL_CHECK", $arrData);
    }

    public function runClientFileSplit($strSrcFile, $strDesFolder, $strDesFile, $intSizeLimit)
    {
        $arrData = array(
            array(
                "name" => "srcfile",
                "value" => $strSrcFile,
            ),
            array(
                "name" => "desfolder",
                "value" => $strDesFolder,
            ),
            array(
                "name" => "desfile",
                "value" => $strDesFile,
            ),
            array(
                "name" => "size",
                "value" => $intSizeLimit,
            ),
        );

        return $this->runServer("RPC_FILE_SPLIT", $arrData);
    }

    public function getClientLocalconfig()
    {
        return $this->runServer("RPC_CONFIG_LOAD");
    }

    public function deleteFiles($arrFilelist)
    {
        $arrData = array(
            array(
                "name" => "list",
                "value" => $arrFilelist,
            )
        );

        return $this->runServer("RPC_FILE_DELETE", $arrData);
    }

}

?>