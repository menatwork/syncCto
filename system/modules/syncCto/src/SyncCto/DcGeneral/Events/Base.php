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

use Contao\File;
use Contao\Message;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

abstract class Base
{
    /**
     * Check if the file cache is active or not.
     */
    public static function checkFileCache()
    {
        // Check the file cache.
        $strInitFilePath = '/system/config/initconfig.php';
        if (\file_exists(TL_ROOT . $strInitFilePath)) {
            $strFile        = new File($strInitFilePath);
            $arrFileContent = $strFile->getContentAsArray();
            foreach ($arrFileContent AS $strContent) {
                if (!\preg_match("/(\/\*|\*|\*\/|\/\/)/", $strContent)) {
                    //system/tmp.
                    if (\preg_match("/system\/tmp/", $strContent)) {
                        // Set data.
                        Message::addInfo($GLOBALS['TL_LANG']['MSC']['disabled_cache']);
                    }
                }
            }
        }
    }

    /**
     * Check if the current call is my context.
     *
     * @param EnvironmentInterface $environment The container with the env.
     *
     * @param null|string          $overwrite   Set a overwrite for the data provider name.
     *
     * @return bool Return false if this class should not do something in this context.
     */
    public function isRightContext($environment, $overwrite = null)
    {
        if ($overwrite !== null && !$environment->hasDataProvider($overwrite)) {
            return false;
        } else if ($overwrite === null && !$environment->hasDataProvider($this->getContextProviderName())) {
            return false;
        }

        return true;
    }

    /**
     * Return the name of the data provider. This name is the right context for running this class.
     *
     * @return string The name of the data provider.
     */
    abstract public function getContextProviderName();
}
