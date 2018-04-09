<?php

/**
 * This file is part of menatwork/synccto.
 *
 * (c) 2014-2018 MEN AT WORK.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    menatwork/synccto
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2014-2018 MEN AT WORK.
 * @license    https://github.com/menatwork/syncCto/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace SyncCto\Contao\Updater;

use Contao\Config;
use Contao\File;
use Contao\Folder;
use Contao\ModuleLoader;
use SyncCto\Contao\ExtensionRepository\RunOnce as ExtensionRepositoryRunOnce;

/**
 * Runonce for autoupdate.
 */
class Runonce
{
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
    public function __construct(){}

    public function run()
    {
        // Include the syncCto config file if we are in core mode only.
        if($GLOBALS['TL_CONFIG']['coreOnlyMode'])
        {
            include_once(TL_ROOT . '/system/modules/syncCto/config/config.php');
        }

        // Check config
        $this->updateConfig();

        // Check the folders
        $this->checkFolders();

        // Check if we have a composer installation.
        $arrActiveModules = ModuleLoader::getActive();

        // If not use the old way for the auto updater.
        if (!\in_array('!composer', $arrActiveModules) && !$GLOBALS['TL_CONFIG']['coreOnlyMode'])
        {
            $objSyncCtoRunOnceEr = new ExtensionRepositoryRunOnce();
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
        $this->importConfig($arrClearConfig);
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
        if (!\file_exists(TL_ROOT . '/' . $strBackupDB))
        {
            new Folder($strBackupDB);

            $objFile = new File($strBackupDB . '/' . '.htaccess');
            $objFile->write($strHT);
            $objFile->close();
        }

        if (!\file_exists(TL_ROOT . '/' . $strBackupFile))
        {
            new Folder($strBackupFile);

            $objFile = new File($strBackupFile . '/' . '.htaccess');
            $objFile->write($strHT);
            $objFile->close();
        }

        if (!\file_exists(TL_ROOT . '/' . $strTemp))
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
        $arrPath = \func_get_args();

        if (\count($arrPath) == 0 || $arrPath == null || $arrPath == "")
        {
            return "";
        }

        $strVar = "";

        foreach ($arrPath as $itPath)
        {
            $itPath = \str_replace(array(TL_ROOT, "\\"), array("", "/"), $itPath);
            $itPath = \explode("/", $itPath);

            foreach ($itPath as $itFolder)
            {
                if ($itFolder == "" || $itFolder == "." || $itFolder == "..")
                {
                    continue;
                }

                $strVar .= "/" . $itFolder;
            }
        }

        return \preg_replace("/^\//i", "", $strVar);
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
        $arrList          = \deserialize($strValue);
        $arrSaveList      = array();

        if (\is_array($arrSaveList) && \count($arrList) > 0)
        {
            foreach ($arrList AS $key => $strValue)
            {
                if (!\in_array($strValue, $arrConfigEntries) && !\in_array($strValue, $arrOldValues))
                {
                    $arrSaveList[$key] = $strValue;
                }
            }

            $arrSaveList = \array_keys(\array_flip($arrSaveList));
            return \serialize($arrSaveList);
        }

        return \serialize(array());
    }

    /**
     * Import configuration entries
     *
     * @param array $arrConfig
     *
     * @return void
     */
    protected function importConfig($arrConfig)
    {
        foreach ($arrConfig as $key => $value)
        {
            if ($key == "disableRefererCheck" && $value == true)
            {
                Config::getInstance()->add("\$GLOBALS['TL_CONFIG']['ctoCom_disableRefererCheck']", true);
                continue;
            }

            Config::getInstance()->add("\$GLOBALS['TL_CONFIG']['" . $key . "']", $value);
        }
    }
}

$objSyncCtoRunOnce = new Runonce();
$objSyncCtoRunOnce->run();
