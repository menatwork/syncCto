<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2013 
 * @package    syncCto
 * @license    GNU/LGPL 
 * @filesource
 */

/**
 * Runonce for autoupdate.
 */
class SyncCtoRunOnce
{

    public function run()
    {
        // Check the folders
        $this->checkFolders();

        // Check if we have a composer installation.
        $arrActiveModules = Config::getInstance()->getActiveModules();

        // If not use the old way for the auto updater.
        if (!in_array('!composer', $arrActiveModules))
        {
            $objSyncCtoRunOnceEr = new SyncCtoRunOnceEr();
            $objSyncCtoRunOnceEr->run();
        }
    }

    protected function checkFolders()
    {
        // Get folders from config
        $strBackupDB     = $this->standardizePath($GLOBALS['SYC_PATH']['db']);
        $strBackupFile     = $this->standardizePath($GLOBALS['SYC_PATH']['file']);
        $strTemp         = $this->standardizePath($GLOBALS['SYC_PATH']['tmp']);

        $objHt     = new File('system/modules/syncCto/config/.htaccess');
        $strHT     = $objHt->getContent();
        $objHt->close();

        // Check each one 
        if (!file_exists(TL_ROOT . '/' . $strBackupDB))
        {
            new Folder($strBackupDB);

            $objFile = new File($strBackupDB . '/' . '.htaccess');
            $objFile->write($strHT);
            $objFile->close();
        }

        if (!file_exists(TL_ROOT . '/' . $strBackupFile))
        {
            new Folder($strBackupFile);

            $objFile = new File($strBackupFile . '/' . '.htaccess');
            $objFile->write($strHT);
            $objFile->close();
        }

        if (!file_exists(TL_ROOT . '/' . $strTemp))
        {
            new Folder($strTemp);
        }
    }
    
    // Helper

    /**
     * Standardize path for folder
     * No TL_ROOT, No starting /
     * 
     * @return string the normalized path
     */
    public function standardizePath()
    {
        $arrPath = func_get_args();

        if (count($arrPath) == 0 || $arrPath == null || $arrPath == "")
        {
            return "";
        }

        $strVar = "";

        foreach ($arrPath as $itPath)
        {
            $itPath = str_replace(array(TL_ROOT, "\\"), array("", "/"), $itPath);
            $itPath = explode("/", $itPath);

            foreach ($itPath as $itFolder)
            {
                if ($itFolder == "" || $itFolder == "." || $itFolder == "..")
                {
                    continue;
                }

                $strVar .= "/" . $itFolder;
            }
        }

        return preg_replace("/^\//i", "", $strVar);
    }

}

$objSyncCtoRunOnce = new SyncCtoRunOnce();
$objSyncCtoRunOnce->run();
