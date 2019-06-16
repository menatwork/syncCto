<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2015
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

namespace MenAtWork\SyncCto\Sync\FileList;

use AppendIterator;
use ArrayIterator;
use ArrayObject;
use EmptyIterator;
use FilterIterator;
use MenAtWork\SyncCto\Sync\FileList\FilterIterator\Base as FilterIteratorBase;

class Base
{
    /**
     * The first array level.
     */
    const FILE_CORE = 'core';
    const FILE_PRIVATE = 'files';

    /**
     * The list with all files.
     *
     * @var ArrayObject
     */
    private $fileList = null;

    /**
     * __construct.
     *
     * @param array $fileList A list with all data. (As reference)
     */
    public function __construct(&$fileList)
    {
        $this->fileList = new ArrayObject((is_array($fileList)) ? $fileList : array());
    }

    /**
     * Check if there are files for the core.
     *
     * @return bool True => We have files | False => bunny
     */
    public function hasCoreFiles()
    {
        return isset($this->fileList[self::FILE_CORE]) && count($this->fileList[self::FILE_CORE]);
    }

    /**
     * Check if there are files for the private folder "files".
     *
     * @return bool True => We have files | False => bunny
     */
    public function hasPrivateFiles()
    {
        return isset($this->fileList[self::FILE_PRIVATE]) && count($this->fileList[self::FILE_PRIVATE]);
    }

    /**
     * Check if there are files.
     *
     * @return bool True => We have files | False => bunny
     */
    public function hasFiles()
    {
        return $this->hasPrivateFiles() || $this->hasPrivateFiles();
    }

    //******************************************************************************************************************
    // Functions for dbafs.
    //******************************************************************************************************************

    /**
     * Get a iterator for the dbafs files.
     *
     * @param bool $onlySend  Flag that only files which are send to the client add to the list.
     *
     * @param bool $onlyMoved Flag that only files which are moved to there destination add to the list.
     *
     * @return AppendIterator The filter iterator.
     */
    protected function createDbafsIterator($onlySend, $onlyMoved)
    {
        // Setup the states.
        $states = array
        (
            \SyncCtoEnum::FILESTATE_DBAFS_CONFLICT
        );

        // Setup the transmission.
        $transmission = array();
        if ($onlySend) {
            $transmission[] = \SyncCtoEnum::FILETRANS_SEND;
        }

        if ($onlyMoved) {
            $transmission[] = \SyncCtoEnum::FILETRANS_MOVED;
        }

        // Private files.
        if ($this->hasPrivateFiles()) {
            return new FilterIteratorBase
            (
                new ArrayIterator($this->fileList[self::FILE_PRIVATE]),
                $states,
                (count($transmission) == 0) ? null : $transmission
            );
        }

        // Return a empty one.
        return new EmptyIterator();
    }

    /**
     * Get a iterator for the dvafs files.
     *
     * @param bool $onlySend  Flag that only files which are send to the client add to the list.
     *
     * @param bool $onlyMoved Flag that only files which are moved to there destination add to the list.
     *
     * @return FilterIterator The filter iterator.
     */
    public function getDbafs($onlySend, $onlyMoved)
    {
        return $this->createDbafsIterator($onlySend, $onlyMoved);
    }

    //******************************************************************************************************************
    // Functions for transfer.
    //******************************************************************************************************************

    /**
     * Get a iterator for the deleted files.
     *
     * @param bool   $onlySend  Flag that only files which are send to the client add to the list.
     *
     * @param bool   $onlyMoved Flag that only files which are moved to there destination add to the list.
     *
     * @param string $type      The type e.g. core or files.
     *
     * @return AppendIterator The filter iterator.
     */
    protected function createTransferIterator($onlySend, $onlyMoved, $type)
    {
        // Setup the states.
        $states = array
        (
            \SyncCtoEnum:: FILESTATE_MISSING,
            \SyncCtoEnum:: FILESTATE_NEED,
            \SyncCtoEnum:: FILESTATE_TOO_BIG_MISSING,
            \SyncCtoEnum:: FILESTATE_TOO_BIG_NEED,
            \SyncCtoEnum:: FILESTATE_FOLDER
        );

        // Setup the transmission.
        $transmission = array();
        if ($onlySend) {
            $transmission[] = \SyncCtoEnum::FILETRANS_SEND;
        }

        if ($onlyMoved) {
            $transmission[] = \SyncCtoEnum::FILETRANS_MOVED;
        }

        // Core files.
        if ($type == self::FILE_CORE && $this->hasCoreFiles()) {
            return new FilterIteratorBase
            (
                new ArrayIterator($this->fileList[self::FILE_CORE]),
                $states,
                (count($transmission) == 0) ? null : $transmission
            );
        }

        // Private files.
        if ($type == self::FILE_PRIVATE && $this->hasPrivateFiles()) {
            return new FilterIteratorBase
            (
                new ArrayIterator($this->fileList[self::FILE_PRIVATE]),
                $states,
                (count($transmission) == 0) ? null : $transmission
            );
        }

        // Return a empty one.
        return new EmptyIterator();
    }

    /**
     * Get a iterator for the deleted files.
     *
     * @param bool $onlySend  Flag that only files which are send to the client add to the list.
     *
     * @param bool $onlyMoved Flag that only files which are moved to there destination add to the list.
     *
     * @return FilterIterator The filter iterator.
     */
    public function getTransferCore($onlySend, $onlyMoved)
    {
        return $this->createTransferIterator($onlySend, $onlyMoved, self::FILE_CORE);
    }

    /**
     * Get a iterator for the deleted files.
     *
     * @param bool $onlySend  Flag that only files which are send to the client add to the list.
     *
     * @param bool $onlyMoved Flag that only files which are moved to there destination add to the list.
     *
     * @return FilterIterator The filter iterator.
     */
    public function getTransferPrivate($onlySend, $onlyMoved)
    {
        return $this->createTransferIterator($onlySend, $onlyMoved, self::FILE_PRIVATE);
    }

    /**
     * Get a iterator for the deleted files.
     *
     * @param bool $onlySend  Flag that only files which are send to the client add to the list.
     *
     * @param bool $onlyMoved Flag that only files which are moved to there destination add to the list.
     *
     * @return AppendIterator The filter iterator.
     */
    public function getTransferFiles($onlySend, $onlyMoved)
    {
        // Create an new append iterator.
        $appendIterator = new AppendIterator();

        // Core files.
        if ($this->hasCoreFiles()) {
            $appendIterator->append($this->createTransferIterator($onlySend, $onlyMoved, self::FILE_CORE));
        }

        // Private files.
        if ($this->hasPrivateFiles()) {
            $appendIterator->append($this->createTransferIterator($onlySend, $onlyMoved, self::FILE_PRIVATE));
        }

        return $appendIterator;
    }

    //******************************************************************************************************************
    // Functions for delete.
    //******************************************************************************************************************

    /**
     * Get a iterator for the deleted files.
     *
     * @param bool   $onlyWaiting Flag if only waiting files should returned,
     *
     * @param string $type        The type e.g. core or files.
     *
     * @return AppendIterator The filter iterator.
     */
    protected function createDeleteIterator($onlyWaiting, $type)
    {
        // Setup the states.
        $states = array
        (
            \SyncCtoEnum::FILESTATE_DELETE,
            \SyncCtoEnum::FILESTATE_FOLDER_DELETE
        );

        // Setup the transmission.
        if ($onlyWaiting) {
            $transmission = array
            (
                \SyncCtoEnum::FILETRANS_WAITING
            );
        } else {
            $transmission = array();
        }

        // Core files.
        if ($type == self::FILE_CORE && $this->hasCoreFiles()) {
            return new FilterIteratorBase
            (
                new ArrayIterator($this->fileList[self::FILE_CORE]),
                $states,
                (count($transmission) == 0) ? null : $transmission
            );
        }

        // Private files.
        if ($type == self::FILE_PRIVATE && $this->hasPrivateFiles()) {
            return new FilterIteratorBase
            (
                new ArrayIterator($this->fileList[self::FILE_PRIVATE]),
                $states,
                (count($transmission) == 0) ? null : $transmission
            );
        }

        // Return a empty one.
        return new EmptyIterator();
    }

    /**
     * Get a iterator for the deleted files.
     *
     * @param bool $onlyWaiting Flag if only waiting files should returned,
     *
     * @return FilterIterator The filter iterator.
     */
    public function getDeletedCore($onlyWaiting)
    {
        return $this->createDeleteIterator($onlyWaiting, self::FILE_CORE);
    }

    /**
     * Get a iterator for the deleted files.
     *
     * @param bool $onlyWaiting Flag if only waiting files should returned,
     *
     * @return FilterIterator The filter iterator.
     */
    public function getDeletedPrivate($onlyWaiting)
    {
        return $this->createDeleteIterator($onlyWaiting, self::FILE_PRIVATE);
    }

    /**
     * Get a iterator for the deleted files.
     *
     * @param bool $onlyWaiting Flag if only waiting files should returned,
     *
     * @return AppendIterator The filter iterator.
     */
    public function getDeletedFiles($onlyWaiting)
    {
        // Create an new append iterator.
        $appendIterator = new AppendIterator();

        // Core files.
        if ($this->hasCoreFiles()) {
            $appendIterator->append($this->createDeleteIterator($onlyWaiting, self::FILE_CORE));
        }

        // Private files.
        if ($this->hasPrivateFiles()) {
            $appendIterator->append($this->createDeleteIterator($onlyWaiting, self::FILE_PRIVATE));
        }

        return $appendIterator;
    }
}
