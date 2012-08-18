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
    protected $objSyncCtoHelper;
    // Temp data
    protected $arrSyncSettings = array();
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
                $this->loadSyncSettings();

                $this->showNormalDatabase();

                $this->saveSyncSettings();

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
        
        // Delete functinality
        if (key_exists("delete", $_POST))
        {
            foreach ($_POST['serverTables'] as $value)
            {
                if(isset($this->arrSyncSettings['syncCto_CompareTables']['recommended']) && key_exists($value, $this->arrSyncSettings['syncCto_CompareTables']['recommended']))
                {
                     unset($this->arrSyncSettings['syncCto_CompareTables']['recommended'][$value]);
                }
                else if( isset($this->arrSyncSettings['syncCto_CompareTables']['none_recommended']) && key_exists($value, $this->arrSyncSettings['syncCto_CompareTables']['none_recommended']))
                {
                     unset($this->arrSyncSettings['syncCto_CompareTables']['none_recommended'][$value]);
                }
            }
        }
        // Close functinality
        else if (key_exists("transfer", $_POST))
        {
            foreach ($this->arrSyncSettings['syncCto_CompareTables'] as $arrType)
            {                
                foreach ($arrType as $keyTable => $valueTable)
                {
                    if($valueTable['del'] == true)
                    {
                        $this->arrSyncSettings['syncCto_SyncDeleteTables'][] = $keyTable;
                    }
                    else
                    {
                        $this->arrSyncSettings['syncCto_SyncTables'][] = $keyTable;
                    }
                }
            }
            
            unset($this->arrSyncSettings['syncCto_CompareTables']);            

            $this->mixStep = self::STEP_CLOSE_DB;
            return;
        }
        
        // If no table is found skip the view
        if (count($this->arrSyncSettings['syncCto_CompareTables']['recommended']) == 0 
                && count($this->arrSyncSettings['syncCto_CompareTables']['none_recommended']) == 0)
        {
            unset($this->arrSyncSettings['syncCto_CompareTables']);
            $this->arrSyncSettings['syncCto_SyncDeleteTables'] = array();
            $this->arrSyncSettings['syncCto_SyncTables'] = array();

            $this->mixStep = self::STEP_CLOSE_DB;
            return;
        }

        $this->Template                 = new BackendTemplate("be_syncCto_database");
        $this->Template->headline       = $GLOBALS['TL_LANG']['MSC']['comparelist'];
        $this->Template->arrCompareList = $this->arrSyncSettings['syncCto_CompareTables'];
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

    protected function loadSyncSettings()
    {
        $this->arrSyncSettings = $this->Session->get("syncCto_SyncSettings_" . $this->intClientID);

        if (!is_array($this->arrSyncSettings))
        {
            $this->arrSyncSettings = array();
        }
    }

    protected function saveSyncSettings()
    {
        if (!is_array($this->arrSyncSettings))
        {
            $this->arrSyncSettings = array();
        }

        $this->Session->set("syncCto_SyncSettings_" . $this->intClientID, $this->arrSyncSettings);
    }

}

/**
 * Instantiate controller
 */
$objPopup = new PopupSyncFiles();
$objPopup->run();
?>