<?php

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
$dir = dirname(isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : __FILE__);

while ($dir && $dir != '.' && $dir != '/' && !is_file($dir . '/system/initialize.php')) {
    $dir = dirname($dir);
}

if (!is_file($dir . '/system/initialize.php')) {
    header("HTTP/1.0 500 Internal Server Error");
    header('Content-Type: text/html; charset=utf-8');
    echo '<h1>500 Internal Server Error</h1>';
    echo '<p>Could not find initialize.php!</p>';
    exit(1);
}

define('TL_MODE', 'BACKUP');
require($dir . '/system/initialize.php');

/**
 * Class CronDeleteFileBackups
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