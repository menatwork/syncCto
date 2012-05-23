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
require_once('../../initialize.php');


/**
 * Class SyncCtoPopup
 */
class SyncCtoPopup extends Backend
{

    // Vars
    protected $intClientID;    
    
    // Helper Classes
    protected $objSyncCtoHelper;
    
    // Temp data
    protected $arrListFile;
    protected $arrListCompare;    
    
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
    }

    /**
     * Load the template list and go through the steps
     */
    public function run()
    {                
        if ($this->intStep == 0)
        {
            $this->intStep = 1;
            
            // Get Client id
            if (strlen($this->Input->get("id")) != 0)
            {
                $this->intClientID = intval($this->Input->get("id"));
            }
            else
            {
                $_SESSION["TL_ERROR"] = array($GLOBALS['TL_LANG']['ERR']['call_directly']);
                $this->redirect("contao/main.php?do=synccto_clients");
            }
        }
        else
        {
            $this->loadStepPool();
        }
        
        $this->loadTempLists();
        
        switch ($this->intStep)
        {
            // Show list with files and count
            case 1:
                $this->step1();
                break;
            
            // Search big files
            case 2:
                $this->step2();
                break;
            
            // Close popup and run next syncCto step
            case 'close':
                $this->stepClose();
                break;
        }
        
        $this->saveTempLists();
        $this->saveStepPool();
    }
    
    /**
     * Show list with files and count
     * 
     * @return type 
     */
    protected function step1()
    {
       // Del and submit Function
        $arrDel = $_POST;

        if (key_exists("delete", $arrDel))
        {
            foreach ($arrDel as $key => $value)
            {
                unset($this->arrListCompare[$value]);
            }
        }
        else if (key_exists("transfer", $arrDel))
        {            
            foreach ($this->arrListCompare as $key => $value)
            {
                if ($value["split"] == true)
                {            
                    $this->intStep++;
                    return;
                }
            }
            $this->intStep = 'close';
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
                
        $this->Template = new BackendTemplate('be_syncCto_filelist');        
        $this->Template->filelist = $this->arrListCompare;
        $this->Template->id = $this->intClientID;
        $this->Template->step = $this->intStep;
        $this->Template->totalsizeNew = $intTotalSizeNew;
        $this->Template->totalsizeDel = $intTotalSizeDel;
        $this->Template->totalsizeChange = $intTotalSizeChange;
        $this->Template->direction = "To";
        $this->Template->compare_complex = false;
        $this->output();        
    }
    
    /**
     * Search big files 
     */
    public function step2()
    {
        // build list with big files
        $arrTempList = array();
        $intTotalSizeNew    = 0;
        $intTotalSizeDel    = 0;
        $intTotalSizeChange = 0;

        // Del Function
        $arrDel = $_POST;

        if (is_array($arrDel) && key_exists("delete", $arrDel))
        {
            foreach ($arrDel as $key => $value)
            {
                if (key_exists($value, $this->arrListCompare))
                {
                    unset($this->arrListCompare[$value]);
                }
            }
        }
        else if (is_array($arrDel) && key_exists("transfer", $arrDel))
        {
            $this->intStep = 'close';
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

        // Skip if we have zero
        if ($intCountSplit == 0)
        {
            $this->intStep = 'close';
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

        uasort($arrTempList, 'syncCtoModelClientCMP');

        $objTemp = new BackendTemplate("be_syncCto_filelist");
        $objTemp->filelist = $arrTempList;
        $objTemp->id = $this->intClientID;
        $objTemp->step = $this->intStep;
        $objTemp->totalsizeNew = $intTotalSizeNew;
        $objTemp->totalsizeChange = $intTotalSizeChange;
        $objTemp->totalsizeDel = $intTotalSizeDel;
        $objTemp->direction = "To";
        $objTemp->compare_complex = true;
        $this->output();
    }
    
    public function stepClose()
    {
        $objTemp = new BackendTemplate("be_syncCto_filelist_close");
        $this->output();        
    }

    public function output()
    {
        $GLOBALS['TL_CSS'] = array(
            'html/css/filelist.css',
            '../../themes/default/basic.css',
            '../../themes/default/popup.css'
        );
        
        $GLOBALS['TL_JAVASCRIPT'] = array(
            '../../../plugins/mootools/1.3.2/mootools-core.js',
            'html/js/htmltable.js',
            'html/js/filelist_src.js'
        );        
        
        $this->Template->output();
    }
    
    protected function initTempLists()
    {
        // Load Files
        $objFileList = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "syncfilelist-ID-" . $this->intClientID . ".txt"));
        $objFileList->delete();
        $objFileList->close();

        $objCompareList = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "synccomparelist-ID-" . $this->intClientID . ".txt"));
        $objCompareList->delete();
        $objCompareList->close();
    }

    protected function loadTempLists()
    {
        // Load Files
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

    protected function saveTempLists()
    {
        $objFileList = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "syncfilelist-ID-" . $this->intClientID . ".txt"));
        $objFileList->write(serialize($this->arrListFile));
        $objFileList->close();

        $objCompareList = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "synccomparelist-ID-" . $this->intClientID . ".txt"));
        $objCompareList->write(serialize($this->arrListCompare));
        $objCompareList->close();
    }
    
    protected function loadStepPool()
    {
        $arrStepPool = $this->Session->get("syncCto_" . $this->intClientID . "_PopupStepPool");

        if ($arrStepPool == false || !is_array($arrStepPool))
        {
            $arrStepPool = array();
        }

        $this->arrStepPool = $arrStepPool;
        $this->intClientID = $arrStepPool['id'];
        $this->intStep = $arrStepPool['step'];
    }

    protected function saveStepPool()
    {
        $arrStepPool = $this->arrStepPool;
        $arrStepPool['id'] = $this->intClientID;
        $arrStepPool['step'] = $this->intStep;
        
        $this->Session->set("syncCto_" . $this->intClientID . "_PopupStepPool", $this->arrStepPool);
    }

}

/**
 * Instantiate controller
 */
$objPopup = new SyncCtoPopup();
$objPopup->run();
?>