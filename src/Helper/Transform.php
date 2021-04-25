<?php


namespace MenAtWork\SyncCto\Helper;


class Transform
{
    /**
     * Parse size
     *
     * @see http://us2.php.net/manual/en/function.ini-get.php#example-501
     *
     * @param string $size
     *
     * @return int|string
     */
    public static function parseSize($size)
    {
        if ($size == -1) {
            return PHP_INT_MAX;
        }

        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1]);
        switch ($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $size *= 1024;
            case 'm':
                $size *= 1024;
            case 'k':
                $size *= 1024;
        }

        return $size;
    }
}