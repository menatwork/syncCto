<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

namespace SyncCto;

use SyncCto\Contao\Controller\Client;

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
     * Add the Client for container things
     *
     * @param Client $syncCtoClient
     *
     * TODO Create an interface for the client.
     */
    public function setSyncCto(Client $syncCtoClient);
}
