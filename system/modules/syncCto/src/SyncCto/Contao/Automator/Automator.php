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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2014-2018 MEN AT WORK.
 * @license    https://github.com/menatwork/syncCto/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace SyncCto\Contao\Automator;

use Contao\Automator as ContaoAutomator;

class Automator extends ContaoAutomator
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Build the internal cache
     */
    public function generateInternalCache()
    {
        if ($GLOBALS['TL_CONFIG']['bypassCache'])
        {
            return;
        }

        // Purge
        $this->purgeInternalCache();

        // Rebuild
        $this->generateConfigCache();
        $this->generateDcaCache();
        $this->generateLanguageCache();
        $this->generateDcaExtracts();
    }

    /**
     * Purge the internal cache
     */
    public function purgeInternalCache()
    {
        if ($GLOBALS['TL_CONFIG']['bypassCache'])
        {
            return;
        }

        parent::purgeInternalCache();
    }

    /**
     * Build the internal cache
     */
    public function createInternalCache()
    {
        if ($GLOBALS['TL_CONFIG']['bypassCache'])
        {
            return;
        }

        // Rebuild
        $this->generateConfigCache();
        $this->generateDcaCache();
        $this->generateLanguageCache();
        $this->generateDcaExtracts();
    }
}
