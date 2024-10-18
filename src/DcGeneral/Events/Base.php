<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2015
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

namespace MenAtWork\SyncCto\DcGeneral\Events;

use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

abstract class Base
{
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
        } else {
            if ($overwrite === null && !$environment->hasDataProvider($this->getContextProviderName())) {
                return false;
            }
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
