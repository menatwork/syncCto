<?php

if (!defined('TL_ROOT'))
    die('You cannot access this file directly!');

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
 * Class for client interaction
 */
class SyncCtoModuleClient extends BackendModule
{
    /* -------------------------------------------------------------------------
     * Variablen
     */

    // Vars     
    protected $strTemplate = 'be_syncCto_steps';
    protected $objTemplateContent;
    protected $intClientID;
    // Helper Classes
    protected $objSyncCtoCommunicationClient;
    protected $objSyncCtoDatabase;
    protected $objSyncCtoFiles;
    protected $objSyncCtoHelper;
    protected $objSyncCtoMeasurement;
    // Content data
    protected $booError;
    protected $booAbort;
    protected $booFinished;
    protected $booRefresh;
    protected $strError;
    protected $strUrl;
    protected $strGoBack;
    protected $strHeadline;
    protected $strInformation;
    protected $intStep;
    protected $floStart;
    // Temp data
    protected $arrListFile;
    protected $arrListCompare;
    // Config
    protected $arrSyncSettings;
    protected $arrClientInformation;

    /**
     * @var ContentData 
     */
    protected $objData;

    /**
     * @var StepPool
     */
    protected $objStepPool;

    /* -------------------------------------------------------------------------
     * Core Functions
     */

    /**
     * Constructor
     * 
     * @param DataContainer $objDc 
     */
    public function __construct(DataContainer $objDc = null)
    {
        parent::__construct($objDc);

        // Load Helper
        $this->objSyncCtoDatabase = SyncCtoDatabase::getInstance();
        $this->objSyncCtoFiles = SyncCtoFiles::getInstance();
        $this->objSyncCtoCommunicationClient = SyncCtoCommunicationClient::getInstance();
        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();

        // Load Language 
        $this->loadLanguageFile("tl_syncCto_steps");

        // Import
        $this->import("Backenduser", "User");
    }

    /**
     * Generate page
     */
    protected function compile()
    {
        // Check if start is set
        if ($this->Input->get("act") != "start")
        {
            $_SESSION["TL_ERROR"] = array($GLOBALS['TL_LANG']['ERR']['call_directly']);
            $this->redirect("contao/main.php?do=synccto_clients");
        }

        // Get step
        if ($this->Input->get("step") == "" || $this->Input->get("step") == null)
        {
            $this->intStep = 0;
        }
        else
        {
            $this->intStep = intval($this->Input->get("step"));
        }

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

        // Set client for communication
        try
        {
            $arrClientInformations = $this->objSyncCtoCommunicationClient->setClientBy(intval($this->Input->get("id")));
            $this->Template->clientName = $arrClientInformations["title"];
        }
        catch (Exception $exc)
        {
            $_SESSION["TL_ERROR"] = array($GLOBALS['TL_LANG']['ERR']['client_set']);
            $this->redirect("contao/main.php?do=synccto_clients");
        }

        // Set template
        $this->Template->showControl = true;
        $this->Template->tryAgainLink = $this->Environment->requestUri;
        $this->Template->abortLink = $this->Environment->requestUri . "&abort=true";

        // Load content from session
        if ($this->intStep != 0)
        {
            $this->loadContenData();
        }

        // Load settings from dca
        $this->loadSyncSettings();
        $this->loadClientInformation();

        if ($this->Input->get("abort") == "true")
        {
            // So abort page
            $this->pageSyncAbort();
            // Save content in session
            $this->saveContentData();
            return;
        }

        // Which table is in use
        switch ($this->Input->get("table"))
        {
            case "tl_syncCto_clients_syncTo":
                $this->pageSyncTo();
                break;

            case "tl_syncCto_clients_syncFrom":
                $this->pageSyncFrom();
                break;

            default :
                $_SESSION["TL_ERROR"][] = $GLOBALS['TL_LANG']['ERR']['unknown_function'];
                $this->redirect("contao/main.php?do=synccto_clients");
                break;
        }

        // Save content in session
        $this->saveContentData();
        $this->saveClientInformation();

        // Set Vars for the template
        $this->setTemplateVars();
    }

    /* -------------------------------------------------------------------------
     * Helper function for session/tempfiles etc.
     */

    protected function setTemplateVars()
    {
        // Set Tempalte
        $this->Template->goBack = $this->strGoBack;
        $this->Template->data = $this->objData->getArrValues();
        $this->Template->step = $this->intStep;
        $this->Template->subStep = $this->objStepPool->step;
        $this->Template->error = $this->booError;
        $this->Template->error_msg = $this->strError;
        $this->Template->refresh = $this->booRefresh;
        $this->Template->url = $this->strUrl;
        $this->Template->start = $this->floStart;
        $this->Template->headline = $this->strHeadline;
        $this->Template->information = $this->strInformation;
        $this->Template->finished = $this->booFinished;
    }

    /**
     * Save the current state of the page/sychronization 
     */
    protected function saveContentData()
    {
        $arrContenData = array(
            "error" => $this->booError,
            "error_msg" => $this->strError,
            "refresh" => $this->booRefresh,
            "finished" => $this->booFinished,
            "step" => $this->intStep,
            "url" => $this->strUrl,
            "goBack" => $this->strGoBack,
            "start" => $this->floStart,
            "headline" => $this->strHeadline,
            "information" => $this->strInformation,
            "data" => $this->objData->getArrValues(),
            "abort" => $this->booAbort,
        );

        $this->Session->set("syncCto_Content", $arrContenData);
    }

    /**
     * Load the current state of the page/synchronization 
     */
    protected function loadContenData()
    {
        $arrContenData = $this->Session->get("syncCto_Content");

        if (is_array($arrContenData) && count($arrContenData) != 0)
        {
            $this->booError = $arrContenData["error"];
            $this->booAbort = $arrContenData["abort"];
            $this->booFinished = $arrContenData["finished"];
            $this->booRefresh = $arrContenData["refresh"];
            $this->strError = $arrContenData["error_msg"];
            $this->strUrl = $arrContenData["url"];
            $this->strGoBack = $arrContenData["goBack"];
            $this->strHeadline = $arrContenData["headline"];
            $this->strInformation = $arrContenData["information"];
            $this->intStep = $arrContenData["step"];
            $this->floStart = $arrContenData["start"];
            $this->objData = new ContentData($arrContenData["data"], $this->intStep);
        }
        else
        {
            $this->booError = false;
            $this->booAbort = false;
            $this->booFinished = false;
            $this->booRefresh = false;
            $this->strError = "";
            $this->strUrl = "";
            $this->strGoBack = "";
            $this->strHeadline = "";
            $this->strInformation = "";
            $this->intStep = 0;
            $this->floStart = 0;
            $this->objData = new ContentData(array(), $this->intStep);
        }
    }

    protected function loadStepPool()
    {
        $arrStepPool = $this->Session->get("syncCto_" . $this->intClientID . "_StepPool" . $this->intStep);

        if ($arrStepPool == false || !is_array($arrStepPool))
        {
            $arrStepPool = array();
        }

        $this->objStepPool = new StepPool($arrStepPool, $this->intStep);
    }

    protected function saveStepPool()
    {
        $this->Session->set("syncCto_" . $this->intClientID . "_StepPool" . $this->objStepPool->getIntStepID(), $this->objStepPool->getArrValues());
    }

    protected function resetStepPool()
    {
        $this->Session->set("syncCto_" . $this->intClientID . "_StepPool" . $this->objStepPool->getIntStepID(), FALSE);
    }

    protected function resetStepPoolByID($arrID)
    {
        foreach ($arrID as $value)
        {
            $this->Session->set("syncCto_" . $this->intClientID . "_StepPool" . $value, FALSE);
        }
    }

    protected function initTempLists()
    {
        // Load Files
        $objFileList = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "syncfilelistTo-ID-" . $this->intClientID . ".txt"));
        $objFileList->delete();
        $objFileList->close();

        $objCompareList = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "synccomparelistTo-ID-" . $this->intClientID . ".txt"));
        $objCompareList->delete();
        $objCompareList->close();
    }

    protected function loadTempLists()
    {
        // Load Files
        $objFileList = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "syncfilelistTo-ID-" . $this->intClientID . ".txt"));

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

        $objCompareList = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "synccomparelistTo-ID-" . $this->intClientID . ".txt"));

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

    protected function saveTempLists()
    {
        $objFileList = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "syncfilelistTo-ID-" . $this->intClientID . ".txt"));
        $objFileList->write(serialize($this->arrListFile));
        $objFileList->close();

        $objCompareList = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "synccomparelistTo-ID-" . $this->intClientID . ".txt"));
        $objCompareList->write(serialize($this->arrListCompare));
        $objCompareList->close();
    }

    protected function loadSyncSettings()
    {
        $this->arrSyncSettings = $this->Session->get("syncCto_SyncSettings_" . $this->intClientID);

        if (!is_array($this->arrSyncSettings))
        {
            $this->arrSyncSettings = array();
        }
    }

    protected function loadClientInformation()
    {
        $this->arrClientInformation = $this->Session->get("syncCto_ClientInformation_" . $this->intClientID);

        if (!is_array($this->arrClientInformation))
        {
            $this->arrClientInformation = array();
        }
    }

    protected function saveClientInformation()
    {
        $this->Session->set("syncCto_ClientInformation_" . $this->intClientID, $this->arrClientInformation);
    }

    protected function resetClientInformation()
    {
        $this->Session->set("syncCto_ClientInformation_" . $this->intClientID, FALSE);
    }

    /* -------------------------------------------------------------------------
     * Helper function for sync settings
     */

    protected function checkSyncFileList()
    {
        if (!key_exists("syncCto_Type", $this->arrSyncSettings) || count($this->arrSyncSettings["syncCto_Type"]) == 0)
        {
            return false;
        }

        $arrCheck = array(
            'core_change',
            'core_delete',
            'user_change',
            'user_delete'
        );

        foreach ($arrCheck as $value)
        {
            if (in_array($value, $this->arrSyncSettings["syncCto_Type"]))
            {
                return true;
            }
        }
    }

    protected function checkSyncDatabase()
    {
        if (!key_exists('syncCto_SyncTables', $this->arrSyncSettings))
        {
            return false;
        }

        if (count($this->arrSyncSettings['syncCto_SyncTables']) == 0)
        {
            return false;
        }

        return true;
    }

    /* -------------------------------------------------------------------------
     * Functions for comunication
     */

    /**
     * Setup for page syncTo
     */
    private function pageSyncTo()
    {
        // Init | Set Step to 1
        if ($this->intStep == 0)
        {
            // Init content
            $this->booError = false;
            $this->booAbort = false;
            $this->booFinished = false;
            $this->strError = "";
            $this->booRefresh = true;
            $this->strUrl = "contao/main.php?do=synccto_clients&amp;table=tl_syncCto_clients_syncTo&amp;act=start&amp;id=" . $this->intClientID;
            $this->strGoBack = $this->Environment->base . "contao/main.php?do=synccto_clients";
            $this->strHeadline = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['edit'];
            $this->strInformation = "";
            $this->intStep = 1;
            $this->floStart = microtime(true);
            $this->objData = new ContentData(array(), $this->intStep);

            // Init tmep files
            $this->initTempLists();

            // Update last sync
            $this->Database->prepare("UPDATE `tl_synccto_clients` %s WHERE `tl_synccto_clients`.`id` = ?")
                    ->set(array("syncTo_user" => $this->User->id, "syncTo_tstamp" => time()))
                    ->execute($this->intClientID);

            // Write log
            $this->log(vsprintf("Start synchronization client ID %s.", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "INFO");

            // Reset some Sessions
            $this->resetStepPoolByID(array(1, 2, 3, 4, 5, 6));
            $this->resetClientInformation();
            
            $this->Session->set("SyncCto_FileLock_ID" . $this->intClientID, array("lock" => false));
        }

        // Check if we have to do the current step
        switch ($this->intStep)
        {
            // Nothing to do
            case 1:
                break;

            // Check if we have files
            case 2:
                if (!$this->checkSyncFileList())
                {
                    $this->intStep++;
                    $this->objData->nextStep();
                }
                else
                {
                    break;
                }

            // Check if we have files and some big ones
            case 3:
                if (!$this->checkSyncFileList())
                {
                    $this->intStep++;
                    $this->objData->nextStep();
                }
                else
                {
                    break;
                }

            // Check if some tables are choosen
            case 4:
                if (!$this->checkSyncDatabase())
                {
                    $this->intStep++;
                    $this->objData->nextStep();
                }
                else
                {
                    break;
                }
        }

        // Load step pool for current step
        $this->loadStepPool();

        // Load Step
        switch ($this->intStep)
        {
            // Init|Check
            case 1:
                $this->pageSyncToShowStep1();
                break;

            // Filelists
            case 2:
                $this->loadTempLists();
                $this->pageSyncToShowStep2();
                $this->saveTempLists();
                break;

            // File send 
            case 3:
                $this->loadTempLists();
                $this->pageSyncToShowStep3();
                $this->saveTempLists();
                break;

            // Database
            case 4:
                $this->pageSyncToShowStep4();
                break;

            // Import Files | Import Config | etc.
            case 5:
                $this->loadTempLists();
                $this->pageSyncToShowStep5();
                break;

            default:
                $_SESSION["TL_ERROR"] = array("Unbekannter Schritt für Backup.");
                $this->redirect("contao/main.php?do=synccto_clients");
                break;
        }

        // Save step pool for current step
        $this->saveStepPool();
    }

    /**
     * Setup for page syncFrom
     * @todo
     */
    private function pageSyncFrom()
    {
        // Build Step
        if ($this->Input->get("step") == "" || $this->Input->get("step") == null)
        {
            $this->intStep = 1;
        }
        else
        {
            $this->intStep = intval($this->Input->get("step"));
        }

        // Set client for communication
        try
        {
            $this->objSyncCtoCommunicationClient->setClientBy(intval($this->Input->get("id")));
        }
        catch (Exception $exc)
        {
            $_SESSION["TL_ERROR"] = array($GLOBALS['TL_LANG']['ERR']['client_set']);
            $this->redirect("contao/main.php?do=synccto_clients");
        }

        if ($this->Input->get("abort") == "true")
        {
            $this->pageSyncAbort();
            return;
        }

        // Set up temp file for filetransmission
        if ($this->intStep != 1)
        {
            $intID = $this->Input->get("id");

            // Load Files
            $objFileList = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "syncfilelistFrom-ID-$intID.txt"));

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

            $objCompareList = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "synccomparelistFrom-ID-$intID.txt"));

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

        // Load Step
        switch ($this->intStep)
        {
            case 1:
                $this->Database->prepare("UPDATE `tl_synccto_clients` %s WHERE `tl_synccto_clients`.`id` = ?")
                        ->set(array("syncFrom_user" => $this->User->id, "syncFrom_tstamp" => time()))
                        ->execute($this->Input->get("id"));

                $this->pageSyncFromShowStep1();
                break;

            case 2:
                $this->pageSyncFromShowStep2();
                break;

            case 3:
                $this->pageSyncFromShowStep3();
                break;

            case 4:
                $this->pageSyncFromShowStep4();
                break;

            case 5:
                $this->pageSyncFromShowStep5();
                break;

            default:
                $_SESSION["TL_ERROR"] = array("Unbekannter Schritt für Backup.");
                $this->redirect("contao/main.php?do=synccto_clients");
                break;
        }

        // Save informatione 
        if ($this->intStep != 1)
        {
            $intID = $this->Input->get("id");

            $objFileList = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "syncfilelistFrom-ID-$intID.txt"));
            $objFileList->write(serialize($this->arrListFile));
            $objFileList->close();

            $objCompareList = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "synccomparelistFrom-ID-$intID.txt"));
            $objCompareList->write(serialize($this->arrListCompare));
            $objCompareList->close();
        }
    }

    /**
     * Abort function
     * @todo
     * @deprecated
     */
    private function pageSyncAbort()
    {
        // Load content
        $arrContenData = $this->Session->get("syncCto_Content");

        // Set content back to normale mode
        $arrContenData["error"]     = false;
        $arrContenData["error_msg"] = "";

        if ($arrContenData["abort"] == false)
        {
            try
            {
                // Reste Session
                $this->Session->set("syncCto_StepPool1", FALSE);
                $this->Session->set("syncCto_StepPool2", FALSE);
                $this->Session->set("syncCto_StepPool3", FALSE);
                $this->Session->set("syncCto_StepPool4", FALSE);
                $this->Session->set("syncCto_StepPool5", FALSE);
                $this->Session->set("syncCto_StepPool6", FALSE);

                $this->Session->set("syncCto_PurgeData", FALSE);
                $this->Session->set("syncCto_SyncTables", FALSE);
                $this->Session->set("syncCto_Filelist", FALSE);
                $this->Session->set("syncCto_Typ", 99);

                // Reste server and client
                $this->objSyncCtoFiles->purgeTemp();
                $this->objSyncCtoCommunicationClient->purgeTemp();
            }
            catch (Exception $exc)
            {
                // Nothing to do 
            }

            try
            {
                $this->objSyncCtoCommunicationClient->stopConnection();
            }
            catch (Exception $exc)
            {
                // Nothing to do 
            }

            try
            {
                $this->objSyncCtoCommunicationClient->referrerEnable();
            }
            catch (Exception $exc)
            {
                // Nothing to do 
            }

            // Set last to skipped        
            $arrKeys                                                       = array_keys($arrContenData["data"]);
            $arrContenData["data"][$arrKeys[count($arrKeys) - 1]]["state"] = $GLOBALS['TL_LANG']['MSC']['skipped'];
            $arrContenData["data"][$arrKeys[count($arrKeys) - 1]]["html"]  = "";

            // Set Abort information 
            $arrContenData["data"][99]["title"]       = $GLOBALS['TL_LANG']['MSC']['abort'];
            $arrContenData["data"][99]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']['abort'];
            $arrContenData["data"][99]["state"]       = "";
        }

        $arrContenData["abort"] == true;

        $this->Template->goBack = $this->script . $arrContenData["goBack"];
        $this->Template->data = $arrContenData["data"];
        $this->Template->step = 99;
        $this->Template->error = false;
        $this->Template->error_msg = "";
        $this->Template->refresh = false;
        $this->Template->url = $arrContenData["url"];
        $this->Template->start = $arrContenData["start"];
        $this->Template->headline = $arrContenData["headline"];
        $this->Template->information = $arrContenData["information"];
        $this->Template->finished = $arrContenData["finished"];
        $this->Template->showControl = FALSE;

        $this->Session->set("syncCto_Content", $arrContenData);

        return;
    }

    /* -------------------------------------------------------------------------
     * Start SyncCto syncTo
     */

    /**
     * Start the connection and save some parameter to session
     */
    private function pageSyncToShowStep1()
    {
        // Init
        if ($this->objStepPool->step == null)
        {
            $this->objStepPool->step = 1;
        }

        // Set content back to normale mode
        $this->strError = "";
        $this->booError = false;
        $this->objData->setState($GLOBALS['TL_LANG']['MSC']['progress']);

        /* ---------------------------------------------------------------------
         * Run page
         */

        try
        {
            switch ($this->objStepPool->step)
            {
                /**
                 * Show step
                 */
                case 1:
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_1']);
                    $this->objData->setState($GLOBALS['TL_LANG']['MSC']['progress']);

                    $this->objStepPool->step++;
                    break;

                /**
                 * Start connection
                 */
                case 2:
                    $this->objSyncCtoCommunicationClient->startConnection();

                    $this->objStepPool->step++;
                    break;

                /**
                 * Referer check deactivate
                 */
                case 3:
                    if (!$this->objSyncCtoCommunicationClient->referrerDisable())
                    {
                        $this->objData->setState($GLOBALS['TL_LANG']['MSC']['error']);
                        $this->booError = true;
                        $this->strError = $GLOBALS['TL_LANG']['tl_syncCto_sync']['error_step_1']['referer'];

                        break;
                    }

                    $this->objStepPool->step++;
                    break;

                /**
                 * Check version
                 */
                case 4:
                    $strVersion = $this->objSyncCtoCommunicationClient->getVersionSyncCto();
                    $this->arrClientInformation["version_SyncCto"] = $strVersion;

                    if (!version_compare($strVersion, $GLOBALS['SYC_VERSION'], "="))
                    {
                        $this->log(vsprintf("Not the same version from syncCto on synchronization client ID %s. Serverversion: %s. Clientversion: %s", array($this->Input->get("id"), $GLOBALS['SYC_VERSION'], $strVersion)), __CLASS__ . " " . __FUNCTION__, "INFO");

                        $this->objData->setState($GLOBALS['TL_LANG']['MSC']['error']);
                        $this->booError = true;
                        $this->strError = vsprintf($GLOBALS['TL_LANG']['ERR']['version'], array("syncCto", $GLOBALS['SYC_VERSION'], $strVersion));
                        break;
                    }

                    $strVersion = $this->objSyncCtoCommunicationClient->getVersionContao();
                    $this->arrClientInformation["version_Contao"] = $strVersion;

                    if (!version_compare($strVersion, VERSION, "="))
                    {
                        $this->log(vsprintf("Not the same version from contao on synchronization client ID %s. Serverversion: %s. Clientversion: %s", array($this->Input->get("id"), $GLOBALS['SYC_VERSION'], $strVersion)), __CLASS__ . " " . __FUNCTION__, "INFO");

                        $this->objData->setState($GLOBALS['TL_LANG']['MSC']['error']);
                        $this->booError = true;
                        $this->strError = vsprintf($GLOBALS['TL_LANG']['ERR']['version'], array("Contao", VERSION, $strVersion));
                        break;
                    }

                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_2']);

                    $this->objStepPool->step++;
                    break;

                /**
                 * Clear client and server temp folder  
                 */
                case 5:
                    $this->objSyncCtoCommunicationClient->purgeTemp();
                    $this->objSyncCtoFiles->purgeTemp();

                    // Current step is okay.
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_1']);

                    $this->objStepPool->step++;

                    break;

                /**
                 * Load parameter from client
                 */
                case 6:
                    // Load the folder settings. Temp Folder | Backup Folder | Log Folder etc.
                    $arrFolders = $this->objSyncCtoCommunicationClient->getPathList();
                    $this->arrClientInformation["folders"] = $arrFolders;

                    // Get parameter for upload and co
                    $arrClientParameter = $this->objSyncCtoCommunicationClient->getClientParameter();

                    $this->arrClientInformation["upload_Parameter"] = $arrClientParameter;

                    // Check if everthing is okay
                    if ($arrClientParameter['file_uploads'] != 1)
                    {
                        $this->objData->setState($GLOBALS['TL_LANG']['MSC']['error']);
                        $this->booError = true;
                        $this->strError = $GLOBALS['TL_LANG']['tl_syncCto_sync']['error_step_3']['upload_ini'];

                        break;
                    }

                    $intClientUploadLimit = intval(str_replace("M", "000000", $arrClientParameter['upload_max_filesize']));
                    $intClientMemoryLimit = intval(str_replace("M", "000000", $arrClientParameter['memory_limit']));
                    $intClientPostLimit   = intval(str_replace("M", "000000", $arrClientParameter['post_max_size']));
                    $intLocalMemoryLimit  = intval(str_replace("M", "000000", ini_get('memory_limit')));

                    // Check if memory limit on server and client is enough for upload  
                    $intLimit = min($intClientUploadLimit, $intClientMemoryLimit, $intClientPostLimit, $intLocalMemoryLimit);

                    // Limit
                    if ($intLimit > 1073741824)
                    { // 1GB
                        $intPercent = 10;
                    }
                    else if ($intLimit > 524288000)
                    { // 500MB
                        $intPercent = 10;
                    }
                    else if ($intLimit > 209715200)
                    { // 200MB
                        $intPercent = 10;
                    }
                    else
                    {
                        $intPercent = 30;
                    }

                    $intLimit = $intLimit / 100 * $intPercent;

                    $this->arrClientInformation["upload_sizeLimit"] = $intLimit;
                    $this->arrClientInformation["upload_sizePercent"] = $intPercent;

                    $this->objData->setState($GLOBALS['TL_LANG']['MSC']['ok']);
                    $this->intStep++;

                    break;
            }
        }
        catch (Exception $exc)
        {
            $this->log(vsprintf("Error on synchronization client ID %s", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "ERROR");

            $this->booError = true;
            $this->strError = $exc->getMessage();

            $this->objData->setState($GLOBALS['TL_LANG']['MSC']['error']);
        }
    }

    /**
     * Build checksum list and ask client
     */
    private function pageSyncToShowStep2()
    {
        // Init
        if ($this->objStepPool->step == null)
        {
            $this->objStepPool->step = 1;
        }

        // Set content back to normale mode
        $this->booError = false;
        $this->strError = "";
        $this->objData->setState($GLOBALS['TL_LANG']['MSC']['progress']);

        /* ---------------------------------------------------------------------
         * Run page
         */

        try
        {
            switch ($this->objStepPool->step)
            {
                /**
                 * Show step
                 */
                case 1:
                    $this->objData->setState($GLOBALS['TL_LANG']['MSC']['progress']);
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1']);

                    $this->objStepPool->step++;

                    break;

                /**
                 * Build checksum list for 'files'
                 */
                case 2:
                    if (in_array("user_change", $this->arrSyncSettings["syncCto_Type"]))
                    {
                        $this->arrListFile = $this->objSyncCtoFiles->runChecksumFiles();

                        $this->objStepPool->step++;
                        break;
                    }
                    else
                    {
                        $this->arrListFile = array();
                    }

                /**
                 * Build checksum list for Conta core
                 */
                case 3:
                    if (in_array("core_change", $this->arrSyncSettings["syncCto_Type"]))
                    {
                        $this->arrListFile = array_merge($this->arrListFile, $this->objSyncCtoFiles->runChecksumCore());
                        $this->objStepPool->step++;

                        break;
                    }

                /**
                 * Send it to the client
                 */
                case 4:
                    if (in_array("core_change", $this->arrSyncSettings["syncCto_Type"]) || in_array("user_change", $this->arrSyncSettings["syncCto_Type"]))
                    {
                        $this->arrListCompare = $this->objSyncCtoCommunicationClient->runCecksumCompare($this->arrListFile);

                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_2']);
                        $this->objStepPool->step++;
                        break;
                    }

                /**
                 * Check for deleted files
                 */
                case 5:
                    if (in_array("core_delete", $this->arrSyncSettings["syncCto_Type"]))
                    {
                        $arrChecksumClient = $this->objSyncCtoCommunicationClient->getChecksumCore();
                        $this->arrListCompare = array_merge($this->arrListCompare, $this->objSyncCtoFiles->checkDeleteFiles($arrChecksumClient));

                        $this->objStepPool->step++;
                        break;
                    }

                case 6:
                    if (in_array("user_delete", $this->arrSyncSettings["syncCto_Type"]))
                    {
                        $arrChecksumClient = $this->objSyncCtoCommunicationClient->getChecksumFiles();
                        $this->arrListCompare = array_merge($this->arrListCompare, $this->objSyncCtoFiles->checkDeleteFiles($arrChecksumClient));
                    }

                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_3']);
                    $this->objStepPool->step++;
                    break;

                /**
                 * Set CSS and search for bigfiles
                 */
                case 7:
                    foreach ($this->arrListCompare as $key => $value)
                    {
                        switch ($value["state"])
                        {
                            case SyncCtoEnum::FILESTATE_BOMBASTIC_BIG:
                                $this->arrListCompare[$key]["css"] = "unknown";
                                $this->arrListCompare[$key]["css_big"] = "ignored";
                                break;

                            case SyncCtoEnum::FILESTATE_TOO_BIG_NEED:
                                $this->arrListCompare[$key]["css_big"] = "ignored";
                            case SyncCtoEnum::FILESTATE_NEED:
                                $this->arrListCompare[$key]["css"] = "modified";
                                break;

                            case SyncCtoEnum::FILESTATE_TOO_BIG_MISSING:
                                $this->arrListCompare[$key]["css_big"] = "ignored";
                            case SyncCtoEnum::FILESTATE_MISSING:
                                $this->arrListCompare[$key]["css"] = "new";
                                break;

                            case SyncCtoEnum::FILESTATE_DELETE:
                                $this->arrListCompare[$key]["css"] = "deleted";
                                break;

                            default:
                                $this->arrListCompare[$key]["css"] = "unknown";
                                break;
                        }

                        if ($value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_DELETE
                                || $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_MISSING
                                || $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_NEED
                                || $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_SAME
                                || $value["state"] == SyncCtoEnum::FILESTATE_BOMBASTIC_BIG
                                || $value["state"] == SyncCtoEnum::FILESTATE_DELETE)
                        {
                            continue;
                        }
                        else if ($value["size"] > $this->arrClientInformation["upload_sizeLimit"])
                        {
                            $this->arrListCompare[$key]["split"] = true;
                        }
                    }

                    $this->objStepPool->step++;
                    break;

                /**
                 * Show list with files and count
                 */
                case 8:
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
                                $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1']);
                                $this->objData->setHtml("");
                                $this->booRefresh = true;
                                $this->objStepPool->step++;
                                return;
                            }
                        }

                        $this->objData->setState($GLOBALS['TL_LANG']['MSC']['ok']);
                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1']);
                        $this->objData->setHtml("");
                        $this->booRefresh = true;
                        $this->intStep++;
                        break;
                    }

                    // Counter
                    $intCountMissing = 0;
                    $intCountNeed    = 0;
                    $intCountIgnored = 0;
                    $intCountDelete  = 0;

                    $intTotalSize = 0;

                    // Count files
                    foreach ($this->arrListCompare as $key => $value)
                    {
                        switch ($value['state'])
                        {
                            case SyncCtoEnum::FILESTATE_MISSING:
                                $intCountMissing++;
                                break;

                            case SyncCtoEnum::FILESTATE_NEED:
                                $intCountNeed++;
                                break;

                            case SyncCtoEnum::FILESTATE_DELETE:
                                $intCountDelete++;
                                break;

                            case SyncCtoEnum::FILESTATE_BOMBASTIC_BIG:
                            case SyncCtoEnum::FILESTATE_TOO_BIG_NEED:
                            case SyncCtoEnum::FILESTATE_TOO_BIG_MISSING:
                            case SyncCtoEnum::FILESTATE_TOO_BIG_DELETE :
                                $intCountIgnored++;
                                break;
                        }

                        if ($value["size"] != -1)
                        {
                            $intTotalSize += $value["size"];
                        }
                    }

                    $this->objStepPool->missing = $intCountMissing;
                    $this->objStepPool->need = $intCountNeed;
                    $this->objStepPool->ignored = $intCountIgnored;
                    $this->objStepPool->delete = $intCountDelete;

                    // Save files and go on or skip here
                    if ($intCountMissing == 0 && $intCountNeed == 0 && $intCountIgnored == 0 && $intCountDelete == 0)
                    {
                        // Set current step informations
                        $this->objData->setState($GLOBALS['TL_LANG']['MSC']['skipped']);
                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1']);
                        $this->objData->setHtml("");
                        $this->booRefresh = true;
                        $this->intStep++;

                        break;
                    }

                    $objTemp = new BackendTemplate("be_syncCto_filelist");
                    $objTemp->filelist = $this->arrListCompare;
                    $objTemp->id = $this->intClientID;
                    $objTemp->step = $this->intStep;
                    $objTemp->totalsize = $intTotalSize;
                    $objTemp->direction = "To";
                    $objTemp->compare_complex = false;

                    // Build content 
                    $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_4'], array($intCountMissing, $intCountNeed, $intCountDelete, $intCountIgnored)));
                    $this->objData->setHtml($objTemp->parse());
                    $this->booRefresh = false;
                    break;


                /**
                 * Search for big file
                 */
                case 9:
                    // build list with big files
                    $arrTempList = array();
                    $intTotalsize = 0;

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
                        $this->objData->setState($GLOBALS['TL_LANG']['MSC']['ok']);
                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_1']);
                        $this->objData->setHtml("");
                        $this->booRefresh = true;
                        $this->intStep++;
                        break;
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
                        $this->objData->setHtml("");
                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_2']['description_1']);
                        $this->objData->setState($GLOBALS['TL_LANG']['MSC']['ok']);
                        $this->booRefresh = true;
                        $this->intStep++;
                        break;
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
                            $intTotalsize += $value["size"];
                        }
                        else if ($value["split"] == 1)
                        {
                            $arrTempList[$key] = $this->arrListCompare[$key];
                            $intTotalsize += $value["size"];
                        }
                    }

                    uasort($arrTempList, 'syncCtoModelClientCMP');

                    $objTemp = new BackendTemplate("be_syncCto_filelist");
                    $objTemp->filelist = $arrTempList;
                    $objTemp->id = $this->intClientID;
                    $objTemp->step = $this->intStep;
                    $objTemp->totalsize = $intTotalsize;
                    $objTemp->direction = "To";
                    $objTemp->compare_complex = true;

                    $this->objData->setHtml($objTemp->parse());
                    $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_2']['description_5'], array($intCountSplit)));
                    $this->booRefresh = false;

                    break;
            }
        }
        catch (Exception $exc)
        {
            $this->log(vsprintf("Error on synchronization client ID %s", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "ERROR");

            $this->booError = true;
            $this->strError = $exc->getMessage();

            $this->objData->setState($GLOBALS['TL_LANG']['MSC']['error']);
        }
    }

    /**
     * Send Files / Split Files
     */
    private function pageSyncToShowStep3()
    {
        // Init
        if ($this->objStepPool->step == null)
        {
            $this->objStepPool->step = 1;
        }

        // Set content back to normale mode
        $this->booError = false;
        $this->strError = "";
        $this->objData->setState($GLOBALS['TL_LANG']['MSC']['progress']);

        // Count files
        if (is_array($this->arrListCompare) && count($this->arrListCompare) != 0 && $this->arrListCompare != false)
        {
            $intSkippCount = 0;
            $intSendCount  = 0;
            $intWaitCount  = 0;
            $intDelCount   = 0;
            $intSplitCount = 0;

            foreach ($this->arrListCompare as $value)
            {
                switch ($value["transmission"])
                {
                    case SyncCtoEnum::FILETRANS_SEND:
                        $intSendCount++;
                        break;

                    case SyncCtoEnum::FILETRANS_SKIPPED:
                        $intSkippCount++;
                        break;

                    case SyncCtoEnum::FILETRANS_WAITING:
                        $intWaitCount++;
                        break;
                }

                if ($value["state"] == SyncCtoEnum::FILESTATE_DELETE)
                {
                    $intDelCount++;
                }

                if ($value["split"] == true)
                {
                    $intSplitCount++;
                }
            }
        }

        try
        {
            // Timer 
            $intStar = time();

            switch ($this->objStepPool->step)
            {
                /**
                 * Show step
                 */
                case 1:
                    $this->objData->setState($GLOBALS['TL_LANG']['MSC']['progress']);
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_1']);

                    $this->objStepPool->step++;
                    break;

                /**
                 * Send normal files
                 */
                case 2:
                    // Send allfiles exclude the big thing ones
                    $intCountTransfer = 1;

                    foreach ($this->arrListCompare as $key => $value)
                    {
                        if ($value["transmission"] == SyncCtoEnum::FILETRANS_SEND || $value["transmission"] == SyncCtoEnum::FILETRANS_SKIPPED)
                        {
                            continue;
                        }

                        if ($value["state"] == SyncCtoEnum::FILESTATE_DELETE)
                        {
                            continue;
                        }

                        if ($value["skipped"] == TRUE)
                        {
                            continue;
                        }

                        if ($value["split"] == TRUE)
                        {
                            continue;
                        }

                        if ($value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG
                                || $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_DELETE
                                || $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_NEED
                                || $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_MISSING
                                || $value["state"] == SyncCtoEnum::FILESTATE_BOMBASTIC_BIG)
                        {
                            $this->arrListCompare[$key]["skipreason"] = $GLOBALS['TL_LANG']['ERR']['maximum_filesize'];
                            $this->arrListCompare[$key]["transmission"] = SyncCtoEnum::FILETRANS_SKIPPED;

                            continue;
                        }

                        try
                        {
                            // Send files
                            $this->objSyncCtoCommunicationClient->sendFile(dirname($value["path"]), str_replace(dirname($value["path"]) . "/", "", $value["path"]), $value["checksum"], SyncCtoEnum::UPLOAD_SYNC_TEMP);
                            $this->arrListCompare[$key]["transmission"] = SyncCtoEnum::FILETRANS_SEND;
                        }
                        catch (Exception $exc)
                        {
                            $this->arrListCompare[$key]["transmission"] = SyncCtoEnum::FILETRANS_SKIPPED;
                            $this->arrListCompare[$key]["skipreason"] = $exc->getMessage();
                        }

                        $intCountTransfer++;

                        if ($intCountTransfer == 201 || $intStart < (time() - 30))
                        {
                            break;
                        }
                    }

                    if ($intWaitCount - ($intDelCount + $intSplitCount + $intSkippCount) > 0)
                    {
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_2'], array($intSendCount, count($this->arrListCompare) - ($intDelCount + $intSplitCount + $intSkippCount))));
                    }
                    else
                    {
                        foreach ($this->arrListCompare as $key => $value)
                        {
                            if ($value["split"] == true)
                            {
                                $this->objStepPool->step++;
                                $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_3']);
                                return;
                            }
                        }

                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_1']);
                        $this->intStep++;
                    }

                    break;

                /**
                 * Split files
                 */
                case 3:
                    $intCountSplit = 0;
                    $intCount      = 0;

                    foreach ($this->arrListCompare as $key => $value)
                    {
                        if ($value["split"] == true)
                        {
                            $intCountSplit++;
                        }
                    }

                    foreach ($this->arrListCompare as $key => $value)
                    {
                        if ($value["split"] != true)
                        {
                            continue;
                        }

                        if ($value["split"] != 0 && $value["splitname"] != "")
                        {
                            $intCount++;
                            continue;
                        }

                        // Splitt file
                        $intSplits = $this->objSyncCtoFiles->splitFiles($value["path"], $GLOBALS['SYC_PATH']['tmp'] . $key, $key, ($this->arrClientInformation["upload_sizeLimit"] / 100 * $this->arrClientInformation["upload_sizePercent"]));

                        $this->arrListCompare[$key]["splitcount"] = $intSplits;
                        $this->arrListCompare[$key]["splitname"] = $key;

                        break;
                    }

                    if ($intCount != $intCountSplit)
                    {
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_4'], array($intCount, $intCountSplit)));
                    }
                    else
                    {
                        $this->objStepPool->step++;
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_3'], array($intCount, $intCountSplit)));
                    }

                    break;

                /**
                 * Send bigfiles 
                 */
                case 4:
                    $intCountSplit = 0;
                    $intCount      = 0;

                    foreach ($this->arrListCompare as $key => $value)
                    {
                        if ($value["split"] == true)
                        {
                            $intCountSplit++;
                        }
                    }

                    foreach ($this->arrListCompare as $key => $value)
                    {
                        if ($value["split"] != true)
                        {
                            continue;
                        }

                        if ($value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_DELETE ||
                                $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_MISSING ||
                                $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_NEED ||
                                $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_SAME ||
                                $value["state"] == SyncCtoEnum::FILESTATE_BOMBASTIC_BIG ||
                                $value["state"] == SyncCtoEnum::FILESTATE_DELETE)
                        {
                            continue;
                        }

                        if (!empty($value["split_transfer"]) && $value["splitcount"] == $value["split_transfer"])
                        {
                            $intCount++;
                            continue;
                        }

                        if (empty($value["split_transfer"]))
                        {
                            $value["split_transfer"] = 0;
                        }

                        for ($ii = $value["split_transfer"]; $ii < $value["splitcount"]; $ii++)
                        {
                            // Max limit for file send, 10 minutes
                            set_time_limit(7200);

                            // Send file to client
                            $arrResponse = $this->objSyncCtoCommunicationClient->sendFile($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], $key), $value["splitname"] . ".sync" . $ii, "", SyncCtoEnum::UPLOAD_SYNC_SPLIT, $value["splitname"]);

                            $this->arrListCompare[$key]["split_transfer"] = $ii + 1;

                            // check time limit 30 secs
                            if ($intStar + 30 < time())
                            {
                                break;
                            }
                        }

                        break;
                    }

                    if ($intCount != $intCountSplit)
                    {
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_4'], array($intCount, $intCountSplit)));
                    }
                    else
                    {
                        $this->objStepPool->step++;
                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_5']);
                    }

                    break;

                /**
                 * Rebuild split files
                 */
                case 5:
                    $intCountSplit = 0;
                    $intCount      = 0;

                    foreach ($this->arrListCompare as $key => $value)
                    {
                        if ($value["split"] == true)
                        {
                            $intCountSplit++;
                        }
                    }

                    foreach ($this->arrListCompare as $key => $value)
                    {
                        if ($value["split"] != true)
                        {
                            continue;
                        }

                        if ($value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_DELETE ||
                                $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_MISSING ||
                                $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_NEED ||
                                $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_SAME ||
                                $value["state"] == SyncCtoEnum::FILESTATE_BOMBASTIC_BIG ||
                                $value["state"] == SyncCtoEnum::FILESTATE_DELETE)
                        {
                            continue;
                        }

                        if ($value["transmission"] == SyncCtoEnum::FILETRANS_SEND)
                        {
                            $intCount++;
                            continue;
                        }

                        if (!$this->objSyncCtoCommunicationClient->buildSingleFile($value["splitname"], $value["splitcount"], $value["path"], $value["checksum"]))
                        {
                            throw new Exception(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']['error_step_3']['rebuild'], array($value["path"])));
                        }

                        $this->arrListCompare[$key]["transmission"] = SyncCtoEnum::FILETRANS_SEND;

                        if ($intStar < time() - 30)
                        {
                            break;
                        }
                    }

                    if ($intCount != $intCountSplit)
                    {
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_4'], array($intCount, $intCountSplit)));
                    }
                    else
                    {
                        $this->objData->setState($GLOBALS['TL_LANG']['MSC']['ok']);
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_6'], array($intCount, $intCountSplit)));

                        $this->intStep++;
                    }

                    break;
            }
        }
        catch (Exception $exc)
        {
            $this->log(vsprintf("Error on synchronization client ID %s", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "ERROR");

            $this->booError = TRUE;
            $this->strError = $exc->getMessage();
            $this->objData->setState($GLOBALS['TL_LANG']['MSC']['error']);
        }
    }

    /**
     * Build SQL zip and send it to the client
     */
    private function pageSyncToShowStep4()
    {
        /* ---------------------------------------------------------------------
         * Init
         */

        if ($this->objStepPool->step == null)
        {
            $this->objStepPool->step = 1;
        }

        // Set content back to normale mode
        if ($this->booError == true)
        {
            $this->booError = false;
            $this->strError = "";
            $this->objData->setState($GLOBALS['TL_LANG']['MSC']['progress']);

            $this->objStepPool->step = 1;
        }

        /* ---------------------------------------------------------------------
         * Run page
         */

        try
        {
            switch ($this->objStepPool->step)
            {
                /**
                 * Init
                 */
                case 1:
                    $this->objData->setState($GLOBALS['TL_LANG']['MSC']['progress']);
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_4"]['description_1']);
                    $this->objStepPool->step++;

                    break;

                /**
                 * Build SQL Zip File
                 */
                case 2:
                    $this->objStepPool->zipname = $this->objSyncCtoDatabase->runDump($this->arrSyncSettings['syncCto_SyncTables'], true);
                    $this->objStepPool->arrTableHash = $this->objSyncCtoHelper->getDatabaseTablesHash();

                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_4"]['description_2']);
                    $this->objStepPool->step++;

                    break;

                /**
                 * Send file to client
                 */
                case 3:
                    $arrResponse = $this->objSyncCtoCommunicationClient->sendFile($GLOBALS['SYC_PATH']['tmp'], $this->objStepPool->zipname, "", SyncCtoEnum::UPLOAD_SQL_TEMP);

                    // Check if the file was send and saved.
                    if (!is_array($arrResponse) || count($arrResponse) == 0)
                    {
                        throw new Exception("Empty file list from client. Maybe file send was not complet.");
                    }

                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_4"]['description_3']);
                    $this->objStepPool->step++;

                    break;

                /**
                 * Import on client side
                 */
                case 4:
                    // Import SQL zip 
                    $this->objSyncCtoCommunicationClient->runSQLImport($this->objSyncCtoHelper->standardizePath($this->arrClientInformation["folders"]["tmp"], "sql", $this->objStepPool->zipname));

                    /**
                     * @todo insert hash table here 
                     */
                    $this->objData->setState($GLOBALS['TL_LANG']['MSC']['ok']);
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_4"]['description_4']);

                    $this->intStep++;

                    break;
            }
        }
        catch (Exception $exc)
        {
            $this->log(vsprintf("Error on synchronization client ID %s", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "ERROR");

            $this->booError = true;
            $this->strError = $exc->getMessage();
            $this->objData->setState($GLOBALS['TL_LANG']['MSC']['error']);
        }
    }

    /**
     * 
     */
    private function pageSyncToShowStep5()
    {
        /* ---------------------------------------------------------------------
         * Init
         */

        if ($this->objStepPool->step == null)
        {
            $this->objStepPool->step = 1;
        }

        // Set content back to normale mode
        $this->booError = false;
        $this->strError = "";
        $this->objData->setState($GLOBALS['TL_LANG']['MSC']['progress']);

        /* ---------------------------------------------------------------------
         * Run page
         */

        try
        {
            switch ($this->objStepPool->step)
            {
                /**
                 * Init
                 */
                case 1:
                    $this->objData->setState($GLOBALS['TL_LANG']['MSC']['progress']);
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_1']);
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
                    $this->objStepPool->step++;
                    break;

                /**
                 * Import Files
                 */
                case 2:
                    if (is_array($this->arrListCompare) && count($this->arrListCompare) != 0)
                    {
                        $arrImport = array();

                        foreach ($this->arrListCompare as $key => $value)
                        {
                            if ($value["transmission"] == SyncCtoEnum::FILETRANS_SEND)
                            {
                                $arrImport[$key] = $this->arrListCompare[$key];
                            }
                        }

                        if (count($arrImport) > 0)
                        {
                            $arrTransmission = $this->objSyncCtoCommunicationClient->runFileImport($arrImport);

                            foreach ($arrTransmission as $key => $value)
                            {
                                $this->arrListCompare[$key] = $arrTransmission[$key];
                            }
                        }

                        $this->objStepPool->step++;
                        break;
                    }

                    $this->objStepPool->step++;

                /**
                 * Delete files
                 */
                case 3:
                    if (count($this->arrListCompare) != 0 && is_array($this->arrListCompare))
                    {
                        $arrDelete = array();

                        foreach ($this->arrListCompare as $key => $value)
                        {
                            if ($value["state"] == SyncCtoEnum::FILESTATE_DELETE)
                            {
                                $arrDelete[$key] = $this->arrListCompare[$key];
                            }
                        }

                        if (count($arrDelete) > 0)
                        {
                            $arrDelete = $this->objSyncCtoCommunicationClient->deleteFiles($arrDelete);

                            foreach ($arrDelete as $key => $value)
                            {
                                $this->arrListCompare[$key] = $value;
                            }
                        }

                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_3']);
                        $this->objStepPool->step++;
                        break;
                    }

                    $this->objStepPool->step++;

                /**
                 * Import Config
                 */
                case 4:
                    if (in_array("localconfig_update", $this->arrSyncSettings["syncCto_Type"]))
                    {
                        $this->objSyncCtoCommunicationClient->runLocalConfigImport();
                        $this->objStepPool->step++;
                        break;
                    }

                    $this->objStepPool->step++;

                /**
                 * Import Config / Set show error
                 */
                case 5:
                    if (in_array("localconfig_errors", $this->arrSyncSettings["syncCto_Type"]))
                    {
                        $this->objSyncCtoCommunicationClient->setDisplayErrors(true);
                    }
                    else
                    {
                        $this->objSyncCtoCommunicationClient->setDisplayErrors(false);
                    }

                    $this->objStepPool->step++;
                    break;

                /**
                 * Import Config / Set referer check
                 */
                case 6:
                    if (in_array("localconfig_refererCheck", $this->arrSyncSettings["syncCto_Type"]))
                    {
                        $this->objSyncCtoCommunicationClient->setReferrerDisable(true);
                    }
                    else
                    {
                        $this->objSyncCtoCommunicationClient->setReferrerDisable(false);
                    }

                    $this->objStepPool->step++;
                    break;

                case 7:
                    if (is_array($this->arrSyncSettings["syncCto_Systemoperations_Maintenance"]) && count($this->arrSyncSettings["syncCto_Systemoperations_Maintenance"]) != 0)
                    {
                        $this->objSyncCtoCommunicationClient->runMaintenance($this->arrSyncSettings["syncCto_Systemoperations_Maintenance"]);
                    }

                    $this->objStepPool->step++;
                    $this->log(vsprintf("Successfully finishing of synchronization client ID %s.", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "INFO");
                    break;

                /**
                 * Cleanup
                 */
                case 8:
                    $this->objSyncCtoCommunicationClient->purgeTemp();
                    $this->objSyncCtoFiles->purgeTemp();

                    $this->objStepPool->step++;
                    break;

                case 9:
                    $this->objSyncCtoCommunicationClient->referrerEnable();
                    $this->objStepPool->step++;
                    break;

                case 10:
                    $this->objSyncCtoCommunicationClient->stopConnection();
                    $this->objStepPool->step++;
                    break;

                /**
                 * Show information
                 */
                case 11:
                    // Count files
                    if (is_array($this->arrListCompare) && count($this->arrListCompare) != 0 && $this->arrListCompare != false)
                    {
                        $intSkippCount = 0;
                        $intSendCount  = 0;
                        $intWaitCount  = 0;
                        $intDelCount   = 0;
                        $intSplitCount = 0;

                        foreach ($this->arrListCompare as $value)
                        {
                            switch ($value["transmission"])
                            {
                                case SyncCtoEnum::FILETRANS_SEND:
                                    $intSendCount++;
                                    break;

                                case SyncCtoEnum::FILETRANS_SKIPPED:
                                    $intSkippCount++;
                                    break;

                                case SyncCtoEnum::FILETRANS_WAITING:
                                    $intWaitCount++;
                                    break;
                            }

                            if ($value["state"] == SyncCtoEnum::FILESTATE_DELETE)
                            {
                                $intDelCount++;
                            }

                            if ($value["split"] == true)
                            {
                                $intSplitCount++;
                            }
                        }
                    }

                    // Hide control div
                    $this->Template->showControl = false;

                    // If no files are send show success msg
                    if (!is_array($this->arrListCompare) || count($this->arrListCompare) == 0)
                    {
                        $this->objData->setHtml("");
                        $this->objData->setState($GLOBALS['TL_LANG']['MSC']['ok']);
                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_1']);
                        $this->booFinished = true;

                        // Set finished msg
                        // Set success information 
                        $arrClientLink = $this->Database
                                ->prepare("SELECT * FROM tl_synccto_clients WHERE id=?")
                                ->limit(1)
                                ->execute($this->intClientID)
                                ->fetchAllAssoc();

                        $strLink = vsprintf('<a href="%s:%s%s" target="_blank" style="text-decoration:underline;">', array($arrClientLink[0]['address'], $arrClientLink[0]['port'], $arrClientLink[0]['path']));

                        $this->objData->nextStep();
                        $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['complete']);
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']['complete'], array($strLink, "</a>")));
                        $this->objData->setState("");

                        break;
                    }
                    // If files was send, show more informations
                    else if (is_array($this->arrListCompare) && count($this->arrListCompare) != 0)
                    {
                        $this->objData->setHtml("");
                        $this->objData->setState($GLOBALS['TL_LANG']['MSC']['ok']);
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_2'], array($intSendCount, count($this->arrListCompare))));
                        $this->booFinished = true;
                    }

                    // Check if there are some skipped files
                    if ($intSkippCount != 0)
                    {
                        $compare .= '<br /><p class="tl_help">' . $intSkippCount . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_4'] . '</p>';

                        $arrSort = array();

                        foreach ($this->arrListCompare as $key => $value)
                        {
                            if ($value["transmission"] != SyncCtoEnum::FILETRANS_SKIPPED)
                                continue;

                            $arrSort[$value["skipreason"]][] = $value["path"];
                        }

                        $compare .= '<ul class="fileinfo">';
                        foreach ($arrSort as $keyOuter => $valueOuter)
                        {
                            $compare .= "<li>";
                            $compare .= '<strong>' . $keyOuter . '</strong>';
                            $compare .= "<ul>";
                            foreach ($valueOuter as $valueInner)
                            {
                                $compare .= "<li>" . $valueInner . "</li>";
                            }
                            $compare .= "</ul>";
                            $compare .= "</li>";
                        }
                        $compare .= "</ul>";
                    }

                    // Show filelist only in debug mode
                    if ($GLOBALS['TL_CONFIG']['syncCto_debug_mode'] == true)
                    {
                        if (count($this->arrListCompare) != 0 && is_array($this->arrListCompare))
                        {
                            $compare .= '<br /><p class="tl_help">' . $intSendCount . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_5'] . '</p>';

                            $arrSort = array();

                            if (($intSendCount - $intDelCount) != 0)
                            {
                                $compare .= '<ul class="fileinfo">';

                                $compare .= "<li>";
                                $compare .= '<strong>' . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_7'] . '</strong>';
                                $compare .= "<ul>";

                                foreach ($this->arrListCompare as $key => $value)
                                {
                                    if ($value["transmission"] != SyncCtoEnum::FILETRANS_SEND)
                                        continue;

                                    if ($value["state"] == SyncCtoEnum::FILESTATE_DELETE)
                                        continue;

                                    $compare .= "<li>";
                                    $compare .= (mb_check_encoding($value["path"], 'UTF-8')) ? $value["path"] : utf8_encode($value["path"]);
                                    $compare .= "</li>";
                                }
                                $compare .= "</ul>";
                                $compare .= "</li>";
                                $compare .= "</ul>";
                            }

                            //---------

                            if ($intDelCount != 0)
                            {
                                $compare .= '<ul class="fileinfo">';

                                $compare .= "<li>";
                                $compare .= '<strong>' . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_8'] . '</strong>';
                                $compare .= "<ul>";

                                foreach ($this->arrListCompare as $key => $value)
                                {
                                    if ($value["transmission"] != SyncCtoEnum::FILETRANS_SEND)
                                        continue;

                                    if ($value["state"] != SyncCtoEnum::FILESTATE_DELETE)
                                        continue;

                                    $compare .= "<li>";
                                    $compare .= (mb_check_encoding($value["path"], 'UTF-8')) ? $value["path"] : utf8_encode($value["path"]);
                                    $compare .= "</li>";
                                }
                                $compare .= "</ul>";
                                $compare .= "</li>";
                                $compare .= "</ul>";
                            }

                            // Not sended, still waiting

                            if ($intWaitCount != 0)
                            {
                                $compare .= '<br /><p class="tl_help">' . $intWaitCount . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_6'] . '</p>';

                                $arrSort = array();

                                $compare .= '<ul class="fileinfo">';

                                $compare .= "<li>";
                                $compare .= '<strong>' . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_9'] . '</strong>';
                                $compare .= "<ul>";

                                foreach ($this->arrListCompare as $key => $value)
                                {
                                    if ($value["transmission"] != SyncCtoEnum::FILETRANS_WAITING)
                                        continue;

                                    $compare .= "<li>";
                                    $compare .= (mb_check_encoding($value["path"], 'UTF-8')) ? $value["path"] : utf8_encode($value["path"]);
                                    $compare .= "</li>";
                                }
                                $compare .= "</ul>";
                                $compare .= "</li>";
                                $compare .= "</ul>";
                            }
                        }
                    }

                    $this->objData->setHtml($compare);

                    // Set finished msg
                    $arrClientLink = $this->Database
                            ->prepare("SELECT * FROM tl_synccto_clients WHERE id=?")
                            ->limit(1)
                            ->execute($this->intClientID)
                            ->fetchAllAssoc();

                    $strLink = vsprintf('<a href="%s:%s%s" target="_blank" style="text-decoration:underline;">', array($arrClientLink[0]['address'], $arrClientLink[0]['port'], $arrClientLink[0]['path']));

                    $this->objData->nextStep();
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['complete']);
                    $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']['complete'], array($strLink, "</a>")));
                    $this->objData->setState("");

                    break;
            }
        }
        catch (Exception $exc)
        {
            $this->log(vsprintf("Error on synchronization client ID %s", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "ERROR");

            $this->booError = true;
            $this->strError = $exc->getMessage();
            $this->objData->setState($GLOBALS['TL_LANG']['MSC']['error']);
        }
    }

    /*
     * End syncTo
     * -------------------------------------------------------------------------
     */

    /* -------------------------------------------------------------------------
     * Start syncFrom
     */

    /**
     * Check client communication
     */
    private function pageSyncFromShowStep1()
    {
        $this->log(vsprintf("Start synchronization client ID %s.", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "INFO");

        /* ---------------------------------------------------------------------
         * Init
         */

        // State save for this step
        $mixStepPool = $this->Session->get("syncCto_StepPool1");
        if ($mixStepPool == FALSE)
        {
            $mixStepPool = array("step" => 1);
        }

        // Load attention flag from session
        $booAttentionFlag = $this->Session->get("syncCto_AttentionFlag");

        // Load content
        $arrContenData = $this->Session->get("syncCto_Content");

        // Set content back to normale mode
        $arrContenData["error"]            = false;
        $arrContenData["error_msg"]        = "";
        $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['MSC']['progress'];

        /* ---------------------------------------------------------------------
         * Run page
         */

        try
        {
            switch ($mixStepPool["step"])
            {
                /**
                 * Show step
                 */
                case 1:
                    $arrContenData["data"][1]["title"]       = $GLOBALS['TL_LANG']['MSC']['step'] . " %s";
                    $arrContenData["data"][1]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_1'];
                    $arrContenData["data"][1]["state"]       = $GLOBALS['TL_LANG']['MSC']['progress'];

                    $mixStepPool["step"] = 2;
                    break;

                /**
                 * Start connection
                 */
                case 2:
                    $this->objSyncCtoCommunicationClient->startConnection();

                    $mixStepPool["step"] = 3;
                    break;

                /**
                 * Referer check deactivate
                 */
                case 3:
                    if (!$this->objSyncCtoCommunicationClient->referrerDisable())
                    {
                        $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['MSC']['error'];
                        $arrContenData["error"]            = true;
                        $arrContenData["error_msg"]        = $GLOBALS['TL_LANG']['tl_syncCto_sync']['error_step_1']['referer'];

                        break;
                    }

                    $mixStepPool["step"] = 4;
                    break;


                /**
                 * Check version / Set Attention flag
                 */
                case 4:
                    // Set attention flag
                    if ($booAttentionFlag == true)
                    {
                        $this->objSyncCtoCommunicationClient->setAttentionFlag(true);
                    }

                    $strVersion = $this->objSyncCtoCommunicationClient->getVersionSyncCto();

                    if (!version_compare($strVersion, $GLOBALS['SYC_VERSION'], "="))
                    {
                        $this->log(vsprintf("Not the same version from syncCto on synchronization client ID %s. Serverversion: %s. Clientversion: %s", array($this->Input->get("id"), $GLOBALS['SYC_VERSION'], $strVersion)), __CLASS__ . " " . __FUNCTION__, "INFO");

                        $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['MSC']['error'];
                        $arrContenData["error"]            = true;
                        $arrContenData["error_msg"]        = vsprintf($GLOBALS['TL_LANG']['ERR']['version'], array("syncCto", $GLOBALS['SYC_VERSION'], $strVersion));

                        break;
                    }

                    $strVersion = $this->objSyncCtoCommunicationClient->getVersionContao();

                    if (!version_compare($strVersion, VERSION, "="))
                    {
                        $this->log(vsprintf("Not the same version from contao on synchronization client ID %s. Serverversion: %s. Clientversion: %s", array($this->Input->get("id"), $GLOBALS['SYC_VERSION'], $strVersion)), __CLASS__ . " " . __FUNCTION__, "INFO");

                        $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['MSC']['error'];
                        $arrContenData["error"]            = true;
                        $arrContenData["error_msg"]        = vsprintf($GLOBALS['TL_LANG']['ERR']['version'], array("Contao", VERSION, $strVersion));

                        break;
                    }

                    $arrContenData["data"][1]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_2'];

                    $mixStepPool["step"] = 5;
                    break;

                /**
                 * Clear client and server temp folder  
                 */
                case 5:
                    $this->objSyncCtoCommunicationClient->purgeTemp();
                    $this->objSyncCtoFiles->purgeTemp();

                    // Current step is okay.
                    $arrContenData["data"][1]["state"]       = $GLOBALS['TL_LANG']['MSC']['ok'];
                    $arrContenData["data"][1]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_1'];

                    // Create next step.
                    $arrContenData["data"][2]["state"]       = $GLOBALS['TL_LANG']['MSC']['progress'];
                    $arrContenData["data"][2]["title"]       = $GLOBALS['TL_LANG']['MSC']['step'] . " 2";
                    $arrContenData["data"][2]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1'];

                    $mixStepPool = FALSE;

                    $this->intStep++;

                    break;
            }
        }
        catch (Exception $exc)
        {
            $this->log(vsprintf("Error on synchronization client ID %s", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "ERROR");

            $arrContenData["error"]            = true;
            $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['MSC']['error'];
            $arrContenData["error_msg"]        = $exc->getMessage();
        }

        $this->Template->goBack = $this->script . $arrContenData["goBack"];
        $this->Template->data = $arrContenData["data"];
        $this->Template->step = $this->intStep;
        $this->Template->error = $arrContenData["error"];
        $this->Template->error_msg = $arrContenData["error_msg"];
        $this->Template->refresh = $arrContenData["refresh"];
        $this->Template->url = $arrContenData["url"];
        $this->Template->start = $arrContenData["start"];
        $this->Template->headline = $arrContenData["headline"];
        $this->Template->information = $arrContenData["information"];
        $this->Template->finished = $arrContenData["finished"];

        $this->Session->set("syncCto_StepPool1", $mixStepPool);
        $this->Session->set("syncCto_Content", $arrContenData);
    }

    /**
     * Build checksum list and ask client
     */
    private function pageSyncFromShowStep2()
    {
        /* ---------------------------------------------------------------------
         * Init
         */

        // Needed files/information
        $intSyncTyp = $this->Session->get("syncCto_Typ");

        // State save for this step
        $mixStepPool = $this->Session->get("syncCto_StepPool2");
        if ($mixStepPool == FALSE)
        {
            $mixStepPool = array("step" => 1);
        }

        // Load content
        $arrContenData = $this->Session->get("syncCto_Content");

        // Set content back to normale mode
        $arrContenData["error"]            = false;
        $arrContenData["error_msg"]        = "";
        $arrContenData["data"][2]["state"] = $GLOBALS['TL_LANG']['MSC']['progress'];

        /* ---------------------------------------------------------------------
         * Run page
         */


        try
        {
            switch ($mixStepPool["step"])
            {
                /**
                 * Build checksum list for 'files'
                 */
                case 1:
                    if ($intSyncTyp == SYNCCTO_SMALL || $intSyncTyp == SYNCCTO_FULL)
                    {
                        // Write filelist to file
                        $this->arrListFile = $this->objSyncCtoCommunicationClient->getChecksumFiles();
                        $mixStepPool["step"] = 2;
                    }
                    else
                    {
                        $this->arrListFile = array();
                        $mixStepPool["step"] = 2;
                    }

                    break;

                /**
                 * Build checksum list for Conta core
                 */
                case 2:
                    if ($intSyncTyp == SYNCCTO_FULL)
                    {
                        $this->arrListFile = array_merge($this->arrListFile, $this->objSyncCtoCommunicationClient->getChecksumCore());
                    }
                    else
                    {
                        $this->arrListFile = array_merge($this->arrListFile, array());
                    }

                    $mixStepPool["step"] = 3;

                    break;

                /**
                 * Send it to the client
                 */
                case 3:
                    $this->arrListCompare = $this->objSyncCtoFiles->runCecksumCompare($this->arrListFile);

                    $arrContenData["data"][2]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_2'];
                    $mixStepPool["step"]                     = 4;

                    break;

                /**
                 * Check for deleted files
                 */
                case 4:
                    switch ($intSyncTyp)
                    {
                        case SYNCCTO_FULL:
                            $arrChecksumClient = $this->objSyncCtoFiles->runChecksumCore();
                            $this->arrListCompare = array_merge($this->arrListCompare, $this->objSyncCtoCommunicationClient->checkDeleteFiles($arrChecksumClient));

                        case SYNCCTO_SMALL:
                            $arrChecksumClient = $this->objSyncCtoFiles->runChecksumFiles();
                            $this->arrListCompare = array_merge($this->arrListCompare, $this->objSyncCtoCommunicationClient->checkDeleteFiles($arrChecksumClient));

                        default:
                            break;
                    }

                    $mixStepPool["step"] = 5;

                    break;

                /**
                 * Check for deleted folders
                 */
                case 5:
                    switch ($intSyncTyp)
                    {
                        case SYNCCTO_FULL:
                            $arrChecksumClient = $this->objSyncCtoFiles->runChecksumFolders();
                            $this->arrListCompare = array_merge($this->arrListCompare, $this->objSyncCtoCommunicationClient->checkDeleteFiles($arrChecksumClient));

                        case SYNCCTO_SMALL:
                            $arrChecksumClient = $this->objSyncCtoFiles->runChecksumFolders(true);
                            $this->arrListCompare = array_merge($this->arrListCompare, $this->objSyncCtoCommunicationClient->checkDeleteFiles($arrChecksumClient));

                        default:
                            break;
                    }

                    $arrContenData["data"][2]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_3'];
                    $mixStepPool["step"]                     = 6;

                    break;

                /**
                 * Set CSS
                 */
                case 6:
                    foreach ($this->arrListCompare as $key => $value)
                    {
                        switch ($value["state"])
                        {
                            case SyncCtoEnum::FILESTATE_BOMBASTIC_BIG:
                                $this->arrListCompare[$key]["css"] = "unknown";
                                $this->arrListCompare[$key]["css_big"] = "ignored";
                                break;

                            case SyncCtoEnum::FILESTATE_TOO_BIG_NEED:
                                $this->arrListCompare[$key]["css_big"] = "ignored";
                            case SyncCtoEnum::FILESTATE_NEED:
                                $this->arrListCompare[$key]["css"] = "modified";
                                break;

                            case SyncCtoEnum::FILESTATE_TOO_BIG_MISSING:
                                $this->arrListCompare[$key]["css_big"] = "ignored";
                            case SyncCtoEnum::FILESTATE_MISSING:
                                $this->arrListCompare[$key]["css"] = "new";
                                break;

                            case SyncCtoEnum::FILESTATE_DELETE:
                                $this->arrListCompare[$key]["css"] = "deleted";
                                break;

                            default:
                                $this->arrListCompare[$key]["css"] = "unknown";
                                break;
                        }
                    }

                    $mixStepPool["step"] = 7;
                    break;

                /**
                 * Show list with files and count
                 */
                case 7:
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
                        // Set current step informations
                        $arrContenData["data"][2]["state"]       = $GLOBALS['TL_LANG']['MSC']['ok'];
                        $arrContenData["data"][2]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1'];
                        $arrContenData["data"][2]["html"]        = "";
                        $arrContenData["refresh"]                = true;

                        // Set next step information
                        $arrContenData["data"][3]["state"]       = $GLOBALS['TL_LANG']['MSC']['progress'];
                        $arrContenData["data"][3]["title"]       = $GLOBALS['TL_LANG']['MSC']['step'] . " 3";
                        $arrContenData["data"][3]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_1'];

                        $this->intStep++;
                        $mixStepPool = false;
                        break;
                    }

                    // Counter
                    $intCountMissing = 0;
                    $intCountNeed    = 0;
                    $intCountIgnored = 0;
                    $intCountDelete  = 0;

                    $intTotalSize = 0;

                    // Count files
                    foreach ($this->arrListCompare as $key => $value)
                    {
                        switch ($value['state'])
                        {
                            case SyncCtoEnum::FILESTATE_MISSING:
                                $intCountMissing++;
                                break;

                            case SyncCtoEnum::FILESTATE_NEED:
                                $intCountNeed++;
                                break;

                            case SyncCtoEnum::FILESTATE_DELETE:
                                $intCountDelete++;
                                break;

                            case SyncCtoEnum::FILESTATE_BOMBASTIC_BIG:
                            case SyncCtoEnum::FILESTATE_TOO_BIG_NEED:
                            case SyncCtoEnum::FILESTATE_TOO_BIG_MISSING:
                            case SyncCtoEnum::FILESTATE_TOO_BIG_DELETE :
                                $intCountIgnored++;
                                break;
                        }

                        if ($value["size"] != -1)
                        {
                            $intTotalSize += $value["size"];
                        }
                    }

                    $mixStepPool["missing"] = $intCountMissing;
                    $mixStepPool["need"]    = $intCountNeed;
                    $mixStepPool["ignored"] = $intCountIgnored;
                    $mixStepPool["delete"]  = $intCountDelete;

                    // Save files and go on or skip here
                    if ($intCountMissing == 0 && $intCountNeed == 0 && $intCountIgnored == 0 && $intCountDelete == 0)
                    {
                        // Set current step informations
                        $arrContenData["data"][2]["state"]       = $GLOBALS['TL_LANG']['MSC']['skipped'];
                        $arrContenData["data"][2]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1'];
                        $arrContenData["data"][2]["html"]        = "";
                        $arrContenData["refresh"]                = true;

                        // Set next step information
                        $arrContenData["data"][3]["state"]       = $GLOBALS['TL_LANG']['MSC']['progress'];
                        $arrContenData["data"][3]["title"]       = $GLOBALS['TL_LANG']['MSC']['step'] . " 3";
                        $arrContenData["data"][3]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_1'];

                        $mixStepPool = false;
                        $this->intStep++;

                        break;
                    }

                    $objTemp = new BackendTemplate("be_syncCto_filelist");
                    $objTemp->filelist = $this->arrListCompare;
                    $objTemp->id = $this->Input->get("id");
                    $objTemp->step = $this->intStep;
                    $objTemp->totalsize = $intTotalSize;
                    $objTemp->direction = "From";
                    $objTemp->compare_complex = false;

                    // Build content                       
                    $arrContenData["data"][2]["description"] = vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_4'], array($intCountMissing, $intCountNeed, $intCountDelete, $intCountIgnored));
                    $arrContenData["data"][2]["html"] = $objTemp->parse();
                    $arrContenData["refresh"]         = false;

                    $mixStepPool["step"] = 7;

                    break;
            }
        }
        catch (Exception $exc)
        {
            $this->log(vsprintf("Error on synchronization client ID %s", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "ERROR");

            $arrContenData["error"]            = true;
            $arrContenData["data"][2]["state"] = $GLOBALS['TL_LANG']['MSC']['error'];
            $arrContenData["error_msg"]        = $exc->getMessage();
        }

        $this->Template->goBack = $this->script . $arrContenData["goBack"];
        $this->Template->data = $arrContenData["data"];
        $this->Template->step = $this->intStep;
        $this->Template->error = $arrContenData["error"];
        $this->Template->error_msg = $arrContenData["error_msg"];
        $this->Template->refresh = $arrContenData["refresh"];
        $this->Template->url = $arrContenData["url"];
        $this->Template->start = $arrContenData["start"];
        $this->Template->headline = $arrContenData["headline"];
        $this->Template->information = $arrContenData["information"];
        $this->Template->finished = $arrContenData["finished"];

        $this->Session->set("syncCto_StepPool2", $mixStepPool);
        $this->Session->set("syncCto_Content", $arrContenData);
    }

    /**
     * Split Files
     */
    private function pageSyncFromShowStep3()
    {
        // State save for this step
        $mixStepPool = $this->Session->get("syncCto_StepPool3");
        if ($mixStepPool == FALSE)
        {
            $mixStepPool = array("step" => 1);
        }

        $strTempFolder = $this->Session->get("syncCto_Client_Tempfolder");
        if (empty($strTempFolder))
        {
            $strTempFolder = FALSE;
        }

        // Load content
        $arrContenData = $this->Session->get("syncCto_Content");

        // Set content back to normale mode
        $arrContenData["error"]            = false;
        $arrContenData["error_msg"]        = "";
        $arrContenData["data"][3]["state"] = $GLOBALS['TL_LANG']['MSC']['progress'];

        // Check if there is any file for upload
        if (count($this->arrListCompare) == 0 || !is_array($this->arrListCompare))
        {
            $arrContenData["data"][3]["state"] = $GLOBALS['TL_LANG']['MSC']['skipped'];

            $arrContenData["data"][4]["state"]       = $GLOBALS['TL_LANG']['MSC']['progress'];
            $arrContenData["data"][4]["title"]       = $GLOBALS['TL_LANG']['MSC']['step'] . " 4";
            $arrContenData["data"][4]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_4"]['description_1'];

            $this->intStep++;
            $mixStepPool == FALSE;
        }
        else
        {
            try
            {
                // Timer 
                $intStar = time();

                switch ($mixStepPool["step"])
                {
                    /**
                     * Load parameter from client
                     */
                    case 1:
                        $arrClientParameter = $this->objSyncCtoCommunicationClient->getClientParameter();

                        // Check if everthing is okay
                        if ($arrClientParameter['file_uploads'] != 1)
                        {
                            $arrContenData["data"][3]["state"] = $GLOBALS['TL_LANG']['MSC']['error'];
                            $arrContenData["error"]            = true;
                            $arrContenData["error_msg"]        = $GLOBALS['TL_LANG']['tl_syncCto_sync']['error_step_3']['upload_ini'];

                            break;
                        }

                        $strTempFolder = $this->objSyncCtoCommunicationClient->getPathList("tmp");

                        $intClientUploadLimit = intval(str_replace("M", "000000", $arrClientParameter['upload_max_filesize']));
                        $intClientMemoryLimit = intval(str_replace("M", "000000", $arrClientParameter['memory_limit']));
                        $intClientPostLimit   = intval(str_replace("M", "000000", $arrClientParameter['post_max_size']));
                        $intLocalMemoryLimit  = intval(str_replace("M", "000000", ini_get('memory_limit')));

                        // Check if memory limit on server and client is enough for download  
                        $intLimit = min($intClientUploadLimit, $intClientMemoryLimit, $intClientPostLimit, $intLocalMemoryLimit);

                        // Limit
                        if ($intLimit > 1073741824)
                        { // 1GB
                            $intPercent = 10;
                        }
                        else if ($intLimit > 524288000)
                        { // 500MB
                            $intPercent = 10;
                        }
                        else if ($intLimit > 209715200)
                        { // 200MB
                            $intPercent = 10;
                        }
                        else
                        {
                            $intPercent = 30;
                        }

                        $intLimit = $intLimit / 100 * $intPercent;

                        $mixStepPool["limit"]   = $intLimit;
                        $mixStepPool["percent"] = $intPercent;
                        $mixStepPool["step"]    = 2;

                        break;

                    /**
                     * Search for big file
                     */
                    case 2:
                        foreach ($this->arrListCompare as $key => $value)
                        {
                            if ($value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_DELETE
                                    || $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_MISSING
                                    || $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_NEED
                                    || $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_SAME
                                    || $value["state"] == SyncCtoEnum::FILESTATE_BOMBASTIC_BIG
                                    || $value["state"] == SyncCtoEnum::FILESTATE_DELETE)
                            {
                                continue;
                            }
                            else if ($value["size"] > $mixStepPool["limit"])
                            {
                                $this->arrListCompare[$key]["split"] = true;
                            }
                        }

                        $intCountSplit = 0;
                        foreach ($this->arrListCompare as $key => $value)
                        {
                            if ($value["split"] == true)
                                $intCountSplit++;
                        }

                        // Skip page if no big file is found
                        if ($intCountSplit == 0)
                        {
                            $arrContenData["data"][3]["state"] = $GLOBALS['TL_LANG']['MSC']['skipped'];
                            $arrContenData["data"][3]["html"]  = "";

                            $arrContenData["data"][4]["state"]       = $GLOBALS['TL_LANG']['MSC']['progress'];
                            $arrContenData["data"][4]["title"]       = $GLOBALS['TL_LANG']['MSC']['step'] . " 4";
                            $arrContenData["data"][4]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_4"]['description_1'];

                            $this->intStep++;
                            $mixStepPool = FALSE;

                            break;
                        }
                        else
                        {
                            // build list with big files
                            $arrTempList = array();
                            $intTotalsize = 0;

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
                                $mixStepPool["step"] = 3;

                                $arrContenData["data"][3]["description"] = vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_2'], array(0, $intCountSplit));
                                $arrContenData["data"][3]["html"] = "";
                                $arrContenData["refresh"]         = true;

                                break;
                            }

                            $intCountSplit = 0;

                            foreach ($this->arrListCompare as $key => $value)
                            {
                                if ($value["split"] == true)
                                {
                                    $intCountSplit++;
                                }
                            }

                            if ($intCountSplit == 0)
                            {
                                $arrContenData["data"][3]["state"] = $GLOBALS['TL_LANG']['MSC']['skipped'];
                                $arrContenData["data"][3]["html"]  = "";

                                $arrContenData["data"][4]["state"]       = $GLOBALS['TL_LANG']['MSC']['progress'];
                                $arrContenData["data"][4]["title"]       = $GLOBALS['TL_LANG']['MSC']['step'] . " 4";
                                $arrContenData["data"][4]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_4"]['description_1'];

                                $arrContenData["refresh"] = true;

                                $this->intStep++;
                                $mixStepPool = FALSE;

                                break;
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
                                    $intTotalsize += $value["size"];
                                }
                                else if ($value["split"] == 1)
                                {
                                    $arrTempList[$key] = $this->arrListCompare[$key];
                                    $intTotalsize += $value["size"];
                                }
                            }

                            uasort($arrTempList, 'syncCtoModelClientCMP');

                            $mixStepPool5["step"]             = 2;
                            $mixStepPool5["splitfiles"]       = $mixSplitFiles;
                            $mixStepPool5["splitfiles_count"] = 0;
                            $mixStepPool5["splitfiles_send"]  = 0;

                            $objTemp = new BackendTemplate("be_syncCto_filelist");
                            $objTemp->filelist = $arrTempList;
                            $objTemp->id = $this->Input->get("id");
                            $objTemp->step = $this->intStep;
                            $objTemp->totalsize = $intTotalsize;
                            $objTemp->direction = "From";
                            $objTemp->compare_complex = true;

                            $arrContenData["data"][3]["html"] = $objTemp->parse();
                            $arrContenData["refresh"]         = false;

                            break;
                        }
                        break;

                    /**
                     * Split files
                     */
                    case 3:
                        $intCountSplit = 0;
                        $intCount      = 0;

                        foreach ($this->arrListCompare as $key => $value)
                        {
                            if ($value["split"] == true)
                            {
                                $intCountSplit++;
                            }
                        }

                        foreach ($this->arrListCompare as $key => $value)
                        {
                            if ($value["split"] != true)
                            {
                                continue;
                            }

                            if ($value["split"] != 0 && $value["splitname"] != "")
                            {
                                $intCount++;
                                continue;
                            }

                            $strSavePath = $this->objSyncCtoHelper->standardizePath($strTempFolder . $key);

                            // Splitt file
                            $intSplits = $this->objSyncCtoCommunicationClient->runSplitFiles($value["path"], $strSavePath, $key, ($mixStepPool["limit"] / 100 * $mixStepPool["percent"]));

                            $this->arrListCompare[$key]["splitcount"] = $intSplits;
                            $this->arrListCompare[$key]["splitname"] = $key;

                            // Check if we are in time or show page
                            if ($intStar < time() - 30)
                            {
                                break;
                            }
                        }

                        if ($intCount != $intCountSplit)
                        {
                            $mixStepPool["step"]                     = 3;
                            $arrContenData["data"][3]["description"] = vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_2'], array($intCount, $intCountSplit));
                        }
                        else
                        {
                            $mixStepPool["step"]                     = 4;
                            $arrContenData["data"][3]["description"] = vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_2'], array($intCount, $intCountSplit));
                        }

                        break;

                    /**
                     * Get bigfiles 
                     */
                    case 4:
                        $intCountSplit = 0;
                        $intCount      = 0;

                        foreach ($this->arrListCompare as $key => $value)
                        {
                            if ($value["split"] == true)
                                $intCountSplit++;
                        }

                        foreach ($this->arrListCompare as $key => $value)
                        {
                            if ($value["split"] != true)
                            {
                                continue;
                            }

                            if ($value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_DELETE ||
                                    $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_MISSING ||
                                    $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_NEED ||
                                    $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_SAME ||
                                    $value["state"] == SyncCtoEnum::FILESTATE_BOMBASTIC_BIG ||
                                    $value["state"] == SyncCtoEnum::FILESTATE_DELETE)
                            {
                                continue;
                            }

                            if (!empty($value["split_transfer"]) && $value["splitcount"] == $value["split_transfer"])
                            {
                                $intCount++;
                                continue;
                            }

                            if (empty($value["split_transfer"]))
                            {
                                $value["split_transfer"] = 0;
                            }

                            for ($ii = $value["split_transfer"]; $ii < $value["splitcount"]; $ii++)
                            {
                                // Max limit for file send, 20 minutes
                                set_time_limit(1200);

                                // Send file to client
                                $arrResponse = $this->objSyncCtoCommunicationClient->getFile($this->objSyncCtoHelper->standardizePath($strTempFolder, $key, $value["splitname"] . ".sync$ii"), $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], $key, $value["splitname"] . ".sync$ii"));

                                $this->arrListCompare[$key]["split_transfer"] = $ii + 1;

                                // check time limit 30 secs
                                if ($intStar + 30 < time())
                                {
                                    break;
                                }
                            }

                            break;
                        }

                        if ($intCount != $intCountSplit)
                        {
                            $mixStepPool["step"]                     = 4;
                            $arrContenData["data"][3]["description"] = vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_2'], array($intCount, $intCountSplit));
                        }
                        else
                        {
                            $mixStepPool["step"]                     = 5;
                            $arrContenData["data"][3]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_3'];
                        }

                        break;

                    case 5:
                        $intCountSplit = 0;
                        $intCount      = 0;

                        foreach ($this->arrListCompare as $key => $value)
                        {
                            if ($value["split"] == true)
                            {
                                $intCountSplit++;
                            }
                        }

                        foreach ($this->arrListCompare as $key => $value)
                        {
                            set_time_limit(600);

                            if ($value["split"] != true)
                            {
                                continue;
                            }

                            if ($value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_DELETE ||
                                    $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_MISSING ||
                                    $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_NEED ||
                                    $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_SAME ||
                                    $value["state"] == SyncCtoEnum::FILESTATE_BOMBASTIC_BIG ||
                                    $value["state"] == SyncCtoEnum::FILESTATE_DELETE)
                            {
                                continue;
                            }

                            if ($value["transmission"] == SyncCtoEnum::FILETRANS_SEND)
                            {
                                $intCount++;
                                continue;
                            }

                            if (!$this->objSyncCtoFiles->rebuildSplitFiles($value["splitname"], $value["splitcount"], $value["path"], $value["checksum"]))
                            {
                                throw new Exception(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']['error_step_3']['rebuild'], array($value["path"])));
                            }

                            $this->arrListCompare[$key]["transmission"] = SyncCtoEnum::FILETRANS_SEND;

                            if ($intStar < time() - 30)
                            {
                                break;
                            }
                        }

                        if ($intCount != $intCountSplit)
                        {
                            $mixStepPool["step"]                     = 5;
                            $arrContenData["data"][3]["description"] = vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_4'], array($intCount, $intCountSplit));
                        }
                        else
                        {
                            $arrContenData["data"][3]["state"]       = $GLOBALS['TL_LANG']['MSC']['ok'];
                            $arrContenData["data"][3]["description"] = vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_4'], array($intCount, $intCountSplit));

                            $arrContenData["data"][4]["state"]       = $GLOBALS['TL_LANG']['MSC']['progress'];
                            $arrContenData["data"][4]["title"]       = $GLOBALS['TL_LANG']['MSC']['step'] . " 4";
                            $arrContenData["data"][4]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_4"]['description_1'];

                            $this->intStep++;
                            $mixStepPool == FALSE;
                        }

                        break;
                }
            }
            catch (Exception $exc)
            {
                $this->log(vsprintf("Error on synchronization client ID %s", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "ERROR");

                $arrContenData["error"]            = true;
                $arrContenData["data"][3]["state"] = $GLOBALS['TL_LANG']['MSC']['error'];
                $arrContenData["error_msg"]        = $exc->getMessage();
            }
        }

        $this->Template->goBack = $this->script . $arrContenData["goBack"];
        $this->Template->data = $arrContenData["data"];
        $this->Template->step = $this->intStep;
        $this->Template->error = $arrContenData["error"];
        $this->Template->error_msg = $arrContenData["error_msg"];
        $this->Template->refresh = $arrContenData["refresh"];
        $this->Template->url = $arrContenData["url"];
        $this->Template->start = $arrContenData["start"];
        $this->Template->headline = $arrContenData["headline"];
        $this->Template->information = $arrContenData["information"];
        $this->Template->finished = $arrContenData["finished"];

        $this->Session->set("syncCto_StepPool3", $mixStepPool);
        $this->Session->set("syncCto_Content", $arrContenData);
        $this->Session->set("syncCto_Client_Tempfolder", $strTempFolder);
    }

    /**
     * Build SQL zip and send it to the client
     */
    private function pageSyncFromShowStep4()
    {
        /* ---------------------------------------------------------------------
         * Init
         */

        // State save for this step
        $mixStepPool = $this->Session->get("syncCto_StepPool4");
        if ($mixStepPool == FALSE)
            $mixStepPool = array("step" => 1);

        // Load content
        $arrContenData = $this->Session->get("syncCto_Content");

        // Start by Step 1
        if ($arrContenData["error"] == true)
        {
            $mixStepPool["step"] = 1;
        }

        // Set content back to normale mode
        $arrContenData["error"]            = false;
        $arrContenData["error_msg"]        = "";
        $arrContenData["data"][4]["state"] = $GLOBALS['TL_LANG']['MSC']['progress'];

        $arrTables = $this->Session->get("syncCto_SyncTables");

        /* ---------------------------------------------------------------------
         * Run page
         */

        // Check if there is a tablelist
        if (count($arrTables) == 0 || $arrTables == FALSE)
        {
            $arrContenData["data"][4]["state"] = $GLOBALS['TL_LANG']['MSC']['skipped'];

            $arrContenData["data"][5]["state"]       = $GLOBALS['TL_LANG']['MSC']['progress'];
            $arrContenData["data"][5]["title"]       = $GLOBALS['TL_LANG']['MSC']['step'] . " 5";
            $arrContenData["data"][5]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_1'];

            $this->intStep++;
            $mixStepPool == FALSE;
        }
        else
        {
            try
            {
                $intStart = time();

                switch ($mixStepPool["step"])
                {

                    /**
                     * Init
                     */
                    case 1:
                        $arrContenData["data"][4]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_4"]['description_1'];
                        $mixStepPool["step"]                     = 2;
                        break;

                    /**
                     * Build SQL Zip File
                     */
                    case 2:
                        $mixStepPool["zipname"] = $this->objSyncCtoCommunicationClient->runDatabaseDump($arrTables, true);

                        $arrContenData["data"][4]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_4"]['description_2'];
                        $mixStepPool["step"]                     = 3;
                        break;

                    /**
                     * Send file to client
                     */
                    case 3:
                        $strTempPath = $this->objSyncCtoCommunicationClient->getPathList("tmp");
                        $arrResponse = $this->objSyncCtoCommunicationClient->getFile($this->objSyncCtoHelper->standardizePath($strTempPath, $mixStepPool["zipname"]), $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "sql", $mixStepPool["zipname"]));

                        // Check if the file was send and saved.
                        if (empty($arrResponse))
                        {
                            throw new Exception("Empty file list from client. Maybe file send was not complet.");
                        }

                        $mixStepPool["zipname"] = $arrResponse;

                        $arrContenData["data"][4]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_4"]['description_3'];
                        $mixStepPool["step"]                     = 4;
                        break;

                    /**
                     * Import on client side
                     */
                    case 4:
                        $this->objSyncCtoDatabase->runRestore($mixStepPool["zipname"]);

                        $arrContenData["data"][4]["state"]       = $GLOBALS['TL_LANG']['MSC']['ok'];
                        $arrContenData["data"][4]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_4"]['description_4'];

                        $arrContenData["data"][5]["state"]       = $GLOBALS['TL_LANG']['MSC']['progress'];
                        $arrContenData["data"][5]["title"]       = $GLOBALS['TL_LANG']['MSC']['step'] . " 5";
                        $arrContenData["data"][5]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_1'];

                        $this->intStep++;
                        $mixStepPool == FALSE;

                        break;
                }
            }
            catch (Exception $exc)
            {
                $this->log(vsprintf("Error on synchronization client ID %s", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "ERROR");

                $arrContenData["error"]            = true;
                $arrContenData["data"][4]["state"] = $GLOBALS['TL_LANG']['MSC']['error'];
                $arrContenData["error_msg"]        = $exc->getMessage();
            }
        }

        $this->Template->goBack = $this->script . $arrContenData["goBack"];
        $this->Template->data = $arrContenData["data"];
        $this->Template->step = $this->intStep;
        $this->Template->error = $arrContenData["error"];
        $this->Template->error_msg = $arrContenData["error_msg"];
        $this->Template->refresh = $arrContenData["refresh"];
        $this->Template->url = $arrContenData["url"];
        $this->Template->start = $arrContenData["start"];
        $this->Template->headline = $arrContenData["headline"];
        $this->Template->information = $arrContenData["information"];
        $this->Template->finished = $arrContenData["finished"];

        $this->Session->set("syncCto_StepPool4", $mixStepPool);
        $this->Session->set("syncCto_Content", $arrContenData);
    }

    /**
     * File send part have fun, much todo here so let`s play a round :P
     */
    private function pageSyncFromShowStep5()
    {
        /* ---------------------------------------------------------------------
         * Init
         */

        // State save for this step
        $mixStepPool = $this->Session->get("syncCto_StepPool5");
        if ($mixStepPool == FALSE)
            $mixStepPool = array("step" => 1);

        // Load content
        $arrContenData                     = $this->Session->get("syncCto_Content");
        $arrContenData["error"]            = false;
        $arrContenData["data"][5]["state"] = $GLOBALS['TL_LANG']['MSC']['progress'];

        // Needed files/information        
        $intSyncTyp   = $this->Session->get("syncCto_Typ");
        $arrTables    = $this->Session->get("syncCto_SyncTables");
        $booPurgeData = $this->Session->get("syncCto_PurgeData");

        // Count files
        if (count($this->arrListCompare) != 0 && $this->arrListCompare != false && is_array($this->arrListCompare))
        {
            $intSkippCount = 0;
            $intSendCount  = 0;
            $intWaitCount  = 0;
            $intDelCount   = 0;

            foreach ($this->arrListCompare as $value)
            {
                switch ($value["transmission"])
                {
                    case SyncCtoEnum::FILETRANS_SEND:
                        $intSendCount++;
                        break;

                    case SyncCtoEnum::FILETRANS_SKIPPED:
                        $intSkippCount++;
                        break;

                    case SyncCtoEnum::FILETRANS_WAITING:
                        $intWaitCount++;
                        break;
                }

                if ($value["state"] == SyncCtoEnum::FILESTATE_DELETE)
                {
                    $intDelCount++;
                }
            }
        }

        /* ---------------------------------------------------------------------
         * Run page
         */

        // Check if there is any file for upload
        if ((count($this->arrListCompare) == 0 || !is_array($this->arrListCompare)) && $mixStepPool["step"] == 1)
        {
            $mixStepPool["step"] = 4;
        }

        try
        {
            $intStart = time();

            switch ($mixStepPool["step"])
            {
                /** ------------------------------------------------------------
                 * Get files
                 */
                case 1:
                    // Get allfiles exclude the big things
                    $intCountTransfer = 1;

                    foreach ($this->arrListCompare as $key => $value)
                    {
                        if ($value["transmission"] == SyncCtoEnum::FILETRANS_SEND || $value["transmission"] == SyncCtoEnum::FILETRANS_SKIPPED)
                        {
                            continue;
                        }

                        if ($value["state"] == SyncCtoEnum::FILESTATE_DELETE)
                        {
                            continue;
                        }

                        if ($value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG
                                || $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_DELETE
                                || $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_NEED
                                || $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_MISSING
                                || $value["state"] == SyncCtoEnum::FILESTATE_BOMBASTIC_BIG)
                        {
                            $this->arrListCompare[$key]["skipreason"] = $GLOBALS['TL_LANG']['ERR']['maximum_filesize'];
                            $this->arrListCompare[$key]["transmission"] = SyncCtoEnum::FILETRANS_SKIPPED;

                            continue;
                        }

                        try
                        {
                            $this->objSyncCtoCommunicationClient->getFile($value["path"], $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "sync", $value["path"]));
                            $this->arrListCompare[$key]["transmission"] = SyncCtoEnum::FILETRANS_SEND;
                        }
                        catch (Exception $exc)
                        {
                            $this->arrListCompare[$key]["transmission"] = SyncCtoEnum::FILETRANS_SKIPPED;
                            $this->arrListCompare[$key]["skipreason"] = $exc->getMessage();
                        }

                        $intCountTransfer++;

                        if ($intCountTransfer == 201 || $intStart < (time() - 30))
                        {
                            break;
                        }
                    }

                    if ($intWaitCount - $intDelCount > 0)
                    {
                        $mixStepPool["step"]                     = 1;
                        $arrContenData["data"][5]["description"] = vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_2'], array($intSendCount, count($this->arrListCompare)));
                    }
                    else
                    {
                        $mixStepPool["step"]                     = 2;
                        $arrContenData["data"][5]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_1'];
                    }

                    break;

                /** ------------------------------------------------------------
                 * Import Files
                 */
                case 2:
                    if (count($this->arrListCompare) != 0 && is_array($this->arrListCompare))
                    {
                        $arrImport = array();

                        foreach ($this->arrListCompare as $key => $value)
                        {
                            if ($value["transmission"] == SyncCtoEnum::FILETRANS_SEND)
                            {
                                $arrImport[$key] = $this->arrListCompare[$key];
                            }
                        }

                        if (count($arrImport) > 0)
                        {
                            $arrTransmission = $this->objSyncCtoFiles->moveTempFile($arrImport);

                            foreach ($arrTransmission as $key => $value)
                            {
                                $this->arrListCompare[$key] = $arrTransmission[$key];
                            }
                        }
                    }

                    $mixStepPool["step"] = 3;
                    break;

                /** ------------------------------------------------------------
                 * Delete files
                 */
                case 3:
                    if (count($this->arrListCompare) != 0 && is_array($this->arrListCompare))
                    {
                        $arrDelete = array();

                        foreach ($this->arrListCompare as $key => $value)
                        {
                            if ($value["state"] == SyncCtoEnum::FILESTATE_DELETE)
                            {
                                $arrDelete[$key] = $this->arrListCompare[$key];
                            }
                        }

                        if (count($arrDelete) > 0)
                        {
                            $arrDelete = $this->objSyncCtoFiles->deleteFiles($arrDelete);

                            foreach ($arrDelete as $key => $value)
                            {
                                $this->arrListCompare[$key] = $value;
                            }
                        }
                    }

                    $arrContenData["data"][5]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_3'];
                    $mixStepPool["step"]                     = 4;
                    break;

                /** ------------------------------------------------------------
                 * Import Config
                 */
                case 4:
                    if ($intSyncTyp == SYNCCTO_FULL)
                    {
                        $arrLocalconfig = $this->objSyncCtoCommunicationClient->getLocalConfig();
                        if (count($arrLocalconfig) != 0)
                        {
                            $this->objSyncCtoHelper->importConfig($arrLocalconfig);
                        }

                        $mixStepPool["step"] = 5;
                    }

                    $arrContenData["data"][5]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_1'];

                /** ------------------------------------------------------------
                 * Cleanup
                 */
                case 5:
                    $this->objSyncCtoCommunicationClient->purgeTemp();
                    $this->objSyncCtoFiles->purgeTemp();

                    $mixStepPool["step"] = 6;

                    break;

                case 6:
                    if ($booPurgeData == true)
                    {
                        $this->objSyncCtoFiles->purgeData();
                    }

                    $mixStepPool["step"] = 7;

                    $this->log(vsprintf("Successfully finishing of synchronization client ID %s.", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "INFO");

                    break;

                case 7:
                    $this->objSyncCtoCommunicationClient->referrerEnable();
                    $mixStepPool["step"] = 8;
                    break;

                case 8:
                    $this->objSyncCtoCommunicationClient->stopConnection();
                    $mixStepPool["step"] = 9;
                    break;

                /** ------------------------------------------------------------
                 * Show information
                 */
                case 9:
                    // Set success information 
                    $arrClientLink = $this->Database
                            ->prepare("SELECT * FROM tl_synccto_clients WHERE id=?")
                            ->limit(1)
                            ->execute($this->Input->get("id"))
                            ->fetchAllAssoc();

                    $arrContenData["data"][99]["title"] = $GLOBALS['TL_LANG']['MSC']['complete'];
                    $strLink                            = vsprintf('<a href="%s:%s%s" target="_blank" style="text-decoration:underline;">', array($arrClientLink[0]['address'], $arrClientLink[0]['port'], $arrClientLink[0]['path']));
                    $arrContenData["data"][99]["description"] = vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']['complete'], array($strLink, "</a>"));
                    $arrContenData["data"][99]["state"] = "";

                    // Hide control div
                    $this->Template->showControl = false;

                    if ($intSyncTyp == SYNCCTO_SMALL
                            && ( (count($this->arrListCompare) == 0 || $this->arrListCompare == FALSE)
                            && !is_array($this->arrListCompare))
                            && $booPurgeData == FALSE)
                    {
                        $arrContenData["data"][5]["html"]        = "";
                        $arrContenData["data"][5]["state"]       = $GLOBALS['TL_LANG']['MSC']['skipped'];
                        $arrContenData["data"][5]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_1'];
                        $arrContenData["finished"]               = true;

                        break;
                    }
                    else if ((count($this->arrListCompare) == 0
                            || $this->arrListCompare == FALSE
                            || !is_array($this->arrListCompare))
                            && $booPurgeData == FALSE)
                    {
                        $arrContenData["data"][5]["html"]        = "";
                        $arrContenData["data"][5]["state"]       = $GLOBALS['TL_LANG']['MSC']['skipped'];
                        $arrContenData["data"][5]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_1'];
                        $arrContenData["finished"]               = true;

                        break;
                    }
                    else
                    {
                        $arrContenData["data"][5]["description"] = vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_2'], array($intSendCount, count($this->arrListCompare)));
                        $arrContenData["data"][5]["state"] = $GLOBALS['TL_LANG']['MSC']['ok'];
                        $arrContenData["finished"]         = true;
                    }

                    if ($intSkippCount != 0)
                    {
                        $compare .= '<br /><p class="tl_help">' . $intSkippCount . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_4'] . '</p>';

                        $arrSort = array();

                        foreach ($this->arrListCompare as $key => $value)
                        {
                            if ($value["transmission"] != SyncCtoEnum::FILETRANS_SKIPPED)
                                continue;

                            $arrSort[$value["skipreason"]][] = $value["path"];
                        }

                        $compare .= '<ul class="fileinfo">';
                        foreach ($arrSort as $keyOuter => $valueOuter)
                        {
                            $compare .= "<li>";
                            $compare .= '<strong>' . $keyOuter . '</strong>';
                            $compare .= "<ul>";
                            foreach ($valueOuter as $valueInner)
                            {
                                $compare .= "<li>" . $valueInner . "</li>";
                            }
                            $compare .= "</ul>";
                            $compare .= "</li>";
                        }
                        $compare .= "</ul>";
                    }

                    // Show filelist only in debug mode
                    if ($GLOBALS['TL_CONFIG']['syncCto_debug_mode'] == true)
                    {
                        if (count($this->arrListCompare) != 0 && is_array($this->arrListCompare))
                        {
                            // Send Part

                            $compare .= '<br /><p class="tl_help">' . $intSendCount . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_5'] . '</p>';

                            $arrSort = array();

                            if (($intSendCount - $intDelCount) != 0)
                            {
                                $compare .= '<ul class="fileinfo">';

                                $compare .= "<li>";
                                $compare .= '<strong>' . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_7'] . '</strong>';
                                $compare .= "<ul>";

                                foreach ($this->arrListCompare as $key => $value)
                                {
                                    if ($value["transmission"] != SyncCtoEnum::FILETRANS_SEND)
                                        continue;

                                    if ($value["state"] == SyncCtoEnum::FILESTATE_DELETE)
                                        continue;

                                    $compare .= "<li>";
                                    $compare .= (mb_check_encoding($value["path"], 'UTF-8')) ? $value["path"] : utf8_encode($value["path"]);
                                    $compare .= "</li>";
                                }
                                $compare .= "</ul>";
                                $compare .= "</li>";
                                $compare .= "</ul>";
                            }

                            //---------

                            if ($intDelCount != 0)
                            {
                                $compare .= '<ul class="fileinfo">';

                                $compare .= "<li>";
                                $compare .= '<strong>' . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_8'] . '</strong>';
                                $compare .= "<ul>";

                                foreach ($this->arrListCompare as $key => $value)
                                {
                                    if ($value["transmission"] != SyncCtoEnum::FILETRANS_SEND)
                                        continue;

                                    if ($value["state"] != SyncCtoEnum::FILESTATE_DELETE)
                                        continue;

                                    $compare .= "<li>";
                                    $compare .= (mb_check_encoding($value["path"], 'UTF-8')) ? $value["path"] : utf8_encode($value["path"]);
                                    $compare .= "</li>";
                                }
                                $compare .= "</ul>";
                                $compare .= "</li>";
                                $compare .= "</ul>";
                            }


                            // Not sended, still waiting

                            if ($intWaitCount != 0)
                            {
                                $compare .= '<br /><p class="tl_help">' . $intWaitCount . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_6'] . '</p>';

                                $arrSort = array();

                                $compare .= '<ul class="fileinfo">';

                                $compare .= "<li>";
                                $compare .= '<strong>' . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_9'] . '</strong>';
                                $compare .= "<ul>";

                                foreach ($this->arrListCompare as $key => $value)
                                {
                                    if ($value["transmission"] != SyncCtoEnum::FILETRANS_WAITING)
                                        continue;

                                    $compare .= "<li>";
                                    $compare .= (mb_check_encoding($value["path"], 'UTF-8')) ? $value["path"] : utf8_encode($value["path"]);
                                    $compare .= "</li>";
                                }
                                $compare .= "</ul>";
                                $compare .= "</li>";
                                $compare .= "</ul>";
                            }
                        }
                    }

                    $arrContenData["data"][5]["html"] = $compare;
                    break;
            }
        }
        catch (Exception $exc)
        {
            $this->log(vsprintf("Error on synchronization client ID %s", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "ERROR");

            $arrContenData["error"]            = true;
            $arrContenData["data"][5]["state"] = $GLOBALS['TL_LANG']['MSC']['error'];
            $arrContenData["error_msg"]        = $exc->getMessage();
        }

        $this->Template->goBack = $this->script . $arrContenData["goBack"];
        $this->Template->data = $arrContenData["data"];
        $this->Template->step = $this->intStep;
        $this->Template->error = $arrContenData["error"];
        $this->Template->error_msg = $arrContenData["error_msg"];
        $this->Template->refresh = $arrContenData["refresh"];
        $this->Template->url = $arrContenData["url"];
        $this->Template->start = $arrContenData["start"];
        $this->Template->headline = $arrContenData["headline"];
        $this->Template->information = $arrContenData["information"];
        $this->Template->finished = $arrContenData["finished"];

        $this->Session->set("syncCto_StepPool5", $mixStepPool);
        $this->Session->set("syncCto_Content", $arrContenData);
    }

    /*
     * End SyncCto Sync. From
     * -------------------------------------------------------------------------
     */
}

/**
 * Sort function
 * @param type $a
 * @param type $b
 * @return type 
 */
function syncCtoModelClientCMP($a, $b)
{
    if ($a["state"] == $b["state"])
    {
        return 0;
    }

    return ($a["state"] < $b["state"]) ? -1 : 1;
}

/* -----------------------------------------------------------------------------
 * Container Classes
 */

class StepPool
{

    protected $arrValues;
    protected $intStepID;

    /**
     *
     * @param type $arrStepPool 
     */
    public function __construct($arrStepPool, $intStepID)
    {
        $this->arrValues = $arrStepPool;
        $this->intStepID = $intStepID;
    }

    public function getArrValues()
    {
        return $this->arrValues;
    }

    public function setArrValues($arrValues)
    {
        $this->arrValues = $arrValues;
    }

    public function getIntStepID()
    {
        return $this->intStepID;
    }

    public function setIntStepID($intStepID)
    {
        $this->intStepID = $intStepID;
    }

    public function __get($name)
    {
        if ($this->arrValues == FALSE || !is_array($this->arrValues))
        {
            return null;
        }

        if (key_exists($name, $this->arrValues))
        {
            return $this->arrValues[$name];
        }
        else
        {
            throw new Exception("Unknown key in step pool.");
        }
    }

    public function __set($name, $value)
    {
        if ($this->arrValues == FALSE || !is_array($this->arrValues))
        {
            $this->arrValues = array();
        }

        return $this->arrValues[$name] = $value;
    }

}

class ContentData
{

    protected $arrValues;
    protected $intStep;

    /**
     *
     * @param type $arrContentData
     * @param type $intStep 
     */
    public function __construct($arrContentData, $intStep)
    {
        $this->arrValues = $arrContentData;

        if (!is_array($this->arrValues))
        {
            $this->arrValues = array();
        }

        $this->intStep = $intStep;
    }

    public function getArrValues()
    {
        return $this->arrValues;
    }

    public function setArrValues($arrValues)
    {
        $this->arrValues = $arrValues;
    }

    public function nextStep()
    {
        $this->intStep++;
    }

    public function getTitle()
    {
        return $this->arrValues[$this->intStep]["title"];
    }

    public function setTitle($title)
    {
        $this->arrValues[$this->intStep]["title"] = $title;
    }

    public function getState()
    {
        return $this->arrValues[$this->intStep]["state"];
    }

    public function setState($state)
    {
        $this->arrValues[$this->intStep]["state"] = $state;
    }

    public function getDescription()
    {
        return $this->arrValues[$this->intStep]["description"];
    }

    public function setDescription($description)
    {
        $this->arrValues[$this->intStep]["description"] = $description;
    }

    public function getMsg()
    {
        return $this->arrValues[$this->intStep]["msg"];
    }

    public function setMsg($msg)
    {
        $this->arrValues[$this->intStep]["msg"] = $msg;
    }

    public function getHtml()
    {
        return $this->arrValues[$this->intStep]["html"];
    }

    public function setHtml($html)
    {
        $this->arrValues[$this->intStep]["html"] = $html;
    }

    public function __get($name)
    {
        throw new Exception("Unknown key for datacontent $name");
    }

    public function __set($name, $value)
    {
        throw new Exception("Unknown key for datacontent $name");
    }

}

?>