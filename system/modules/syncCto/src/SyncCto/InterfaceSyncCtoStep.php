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
