<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
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
 * Initialize the system
 */
define('TL_MODE', 'BE');
require_once('../../../initialize.php');

/**
 * Class PurgeLog
 */
class CronFileBackups extends Backend
{

    /**
     * @var SyncCtoFiles 
     */
    protected $objSyncCtoFiles;

    /**
     * @var SyncCtoHelper 
     */
    protected $objSyncCtoHelper;

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        parent::__construct();

        $this->objSyncCtoFile = SyncCtoFiles::getInstance();
        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();
    }

    /**
     * Implement the commands to run by this batch program
     */
    public function run()
    {
        try
        {
            // Create XML file path
            $strXMLPath  = $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['file'], "Auto-File-BackUp.xml");
            $booFirstRun = false;

            // Check if we allready have a filelist
            if (!file_exists(TL_ROOT . "/" . $strXMLPath))
            {
                $booFirstRun = true;

                if (!$this->objSyncCtoFile->getChecksumFilesAsXMLSmall($strXMLPath, true, true))
                {
                    $this->log("Error by creating filelist.", __CLASS__ . " | " . __FUNCTION__, TL_CRON);
                }
            }

            $arrResult = null;

            // Do Backup
            if ($booFirstRun == true)
            {
                // If first run, the function will create a file name
                $arrResult = $this->objSyncCtoFile->runIncrementalDump($strXMLPath, $GLOBALS['SYC_PATH']['file'], null, 100);

                // Save zipname into xml
                // Open XML Reader
                $objXml     = new DOMDocument("1.0", "UTF-8");
                $objXml->load(TL_ROOT . "/" . $strXMLPath);
                // Search metatags
                $objXml->getElementsByTagName("metatags")->item(0);
                // Create new tag
                $objFileXML = $objXml->createElement("zipfile", $arrResult["file"]);
                // Add to document
                $objXml->getElementsByTagName("metatags")->item(0)->appendChild($objFileXML);
                // Save
                $objXml->save(TL_ROOT . "/" . $strXMLPath);
            }
            else
            {
                // Load the zipname from xml
                $objXml     = new DOMDocument("1.0", "UTF-8");
                $objXml->load(TL_ROOT . "/" . $strXMLPath);
                $strZipFile = $objXml->getElementsByTagName("zipfile")->item(0)->nodeValue;
                unset($objXml);

                // Run backup
                $arrResult = $this->objSyncCtoFile->runIncrementalDump($strXMLPath, $GLOBALS['SYC_PATH']['file'], $strZipFile, 100);
            }

            // If all work is done delete the filelist
            if ($arrResult["done"] == true)
            {
                $objFile = new File($strXMLPath);
                $objFile->delete();
                $objFile->close();
            }
        }
        catch (Exception $exc)
        {
            $this->log("Error by file backup with msg: " . $exc->getMessage(), __CLASS__ . " | " . __FUNCTION__, TL_CRON);
            var_dump($exc);
        }
    }

}

/**
 * Instantiate log purger
 */
$objFileBackups = new CronFileBackups();
$objFileBackups->run();
?>