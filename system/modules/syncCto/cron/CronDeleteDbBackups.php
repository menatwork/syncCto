<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2013 
 * @package    syncCto
 * @license    GNU/LGPL 
 * @filesource
 */

/**
 * Initialize the system
 */
define('TL_MODE', 'BE');
require_once('../../initialize.php');

/**
 * Class PurgeLog
 */
class CronDeleteDbBackups extends Backend
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

        $files = scan(TL_ROOT . $GLOBALS['TL_CONFIG']['uploadPath'] . '/syncCto_backups/database');
        foreach ($files as $file)
        {
            $f = new File($GLOBALS['TL_CONFIG']['uploadPath'] . '/syncCto_backups/database/' . $file);

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
$objDeleteDatabaseBackups = new CronDeleteDbBackups();
$objDeleteDatabaseBackups->run();