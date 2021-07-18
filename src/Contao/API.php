<?php

namespace MenAtWork\SyncCto\Contao;

use Contao\System;

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
     * @param int $size     The size in bytes
     *
     * @param int $decimals The number of decimals to show
     *
     * @return string The human readable size
     */
    public static function getReadableSize(int $size, int $decimals = 1): string
    {
        return \Backend::getReadableSize($size, $decimals);
    }

    /**
     * Set data into the session.
     *
     * @param string $name The name of the var.
     *
     * @param mixed  $data The data to add in the session.
     *
     * @return void
     */
    public static function setSessionData(string $name, $data): void
    {
        /** @var \Contao\Session $session */
        $session = System::getContainer()->get('session');
        $session->set($name, $data);
    }

    /**
     * Get data from the session.
     *
     * @param string $name The name of the var.
     *
     * @return mixed
     */
    public static function getSessionData(string $name)
    {
        /** @var \Contao\Session $session */
        $session = System::getContainer()->get('session');

        return $session->get($name);
    }
}
