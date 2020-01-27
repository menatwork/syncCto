<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL 
 * @filesource
 */

/**
 * Class for file filtering operations
 */
class SyncCtoFilterIteratorFolder extends SyncCtoFilterIteratorBase
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
            return !$this->objSyncCtoFiles->isInBlackFolder($this->current()->getPathname());
        }
        else
        {
            return false;
        }
    }
}