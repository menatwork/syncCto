<?php

namespace MenAtWork\SyncCto\Contao\Table;

use Contao\Input;
use Contao\System;
use MenAtWork\SyncCto\Contao\ScopeMatcher;

/**
 * Class SyncTo
 *
 * @package MenAtWork\SyncCto\Contao\Table
 */
class Sync
{
    /**
     * Load some more information.
     *
     * @param $container
     *
     * @return void
     */
    public function onLoadCallback($container): void
    {
        /** @var ScopeMatcher $scopeMatcher */
        $scopeMatcher = System::getContainer()->get(ScopeMatcher::class);
        if ($scopeMatcher->isBackend() && Input::get('act') === 'startSync') {
            $GLOBALS['TL_CSS'][] = 'bundles/synccto/css/contao.css';
        }
    }
}