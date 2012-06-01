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

    // defines

    const STEP_NORMAL_FILES = 'nf';
    const STEP_BIG_FILES = 'bf';
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
        if ($this->mixStep == self::STEP_NORMAL_FILES)
        {
            $this->loadTempLists();
            $this->showNormalFiles();
            $this->saveTempLists();
            unset($_POST);
        }

        if ($this->mixStep == self::STEP_BIG_FILES)
        {
            $this->loadTempLists();
            $this->showBigFiles();
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

    /**
     * Show normal files
     * 
     * @return 
     */
    protected function showNormalFiles()
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
            foreach ($this->arrListCompare as $key => $value)
            {
                if ($value["split"] == true)
                {
                    $this->mixStep = self::STEP_BIG_FILES;
                    return;
                }
            }
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
        $intCountNeed = 0;
        $intCountIgnored = 0;
        $intCountDelete = 0;

        $intTotalSizeNew = 0;
        $intTotalSizeDel = 0;
        $intTotalSizeChange = 0;

        // Count files
        foreach ($this->arrListCompare as $key => $value)
        {
            switch ($value['state'])
            {
                case SyncCtoEnum::FILESTATE_MISSING:
                    $intCountMissing++;
                    $intTotalSizeNew += $value["size"];
                    break;

                case SyncCtoEnum::FILESTATE_NEED:
                    $intCountNeed++;
                    $intTotalSizeChange += $value["size"];
                    break;

                case SyncCtoEnum::FILESTATE_DELETE:
                    $intCountDelete++;
                    $intTotalSizeDel += $value["size"];
                    break;

                case SyncCtoEnum::FILESTATE_BOMBASTIC_BIG:
                case SyncCtoEnum::FILESTATE_TOO_BIG_NEED:
                case SyncCtoEnum::FILESTATE_TOO_BIG_MISSING:
                case SyncCtoEnum::FILESTATE_TOO_BIG_DELETE :
                    $intCountIgnored++;
                    break;
            }
        }

        $arrTempList = $this->arrListCompare;
        uasort($arrTempList, array($this, 'sort'));
        
        // Set template
        $this->Template = new BackendTemplate('be_syncCto_files');
        $this->Template->headline = $GLOBALS['TL_LANG']['MSC']['comparelist'];
        $this->Template->filelist = $arrTempList;
        $this->Template->totalsizeNew = $intTotalSizeNew;
        $this->Template->totalsizeDel = $intTotalSizeDel;
        $this->Template->totalsizeChange = $intTotalSizeChange;
        $this->Template->compare_complex = FALSE;
        $this->Template->close = FALSE;
        $this->Template->error = FALSE;
    }

    /**
     * Search big files 
     */
    public function showBigFiles()
    {
        $arrTempList = array();
        $intTotalSizeNew = 0;
        $intTotalSizeDel = 0;
        $intTotalSizeChange = 0;

        // Delete functinality       
        if (is_array($_POST) && key_exists("delete", $_POST))
        {
            foreach ($_POST as $key => $value)
            {
                if (key_exists($value, $this->arrListCompare))
                {
                    unset($this->arrListCompare[$value]);
                }
            }
        }

        // Close functinality
        else if (is_array($_POST) && key_exists("transfer", $_POST))
        {
            $this->mixStep = self::STEP_CLOSE_FILES;
            return;
        }

        // Count split files
        $intCountSplit = 0;
        foreach ($this->arrListCompare as $key => $value)
        {
            if ($value["split"] == true)
            {
                $intCountSplit++;
            }
        }

        // Check if big filelist is empty and close
        if ($intCountSplit == 0)
        {
            $this->mixStep = self::STEP_CLOSE_FILES;
            return;
        }

        // Build list
        foreach ($this->arrListCompare as $key => $value)
        {
            if ($value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_DELETE ||
                    $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_MISSING ||
                    $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_NEED ||
                    $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_SAME ||
                    $value["state"] == SyncCtoEnum::FILESTATE_BOMBASTIC_BIG)
            {
                $arrTempList[$key] = $this->arrListCompare[$key];
            }
            else if ($value["split"] == 1)
            {
                $arrTempList[$key] = $this->arrListCompare[$key];
                $intTotalSizeNew += $value["size"];
            }
        }

        uasort($arrTempList, array($this, 'sort'));

        // Set template
        $this->Template = new BackendTemplate("be_syncCto_files");
        $this->Template->headline = $GLOBALS['TL_LANG']['MSC']['totalsize'];
        $this->Template->filelist = $arrTempList;
        $this->Template->totalsizeNew = $intTotalSizeNew;
        $this->Template->totalsizeChange = $intTotalSizeChange;
        $this->Template->totalsizeDel = $intTotalSizeDel;
        $this->Template->compare_complex = FALSE;
        $this->Template->close = FALSE;
        $this->Template->error = FALSE;
    }

    /**
     * Close popup and go throug next syncCto step
     */
    public function showClose()
    {
        $this->Template = new BackendTemplate("be_syncCto_files");
        $this->Template->headline = $GLOBALS['TL_LANG']['MSC']['backBT'];
        $this->Template->close = TRUE;
        $this->Template->error = FALSE;
    }

    /**
     * Show errors
     */
    public function showError()
    {
        $this->Template = new BackendTemplate("be_syncCto_files");
        $this->Template->headline = $GLOBALS['TL_LANG']['MSC']['error'];
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
        $GLOBALS['TL_CSS'][] = 'system/themes/' . $this->getTheme() . '/main.css';
        $GLOBALS['TL_CSS'][] = 'system/themes/' . $this->getTheme() . '/basic.css';
        $GLOBALS['TL_CSS'][] = 'system/themes/' . $this->getTheme() . '/popup.css';
        $GLOBALS['TL_CSS'][] = 'system/modules/syncCto/html/css/compare.css';

        // Set javascript
        $GLOBALS['TL_JAVASCRIPT'][] = TL_PLUGINS_URL . 'plugins/mootools/' . MOOTOOLS_CORE . '/mootools-core.js';
        $GLOBALS['TL_JAVASCRIPT'][] = 'contao/contao.js';
        
        if (version_compare(VERSION, '2.11', '=='))
        {
        $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/syncCto/html/js/htmltable.js';
        }
        
        $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/syncCto/html/js/compare.js';

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
     * Load temporary filelist 
     */
    protected function loadTempLists()
    {
        $objFileList = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "syncfilelist-ID-" . $this->intClientID . ".txt"));
        $strContent = $objFileList->getContent();
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
        $strContent = $objCompareList->getContent();
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

        // Get next step
        if (strlen($this->Input->get("step")) != 0)
        {
            $this->mixStep = $this->Input->get("step");
        }
        else
        {
            $this->mixStep = self::STEP_NORMAL_FILES;
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