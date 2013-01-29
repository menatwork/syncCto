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
    protected $objSyncCtoHelper;
    // Temp data
    protected $arrListFile;
    protected $arrListCompare;
    protected $arrClientInformation;

    // defines

    const STEP_SHOW_FILES  = 'Sf';
    const STEP_CLOSE_FILES = 'cl';
    const STEP_ERROR_FILES = 'er';

    /**
     * Initialize the object
     */
    public function __construct()
    {
        $this->import('Input');
        $this->import('BackendUser', 'User');

        parent::__construct();

        $this->User->authenticate();

        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();

        $this->initGetParams();
    }

    /**
     * Load the template list and go through the steps
     */
    public function run()
    {
        if ($this->mixStep == self::STEP_SHOW_FILES)
        {
            $this->loadTempLists();
            $this->showFiles();
            $this->saveTempLists();
            unset($_POST);
        }

        if ($this->mixStep == self::STEP_CLOSE_FILES)
        {
            $this->showClose();
        }

        if ($this->mixStep == self::STEP_ERROR_FILES)
        {
            $this->showError();
        }

        // Output template
        $this->output();
    }

    protected function showFiles()
    {
        // Delete functinality
        if (key_exists("delete", $_POST))
        {
            foreach ($_POST as $key => $value)
            {
                unset($this->arrListCompare[$value]);
            }
        }
        // Close functinality
        else if (key_exists("transfer", $_POST))
        {
            $this->mixStep = self::STEP_CLOSE_FILES;
            return;
        }

        // Check if filelist is empty and close
        if (count($this->arrListCompare) == 0)
        {
            $this->mixStep = self::STEP_CLOSE_FILES;
            return;
        }

        // Counter
        $intCountMissing = 0;
        $intCountNeed    = 0;
        $intCountIgnored = 0;
        $intCountDelete  = 0;

        $intTotalSizeNew    = 0;
        $intTotalSizeDel    = 0;
        $intTotalSizeChange = 0;

        // Lists
        $arrNormalFiles = array();
        $arrBigFiles = array();

        // Build list
        foreach ($this->arrListCompare as $key => $value)
        {
            switch ($value['state'])
            {
                case SyncCtoEnum::FILESTATE_TOO_BIG_MISSING:
                case SyncCtoEnum::FILESTATE_MISSING:
                    $intCountMissing++;
                    $intTotalSizeNew += $value["size"];
                    break;

                case SyncCtoEnum::FILESTATE_TOO_BIG_NEED:
                case SyncCtoEnum::FILESTATE_NEED:
                    $intCountNeed++;
                    $intTotalSizeChange += $value["size"];
                    break;

                case SyncCtoEnum::FILESTATE_TOO_BIG_DELETE :
                case SyncCtoEnum::FILESTATE_DELETE:
                case SyncCtoEnum::FILESTATE_FOLDER_DELETE:
                    $intCountDelete++;
                    $intTotalSizeDel += $value["size"];
                    break;

                case SyncCtoEnum::FILESTATE_BOMBASTIC_BIG:
                    $intCountIgnored++;
                    break;
            }

            if (in_array($value["state"], array(
                        SyncCtoEnum::FILESTATE_TOO_BIG_DELETE,
                        SyncCtoEnum::FILESTATE_TOO_BIG_MISSING,
                        SyncCtoEnum::FILESTATE_TOO_BIG_NEED,
                        SyncCtoEnum::FILESTATE_TOO_BIG_SAME,
                        SyncCtoEnum::FILESTATE_BOMBASTIC_BIG
            )))
            {
                $arrBigFiles[$key] = $value;
            }
            else if ($value["split"] == 1)
            {
                $arrBigFiles[$key] = $value;
            }
            else if($value["size"] > $this->arrClientInformation["upload_sizeLimit"])
            {
                $arrBigFiles[$key] = $value;
            }
            else
            {
                $arrNormalFiles[$key] = $value;
            }
        }

        uasort($arrBigFiles, array($this, 'sort'));
        uasort($arrNormalFiles, array($this, 'sort'));

        // Language array for filestate
        $arrLanguageTags = array();
        $arrLanguageTags[SyncCtoEnum::FILESTATE_MISSING]         = $GLOBALS['TL_LANG']['MSC']['create'];
        $arrLanguageTags[SyncCtoEnum::FILESTATE_NEED]            = $GLOBALS['TL_LANG']['MSC']['overrideSelected'];
        $arrLanguageTags[SyncCtoEnum::FILESTATE_DELETE]          = $GLOBALS['TL_LANG']['MSC']['delete'];
        $arrLanguageTags[SyncCtoEnum::FILESTATE_FOLDER_DELETE]   = $GLOBALS['TL_LANG']['MSC']['delete'];
        $arrLanguageTags[SyncCtoEnum::FILESTATE_TOO_BIG_MISSING] = $GLOBALS['TL_LANG']['MSC']['skipped'];
        $arrLanguageTags[SyncCtoEnum::FILESTATE_TOO_BIG_NEED]    = $GLOBALS['TL_LANG']['MSC']['skipped'];
        $arrLanguageTags[SyncCtoEnum::FILESTATE_TOO_BIG_DELETE]  = $GLOBALS['TL_LANG']['MSC']['skipped'];
        $arrLanguageTags[SyncCtoEnum::FILESTATE_BOMBASTIC_BIG]   = $GLOBALS['TL_LANG']['MSC']['ignored'];
        
        // Set template
        $this->Template                  = new BackendTemplate('be_syncCto_files');
        $this->Template->maxLength       = 60;
        $this->Template->arrLangStates   = $arrLanguageTags;
        $this->Template->headline        = $GLOBALS['TL_LANG']['MSC']['comparelist'];
        $this->Template->normalFilelist  = $arrNormalFiles;
        $this->Template->bigFilelist     = $arrBigFiles;
        $this->Template->totalsizeNew    = $intTotalSizeNew;
        $this->Template->totalsizeDel    = $intTotalSizeDel;
        $this->Template->totalsizeChange = $intTotalSizeChange;
        $this->Template->compare_complex = FALSE;
        $this->Template->close           = FALSE;
        $this->Template->error           = FALSE;
    }

    /**
     * Close popup and go throug next syncCto step
     */
    public function showClose()
    {
        $this->Template           = new BackendTemplate("be_syncCto_files");
        $this->Template->headline = $GLOBALS['TL_LANG']['MSC']['backBT'];
        $this->Template->close    = TRUE;
        $this->Template->error    = FALSE;
    }

    /**
     * Show errors
     */
    public function showError()
    {
        $this->Template           = new BackendTemplate("be_syncCto_files");
        $this->Template->headline = $GLOBALS['TL_LANG']['MSC']['error'];
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
        $GLOBALS['TL_CSS'][] = 'system/themes/' . $this->getTheme() . '/main.css';
        $GLOBALS['TL_CSS'][] = 'system/themes/' . $this->getTheme() . '/basic.css';
        $GLOBALS['TL_CSS'][] = 'system/themes/' . $this->getTheme() . '/popup.css';
        $GLOBALS['TL_CSS'][] = 'system/modules/syncCto/html/css/compare.css';

        // Set javascript
        $GLOBALS['TL_JAVASCRIPT'][] = TL_PLUGINS_URL . 'plugins/mootools/' . MOOTOOLS_CORE . '/mootools-core.js';
        $GLOBALS['TL_JAVASCRIPT'][] = 'contao/contao.js';

        if (version_compare(VERSION, '2.11', '>='))
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
        $this->Template->id   = $this->intClientID;
        $this->Template->step = $this->mixStep;

        // Output template
        $this->popupTemplate->content = $this->Template->parse();
        $this->popupTemplate->output();
    }

    // Helper functions --------------------------------------------------------

    /**
     * Load temporary filelist 
     */
    protected function loadTempLists()
    {
        $objFileList = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "syncfilelist-ID-" . $this->intClientID . ".txt"));
        $strContent  = $objFileList->getContent();
        if (strlen($strContent) == 0)
        {
            $this->arrListFile = array();
        }
        else
        {
            $this->arrListFile = deserialize($strContent);
        }
        $objFileList->close();

        $objCompareList = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "synccomparelist-ID-" . $this->intClientID . ".txt"));
        $strContent     = $objCompareList->getContent();
        if (strlen($strContent) == 0)
        {
            $this->arrListCompare = array();
        }
        else
        {
            $this->arrListCompare = deserialize($strContent);
        }

        $objCompareList->close();
    }

    /**
     * Save temporary filelist 
     */
    protected function saveTempLists()
    {
        $objFileList = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "syncfilelist-ID-" . $this->intClientID . ".txt"));
        $objFileList->write(serialize($this->arrListFile));
        $objFileList->close();

        $objCompareList = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "synccomparelist-ID-" . $this->intClientID . ".txt"));
        $objCompareList->write(serialize($this->arrListCompare));
        $objCompareList->close();
    }

    /**
     * Load information from client
     */
    protected function loadClientInformation()
    {
        $this->arrClientInformation = $this->Session->get("syncCto_ClientInformation_" . $this->intClientID);

        if (!is_array($this->arrClientInformation))
        {
            $this->arrClientInformation = array();
        }
    }

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
            $this->mixStep = self::STEP_ERROR_FILES;
            return;
        }
        
        // Load information 
        $this->loadClientInformation();

        // Get next step
        if (strlen($this->Input->get("step")) != 0)
        {
            $this->mixStep = $this->Input->get("step");
        }
        else
        {
            $this->mixStep = self::STEP_SHOW_FILES;
        }
    }

    /**
     * Sort function
     * @param type $a
     * @param type $b
     * @return type 
     */
    public function sort($a, $b)
    {
        if ($a["state"] == $b["state"])
        {
            return 0;
        }

        return ($a["state"] < $b["state"]) ? -1 : 1;
    }

}

/**
 * Instantiate controller
 */
$objPopup = new PopupSyncFiles();
$objPopup->run();
?>