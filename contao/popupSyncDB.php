<?php

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
 * @copyright  MEN AT WORK 2012
 * @package    syncCto
 * @license    GNU/GPL 2
 * @filesource
 */

/**
 * Initialize the system
 */
define('TL_MODE', 'BE');
require_once('../system/initialize.php');

/**
 * Class SyncCtoPopup
 * 
 * PHP version 5
 * @copyright  MEN AT WORK 2012
 * @package    syncCto
 * @license    GNU/GPL 2
 * @filesource
 */
class PopupSyncFiles extends Backend
{

    // Vars
    protected $intClientID;
    // Helper Classes
    protected $objSyncCtoCommunicationClient;
    protected $objSyncCtoHelper;
    // Temp data
    protected $arrListFile;
    protected $arrListCompare;
    protected $arrErrors = array();
    // defines
    const STEP_NORMAL_DB = 'nd';
    const STEP_CLOSE_DB = 'cl';
    const STEP_ERROR_DB = 'er';

    /**
     * Initialize the object
     */
    public function __construct()
    {
        $this->import('Input');
        $this->import('BackendUser', 'User');

        parent::__construct();

        $this->User->authenticate();

        $this->objSyncCtoCommunicationClient = SyncCtoCommunicationClient::getInstance();
        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();

        $this->initGetParams();
    }

    /**
     * Load the template list and go through the steps
     */
    public function run()
    {
        if ($this->mixStep == self::STEP_NORMAL_DB)
        {
            // Set client for communication
            try
            {
                $this->objSyncCtoCommunicationClient->setClientBy($this->intClientID);
                $this->showNormalDatabase();
                unset($_POST);
            }
            catch (Exception $exc)
            {
                $this->arrErrors[] = $exc->getMessage();
                $this->mixStep = self::STEP_ERROR_DB;
            }
        }

        if ($this->mixStep == self::STEP_CLOSE_DB)
        {
            $this->showClose();
        }

        if ($this->mixStep == self::STEP_ERROR_DB)
        {
            $this->showError();
        }

        // Output template
        $this->output();
    }

    /**
     * Show database server and client compare list
     */
    public function showNormalDatabase()
    {
        // Close functinality
        if (key_exists("transfer", $_POST))
        {
            $arrSyncSettings = $this->Session->get("syncCto_SyncSettings_" . $this->intClientID);
            $arrSyncSettings['syncCto_SyncTables'] = $this->Input->post('serverTables');
            $this->Session->set("syncCto_SyncSettings_" . $this->intClientID, $arrSyncSettings);

            $this->mixStep = self::STEP_CLOSE_DB;            
            return;
        }

        $this->Template = new BackendTemplate("be_syncCto_database");
        $this->Template->headline = $GLOBALS['TL_LANG']['MSC']['comparelist'];
        $this->Template->arrStatesCompareList = $this->getFormatedCompareList(
                array(
            'recommended' => $this->objSyncCtoHelper->databaseTablesRecommended(),
            'nonRecommended' => $this->objSyncCtoHelper->databaseTablesNoneRecommended()
                ), array(
            'recommended' => $this->objSyncCtoCommunicationClient->getRecommendedTables(),
            'nonRecommended' => $this->objSyncCtoCommunicationClient->getNoneRecommendedTables()
                )
        );
        $this->Template->close = FALSE;
        $this->Template->error = FALSE;
    }

    /**
     * Close popup and go throug next syncCto step
     */
    public function showClose()
    {
        $this->Template = new BackendTemplate("be_syncCto_database");
        $this->Template->headline = $GLOBALS['TL_LANG']['MSC']['backBT'];
        $this->Template->close = TRUE;
        $this->Template->error = FALSE;
    }

    /**
     * Show errors
     */
    public function showError()
    {
        $this->Template = new BackendTemplate("be_syncCto_database");
        $this->Template->headline = $GLOBALS['TL_LANG']['MSC']['error'];
        $this->Template->arrError = $this->arrErrors;
        $this->Template->text = $GLOBALS['TL_LANG']['ERR']['general'];
        $this->Template->close = FALSE;
        $this->Template->error = TRUE;
    }

    /**
     * Output templates
     */
    public function output()
    {
        // Set stylesheets
        $GLOBALS['TL_CSS'][] = TL_SCRIPT_URL . 'system/themes/' . $this->getTheme() . '/main.css';
        $GLOBALS['TL_CSS'][] = TL_SCRIPT_URL . 'system/themes/' . $this->getTheme() . '/basic.css';
        $GLOBALS['TL_CSS'][] = TL_SCRIPT_URL . 'system/themes/' . $this->getTheme() . '/popup.css';
        $GLOBALS['TL_CSS'][] = TL_SCRIPT_URL . 'system/modules/syncCto/html/css/compare_src.css';

        // Set javascript
        $GLOBALS['TL_JAVASCRIPT'][] = TL_PLUGINS_URL . 'plugins/mootools/' . MOOTOOLS_CORE . '/mootools-core.js';
        $GLOBALS['TL_JAVASCRIPT'][] = 'contao/contao.js';
        $GLOBALS['TL_JAVASCRIPT'][] = TL_SCRIPT_URL . 'system/modules/syncCto/html/js/htmltable.js';
        $GLOBALS['TL_JAVASCRIPT'][] = TL_SCRIPT_URL . 'system/modules/syncCto/html/js/compare_src.js';

        // Set wrapper template information
        $this->popupTemplate = new BackendTemplate("be_syncCto_popup");
        $this->popupTemplate->theme = $this->getTheme();
        $this->popupTemplate->base = $this->Environment->base;
        $this->popupTemplate->language = $GLOBALS['TL_LANGUAGE'];
        $this->popupTemplate->title = $GLOBALS['TL_CONFIG']['websiteTitle'];
        $this->popupTemplate->charset = $GLOBALS['TL_CONFIG']['characterSet'];
        $this->popupTemplate->headline = basename(utf8_convert_encoding($this->strFile, $GLOBALS['TL_CONFIG']['characterSet']));

        // Set default information
        $this->Template->id = $this->intClientID;
        $this->Template->step = $this->mixStep;

        // Output template
        $this->popupTemplate->content = $this->Template->parse();
        $this->popupTemplate->output();
    }

    // Helper functions --------------------------------------------------------

    /**
     * Initianize get parameter
     */
    protected function initGetParams()
    {
        // Get Client id
        if (strlen($this->Input->get("id")) != 0)
        {
            $this->intClientID = intval($this->Input->get("id"));
        }
        else
        {
            $this->mixStep = self::STEP_ERROR_DB;
            return;
        }

        // Get next step
        if (strlen($this->Input->get("step")) != 0)
        {
            $this->mixStep = $this->Input->get("step");
        }
        else
        {
            $this->mixStep = self::STEP_NORMAL_DB;
        }
    }

    /**
     * Return the given server and client tableListe as formated compared array list
     * 
     * @param type $arrServerState
     * @param type $arrClientState
     * @return type 
     */
    protected function getFormatedCompareList($arrServerState, $arrClientState)
    {               
        $arrAllTimeStamps = $this->getAllTimeStamps();
        
        $arrCompareList = array();

        foreach ($arrServerState AS $strState => $arrServerTables)
        {
            foreach ($arrServerTables AS $strTableName => $arrTable)
            {                
                $strClientState = '';

                if (array_key_exists($strTableName, $arrClientState['recommended']))
                {
                    $strClientState = 'recommended';
                }
                else if (array_key_exists($strTableName, $arrClientState['nonRecommended']))
                {
                    $strClientState = 'nonRecommended';
                }
                else
                {
                    $strClientState = FALSE;
                }
                
                $strState = (($strState == 'nonRecommended' || $strClientState == 'nonRecommended') ? 'nonRecommended' : 'recommended');
                
                $arrTmpTable = array();
                $arrClientTable = $arrClientState[$strClientState][$strTableName];
                $arrTmpTable['server']['name'] = $arrTable['name'];
                $arrTmpTable['server']['tooltip'] = $this->getReadableSize($arrTable['size']) . ', ' . $arrTable['count'] . (($arrTable['count'] == 1) ? $GLOBALS['TL_LANG']['MSC']['db_entry'] : $GLOBALS['TL_LANG']['MSC']['db_entries']);

                if ($strClientState)
                {
                    $arrTmpTable['client']['name'] = $arrClientTable['name'];
                    $arrTmpTable['diff'] = $this->getDiff($arrServerTables[$strTableName], $arrClientTable);
                    unset($arrClientState[$strClientState][$strTableName]);
                }
                else
                {
                    $arrTmpTable['diff'] = '-';
                    $arrTmpTable['client']['name'] = '-';
                }
                $arrTmpTable['client']['tooltip'] = $this->getReadableSize($arrClientTable['size']) . ', ' . $arrClientTable['count'] . (($arrClientTable['count'] == 1) ? $GLOBALS['TL_LANG']['MSC']['db_entry'] : $GLOBALS['TL_LANG']['MSC']['db_entries']);                                
                
                foreach($arrAllTimeStamps AS $strLocation => $arrTimeStamps)
                {
                    if(array_key_exists($strTableName, $arrTimeStamps['current']) && array_key_exists($strTableName, $arrTimeStamps['lastSync']))
                    {
                        $arrTmpTable[$strLocation]['class'] = (($arrTimeStamps['current'][$strTableName] == $arrTimeStamps['lastSync'][$strTableName]) ? 'unchanged' : 'changed');
                    }
                    else
                    {
                        $arrTmpTable[$strLocation]['class'] = 'no-sync';
                    }
                }
                
                if($arrTmpTable['server']['class'] == 'changed' && $arrTmpTable['client']['class'] == 'changed')
                {
                    $arrTmpTable['server']['class'] = 'changed-both';
                    $arrTmpTable['client']['class'] = 'changed-both';
                }
                else if($arrTmpTable['server']['name'] == '-')
                {
                    $arrTmpTable['server']['class'] = 'none';
                }
                else if($arrTmpTable['client']['name'] == '-')
                {
                    $arrTmpTable['client']['class'] = 'none';
                }
                
                if($arrTmpTable['server']['class'] != 'unchanged' || $arrTmpTable['client']['class'] != 'unchanged')
                {
                    $arrCompareList[$strState][$strTableName] = $arrTmpTable;
                }
            }
        }
        
        // Check if client has tables that are unknowed on server
        foreach ($arrClientState AS $strState => $arrServerTables)
        {        
            foreach ($arrServerTables AS $strTableName => $arrTable)
            {
                if (count($arrTable) > 0)
                {
                    $arrCompareList[$strState][$strTableName]['client']['name'] = $arrTable['name'];
                    $arrCompareList[$strState][$strTableName]['client']['tooltip'] = $this->getReadableSize($arrTable['size']) . ', ' . $arrTable['count'] . (($arrTable['count'] == 1) ? $GLOBALS['TL_LANG']['MSC']['db_entry'] : $GLOBALS['TL_LANG']['MSC']['db_entries']);                    
                    $arrCompareList[$strState][$strTableName]['server']['name'] = '-';
                    $arrCompareList[$strState][$strTableName]['diff'] = '-';
                }
            }
        }
        
        return $arrCompareList;
    }

    /**
     * Get the calculated differenz between the to given arrays
     * 
     * @param array $arrServerTables
     * @param array $arrClientTables
     * @return string 
     */
    public function getDiff($arrServerTables, $arrClientTables)
    {
        $intCount = 0;

        // Calculate count
        if (count($arrServerTables) == 0)
        {
            $intCount = $arrClientTables['count'];
        }
        else if (count($arrClientTables) == 0)
        {
            $intCount = $arrServerTables['count'];
        }
        else
        {
            if ($arrServerTables['count'] > $arrClientTables['count'])
            {
                $intCount = $arrServerTables['count'] - $arrClientTables['count'];
            }
            else
            {
                $intCount = $arrClientTables['count'] - $arrServerTables['count'];
            }
        }

        return $intCount . (($intCount == 1) ? $GLOBALS['TL_LANG']['MSC']['db_entry'] : $GLOBALS['TL_LANG']['MSC']['db_entries']);
    }
    
    /**
     * Return all timestamps from client and server from current and last sync
     * 
     * @return array 
     */
    protected function getAllTimeStamps()
    {
        $arrLocationLastTableTimstamp = array('server' => array(), 'client' => array());
        
        foreach($arrLocationLastTableTimstamp AS $location => $v)
        {
            $mixLastTableTimestamp = $this->Database
                    ->prepare("SELECT " . $location . "_timestamp FROM tl_synccto_clients WHERE id=?")
                    ->limit(1)
                    ->execute($this->intClientID)
                    ->fetchAllAssoc();

            if (strlen($mixLastTableTimestamp[0][$location . "_timestamp"]) != 0)
            {
                $arrLocationLastTableTimstamp[$location] = deserialize($mixLastTableTimestamp[0][$location . "_timestamp"]);
            }
            else
            {
                $arrLocationLastTableTimstamp[$location] = array();
            }
        }

        return array(
            'server' => array(
                'current'   => $this->objSyncCtoHelper->getDatabaseTablesTimestamp(),
                'lastSync'  => $arrLocationLastTableTimstamp['server']
            ),
            'client' => array(
                'current'   => $this->objSyncCtoCommunicationClient->getClientTimestamp(array()),
                'lastSync'  => $arrLocationLastTableTimstamp['client']
            )
        );
    }

}

/**
 * Instantiate controller
 */
$objPopup = new PopupSyncFiles();
$objPopup->run();
?>