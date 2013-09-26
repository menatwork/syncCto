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
        // Check if we have a composer installation.
        $arrActiveModules = Config::getInstance()->getActiveModules();

        // If not use the old way for the auto updater.
        if (!in_array('!composer', $arrActiveModules) && !class_exists('Composer\Composer'))
        {
            $objSyncCtoRunOnceEr = new SyncCtoRunOnceEr();
            $objSyncCtoRunOnceEr->run();
        }
    }

}

$objSyncCtoRunOnce = new SyncCtoRunOnce();
$objSyncCtoRunOnce->run();
