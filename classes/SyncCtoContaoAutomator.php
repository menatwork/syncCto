<?php

use Contao\Automator;

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */
class SyncCtoContaoAutomator extends Automator
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
        return;

        if ($GLOBALS['TL_CONFIG']['bypassCache']) {
            return;
        }

        // Purge
//        $this->purgeInternalCache();

        // Rebuild
//        $this->generateConfigCache();
//        $this->generateDcaCache();
//        $this->generateLanguageCache();
//        $this->generateDcaExtracts();
    }

    /**
     * Purge the internal cache
     */
    public function purgeInternalCache()
    {
        if ($GLOBALS['TL_CONFIG']['bypassCache']) {
            return;
        }

        parent::purgeInternalCache();
    }

    /**
     * Build the internal cache
     */
    public function createInternalCache()
    {
        return;

        if ($GLOBALS['TL_CONFIG']['bypassCache']) {
            return;
        }

        // Rebuild
//        $this->generateConfigCache();
//        $this->generateDcaCache();
//        $this->generateLanguageCache();
//        $this->generateDcaExtracts();
    }
} 