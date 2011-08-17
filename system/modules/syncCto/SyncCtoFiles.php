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

    // Variables    
    protected $objSyncCtoHelper;
    protected static $instance = null;

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
     * Create functions
     */

    public function runCreateZip($name = "", $booTempFolder = false)
    {
        @set_time_limit(30);

        $intTstamp = time();

        if ($booTempFolder == TRUE)
            $strFilename = TL_ROOT . $GLOBALS['syncCto']['path']['tmp'] . $this->buildFilename($intTstamp, $name);
        else
            $strFilename = TL_ROOT . $GLOBALS['syncCto']['path']['file'] . $this->buildFilename($intTstamp, $name);

        $objZip = new ZipArchive();

        if ($objZip->open($strFilename, ZIPARCHIVE::CREATE) !== TRUE)
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['syncCto']['cant_open'], array($strFilename)));

        $objZip->addFromString("readme.txt", "foo");

        $objZip->close();

        if (!file_exists($strFilename))
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['syncCto']['file_not_exists'], array($strFilename)));

        return array("id" => $intTstamp, "name" => $this->buildFilename($intTstamp, $name));
    }

    // Checksum Functions ------------------------------------------------------

    /**
     * Create a checksum list from tl_files.
     * 
     * @param array $arrFileList List of files in tl_files
     * @return array 
     */
    public function runTlFilesChecksum($arrFileList)
    {
        @set_time_limit(600);

        $arrChecksum = array();

        foreach ($arrFileList as $value)
        {
            // Kick empty values
            if ($value == "" || $value == null)
                continue;

            // Check if we are still in tl_files
            if (strpos($value, "tl_files") === FALSE)
                continue;

            //Build checksum
            $arrChecksum = $this->recursiveChecksum($value, $arrChecksum);
        }

        return $arrChecksum;
    }

    /**
     * Create a checksum list from the contao core.
     * 
     * @return array 
     */
    public function runCoreFilesChecksum()
    {
        @set_time_limit(600);

        $arrChecksum = array();

        foreach ($this->objSyncCtoHelper->getWhitelistFolder() as $value)
        {
            // Kick empty values
            if ($value == "" || $value == null)
                continue;

            if ($value == "." || $value == "/" || $value == "TL_ROOT")
            {
                // Build checksum from root, only files
                $arrChecksum = $this->recursiveChecksum("", $arrChecksum, TRUE);
            }
            else
            {
                // Build checksum
                $arrChecksum = $this->recursiveChecksum($value, $arrChecksum);
            }
        }

        return $arrChecksum;
    }

    // Dump Functions ----------------------------------------------------------

    public function runTlFilesDump($strZip, $arrBackupList)
    {
        @set_time_limit(3600);

        $strFilename = TL_ROOT . $GLOBALS['syncCto']['path']['file'] . $strZip;

        if (!file_exists($strFilename))
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['syncCto']['file_not_exists'], array($strFilename)));

        $objZip = new ZipArchive();

        if ($objZip->open($strFilename) !== TRUE)
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['syncCto']['cant_open'], array($strFilename)));

        foreach ($arrBackupList as $value)
        {
            $this->recursiveZip($value, TL_ROOT . "/" . $value, $objZip);
        }

        $objZip->close();

        return;
    }

    public function runCoreFilesDump($strZip)
    {
        @set_time_limit(60);

        $strFilename = TL_ROOT . $GLOBALS['syncCto']['path']['file'] . $strZip;

        if (!file_exists($strFilename))
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['syncCto']['file_not_exists'], array($strFilename)));

        $objZip = new ZipArchive();

        if ($objZip->open($strFilename) !== TRUE)
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['syncCto']['cant_open'], array($strFilename)));

        foreach ($GLOBALS['syncCto']['folder_whitelist'] as $value)
        {
            if ($value == "." || $value == "/" || $value == "TL_ROOT")
            {
                $this->recursiveZip(null, TL_ROOT, $objZip, true);
            }
            else
            {
                $this->recursiveZip($value, TL_ROOT . "/" . $value, $objZip);
            }
        }

        $objZip->close();

        return;
    }

    public function runSyncDump($strZip, $arrFiles)
    {
        @set_time_limit(60);

        $strFilename = TL_ROOT . $GLOBALS['syncCto']['path']['tmp'] . $strZip;

        if (!file_exists($strFilename))
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['syncCto']['file_not_exists'], array($strFilename)));

        $objZip = new ZipArchive();

        if ($objZip->open($strFilename) !== TRUE)
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['syncCto']['cant_open'], array($strFilename)));

        foreach ($arrFiles as $value)
        {
            $this->recursiveZip($value["path"], TL_ROOT . "/" . $value["path"], $objZip);
        }

        $objZip->close();

        return;
    }

    public function runRestore($strRestoreFile)
    {
        $strRestoreFile = (TL_ROOT . "/" . $strRestoreFile);

        $zip = new ZipArchive();

        if ($zip->open($strRestoreFile) === TRUE)
        {
            $zip->extractTo(TL_ROOT);
            $zip->close();
        }
        else
        {
            $zip->close();
            throw new Exception("Could not open");
        }

        return;
    }

    public function runCecksum($arrChecksumList)
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

    /* -------------------------------------------------------------------------
     * Helper functions
     */

    private function buildFilename($intTstamp, $name = "")
    {
        if ($name == "")
        {
            return vsprintf("%s_" . FILE_ZIP, array(date($GLOBALS['syncCto']['settings']['time_format'], intval($intTstamp))));
        }
        else
        {
            return vsprintf("%s_%s.zip", array(date($GLOBALS['syncCto']['settings']['time_format'], intval($intTstamp)), standardize($name)));
        }
    }

    private function recursiveZip($strIntern, $strExtern, ZipArchive $zip, $booSkipFolder = false)
    {
        $arrFolderBlacklist = $this->objSyncCtoHelper->getBlacklistFolder();
        $arrFileBlacklist = $this->objSyncCtoHelper->getBlacklistFile();

        if ($strIntern != "")
        {
            foreach ($arrFolderBlacklist as $valule)
            {
                $valule = str_replace(array("\\", ".", "^", "?", "*"), array("\\\\", "\\.", "\\^", ".?", ".*"), $valule);
                if (preg_match("^" . $valule . "^i", $strIntern) != 0)
                {
                    return;
                }
            }
        }

        if (is_dir($strExtern))
        {
            $zip->addEmptyDir($strSource);

            // Open folder
            $hanDir = opendir($strExtern);

            // Switch throw all folders and datas
            while ($file = readdir($hanDir))
            {
                // Skip "." and ".."
                if ($file != '.' && $file != '..')
                {
                    // If dir open it and read it
                    if (is_dir($this->buildPath($strExtern, $file)) && $booSkipFolder == false)
                    {
                        if ($strIntern == null)
                        {
                            $this->recursiveZip($file, $this->buildPath($strExtern, $file), $zip);
                        }
                        else
                        {
                            $this->recursiveZip($strIntern . "/" . $file, $this->buildPath($strExtern, $file), $zip);
                        }
                    }
                    // If file, copy to the zip archive
                    else if (is_file($this->buildPath($strExtern, $file)))
                    {
                        // Check if file in blacklist
                        foreach ($arrFileBlacklist as $valule)
                        {
                            $valule = str_replace(array("\\", ".", "^", "?", "*"), array("\\\\", "\\.", "\\^", ".?", ".*"), $valule);
                            if (preg_match("^" . $valule . "^i", $strIntern . "/" . $file) != 0)
                            {
                                return;
                            }
                        }

                        if ($strIntern == null)
                        {
                            $zip->addFile($this->buildPath($strExtern, $file), $file);
                        }
                        else
                        {
                            $zip->addFile($this->buildPath($strExtern, $file), $strIntern . "/" . $file);
                        }
                    }
                }
            }
        }
        else
        {
            // Check if file in blacklist            
            foreach ($arrFileBlacklist as $valule)
            {
                $valule = str_replace(array("\\", ".", "^", "?", "*"), array("\\\\", "\\.", "\\^", ".?", ".*"), $valule);
                if (preg_match("^" . $valule . "^i", $strIntern) != 0)
                {
                    return;
                }
            }

            $zip->addFile($this->buildPath($strExtern), $strIntern);
        }

        return;
    }

    function recursiveChecksum($strIntern, $arrChecksum, $booSkipFolder = false)
    {
        $arrFolderBlacklist = $this->objSyncCtoHelper->getBlacklistFolder();
        $arrFileBlacklist = $this->objSyncCtoHelper->getBlacklistFile();

        if ($strIntern != "")
        {
            foreach ($arrFolderBlacklist as $valule)
            {
                $valule = str_replace(array("\\", ".", "^", "?", "*"), array("\\\\", "\\.", "\\^", ".?", ".*"), $valule);
                if (preg_match("^" . $valule . "^i", $strIntern) != 0)
                {
                    return $arrChecksum;
                }
            }
        }

        if (is_dir($this->buildPath($strIntern)))
        {
            // Open folder
            $hanDir = opendir($this->buildPath($strIntern));

            // Switch throw all folders and datas
            while ($file = readdir($hanDir))
            {
                // Skip "." and ".."
                if ($file != '.' && $file != '..')
                {
                    if ($strIntern == "" || $strIntern == null)
                    {
                        // If dir open it and read it
                        if (is_dir($this->buildPath($file)) && $booSkipFolder == false)
                        {
                            $arrChecksum = $this->recursiveChecksum($file, $arrChecksum);
                        }
                        // If file, copy to the zip archive
                        else if (is_file($this->buildPath($file)))
                        {
                            foreach ($arrFileBlacklist as $valule)
                            {
                                $valule = str_replace(array("\\", ".", "^", "?", "*"), array("\\\\", "\\.", "\\^", ".?", ".*"), $valule);
                                if (preg_match("^" . $valule . "^i", $file) != 0)
                                {
                                    return $arrChecksum;
                                }
                            }

                            if (in_array($file, $arrFileBlacklist))
                                continue;

                            $intSize = filesize($this->buildPath($file));

                            if ($intSize >= $GLOBALS['syncCto']['size_limit_ignore'])
                            {
                                $arrChecksum[md5($file)] = array(
                                    "path" => $file,
                                    "checksum" => 0,
                                    "size" => $intSize,
                                    "state" => SyncCtoEnum::FILESTATE_BOMBASTIC_BIG,
                                    "raw" => "file bombastic",
                                    "transmission" => SyncCtoEnum::FILETRANS_WAITING,
                                    "path_raw" => standardize($file),
                                );
                            }
                            else if ($intSize >= $GLOBALS['syncCto']['size_limit'])
                            {
                                $arrChecksum[md5($file)] = array(
                                    "path" => $file,
                                    "checksum" => md5_file($this->buildPath($file)),
                                    "size" => $intSize,
                                    "state" => SyncCtoEnum::FILESTATE_TOO_BIG,
                                    "raw" => "file big",
                                    "transmission" => SyncCtoEnum::FILETRANS_WAITING,
                                    "path_raw" => standardize($file),
                                );
                            }
                            else
                            {
                                $arrChecksum[md5($file)] = array(
                                    "path" => $file,
                                    "checksum" => md5_file($this->buildPath($file)),
                                    "size" => $intSize,
                                    "state" => SyncCtoEnum::FILESTATE_FILE,
                                    "raw" => "file",
                                    "transmission" => SyncCtoEnum::FILETRANS_WAITING,
                                    "path_raw" => standardize($file),
                                );
                            }
                        }
                    }
                    else
                    {
                        // If dir open it and read it
                        if (is_dir($this->buildPath($strIntern, $file)) && $booSkipFolder == false)
                        {
                            $arrChecksum = $this->recursiveChecksum($strIntern . "/" . $file, $arrChecksum);
                        }
                        // If file, copy to the zip archive
                        else if (is_file($this->buildPath($strIntern, $file)))
                        {
                            foreach ($arrFileBlacklist as $valule)
                            {
                                $valule = str_replace(array("\\", ".", "^", "?", "*"), array("\\\\", "\\.", "\\^", ".?", ".*"), $valule);
                                if (preg_match("^" . $valule . "^i", $strIntern . "/" . $file) != 0)
                                {
                                    return $arrChecksum;
                                }
                            }

                            $intSize = filesize($this->buildPath($strIntern, $file));

                            if ($intSize >= $GLOBALS['syncCto']['size_limit_ignore'])
                            {
                                $arrChecksum[md5($strIntern . "/" . $file)] = array(
                                    "path" => $strIntern . "/" . $file,
                                    "checksum" => 0,
                                    "size" => $intSize,
                                    "state" => SyncCtoEnum::FILESTATE_BOMBASTIC_BIG,
                                    "raw" => "file bombastic",
                                    "transmission" => SyncCtoEnum::FILETRANS_WAITING,
                                    "path_raw" => standardize($file),
                                );
                            }
                            else if ($intSize >= $GLOBALS['syncCto']['size_limit'])
                            {
                                $arrChecksum[md5($strIntern . "/" . $file)] = array(
                                    "path" => $strIntern . "/" . $file,
                                    "checksum" => md5_file($this->buildPath($strIntern, $file)),
                                    "size" => $intSize,
                                    "state" => SyncCtoEnum::FILESTATE_TOO_BIG,
                                    "raw" => "file big",
                                    "transmission" => SyncCtoEnum::FILETRANS_WAITING,
                                    "path_raw" => standardize($file),
                                );
                            }
                            else
                            {
                                $arrChecksum[md5($strIntern . "/" . $file)] = array(
                                    "path" => $strIntern . "/" . $file,
                                    "checksum" => md5_file($this->buildPath($strIntern, $file)),
                                    "size" => $intSize,
                                    "state" => SyncCtoEnum::FILESTATE_FILE,
                                    "raw" => "file",
                                    "transmission" => SyncCtoEnum::FILETRANS_WAITING,
                                    "path_raw" => standardize($file),
                                );
                            }
                        }
                    }
                }
            }
        }
        else if (is_file($this->buildPath($strIntern)))
        {
            // Check if file in blacklist            
            if (in_array($strIntern, $arrFileBlacklist))
                return $arrChecksum;

            $intSize = filesize($this->buildPath($strIntern));

            if ($intSize >= $GLOBALS['syncCto']['size_limit_ignore'])
            {
                $arrChecksum[md5($strIntern)] = array(
                    "path" => $strIntern,
                    "checksum" => 0,
                    "size" => $intSize,
                    "state" => SyncCtoEnum::FILESTATE_BOMBASTIC_BIG,
                    "raw" => "file bombastic",
                    "transmission" => SyncCtoEnum::FILETRANS_WAITING,
                    "path_raw" => standardize($file),
                );
            }
            else if ($intSize >= $GLOBALS['syncCto']['size_limit'])
            {
                $arrChecksum[md5($strIntern)] = array(
                    "path" => $strIntern,
                    "checksum" => md5_file($this->buildPath($strIntern)),
                    "size" => $intSize,
                    "state" => SyncCtoEnum::FILESTATE_TOO_BIG,
                    "raw" => "file big",
                    "transmission" => SyncCtoEnum::FILETRANS_WAITING,
                    "path_raw" => standardize($file),
                );
            }
            else
            {
                $arrChecksum[md5($strIntern)] = array(
                    "path" => $strIntern,
                    "checksum" => md5_file($this->buildPath($strIntern)),
                    "size" => $intSize,
                    "state" => SyncCtoEnum::FILESTATE_FILE,
                    "raw" => "file",
                    "transmission" => SyncCtoEnum::FILETRANS_WAITING,
                    "path_raw" => standardize($file),
                );
            }
        }

        return $arrChecksum;
    }

    // New Part--------------------

    /* -------------------------------------------------------------------------
     * Path Operations 
     */

    /**
     * Baut einen Ordnerlink zusammen, dabei wird der TL_ROOT gelöscht und 
     * neu hinzugefügt. Dabei ist es egal wo "/" gesetzt sind.
     * 
     * @param array $arrPath Array der einzelnen Ordner teilen
     * @return string 
     */
    public function buildPath()
    {
        $arrPath = func_get_args();

        if (count($arrPath) == 0 || $arrPath == null || $arrPath == "")
            return TL_ROOT;

        $strVar = "";

        foreach ($arrPath as $itPath)
        {
            $itPath = str_replace(TL_ROOT, "", $itPath);
            $itPath = explode("/", $itPath);

            foreach ($itPath as $itFolder)
            {
                if ($itFolder == "" || $itFolder == "." || $itFolder == "..")
                    continue;

                $strVar .= "/" . $itFolder;
            }
        }

        return TL_ROOT . $strVar;
    }

    /* -------------------------------------------------------------------------
     * Folder Operations 
     */

    /**
     * Prüft ob die syncCto Ordner bestehen und erstellt diese bei bedarf.
     * 
     * @param bool $booCreate 
     */
    public function checkSyncCtoFolders($booCreate = true)
    {
        $mixPath = explode("/", $GLOBALS['syncCto']['path']['db']);
        $strVar = "";
        foreach ($mixPath as $value)
        {
            if ($value == "")
                continue;

            $strVar = "/" . $value;

            if (!file_exists(TL_ROOT . $strVar))
                mkdir(TL_ROOT . $strVar);
        }

        $mixPath = explode("/", $GLOBALS['syncCto']['path']['tmp']);
        $strVar = "";
        foreach ($mixPath as $value)
        {
            if ($value == "")
                continue;

            $strVar = "/" . $value;

            if (!file_exists(TL_ROOT . $strVar))
                mkdir(TL_ROOT . $strVar);
        }

        $mixPath = explode("/", $GLOBALS['syncCto']['path']['file']);
        $strVar = "";
        foreach ($mixPath as $value)
        {
            if ($value == "")
                continue;

            $strVar = "/" . $value;

            if (!file_exists(TL_ROOT . $strVar))
                mkdir(TL_ROOT . $strVar);
        }
    }

    /**
     * Löscht den Temp Ordner bzw. Ordner innerhalb des Temp Ordners.
     * 
     * @param string $strFolder
     * @return type 
     */
    public function purgeTemp($strFolder = null)
    {
        try
        {
            if ($strFolder == null || $strFolder == "")
                $strPath = $this->buildPath($GLOBALS['syncCto']['path']['tmp']);
            else
                $strPath = $this->buildPath($GLOBALS['syncCto']['path']['tmp'], $strFolder);

            if (strpos($strPath, TL_ROOT . "/system/tmp") === FALSE)
                return;

            $arrScan = scandir($strPath);

            foreach ($arrScan as $key => $value)
            {
                // Skip
                if ($value == "." || $value == "..")
                    continue;

                if (strpos($strPath, TL_ROOT . "/system/tmp/.htaccess") !== FALSE)
                    continue;

                if (is_dir($this->buildPath($strPath, $value)))
                {
                    // Clear folder
                    $this->purgeTemp($strFolder . "/" . $value);
                    // Kill folder
                    rmdir($this->buildPath($strPath, $value));
                }
                else if (is_file($this->buildPath($strPath, $value)))
                {
                    if (strpos($this->buildPath($strPath, $value), TL_ROOT . "/system/tmp/.htaccess") !== FALSE)
                        continue;

                    // Kill file
                    unlink($this->buildPath($strPath, $value));
                }
            }

            // Check for .htaccess
            if (!file_exists($this->buildPath('/system/tmp/.htaccess')))
            {
                $objFile = new File($this->buildPath('/system/tmp/.htaccess'));
                $objFile->write("order deny,allow\ndeny from all");
                $objFile->close();
            }

            return;
        }
        catch (Exception $exc)
        {
            $this->log("Error by purge temp folder. Errormsg: " . $exc->getMessage(), __CLASS__ . " " . __FUNCTION__, "SyncCto Communication");
            return;
        }
    }

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