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
class SyncCtoFiles extends Backend
{
    /* -------------------------------------------------------------------------
     * Vars
     */

    //- Singelten pattern --------
    protected static $instance = null;
    //- Vars ---------------------   
    protected $strSuffixZipName = "File-Backup.zip";
    protected $strTimestampFormat = "Ymd_H-i-s";
    //- Objects ------------------
    protected $objSyncCtoHelper;

    /* -------------------------------------------------------------------------
     * Core
     */

    public function __construct()
    {
        parent::__construct();
        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();
    }

    /**
     * @return SyncCtoFiles 
     */
    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new SyncCtoFiles();

        return self::$instance;
    }

    /* -------------------------------------------------------------------------
     * Getter / Setter - Functions
     */

    public function getSuffixZipName()
    {
        return $this->strSuffixZipName;
    }

    public function setSuffixZipName($strSuffixZipName)
    {
        $this->strSuffixZipName = $strSuffixZipName;
    }

    public function getTimestampFormat()
    {
        return $this->strTimestampFormat;
    }

    public function setTimestampFormat($strTimestampFormat)
    {
        $this->strTimestampFormat = $strTimestampFormat;
    }

    /* -------------------------------------------------------------------------
     * Checksum Functions
     */

    /**
     * Create a checksum list from contao core.
     * 
     * @return array 
     */
    public function runChecksumCore()
    {
        $arrFileList = $this->recrusiveFileList(array(), "");
        $arrChecksum = array();

        foreach ($arrFileList as $key => $value)
        {
            $intSize = filesize($this->objSyncCtoHelper->buildPath($value));

            if ($intSize >= $GLOBALS['syncCto']['size_limit_ignore'])
            {
                $arrChecksum[md5($value)] = array(
                    "path" => $value,
                    "checksum" => 0,
                    "size" => $intSize,
                    "state" => SyncCtoEnum::FILESTATE_BOMBASTIC_BIG,
                    "raw" => "file bombastic",
                    "transmission" => SyncCtoEnum::FILETRANS_WAITING,
                    "path_raw" => standardize($value),
                );
            }
            else if ($intSize >= $GLOBALS['syncCto']['size_limit'])
            {
                $arrChecksum[md5($value)] = array(
                    "path" => $value,
                    "checksum" => md5_file($this->objSyncCtoHelper->buildPath($value)),
                    "size" => $intSize,
                    "state" => SyncCtoEnum::FILESTATE_TOO_BIG,
                    "raw" => "file big",
                    "transmission" => SyncCtoEnum::FILETRANS_WAITING,
                    "path_raw" => standardize($value),
                );
            }
            else
            {
                $arrChecksum[md5($value)] = array(
                    "path" => $value,
                    "checksum" => md5_file($this->objSyncCtoHelper->buildPath($value)),
                    "size" => $intSize,
                    "state" => SyncCtoEnum::FILESTATE_FILE,
                    "raw" => "file",
                    "transmission" => SyncCtoEnum::FILETRANS_WAITING,
                    "path_raw" => standardize($value),
                );
            }
        }

        return $arrChecksum;
    }

    /**
     * Create a checksum list from contao tl_files.
     * 
     * @return array 
     */
    public function runChecksumTlFiles()
    {
        $arrFileList = $this->recrusiveFileList(array(), "tl_files", true);
        $arrChecksum = array();

        foreach ($arrFileList as $key => $value)
        {
            $intSize = filesize($this->objSyncCtoHelper->buildPath($value));

            if ($intSize >= $GLOBALS['syncCto']['size_limit_ignore'])
            {
                $arrChecksum[md5($value)] = array(
                    "path" => $value,
                    "checksum" => 0,
                    "size" => $intSize,
                    "state" => SyncCtoEnum::FILESTATE_BOMBASTIC_BIG,
                    "raw" => "file bombastic",
                    "transmission" => SyncCtoEnum::FILETRANS_WAITING,
                    "path_raw" => standardize($value),
                );
            }
            else if ($intSize >= $GLOBALS['syncCto']['size_limit'])
            {
                $arrChecksum[md5($value)] = array(
                    "path" => $value,
                    "checksum" => md5_file($this->objSyncCtoHelper->buildPath($value)),
                    "size" => $intSize,
                    "state" => SyncCtoEnum::FILESTATE_TOO_BIG,
                    "raw" => "file big",
                    "transmission" => SyncCtoEnum::FILETRANS_WAITING,
                    "path_raw" => standardize($value),
                );
            }
            else
            {
                $arrChecksum[md5($value)] = array(
                    "path" => $value,
                    "checksum" => md5_file($this->objSyncCtoHelper->buildPath($value)),
                    "size" => $intSize,
                    "state" => SyncCtoEnum::FILESTATE_FILE,
                    "raw" => "file",
                    "transmission" => SyncCtoEnum::FILETRANS_WAITING,
                    "path_raw" => standardize($value),
                );
            }
        }

        return $arrChecksum;
    }

    public function runCecksumCompare($arrChecksumList)
    {
        $arrFileList = array();

        foreach ($arrChecksumList as $key => $value)
        {
            if ($value['state'] == SyncCtoEnum::FILESTATE_BOMBASTIC_BIG)
            {
                $arrFileList[$key] = $arrChecksumList[$key];
                $arrFileList[$key]["raw"] = "file bombastic";
            }
            else if (file_exists(TL_ROOT . "/" . $value['path']))
            {
                if (md5_file(TL_ROOT . "/" . $value['path']) == $value['checksum'])
                {
                    // Only for debug, if needed kill the "continue"
                    continue;

                    if ($value['state'] == SyncCtoEnum::FILESTATE_TOO_BIG)
                    {
                        $arrFileList[$key] = $arrChecksumList[$key];
                        $arrFileList[$key]["raw"] = "same big";
                        $arrFileList[$key]["state"] = SyncCtoEnum::FILESTATE_TOO_BIG_SAME;
                    }
                    else
                    {
                        $arrFileList[$key] = $arrChecksumList[$key];
                        $arrFileList[$key]["raw"] = "same";
                        $arrFileList[$key]["state"] = SyncCtoEnum::FILESTATE_SAME;
                    }
                }
                else
                {
                    if ($value['state'] == SyncCtoEnum::FILESTATE_TOO_BIG)
                    {
                        $arrFileList[$key] = $arrChecksumList[$key];
                        $arrFileList[$key]["raw"] = "need big";
                        $arrFileList[$key]["state"] = SyncCtoEnum::FILESTATE_TOO_BIG_NEED;
                    }
                    else
                    {
                        $arrFileList[$key] = $arrChecksumList[$key];
                        $arrFileList[$key]["raw"] = "need";
                        $arrFileList[$key]["state"] = SyncCtoEnum::FILESTATE_NEED;
                    }
                }
            }
            else
            {
                if ($value['state'] == SyncCtoEnum::FILESTATE_TOO_BIG)
                {
                    $arrFileList[$key] = $arrChecksumList[$key];
                    $arrFileList[$key]["raw"] = "missing big";
                    $arrFileList[$key]["state"] = SyncCtoEnum::FILESTATE_TOO_BIG_MISSING;
                }
                else
                {
                    $arrFileList[$key] = $arrChecksumList[$key];
                    $arrFileList[$key]["raw"] = "missing";
                    $arrFileList[$key]["state"] = SyncCtoEnum::FILESTATE_MISSING;
                }
            }
        }

        return $arrFileList;
    }

    // Dump Functions ----------------------------------------------------------

    public function runDump($strZip = "", $arrTlFiles = null)
    {
        if ($strZip == "")
        {
            $strFilename = date($this->strTimestampFormat) . "_" . $this->strSuffixZipName;
        }
        else
        {
            if (preg_match("/.*\.zip\z/i", $subject) == 0)
                $strFilename = $strZip . ".zip";
            else
                $strFilename = $strZip;
        }

        $strPath = $this->objSyncCtoHelper->buildPathWoTL($GLOBALS['syncCto']['path']['file'], $strFilename);

        $objZipWrite = new ZipWriter($strPath);

        $arrFileList = $this->recrusiveFileList(array(), "", false);
        
        if(is_array($arrTlFiles) == true && count($arrTlFiles) != 0)
        {
            foreach ($arrTlFiles as $key => $value)
            {
                if(!file_exists($this->objSyncCtoHelper->buildPath($value)))
                        unset($arrTlFiles[$key]);
            }
            
            $arrFileList = array_merge($arrFileList, $arrTlFiles);
        }        

        foreach ($arrFileList as $key => $value)
        {
            $value = preg_replace("/^\//i", "", $value);
            $objZipWrite->addFile($value);
        }

        $objZipWrite->close();

        unset($objZipWrite);
        unset($arrFileList);
    }

    public function runDumpTlFiles($strZip = "", $arrFileList = null)
    {
        if ($strZip == "")
        {
            $strFilename = date($this->strTimestampFormat) . "_" . $this->strSuffixZipName;
        }
        else
        {
            if (preg_match("/.*\.zip\z/i", $subject) == 0)
                $strFilename = $strZip . ".zip";
            else
                $strFilename = $strZip;
        }

        $strPath = $this->objSyncCtoHelper->buildPathWoTL($GLOBALS['syncCto']['path']['file'], $strFilename);

        $objZipWrite = new ZipWriter($strPath);

        if ($arrFileList == null)
        {
            $arrFileList = $this->recrusiveFileList(array(), "tl_files", true);
        }

        foreach ($arrFileList as $key => $value)
        {
            $value = preg_replace("/^\//i", "", $value);
            $objZipWrite->addFile($value);
        }

        $objZipWrite->close();

        unset($objZipWrite);
        unset($arrFileList);
    }

    public function runDumpCore($strZip = "")
    {
        if ($strZip == "")
        {
            $strFilename = date($this->strTimestampFormat) . "_" . $this->strSuffixZipName;
        }
        else
        {
            if (preg_match("/.*\.zip\z/i", $subject) == 0)
                $strFilename = $strZip . ".zip";
            else
                $strFilename = $strZip;
        }

        $strPath = $this->objSyncCtoHelper->buildPathWoTL($GLOBALS['syncCto']['path']['file'], $strFilename);

        $objZipWrite = new ZipWriter($strPath);

        $arrFileList = $this->recrusiveFileList(array(), "", false);

        foreach ($arrFileList as $key => $value)
        {
            $value = preg_replace("/^\//i", "", $value);
            $objZipWrite->addFile($value);
        }

        $objZipWrite->close();

        unset($objZipWrite);
        unset($arrFileList);
    }

    /**
     * ToDo
     * @param type $strRestoreFile
     * @return type 
     */
    public function runRestore($strRestoreFile)
    {
        $objZipRead = new ZipReader($strRestoreFile);

        $arrFileList = $objZipRead->getFileList();

        foreach ($arrFileList as $key => $value)
        {
            if ($objZipRead->getFile($value) != true)
                throw new Exception("Error by unziping file. File not found in zip archive.");

            $objFile = new File($GLOBALS['syncCto']['path']['tmp'] . $value);
            $objFile->write($objZipRead->unzip());
            $objFile->close();
            
            unset($objFile);
        }

        unset($objZipWrite);
        
        // Move Files
        
        unset($arrFileList);

        return;
    }

    /* -------------------------------------------------------------------------
     * Helper functions
     */

    public function recrusiveFileList($arrList, $strPath, $blnTlFiles = false)
    {
        // Load blacklists and whitelists
        $arrFolderBlacklist = $this->objSyncCtoHelper->getBlacklistFolder();
        $arrFileBlacklist = $this->objSyncCtoHelper->getBlacklistFile();
        $arrFolderWhiteList = $this->objSyncCtoHelper->getWhitelistFolder();

        if ($blnTlFiles)
        {
            $arrFolderWhiteList[] = "tl_files";
        }

        // Build path with and without TL_ROOT
        $strPath = $this->objSyncCtoHelper->buildPathWoTL($strPath);
        $strPathTl = $this->objSyncCtoHelper->buildPath($strPath);

        // Check if the current path is on blacklist
        if ($strPath != "")
        {
            // Run through each entry in blacklistfolder
            foreach ($arrFolderBlacklist as $valueBalck)
            {
                // Search with preg for values
                $valueBalck = str_replace(array("\\", ".", "^", "?", "*"), array("\\\\", "\\.", "\\^", ".?", ".*"), $valueBalck);
                if (preg_match("^" . $valueBalck . "^i", $strPath) != 0)
                {
                    return $arrList;
                }
            }

            // Run through each entry in whitelistfolder
            $blnWhite = false;
            foreach ($arrFolderWhiteList as $valueWhite)
            {
                // Search with preg for values
                $valueWhite = str_replace(array("\\", ".", "^", "?", "*", "/"), array("\\\\", "\\.", "\\^", ".?", ".*", "\/"), $this->objSyncCtoHelper->buildPathWoTL($valueWhite));
                if (preg_match("/^" . $valueWhite . ".*/i", $strPath) != 0)
                {
                    $blnWhite = true;
                }
            }

            if (!$blnWhite)
                return $arrList;
        }

        // Is the given string a file
        if (is_file($strPathTl))
        {
            // Run through each entry in blacklistfile     
            foreach ($arrFileBlacklist as $valule)
            {
                // Search with preg for values
                $valule = str_replace(array("\\", ".", "^", "?", "*"), array("\\\\", "\\.", "\\^", ".?", ".*"), $valule);
                if (preg_match("^" . $valule . "^i", $strPath) != 0)
                {
                    return $arrList;
                }
            }

            $arrList[] = $strPathTl;
        }
        // Is the given string a folder
        else
        {
            // Scann Folder
            $arrFolder = scandir($strPathTl);

            // Rund through each file
            foreach ($arrFolder as $key => $value)
            {
                // Handle open_basedir
                if ($value == '.' || $value == '..')
                    continue;

                // Have we a file or ...
                if (is_file($this->objSyncCtoHelper->buildPath($strPath, $value)))
                {
                    // Check if file is in blacklist    
                    $blnBlack = false;
                    // Run through each entry in blacklistfile           
                    foreach ($arrFileBlacklist as $valule)
                    {
                        // Search with preg for values
                        $valule = str_replace(array("\\", ".", "^", "?", "*"), array("\\\\", "\\.", "\\^", ".?", ".*"), $valule);
                        if (preg_match("^" . $valule . "^i", $this->objSyncCtoHelper->buildPathWoTL($strPath, $value)) != 0)
                        {
                            $blnBlack = true;
                            break;
                        }
                    }

                    // Skip if file is in blacklist
                    if ($blnBlack)
                        continue;

                    // Add to list
                    $arrList[] = $this->objSyncCtoHelper->buildPathWoTL($strPath, $value);
                }
                // ... a folder
                else
                {
                    // Check if folder is in whitelist    
                    $blnWhitelist = false;
                    foreach ($arrFolderWhiteList as $valueWhite)
                    {
                        // Search with preg for values
                        $valueWhite = str_replace(array("\\", ".", "^", "?", "*", "/"), array("\\\\", "\\.", "\\^", ".?", ".*", "\/"), $this->objSyncCtoHelper->buildPathWoTL($valueWhite));
                        if (preg_match("/^" . $valueWhite . ".*/i", $this->objSyncCtoHelper->buildPathWoTL($strPath, $value)) != 0)
                        {
                            $blnWhitelist = true;
                            break;
                        }
                    }

                    if (!$blnWhitelist)
                        continue;

                    // Recursive-Call
                    $arrList = $this->recrusiveFileList($arrList, $this->objSyncCtoHelper->buildPathWoTL($strPath, $value), $blnTlFiles);
                }
            }
        }

        // Return list
        return $arrList;
    }

    /* -------------------------------------------------------------------------
     * Folder Operations 
     */

    /**
     * Create syncCto folders if not exists
     */
    public function checkSyncCtoFolders()
    {
        $objFile = new Folder($this->objSyncCtoHelper->buildPathWoTL($GLOBALS['syncCto']['path']['db']));
        $objFile = new Folder($this->objSyncCtoHelper->buildPathWoTL($GLOBALS['syncCto']['path']['tmp']));
        $objFile = new Folder($this->objSyncCtoHelper->buildPathWoTL($GLOBALS['syncCto']['path']['file']));
    }

    /**
     * Clear tempfolder or a folder inside of temp
     * 
     * @param string $strFolder
     */
    public function purgeTemp($strFolder = null)
    {
        if ($strFolder == null || $strFolder == "")
            $strPath = $this->buildPathWoTL($GLOBALS['syncCto']['path']['tmp']);
        else
            $strPath = $this->buildPathWoTL($GLOBALS['syncCto']['path']['tmp'], $strFolder);

        $objFolder = new Folder($strFolder);
        $objFolder->clear();
    }

    /*
     * ------------------------------------------------------------------------
     * ------------------------------------------------------------------------
     * ALT
     * ------------------------------------------------------------------------
     * ------------------------------------------------------------------------
     */



    /* -------------------------------------------------------------------------
     * File Operations 
     */

    /**
     * Verschiebt eine Datei aus den Temp Ordner zu ihren eigentlichen Platz.
     * Dient dafür die übertragenen Dateien der Sync. zu verschieben.
     * 
     * @param array $arrFileList Liste von Dateien im aufbau array ( [array("path" => [Interne Adresse start bei TL_ROOT])] )
     * @return array  
     */
    public function moveFile($arrFileList)
    {
        return $this->moveTempFile($arrFileList);
    }

    public function moveTempFile($arrFileList)
    {
        foreach ($arrFilelist as $key => $value)
        {
            if (!file_exists($this->buildPath($GLOBALS['syncCto']['path']['tmp'], "sync", $value["path"])))
            {
                $arrFilelist[$key]["saved"] = false;
                $arrFilelist[$key]["error"] = "missing file";
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
                $arrFilelist[$key]["error"] = "file copy error Src:" . $this->buildPath($GLOBALS['syncCto']['path']['tmp'], "sync", $value["path"]) . " | Des: " . $this->buildPath($value["path"]);
        }

        return $arrFileList;
    }

    /**
     *
     * @param type $strSrcFile File start at TL_ROOT exp. system/foo/foo.php
     * @param type $strDesFolder Folder for split files, start at TL_ROOT , exp. system/temp/
     * @param type $strDesFile Name of file without extension. Example: Foo or MyFile
     * @param type $intSizeLimit Split Size in Bytes
     */
    public function splitFiles($strSrcFile, $strDesFolder, $strDesFile, $intSizeLimit)
    {
        @set_time_limit(3600);

        $strSrcFile = TL_ROOT . "/" . $strSrcFile;
        $strDesPath = TL_ROOT . $strDesFolder . "/";

        if (!file_exists($strSrcFile))
            throw new Exception("File not exsist");

        @mkdir($strDesPath);

        $intFilesize = filesize($strSrcFile);
        $booRun = true;
        $i = 0;
        for ($i; $booRun; $i++)
        {
            $fp = fopen($strSrcFile, "rb");

            if ($fp === false)
                throw new Exception("Could nt open file");

            if (fseek($fp, $i * $intSizeLimit, SEEK_SET) === -1)
                throw new Exception("Fseek error");

            if (feof($fp) === TRUE)
            {
                $i--;
                break;
            }

            $data = fread($fp, $intSizeLimit);

            $handle = fopen($strDesPath . $strDesFile . ".sync" . $i, "w");
            fwrite($handle, $data);
            fclose($handle);

            fclose($fp);

            unset($handle);
            unset($data);
            unset($fp);

            if (( ( $i + 1 ) * $intSizeLimit) > $intFilesize)
                $booRun = false;

            sleep(1);
        }

        return $i;
    }

}

?>