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
class SyncCtoRPCFunctions extends Backend
{

    protected $objSyncCtoFiles;
    protected $objSyncCtoHelper;
    protected $objSyncCtoDatabase;
    protected $objSyncCtoMeasurement;
    protected $BackendUser;
    protected $Encryption;
    protected $Config;

    /**
     * Constructor
     */
    function __construct()
    {
        $this->BackendUser = BackendUser::getInstance();
        
        parent::__construct();

        $this->Encryption = Encryption::getInstance();
        $this->Config = Config::getInstance();

        $this->objSyncCtoFiles = SyncCtoFiles::getInstance();
        $this->objSyncCtoDatabase = SyncCtoDatabase::getInstance();
        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();

        $this->loadLanguageFile("syncCto");

        $this->arrDebug = array();
    }

    /*
     * -------------------------------------------------------------------------
     * -------------------------------------------------------------------------
     * 
     * RPC FUNCTIONS for client 
     * 
     * -------------------------------------------------------------------------
     * -------------------------------------------------------------------------
     */

    /* -------------------------------------------------------------------------
     * RPC Functions - User
     */

    /**
     * Authenticate the user.
     */
    public function rpc_auth()
    {
        // Try to authenticate user
        if ($this->BackendUser->authenticate() === FALSE)
            throw new Exception("Auth fail.");

        return;
    }

    /**
     * User login. Username and password are set by Post.
     */
    public function rpc_login()
    {
        if (!$this->BackendUser->login())
            throw new Exception("Could not login.");
    }

    /**
     * User logout.
     */
    public function rpc_logout()
    {
        if (!$this->BackendUser->logout())
            throw new Exception("Could not logout.");
    }

    /* -------------------------------------------------------------------------
     * RPC Functions - Helper 
     */

    /**
     * Calculate 
     * 
     * @param int $int 
     */
    public function rpc_calc($int)
    {
        return $int * 2;
    }

    /**
     * Send the version number of this syncCto
     */
    public function rpc_version()
    {
        return SYNCCTO_GET_VERSION;
    }

    /**
     * Send informations about this php instalation
     */
    public function rpc_parameter()
    {
        return array
            (
            'max_execution_time' => ini_get('max_execution_time'),
            'memory_limit' => ini_get('memory_limit'),
            'file_uploads' => ini_get('file_uploads'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size')
        );
    }

    /**
     * Purge Tempfolder          
     */
    public function rpc_clear_temp()
    {
        $this->objSyncCtoFiles->purgeTemp();
        return true;
    }

    /* -------------------------------------------------------------------------
     * RPC Functions - Checksums 
     */

    public function rpc_checksum_check($arrChecksumList)
    {
        return $this->objSyncCtoFiles->runCecksum($arrChecksumList);
    }

    public function rpc_checksum_core()
    {
        return $this->objSyncCtoFiles->runCoreFilesChecksum();
    }

    public function rpc_checksum_tlfiles()
    {
        return $this->objSyncCtoFiles->runTlFilesChecksum(array("tl_files"));
    }

    /* -------------------------------------------------------------------------
     * RPC Functions - Referer 
     */

    public function rpc_referer_disable()
    {
        $this->import("Config");
        $this->Config->update("\$GLOBALS['TL_CONFIG']['disableRefererCheck']", true);
        return true;
    }

    public function rpc_referer_enable()
    {
        $this->import("Config");
        $this->Config->update("\$GLOBALS['TL_CONFIG']['disableRefererCheck']", false);
        return false;
    }

    /* -------------------------------------------------------------------------
     * RPC Functions - KA 
     */

    public function rpc_file($arrMetafiles)
    {
        $arrMetafiles = deserialize($arrMetafiles);

        if (!is_array($arrMetafiles))
            throw new Exception("Missing metafiles in array check.");

        $arrResponse = array();

        foreach ($_FILES as $key => $value)
        {
            $strFolder = $arrMetafiles[$key]["folder"];
            $mixFolder = explode("/", $strFolder);
            $strFile = $arrMetafiles[$key]["file"];
            $strMD5 = $arrMetafiles[$key]["MD5"];

            switch ($arrMetafiles[$key]["typ"])
            {
                case SyncCtoEnum::UPLOAD_TEMP:
                    $strSaveFolder = TL_ROOT . $GLOBALS['syncCto']['path']['tmp'] . $strFolder;
                    $strSaveFile = TL_ROOT . $GLOBALS['syncCto']['path']['tmp'] . $strFolder . "/" . $strFile;
                    break;

                case SyncCtoEnum::UPLOAD_SYNC_TEMP:
                    $strSaveFolder = TL_ROOT . $GLOBALS['syncCto']['path']['tmp'] . "sync/" . $strFolder;
                    $strSaveFile = TL_ROOT . $GLOBALS['syncCto']['path']['tmp'] . "sync/" . $strFolder . "/" . $strFile;
                    array_unshift($mixFolder, "sync");
                    break;

                case SyncCtoEnum::UPLOAD_SQL_TEMP:
                    $strSaveFolder = TL_ROOT . $GLOBALS['syncCto']['path']['tmp'] . "sql/";
                    $strSaveFile = TL_ROOT . $GLOBALS['syncCto']['path']['tmp'] . "sql/" . $strFile;
                    $mixFolder = array("sql");
                    break;

                case SyncCtoEnum::UPLOAD_SYNC_SPLIT:
                    $strSaveFolder = TL_ROOT . $GLOBALS['syncCto']['path']['tmp'] . $arrMetafiles[$key]["splitname"] . "/";
                    $strSaveFile = TL_ROOT . $GLOBALS['syncCto']['path']['tmp'] . $arrMetafiles[$key]["splitname"] . "/" . $strFile;
                    $mixFolder = array($arrMetafiles[$key]["splitname"]);
                    break;

                default:
                    throw new Exception("Unknown Path for file.");
                    break;
            }

            $strPartFolder = "";
            foreach ($mixFolder as $folderpart)
            {
                $strPartFolder .= "/" . $folderpart;

                if (!file_exists(TL_ROOT . $GLOBALS['syncCto']['path']['tmp'] . $strPartFolder))
                    mkdir(TL_ROOT . $GLOBALS['syncCto']['path']['tmp'] . $strPartFolder);
            }

            if (move_uploaded_file($value["tmp_name"], $strSaveFile) === FALSE)
            {
                $arrResponse[$key] = "Error move file.";
            }
            else if ($key != md5_file($strSaveFile))
            {
                $arrResponse[$key] = $GLOBALS['TL_LANG']['syncCto']['checksum_error'];
            }
            else
            {
                $arrResponse[$key] = "Saving " . $arrMetafiles[$key]["file"];
            }
        }

        return $arrResponse;
    }

    public function rpc_run_sql($filename)
    {
        if (!file_exists(TL_ROOT . $GLOBALS['syncCto']['path']['tmp'] . "sql/" . $filename))
        {
            $this->arrError[] = "Unknow or missing file. Path: " . TL_ROOT . $GLOBALS['syncCto']['path']['tmp'] . "sql/";
            return;
        }

        $arrZip = $this->objSyncCtoDatabase->runCreateZip();
        sleep(1);
        $this->objSyncCtoDatabase->runDumpSQL($this->Database->listTables(), $arrZip["name"]);
        sleep(1);
        $this->objSyncCtoDatabase->runDumpInsert($this->Database->listTables(), $arrZip["name"]);

        $this->objSyncCtoDatabase->runCheckZip("sql/" . $filename, false, true);
        $this->objSyncCtoDatabase->runRestore($GLOBALS['syncCto']['path']['tmp'] . "sql/" . $filename);

        return true;
    }

    public function rpc_run_file($arrFilelist)
    {

        foreach ($arrFilelist as $key => $value)
        {
            if (!file_exists(TL_ROOT . $GLOBALS['syncCto']['path']['tmp'] . "sync/" . $value["path"]))
            {
                $arrFilelist[$key]["saved"] = false;
                continue;
            }

            $arrFolderPart = explode("/", $value["path"]);
            array_pop($arrFolderPart);
            $strVar = "";

            foreach ($arrFolderPart as $itFolder)
            {
                $strVar .= "/" . $itFolder;

                if (!file_exists(TL_ROOT . $strVar))
                    mkdir(TL_ROOT . $strVar);
            }

            if (copy(TL_ROOT . $GLOBALS['syncCto']['path']['tmp'] . "sync/" . $value["path"], TL_ROOT . "/" . $value["path"]) == false)
            {
                $arrFilelist[$key]["transmission"] = SyncCtoEnum::FILETRANS_SKIPPED;
                $arrFilelist[$key]["skipreason"] = "File copy error";
            }
        }

        return $arrFilelist;
    }

    public function rpc_run_localconfig($arrConfig)
    {
        $arrLocalConfig = $this->objSyncCtoHelper->loadConfig(SyncCtoEnum::LOADCONFIG_KEYS_ONLY);

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

    public function rpc_run_splitfile($strSplitname, $intSplitcount, $strMovepath, $strMD5)
    {
        @set_time_limit(3600);

        $strSavePath = $GLOBALS['syncCto']['path']['tmp'] . "sync/" . $strMovepath;

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

        for ($i = 0; $i < $intSplitcount; $i++)
        {
            $strReadFile = TL_ROOT . $GLOBALS['syncCto']['path']['tmp'] . $strSplitname . "/" . $strSplitname . ".sync" . $i;

            if (!file_exists($strReadFile))
            {
                $this->arrError[] = "Missing part file " . $strSplitname . ".sync" . $i;
                return;
            }

            $fpPartFile = fopen($strReadFile, 'r');
            $fpWholeFile = fopen($strSavePath, 'a+');

            while (feof($fpPartFile) !== TRUE)
            {
                fwrite($fpWholeFile, fread($fpPartFile, 1024));
            }

            fclose($fpPartFile);
            fclose($fpWholeFile);

            sleep(1);
        }

        if (md5_file($strSavePath) != $strMD5)
        {
            $this->arrError[] = "MD5 Check error";
            return;
        }

        return true;
    }

    public function rpc_file_get($strPath)
    {
        $strFilePath = TL_ROOT . $strPath;

        if (!file_exists($strFilePath))
        {
            $this->arrError[] = "Can not find " . $strFilePath;
            return;
        }

        // Read file
        $fh = fopen($strFilePath, 'rb');

        if ($fh === FALSE)
        {
            $this->arrError[] = "Error by reading file " . $strFilePath;
            return;
        }

        while (!feof($fh))
        {
            $content .= fread($fh, 512);
        }

        fclose($fh);
        unset($fh);

        $this->mixOutput["RPC_FILE_GET"] = $content;
        $this->mixOutput["md5"] = md5_file($strFilePath);

        unset($content);

        return true;
    }

    public function rpc_sql_zip()
    {
        $arrZip = $this->objSyncCtoDatabase->runCreateZip(true);
        return $arrZip;
    }

    public function rpc_sql_script($strZipname, $arrTables)
    {
        $this->objSyncCtoDatabase->runDumpSQL($arrTables, $strZipname, TRUE);
        return true;
    }

    public function rpc_sql_syncscript($strZipname, $arrTables)
    {
        $this->objSyncCtoDatabase->runDumpInsert($arrTables, $strZipname, TRUE);
        return true;
    }

    public function rpc_sql_check($strZipname)
    {
        $this->objSyncCtoDatabase->runCheckZip($strZipname, FALSE, TRUE);
        return true;
    }

    public function rpc_file_split($strSrcFile, $strDesFolder, $strDesFile, $intSizeLimit)
    {
        $intCount = $this->objSyncCtoFiles->splitFiles($strSrcFile, $strDesFolder, $strDesFile, $intSizeLimit);
        return $intCount;
    }

    public function rpc_config_load()
    {
        $arrConfigBlacklist = $this->objSyncCtoHelper->getBlacklistLocalconfig();
        $arrConfig = $this->objSyncCtoHelper->loadConfig(SyncCtoEnum::LOADCONFIG_KEY_VALUE);

        foreach ($arrConfig as $key => $value)
        {
            if (in_array($key, $arrConfigBlacklist))
                unset($arrConfig[$key]);
        }

        return $arrConfig;
    }

    /* -------------------------------------------------------------------------
     * Security Function
     */

    public function refererDisable()
    {
        $arrResponse = $this->runServer("RPC_REFERER_DISABLE", null, TRUE);
        return;
    }

    public function refererEnable()
    {
        $arrResponse = $this->runServer("RPC_REFERER_ENABLE", null, TRUE);
        return;
    }

    public function rpc_file_delete($arrFileList)
    {
        if (count($arrFileList) != 0)
        {
            foreach ($arrFileList as $key => $value)
            {
                if (@unlink($this->objSyncCtoFiles->buildPath($value['path'])))
                {
                    $arrFileList[$key]['transmission'] = SyncCtoEnum::FILETRANS_SEND;
                }
                else
                {
                    $arrFileList[$key]['transmission'] = SyncCtoEnum::FILETRANS_SKIPPED;
                    $arrFileList[$key]["skipreason"] = "Error by deleting file";
                }
            }
        }

        return $arrFileList;
    }

}

?>