<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

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
class SyncCtoAutoUpdater extends Backend
{

    // Vars
    protected $booZipArchiveCto = false;

    // Const 

    const MODULE_PATH   = "system/modules/zz_syncCto_updater";
    const TEMP_PATH     = "system/tmp/";
    const ZIP_FILE_PATH = "FILES";
    const ZIP_FILE_SQL  = "SQL";

    /**
     * Constructor 
     */
    public function __construct()
    {
        parent::__construct();

        $this->checkExtensions();
    }

    /**
     * Check some extensions 
     */
    protected function checkExtensions()
    {
        if (file_exists(TL_ROOT . "/system/libraries/ZipArchiveCto.php"))
        {
            $this->booZipArchiveCto = true;
        }
    }

    /**
     * Run Update
     * 
     * @param string $strZipPath Path to the zip file.
     */
    public function update($strZipPath)
    {
        // Check if update archive exists
        if (!file_exists(TL_ROOT . "/" . $strZipPath))
        {
            throw new Exception("Update archive was not found: '$strZipPath'.");
        }

        // Update file system
        $this->updateFile($strZipPath);

        // Update database
        $this->updateDatabase($strZipPath);
    }

    /**
     * Update the filesystem 
     */
    protected function updateFile($strZipPath)
    {
        // If ZipArchiveCto is installed, use it. If not use contao`s zipReader.
        if ($this->booZipArchiveCto)
        {
            // New archive
            $objZipArchive = new ZipArchiveCto();

            // Open archive
            if (($mixError = $objZipArchive->open($strZipPath, ZipArchiveCto::CREATE)) !== true)
            {
                throw new Exception($GLOBALS['TL_LANG']['MSC']['error'] . ": " . $objZipArchive->getErrorDescription($mixError));
            }

            // Create tmp folder
            new Folder(self::TEMP_PATH . '/syncCtoAutoUpdate');

            $arrMoveList = array();

            // Extract all files to temp folder
            for ($i = 0; $i < $objZipArchive->numFiles; $i++)
            {
                $filename   = $objZipArchive->getNameIndex($i);
                $movePath   = preg_replace("/^" . ZIP_FILE_PATH . "\//i", "", $filename);
                $targetPath = self::TEMP_PATH . 'syncCtoAutoUpdate/' . $movePath;

                $arrMoveList[$targetPath] = $movePath;

                if (!$objZipArchive->extractTo($targetPath, $filename))
                {
                    throw new Exception("Error by extract file: " . $filename);
                }
            }

            $objfiles = new Files();

            // Move files from temp to destination folder/path
            foreach ($arrMoveList as $key => $value)
            {
                if (!$objfiles->rename($key, $value))
                {
                    throw new Exception("Could not move tmp file to destination.");
                }
            }
        }
        else
        {
            // TODO contao zip reader
        }
    }

    /**
     * UPdate the database.
     */
    protected function updateDatabase($strZipPath)
    {
        // New archive
        $objZipArchive = new ZipArchiveCto();

        // Open archive
        if (($mixError = $objZipArchive->open($strZipPath, ZipArchiveCto::CREATE)) !== true)
        {
            throw new Exception($GLOBALS['TL_LANG']['MSC']['error'] . ": " . $objZipArchive->getErrorDescription($mixError));
        }

        // Create tmp folder
        new Folder(self::TEMP_PATH . '/syncCtoAutoUpdate');

        $xmlReader = new XMLReader();
        $xmlReader->XML($objZipArchive->getFromName("SQL/sql.xml"));
        
        // TODO
        throw new Exception("Not impl. now.");
    }

    /**
     * Delete auto updater and update file
     * 
     * @param type $strZipPath Path to the Zip file
     */
    public function delete($strZipPath)
    {
        // Delete update zip
        $objZipFile = new File($strZipPath);
        $objZipFile->delete();

        // Delete auto updater
        $objFolder = new Folder(self::MODULE_PATH);
        $objFolder->delete();
    }

}

?>
