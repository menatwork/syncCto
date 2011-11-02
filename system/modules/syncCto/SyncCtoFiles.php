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
 * Class for file operations
 */
class SyncCtoFiles extends Backend
{
    /* -------------------------------------------------------------------------
     * Vars
     */

    // Singelten pattern
    protected static $instance = null;
    // Vars
    protected $strSuffixZipName = "File-Backup.zip";
    protected $strTimestampFormat;
    // Objects 
    protected $objSyncCtoHelper;

    /* -------------------------------------------------------------------------
     * Core
     */

    public function __construct()
    {
        parent::__construct();

        // My Class
        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();
        
        $this->strTimestampFormat = standardize($GLOBALS['TL_CONFIG']['datimFormat']);        
        
        set_time_limit(0);
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
        $arrFileList = $this->recursiveFileList(array(), "");
        $arrChecksum = array();

        foreach ($arrFileList as $key => $value)
        {
            $intSize = filesize($this->objSyncCtoHelper->buildPath($value));

            if ($intSize < 0 && $intSize != 0)
            {
                $arrChecksum[md5($value)] = array(
                    "path" => $value,
                    "checksum" => 0,
                    "size" => 0,
                    "state" => SyncCtoEnum::FILESTATE_BOMBASTIC_BIG,
                    "transmission" => SyncCtoEnum::FILETRANS_WAITING,
                );
            }
            else if ($intSize >= $GLOBALS['SYC_SIZE']['limit_ignore'])
            {
                $arrChecksum[md5($value)] = array(
                    "path" => $value,
                    "checksum" => 0,
                    "size" => $intSize,
                    "state" => SyncCtoEnum::FILESTATE_BOMBASTIC_BIG,
                    "transmission" => SyncCtoEnum::FILETRANS_WAITING,
                );
            }
            else if ($intSize >= $GLOBALS['SYC_SIZE']['limit'])
            {
                $arrChecksum[md5($value)] = array(
                    "path" => $value,
                    "checksum" => md5_file($this->objSyncCtoHelper->buildPath($value)),
                    "size" => $intSize,
                    "state" => SyncCtoEnum::FILESTATE_TOO_BIG,
                    "transmission" => SyncCtoEnum::FILETRANS_WAITING,
                );
            }
            else
            {
                $arrChecksum[md5($value)] = array(
                    "path" => $value,
                    "checksum" => md5_file($this->objSyncCtoHelper->buildPath($value)),
                    "size" => $intSize,
                    "state" => SyncCtoEnum::FILESTATE_FILE,
                    "transmission" => SyncCtoEnum::FILETRANS_WAITING,
                );
            }
        }

        return $arrChecksum;
    }

    /**
     * Create a checksum list from contao files.
     * 
     * @return array 
     */
    public function runChecksumFiles($arrFileList = null)
    {
        // Check if filelit is set or not.
        if ($arrFileList != null && is_array($arrFileList))
        {
            // Create checksumlist with all subfolders and files.
            $arrTempFilelist = array();
            foreach ($arrFileList as $key => $value)
            {
                // If we have a folder go in an create a checksumlist
                if (is_dir($this->objSyncCtoHelper->buildPath($value)))
                {
                    $arrTempFilelist = array_merge($arrTempFilelist, $this->recursiveFileList(array(), $value, true));
                }
                // Else just add the file
                else
                {
                    $arrTempFilelist[] = $value;
                }
            }

            // Replace current list with new one.
            $arrFileList = $arrTempFilelist;
        }
        else
        {
            $arrFileList = $this->recursiveFileList(array(), $GLOBALS['TL_CONFIG']['uploadPath'], true);
            $arrChecksum = array();
        }

        foreach ($arrFileList as $key => $value)
        {
            $intSize = filesize($this->objSyncCtoHelper->buildPath($value));

            if ($intSize < 0 && $intSize != 0)
            {
                $arrChecksum[md5($value)] = array(
                    "path" => $value,
                    "checksum" => 0,
                    "size" => 0,
                    "state" => SyncCtoEnum::FILESTATE_BOMBASTIC_BIG,
                    "transmission" => SyncCtoEnum::FILETRANS_WAITING,
                );
            }
            else if ($intSize >= $GLOBALS['SYC_SIZE']['limit_ignore'])
            {
                $arrChecksum[md5($value)] = array(
                    "path" => $value,
                    "checksum" => 0,
                    "size" => $intSize,
                    "state" => SyncCtoEnum::FILESTATE_BOMBASTIC_BIG,
                    "raw" => "file bombastic",
                    "transmission" => SyncCtoEnum::FILETRANS_WAITING,
                );
            }
            else if ($intSize >= $GLOBALS['SYC_SIZE']['limit'])
            {
                $arrChecksum[md5($value)] = array(
                    "path" => $value,
                    "checksum" => md5_file($this->objSyncCtoHelper->buildPath($value)),
                    "size" => $intSize,
                    "state" => SyncCtoEnum::FILESTATE_TOO_BIG,
                    "raw" => "file big",
                    "transmission" => SyncCtoEnum::FILETRANS_WAITING,
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
                    // Do nocthing
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
            $strFilename = standardize(str_replace(array(" "), array("_"), preg_replace("/\.zip\z/i", "", $strZip))) . ".zip";
        }

        $strPath = $this->objSyncCtoHelper->buildPathWoTL($GLOBALS['SYC_PATH']['file'], $strFilename);

        $objZipWrite = new ZipWriter($strPath);

        $arrFileList = $this->recursiveFileList(array(), "", false);

        if (is_array($arrTlFiles) == true && count($arrTlFiles) != 0)
        {
            $arrTempList = array();

            foreach ($arrTlFiles as $key => $value)
            {
                if (is_dir($this->objSyncCtoHelper->buildPath($value)))
                {
                    $arrList = $this->recursiveFileList(array(), $value, true);
                    $arrTempList = array_merge($arrTempList, $arrList);
                }
                else
                {
                    $arrTempList[] = $value;
                }
            }

            $arrFileList = array_merge($arrFileList, $arrTempList);
        }

        foreach ($arrFileList as $key => $value)
        {
            $value = preg_replace("/^\//i", "", $value);
            $objZipWrite->addFile($value);
        }

        $objZipWrite->close();

        unset($objZipWrite);
        unset($arrFileList);

        return $strFilename;
    }

    public function runDumpTlFiles($strZip = "", $arrFileList = null)
    {
        if ($strZip == "")
        {
            $strFilename = date($this->strTimestampFormat) . "_" . $this->strSuffixZipName;
        }
        else
        {
            $strFilename = standardize(str_replace(array(" "), array("_"), preg_replace("/\.zip\z/i", "", $strZip))) . ".zip";
        }

        $strPath = $this->objSyncCtoHelper->buildPathWoTL($GLOBALS['SYC_PATH']['file'], $strFilename);

        $objZipWrite = new ZipWriter($strPath);

        if ($arrFileList == null)
        {
            $arrFileList = $this->recursiveFileList(array(), $GLOBALS['TL_CONFIG']['uploadPath'], true);
        }

        foreach ($arrFileList as $key => $value)
        {
            if (is_dir($this->objSyncCtoHelper->buildPath($value)))
            {
                $arrList = $this->recursiveFileList(array(), $value, true);

                foreach ($arrList as $keySubFiles => $valueSubFiles)
                {
                    $objZipWrite->addFile($valueSubFiles);
                }
            }
            else
            {
                $objZipWrite->addFile($value);
            }
        }

        $objZipWrite->close();

        unset($objZipWrite);
        unset($arrFileList);

        return $strFilename;
    }

    public function runDumpCore($strZip = "")
    {
        if ($strZip == "")
        {
            $strFilename = date($this->strTimestampFormat) . "_" . $this->strSuffixZipName;
        }
        else
        {
            $strFilename = standardize(str_replace(array(" "), array("_"), preg_replace("/\.zip\z/i", "", $strZip))) . ".zip";
        }

        $strPath = $this->objSyncCtoHelper->buildPathWoTL($GLOBALS['SYC_PATH']['file'], $strFilename);

        $objZipWrite = new ZipWriter($strPath);

        $arrFileList = $this->recursiveFileList(array(), "", false);

        foreach ($arrFileList as $key => $value)
        {
            $value = preg_replace("/^\//i", "", $value);
            $objZipWrite->addFile($value);
        }

        $objZipWrite->close();

        unset($objZipWrite);
        unset($arrFileList);

        return $strFilename;
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

            $objFile = new File($value);
            $objFile->write($objZipRead->unzip());
            $objFile->close();

            unset($objFile);
        }

        unset($objZipWrite);
        unset($arrFileList);

        return;
    }

    /* -------------------------------------------------------------------------
     * Helper functions
     */

    public function recursiveFileList($arrList, $strPath, $blnTlFiles = false)
    {
        // Load blacklists and whitelists
        $arrFolderBlacklist = $this->objSyncCtoHelper->getBlacklistFolder();
        $arrFileBlacklist = $this->objSyncCtoHelper->getBlacklistFile();
        $arrFolderWhiteList = $this->objSyncCtoHelper->getWhitelistFolder();

        if ($blnTlFiles)
        {
            $arrFolderWhiteList[] = $GLOBALS['TL_CONFIG']['uploadPath'];
        }

        // Build path with and without TL_ROOT
        $strPath = $this->objSyncCtoHelper->buildPathWoTL($strPath);
        $strPathTl = $this->objSyncCtoHelper->buildPath($strPath);

        // Check if the current path is on blacklist
        if ($strPath != "")
        {
            // Run through each entry in blacklistfolder
            foreach ($arrFolderBlacklist as $valueBlack)
            {
                // Search with preg for values
                $valueBlack = str_replace(array("\\", ".", "^", "?", "*"), array("\\\\", "\\.", "\\^", ".?", ".*"), $valueBlack);
                if (preg_match("^" . $valueBlack . "^i", $strPath) != 0)
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
            foreach ($arrFileBlacklist as $valueBlack)
            {
                // Search with preg for values
                $valueBlack = str_replace(array("\\", ".", "^", "?", "*"), array("\\\\", "\\.", "\\^", ".?", ".*"), $valueBlack);
                if (preg_match("^" . $valueBlack . "^i", $strPath) != 0)
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
            $arrScan = scan($strPathTl);

            // Rund through each file
            foreach ($arrScan as $key => $valueItem)
            {
                // Have we a file or ...
                if (is_file($this->objSyncCtoHelper->buildPath($strPath, $valueItem)))
                {
                    // Check if file is in blacklist    
                    $blnBlack = false;
                    // Run through each entry in blacklistfile           
                    foreach ($arrFileBlacklist as $valueBlack)
                    {
                        // Search with preg for values
                        $valueBlack = str_replace(array("\\", ".", "^", "?", "*"), array("\\\\", "\\.", "\\^", ".?", ".*"), $valueBlack);
                        if (preg_match("^" . $valueBlack . "^i", $this->objSyncCtoHelper->buildPathWoTL($strPath, $valueItem)) != 0)
                        {
                            $blnBlack = true;
                            break;
                        }
                    }

                    // Skip if file is in blacklist
                    if ($blnBlack)
                        continue;

                    // Add to list
                    $arrList[] = $this->objSyncCtoHelper->buildPathWoTL($strPath, $valueItem);
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
                        if (preg_match("/^" . $valueWhite . ".*/i", $this->objSyncCtoHelper->buildPathWoTL($strPath, $valueItem)) != 0)
                        {
                            $blnWhitelist = true;
                            break;
                        }
                    }

                    if (!$blnWhitelist)
                        continue;

                    // Recursive-Call
                    $arrList = $this->recursiveFileList($arrList, $this->objSyncCtoHelper->buildPathWoTL($strPath, $valueItem), $blnTlFiles);
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
        $objFile = new Folder($this->objSyncCtoHelper->buildPathWoTL($GLOBALS['SYC_PATH']['db']));
        $objFile = new Folder($this->objSyncCtoHelper->buildPathWoTL($GLOBALS['SYC_PATH']['tmp']));
        $objFile = new Folder($this->objSyncCtoHelper->buildPathWoTL($GLOBALS['SYC_PATH']['file']));
    }

    /**
     * Clear tempfolder or a folder inside of temp
     * 
     * @param string $strFolder
     */
    public function purgeTemp($strFolder = null)
    {
        if ($strFolder == null || $strFolder == "")
            $strPath = $this->objSyncCtoHelper->buildPathWoTL($GLOBALS['SYC_PATH']['tmp']);
        else
            $strPath = $this->objSyncCtoHelper->buildPathWoTL($GLOBALS['SYC_PATH']['tmp'], $strFolder);

        $objFolder = new Folder($strPath);
        $objFolder->clear();
    }

    /* -------------------------------------------------------------------------
     * File Operations 
     */

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

        $strSrcFile = $this->objSyncCtoHelper->buildPath($strSrcFile);
        $strDesPath = $this->objSyncCtoHelper->buildPath($strDesFolder);


        if (!file_exists($strSrcFile))
            throw new Exception("File not exsist");

        $objFolder = new Folder($this->objSyncCtoHelper->buildPathWoTL($strDesPath));
        $objFile = new File($this->objSyncCtoHelper->buildPathWoTL($strSrcFile));

        if ($objFile->filesize < 0)
            throw new Exception("Int overload, try a 64Bit PHP Version.");

        $booRun = true;
        $i = 0;
        for ($i; $booRun; $i++)
        {
            $fp = fopen($strSrcFile, "rb");

            if ($fp === false)
                throw new Exception("Could not open file");

            if (fseek($fp, $i * $intSizeLimit, SEEK_SET) === -1)
                throw new Exception("Fseek error");

            if (feof($fp) === TRUE)
            {
                $i--;
                break;
            }

            $data = fread($fp, $intSizeLimit);

            $handle = fopen($this->objSyncCtoHelper->buildPath($strDesPath, $strDesFile . ".sync" . $i), "w");
            fwrite($handle, $data);
            fclose($handle);

            fclose($fp);

            unset($handle);
            unset($data);
            unset($fp);

            if (( ( $i + 1 ) * $intSizeLimit) > $objFile->filesize)
                $booRun = false;
        }

        return $i;
    }

    public function rebuildSplitFiles($strSplitname, $intSplitcount, $strMovepath, $strMD5)
    {
        @set_time_limit(3600);

        // Build savepath
        $strSavePath = $this->objSyncCtoHelper->buildPath($GLOBALS['SYC_PATH']['tmp'], "sync", $strMovepath);

        // Create Folder
        $objFolder = new Folder($this->objSyncCtoHelper->buildPathWoTL(dirname($strSavePath)));

        // Run for each part file
        for ($i = 0; $i < $intSplitcount; $i++)
        {
            // Build path for part file
            $strReadFile = $this->objSyncCtoHelper->buildPath($GLOBALS['SYC_PATH']['tmp'], $strSplitname, $strSplitname . ".sync" . $i);

            // Check if file exists
            if (!file_exists($strReadFile))
            {
                throw new Exception("Missing part file " . $strSplitname . ".sync" . $i);
            }

            // Create new file objects
            $objFilePart = new File($this->objSyncCtoHelper->buildPathWoTL($strReadFile));
            $objFileWhole = new File($this->objSyncCtoHelper->buildPathWoTL($strSavePath));

            // Write part file to man file
            $objFileWhole->append($objFilePart->getContent());

            // Close objects
            $objFilePart->close();
            $objFileWhole->close();

            // Free up memory
            unset($objFilePart);
            unset($objFileWhole);

            // wait
            sleep(1);
        }

        // Check MD5 Checksum
        if (md5_file($strSavePath) != $strMD5)
        {
            throw new Exception("MD5 Checksum error");
        }

        return true;
    }

    public function moveTempFile($arrFileList)
    {
        foreach ($arrFileList as $key => $value)
        {
            if (!file_exists($this->objSyncCtoHelper->buildPath($GLOBALS['SYC_PATH']['tmp'], "sync", $value["path"])))
            {
                $arrFileList[$key]["saved"] = false;
                $arrFileList[$key]["error"] = "Missing file in tempfolder.";
                continue;
            }

            $strFolderPath = dirname($this->objSyncCtoHelper->buildPathWoTL($value["path"]));

            if ($strFolderPath != ".")
            {
                $objFolder = new Folder($strFolderPath);
                unset($objFolder);
            }

            $strFileSource = $this->objSyncCtoHelper->buildPath($GLOBALS['SYC_PATH']['tmp'], "sync", $value["path"]);
            $strFileDestination = $this->objSyncCtoHelper->buildPath($value["path"]);

            if (copy($strFileSource, $strFileDestination) == false)
            {
                $arrFileList[$key]["saved"] = false;
                $arrFileList[$key]["error"] = "file copy error Src:" . $strFileSource . " | Des: " . $strFileDestination;
            }
            else
            {
                $arrFileList[$key]["saved"] = true;
            }
        }

        return $arrFileList;
    }

    public function deleteFiles($arrFileList)
    {
        if (count($arrFileList) != 0)
        {
            foreach ($arrFileList as $key => $value)
            {
                try
                {
                    $objFiel = new File($this->objSyncCtoHelper->buildPathWoTL($value['path']));

                    if ($objFiel->delete())
                    {
                        $arrFileList[$key]['transmission'] = SyncCtoEnum::FILETRANS_SEND;
                    }
                    else
                    {
                        $arrFileList[$key]['transmission'] = SyncCtoEnum::FILETRANS_SKIPPED;
                        $arrFileList[$key]["skipreason"] = "Error by deleting file";
                    }

                    $objFiel->close();
                }
                catch (Exception $exc)
                {
                    $arrFileList[$key]['transmission'] = SyncCtoEnum::FILETRANS_SKIPPED;
                    $arrFileList[$key]["skipreason"] = $exc->getMessage();
                }
            }
        }

        return $arrFileList;
    }

    /**
     * Recive a file and move it to the right folder.
     * 
     * @param type $arrMetafiles
     * @return string 
     */
    public function saveFile($arrMetafiles)
    {
        if (!is_array($arrMetafiles))
            throw new Exception("Missing metafiles in array check.");

        $arrResponse = array();

        foreach ($_FILES as $key => $value)
        {
            if (!key_exists($key, $arrMetafiles))
                throw new Exception("Could not find metafiles for the file $key");

            $strFolder = $arrMetafiles[$key]["folder"];
            $strFile = $arrMetafiles[$key]["file"];
            $strMD5 = $arrMetafiles[$key]["MD5"];

            switch ($arrMetafiles[$key]["typ"])
            {
                case SyncCtoEnum::UPLOAD_TEMP:
                    $strSaveFile = $this->objSyncCtoHelper->buildPath($GLOBALS['SYC_PATH']['tmp'], $strFolder, $strFile);
                    break;

                case SyncCtoEnum::UPLOAD_SYNC_TEMP:
                    $strSaveFile = $this->objSyncCtoHelper->buildPath($GLOBALS['SYC_PATH']['tmp'], "sync", $strFolder, $strFile);
                    break;

                case SyncCtoEnum::UPLOAD_SQL_TEMP:
                    $strSaveFile = $this->objSyncCtoHelper->buildPath($GLOBALS['SYC_PATH']['tmp'], "sql", $strFile);
                    break;

                case SyncCtoEnum::UPLOAD_SYNC_SPLIT:
                    $strSaveFile = $this->objSyncCtoHelper->buildPath($GLOBALS['SYC_PATH']['tmp'], $arrMetafiles[$key]["splitname"], $strFile);
                    break;

                default:
                    throw new Exception("Unknown Path for file.");
                    break;
            }

            $objFolder = new Folder($this->objSyncCtoHelper->buildPathWoTL(dirname($strSaveFile)));

            if (move_uploaded_file($value["tmp_name"], $strSaveFile) === FALSE)
            {
                throw new Exception("Error by moving tempfile to destination folder. Src: " . $value["tmp_name"] . " | Des: " . $strSaveFile);
            }
            else if ($key != md5_file($strSaveFile))
            {
                throw new Exception($GLOBALS['TL_LANG']['syncCto']['checksum_error']);
            }
            else
            {
                $arrResponse[$key] = "Saving " . $arrMetafiles[$key]["file"];
            }
        }

        return $arrResponse;
    }

}

?>