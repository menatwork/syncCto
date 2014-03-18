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
 * Initialize the system
 */
define('TL_MODE', 'BACKUP');
require_once('../../initialize.php');

/**
 * Class PurgeLog
 */
class CronDeleteFileBackups extends Backend
{

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Implement the commands to run by this batch program
     */
    public function run()
    {
        $this->import('Files');
		
        $files = scan(TL_ROOT . $GLOBALS['SYC_PATH']['file']);
        foreach ($files as $file)
        {
            $f = new File($GLOBALS['SYC_PATH']['file'] . $file);

            if (strtolower($f->__get('extension')) == "zip")
            {
                $f->delete();
            }
        }
    }

}

/**
 * Instantiate log purger
 */
$objDeleteFileBackups = new CronDeleteFileBackups();
$objDeleteFileBackups->run();