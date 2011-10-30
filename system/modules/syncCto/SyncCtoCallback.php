<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  MEN AT WORK 2011
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource  
 */

/**
 * Callback Class for SyncCto
 */
class SyncCtoCallback extends Backend
{

    //Variablen    
    protected $objSyncCtoHelper;
    protected $objSyncCtoCodifyengine;
    //-----
    protected $BackendUser;
    //-----
    protected static $instance = null;

    // Constructer and singelten pattern
    public function __construct()
    {
        // Import Contao classes
        $this->BackendUser = BackendUser::getInstance();

        parent::__construct();

        // Import SyncCto classes
        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();
        //$this->objSyncCtoCodifyengine = SyncCtoCodifyengineFactory::getEngine();
    }

    /**
     *
     * @return SyncCtoCallback
     */
    public static function getInstance()
    {
        if (self::$instance == null) self::$instance = new SyncCtoCallback();

        return self::$instance;
    }

    /* -------------------------------------------------------------------------
     * Magical functions
     */

    public function checkExtensions($strContent, $strTemplate)
    {
        if ($strTemplate == 'be_main')
        {
            if (!is_array($_SESSION["TL_INFO"])) $_SESSION["TL_INFO"] = array();

            // required extensions
            $arrRequiredExtensions = array('ctoCommunication', 'httprequestextended', 'textwizard', '3cframework');

            // required files
            $arrRequiredFiles = array('system/drivers/DC_Memory.php');

            // check for required extensions
            foreach ($arrRequiredExtensions as $val)
            {
                if (!in_array($val, $this->Config->getActiveModules()))
                {
                    $_SESSION["TL_INFO"] = array_merge($_SESSION["TL_INFO"], array($val => 'Please install the required extension <strong>' . $val . '</strong>'));
                }
                else
                {
                    if (is_array($_SESSION["TL_INFO"]) && key_exists($val, $_SESSION["TL_INFO"]))
                    {
                        unset($_SESSION["TL_INFO"][$val]);
                    }
                }
            }

            // check for required files
            foreach ($arrRequiredFiles as $val)
            {
                if (!file_exists(TL_ROOT . '/' . $val))
                {
                    $_SESSION["TL_INFO"] = array_merge($_SESSION["TL_INFO"], array($val => 'Please install the required file <strong>' . $val . '</strong>'));
                }
                else
                {
                    if (is_array($_SESSION["TL_INFO"]) && key_exists($val, $_SESSION["TL_INFO"]))
                    {
                        unset($_SESSION["TL_INFO"][$val]);
                    }
                }
            }

            // Last syncTo with time and user information
            if ($this->Input->get("do") == "synccto_clients" && $this->Input->get("table") == "tl_syncCto_clients_syncTo")
            {
                $objSyncTime = $this->Database->prepare("SELECT cl.syncTo_tstamp as syncTo_tstamp, user.name as syncTo_user, user.username as syncTo_alias
                                            FROM tl_synccto_clients as cl 
                                            INNER JOIN tl_user as user
                                            ON cl.syncTo_user = user.id
                                            WHERE cl.id = ?")
                        ->limit(1)
                        ->execute($this->Input->get("id"));

                if ($objSyncTime->syncTo_tstamp != "" && $objSyncTime->syncTo_user != "" && $objSyncTime->syncTo_tstamp != '0' && $objSyncTime->syncTo_user != '0')
                {
                    $strLastSync = vsprintf($GLOBALS['TL_LANG']['MSC']['information_last_sync'], array(
                        date($GLOBALS['TL_CONFIG']['timeFormat'], $objSyncTime->syncTo_tstamp),
                        date($GLOBALS['TL_CONFIG']['dateFormat'], $objSyncTime->syncTo_tstamp),
                        $objSyncTime->syncTo_user,
                        $objSyncTime->syncTo_alias)
                    );

                    $_SESSION["TL_INFO"] = array_merge($_SESSION["TL_INFO"], array("lastSyncTo" => $strLastSync));
                }
            }
            else
            {
                unset($_SESSION["TL_INFO"]["lastSyncTo"]);
            }

            // Last syncFrom with time and user information
            if ($this->Input->get("do") == "synccto_clients" && $this->Input->get("table") == "tl_syncCto_clients_syncFrom")
            {
                $objSyncTime = $this->Database->prepare("SELECT cl.syncFrom_tstamp as syncFrom_tstamp, user.name as syncFrom_user
                          FROM tl_synccto_clients as cl
                          INNER JOIN tl_user as user
                          ON cl.syncFrom_user = user.id
                          WHERE cl.id = ?")
                        ->limit(1)
                        ->execute($this->Input->get("id"));

                if ($objSyncTime->syncFrom_tstamp != "" && $objSyncTime->syncFrom_user != "" && $objSyncTime->syncFrom_tstamp != '0' && $objSyncTime->syncFrom_user != '0')
                {
                    $strLastSync = vsprintf($GLOBALS['TL_LANG']['MSC']['information_last_sync'], array(
                        date($GLOBALS['TL_CONFIG']['timeFormat'], $objSyncTime->syncFrom_tstamp),
                        date($GLOBALS['TL_CONFIG']['dateFormat'], $objSyncTime->syncFrom_tstamp),
                        $objSyncTime->syncFrom_user,
                        $objSyncTime->syncTo_alias)
                    );

                    $_SESSION["TL_INFO"] = array_merge($_SESSION["TL_INFO"], array("lastSyncFrom" => $strLastSync));
                }
            }
            else
            {
                unset($_SESSION["TL_INFO"]["lastSyncFrom"]);
            }
        }

        return $strContent;
    }

    /* -------------------------------------------------------------------------
     * Load call backs for syncCto settings
     */

    public function loadBlacklistLocalconfig($strValue)
    {
        return $this->objSyncCtoHelper->getBlacklistLocalconfig();
    }

    public function loadBlacklistFolder($strValue)
    {
        return $this->objSyncCtoHelper->getBlacklistFolder();
    }

    public function loadBlacklistFile($strValue)
    {
        return $this->objSyncCtoHelper->getBlacklistFile();
    }

    public function loadWhitelistFolder($strValue)
    {
        return $this->objSyncCtoHelper->getWhitelistFolder();
    }

    public function loadTablesHidden($strValue)
    {
        return $this->objSyncCtoHelper->getTablesHidden();
    }

    /* -------------------------------------------------------------------------
     * Return all sync types as array
     */

    public function getSyncType()
    {
        $groups = array();

        foreach ($GLOBALS['SYC_SYNC'] as $key => $value)
        {
            foreach ($value as $key2 => $value2)
            {
                $groups[$key][$value2] = $key2;
            }
        }

        return $groups;
    }

    /* -------------------------------------------------------------------------
     * Return all backup types as array
     */

    public function getBackupType()
    {
        $groups = array();

        foreach ($GLOBALS['SYC_BACKUP'] as $key => $value)
        {
            foreach ($value as $key2 => $value2)
            {
                $groups[$key][$value2] = $key2;
            }
        }

        return $groups;
    }

    /* -------------------------------------------------------------------------
     * Load options for list
     */

    public function localconfigEntries()
    {
        // Read the local configuration file
        $strMode = 'top';
        $resFile = fopen(TL_ROOT . '/system/config/localconfig.php', 'rb');

        $arrData = array();

        while (!feof($resFile))
        {
            $strLine = fgets($resFile);
            $strTrim = trim($strLine);

            if ($strTrim == '?>')
            {
                continue;
            }

            if ($strTrim == '### INSTALL SCRIPT START ###')
            {
                $strMode = 'data';
                continue;
            }

            if ($strTrim == '### INSTALL SCRIPT STOP ###')
            {
                $strMode = 'bottom';
                continue;
            }

            if ($strMode == 'top')
            {
                $this->strTop .= $strLine;
            }
            elseif ($strMode == 'bottom')
            {
                $this->strBottom .= $strLine;
            }
            elseif ($strTrim != '')
            {
                $arrChunks = array_map('trim', explode('=', $strLine, 2));
                $arrData[] = str_replace(array("$", "GLOBALS['TL_CONFIG']['", "']"), array("", "", ""), $arrChunks[0]);
            }
        }

        fclose($resFile);

        return $arrData;
    }

    /**
     * Returns a whole list of all tables in the database
     * @return array 
     */
    public function hiddenTables()
    {
        $arrTables = array();

        foreach ($this->Database->listTables() as $key => $value)
        {
            $arrTables[] = $value;
        }

        return $arrTables;
    }

    /**
     * Returns a list without the hidden tables.
     * 
     * @return array 
     */
    public function databaseTables()
    {
        $arrTables = array();
        $arrTablesHidden = $this->objSyncCtoHelper->getTablesHidden();

        foreach ($this->Database->listTables() as $key => $value)
        {
            // Check if table is a hidden one.
            if (in_array($value, $arrTablesHidden)) continue;

            $arrTables[] = $value;
        }

        return $arrTables;
    }

    public function databaseTablesRecommended()
    {
        // Recommended tables
        $arrBlacklist = deserialize($GLOBALS['TL_CONFIG']['syncCto_database_tables']);
        if (!is_array($arrBlacklist))
        {
            $arrBlacklist = array();
        }

        $arrTablesPermission = $this->BackendUser->syncCto_tables;

        $arrTables = array();

        foreach ($this->databaseTables() as $key => $value)
        {
            if (in_array($value, $arrBlacklist)) continue;

            if (is_array($arrTablesPermission) && !in_array($value, $arrTablesPermission) && $this->BackendUser->isAdmin != true)
            {
                continue;
            }
            $arrTables[] = $value;
        }

        return $arrTables;
    }

    public function databaseTablesNoneRecommended()
    {
        // None recommended tables
        $arrBlacklist = deserialize($GLOBALS['TL_CONFIG']['syncCto_database_tables']);
        if (!is_array($arrBlacklist))
        {
            $arrBlacklist = array();
        }

        $arrTablesPermission = $this->BackendUser->syncCto_tables;

        $arrTables = array();

        foreach ($this->databaseTables() as $key => $value)
        {
            if (!in_array($value, $arrBlacklist)) continue;

            if (is_array($arrTablesPermission) && !in_array($value, $arrTablesPermission) && $this->BackendUser->isAdmin != true)
            {
                continue;
            }
            $arrTables[] = $value;
        }

        return $arrTables;
    }

}

?>