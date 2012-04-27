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
 * @copyright  MEN AT WORK 2012
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
    protected static $instance         = null;
    // Vars
    protected $strSuffixZipName = "File-Backup.zip";
    protected $strTimestampFormat;
    // Lists
    protected $arrFolderBlacklist;
    protected $arrFileBlacklist;
    protected $arrRootFolderList;
    // Objects 
    protected $objSyncCtoHelper;
    protected $objFiles;

    /* -------------------------------------------------------------------------
     * Core
     */

    /**
     * Constructor
     */
    protected function __construct()
    {
        parent::__construct();

        // Init
        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();
        $this->objFiles = Files::getInstance();
        $this->strTimestampFormat = standardize($GLOBALS['TL_CONFIG']['datimFormat']);

        // Load blacklists and whitelists
        $this->arrFolderBlacklist = $this->objSyncCtoHelper->getBlacklistFolder();
        $this->arrFileBlacklist = $this->objSyncCtoHelper->getBlacklistFile();
        $this->arrRootFolderList = $this->objSyncCtoHelper->getWhitelistFolder();

        $arrSearch = array("\\", ".", "^", "?", "*");
        $arrReplace = array("\\\\", "\\.", "\\^", ".?", ".*");

        foreach ($this->arrFolderBlacklist as $key => $value)
        {
            $this->arrFolderBlacklist[$key] = str_replace($arrSearch, $arrReplace, $value);
        }

        foreach ($this->arrFileBlacklist as $key => $value)
        {
            $this->arrFileBlacklist[$key] = str_replace($arrSearch, $arrReplace, $value);
        }
    }

    /**
     * @return SyncCtoFiles 
     */
    public function __clone()
    {
        return self::$instance;
    }

    /**
     * @return SyncCtoFiles 
     */
    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new SyncCtoFiles();
        }

        return self::$instance;
    }

    /* -------------------------------------------------------------------------
     * Getter / Setter - Functions
     */

    /**
     * Return zipname
     * 
     * @return string
     */
    public function getSuffixZipName()
    {
        return $this->strSuffixZipName;
    }

    /**
     * Set zipname
     * 
     * @param string $strSuffixZipName 
     */
    public function setSuffixZipName($strSuffixZipName)
    {
        $this->strSuffixZipName = $strSuffixZipName;
    }

    /**
     * Get timestamp format
     * 
     * @return string 
     */
    public function getTimestampFormat()
    {
        return $this->strTimestampFormat;
    }

    /**
     * Set timestamp format
     * 
     * @param type $strTimestampFormat 
     */
    public function setTimestampFormat($strTimestampFormat)
    {
        $this->strTimestampFormat = $strTimestampFormat;
    }

    /* -------------------------------------------------------------------------
     * Checksum Functions
     */

    protected function getChecksumFiles($booCore = false, $booFiles = false)
    {
        $arrChecksum = array();

        $arrFiles = $this->getFileList($booCore, $booFiles);

        // Check each file
        foreach ($arrFiles as $value)
        {
            // Get filesize
            $intSize = filesize(TL_ROOT . "/" . $value);

            if ($intSize < 0 && $intSize != 0)
            {
                $arrChecksum[md5($value)] = array(
                    "path" => $value,
                    "checksum" => 0,
                    "size" => -1,
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
                    "checksum" => md5_file(TL_ROOT . "/" . $value),
                    "size" => $intSize,
                    "state" => SyncCtoEnum::FILESTATE_TOO_BIG,
                    "transmission" => SyncCtoEnum::FILETRANS_WAITING,
                );
            }
            else
            {
                $arrChecksum[md5($value)] = array(
                    "path" => $value,
                    "checksum" => md5_file(TL_ROOT . "/" . $value),
                    "size" => $intSize,
                    "state" => SyncCtoEnum::FILESTATE_FILE,
                    "transmission" => SyncCtoEnum::FILETRANS_WAITING,
                );
            }
        }

        return $arrChecksum;
    }

    /**
     * Create a checksum list from contao core folders
     * 
     * @CtoCommunication Enable
     * @return array 
     */
    public function runChecksumFolders($booFiles = false)
    {
        $arrFolderList = $this->getFolderList(true, $booFiles);
        $arrChecksum   = array();

        // Check each file
        foreach ($arrFolderList as $value)
        {
            $arrChecksum[md5($value)] = array(
                "path" => $value,
                "checksum" => 0,
                "size" => 0,
                "state" => SyncCtoEnum::FILESTATE_FILE,
                "transmission" => SyncCtoEnum::FILETRANS_WAITING,
            );
        }

        return $arrChecksum;
    }

    /**
     * Create a checksum list from contao core
     * 
     * @CtoCommunication Enable
     * @return array 
     */
    public function runChecksumCore()
    {
        return $this->getChecksumFiles(true, false);
    }

    /**
     * Create a checksum list from contao files
     * 
     * @CtoCommunication Enable
     * @return array 
     */
    public function runChecksumFiles()
    {
        return $this->getChecksumFiles(false, true);
    }

    /**
     * Check a filelist with the current filesystem
     * 
     * @param array $arrChecksumList
     * @return array 
     */
    public function runCecksumCompare($arrChecksumList)
    {
        $arrFileList = array();

        foreach ($arrChecksumList as $key => $value)
        {
            if ($value['state'] == SyncCtoEnum::FILESTATE_BOMBASTIC_BIG)
            {
                $arrFileList[$key]        = $arrChecksumList[$key];
                $arrFileList[$key]["raw"] = "file bombastic";
            }
            else if (file_exists(TL_ROOT . "/" . $value['path']))
            {
                if (md5_file(TL_ROOT . "/" . $value['path']) == $value['checksum'])
                {
                    // Do nothing
                }
                else
                {
                    if ($value['state'] == SyncCtoEnum::FILESTATE_TOO_BIG)
                    {
                        $arrFileList[$key]          = $arrChecksumList[$key];
                        $arrFileList[$key]["raw"]   = "need big";
                        $arrFileList[$key]["state"] = SyncCtoEnum::FILESTATE_TOO_BIG_NEED;
                    }
                    else
                    {
                        $arrFileList[$key]          = $arrChecksumList[$key];
                        $arrFileList[$key]["raw"]   = "need";
                        $arrFileList[$key]["state"] = SyncCtoEnum::FILESTATE_NEED;
                    }
                }
            }
            else
            {
                if ($value['state'] == SyncCtoEnum::FILESTATE_TOO_BIG)
                {
                    $arrFileList[$key]          = $arrChecksumList[$key];
                    $arrFileList[$key]["raw"]   = "missing big";
                    $arrFileList[$key]["state"] = SyncCtoEnum::FILESTATE_TOO_BIG_MISSING;
                }
                else
                {
                    $arrFileList[$key]          = $arrChecksumList[$key];
                    $arrFileList[$key]["raw"]   = "missing";
                    $arrFileList[$key]["state"] = SyncCtoEnum::FILESTATE_MISSING;
                }
            }
        }

        return $arrFileList;
    }

    /**
     * Check for deleted files with a filelist from an other system
     * 
     * @param array $arrFilelist 
     */
    public function checkDeleteFiles($arrFilelist)
    {
        $arrReturn = array();

        foreach ($arrFilelist as $keyItem => $valueItem)
        {
            if (!file_exists(TL_ROOT . "/" . $valueItem["path"]))
            {
                $arrReturn[$keyItem]          = $valueItem;
                $arrReturn[$keyItem]["state"] = SyncCtoEnum::FILESTATE_DELETE;
                $arrReturn[$keyItem]["css"]   = "deleted";
            }
        }

        return $arrReturn;
    }

    /* -------------------------------------------------------------------------
     * Dump Functions
     */

    /**
     * Make a backup from a filelist
     * 
     * @CtoCommunication Enable
     * @param string $strZip
     * @param array $arrFileList
     * @return string Filename 
     */
    public function runDump($strZip = "", $booCore = false, $arrFiles = array())
    {        
        if ($strZip == "")
        {
            $strFilename = date($this->strTimestampFormat) . "_" . $this->strSuffixZipName;
        }
        else
        {
            $strFilename = standardize(str_replace(array(" "), array("_"), preg_replace("/\.zip\z/i", "", $strZip))) . ".zip";
        }

        $strPath = $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['file'], $strFilename);

        $objZipArchive = new ZipArchiveCto();

        if (($mixError = $objZipArchive->open($strPath, ZipArchiveCto::CREATE)) !== true)
        {
            throw new Exception($GLOBALS['TL_LANG']['MSC']['error'] . ": " . $objZipArchive->getErrorDescription($mixError));
        }

        $arrFileList    = $this->getFileList($booCore, false);
        $arrFileSkipped = array();

        for ($index = 0; $index < count($arrFiles); $index++)
        {
            if (is_dir(TL_ROOT . "/" . $arrFiles[$index]))
            {
                $arrFiles = array_merge($arrFiles, $this->getFileListFromFolders(array($arrFiles[$index])));
                continue;
            }

            if ($objZipArchive->addFile($arrFiles[$index], $arrFiles[$index]) == false)
            {
                $arrFileSkipped[] = $arrFiles[$index];
            }
        }

        foreach ($arrFileList as $file)
        {
            if ($objZipArchive->addFile($file, $file) == false)
            {
                $arrFileSkipped[] = $file;
            }
        }

        $objZipArchive->close();

        return array("name" => $strFilename, "skipped" => $arrFileSkipped);
    }
    
    /**
     * Unzip files
     * 
     * @param string $strRestoreFile
     * @return void 
     */
    public function runRestore($strRestoreFile)
    {
        $objZipArchive = new ZipArchiveCto();
        
        if (($mixError = $objZipArchive->open($strRestoreFile)) !== true)
        {
            throw new Exception($GLOBALS['TL_LANG']['MSC']['error'] . ": " . $objZipArchive->getErrorDescription($mixError));
        }
        
        if($objZipArchive->numFiles == 0)
        {
            return;
        }
        
        $objZipArchive->extractTo("");
        
        $objZipArchive->close();

        return;
    }

    /* -------------------------------------------------------------------------
     * Scann Functions
     */

    /**
     * Check if the given path is in blacklist of folders
     * 
     * @param string $strPath
     * @return boolean 
     */
    protected function isInBlackFolder($strPath)
    {
        $strPath = $this->objSyncCtoHelper->standardizePath($strPath);

        foreach ($this->arrFolderBlacklist as $value)
        {
            // Search with preg for values            
            if (preg_match("^" . $value . "^i", $strPath) != 0)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the given path is in blacklist of files
     * 
     * @param string $strPath
     * @return boolean 
     */
    protected function isInBlackFile($strPath)
    {
        $strPath = $this->objSyncCtoHelper->standardizePath($strPath);

        foreach ($this->arrFileBlacklist as $value)
        {
            // Search with preg for values            
            if (preg_match("^" . $value . "^i", $strPath) != 0)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all files from a list of folders
     * 
     * @param array $arrFolders
     * @return array A List with all files 
     */
    public function getFileListFromFolders($arrFolders = array())
    {
        $arrAllFolders = array();
        $arrFiles = array();

        foreach ($arrFolders as $strFolder)
        {
            $arrAllFolders = array_merge($arrAllFolders, $this->recursiveFolderList($strFolder));
        }

        foreach ($arrAllFolders as $strFolders)
        {
            $arrResult = scan(TL_ROOT . "/" . $strFolders, true);

            foreach ($arrResult as $strFile)
            {
                if (is_file(TL_ROOT . "/" . $strFolders . "/" . $strFile))
                {
                    if ($this->isInBlackFile($strFolders . "/" . $strFile) == true)
                    {
                        continue;
                    }

                    $arrFiles[] = $strFolders . "/" . $strFile;
                }
            }
        }

        return $arrFiles;
    }

    /**
     * Get a list from all files into root and/or files
     * 
     * @param boolean $booRoot Start search from root
     * @param boolean $booFiles Start search from files
     * @return array A list with all files 
     */
    public function getFileList($booRoot = false, $booFiles = false)
    {
        // Get a list with all folders
        $arrFolder = $this->getFolderList($booRoot, $booFiles);
        $arrFiles  = array();

        // Search files in root folder
        if ($booRoot == true)
        {
            $arrResult = scan(TL_ROOT, true);

            foreach ($arrResult as $strFile)
            {
                if (is_file(TL_ROOT . "/" . $strFile))
                {
                    if ($this->isInBlackFile($strFile) == true)
                    {
                        continue;
                    }

                    $arrFiles[] = $strFile;
                }
            }
        }

        // Search in each folder
        foreach ($arrFolder as $strFolders)
        {
            $arrResult = scan(TL_ROOT . "/" . $strFolders, true);

            foreach ($arrResult as $strFile)
            {
                if (is_file(TL_ROOT . "/" . $strFolders . "/" . $strFile))
                {
                    if ($this->isInBlackFile($strFolders . "/" . $strFile) == true)
                    {
                        continue;
                    }

                    $arrFiles[] = $strFolders . "/" . $strFile;
                }
            }
        }

        return $arrFiles;
    }

    /**
     * Get a list with all folders
     * 
     * @param boolean $booRoot Start search from root
     * @param boolean $booFiles Start search from files
     * @return array A list with all folders
     */
    public function getFolderList($booRoot = false, $booFiles = false)
    {
        $arrFolders = array();

        if ($booRoot == false && $booFiles == false)
        {
            return $arrFolders;
        }

        if ($booRoot == true)
        {
            foreach ($this->arrRootFolderList as $value)
            {
                $arrFolders = array_merge($arrFolders, $this->recursiveFolderList($value));
            }
        }

        if ($booFiles == true)
        {
            $arrFolders = array_merge($arrFolders, $this->recursiveFolderList($GLOBALS['TL_CONFIG']['uploadPath']));
        }

        return $arrFolders;
    }

    /**
     * Scan path for all folders and subfolders
     * 
     * @param string $strPath start folder
     * @return array A list with all folders 
     */
    public function recursiveFolderList($strPath)
    {
        $strPath = $this->objSyncCtoHelper->standardizePath($strPath);

        if (!is_dir(TL_ROOT . "/" . $strPath) || $this->isInBlackFolder($strPath) == true)
        {
            return array();
        }

        $arrFolders = array($strPath);

        $arrResult = scan(TL_ROOT . "/" . $strPath, true);

        foreach ($arrResult as $value)
        {
            if (is_dir(TL_ROOT . "/" . $strPath . "/" . $value))
            {
                if ($this->isInBlackFolder($strPath . "/" . $value) == true)
                {
                    continue;
                }

                $arrFolders = array_merge($arrFolders, $this->recursiveFolderList($strPath . "/" . $value));
            }
        }

        return $arrFolders;
    }

    /* -------------------------------------------------------------------------
     * Folder Operations 
     */

    /**
     * Create syncCto folders if not exists
     */
    public function checkSyncCtoFolders()
    {
        $objFile = new Folder($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['db']));
        $objFile = new Folder($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp']));
        $objFile = new Folder($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['file']));
    }

    /**
     * Clear tempfolder or a folder inside of temp
     * 
     * @CtoCommunication Enable
     * @param string $strFolder
     */
    public function purgeTemp($strFolder = null)
    {
        if ($strFolder == null || $strFolder == "")
        {
            $strPath = $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp']);
        }
        else
        {
            $strPath = $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], $strFolder);
        }

        $objFolder = new Folder($strPath);
        $objFolder->clear();
    }

    /**
     * Use the contao maintance
     * 
     * @CtoCommunication Enable
     * @return array
     */
    public function runMaintenance($arrSetings)
    {
        $arrRetrun = array(
            "success" => false,
            "info_msg" => array()
        );

        $this->import('Automator');
        $this->import('StyleSheets');
        $this->import("Database");

        foreach ($arrSetings as $value)
        {
            try
            {
                switch ($value)
                {
                    // Database table
                    // Get all cachable tables from TL_CACHE
                    case "temp_tables":
                        foreach ($GLOBALS['TL_CACHE'] as $k => $v)
                        {
                            if (in_array($v, array("tl_ctocom_cache", "tl_requestcache ")))
                            {
                                continue;
                            }

                            $this->Database->execute("TRUNCATE TABLE " . $v);
                        }
                        break;

                    case "temp_folders":
                        // Html folder
                        $this->Automator->purgeHtmlFolder();
                        // Scripts folder
                        $this->Automator->purgeScriptsFolder();
                        // Temporary folder
                        $this->Automator->purgeTempFolder();
                        break;

                    // CSS files
                    case "css_create":
                        $this->StyleSheets->updateStyleSheets();
                        break;

                    case "xml_create":
                        try
                        {
                            // XML files
                            // HOOK: use the googlesitemap module
                            if (in_array('googlesitemap', $this->Config->getActiveModules()))
                            {
                                $this->import('GoogleSitemap');
                                $this->GoogleSitemap->generateSitemap();
                            }
                            else
                            {
                                $this->Automator->generateSitemap();
                            }
                        }
                        catch (Exception $exc)
                        {
                            $arrRetrun["info_msg"][] = "Error by: $value with Msg: " . $exc->getMessage();
                        }

                        try
                        {
                            // HOOK: recreate news feeds
                            if (in_array('news', $this->Config->getActiveModules()))
                            {
                                $this->import('News');
                                $this->News->generateFeeds();
                            }
                        }
                        catch (Exception $exc)
                        {
                            $arrRetrun["info_msg"][] = "Error by: $value with Msg: " . $exc->getMessage();
                        }

                        try
                        {
                            // HOOK: recreate calendar feeds
                            if (in_array('calendar', $this->Config->getActiveModules()))
                            {
                                $this->import('Calendar');
                                $this->Calendar->generateFeeds();
                            }
                        }
                        catch (Exception $exc)
                        {
                            $arrRetrun["info_msg"][] = "Error by: $value with Msg: " . $exc->getMessage();
                        }
                    default:
                        break;
                }
            }
            catch (Exception $exc)
            {
                $arrRetrun["info_msg"][] = "Error by: $value with Msg: " . $exc->getMessage();
            }
        }

        return true;
    }

    /* -------------------------------------------------------------------------
     * File Operations 
     */

    /**
     * Split files function
     * 
     * @CtoCommunication Enable
     * @param type $strSrcFile File start at TL_ROOT exp. system/foo/foo.php
     * @param type $strDesFolder Folder for split files, start at TL_ROOT , exp. system/temp/
     * @param type $strDesFile Name of file without extension. Example: Foo or MyFile
     * @param type $intSizeLimit Split Size in Bytes
     * @return int 
     */
    public function splitFiles($strSrcFile, $strDesFolder, $strDesFile, $intSizeLimit)
    {
        @set_time_limit(3600);

        if (!file_exists(TL_ROOT . "/" . $strSrcFile))
        {
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['unknown_file'], array($strSrcFile)));
        }

        $objFolder = new Folder($strDesFolder);
        $objFile   = new File($strSrcFile);

        if ($objFile->filesize < 0)
        {
            throw new Exception($GLOBALS['TL_LANG']['ERR']['64Bit_error']);
        }

        $booRun = true;
        $i      = 0;
        for ($i; $booRun; $i++)
        {
            $fp = fopen(TL_ROOT . "/" . $strSrcFile, "rb");

            if ($fp === FALSE)
            {
                throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['cant_open'], array($strSrcFile)));
            }

            if (fseek($fp, $i * $intSizeLimit, SEEK_SET) === -1)
            {
                throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['cant_open'], array($strSrcFile)));
            }

            if (feof($fp) === TRUE)
            {
                $i--;
                break;
            }

            $data = fread($fp, $intSizeLimit);
            fclose($fp);
            unset($fp);

            $objFileWrite = new File($this->objSyncCtoHelper->standardizePath($strDesFolder, $strDesFile . ".sync" . $i));
            $objFileWrite->write($data);
            $objFileWrite->close();

            unset($objFileWrite);
            unset($data);

            if (( ( $i + 1 ) * $intSizeLimit) > $objFile->filesize)
            {
                $booRun = false;
            }
        }

        return $i;
    }

    /**
     * Rebuild split files
     * 
     * @CtoCommunication Enable
     * @param type $strSplitname
     * @param type $intSplitcount
     * @param type $strMovepath
     * @param type $strMD5
     * @return type 
     */
    public function rebuildSplitFiles($strSplitname, $intSplitcount, $strMovepath, $strMD5)
    {
        // Build savepath
        $strSavePath = $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "sync", $strMovepath);

        // Create Folder
        $objFolder = new Folder(dirname($strSavePath));

        // Run for each part file
        for ($i = 0; $i < $intSplitcount; $i++)
        {
            // Build path for part file
            $strReadFile = $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], $strSplitname, $strSplitname . ".sync" . $i);

            // Check if file exists
            if (!file_exists(TL_ROOT . "/" . $strReadFile))
            {
                throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['unknown_file'], array($strSplitname . ".sync" . $i)));
            }

            // Create new file objects
            $objFilePart  = new File($strReadFile);
            $hanFileWhole = fopen(TL_ROOT . "/" . $strSavePath, "a+");

            // Write part file to main file
            fwrite($hanFileWhole, $objFilePart->getContent());

            // Close objects
            $objFilePart->close();
            fclose($hanFileWhole);

            // Free up memory
            unset($objFilePart);
            unset($hanFileWhole);

            // wait
            sleep(1);
        }

        // Check MD5 Checksum
        if (md5_file(TL_ROOT . "/" . $strSavePath) != $strMD5)
        {
            throw new Exception($GLOBALS['TL_LANG']['ERR']['checksum_error']);
        }

        return true;
    }

    /**
     * Move temp files
     * 
     * @CtoCommunication Enable
     * @param type $arrFileList
     * @return boolean 
     */
    public function moveTempFile($arrFileList)
    {
        foreach ($arrFileList as $key => $value)
        {
            if (!file_exists(TL_ROOT . "/" . $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "sync", $value["path"])))
            {
                $arrFileList[$key]["saved"] = false;
                $arrFileList[$key]["error"] = vsprintf($GLOBALS['TL_LANG']['ERR']['unknown_file'], array($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "sync", $value["path"])));
                continue;
            }

            $strFolderPath = dirname($value["path"]);

            if ($strFolderPath != ".")
            {
                $objFolder = new Folder($strFolderPath);
                unset($objFolder);
            }

            $strFileSource      = $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "sync", $value["path"]);
            $strFileDestination = $this->objSyncCtoHelper->standardizePath($value["path"]);

            if ($this->objFiles->copy($strFileSource, $strFileDestination) == false)
            {
                $arrFileList[$key]["saved"] = false;
                $arrFileList[$key]["error"] = vsprintf($GLOBALS['TL_LANG']['ERR']['cant_move_file'], array($strFileSource, $strFileDestination));
            }
            else
            {
                $arrFileList[$key]["saved"] = true;
            }
        }

        return $arrFileList;
    }

    /**
     * Delete files
     * 
     * @CtoCommunication Enable
     * @param type $arrFileList
     * @return type 
     */
    public function deleteFiles($arrFileList)
    {
        if (count($arrFileList) != 0)
        {
            foreach ($arrFileList as $key => $value)
            {

                if (is_file(TL_ROOT . "/" . $value['path']))
                {
                    try
                    {
                        if ($this->objFiles->delete($value['path']))
                        {
                            $arrFileList[$key]['transmission'] = SyncCtoEnum::FILETRANS_SEND;
                        }
                        else
                        {
                            $arrFileList[$key]['transmission'] = SyncCtoEnum::FILETRANS_SKIPPED;
                            $arrFileList[$key]["skipreason"]   = $GLOBALS['TL_LANG']['ERR']['cant_delete_file'];
                        }
                    }
                    catch (Exception $exc)
                    {
                        $arrFileList[$key]['transmission'] = SyncCtoEnum::FILETRANS_SKIPPED;
                        $arrFileList[$key]["skipreason"]   = $exc->getMessage();
                    }
                }
                else
                {
                    try
                    {
                        $this->objFiles->rrdir($value['path']);
                        $arrFileList[$key]['transmission'] = SyncCtoEnum::FILETRANS_SEND;
                    }
                    catch (Exception $exc)
                    {
                        $arrFileList[$key]['transmission'] = SyncCtoEnum::FILETRANS_SKIPPED;
                        $arrFileList[$key]["skipreason"]   = $exc->getMessage();
                    }
                }
            }
        }

        return $arrFileList;
    }

    /**
     * Receive a file and move it to the right folder.
     * 
     * @CtoCommunication Enable
     * @param type $arrMetafiles
     * @return string 
     */
    public function saveFiles($arrMetafiles)
    {
        if (!is_array($arrMetafiles) || count($_FILES) == 0)
        {
            throw new Exception($GLOBALS['TL_LANG']['ERR']['missing_file_information']);
        }

        $arrResponse = array();

        foreach ($_FILES as $key => $value)
        {
            if (!key_exists($key, $arrMetafiles))
            {
                throw new Exception($GLOBALS['TL_LANG']['ERR']['missing_file_information']);
            }

            $strFolder = $arrMetafiles[$key]["folder"];
            $strFile   = $arrMetafiles[$key]["file"];
            $strMD5    = $arrMetafiles[$key]["MD5"];

            switch ($arrMetafiles[$key]["typ"])
            {
                case SyncCtoEnum::UPLOAD_TEMP:
                    $strSaveFile = $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], $strFolder, $strFile);
                    break;

                case SyncCtoEnum::UPLOAD_SYNC_TEMP:
                    $strSaveFile = $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "sync", $strFolder, $strFile);
                    break;

                case SyncCtoEnum::UPLOAD_SQL_TEMP:
                    $strSaveFile = $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "sql", $strFile);
                    break;

                case SyncCtoEnum::UPLOAD_SYNC_SPLIT:
                    $strSaveFile = $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], $arrMetafiles[$key]["splitname"], $strFile);
                    break;

                default:
                    throw new Exception($GLOBALS['TL_LANG']['ERR']['unknown_path']);
                    break;
            }

            $objFolder = new Folder(dirname($strSaveFile));

            if ($this->objFiles->move_uploaded_file($value["tmp_name"], $strSaveFile) === FALSE)
            {
                throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['cant_move_file'], array($value["tmp_name"], $strSaveFile)));
            }
            else if ($key != md5_file(TL_ROOT . "/" . $strSaveFile))
            {
                throw new Exception($GLOBALS['TL_LANG']['ERR']['checksum_error']);
            }
            else
            {
                $arrResponse[$key] = "Saving " . $arrMetafiles[$key]["file"];
            }
        }

        return $arrResponse;
    }

    /**
     * Send a file as serelizard array
     * 
     * @CtoCommunication Enable
     * @param string $strPath
     * @return array
     */
    public function getFile($strPath)
    {
        if (!file_exists(TL_ROOT . "/" . $strPath))
        {
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['unknown_file'], array($strPath)));
        }

        $objFile    = new File($strPath);
        $strContent = base64_encode($objFile->getContent());
        $objFile->close();

        return array("md5" => md5_file(TL_ROOT . "/" . $strPath), "content" => $strContent);
    }

}

?>