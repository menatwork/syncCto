<?php

if ( !defined('TL_ROOT') )
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
include_once 'SyncCtoSimpleHtmlDom.php';

class SyncCtoZip extends Backend
{
    // instance
    protected static $instance = null;
    // Objects
    protected $objSyncCtoFiles;
    protected $objSyncCtoHelper;
    // Zip
    protected $strZip;
    protected $objZip;

    protected function __construct()
    {
        parent::__construct();

        $this->objSyncCtoFiles = SyncCtoFiles::getInstance();
        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();

        $this->strZip = "";
        $this->objZip = null;
    }

    public function __destruct()
    {
        $this->closeZip();
    }

    /**
     *
     * @return SyncCtoZip
     */
    public static function getInstance()
    {
        if ( self::$instance == null )
            self::$instance = new SyncCtoHelper();

        return self::$instance;
    }

    /* -------------------------------------------------------------------------
     * Setter / Getter - Operations
     */

    /**
     *
     * @param type $string 
     */
    public function setStrZip($string)
    {
        $this->strZip = $string;
    }

    /* -------------------------------------------------------------------------
     * Zip Operations
     */

    public function openZip($strZip = null)
    {
        if ( $this->objSyncCtoFiles->buildPath($strZip) == $this->strZip )
        {
            return;
        }
        else
        {
            $strFilename = $this->objSyncCtoFiles->buildPath($strZip);

            if ( !file_exists($strFilename) )
                throw new Exception(vsprintf($GLOBALS['TL_LANG']['syncCto']['file_not_exists'], array($strGetFile)));

            $this->closeZip();

            $this->objZip = new ZipArchive();

            if ( $this->objZip->open($strFilename) === TRUE )
            {
                $this->strZip = $strZip;
                return TRUE;
            }
            else
            {
                $this->objZip = null;
                throw new Exception("Could not open");
            }
        }
    }

    public function closeZip()
    {
        if ( $this->objZip != null )
            $this->objZip->close();

        return true;
    }

    /**
     *
     * @param type $strZipName
     * @param type $intFolder
     * @return type 
     */
    public function createZip($strZipName, $intFolder = 3)
    {
        @set_time_limit(30);

        $objZip = new ZipArchive();

        switch ( $intFolder )
        {
            case SyncCtoFiles::$DB_BAKUP_FOLDER:
                $strFilename = $this->objSyncCtoFiles->buildPath($GLOBALS['syncCto']['path']['db'], $strZipName);
                break;

            case SyncCtoFiles::$FILE_BAKUP_FOLDER:
                $strFilename = $this->objSyncCtoFiles->buildPath($GLOBALS['syncCto']['path']['file'], $strZipName);
                break;

            case SyncCtoFiles::$TEMP_FOLDER:
                $strFilename = $this->objSyncCtoFiles->buildPath($GLOBALS['syncCto']['path']['tmp'], $strZipName);
                break;

            default:
                throw new Exception("Unknow operation.");
                break;
        }

        if ( $objZip->open($strFilename, ZIPARCHIVE::CREATE) !== TRUE )
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['syncCto']['cant_open'], array($strFilename)));

        $objZip->addFromString("readme.txt", vsprintf($GLOBALS['TL_LANG']['syncCto']['readme'], array(date($GLOBALS['SyncCto']['settings']['time_format'], $intTstamp))));

        $objZip->close();

        if ( !file_exists($strFilename) )
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['syncCto']['file_not_exists'], array($strFilename)));

        $this->strZip = $strFilename;

        return array("name" => $strZipName, "path" => $strFilename);
    }

    /**
     * Durchsucht ein ZIP File nach einer Datei und gibt deren 
     * Inhalt zurück.
     * 
     * @param string $strGetFile File das ausgegeben werden soll.
     * @param string $strZip Pfad zu der ZIP
     * @return [false|string] 
     */
    public function getFile($strGetFile, $strZip = null)
    {
        @set_time_limit(300);

        if ( $strZip == null )
            $strFilename = $this->strZip;
        else
            $strFilename = $this->objSyncCtoFiles->buildPath($strZip);

        if ( !file_exists($strFilename) )
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['syncCto']['file_not_exists'], array($strFilename)));

        $objZip = zip_open($strFilename);

        while ( ($objRead = zip_read($objZip)) !== false )
        {
            if ( zip_entry_name($objRead) == $strGetFile )
            {
                $intSize = zip_entry_filesize($objRead);
                $strEntry = zip_entry_read($objRead, $intSize);
                return $strEntry;
            }
        }

        return FALSE;
    }

    public function setFile($strSrcFile, $strDesFile, $strZip = null)
    {
        @set_time_limit(300);

        if ( $strZip == null )
            $strFilename = $this->strZip;
        else
            $strFilename = $this->objSyncCtoFiles->buildPath($strZip);

        $strSrcFile = $this->objSyncCtoFiles->buildPath($strSrcFile);

        if ( !file_exists($strFilename) )
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['syncCto']['file_not_exists'], array($strGetFile)));

        if ( !file_exists($strSrcFile) )
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['syncCto']['file_not_exists'], array($strSrcFile)));

        $zip = new ZipArchive();

        if ( $zip->open($strFilename) === TRUE )
        {
            $zip->addFile($strSrcFile, str_replace(TL_ROOT, "", $this->objSyncCtoFiles->buildPath($strDesFile)));
        }
        else
        {
            throw new Exception("Could not open");
        }

        zip_close($zip);

        return true;
    }

    /**
     * Extract a Backupfile to TL_ROOT
     * 
     * @param string $strRestoreFile Path to file
     * @return bool 
     */
    public function restoreBackup($strRestoreFile)
    {
        @set_time_limit(3600);

        $strRestoreFile = $this->objSyncCtoFiles->buildPath($strRestoreFile);

        $zip = new ZipArchive();

        if ( $zip->open($strRestoreFile) === TRUE )
        {
            $zip->extractTo(TL_ROOT);
            $zip->close();
        }
        else
        {
            throw new Exception("Could not open");
        }

        zip_close($zip);

        return true;
    }

    private function recursiveZip($strIntern, $strExtern, ZipArchive $zip, $booSkipFolder = false)
    {
        $arrFolderBlacklist = $this->objSyncCtoHelper->getBlacklistFolder();
        $arrFileBlacklist = $this->objSyncCtoHelper->getBlacklistFile();

        foreach ( $arrFolderBlacklist as $value )
        {
            if ( stristr($strExtern, $this->objSyncCtoFiles->buildPath($value)) !== FALSE )
                return;
        }

        if ( is_dir($strExtern) )
        {
            $zip->addEmptyDir($strSource);

            // Open folder
            $hanDir = opendir($strExtern);

            // Switch throw all folders and datas
            while ( $file = readdir($hanDir) )
            {
                // Skip "." and ".."
                if ( $file != '.' && $file != '..' )
                {
                    // If dir open it and read it
                    if ( is_dir($this->objSyncCtoFiles->buildPath($strExtern, $file)) && $booSkipFolder == false )
                    {
                        if ( $strIntern == null )
                        {
                            $this->recZip($file, $strExtern . "/" . $file, $zip);
                        }
                        else
                        {
                            $this->recZip($strIntern . "/" . $file, $strExtern . "/" . $file, $zip);
                        }
                    }
                    // If file, copy to the zip archive
                    else if ( is_file($this->objSyncCtoFiles->buildPath($strExtern, $file)) )
                    {
                        // Check if file in blacklist
                        if ( !in_array($file, $arrFileBlacklist) )
                            if ( $strIntern == null )
                            {
                                $zip->addFile($strExtern . "/" . $file, $file);
                            }
                            else
                            {
                                $zip->addFile($strExtern . "/" . $file, $strIntern . "/" . $file);
                            }
                    }
                }
            }
        }
        else
        {
            // Check if file in blacklist
            $arrFileBlacklist = $this->objSyncCtoHelper->getBlacklistFile();
            if ( in_array(basename($strExtern), $arrFileBlacklist) )
                return;

            $zip->addFile($strExtern, $strIntern);
        }

        return;
    }

}

?>