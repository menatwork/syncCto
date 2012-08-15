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
    protected $strMode;
    // Helper Classes
    protected $objSyncCtoCommunicationClient;
    protected $objSyncCtoHelper;
    // Temp data
    protected $arrListFile;
    protected $arrListCompare;
    protected $arrErrors = array();

    // defines

    const STEP_NORMAL_DB = 'nd';
    const STEP_CLOSE_DB  = 'cl';
    const STEP_ERROR_DB  = 'er';

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
        $this->objSyncCtoHelper              = SyncCtoHelper::getInstance();

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
                $this->mixStep     = self::STEP_ERROR_DB;
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
            $arrSyncSettings                             = $this->Session->get("syncCto_SyncSettings_" . $this->intClientID);
            $arrSyncSettings['syncCto_SyncTables']       = $this->Input->post('serverTables');
            $arrSyncSettings['syncCto_SyncDeleteTables'] = $this->Input->post('serverDeleteTables');
            $this->Session->set("syncCto_SyncSettings_" . $this->intClientID, $arrSyncSettings);

            $this->mixStep = self::STEP_CLOSE_DB;
            return;
        }

        $arrCompareList = array();

        // Check user
        if ($this->User->isAdmin || $this->User->syncCto_tables != null)
        {
            // Load allowed tables for this user
            if (!$this->User->isAdmin)
            {
                $arrAllowedTables = true;
            }
            else
            {
                $arrAllowedTables = $this->User->syncCto_tables;
            }

            $arrServerTableR  = $this->objSyncCtoHelper->databaseTablesRecommended();
            $arrServerTableNR = $this->objSyncCtoHelper->databaseTablesNoneRecommended();
            $arrServerTableH  = $this->objSyncCtoHelper->getTablesHidden();

            $arrClientTableR  = $this->objSyncCtoCommunicationClient->getRecommendedTables();
            $arrClientTableNR = $this->objSyncCtoCommunicationClient->getNoneRecommendedTables();
            $arrClientTableH  = $this->objSyncCtoCommunicationClient->getHiddenTables();

            // Merge all together
            foreach ($arrServerTableR as $key => $value)
            {
                $arrServerTableR[$key]['type'] = 'recommended';
            }

            foreach ($arrClientTableR as $key => $value)
            {
                $arrClientTableR[$key]['type'] = 'recommended';
            }

            foreach ($arrServerTableNR as $key => $value)
            {
                $arrServerTableNR[$key]['type'] = 'nonRecommended';
            }

            foreach ($arrClientTableNR as $key => $value)
            {
                $arrClientTableNR[$key]['type'] = 'nonRecommended';
            }

            $arrServerTables  = array_merge($arrServerTableR, $arrServerTableNR);
            $arrClientTables  = array_merge($arrClientTableR, $arrClientTableNR);
            $arrHiddenTables  = array_keys(array_flip(array_merge($arrServerTableH, $arrClientTableH)));
            $arrAllTimeStamps = $this->getAllTimeStamps();

            switch ($this->strMode)
            {
                case 'To':
                    $arrCompareList = $this->getFormatedCompareList($arrServerTables, $arrClientTables, $arrHiddenTables, $arrAllTimeStamps['server'], $arrAllTimeStamps['client'], $arrAllowedTables, 'server', 'client');
                    break;

                case 'From':
                    $arrCompareList = $this->getFormatedCompareList($arrClientTables, $arrServerTables, $arrHiddenTables, $arrDesTS['client'], $arrAllTimeStamps['server'], $arrAllowedTables, 'client', 'server');
                    break;
            }
        }

        $this->Template                 = new BackendTemplate("be_syncCto_database");
        $this->Template->headline       = $GLOBALS['TL_LANG']['MSC']['comparelist'];
        $this->Template->arrCompareList = $arrCompareList;
        $this->Template->close          = FALSE;
        $this->Template->error          = FALSE;
    }

    /**
     * Close popup and go throug next syncCto step
     */
    public function showClose()
    {
        $this->Template           = new BackendTemplate("be_syncCto_database");
        $this->Template->headline = $GLOBALS['TL_LANG']['MSC']['backBT'];
        $this->Template->close    = TRUE;
        $this->Template->error    = FALSE;
    }

    /**
     * Show errors
     */
    public function showError()
    {
        $this->Template           = new BackendTemplate("be_syncCto_database");
        $this->Template->headline = $GLOBALS['TL_LANG']['MSC']['error'];
        $this->Template->arrError = $this->arrErrors;
        $this->Template->text     = $GLOBALS['TL_LANG']['ERR']['general'];
        $this->Template->close    = FALSE;
        $this->Template->error    = TRUE;
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
        $GLOBALS['TL_CSS'][] = TL_SCRIPT_URL . 'system/modules/syncCto/html/css/compare.css';

        // Set javascript
        $GLOBALS['TL_JAVASCRIPT'][] = TL_PLUGINS_URL . 'plugins/mootools/' . MOOTOOLS_CORE . '/mootools-core.js';
        $GLOBALS['TL_JAVASCRIPT'][] = 'contao/contao.js';

        if (version_compare(VERSION, '2.11', '=='))
        {
            $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/syncCto/html/js/htmltable.js';
        }

        $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/syncCto/html/js/compare.js';

        // Set wrapper template information
        $this->popupTemplate           = new BackendTemplate("be_syncCto_popup");
        $this->popupTemplate->theme    = $this->getTheme();
        $this->popupTemplate->base     = $this->Environment->base;
        $this->popupTemplate->language = $GLOBALS['TL_LANGUAGE'];
        $this->popupTemplate->title    = $GLOBALS['TL_CONFIG']['websiteTitle'];
        $this->popupTemplate->charset  = $GLOBALS['TL_CONFIG']['characterSet'];
        $this->popupTemplate->headline = basename(utf8_convert_encoding($this->strFile, $GLOBALS['TL_CONFIG']['characterSet']));

        // Set default information
        $this->Template->id        = $this->intClientID;
        $this->Template->step      = $this->mixStep;
        $this->Template->direction = $this->strMode;

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
        if (strlen($this->Input->get('id')) != 0)
        {
            $this->intClientID = intval($this->Input->get('id'));
        }
        else
        {
            $this->mixStep = self::STEP_ERROR_DB;
            return;
        }

        // Get 
        // Get Client id
        if (strlen($this->Input->get('direction')) != 0)
        {
            $this->strMode = $this->Input->get('direction');
        }
        else
        {
            $this->mixStep = self::STEP_ERROR_DB;
            return;
        }

        // Get next step
        if (strlen($this->Input->get('step')) != 0)
        {
            $this->mixStep = $this->Input->get('step');
        }
        else
        {
            $this->mixStep = self::STEP_NORMAL_DB;
        }
    }

    /**
     * 
     * @param type $arrSourceTables
     * @param type $arrDesTables
     * @param type $arrHiddenTables
     * @param type $arrSourceTS
     * @param type $arrDesTS
     * @return string
     */
    protected function getFormatedCompareList($arrSourceTables, $arrDesTables, $arrHiddenTables, $arrSourceTS, $arrDesTS, $arrAllowedTables, $strSrcName, $strDesName)
    {
        // Remove hidden tables or tables without premission
        foreach ($arrSourceTables as $key => $value)
        {
            if (in_array($key, $arrHiddenTables) || (is_array($arrAllowedTables) && in_array($key, $arrAllowedTables)))
            {
                unset($arrSourceTables[$key]);
            }
        }

        foreach ($arrDesTables as $key => $value)
        {
            if (in_array($key, $arrHiddenTables) || (is_array($arrAllowedTables) && in_array($key, $arrAllowedTables)))
            {
                unset($arrDesTables[$key]);
            }
        }

        $arrCompareList = array();

        // Make a diff
        $arrMissingOnDes    = array_diff(array_keys($arrSourceTables), array_keys($arrDesTables));
        $arrMissingOnSource = array_diff(array_keys($arrDesTables), array_keys($arrSourceTables));

        // New Tables
        foreach ($arrMissingOnDes as $keySrcTables)
        {
            $strType = $arrSourceTables[$keySrcTables]['type'];

            $arrCompareList[$strType][$keySrcTables][$strSrcName]['name']    = $keySrcTables;
            $arrCompareList[$strType][$keySrcTables][$strSrcName]['tooltip'] = $this->getReadableSize($arrSourceTables[$keySrcTables]['size']) . ', ' . $arrSourceTables[$keySrcTables]['count'] . (($arrSourceTables[$keySrcTables]['count'] == 1) ? $GLOBALS['TL_LANG']['MSC']['db_entry'] : $GLOBALS['TL_LANG']['MSC']['db_entries']);
            $arrCompareList[$strType][$keySrcTables][$strSrcName]['class']   = 'none';

            $arrCompareList[$strType][$keySrcTables][$strDesName]['name'] = '-';
            $arrCompareList[$strType][$keySrcTables]['diff']           = $GLOBALS['TL_LANG']['MSC']['new_data'];

            unset($arrSourceTables[$keySrcTables]);
        }

        // Del Tables
        foreach ($arrMissingOnSource as $keyDesTables)
        {
            $strType = $arrDesTables[$keyDesTables]['type'];

            $arrCompareList[$strType][$keyDesTables][$strDesName]['name']    = $keyDesTables;
            $arrCompareList[$strType][$keyDesTables][$strDesName]['tooltip'] = $this->getReadableSize($arrDesTables[$keyDesTables]['size']) . ', ' . $arrDesTables[$keyDesTables]['count'] . (($arrDesTables[$keyDesTables]['count'] == 1) ? $GLOBALS['TL_LANG']['MSC']['db_entry'] : $GLOBALS['TL_LANG']['MSC']['db_entries']);
            $arrCompareList[$strType][$keyDesTables][$strDesName]['class']   = 'none';

            $arrCompareList[$strType][$keyDesTables][$strSrcName]['name'] = '-';
            $arrCompareList[$strType][$keyDesTables]['diff']              = $GLOBALS['TL_LANG']['MSC']['deleted_data'];
            $arrCompareList[$strType][$keyDesTables]['del']               = true;

            unset($arrDesTables[$keyDesTables]);
        }

        // Tables which exsist on both systems
        foreach ($arrSourceTables as $keySrcTable => $valueSrcTable)
        {
            $strType = $valueSrcTable['type'];

            $arrCompareList[$strType][$keySrcTable][$strSrcName]['name']    = $keySrcTable;
            $arrCompareList[$strType][$keySrcTable][$strSrcName]['tooltip'] = $this->getReadableSize($valueSrcTable['size']) . ', ' . $valueSrcTable['count'] . (($valueSrcTable['count'] == 1) ? $GLOBALS['TL_LANG']['MSC']['db_entry'] : $GLOBALS['TL_LANG']['MSC']['db_entries']);

            $valueClientTable = $arrDesTables[$keySrcTable];

            $arrCompareList[$strType][$keySrcTable][$strDesName]['name']    = $keySrcTable;
            $arrCompareList[$strType][$keySrcTable][$strDesName]['tooltip'] = $this->getReadableSize($valueClientTable['size']) . ', ' . $valueClientTable['count'] . (($valueClientTable['count'] == 1) ? $GLOBALS['TL_LANG']['MSC']['db_entry'] : $GLOBALS['TL_LANG']['MSC']['db_entries']);

            $intDiff                                             = $this->getDiff($valueSrcTable, $valueClientTable);
            // Add 'entry' or 'entries' to diff
            $arrCompareList[$strType][$keySrcTable]['diffCount'] = $intDiff;
            $arrCompareList[$strType][$keySrcTable]['diff']      = $intDiff . (($intDiff == 1) ? $GLOBALS['TL_LANG']['MSC']['db_entry'] : $GLOBALS['TL_LANG']['MSC']['db_entries']);

            // Check timestamps
            if (key_exists($keySrcTable, $arrSourceTS['current']) && key_exists($keySrcTable, $arrSourceTS['lastSync']))
            {
                if ($arrSourceTS['current'][$keySrcTable] == $arrSourceTS['lastSync'][$keySrcTable])
                {
                    $arrCompareList[$strType][$keySrcTable][$strSrcName]['class'] = 'unchanged';
                }
                else
                {
                    $arrCompareList[$strType][$keySrcTable][$strSrcName]['class'] = 'changed';
                }
            }
            else
            {
                $arrCompareList[$strType][$keySrcTable][$strSrcName]['class'] = 'no-sync';
            }

            if (key_exists($keySrcTable, $arrDesTS['current']) && key_exists($keySrcTable, $arrDesTS['lastSync']))
            {
                if ($arrDesTS['current'][$keySrcTable] == $arrDesTS['lastSync'][$keySrcTable])
                {
                    $arrCompareList[$strType][$keySrcTable][$strDesName]['class'] = 'unchanged';
                }
                else
                {
                    $arrCompareList[$strType][$keySrcTable][$strDesName]['class'] = 'changed';
                }
            }
            else
            {
                $arrCompareList[$strType][$keySrcTable][$strDesName]['class'] = 'no-sync';
            }

            // Check CSS
            if ($arrCompareList[$strType][$keySrcTable][$strSrcName]['class'] == 'changed' && $arrCompareList[$strType][$keySrcTable]['client']['class'] == 'changed')
            {
                $arrCompareList[$strType][$keySrcTable][$strSrcName]['class'] = 'changed-both';
                $arrCompareList[$strType][$keySrcTable][$strSrcName]['class'] = 'changed-both';
            }

            // Check if we have some changes
            if ($arrCompareList[$strType][$keySrcTable][$strSrcName]['class'] == 'unchanged'
                    && $arrCompareList[$strType][$keySrcTable][$strDesName]['class'] == 'unchanged'
                    && $arrCompareList[$strType][$keySrcTable]['diffCount'] == 0
            )
            {
                unset($arrCompareList[$strType][$keySrcTable]);
                continue;
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

        return $intCount;
    }

    /**
     * Return all timestamps from client and server from current and last sync
     * 
     * @return array 
     */
    protected function getAllTimeStamps()
    {
        $arrLocationLastTableTimstamp = array('server' => array(), 'client' => array());

        foreach ($arrLocationLastTableTimstamp AS $location => $v)
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
                'current'  => $this->objSyncCtoHelper->getDatabaseTablesTimestamp(),
                'lastSync' => $arrLocationLastTableTimstamp['server']
            ),
            'client'   => array(
                'current' => $this->objSyncCtoCommunicationClient->getClientTimestamp(array()),
                'lastSync' => $arrLocationLastTableTimstamp['client']
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