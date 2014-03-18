<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL 
 * @filesource
 */

/**
 * Initialize the system
 */
$dir = dirname(isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : __FILE__);

while ($dir && $dir != '.' && $dir != '/' && !is_file($dir . '/system/initialize.php')) {
    $dir = dirname($dir);
}

if (!is_file($dir . '/system/initialize.php')) {
    header("HTTP/1.0 500 Internal Server Error");
    header('Content-Type: text/html; charset=utf-8');
    echo '<h1>500 Internal Server Error</h1>';
    echo '<p>Could not find initialize.php!</p>';
    exit(1);
}

define('TL_MODE', 'BACKUP');
require($dir . '/system/initialize.php');

/**
 * Class CronFileBackups
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

            // Check if we already have a file list
            if (!file_exists(TL_ROOT . "/" . $strXMLPath))
            {
                $booFirstRun = true;
                if (!$this->objSyncCtoFile->generateChecksumFileAsXML($strXMLPath, true, true, SyncCtoEnum::FILEINFORMATION_SMALL))
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
                $objFile->blnSyncDb = false;
                $objFile->delete();
                $objFile->close();
            }
        }
        catch (Exception $exc)
        {
            $this->log("Error by file backup with msg: " . $exc->getMessage(), __CLASS__ . " | " . __FUNCTION__, TL_CRON);
        }
    }

}

/**
 * Instantiate log purger
 */
$objFileBackups = new CronFileBackups();
$objFileBackups->run();