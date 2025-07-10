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
use Contao\System;
use Doctrine\DBAL\Exception;

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

    /**
     * Get the upload path.
     *
     * @return string
     */
    public static function getUploadPath(): string
    {
        return (string) System::getContainer()
                              ->getParameter('contao.upload_path') ?? 'files';
    }

    /**
     * Get the database name.
     *
     * @return string
     *
     * @throws Exception
     */
    public static function getDatabaseName(): string
    {
        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = System::getContainer()->get('database_connection');
        return (string) $connection->getDatabase();
    }
}
