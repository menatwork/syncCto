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
 * Runonce for autoupdate.
 */
class SyncCtoRunOnce
{
    /**
     * The SyncCto Helper.
     *
     * @var SyncCtoHelper
     */
    protected $objSyncCtoHelper;

    /**
     * Old settings to be replaced with the new ones.
     *
     * @var array
     */
    protected $arrFolderBlacklist = array
    (
        'assets/css',
        'assets/images',
        'assets/js',
        'composer/cache',
        'system/cache',
        'system/backup',
        'system/html',
        'system/logs',
        'system/scripts',
        'system/tmp',
        '*/syncCto_backups',
        'files/translations/export',
        'files/translations/import'
    );

    /**
     * Init
     */
    public function __construct()
    {
        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();
    }

    public function run()
    {
        // Check config
        $this->updateConfig();

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

    protected function updateConfig()
    {
        $arrClearConfig = array();

        // Cleanup the folder blacklist.
        $strLocalconfig = $GLOBALS['TL_CONFIG']['syncCto_folder_blacklist'];
        $strLocalconfig = $this->cleanEntries($strLocalconfig, 'folder_blacklist', $this->arrFolderBlacklist);

        $arrClearConfig['syncCto_folder_blacklist'] = $strLocalconfig;

        // Cleanup the file blacklist.
        $strLocalconfig = $GLOBALS['TL_CONFIG']['syncCto_file_blacklist'];
        $strLocalconfig = $this->cleanEntries($strLocalconfig, 'file_blacklist', array());

        $arrClearConfig['syncCto_file_blacklist'] = $strLocalconfig;

        // Cleanup the folder whitelist.
        $strLocalconfig = $GLOBALS['TL_CONFIG']['syncCto_folder_whitelist'];
        $strLocalconfig = $this->cleanEntries($strLocalconfig, 'folder_whitelist', array());

        $arrClearConfig['syncCto_folder_whitelist'] = $strLocalconfig;

        // Cleanup the file localconfig blacklist.
        $strLocalconfig = $GLOBALS['TL_CONFIG']['syncCto_local_blacklist'];
        $strLocalconfig = $this->cleanEntries($strLocalconfig, 'local_blacklist', array());

        $arrClearConfig['syncCto_local_blacklist'] = $strLocalconfig;

        // Cleanup the file localconfig blacklist.
        $strLocalconfig = $GLOBALS['TL_CONFIG']['syncCto_hidden_tables'];
        $strLocalconfig = $this->cleanEntries($strLocalconfig, 'table_hidden', array());

        $arrClearConfig['syncCto_hidden_tables'] = $strLocalconfig;

        // Save back to the localconfig.
        $this->objSyncCtoHelper->importConfig($arrClearConfig);
    }

    /**
     * Create the folders and protect them.
     */
    protected function checkFolders()
    {
        // Get folders from config
        $strBackupDB   = $this->standardizePath($GLOBALS['SYC_PATH']['db']);
        $strBackupFile = $this->standardizePath($GLOBALS['SYC_PATH']['file']);
        $strTemp       = $this->standardizePath($GLOBALS['SYC_PATH']['tmp']);

        $objHt = new File('system/modules/syncCto/config/.htaccess');
        $strHT = $objHt->getContent();
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

    /**
     * @param string $strValue     Values.
     *
     * @param string $strConfigKey Name of the config key.
     *
     * @param array  $arrOldValues Old value to be removed.
     *
     * @return string Return the cleaned array.
     */
    protected function cleanEntries($strValue, $strConfigKey, $arrOldValues)
    {
        $arrConfigEntries = $GLOBALS['SYC_CONFIG'][$strConfigKey];
        $arrList          = deserialize($strValue);
        $arrSaveList      = array();

        if (is_array($arrSaveList) && count($arrList) > 0)
        {
            foreach ($arrList AS $key => $strValue)
            {
                if (!in_array($strValue, $arrConfigEntries) && !in_array($strValue, $arrOldValues))
                {
                    $arrSaveList[$key] = $strValue;
                }
            }

            $arrSaveList = array_keys(array_flip($arrSaveList));
            return serialize($arrSaveList);
        }

        return serialize(array());
    }

}

$objSyncCtoRunOnce = new SyncCtoRunOnce();
$objSyncCtoRunOnce->run();
