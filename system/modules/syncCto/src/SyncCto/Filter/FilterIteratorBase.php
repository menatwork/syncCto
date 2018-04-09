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
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2014-2018 MEN AT WORK.
 * @license    https://github.com/menatwork/syncCto/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace SyncCto\Filter;

use SyncCto\Contao\Finder\Finder;
use SyncCto\Helper\Helper;

/**
 * Class for file filtering operations
 */
class FilterIteratorBase extends \RecursiveFilterIterator
{
    ////////////////////////////////////////////////////////////////////////////
    // Vars
    ////////////////////////////////////////////////////////////////////////////

    /**
     * @var Helper
     */
    protected $objSyncCtoFiles;


    ////////////////////////////////////////////////////////////////////////////
    // Core
    ////////////////////////////////////////////////////////////////////////////

    /**
     *
     * @param \RecursiveIterator $iterator The RecursiveIterator to be filtered.
     */
    public function __construct(\RecursiveIterator $iterator)
    {
        // Call parent.
        parent::__construct($iterator);

        // Init some helper.
        $this->objSyncCtoFiles  = Finder::getInstance();
    }

    /**
     * Check if we add the file to the list.
     */
    public function accept()
    {
        // If we have a dir check if it is on the blacklist.
        if($this->current()->isDir())
        {
            return !$this->objSyncCtoFiles->isInBlackFolder($this->current()->getPathname());
        }

        // Check for all other files.
        return !$this->objSyncCtoFiles->isInBlackFile($this->current()->getPathname());
    }
}
