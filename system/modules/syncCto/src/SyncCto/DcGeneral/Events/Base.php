<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2015
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

namespace SyncCto\DcGeneral\Events;

abstract class Base
{
    /**
     * Check if the file cache is active or not.
     */
    public static function checkFileCache()
    {
        // Check the file cache.
        $strInitFilePath = '/system/config/initconfig.php';
        if (file_exists(TL_ROOT . $strInitFilePath))
        {
            $strFile        = new \File($strInitFilePath);
            $arrFileContent = $strFile->getContentAsArray();
            foreach ($arrFileContent AS $strContent)
            {
                if (!preg_match("/(\/\*|\*|\*\/|\/\/)/", $strContent))
                {
                    //system/tmp.
                    if (preg_match("/system\/tmp/", $strContent))
                    {
                        // Set data.
                        \Message::addInfo($GLOBALS['TL_LANG']['MSC']['disabled_cache']);
                    }
                }
            }
        }
    }
}
