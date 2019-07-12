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
class SyncCtoFilterIteratorBase extends RecursiveFilterIterator
{
    ////////////////////////////////////////////////////////////////////////////
    // Vars
    ////////////////////////////////////////////////////////////////////////////
    
    /**
     * @var SyncCtoHelper 
     */
    protected $objSyncCtoFiles;


    ////////////////////////////////////////////////////////////////////////////
    // Core
    ////////////////////////////////////////////////////////////////////////////

    /**
     * 
     * @param RecursiveIterator $iterator The RecursiveIterator to be filtered.
     */
    public function __construct(\RecursiveIterator $iterator)
    {
        // Call parent.
        parent::__construct($iterator);

        // Init some helper.
        $this->objSyncCtoFiles  = SyncCtoFiles::getInstance();
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