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

namespace SyncCto\Contao;

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
