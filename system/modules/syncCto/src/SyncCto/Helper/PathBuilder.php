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

namespace SyncCto\Helper;

/**
 * Class PathBuilder
 *
 * A helper class to clean the path and add the TL_ROOT if wanted.
 *
 * @package SyncCto\Helper
 */
class PathBuilder
{
    /**
     * The current array with all path parts.
     *
     * @var array
     */
    protected $pathParts = array();

    /**
     * Add a path part to the system.
     *
     * @param string|array $path
     *
     * @param string       $separator
     *
     * @return $this
     */
    public function addPath($path, $separator = '/')
    {
        if (\is_array($path)) {
            $this->addArray($path);
        } else {
            $this->addString($path, $separator);
        }

        return $this;
    }

    /**
     * Add a path part to the system, but without the knowing of the directory separator.
     *
     * @param string|array $path
     *
     * @return $this
     */
    public function addUnknownPath($path)
    {
        if (\is_array($path)) {
            $this->addArray($path);
        } else {
            $wrongSeparator = ((DIRECTORY_SEPARATOR == '/') ? '\\' : '/');
            $path           = \str_replace($wrongSeparator, DIRECTORY_SEPARATOR, $path);
            $this->addString($path, DIRECTORY_SEPARATOR);
        }

        return $this;
    }

    /**
     * Build the whole path with the right directory separator.
     *
     * @param bool $withTlRoot If true the TL_ROOT will be added.
     *
     * @return string
     */
    public function getPath($withTlRoot = true)
    {
        // Build the path.
        $return = (($withTlRoot) ? TL_ROOT . DIRECTORY_SEPARATOR : '')
                  . \implode(DIRECTORY_SEPARATOR, $this->pathParts);

        // Reset the array.
        $this->pathParts = array();

        // Return the value.
        return $return;
    }

    /**
     * Add all elements to the current array.
     *
     * @param $path
     */
    protected function addArray($path)
    {
        // Trim all values.
        $path = \array_map(function ($value) {
            return \trim($value);
        }, $path);

        // Remove empty.
        $path = \array_filter($path);

        // Add to the array.
        $this->pathParts = \array_merge($this->pathParts, $path);
    }

    /**
     * Add a string path to the current array.
     *
     * @param string $path
     *
     * @param string $separator
     */
    protected function addString($path, $separator = '/')
    {
        // Remove blanks and split.
        $parts = \trimsplit($separator, $path);

        // Add.
        $this->addArray($parts);
    }
}
