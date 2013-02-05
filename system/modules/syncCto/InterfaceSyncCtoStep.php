<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2013 
 * @package    syncCto
 * @license    GNU/LGPL 
 * @filesource
 */

interface InterfaceSyncCtoStep
{

    /**
     * Call the syncTo function
     */
    public function syncTo();

    /**
     * Call the syncTo function
     */
    public function syncFrom();

    /**
     * Check if we have to run this function
     */
    public function checkSyncTo();

    /**
     * Check if we have to run this function
     */
    public function checkSyncFrom();
    
    /**
     * Add the SyncCtoModuleClient for container things
     * 
     * @param SyncCtoModuleClient $syncCtoClient
     */
    public function setSyncCto(SyncCtoModuleClient $syncCtoClient);
}

?>
