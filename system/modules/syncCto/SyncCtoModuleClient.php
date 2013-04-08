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
     * Getter / Setter 
     */

    /**
     * @return int
     */
    public function getClientID()
    {
        return $this->intClientID;
    }

    /**
     * @return array
     */
    public function getSyncSettings()
    {
        return $this->arrSyncSettings;
    }

    /**
     * @param array $arrSyncSettings
     */
    public function setSyncSettings($arrSyncSettings)
    {
        $this->arrSyncSettings = $arrSyncSettings;
    }

    /**
     * @return StepPool
     */
    public function getStepPool()
    {
        return $this->objStepPool;
    }

    /**
     * @return ContentData
     */
    public function getData()
    {
        return $this->objData;
    }

    /**
     * Get the client informations
     * 
     * @return array
     */
    public function getClientInformation()
    {
        return $this->arrClientInformation;
    }

    // Template getter / setter ------------------------------------------------

    public function isError()
    {
        return $this->booError;
    }

    public function setError($booError)
    {
        $this->booError = $booError;
    }

    public function isAbort()
    {
        return $this->booAbort;
    }

    public function setAbort($booAbort)
    {
        $this->booAbort = $booAbort;
    }

    public function isFinished()
    {
        return $this->booFinished;
    }

    public function setFinished($booFinished)
    {
        $this->booFinished = $booFinished;
    }

    public function isRefresh()
    {
        return $this->booRefresh;
    }

    public function setRefresh($booRefresh)
    {
        $this->booRefresh = $booRefresh;
    }

    public function getErrorMsg()
    {
        return $this->strError;
    }

    public function setErrorMsg($strError)
    {
        $this->strError = $strError;
    }

    public function getUrl()
    {
        return $this->strUrl;
    }

    public function setUrl($strUrl)
    {
        $this->strUrl = $strUrl;
    }

    public function getGoBack()
    {
        return $this->strGoBack;
    }

    public function setGoBack($strGoBack)
    {
        $this->strGoBack = $strGoBack;
    }

    public function getHeadline()
    {
        return $this->strHeadline;
    }

    public function setHeadline($strHeadline)
    {
        $this->strHeadline = $strHeadline;
    }

    public function getInformation()
    {
        return $this->strInformation;
    }

    public function setInformation($strInformation)
    {
        $this->strInformation = $strInformation;
    }

    public function getStep()
    {
        return $this->intStep;
    }

    public function setStep($intStep)
    {
        $this->intStep = $intStep;
    }

    public function getStart()
    {
        return $this->floStart;
    }

    public function setStart($floStart)
    {
        $this->floStart = $floStart;
    }

    // Special getter / setter -------------------------------------------------

    public function addStep()
    {
        $this->intStep++;
    }

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

        // Load helper
        $this->objSyncCtoDatabase            = SyncCtoDatabase::getInstance();
        $this->objSyncCtoFiles               = SyncCtoFiles::getInstance();
        $this->objSyncCtoCommunicationClient = SyncCtoCommunicationClient::getInstance();
        $this->objSyncCtoHelper              = SyncCtoHelper::getInstance();

        // Load language 
        $this->loadLanguageFile("tl_syncCto_steps");
        $this->loadLanguageFile("tl_syncCto_check");

        // Load CSS
        $GLOBALS['TL_CSS'][] = 'system/modules/syncCto/html/css/steps.css';

        // Import
        $this->import("Backenduser", "User");
    }

    /**
     * Generate page
     */
    protected function compile()
    {
        // Check if start is set
        if ($this->Input->get("act") != "start" && $this->Input->get('table') != 'tl_syncCto_clients_showExtern')
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
            $arrClientInformations      = $this->objSyncCtoCommunicationClient->setClientBy(intval($this->Input->get("id")));
            $this->Template->clientName = $arrClientInformations["title"];
        }
        catch (Exception $exc)
        {
            $_SESSION["TL_ERROR"] = array($GLOBALS['TL_LANG']['ERR']['client_set']);
            $this->log($exc->getMessage(), __CLASS__ . " | " . __FUNCTION__, TL_ERROR);
            $this->redirect("contao/main.php?do=synccto_clients");
        }

        // Set template
        $this->Template->showControl  = true;
        $this->Template->tryAgainLink = $this->Environment->requestUri;
        $this->Template->abortLink    = $this->Environment->requestUri . "&abort=true";

        // Load content from session
        if ($this->intStep != 0)
        {
            $this->loadContenData();
        }

        // Load settings from dca
        $this->loadSyncSettings();
        $this->loadClientInformation();

        // Set time out for database. Ticket #2653
        if ($GLOBALS['TL_CONFIG']['syncCto_custom_settings'] == true
                && intval($GLOBALS['TL_CONFIG']['syncCto_wait_timeout']) > 0
                && intval($GLOBALS['TL_CONFIG']['syncCto_interactive_timeout']) > 0)
        {
            $this->Database->query('SET SESSION wait_timeout = GREATEST(' . intval($GLOBALS['TL_CONFIG']['syncCto_wait_timeout']) . ', @@wait_timeout), SESSION interactive_timeout = GREATEST(' . intval($GLOBALS['TL_CONFIG']['syncCto_interactive_timeout']) . ', @@wait_timeout);');
        }
        else
        {
            $this->Database->query('SET SESSION wait_timeout = GREATEST(28000, @@wait_timeout), SESSION interactive_timeout = GREATEST(28000, @@wait_timeout);');
        }

        if ($this->Input->get("abort") == "true")
        {
            // Load content from session
            $this->loadContenData();
            // So abort page
            $this->pageSyncAbort();
            // Save content in session
            $this->saveContentData();
            // Set template vars
            $this->setTemplateVars();
            // Hidden control
            $this->Template->showControl = false;
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

            case "tl_syncCto_clients_showExtern":
                $this->pageShowExtern();
                break;

            default :
                $_SESSION["TL_ERROR"][] = $GLOBALS['TL_LANG']['ERR']['unknown_function'];
                $this->redirect("contao/main.php?do=synccto_clients");
                break;
        }

        // Save content in session
        $this->saveContentData();
        $this->saveClientInformation();
        $this->saveSyncSettings();

        // Set Vars for the template
        $this->setTemplateVars();
    }

    /* -------------------------------------------------------------------------
     * Helper function for session/tempfiles etc.
     */

    protected function setTemplateVars()
    {
        // Set Tempalte
        $this->Template->goBack      = $this->strGoBack;
        $this->Template->data        = $this->objData->getArrValues();
        $this->Template->step        = $this->intStep;
        $this->Template->subStep     = $this->objStepPool->step;
        $this->Template->error       = $this->booError;
        $this->Template->error_msg   = $this->strError;
        $this->Template->refresh     = $this->booRefresh;
        $this->Template->url         = $this->strUrl;
        $this->Template->start       = $this->floStart;
        $this->Template->headline    = $this->strHeadline;
        $this->Template->information = $this->strInformation;
        $this->Template->finished    = $this->booFinished;
        
        if ($this->Input->get('table') == 'tl_syncCto_clients_syncTo')
        {
            $this->Template->direction = 'to';
        }
        else if ($this->Input->get('table') == 'tl_syncCto_clients_syncFrom')
        {
            $this->Template->direction = 'from';
        }
        else
        {
            $this->Template->direction = 'na';
        }
    }

    /**
     * Save the current state of the page/sychronization 
     */
    protected function saveContentData()
    {
        $arrContenData = array(
            "error"       => $this->booError,
            "error_msg"   => $this->strError,
            "refresh"     => $this->booRefresh,
            "finished"    => $this->booFinished,
            "step"        => $this->intStep,
            "url"         => $this->strUrl,
            "goBack"      => $this->strGoBack,
            "start"       => $this->floStart,
            "headline"    => $this->strHeadline,
            "information" => $this->strInformation,
            "data"        => $this->objData->getArrValues(),
            "abort"       => $this->booAbort,
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
            $this->booError       = $arrContenData["error"];
            $this->booAbort       = $arrContenData["abort"];
            $this->booFinished    = $arrContenData["finished"];
            $this->booRefresh     = $arrContenData["refresh"];
            $this->strError       = $arrContenData["error_msg"];
            $this->strUrl         = $arrContenData["url"];
            $this->strGoBack      = $arrContenData["goBack"];
            $this->strHeadline    = $arrContenData["headline"];
            $this->strInformation = $arrContenData["information"];
            $this->intStep        = $arrContenData["step"];
            $this->floStart       = $arrContenData["start"];
            $this->objData        = new ContentData($arrContenData["data"], $this->intStep);
        }
        else
        {
            $this->booError       = false;
            $this->booAbort       = false;
            $this->booFinished    = false;
            $this->booRefresh     = false;
            $this->strError       = "";
            $this->strUrl         = "";
            $this->strGoBack      = "";
            $this->strHeadline    = "";
            $this->strInformation = "";
            $this->intStep        = 0;
            $this->floStart       = 0;
            $this->objData        = new ContentData(array(), $this->intStep);
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
        if (!key_exists('syncCto_SyncDatabase', $this->arrSyncSettings))
        {
            return false;
        }

        return $this->arrSyncSettings['syncCto_SyncDatabase'];
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
            $this->booError       = false;
            $this->booAbort       = false;
            $this->booFinished    = false;
            $this->strError       = "";
            $this->booRefresh     = true;
            $this->strUrl         = "contao/main.php?do=synccto_clients&amp;table=tl_syncCto_clients_syncTo&amp;act=start&amp;id=" . $this->intClientID;
            $this->strGoBack      = $this->Environment->base . "contao/main.php?do=synccto_clients";
            $this->strHeadline    = $GLOBALS['TL_LANG']['tl_syncCto_sync']['edit'];
            $this->strInformation = "";
            $this->intStep        = 1;
            $this->floStart       = microtime(true);
            $this->objData        = new ContentData(array(), $this->intStep);
            
            // Init tmep files
            $this->initTempLists();

            // Update last sync
            $this->Database->prepare("UPDATE `tl_synccto_clients` %s WHERE `tl_synccto_clients`.`id` = ?")
                    ->set(array("syncTo_user"   => $this->User->id, "syncTo_tstamp" => time()))
                    ->execute($this->intClientID);

            // Write log
            $this->log(vsprintf("Start synchronization client ID %s.", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "INFO");

            // Reset some Sessions
            $this->resetStepPoolByID(array(1, 2, 3, 4, 5, 6, 7));
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
                $this->loadTempLists();

                if (!$this->checkSyncFileList())
                {
                    $this->intStep++;
                    $this->objData->nextStep();
                }
                else if (count($this->arrListCompare) == 0)
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

            // Check if we have pro features
            case 5:
                if (in_array('syncCtoPro', Config::getInstance()->getActiveModules()))
                {
                    $objStepPro = SyncCtoStepDatabaseDiff::getInstance();
                    $objStepPro->setSyncCto($this);

                    if (!$objStepPro->checkSyncTo())
                    {
                        $this->intStep++;
                        $this->objData->nextStep();
                    }
                    else
                    {
                        break;
                    }
                }
                else
                {
                    $this->intStep++;
                    $this->objData->nextStep();
                }

            // Check uf we have to run the import step
            case 6:
                $this->loadTempLists();

                if (count((array) $this->arrListCompare) == 0
                        && !in_array("localconfig_update", $this->arrSyncSettings["syncCto_Type"])
                        && $this->arrSyncSettings["syncCto_ShowError"] != true
                        && $this->arrSyncSettings["syncCto_AttentionFlag"] != true
                        && count((array) $this->arrSyncSettings["syncCto_Systemoperations_Maintenance"]) == 0
                        && !in_array("temp_folders", $this->arrSyncSettings["syncCto_Systemoperations_Maintenance"])
                )
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
                $this->pageSyncShowStep1();
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

            // Run pro features
            case 5:
                $this->pageSyncToShowStepPro();
                break;

            // Import Files | Import Config | etc.
            case 6:
                $this->loadTempLists();
                $this->pageSyncToShowStep6();
                $this->saveTempLists();
                break;

            // Cleanup | Show informations
            case 7:
                $this->loadTempLists();
                $this->pageSyncToShowStep7();
                $this->saveTempLists();
                break;

            default:
                $_SESSION["TL_ERROR"] = array("Unknown step for sync.");
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
        // Init | Set Step to 1
        if ($this->intStep == 0)
        {
            // Init content
            $this->booError       = false;
            $this->booAbort       = false;
            $this->booFinished    = false;
            $this->strError       = "";
            $this->booRefresh     = true;
            $this->strUrl         = "contao/main.php?do=synccto_clients&amp;table=tl_syncCto_clients_syncFrom&amp;act=start&amp;id=" . $this->intClientID;
            $this->strGoBack      = $this->Environment->base . "contao/main.php?do=synccto_clients";
            $this->strHeadline    = $GLOBALS['TL_LANG']['tl_syncCto_sync']['edit'];
            $this->strInformation = "";
            $this->intStep        = 1;
            $this->floStart       = microtime(true);
            $this->objData        = new ContentData(array(), $this->intStep);

            // Init tmep files
            $this->initTempLists();

            // Update last sync
            $this->Database->prepare("UPDATE `tl_synccto_clients` %s WHERE `tl_synccto_clients`.`id` = ?")
                    ->set(array("syncFrom_user"   => $this->User->id, "syncFrom_tstamp" => time()))
                    ->execute($this->intClientID);

            // Write log
            $this->log(vsprintf("Start synchronization server with client ID %s.", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "INFO");

            // Reset some Sessions
            $this->resetStepPoolByID(array(1, 2, 3, 4, 5, 6, 7));
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
                $this->loadTempLists();

                if (!$this->checkSyncFileList())
                {
                    $this->intStep++;
                    $this->objData->nextStep();
                }
                else if (count($this->arrListCompare) == 0)
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


            // Check if we have pro features
            case 5:
                if (in_array('syncCtoPro', Config::getInstance()->getActiveModules()))
                {
                    $objStepPro = SyncCtoStepDatabaseDiff::getInstance();
                    $objStepPro->setSyncCto($this);

                    if (!$objStepPro->checkSyncTo())
                    {
                        $this->intStep++;
                        $this->objData->nextStep();
                    }
                    else
                    {
                        break;
                    }
                }
                else
                {
                    $this->intStep++;
                    $this->objData->nextStep();
                }

            // Check if we have to run the import step
            case 6:
                $this->loadTempLists();

                if (count((array) $this->arrListCompare) == 0
                        && !in_array("localconfig_update", $this->arrSyncSettings["syncCto_Type"])
                        && $this->arrSyncSettings["syncCto_AttentionFlag"] != true
                        && count((array) $this->arrSyncSettings["syncCto_Systemoperations_Maintenance"]) == 0
                        && !in_array("temp_folders", $this->arrSyncSettings["syncCto_Systemoperations_Maintenance"])
                )
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
                $this->pageSyncShowStep1();
                break;

            // Filelists
            case 2:
                $this->loadTempLists();
                $this->pageSyncFromShowStep2();
                $this->saveTempLists();
                break;

            // File send 
            case 3:
                $this->loadTempLists();
                $this->pageSyncFromShowStep3();
                $this->saveTempLists();
                break;

            // Database
            case 4:
                $this->pageSyncFromShowStep4();
                break;

            // Run pro features
            case 5:
                $this->pageSyncFromShowStepPro();
                break;

            // Import Files | Import Config | etc.
            case 6:
                $this->loadTempLists();
                $this->pageSyncFromShowStep6();
                $this->saveTempLists();
                break;

            // Show informations
            case 7:
                $this->loadTempLists();
                $this->pageSyncFromShowStep7();
                $this->saveTempLists();
                break;

            default:
                $_SESSION["TL_ERROR"] = array("Unknown step for sync.");
                $this->redirect("contao/main.php?do=synccto_clients");
                break;
        }

        // Save step pool for current step
        $this->saveStepPool();
    }

    /**
     * Setup for page showExtern
     */
    private function pageShowExtern()
    {
        // Init | Set Step to 1
        if ($this->intStep == 0)
        {
            // Init content
            $this->booError       = false;
            $this->booAbort       = false;
            $this->booFinished    = false;
            $this->strError       = "";
            $this->booRefresh     = true;
            $this->strUrl         = "contao/main.php?do=synccto_clients&amp;table=tl_syncCto_clients_showExtern&amp;act=start&amp;id=" . $this->intClientID;
            $this->strGoBack      = $this->Environment->base . "contao/main.php?do=synccto_clients";
            $this->strHeadline    = $GLOBALS['TL_LANG']['tl_syncCto_check']['check'];
            $this->strInformation = "";
            $this->intStep        = 1;
            $this->floStart       = microtime(true);
            $this->objData        = new ContentData(array(), $this->intStep);

            // Init tmep files
            $this->initTempLists();

            // Reset some Sessions
            $this->resetStepPoolByID(array(1, 2, 3, 4, 5, 6, 7));
            $this->resetClientInformation();
        }

        // Load step pool for current step
        $this->loadStepPool();

        /* ---------------------------------------------------------------------
         * Run page
         */

        // Init
        if ($this->objStepPool->step == null)
        {
            $this->objStepPool->step = 1;
        }

        // Set content back to normale mode
        $this->strError = "";
        $this->booError = false;
        $this->objData->setState(SyncCtoEnum::WORK_WORK);

        try
        {
            switch ($this->objStepPool->step)
            {
                /**
                 * Show step
                 */
                case 1:
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1_show"]['description_1']);
                    $this->objData->setState(SyncCtoEnum::WORK_WORK);

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
                 * Get informations and close connection
                 */
                case 3:
                    // Get infomations
                    $arrConfigurations = $this->objSyncCtoCommunicationClient->getPhpConfigurations();
                    $arrFunctions      = $this->objSyncCtoCommunicationClient->getPhpFunctions();
                    $arrProFunctions   = $this->objSyncCtoCommunicationClient->getProFunctions();
                    $strVersion        = $this->objSyncCtoCommunicationClient->getVersionSyncCto();

                    // Stop connection
                    $this->objSyncCtoCommunicationClient->stopConnection();

                    // Load module for html
                    $objCheck                                = new SyncCtoModuleCheck();
                    $objCheckTemplate                        = new BackendTemplate('be_syncCto_smallCheck');
                    $objCheckTemplate->checkPhpConfiguration = $objCheck->checkPhpConfiguration($arrConfigurations);
                    $objCheckTemplate->checkPhpFunctions     = $objCheck->checkPhpFunctions($arrFunctions);
                    $objCheckTemplate->checkProFunctions     = $objCheck->checkProFunctions($arrProFunctions);
                    $objCheckTemplate->syc_version           = $strVersion;

                    // Show information
                    $this->objData->setState(SyncCtoEnum::WORK_OK);
                    $this->objData->setHtml($objCheckTemplate->parse());

                    $this->booFinished           = true;
                    $this->booRefresh            = false;
                    $this->Template->showControl = false;

                    $this->objStepPool->step++;

                case 4:
                    break;
            }
        }
        catch (Exception $exc)
        {
            $this->log(vsprintf("Error on synchronization client ID %s", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "ERROR");

            $this->booError = true;
            $this->strError = $exc->getMessage();

            $this->objData->setState(SyncCtoEnum::WORK_ERROR);
        }

        // Save step pool for current step
        $this->saveStepPool();
    }

    /* -------------------------------------------------------------------------
     * Step function for SyncTo AND SyncFrom
     */

    /**
     * Start the connection and save some parameter to session
     */
    private function pageSyncShowStep1()
    {
        // Init
        if ($this->objStepPool->step == null)
        {
            $this->objStepPool->step = 1;
        }

        // Set content back to normale mode
        $this->strError = "";
        $this->booError = false;
        $this->objData->setState(SyncCtoEnum::WORK_WORK);

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
                    $this->objData->setState(SyncCtoEnum::WORK_WORK);

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
                        $this->objData->setState(SyncCtoEnum::WORK_ERROR);
                        $this->booError = true;
                        $this->strError = $GLOBALS['TL_LANG']['ERR']['referer'];

                        break;
                    }

                    $this->objStepPool->step++;
                    break;

                /**
                 * Check version
                 */
                case 4:
                    // Check syncCto
                    $strVersion                                    = $this->objSyncCtoCommunicationClient->getVersionSyncCto();
                    $this->arrClientInformation["version_SyncCto"] = $strVersion;

                    if (version_compare($strVersion, $GLOBALS['SYC_VERSION'], "="))
                    {
                        $this->objStepPool->autoUpdate = false;
                    }
                    else
                    {
                        $this->objStepPool->autoUpdate = true;
                    }

                    // Check Contao
                    $strVersion                                   = $this->objSyncCtoCommunicationClient->getVersionContao();
                    $this->arrClientInformation["version_Contao"] = $strVersion;
                    $strVersion                                   = trimsplit(".", $strVersion);
                    $strVersion                                   = $strVersion[0];

                    $strCurrentVersion = trimsplit(".", VERSION);
                    $strCurrentVersion = $strCurrentVersion[0];

                    if ($strVersion != $strCurrentVersion)
                    {
                        $this->log(vsprintf("Not the same version from contao on synchronization client ID %s. Serverversion: %s. Clientversion: %s", array($this->Input->get("id"), $GLOBALS['SYC_VERSION'], $strVersion)), __CLASS__ . " " . __FUNCTION__, "GENERAL");

                        $this->objData->setState(SyncCtoEnum::WORK_ERROR);
                        $this->booError = true;
                        $this->strError = vsprintf($GLOBALS['TL_LANG']['ERR']['version'], array("Contao", $strCurrentVersion, $strVersion));
                        break;
                    }

                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_2']);

                    $this->objStepPool->step++;
                    break;

                /**
                 * Clear client and server temp folder  
                 */
                case 5:
                    $this->objSyncCtoCommunicationClient->purgeTempFolder();
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
                    $arrFolders                            = $this->objSyncCtoCommunicationClient->getPathList();
                    $this->arrClientInformation["folders"] = $arrFolders;

                    // Get parameter for upload and co
                    $arrClientParameter = $this->objSyncCtoCommunicationClient->getClientParameter();

                    $this->arrClientInformation["upload_Parameter"] = $arrClientParameter;

                    // Check if everything is okay
                    if ($arrClientParameter['file_uploads'] != 1)
                    {
                        $this->objData->setState(SyncCtoEnum::WORK_ERROR);
                        $this->booError = true;
                        $this->strError = $GLOBALS['TL_LANG']['ERR']['upload_ini'];

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
                        $intPercent = 80;
                    }

                    $intLimit = $intLimit / 100 * $intPercent;

                    $this->arrClientInformation["upload_sizeLimit"]   = $intLimit;
                    $this->arrClientInformation["upload_sizePercent"] = $intPercent;

                    if ($this->objStepPool->autoUpdate == false)
                    {
                        $this->objStepPool->step = 11;
                    }
                    else
                    {
                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_1']['description_3']);
                        $this->objStepPool->step++;
                    }

                    break;

                /**
                 * Auto Updater
                 */
                case 7:
                    $objSyncCtoUpdater = SyncCtoUpdater::getInstance();
                    $strZipPath        = $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "autoupdater", "autoupdate_" . time() . ".zip");
                    new Folder(dirname($strZipPath));
                    $objSyncCtoUpdater->buildUpdateZip($strZipPath);

                    $this->objStepPool->AutoUpdateZip = $strZipPath;
                    $this->objStepPool->step++;

                    break;

                // Send files
                case 8:
                    $arrFiles = array(
                        "system/modules/syncCtoUpdater/SyncCtoAutoUpdater.php",
                        "system/modules/syncCtoUpdater/config/config.php",
                        "system/modules/syncCtoUpdater/config/.htaccess",
                        $this->objStepPool->AutoUpdateZip
                    );

                    foreach ($arrFiles as $value)
                    {
                        $this->objSyncCtoCommunicationClient->sendFile(dirname($value), basename($value), "", SyncCtoEnum::UPLOAD_SYNC_TEMP);
                    }

                    $this->objStepPool->step++;

                    break;

                // Import files    
                case 9:
                    $arrFiles = array(
                        "system/modules/syncCtoUpdater/SyncCtoAutoUpdater.php",
                        "system/modules/syncCtoUpdater/config/config.php",
                        "system/modules/syncCtoUpdater/config/.htaccess",
                        $this->objStepPool->AutoUpdateZip
                    );

                    $arrImport = array();

                    foreach ($arrFiles as $value)
                    {
                        $strChecksum = md5(TL_ROOT . "/" . $value);

                        $arrImport[$strChecksum] = array(
                            "path"         => $value,
                            "checksum"     => $strChecksum,
                            "size"         => 0,
                            "state"        => SyncCtoEnum::FILESTATE_FILE,
                            "transmission" => SyncCtoEnum::FILETRANS_WAITING,
                        );
                    }

                    $this->objSyncCtoCommunicationClient->runFileImport($arrImport);

                    $this->objStepPool->step++;

                    break;

                // Start update
                case 10:
                    $this->objSyncCtoCommunicationClient->startAutoUpdater($this->objStepPool->AutoUpdateZip);
                    $this->objStepPool->step++;
					break;

				// Check pathconfig for contao 2.11.10 =<
				case 11:					
					if(version_compare(VERSION . '.' . BUILD, '2.11.10', '>='))
					{
						$this->objSyncCtoCommunicationClient->createPathconfig();
						$this->objStepPool->step++;
						break;
					}

                case 12:
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_1']);
                    $this->objData->setState(SyncCtoEnum::WORK_OK);
                    $this->intStep++;
                    break;
            }
        }
        catch (Exception $exc)
        {
            $this->log(vsprintf("Error on synchronization client ID %s with msg: %s", array($this->Input->get("id"), $exc->getMessage())), __CLASS__ . " " . __FUNCTION__, "ERROR");

            $this->booError = true;
            $this->strError = $exc->getMessage();

            $this->objData->setState(SyncCtoEnum::WORK_ERROR);
        }
    }

    /**
     * Abort function
     */
    private function pageSyncAbort()
    {
        if ($this->booAbort == false)
        {
            // Set content back to normale mode
            $this->booError   = false;
            $this->strError   = "";
            $this->booAbort   = true;
            $this->booRefresh = false;

            // Reset Session
            $this->resetStepPoolByID(array(1, 2, 3, 4, 5, 6));

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

            // Set stepe
            $this->intStep = 99;

            // Set last to skipped        
            $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
            $this->objData->setHtml("");

            // Set Abort information 
            $this->objData->setStep(99);
            $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['abort']);
            $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['abort']);
            $this->objData->setState(SyncCtoEnum::WORK_ERROR);
        }
    }

    /* -------------------------------------------------------------------------
     * Start SyncCto syncTo
     */

    /**
     * Pro Version - Sync To
     */
    private function pageSyncToShowStepPro()
    {
        $objStepPro = SyncCtoStepDatabaseDiff::getInstance();
        $objStepPro->setSyncCto($this);
        $objStepPro->syncTo();
    }

    /**
     * Pro Version - Sync From
     */
    private function pageSyncFromShowStepPro()
    {
        $objStepPro = SyncCtoStepDatabaseDiff::getInstance();
        $objStepPro->setSyncCto($this);
        $objStepPro->syncFrom();
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
        $this->objData->setState(SyncCtoEnum::WORK_WORK);

        // Run page
        try
        {
            switch ($this->objStepPool->step)
            {
                /**
                 * Show step
                 */
                case 1:
                    $this->objData->setState(SyncCtoEnum::WORK_WORK);
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
                        $arrChecksumClient    = $this->objSyncCtoCommunicationClient->getChecksumCore();
                        $this->arrListCompare = array_merge($this->arrListCompare, $this->objSyncCtoFiles->checkDeleteFiles($arrChecksumClient));
                        $this->objStepPool->step++;
                        break;
                    }

                case 6:
                    if (in_array("user_delete", $this->arrSyncSettings["syncCto_Type"]))
                    {
                        $arrChecksumClient    = $this->objSyncCtoCommunicationClient->getChecksumFiles();
                        $this->arrListCompare = array_merge($this->arrListCompare, $this->objSyncCtoFiles->checkDeleteFiles($arrChecksumClient));

                        $this->objStepPool->step++;
                        break;
                    }

                // Check folders
                case 7:
                    if (in_array("core_delete", $this->arrSyncSettings["syncCto_Type"]))
                    {
                        $arrChecksumClient    = $this->objSyncCtoCommunicationClient->getChecksumFolderCore();
                        $this->arrListCompare = array_merge($this->arrListCompare, $this->objSyncCtoFiles->searchDeleteFolders($arrChecksumClient));

                        $this->objStepPool->step++;
                        break;
                    }

                case 8:
                    if (in_array("user_delete", $this->arrSyncSettings["syncCto_Type"]))
                    {
                        $arrChecksumClient    = $this->objSyncCtoCommunicationClient->getChecksumFolderFiles();
                        $this->arrListCompare = array_merge($this->arrListCompare, $this->objSyncCtoFiles->searchDeleteFolders($arrChecksumClient));
                    }

                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_3']);
                    $this->objStepPool->step++;
                    break;

                /**
                 * Set CSS and search for bigfiles
                 */
                case 9:
                    foreach ($this->arrListCompare as $key => $value)
                    {
                        switch ($value["state"])
                        {
                            case SyncCtoEnum::FILESTATE_BOMBASTIC_BIG:
                                $this->arrListCompare[$key]["css"]     = "unknown";
                                $this->arrListCompare[$key]["css_big"] = "ignored";
                                break;

                            case SyncCtoEnum::FILESTATE_TOO_BIG_NEED:
                                $this->arrListCompare[$key]["css_big"] = "ignored";
                            case SyncCtoEnum::FILESTATE_NEED:
                                $this->arrListCompare[$key]["css"]     = "modified";
                                break;

                            case SyncCtoEnum::FILESTATE_TOO_BIG_MISSING:
                                $this->arrListCompare[$key]["css_big"] = "ignored";
                            case SyncCtoEnum::FILESTATE_MISSING:
                                $this->arrListCompare[$key]["css"]     = "new";
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
                 * Show files form
                 */
                case 10:
                    // Counter
                    $intCountMissing = 0;
                    $intCountNeed    = 0;
                    $intCountIgnored = 0;
                    $intCountDelete  = 0;

                    $intTotalSizeNew    = 0;
                    $intTotalSizeDel    = 0;
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
                            case SyncCtoEnum::FILESTATE_FOLDER_DELETE:
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

                    $this->objStepPool->missing = $intCountMissing;
                    $this->objStepPool->need    = $intCountNeed;
                    $this->objStepPool->ignored = $intCountIgnored;
                    $this->objStepPool->delete  = $intCountDelete;

                    // Save files and go on or skip here
                    if ($intCountMissing == 0 && $intCountNeed == 0 && $intCountIgnored == 0 && $intCountDelete == 0)
                    {
                        // Set current step informations
                        $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1']);
                        $this->objData->setHtml("");
                        $this->booRefresh = true;
                        $this->intStep++;

                        break;
                    }
                    else if (count($this->arrListCompare) == 0 || key_exists("skip", $_POST))
                    {
                        $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1']);
                        $this->objData->setHtml("");
                        $this->booRefresh = true;
                        $this->intStep++;

                        $this->arrListCompare = array();

                        break;
                    }
                    else if (key_exists("forward", $_POST) && count($this->arrListCompare) != 0)
                    {
                        $this->objData->setState(SyncCtoEnum::WORK_OK);
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_4'], array($intCountMissing, $intCountNeed, $intCountDelete, $intCountIgnored, $this->getReadableSize($intTotalSizeNew), $this->getReadableSize($intTotalSizeChange), $this->getReadableSize($intTotalSizeDel))));
                        $this->objData->setHtml("");
                        $this->booRefresh = true;
                        $this->intStep++;

                        break;
                    }

                    $objTemp                 = new BackendTemplate("be_syncCto_form");
                    $objTemp->id             = $this->intClientID;
                    $objTemp->step           = $this->intStep;
                    $objTemp->direction      = "To";
                    $objTemp->headline       = $GLOBALS['TL_LANG']['MSC']['totalsize'];
                    $objTemp->cssId          = 'syncCto_filelist_form';
                    $objTemp->forwardValue   = $GLOBALS['TL_LANG']['MSC']['apply'];
                    $objTemp->popupClassName = 'popupSyncFiles.php';

                    // Build content 
                    $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_4'], array($intCountMissing, $intCountNeed, $intCountDelete, $intCountIgnored, $this->getReadableSize($intTotalSizeNew), $this->getReadableSize($intTotalSizeChange), $this->getReadableSize($intTotalSizeDel))));
                    $this->objData->setHtml($objTemp->parse());
                    $this->booRefresh = false;

                    break;
            }
        }
        catch (Exception $exc)
        {
            // If an error occurs skip the whole step
            $this->arrListCompare = array();

            $objErrTemplate              = new BackendTemplate('be_syncCto_error');
            $objErrTemplate->strErrorMsg = $exc->getMessage();

            $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
            $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1']);
            $this->objData->setHtml($objErrTemplate->parse());
            $this->booRefresh = true;
            $this->intStep++;

            $this->log(vsprintf("Error on synchronization client ID %s with msg: %s", array($this->Input->get("id"), $exc->getMessage())), __CLASS__ . " " . __FUNCTION__, "ERROR");
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
        $this->objData->setState(SyncCtoEnum::WORK_WORK);

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

                if ($value["state"] == SyncCtoEnum::FILESTATE_DELETE || $value["state"] == SyncCtoEnum::FILESTATE_FOLDER_DELETE)
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
            $intStart = time();

            switch ($this->objStepPool->step)
            {
                /**
                 * Show step
                 */
                case 1:
                    $this->objData->setState(SyncCtoEnum::WORK_WORK);
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_1']);

                    $this->objStepPool->step++;
                    break;

                /**
                 * Send normal files
                 */
                case 2:
                    // Send allfiles exclude the big ones
                    $intCountTransfer = 1;

                    foreach ($this->arrListCompare as $key => $value)
                    {
                        if ($value["transmission"] == SyncCtoEnum::FILETRANS_SEND || $value["transmission"] == SyncCtoEnum::FILETRANS_SKIPPED)
                        {
                            continue;
                        }

                        if (in_array($value["state"], array(SyncCtoEnum::FILESTATE_DELETE, SyncCtoEnum::FILESTATE_FOLDER_DELETE)))
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
                            $this->arrListCompare[$key]["skipreason"]   = $GLOBALS['TL_LANG']['ERR']['maximum_filesize'];
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
                            $this->arrListCompare[$key]["skipreason"]   = $exc->getMessage();
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
                        $this->objData->setState(SyncCtoEnum::WORK_OK);
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
                        $this->arrListCompare[$key]["splitname"]  = $key;

                        break;
                    }

                    if ($intCount != $intCountSplit)
                    {
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_6'], array($intCount, $intCountSplit)));
                    }
                    else
                    {
                        $this->objStepPool->step++;
                        $this->objData->setState(SyncCtoEnum::WORK_WORK);
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_4'], array($intCount, $intCountSplit)));
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

                        if (in_array($value["state"], array(
                                    SyncCtoEnum::FILESTATE_TOO_BIG_DELETE,
                                    SyncCtoEnum::FILESTATE_TOO_BIG_MISSING,
                                    SyncCtoEnum::FILESTATE_TOO_BIG_NEED,
                                    SyncCtoEnum::FILESTATE_TOO_BIG_SAME,
                                    SyncCtoEnum::FILESTATE_BOMBASTIC_BIG,
                                    SyncCtoEnum::FILESTATE_DELETE,
                                    SyncCtoEnum::FILESTATE_FOLDER_DELETE
                                )))
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
                            if ($intStart + 30 < time())
                            {
                                break;
                            }
                        }

                        break;
                    }

                    if ($intCount != $intCountSplit)
                    {
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_7'], array($intCount, $intCountSplit)));
                    }
                    else
                    {
                        $this->objStepPool->step++;
                        $this->objData->setState(SyncCtoEnum::WORK_WORK);
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

                        if (in_array($value["state"], array(
                                    SyncCtoEnum::FILESTATE_TOO_BIG_DELETE,
                                    SyncCtoEnum::FILESTATE_TOO_BIG_MISSING,
                                    SyncCtoEnum::FILESTATE_TOO_BIG_NEED,
                                    SyncCtoEnum::FILESTATE_TOO_BIG_SAME,
                                    SyncCtoEnum::FILESTATE_BOMBASTIC_BIG,
                                    SyncCtoEnum::FILESTATE_DELETE,
                                    SyncCtoEnum::FILESTATE_FOLDER_DELETE
                                )))
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
                            throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['rebuild'], array($value["path"])));
                        }

                        $this->arrListCompare[$key]["transmission"] = SyncCtoEnum::FILETRANS_SEND;

                        break;
                    }

                    if ($intCount != $intCountSplit)
                    {
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_8'], array($intCount, $intCountSplit)));
                    }
                    else
                    {
                        $this->objData->setState(SyncCtoEnum::WORK_OK);
                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_1']);

                        $this->intStep++;
                    }

                    break;
            }
        }
        catch (Exception $exc)
        {
            // If an error occurs skip the whole step
            $objErrTemplate              = new BackendTemplate('be_syncCto_error');
            $objErrTemplate->strErrorMsg = $exc->getMessage();

            $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
            $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_1']);
            $this->objData->setHtml($objErrTemplate->parse());
            $this->booRefresh = true;
            $this->intStep++;

            $this->log(vsprintf("Error on synchronization client ID %s with msg: %s", array($this->Input->get("id"), $exc->getMessage())), __CLASS__ . " " . __FUNCTION__, "ERROR");
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

                    $this->objData->setState(SyncCtoEnum::WORK_WORK);
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_1']);
                    $this->objStepPool->step++;

                    break;

                case 2:
                    // Check user
                    if ($this->User->isAdmin || $this->User->syncCto_tables != null)
                    {
                        // Load allowed tables for this user
                        if ($this->User->isAdmin)
                        {
                            $arrAllowedTables = true;
                        }
                        else
                        {
                            $arrAllowedTables = $this->User->syncCto_tables;
                        }

                        $arrClientTableR    = $this->objSyncCtoCommunicationClient->getRecommendedTables();
                        $arrClientTableNR   = $this->objSyncCtoCommunicationClient->getNoneRecommendedTables();
                        $arrClientTableH    = $this->objSyncCtoCommunicationClient->getHiddenTables();
                        $arrClientTimestamp = $this->objSyncCtoCommunicationClient->getClientTimestamp(array());

                        $arrServerTableR    = $this->objSyncCtoHelper->databaseTablesRecommended();
                        $arrServerTableNR   = $this->objSyncCtoHelper->databaseTablesNoneRecommended();
                        $arrServerTableH    = $this->objSyncCtoHelper->getTablesHidden();
                        $arrServerTimestamp = $this->objSyncCtoHelper->getDatabaseTablesTimestamp();

                        // clean up tables. Use user rights.
                        if ($arrAllowedTables !== true)
                        {
                            foreach ($arrClientTableR as $key => $value)
                            {
                                if (!in_array($value['name'], $arrAllowedTables))
                                {
                                    unset($arrClientTableR[$key]);
                                }
                            }

                            foreach ($arrClientTableNR as $key => $value)
                            {
                                if (!in_array($value['name'], $arrAllowedTables))
                                {
                                    unset($arrClientTableNR[$key]);
                                }
                            }

                            foreach ($arrClientTableH as $key => $value)
                            {
                                if (!in_array($value['name'], $arrAllowedTables))
                                {
                                    unset($arrClientTableH[$key]);
                                }
                            }

                            foreach ($arrServerTableR as $key => $value)
                            {
                                if (!in_array($value['name'], $arrAllowedTables))
                                {
                                    unset($arrServerTableR[$key]);
                                }
                            }

                            foreach ($arrServerTableNR as $key => $value)
                            {
                                if (!in_array($value['name'], $arrAllowedTables))
                                {
                                    unset($arrServerTableNR[$key]);
                                }
                            }

                            foreach ($arrServerTableH as $key => $value)
                            {
                                if (!in_array($value['name'], $arrAllowedTables))
                                {
                                    unset($arrServerTableH[$key]);
                                }
                            }
                        }

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
                        $arrAllTimeStamps = $this->objSyncCtoDatabase->getAllTimeStamps($arrServerTimestamp, $arrClientTimestamp, $this->intClientID);

                        $arrCompareList = $this->objSyncCtoDatabase->getFormatedCompareList($arrServerTables, $arrClientTables, $arrHiddenTables, $arrAllTimeStamps['server'], $arrAllTimeStamps['client'], $arrAllowedTables, 'server', 'client');

                        if (count($arrCompareList['recommended']) == 0 && count($arrCompareList['none_recommended']) == 0)
                        {
                            $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
                            $this->objData->setHtml("");
                            $this->intStep++;

                            break;
                        }

                        $this->arrSyncSettings['syncCto_CompareTables'] = $arrCompareList;

                        $this->objStepPool->step++;
                    }
                    else
                    {
                        $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
                        $this->objData->setHtml("");
                        $this->intStep++;
                    }

                    break;

                case 3:
                    // Unset some tables for pro feature
                    if (key_exists("forward", $_POST) && $this->arrSyncSettings['post_data']['database_pages_check'] == true)
                    {
                        if (($mixKey = array_search('tl_page', $this->arrSyncSettings['syncCto_SyncTables'])) !== false)
                        {
                            unset($this->arrSyncSettings['syncCto_SyncTables'][$mixKey]);
                            $this->arrSyncSettings['syncCtoPro_tables_checked'][] = 'tl_page';
                        }

                        if (($mixKey = array_search('tl_article', $this->arrSyncSettings['syncCto_SyncTables'])) !== false)
                        {
                            unset($this->arrSyncSettings['syncCto_SyncTables'][$mixKey]);
                            $this->arrSyncSettings['syncCtoPro_tables_checked'][] = 'tl_article';
                        }

                        if (($mixKey = array_search('tl_content', $this->arrSyncSettings['syncCto_SyncTables'])) !== false)
                        {
                            unset($this->arrSyncSettings['syncCto_SyncTables'][$mixKey]);
                            $this->arrSyncSettings['syncCtoPro_tables_checked'][] = 'tl_content';
                        }
                    }

                    if (key_exists("forward", $_POST) && !(count($this->arrSyncSettings['syncCto_SyncTables']) == 0 && count($this->arrSyncSettings['syncCto_SyncDeleteTables']) == 0))
                    {
                        // Go to next step
                        $this->objData->setState(SyncCtoEnum::WORK_WORK);
                        $this->objData->setHtml("");
                        $this->booRefresh = true;
                        $this->objStepPool->step++;

                        break;
                    }
                    else if (key_exists("forward", $_POST) && count($this->arrSyncSettings['syncCto_SyncTables']) == 0 && count($this->arrSyncSettings['syncCto_SyncDeleteTables']) == 0)
                    {
                        // Skip if no tables are selected
                        $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
                        $this->objData->setHtml("");
                        $this->booRefresh = true;
                        $this->intStep++;

                        break;
                    }
                    else if (key_exists("skip", $_POST))
                    {
                        $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
                        $this->objData->setHtml("");
                        $this->booRefresh = true;
                        $this->intStep++;

                        break;
                    }

                    $objTemp                 = new BackendTemplate("be_syncCto_form");
                    $objTemp->id             = $this->intClientID;
                    $objTemp->step           = $this->intStep;
                    $objTemp->direction      = "To";
                    $objTemp->headline       = $GLOBALS['TL_LANG']['MSC']['totalsize'];
                    $objTemp->cssId          = 'syncCto_database_form';
                    $objTemp->forwardValue   = $GLOBALS['TL_LANG']['MSC']['apply'];
                    $objTemp->popupClassName = 'popupSyncDB.php';

                    // Build content 
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_1']);
                    $this->objData->setHtml($objTemp->parse());
                    $this->booRefresh = false;

                    break;

                /**
                 * Build SQL Zip File
                 */
                case 4:
                    if (count($this->arrSyncSettings['syncCto_SyncTables']) != 0)
                    {
                        $this->objStepPool->zipname = $this->objSyncCtoDatabase->runDump($this->arrSyncSettings['syncCto_SyncTables'], true, true);

                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_2']);
                        $this->objStepPool->step++;
                    }
                    else
                    {
                        $this->objStepPool->step = 8;
                    }

                    break;

                /**
                 * Send file to client
                 */
                case 5:

                    $arrResponse = $this->objSyncCtoCommunicationClient->sendFile($GLOBALS['SYC_PATH']['tmp'], $this->objStepPool->zipname, "", SyncCtoEnum::UPLOAD_SQL_TEMP);

                    // Check if the file was send and saved.
                    if (!is_array($arrResponse) || count($arrResponse) == 0)
                    {
                        throw new Exception("Empty file list from client. Maybe file send was not complet.");
                    }

                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_3']);
                    $this->objStepPool->step++;

                    break;

                /**
                 * Import on client side
                 */
                case 6:

                    $arrSQL = array();

                    if (isset($GLOBALS['TL_HOOKS']['syncDBUpdateBeforeDrop']) && is_array($GLOBALS['TL_HOOKS']['syncDBUpdateBeforeDrop']))
                    {
                        foreach ($GLOBALS['TL_HOOKS']['syncDBUpdateBeforeDrop'] as $callback)
                        {
                            $this->import($callback[0]);
                            $mixReturn = $this->$callback[0]->$callback[1]($this->intClientID, $this->arrSyncSettings['syncCto_SyncTables'], $arrSQL);

                            if (!empty($mixReturn) && is_array($mixReturn))
                            {
                                $arrSQL = $mixReturn;
                            }
                        }
                    }

                    // Import SQL zip 
                    $this->objSyncCtoCommunicationClient->runSQLImport($this->objSyncCtoHelper->standardizePath($this->arrClientInformation["folders"]["tmp"], "sql", $this->objStepPool->zipname), $arrSQL);

                    $this->objStepPool->step++;

                    break;

                /**
                 * Set timestamps 
                 */
                case 7:

                    $arrTableTimestamp = array(
                        'server' => $this->objSyncCtoHelper->getDatabaseTablesTimestamp($this->arrSyncSettings['syncCto_SyncTables']),
                        'client' => $this->objSyncCtoCommunicationClient->getClientTimestamp($this->arrSyncSettings['syncCto_SyncTables'])
                    );

                    foreach ($arrTableTimestamp AS $location => $arrTimeStamps)
                    {
                        // Update timestamp
                        $mixLastTableTimestamp = $this->Database
                                ->prepare("SELECT " . $location . "_timestamp FROM tl_synccto_clients WHERE id=?")
                                ->limit(1)
                                ->execute($this->intClientID)
                                ->fetchAllAssoc();

                        if (strlen($mixLastTableTimestamp[0][$location . "_timestamp"]) != 0)
                        {
                            $arrLastTableTimestamp = deserialize($mixLastTableTimestamp[0][$location . "_timestamp"]);
                        }
                        else
                        {
                            $arrLastTableTimestamp = array();
                        }

                        foreach ($arrTimeStamps as $key => $value)
                        {
                            $arrLastTableTimestamp[$key] = $value;
                        }

                        // Search for old entries
                        $arrTables = $this->Database->listTables();
                        foreach ($arrLastTableTimestamp as $key => $value)
                        {
                            if (!in_array($key, $arrTables))
                            {
                                unset($arrLastTableTimestamp[$key]);
                            }
                        }

                        $this->Database
                                ->prepare("UPDATE tl_synccto_clients SET " . $location . "_timestamp = ? WHERE id = ? ")
                                ->execute(serialize($arrLastTableTimestamp), $this->intClientID);
                    }

                    $this->objStepPool->step++;

                    break;

                /**
                 * Drop Tables
                 */
                case 8:

                    if (count($this->arrSyncSettings['syncCto_SyncDeleteTables']) != 0)
                    {
                        $arrKnownTables = $this->Database->listTables();

                        foreach ($this->arrSyncSettings['syncCto_SyncDeleteTables'] as $key => $value)
                        {
                            if (in_array($value, $arrKnownTables))
                            {
                                unset($this->arrSyncSettings['syncCto_SyncDeleteTables'][$key]);
                            }
                        }

                        $this->objSyncCtoCommunicationClient->dropTable($this->arrSyncSettings['syncCto_SyncDeleteTables'], true);

                        // Show step information
                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_4']);
                        $this->objStepPool->step++;

                        break;
                    }

                /**
                 * Hook for custom sql code
                 */
                case 9:
                    if (isset($GLOBALS['TL_HOOKS']['syncDBUpdate']) && is_array($GLOBALS['TL_HOOKS']['syncDBUpdate']))
                    {
                        $arrSQL = array();

                        foreach ($GLOBALS['TL_HOOKS']['syncDBUpdate'] as $callback)
                        {
                            $this->import($callback[0]);
                            $mixReturn = $this->$callback[0]->$callback[1]($this->intClientID, $arrSQL);

                            if (!empty($mixReturn) && is_array($mixReturn))
                            {
                                $arrSQL = $mixReturn;
                            }
                        }

                        if (count($arrSQL) != 0)
                        {
                            $this->objSyncCtoCommunicationClient->executeSQL($arrSQL);
                        }
                    }

                    // Show step information
                    $this->objData->setState(SyncCtoEnum::WORK_OK);
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_4']);

                    $this->intStep++;

                    break;
            }
        }
        catch (Exception $exc)
        {
            // If an error occurs skip the whole step
            $this->arrSyncSettings['syncCto_SyncDeleteTables'] = array();
            $this->arrSyncSettings['syncCto_CompareTables'] = array();

            $objErrTemplate              = new BackendTemplate('be_syncCto_error');
            $objErrTemplate->strErrorMsg = $exc->getMessage();

            $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
            $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_4"]['description_1']);
            $this->objData->setHtml($objErrTemplate->parse());
            $this->booRefresh = true;
            $this->intStep++;

            $this->log(vsprintf("Error on synchronization client ID %s with msg: %s", array($this->Input->get("id"), $exc->getMessage())), __CLASS__ . " " . __FUNCTION__, "ERROR");
        }
    }

    /**
     * Last Steps for all functions
     */
    private function pageSyncToShowStep6()
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
        $this->objData->setState(SyncCtoEnum::WORK_WORK);

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
                    $this->objData->setState(SyncCtoEnum::WORK_WORK);
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
                            if ($value["state"] == SyncCtoEnum::FILESTATE_DELETE || $value["state"] == SyncCtoEnum::FILESTATE_FOLDER_DELETE)
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
                    }

                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_2']);
                    $this->objStepPool->step++;
                    break;

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
                    $this->objSyncCtoCommunicationClient->setDisplayErrors($this->arrSyncSettings["syncCto_ShowError"]);
                    $this->objStepPool->step++;
                    break;

                /**
                 * Import Config / Set referer check
                 */
                case 6:
                    if (is_array($this->arrSyncSettings["syncCto_Systemoperations_Maintenance"]) && count($this->arrSyncSettings["syncCto_Systemoperations_Maintenance"]) != 0)
                    {
                        $this->objSyncCtoCommunicationClient->runMaintenance($this->arrSyncSettings["syncCto_Systemoperations_Maintenance"]);
                    }

                    $this->objStepPool->step++;
                    break;

                case 7:
                    if ($this->arrSyncSettings["syncCto_AttentionFlag"] == true)
                    {
                        $this->objSyncCtoCommunicationClient->setAttentionFlag(false);
                    }

                    $this->log(vsprintf("Successfully finishing of synchronization client ID %s.", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "INFO");

                /**
                 * Cleanup
                 */
                case 8:
                    if (in_array("temp_folders", $this->arrSyncSettings["syncCto_Systemoperations_Maintenance"]))
                    {
                        $this->objSyncCtoCommunicationClient->purgeTempFolder();
                        $this->objSyncCtoFiles->purgeTemp();
                    }

                    $this->objStepPool->step++;
                    $this->objData->setState(SyncCtoEnum::WORK_OK);
                    $this->objData->setHtml("");
                    $this->booRefresh = true;
                    $this->intStep++;

                    break;
            }
        }
        catch (Exception $exc)
        {
            $this->objStepPool->step++;

            $this->log(vsprintf("Error on synchronization client ID %s with msg: %s", array($this->Input->get("id"), $exc->getMessage())), __CLASS__ . " " . __FUNCTION__, "ERROR");
        }
    }

    /**
     * Last Step
     */
    private function pageSyncToShowStep7()
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
        $this->objData->setState(SyncCtoEnum::WORK_WORK);

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
                    $this->objData->setState(SyncCtoEnum::WORK_WORK);
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_1']['description_2']);
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
                    $this->objStepPool->step++;
                    break;

                /**
                 * Call the final operations hook
                 */
                case 2:
                    $arrResponse = $this->objSyncCtoCommunicationClient->runFinalOperations();
                    $this->objStepPool->step++;
                    break;

                case 3:
                    $this->objSyncCtoCommunicationClient->referrerEnable();
                    $this->objStepPool->step++;
                    break;

                case 4:
                    $this->objSyncCtoCommunicationClient->stopConnection();
                    $this->objStepPool->step++;
                    break;

                /**
                 * Show information
                 */
                case 5:
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

                            if ($value["state"] == SyncCtoEnum::FILESTATE_DELETE || $value["state"] == SyncCtoEnum::FILESTATE_FOLDER_DELETE)
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
                        $this->objData->setState(SyncCtoEnum::WORK_OK);
                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_1']['description_2']);
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
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']['complete_client'], array($strLink, "</a>")));
                        $this->objData->setState(SyncCtoEnum::WORK_OK);

                        break;
                    }
                    // If files was send, show more informations
                    else if (is_array($this->arrListCompare) && count($this->arrListCompare) != 0)
                    {
                        $this->objData->setHtml("");
                        $this->objData->setState(SyncCtoEnum::WORK_OK);
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_2'], array($intSendCount, count($this->arrListCompare))));
                        $this->booFinished = true;
                    }

                    // Check if there are some skipped files
                    if ($intSkippCount != 0)
                    {
                        $compare .= '<br /><p class="tl_help">' . $intSkippCount . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_3'] . '</p>';

                        $arrSort = array();

                        foreach ($this->arrListCompare as $key => $value)
                        {
                            if ($value["transmission"] != SyncCtoEnum::FILETRANS_SKIPPED)
                            {
                                continue;
                            }

                            $skipreason = preg_replace("/(RPC Call:.*|\<br\>|\<br\/\>)/i", " ", $value["skipreason"]);

                            $arrSort[$skipreason][] = $value["path"];
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
                            $compare .= '<br /><p class="tl_help">' . $intSendCount . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_4'] . '</p>';

                            $arrSort = array();

                            if (($intSendCount - $intDelCount) != 0)
                            {
                                $compare .= '<ul class="fileinfo">';

                                $compare .= "<li>";
                                $compare .= '<strong>' . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_6'] . '</strong>';
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
                                $compare .= '<strong>' . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_7'] . '</strong>';
                                $compare .= "<ul>";

                                $compare .= "<li>";
                                $compare .= '<strong>' . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_7'] . '</strong>';
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

                                $compare .= "<li>";
                                $compare .= '<strong>' . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_9'] . '</strong>';
                                $compare .= "<ul>";

                                foreach ($this->arrListCompare as $key => $value)
                                {
                                    if ($value["transmission"] != SyncCtoEnum::FILETRANS_SEND)
                                        continue;

                                    if ($value["state"] != SyncCtoEnum::FILESTATE_FOLDER_DELETE)
                                        continue;

                                    $compare .= "<li>";
                                    $compare .= (mb_check_encoding($value["path"], 'UTF-8')) ? $value["path"] : utf8_encode($value["path"]);
                                    $compare .= "</li>";
                                }

                                $compare .= "</ul>";
                                $compare .= "</li>";

                                $compare .= "</ul>";
                                $compare .= "</li>";
                                $compare .= "</ul>";
                            }

                            // Not sended, still waiting

                            if ($intWaitCount != 0)
                            {
                                $compare .= '<br /><p class="tl_help">' . $intWaitCount . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_5'] . '</p>';

                                $arrSort = array();

                                $compare .= '<ul class="fileinfo">';

                                $compare .= "<li>";
                                $compare .= '<strong>' . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_8'] . '</strong>';
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
                    $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']['complete_client'], array($strLink, "</a>")));
                    $this->objData->setState(SyncCtoEnum::WORK_OK);

                    break;
            }
        }
        catch (Exception $exc)
        {
            $this->objStepPool->step++;

            $this->log(vsprintf("Error on synchronization client ID %s with msg: %s", array($this->Input->get("id"), $exc->getMessage())), __CLASS__ . " " . __FUNCTION__, "ERROR");
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
     * Build checksum list and ask client
     */
    private function pageSyncFromShowStep2()
    {
        // Init
        if ($this->objStepPool->step == null)
        {
            $this->objStepPool->step = 1;
        }

        // Set content back to normale mode
        $this->booError = false;
        $this->strError = "";
        $this->objData->setState(SyncCtoEnum::WORK_WORK);

        // Run page
        try
        {
            switch ($this->objStepPool->step)
            {
                /**
                 * Show step
                 */
                case 1:
                    $this->objData->setState(SyncCtoEnum::WORK_WORK);
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
                        $this->arrListFile = $this->objSyncCtoCommunicationClient->getChecksumFiles(array());
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
                        $this->arrListFile = array_merge($this->arrListFile, $this->objSyncCtoCommunicationClient->getChecksumCore());
                        $this->objStepPool->step++;
                        break;
                    }

                /**
                 * Check List
                 */
                case 4:
                    if (in_array("core_change", $this->arrSyncSettings["syncCto_Type"]) || in_array("user_change", $this->arrSyncSettings["syncCto_Type"]))
                    {
                        $this->arrListCompare = $this->objSyncCtoFiles->runCecksumCompare($this->arrListFile);
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
                        $arrChecksumClient = $this->objSyncCtoFiles->runChecksumCore();
                        $arrChecksumClient = $this->objSyncCtoCommunicationClient->checkDeleteFiles($arrChecksumClient);
                        if (count($arrChecksumClient) != 0)
                        {
                            $this->arrListCompare = array_merge($this->arrListCompare, $arrChecksumClient);
                        }

                        $this->objStepPool->step++;
                        break;
                    }

                case 6:
                    if (in_array("user_delete", $this->arrSyncSettings["syncCto_Type"]))
                    {
                        $arrChecksumClient = $this->objSyncCtoFiles->runChecksumFiles();
                        $arrChecksumClient = $this->objSyncCtoCommunicationClient->checkDeleteFiles($arrChecksumClient);

                        if (count($arrChecksumClient) != 0)
                        {
                            $this->arrListCompare = array_merge($this->arrListCompare, $arrChecksumClient);
                        }

                        $this->objStepPool->step++;
                        break;
                    }

                case 7:
                    if (in_array("core_delete", $this->arrSyncSettings["syncCto_Type"]))
                    {
                        $arrChecksumClienta = $this->objSyncCtoFiles->runChecksumFolderCore();
                        $arrChecksumClientb = $this->objSyncCtoCommunicationClient->searchDeleteFolders($arrChecksumClienta);

                        if (count($arrChecksumClientb) != 0)
                        {
                            $this->arrListCompare = array_merge($this->arrListCompare, $arrChecksumClientb);
                        }

                        $this->objStepPool->step++;
                        break;
                    }

                case 8:
                    if (in_array("user_delete", $this->arrSyncSettings["syncCto_Type"]))
                    {
                        $arrChecksumClient = $this->objSyncCtoFiles->runChecksumFolderFiles();
                        $arrChecksumClient = $this->objSyncCtoCommunicationClient->searchDeleteFolders($arrChecksumClient);

                        if (count($arrChecksumClient) != 0)
                        {
                            $this->arrListCompare = array_merge($this->arrListCompare, $arrChecksumClient);
                        }
                    }

                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_3']);
                    $this->objStepPool->step++;
                    break;

                /**
                 * Set CSS and search for bigfiles
                 */
                case 9:
                    foreach ($this->arrListCompare as $key => $value)
                    {
                        switch ($value["state"])
                        {
                            case SyncCtoEnum::FILESTATE_BOMBASTIC_BIG:
                                $this->arrListCompare[$key]["css"]     = "unknown";
                                $this->arrListCompare[$key]["css_big"] = "ignored";
                                break;

                            case SyncCtoEnum::FILESTATE_TOO_BIG_NEED:
                                $this->arrListCompare[$key]["css_big"] = "ignored";
                            case SyncCtoEnum::FILESTATE_NEED:
                                $this->arrListCompare[$key]["css"]     = "modified";
                                break;

                            case SyncCtoEnum::FILESTATE_TOO_BIG_MISSING:
                                $this->arrListCompare[$key]["css_big"] = "ignored";
                            case SyncCtoEnum::FILESTATE_MISSING:
                                $this->arrListCompare[$key]["css"]     = "new";
                                break;

                            case SyncCtoEnum::FILESTATE_DELETE:
                            case SyncCtoEnum::FILESTATE_FOLDER_DELETE:
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
                                || $value["state"] == SyncCtoEnum::FILESTATE_DELETE
                                || $value["state"] == SyncCtoEnum::FILESTATE_FOLDER_DELETE
                                || $value["state"] == SyncCtoEnum::FILESTATE_FOLDER)
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
                 * Show files form
                 */
                case 10:
                    // Counter
                    $intCountMissing = 0;
                    $intCountNeed    = 0;
                    $intCountIgnored = 0;
                    $intCountDelete  = 0;

                    $intTotalSizeNew    = 0;
                    $intTotalSizeDel    = 0;
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
                            case SyncCtoEnum::FILESTATE_FOLDER_DELETE:
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

                    $this->objStepPool->missing = $intCountMissing;
                    $this->objStepPool->need    = $intCountNeed;
                    $this->objStepPool->ignored = $intCountIgnored;
                    $this->objStepPool->delete  = $intCountDelete;

                    // Save files and go on or skip here
                    if ($intCountMissing == 0 && $intCountNeed == 0 && $intCountIgnored == 0 && $intCountDelete == 0)
                    {
                        // Set current step informations
                        $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1']);
                        $this->objData->setHtml("");
                        $this->booRefresh = true;
                        $this->intStep++;

                        break;
                    }
                    else if (count($this->arrListCompare) == 0 || key_exists("skip", $_POST))
                    {
                        $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1']);
                        $this->objData->setHtml("");
                        $this->booRefresh = true;
                        $this->intStep++;

                        $this->arrListCompare = array();

                        break;
                    }
                    else if (key_exists("forward", $_POST) && count($this->arrListCompare) != 0)
                    {
                        $this->objData->setState(SyncCtoEnum::WORK_OK);
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_4'], array($intCountMissing, $intCountNeed, $intCountDelete, $intCountIgnored, $this->getReadableSize($intTotalSizeNew), $this->getReadableSize($intTotalSizeChange), $this->getReadableSize($intTotalSizeDel))));
                        $this->objData->setHtml("");
                        $this->booRefresh = true;
                        $this->intStep++;

                        break;
                    }

                    $objTemp                 = new BackendTemplate("be_syncCto_form");
                    $objTemp->id             = $this->intClientID;
                    $objTemp->step           = $this->intStep;
                    $objTemp->direction      = "From";
                    $objTemp->headline       = $GLOBALS['TL_LANG']['MSC']['totalsize'];
                    $objTemp->cssId          = 'syncCto_filelist_form';
                    $objTemp->forwardValue   = $GLOBALS['TL_LANG']['MSC']['apply'];
                    $objTemp->popupClassName = 'popupSyncFiles.php';

                    // Build content 
                    $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_4'], array($intCountMissing, $intCountNeed, $intCountDelete, $intCountIgnored, $this->getReadableSize($intTotalSizeNew), $this->getReadableSize($intTotalSizeChange), $this->getReadableSize($intTotalSizeDel))));
                    $this->objData->setHtml($objTemp->parse());
                    $this->booRefresh = false;

                    break;
            }
        }
        catch (Exception $exc)
        {
            // If an error occurs skip the whole step
            $this->arrListCompare = array();

            $objErrTemplate              = new BackendTemplate('be_syncCto_error');
            $objErrTemplate->strErrorMsg = $exc->getMessage();

            $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
            $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1']);
            $this->objData->setHtml($objErrTemplate->parse());
            $this->booRefresh = true;
            $this->intStep++;

            $this->log(vsprintf("Error on synchronization client ID %s with msg: %s", array($this->Input->get("id"), $exc->getMessage())), __CLASS__ . " " . __FUNCTION__, "ERROR");
        }
    }

    /**
     * Send Files / Split Files
     */
    private function pageSyncFromShowStep3()
    {
        // Init
        if ($this->objStepPool->step == null)
        {
            $this->objStepPool->step = 1;
        }

        // Set content back to normale mode
        $this->booError = false;
        $this->strError = "";
        $this->objData->setState(SyncCtoEnum::WORK_WORK);

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

                if ($value["state"] == SyncCtoEnum::FILESTATE_DELETE || $value["state"] == SyncCtoEnum::FILESTATE_FOLDER_DELETE)
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
            $intStart = time();

            switch ($this->objStepPool->step)
            {
                /**
                 * Show step
                 */
                case 1:
                    $this->objData->setState(SyncCtoEnum::WORK_WORK);
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_1']);

                    $this->objStepPool->step++;
                    break;

                /**
                 * Get normal files
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

                        if (in_array($value["state"], array(SyncCtoEnum::FILESTATE_DELETE, SyncCtoEnum::FILESTATE_FOLDER_DELETE)))
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
                            $this->arrListCompare[$key]["skipreason"]   = $GLOBALS['TL_LANG']['ERR']['maximum_filesize'];
                            $this->arrListCompare[$key]["transmission"] = SyncCtoEnum::FILETRANS_SKIPPED;

                            continue;
                        }

                        try
                        {
                            // Get files
                            $booResponse = $this->objSyncCtoCommunicationClient->getFile($value["path"], $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "sync", $value["path"]));

                            // Check if the file was send and saved.
                            if ($booResponse != true)
                            {
                                throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['unknown_file'], $value["path"]));
                            }

                            $this->arrListCompare[$key]["transmission"] = SyncCtoEnum::FILETRANS_SEND;
                        }
                        catch (Exception $exc)
                        {
                            $this->arrListCompare[$key]["transmission"] = SyncCtoEnum::FILETRANS_SKIPPED;
                            $this->arrListCompare[$key]["skipreason"]   = $exc->getMessage();
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
                        $this->objData->setState(SyncCtoEnum::WORK_OK);
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

                        $strSavePath = $this->objSyncCtoHelper->standardizePath($this->arrClientInformation["folders"]['tmp'], $key);

                        // Splitt file
                        $intSplits = $this->objSyncCtoCommunicationClient->runSplitFiles($value["path"], $strSavePath, $key, ($this->arrClientInformation["upload_sizeLimit"] / 100 * $this->arrClientInformation["upload_sizePercent"]));

                        $this->arrListCompare[$key]["splitcount"] = $intSplits;
                        $this->arrListCompare[$key]["splitname"]  = $key;

                        break;
                    }

                    if ($intCount != $intCountSplit)
                    {
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_6'], array($intCount, $intCountSplit)));
                    }
                    else
                    {
                        $this->objStepPool->step++;
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_4'], array($intCount, $intCountSplit)));
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

                        if (in_array($value["state"], array(
                                    SyncCtoEnum::FILESTATE_TOO_BIG_DELETE,
                                    SyncCtoEnum::FILESTATE_TOO_BIG_MISSING,
                                    SyncCtoEnum::FILESTATE_TOO_BIG_NEED,
                                    SyncCtoEnum::FILESTATE_TOO_BIG_SAME,
                                    SyncCtoEnum::FILESTATE_BOMBASTIC_BIG,
                                    SyncCtoEnum::FILESTATE_DELETE,
                                    SyncCtoEnum::FILESTATE_FOLDER_DELETE
                                )))
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
                            $booResponse = $this->objSyncCtoCommunicationClient->getFile($this->objSyncCtoHelper->standardizePath($this->arrClientInformation["folders"]['tmp'], $key, $value["splitname"] . ".sync$ii"), $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], $key, $value["splitname"] . ".sync$ii"));

                            // Check if the file was send and saved.
                            if ($booResponse != true)
                            {
                                throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['unknown_file'], $strFrom));
                            }

                            $this->arrListCompare[$key]["split_transfer"] = $ii + 1;

                            // check time limit 30 secs
                            if ($intStart + 30 < time())
                            {
                                break;
                            }
                        }

                        break;
                    }

                    if ($intCount != $intCountSplit)
                    {
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_7'], array($intCount, $intCountSplit)));
                    }
                    else
                    {
                        $this->objStepPool->step++;
                        $this->objData->setState(SyncCtoEnum::WORK_WORK);
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

                        if (in_array($value["state"], array(
                                    SyncCtoEnum::FILESTATE_TOO_BIG_DELETE,
                                    SyncCtoEnum::FILESTATE_TOO_BIG_MISSING,
                                    SyncCtoEnum::FILESTATE_TOO_BIG_NEED,
                                    SyncCtoEnum::FILESTATE_TOO_BIG_SAME,
                                    SyncCtoEnum::FILESTATE_BOMBASTIC_BIG,
                                    SyncCtoEnum::FILESTATE_DELETE,
                                    SyncCtoEnum::FILESTATE_FOLDER_DELETE
                                )))
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
                            throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['rebuild'], array($value["path"])));
                        }

                        $this->arrListCompare[$key]["transmission"] = SyncCtoEnum::FILETRANS_SEND;

                        if ($intStart < time() - 30)
                        {
                            break;
                        }
                    }

                    if ($intCount != $intCountSplit)
                    {
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_8'], array($intCount, $intCountSplit)));
                    }
                    else
                    {
                        $this->objData->setState(SyncCtoEnum::WORK_OK);
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_1'], array($intCount, $intCountSplit)));

                        $this->intStep++;
                    }

                    break;
            }
        }
        catch (Exception $exc)
        {

            // If an error occurs skip the whole step
            $objErrTemplate              = new BackendTemplate('be_syncCto_error');
            $objErrTemplate->strErrorMsg = $exc->getMessage();

            $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
            $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_1']);
            $this->objData->setHtml($objErrTemplate->parse());
            $this->booRefresh = true;
            $this->intStep++;

            $this->log(vsprintf("Error on synchronization client ID %s with msg: %s", array($this->Input->get("id"), $exc->getMessage())), __CLASS__ . " " . __FUNCTION__, "ERROR");
        }
    }

    /**
     * Build SQL zip and send it to the client
     */
    private function pageSyncFromShowStep4()
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
            $this->objData->setState(SyncCtoEnum::WORK_WORK);

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
                    $this->objData->setState(SyncCtoEnum::WORK_WORK);
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_1']);
                    $this->objStepPool->step++;

                    break;

                case 2:
                    // Check user
                    if ($this->User->isAdmin || $this->User->syncCto_tables != null)
                    {
                        // Load allowed tables for this user
                        if ($this->User->isAdmin)
                        {
                            $arrAllowedTables = true;
                        }
                        else
                        {
                            $arrAllowedTables = $this->User->syncCto_tables;
                        }

                        $arrClientTableR    = $this->objSyncCtoCommunicationClient->getRecommendedTables();
                        $arrClientTableNR   = $this->objSyncCtoCommunicationClient->getNoneRecommendedTables();
                        $arrClientTableH    = $this->objSyncCtoCommunicationClient->getHiddenTables();
                        $arrClientTimestamp = $this->objSyncCtoCommunicationClient->getClientTimestamp(array());

                        $arrServerTableR    = $this->objSyncCtoHelper->databaseTablesRecommended();
                        $arrServerTableNR   = $this->objSyncCtoHelper->databaseTablesNoneRecommended();
                        $arrServerTableH    = $this->objSyncCtoHelper->getTablesHidden();
                        $arrServerTimestamp = $this->objSyncCtoHelper->getDatabaseTablesTimestamp();

                        // clean up tables. Use user rights.
                        if ($arrAllowedTables !== true)
                        {
                            foreach ($arrClientTableR as $key => $value)
                            {
                                if (!in_array($value['name'], $arrAllowedTables))
                                {
                                    unset($arrClientTableR[$key]);
                                }
                            }

                            foreach ($arrClientTableNR as $key => $value)
                            {
                                if (!in_array($value['name'], $arrAllowedTables))
                                {
                                    unset($arrClientTableNR[$key]);
                                }
                            }

                            foreach ($arrClientTableH as $key => $value)
                            {
                                if (!in_array($value['name'], $arrAllowedTables))
                                {
                                    unset($arrClientTableH[$key]);
                                }
                            }

                            foreach ($arrServerTableR as $key => $value)
                            {
                                if (!in_array($value['name'], $arrAllowedTables))
                                {
                                    unset($arrServerTableR[$key]);
                                }
                            }

                            foreach ($arrServerTableNR as $key => $value)
                            {
                                if (!in_array($value['name'], $arrAllowedTables))
                                {
                                    unset($arrServerTableNR[$key]);
                                }
                            }

                            foreach ($arrServerTableH as $key => $value)
                            {
                                if (!in_array($value['name'], $arrAllowedTables))
                                {
                                    unset($arrServerTableH[$key]);
                                }
                            }
                        }

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
                        $arrAllTimeStamps = $this->objSyncCtoDatabase->getAllTimeStamps($arrServerTimestamp, $arrClientTimestamp, $this->intClientID);

                        $arrCompareList = $this->objSyncCtoDatabase->getFormatedCompareList($arrClientTables, $arrServerTables, $arrHiddenTables, $arrAllTimeStamps['client'], $arrAllTimeStamps['server'], $arrAllowedTables, 'client', 'server');

                        if (count($arrCompareList['recommended']) == 0 && count($arrCompareList['none_recommended']) == 0)
                        {
                            $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
                            $this->objData->setHtml("");
                            $this->intStep++;

                            break;
                        }

                        $this->arrSyncSettings['syncCto_CompareTables'] = $arrCompareList;

                        $this->objStepPool->step++;
                    }
                    else
                    {
                        $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
                        $this->objData->setHtml("");
                        $this->intStep++;
                    }

                    break;

                case 3:

                    if (key_exists("forward", $_POST) && !(count($this->arrSyncSettings['syncCto_SyncTables']) == 0 && count($this->arrSyncSettings['syncCto_SyncDeleteTables']) == 0))
                    {
                        // Go to next step
                        $this->objData->setState(SyncCtoEnum::WORK_WORK);
                        $this->objData->setHtml("");
                        $this->booRefresh = true;
                        $this->objStepPool->step++;

                        break;
                    }
                    else if (key_exists("forward", $_POST) && count($this->arrSyncSettings['syncCto_SyncTables']) == 0 && count($this->arrSyncSettings['syncCto_SyncDeleteTables']) == 0)
                    {
                        // Skip if no tables are selected
                        $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
                        $this->objData->setHtml("");
                        $this->booRefresh = true;
                        $this->intStep++;

                        break;
                    }
                    else if (key_exists("skip", $_POST))
                    {
                        $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
                        $this->objData->setHtml("");
                        $this->booRefresh = true;
                        $this->intStep++;

                        break;
                    }

                    $objTemp                 = new BackendTemplate("be_syncCto_form");
                    $objTemp->id             = $this->intClientID;
                    $objTemp->step           = $this->intStep;
                    $objTemp->direction      = "From";
                    $objTemp->headline       = $GLOBALS['TL_LANG']['MSC']['totalsize'];
                    $objTemp->cssId          = 'syncCto_database_form';
                    $objTemp->forwardValue   = $GLOBALS['TL_LANG']['MSC']['apply'];
                    $objTemp->popupClassName = 'popupSyncDB.php';

                    // Build content 
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_1']);
                    $this->objData->setHtml($objTemp->parse());
                    $this->booRefresh = false;

                    break;

                /**
                 * Build SQL Zip File
                 */
                case 4:
                    if (count($this->arrSyncSettings['syncCto_SyncTables']) != 0)
                    {
                        $this->objStepPool->zipname = $this->objSyncCtoCommunicationClient->runDatabaseDump($this->arrSyncSettings['syncCto_SyncTables'], true, true);

                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_2']);
                        $this->objStepPool->step++;
                    }
                    else
                    {
                        $this->objStepPool->step = 8;
                    }

                    break;

                /**
                 * Get file to client
                 */
                case 5:

                    $strFrom     = $this->objSyncCtoHelper->standardizePath($this->arrClientInformation["folders"]['tmp'], $this->objStepPool->zipname);
                    $strTo       = $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "sql", $this->objStepPool->zipname);
                    $booResponse = $this->objSyncCtoCommunicationClient->getFile($strFrom, $strTo);

                    // Check if the file was send and saved.
                    if ($booResponse != true)
                    {
                        throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['unknown_file'], $strFrom));
                    }

                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_3']);
                    $this->objStepPool->step++;

                    break;

                /**
                 * Import on server side
                 */
                case 6:

                    $strSrc = $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "sql", $this->objStepPool->zipname);
                    $this->objSyncCtoDatabase->runRestore($strSrc);

                    $this->objStepPool->step++;

                    break;

                /**
                 * Set timestamps 
                 */
                case 7:

                    $arrTableTimestamp = array(
                        'server' => $this->objSyncCtoHelper->getDatabaseTablesTimestamp($this->arrSyncSettings['syncCto_SyncTables']),
                        'client' => $this->objSyncCtoCommunicationClient->getClientTimestamp($this->arrSyncSettings['syncCto_SyncTables'])
                    );

                    foreach ($arrTableTimestamp AS $location => $arrTimeStamps)
                    {
                        // Update Timestamp
                        $mixLastTableTimestamp = $this->Database
                                ->prepare("SELECT " . $location . "_timestamp FROM tl_synccto_clients WHERE id=?")
                                ->limit(1)
                                ->executeUncached($this->intClientID)
                                ->fetchAllAssoc();

                        if (strlen($mixLastTableTimestamp[0][$location . "_timestamp"]) != 0)
                        {
                            $arrLastTableTimestamp = deserialize($mixLastTableTimestamp[0][$location . "_timestamp"]);
                        }
                        else
                        {
                            $arrLastTableTimestamp = array();
                        }

                        foreach ($arrTimeStamps as $key => $value)
                        {
                            $arrLastTableTimestamp[$key] = $value;
                        }

                        // Search for old entries
                        $arrTables = $this->Database->listTables();
                        foreach ($arrLastTableTimestamp as $key => $value)
                        {
                            if (!in_array($key, $arrTables))
                            {
                                unset($arrLastTableTimestamp[$key]);
                            }
                        }

                        $this->Database
                                ->prepare("UPDATE tl_synccto_clients SET " . $location . "_timestamp = ? WHERE id = ? ")
                                ->execute(serialize($arrLastTableTimestamp), $this->intClientID);
                    }

                    $this->objStepPool->step++;

                    break;

                /**
                 * Drop Tables
                 */
                case 8:
                    if (count($this->arrSyncSettings['syncCto_SyncDeleteTables']) != 0)
                    {
                        $this->objSyncCtoDatabase->dropTable($this->arrSyncSettings['syncCto_SyncDeleteTables'], true);
                    }

                    // Show step information
                    $this->objData->setState(SyncCtoEnum::WORK_OK);
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_4']);

                    $this->intStep++;

                    break;
            }
        }
        catch (Exception $exc)
        {
            // If an error occurs skip the whole step
            $this->arrSyncSettings['syncCto_SyncDeleteTables'] = array();
            $this->arrSyncSettings['syncCto_CompareTables'] = array();

            $objErrTemplate              = new BackendTemplate('be_syncCto_error');
            $objErrTemplate->strErrorMsg = $exc->getMessage();

            $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
            $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_1']);
            $this->objData->setHtml($objErrTemplate->parse());
            $this->booRefresh = true;
            $this->intStep++;

            $this->log(vsprintf("Error on synchronization client ID %s with msg: %s", array($this->Input->get("id"), $exc->getMessage())), __CLASS__ . " " . __FUNCTION__, "ERROR");
        }
    }

    /**
     * File send part have fun, much todo here so let`s play a round :P
     */
    private function pageSyncFromShowStep6()
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
        $this->objData->setState(SyncCtoEnum::WORK_WORK);

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
                    $this->objData->setState(SyncCtoEnum::WORK_WORK);
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
                            $arrTransmission = $this->objSyncCtoFiles->moveTempFile($arrImport);

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
                            if (in_array($value["state"], array(SyncCtoEnum::FILESTATE_DELETE, SyncCtoEnum::FILESTATE_FOLDER_DELETE)))
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

                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_2']);
                    $this->objStepPool->step++;
                    break;

                /**
                 * Import Config
                 */
                case 4:
                    if (in_array("localconfig_update", $this->arrSyncSettings["syncCto_Type"]))
                    {
                        $arrLocalconfig = $this->objSyncCtoCommunicationClient->getLocalConfig();
                        if (count($arrLocalconfig) != 0)
                        {
                            $this->objSyncCtoHelper->importConfig($arrLocalconfig);
                        }

                        $this->objStepPool->step++;
                        break;
                    }

                    $this->objStepPool->step++;

                /**
                 * Import Config / Set referer check
                 */
                case 5:
                    if (is_array($this->arrSyncSettings["syncCto_Systemoperations_Maintenance"]) && count($this->arrSyncSettings["syncCto_Systemoperations_Maintenance"]) != 0)
                    {
                        $this->objSyncCtoFiles->runMaintenance($this->arrSyncSettings["syncCto_Systemoperations_Maintenance"]);
                    }

                    $this->objStepPool->step++;
                    break;

                case 6:
                    if ($this->arrSyncSettings["syncCto_AttentionFlag"] == true)
                    {
                        $this->objSyncCtoCommunicationClient->setAttentionFlag(true);
                    }
                    else
                    {
                        $this->objSyncCtoCommunicationClient->setAttentionFlag(false);
                    }

                    $this->log(vsprintf("Successfully finishing of synchronization client ID %s.", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "INFO");

                    $this->objData->setState(SyncCtoEnum::WORK_OK);
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_1']);
                    $this->objData->setHtml("");
                    $this->booRefresh = true;
                    $this->intStep++;
            }
        }
        catch (Exception $exc)
        {
            $this->objStepPool->step++;

            $this->log(vsprintf("Error on synchronization client ID %s with msg: %s", array($this->Input->get("id"), $exc->getMessage())), __CLASS__ . " " . __FUNCTION__, "ERROR");
        }
    }

    /**
     * File send part have fun, much todo here so let`s play a round :P
     */
    private function pageSyncFromShowStep7()
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
        $this->objData->setState(SyncCtoEnum::WORK_WORK);

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
                    $this->objData->setState(SyncCtoEnum::WORK_WORK);
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_1']['description_2']);
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
                    $this->objStepPool->step++;
                    break;

                /**
                 * Cleanup
                 */
                case 2:
                    $this->objSyncCtoCommunicationClient->purgeTempFolder();
                    $this->objSyncCtoFiles->purgeTemp();
                    $this->objStepPool->step++;
                    break;

                /**
                 * Call the final operations hook
                 */
                case 3:
                    $arrResponse = $this->objSyncCtoHelper->executeFinalOperations();
                    $this->objStepPool->step++;
                    break;

                case 4:
                    $this->objSyncCtoCommunicationClient->referrerEnable();
                    $this->objStepPool->step++;
                    break;

                case 5:
                    $this->objSyncCtoCommunicationClient->stopConnection();
                    $this->objStepPool->step++;
                    break;

                /**
                 * Show information
                 */
                case 6:
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

                            if (in_array($value["state"], array(SyncCtoEnum::FILESTATE_DELETE, SyncCtoEnum::FILESTATE_FOLDER_DELETE)))
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
                        $this->objData->setState(SyncCtoEnum::WORK_OK);
                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_1']['description_2']);
                        $this->booFinished = true;

                        // Set finished msg
                        // Set success information 
                        $arrClientLink = $this->Database
                                ->prepare("SELECT * FROM tl_synccto_clients WHERE id=?")
                                ->limit(1)
                                ->execute($this->intClientID)
                                ->fetchAllAssoc();

                        $strLink = vsprintf('<a href="%s" target="_blank" style="text-decoration:underline;">', array($this->Environment->base));

                        $this->objData->nextStep();
                        $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['complete']);
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']['complete_server'], array($strLink, "</a>")));
                        $this->objData->setState(SyncCtoEnum::WORK_OK);

                        break;
                    }
                    // If files was send, show more informations
                    else if (is_array($this->arrListCompare) && count($this->arrListCompare) != 0)
                    {
                        $this->objData->setHtml("");
                        $this->objData->setState(SyncCtoEnum::WORK_OK);
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_2'], array($intSendCount, count($this->arrListCompare))));
                        $this->booFinished = true;
                    }

                    // Check if there are some skipped files
                    if ($intSkippCount != 0)
                    {
                        $compare .= '<br /><p class="tl_help">' . $intSkippCount . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_3'] . '</p>';

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
                            $compare .= '<br /><p class="tl_help">' . $intSendCount . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_4'] . '</p>';

                            $arrSort = array();

                            if (($intSendCount - $intDelCount) != 0)
                            {
                                $compare .= '<ul class="fileinfo">';

                                $compare .= "<li>";
                                $compare .= '<strong>' . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_6'] . '</strong>';
                                $compare .= "<ul>";

                                foreach ($this->arrListCompare as $key => $value)
                                {
                                    if ($value["transmission"] != SyncCtoEnum::FILETRANS_SEND)
                                    {
                                        continue;
                                    }

                                    if (in_array($value["state"], array(SyncCtoEnum::FILESTATE_DELETE, SyncCtoEnum::FILESTATE_FOLDER_DELETE)))
                                    {
                                        continue;
                                    }

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
                                $compare .= '<strong>' . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_7'] . '</strong>';
                                $compare .= "<ul>";

                                $compare .= "<li>";
                                $compare .= '<strong>' . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_7'] . '</strong>';
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

                                $compare .= "<li>";
                                $compare .= '<strong>' . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_9'] . '</strong>';
                                $compare .= "<ul>";

                                foreach ($this->arrListCompare as $key => $value)
                                {
                                    if ($value["transmission"] != SyncCtoEnum::FILETRANS_SEND)
                                        continue;

                                    if ($value["state"] != SyncCtoEnum::FILESTATE_FOLDER_DELETE)
                                        continue;

                                    $compare .= "<li>";
                                    $compare .= (mb_check_encoding($value["path"], 'UTF-8')) ? $value["path"] : utf8_encode($value["path"]);
                                    $compare .= "</li>";
                                }

                                $compare .= "</ul>";
                                $compare .= "</li>";

                                $compare .= "</ul>";
                                $compare .= "</li>";
                                $compare .= "</ul>";
                            }

                            // Not sended, still waiting

                            if ($intWaitCount != 0)
                            {
                                $compare .= '<br /><p class="tl_help">' . $intWaitCount . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_5'] . '</p>';

                                $arrSort = array();

                                $compare .= '<ul class="fileinfo">';

                                $compare .= "<li>";
                                $compare .= '<strong>' . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_9'] . '</strong>';
                                $compare .= "<ul>";

                                foreach ($this->arrListCompare as $key => $value)
                                {
                                    if ($value["transmission"] != SyncCtoEnum::FILETRANS_WAITING)
                                    {
                                        continue;
                                    }

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

                    $strLink = vsprintf('<a href="%s" target="_blank" style="text-decoration:underline;">', array($this->Environment->base));

                    $this->objData->nextStep();
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['complete']);
                    $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']['complete_server'], array($strLink, "</a>")));
                    $this->objData->setState(SyncCtoEnum::WORK_OK);

                    break;
            }
        }
        catch (Exception $exc)
        {
            $this->objStepPool->step++;

            $this->log(vsprintf("Error on synchronization client ID %s with msg: %s", array($this->Input->get("id"), $exc->getMessage())), __CLASS__ . " " . __FUNCTION__, "ERROR");
        }
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

    public function setStep($intStep)
    {
        $this->intStep = $intStep;
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