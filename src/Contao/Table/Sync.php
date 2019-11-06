<?php

namespace MenAtWork\SyncCto\Contao\Table;

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
    public function onLoadCallback($container)
    {
        if (TL_MODE == 'BE' && \Input::get('act') === 'startSync') {
            $GLOBALS['TL_CSS'][] = 'bundles/synccto/css/contao.css';
        }
    }
}