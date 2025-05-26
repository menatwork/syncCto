<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

namespace MenAtWork\SyncCto\Contao;

use Contao\Backend;

/**
 * Class API
 *
 * Bridge between SyncCto and Contao.
 *
 * @package SyncCto\Contao
 */
class API
{
    /**
     * Convert a byte value into a human readable format
     *
     * @param integer $intSize     The size in bytes
     *
     * @param integer $intDecimals The number of decimals to show
     *
     * @return string The human readable size
     */
    public static function getReadableSize($intSize, $intDecimals = 1)
    {
        return Backend::getReadableSize($intSize, $intDecimals);
    }
}
