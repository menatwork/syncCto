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
 * Class CronDbBackups
 */
class CronDbBackups extends Backend
{

    /**
     * Set level
     * 
     * @var boolean 
     */
    protected $blnRecommended               = true;
    protected $blnNoneRecommended           = true;
    protected $blnNoneRecommendedWithHidden = true;

    /**
     * @var SyncCtoHelper 
     */
    protected $objSyncCtoHelper;

    /**
     * @var SyncCtoDatabase 
     */
    protected $objSyncCtoDatabase;

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        parent::__construct();

        // Init helper
        $this->objSyncCtoHelper   = SyncCtoHelper::getInstance();
        $this->objSyncCtoDatabase = SyncCtoDatabase::getInstance();

        // Set zip suffix
        $this->objSyncCtoDatabase->suffixZipname = "AutoBackup.zip";
    }

    /**
     * Implement the commands to run by this batch program
     */
    public function run()
    {
        try
        {
            // Get a list with all needed tables
            $arrTables = array();

            if ($this->blnRecommended)
            {
                $arrTables = array_merge($arrTables, $this->objSyncCtoHelper->databaseTablesRecommended());
            }

            if ($this->blnNoneRecommended && !$this->blnNoneRecommendedWithHidden)
            {
                $arrTables = array_merge($arrTables, $this->objSyncCtoHelper->databaseTablesNoneRecommended());
            }
            else if (!$this->blnNoneRecommended && $this->blnNoneRecommendedWithHidden)
            {
                $arrTables = array_merge($arrTables, $this->objSyncCtoHelper->databaseTablesNoneRecommendedWithHidden());
            }

            if (empty($arrTables))
            {
                $this->log("No tables found for syncCto auto DB backup.", __CLASS__ . " | " . __FUNCTION__, 'CRON');
                return;
            }

            // Run dump
            $this->objSyncCtoDatabase->runDump(array_keys($arrTables), false);
            
            $this->log("Finished syncCto auto DB backup." , __CLASS__ . " | " . __FUNCTION__, 'CRON');
        }
        catch (Exception $exc)
        {
            $this->log("Error by db backup with msg: " . $exc->getMessage(), __CLASS__ . " | " . __FUNCTION__, 'CRON');
        }
    }

}

/**
 * Instantiate log purger
 */
$objFileBackups = new CronDbBackups();
$objFileBackups->run();