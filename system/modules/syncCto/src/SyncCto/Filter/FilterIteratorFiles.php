<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

namespace SyncCto\Filter;

/**
 * Class for file filtering operations
 */
class FilterIteratorFiles extends FilterIteratorBase
{

    ////////////////////////////////////////////////////////////////////////////
    // Core
    ////////////////////////////////////////////////////////////////////////////

    /**
     * Check if we add the file to the list.
     * Dirs only.
     */
    public function accept()
    {
        if($this->current()->isDir())
        {
            return false;
        }
        else
        {
            return !$this->objSyncCtoFiles->isInBlackFile($this->current()->getPathname());
        }
    }
}
