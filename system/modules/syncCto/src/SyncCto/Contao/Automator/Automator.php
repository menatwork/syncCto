<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
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
