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
    protected $arrListFile;
    protected $arrListCompare;
    protected $intStep;
    protected $intClientID;
    
    // Helper Class
    protected $objSyncCtoCommunicationClient;
    protected $objSyncCtoDatabase;
    protected $objSyncCtoFiles;
    protected $objSyncCtoHelper;
    protected $objSyncCtoMeasurement;

    /* -------------------------------------------------------------------------
     * Core Functions
     */

    /**
     * Constructor
     * 
     * @param DataContainer $objDc 
     */
    function __construct(DataContainer $objDc = null)
    {
        parent::__construct($objDc);

        // Load Helper
        $this->objSyncCtoDatabase = SyncCtoDatabase::getInstance();
        $this->objSyncCtoFiles = SyncCtoFiles::getInstance();
        $this->objSyncCtoCommunicationClient = SyncCtoCommunicationClient::getInstance();
        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();

        // Load Language 
        $this->loadLanguageFile("tl_syncCto_steps");
    }

    /**
     * Generate page
     */
    protected function compile()
    {
        if ($this->Input->get("act") != "start")
        {
            $_SESSION["TL_ERROR"] = array($GLOBALS['TL_LANG']['ERR']['call_directly']);
            $this->redirect("contao/main.php?do=synccto_clients");
        }
        
        echo $this->Input->get("table");
        exit();

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
                $_SESSION["TL_ERROR"][] = $GLOBALS['TL_LANG']['ERR']['unknown_function'] ;
                $this->redirect("contao/main.php?do=synccto_clients");
                break;
        }
    }

    /* -------------------------------------------------------------------------
     * Functions for comunication
     */

    /**
     * Setup for page syncTo
     */
    private function pageSyncTo()
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

        // Set up temp file for filetransmission
        if ($this->intStep != 1)
        {
            $intID = $this->Input->get("id");

            // Load Files
            $objFileList = new File("system/tmp/syncfilelist-ID-$intID.txt");

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

            $objCompareList = new File("system/tmp/synccomparelist-ID-$intID.txt");

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
                        ->set(array("syncTo_user" => $this->User->id, "syncTo_tstamp" => time()))
                        ->execute($this->Input->get("id"));

                $this->pageSyncToShowStep1();
                break;

            case 2:
                $this->pageSyncToShowStep2();
                break;

            case 3:
                $this->pageSyncToShowStep3();
                break;

            case 4:
                $this->pageSyncToShowStep4();
                break;

            case 5:
                $this->pageSyncToShowStep5();
                break;

            case 6:
                $this->pageSyncToShowStep6();
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

            $objFileList = new File("system/tmp/syncfilelist-ID-$intID.txt");
            $objFileList->write(serialize($this->arrListFile));
            $objFileList->close();

            $objCompareList = new File("system/tmp/synccomparelist-ID-$intID.txt");
            $objCompareList->write(serialize($this->arrListCompare));
            $objCompareList->close();
        }
    }

    /**
     * Setup for page syncFrom
     */
    private function pageSyncFrom()
    {

        echo "in ";
        exit();

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

        // Set up temp file for filetransmission
        if ($this->intStep != 1)
        {
            $intID = $this->Input->get("id");

            // Load Files
            $objFileList = new File("system/tmp/syncfilelist-ID-$intID.txt");

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

            $objCompareList = new File("system/tmp/synccomparelist-ID-$intID.txt");

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
                        ->set(array("syncTo_user" => $this->User->id, "syncTo_tstamp" => time()))
                        ->execute($this->Input->get("id"));

                $this->pageSyncFromShowStep1();
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

            $objFileList = new File("system/tmp/syncfilelist-ID-$intID.txt");
            $objFileList->write(serialize($this->arrListFile));
            $objFileList->close();

            $objCompareList = new File("system/tmp/synccomparelist-ID-$intID.txt");
            $objCompareList->write(serialize($this->arrListCompare));
            $objCompareList->close();
        }
    }

    /* -------------------------------------------------------------------------
     * Start SyncCto syncTo
     */

    /**
     * Check client communication
     */
    private function pageSyncToShowStep1()
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

        // Load content
        $arrContenData = $this->Session->get("syncCto_Content");

        // Set content back to normale mode
        $arrContenData["error"] = false;
        $arrContenData["error_msg"] = "";
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
                    $arrContenData["data"][1]["title"] = $GLOBALS['TL_LANG']['MSC']['step'] . " 1";
                    $arrContenData["data"][1]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_1'];
                    $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['MSC']['progress'];

                    $mixStepPool["step"] = 2;
                    break;

                /**
                 * Referer check deactivate
                 */
                case 2:
                    if (!$this->objSyncCtoCommunicationClient->refererDisable())
                    {
                        $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['MSC']['error'];
                        $arrContenData["error"] = true;
                        $arrContenData["error_msg"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']['error_step_1']['referer'];

                        break;
                    }

                    $mixStepPool["step"] = 3;
                    break;


                /**
                 * Check version
                 */
                case 3:
                    $strVersion = $this->objSyncCtoCommunicationClient->getVersionSyncCto();

                    if (!version_compare($strVersion, $GLOBALS['SYC_VERSION'], "="))
                    {
                        $this->log(vsprintf("Not the same version from syncCto on synchronization client ID %s. Serverversion: %s. Clientversion: %s", array($this->Input->get("id"), $GLOBALS['SYC_VERSION'], $strVersion)), __CLASS__ . " " . __FUNCTION__, "INFO");

                        $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['MSC']['error'];
                        $arrContenData["error"] = true;
                        $arrContenData["error_msg"] = vsprintf($GLOBALS['TL_LANG']['ERR']['version'], array("syncCto", $GLOBALS['SYC_VERSION'], $strVersion));

                        break;
                    }

                    $strVersion = $this->objSyncCtoCommunicationClient->getVersionContao();

                    if (!version_compare($strVersion, VERSION, "="))
                    {
                        $this->log(vsprintf("Not the same version from contao on synchronization client ID %s. Serverversion: %s. Clientversion: %s", array($this->Input->get("id"), $GLOBALS['SYC_VERSION'], $strVersion)), __CLASS__ . " " . __FUNCTION__, "INFO");

                        $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['MSC']['error'];
                        $arrContenData["error"] = true;
                        $arrContenData["error_msg"] = vsprintf($GLOBALS['TL_LANG']['ERR']['version'], array("Contao", VERSION, $strVersion));

                        break;
                    }

                    $arrContenData["data"][1]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_2'];

                    $mixStepPool["step"] = 4;
                    break;

                /**
                 * Clear client and server temp folder  
                 */
                case 4:
                    $this->objSyncCtoCommunicationClient->purgeTemp();
                    $this->objSyncCtoFiles->purgeTemp();

                    // Current step is okay.
                    $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['MSC']['ok'];
                    $arrContenData["data"][1]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_1'];

                    // Create next step.
                    $arrContenData["data"][2]["state"] = $GLOBALS['TL_LANG']['MSC']['progress'];
                    $arrContenData["data"][2]["title"] = $GLOBALS['TL_LANG']['MSC']['step'] . " 2";
                    $arrContenData["data"][2]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1'];

                    $mixStepPool = FALSE;

                    $this->intStep++;

                    break;
            }
        }
        catch (Exception $exc)
        {
            $this->log(vsprintf("Error on synchronization client ID %s", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "ERROR");

            $arrContenData["error"] = true;
            $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['MSC']['error'];
            $arrContenData["error_msg"] = $exc->getMessage();
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
    private function pageSyncToShowStep2()
    {
        /* ---------------------------------------------------------------------
         * Init
         */

        // Needed files/information
        $mixFilelist = $this->Session->get("syncCto_Filelist");
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
        $arrContenData["error"] = false;
        $arrContenData["error_msg"] = "";
        $arrContenData["data"][2]["state"] = $GLOBALS['TL_LANG']['MSC']['progress'];

        /* ---------------------------------------------------------------------
         * Run page
         */

        // Check if there is a filelist
        if ($mixFilelist == FALSE && $intSyncTyp == SYNCCTO_SMALL)
        {
            $mixStepPool = FALSE;

            // Set current step informations
            $arrContenData["data"][2]["state"] = $GLOBALS['TL_LANG']['MSC']['skipped'];

            // Set next step information
            $arrContenData["data"][3]["state"] = $GLOBALS['TL_LANG']['MSC']['progress'];
            $arrContenData["data"][3]["title"] = $GLOBALS['TL_LANG']['MSC']['step'] . " 3";
            $arrContenData["data"][3]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_1'];

            $this->intStep++;
        }
        else
        {
            try
            {
                switch ($mixStepPool["step"])
                {
                    /**
                     * Build checksum list for 'files'
                     */
                    case 1:
                        if ($mixFilelist != false && is_array($mixFilelist) && ( $intSyncTyp == SYNCCTO_SMALL || $intSyncTyp == SYNCCTO_FULL ))
                        {
                            // Decode html special chars
                            foreach ($mixFilelist as $key => $value)
                            {
                                $mixFilelist[$key] = html_entity_decode($value);
                            }

                            // Write filelist to file
                            $this->arrListFile = $this->objSyncCtoFiles->runChecksumFiles($mixFilelist);
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
                        if ($intSyncTyp == SYNCCTO_FULL && $intSyncTyp != SYNCCTO_SMALL)
                        {
                            $this->arrListFile = array_merge($this->arrListFile, $this->objSyncCtoFiles->runChecksumCore());
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
                        $this->arrListCompare = $this->objSyncCtoCommunicationClient->runCecksumCompare($this->arrListFile);

                        $arrContenData["data"][2]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_2'];
                        $mixStepPool["step"] = 4;

                        break;

                    /**
                     * Check for deleted files
                     */
                    case 4:
                        switch ($intSyncTyp)
                        {
                            case SYNCCTO_FULL:
                                $arrChecksumClient = (array) $this->objSyncCtoCommunicationClient->getChecksumCore();
                                $this->arrListCompare = array_merge($this->arrListCompare, $this->objSyncCtoFiles->checkDeleteFiles($arrChecksumClient));

                            case SYNCCTO_SMALL:
                                $arrChecksumClient = (array) $this->objSyncCtoCommunicationClient->getChecksumCore();
                                $this->arrListCompare = array_merge($this->arrListCompare, $this->objSyncCtoFiles->checkDeleteFiles($arrChecksumClient));

                            default:
                                break;
                        }

                        $arrContenData["data"][2]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_3'];
                        $mixStepPool["step"] = 5;

                        break;

                    /**
                     * Set CSS
                     */
                    case 5:
                        foreach ($this->arrListCompare as $key => $value)
                        {
                            switch ($value["state"])
                            {
                                case SyncCtoEnum::FILESTATE_BOMBASTIC_BIG:
                                    $this->arrListCompare[$key]["css"] = "unknown";
                                    break;

                                case SyncCtoEnum::FILESTATE_NEED:
                                case SyncCtoEnum::FILESTATE_TOO_BIG_NEED:
                                    $this->arrListCompare[$key]["css"] = "modified";
                                    break;

                                case SyncCtoEnum::FILESTATE_MISSING:
                                case SyncCtoEnum::FILESTATE_TOO_BIG_MISSING:
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

                        $mixStepPool["step"] = 6;
                        break;

                    /**
                     * Show list with files and count
                     */
                    case 6:
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
                            $arrContenData["data"][2]["state"] = $GLOBALS['TL_LANG']['MSC']['ok'];
                            $arrContenData["data"][2]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1'];
                            $arrContenData["data"][2]["html"] = "";
                            $arrContenData["refresh"] = true;

                            // Set next step information
                            $arrContenData["data"][3]["state"] = $GLOBALS['TL_LANG']['MSC']['progress'];
                            $arrContenData["data"][3]["title"] = $GLOBALS['TL_LANG']['MSC']['step'] . " 3";
                            $arrContenData["data"][3]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_1'];

                            $this->intStep++;
                            $mixStepPool = false;
                            break;
                        }

                        // Counter
                        $intCountMissing = 0;
                        $intCountNeed = 0;
                        $intCountIgnored = 0;
                        $intCountDelete = 0;

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
                        $mixStepPool["need"] = $intCountNeed;
                        $mixStepPool["ignored"] = $intCountIgnored;
                        $mixStepPool["delete"] = $intCountDelete;

                        // Save files and go on or skip here
                        if ($intCountMissing == 0 && $intCountNeed == 0 && $intCountIgnored == 0 && $intCountDelete == 0)
                        {
                            // Set current step informations
                            $arrContenData["data"][2]["state"] = $GLOBALS['TL_LANG']['MSC']['skipped'];
                            $arrContenData["data"][2]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1'];
                            $arrContenData["data"][2]["html"] = "";
                            $arrContenData["refresh"] = true;

                            // Set next step information
                            $arrContenData["data"][3]["state"] = $GLOBALS['TL_LANG']['MSC']['progress'];
                            $arrContenData["data"][3]["title"] = $GLOBALS['TL_LANG']['MSC']['step'] . " 3";
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
                        $objTemp->direction = "To";
                        $objTemp->compare_complex = false;

                        // Build content                       
                        $arrContenData["data"][2]["description"] = vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_4'], array($intCountMissing, $intCountNeed, $intCountDelete, $intCountIgnored));
                        $arrContenData["data"][2]["html"] = $objTemp->parse();
                        $arrContenData["refresh"] = false;

                        $mixStepPool["step"] = 6;

                        break;
                }
            }
            catch (Exception $exc)
            {
                $this->log(vsprintf("Error on synchronization client ID %s", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "ERROR");

                $arrContenData["error"] = true;
                $arrContenData["data"][2]["state"] = $GLOBALS['TL_LANG']['MSC']['error'];
                $arrContenData["error_msg"] = $exc->getMessage();
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

        $this->Session->set("syncCto_StepPool2", $mixStepPool);
        $this->Session->set("syncCto_Content", $arrContenData);
    }

    /**
     * Split Files
     */
    private function pageSyncToShowStep3()
    {
        // State save for this step
        $mixStepPool = $this->Session->get("syncCto_StepPool3");
        if ($mixStepPool == FALSE)
        {
            $mixStepPool = array("step" => 1);
        }

        // Load content
        $arrContenData = $this->Session->get("syncCto_Content");

        // Set content back to normale mode
        $arrContenData["error"] = false;
        $arrContenData["error_msg"] = "";
        $arrContenData["data"][3]["state"] = $GLOBALS['TL_LANG']['MSC']['progress'];

        // Check if there is any file for upload
        if (count($this->arrListCompare) == 0 || !is_array($this->arrListCompare))
        {
            $arrContenData["data"][3]["state"] = $GLOBALS['TL_LANG']['MSC']['skipped'];

            $arrContenData["data"][4]["state"] = $GLOBALS['TL_LANG']['MSC']['progress'];
            $arrContenData["data"][4]["title"] = $GLOBALS['TL_LANG']['MSC']['step'] . " 4";
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
                            $arrContenData["error"] = true;
                            $arrContenData["error_msg"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']['error_step_3']['upload_ini'];

                            break;
                        }

                        $intClientUploadLimit = intval(str_replace("M", "000000", $arrClientParameter['upload_max_filesize']));
                        $intClientMemoryLimit = intval(str_replace("M", "000000", $arrClientParameter['memory_limit']));
                        $intClientPostLimit = intval(str_replace("M", "000000", $arrClientParameter['post_max_size']));
                        $intLocalMemoryLimit = intval(str_replace("M", "000000", ini_get('memory_limit')));

                        // Check if memory limit on server and client is enough for upload  
                        $intLimit = min($intClientUploadLimit, $intClientMemoryLimit, $intClientPostLimit, $intLocalMemoryLimit);

                        // Limit
                        if ($intLimit > 1073741824) // 1GB
                        {
                            $intPercent = 10;
                        }
                        else if ($intLimit > 524288000) // 500MB
                        {    
                            $intPercent = 20;
                        }
                        else if ($intLimit > 209715200) // 200MB
                        {
                            $intPercent = 50;
                        }
                        else
                        {
                            $intPercent = 70;
                        }

                        $intLimit = $intLimit / 100 * $intPercent;

                        $mixStepPool["limit"] = $intLimit;
                        $mixStepPool["percent"] = $intPercent;
                        $mixStepPool["step"] = 2;

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
                            $arrContenData["data"][3]["html"] = "";

                            $arrContenData["data"][4]["state"] = $GLOBALS['TL_LANG']['MSC']['progress'];
                            $arrContenData["data"][4]["title"] = $GLOBALS['TL_LANG']['MSC']['step'] . " 4";
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
                                $arrContenData["refresh"] = true;

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
                                $arrContenData["data"][3]["html"] = "";

                                $arrContenData["data"][4]["state"] = $GLOBALS['TL_LANG']['MSC']['progress'];
                                $arrContenData["data"][4]["title"] = $GLOBALS['TL_LANG']['MSC']['step'] . " 4";
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

                            $mixStepPool5["step"] = 2;
                            $mixStepPool5["splitfiles"] = $mixSplitFiles;
                            $mixStepPool5["splitfiles_count"] = 0;
                            $mixStepPool5["splitfiles_send"] = 0;

                            $objTemp = new BackendTemplate("be_syncCto_filelist");
                            $objTemp->filelist = $arrTempList;
                            $objTemp->id = $this->Input->get("id");
                            $objTemp->step = $this->intStep;
                            $objTemp->totalsize = $intTotalsize;
                            $objTemp->direction = "To";
                            $objTemp->compare_complex = true;

                            $arrContenData["data"][3]["html"] = $objTemp->parse();
                            $arrContenData["refresh"] = false;

                            break;
                        }
                        break;

                    /**
                     * Split files
                     */
                    case 3:
                        $intCountSplit = 0;
                        $intCount = 0;

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
                            $intSplits = $this->objSyncCtoFiles->splitFiles($value["path"], $GLOBALS['SYC_PATH']['tmp'] . $key, $key, ($mixStepPool["limit"] / 100 * $mixStepPool["percent"]));

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
                            $mixStepPool["step"] = 3;
                            $arrContenData["data"][3]["description"] = vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_2'], array($intCount, $intCountSplit));
                        }
                        else
                        {
                            $mixStepPool["step"] = 4;
                            $arrContenData["data"][3]["description"] = vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_2'], array($intCount, $intCountSplit));
                        }

                        break;

                    /**
                     * Send bigfiles 
                     */
                    case 4:
                        $intCountSplit = 0;
                        $intCount = 0;

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

                            if ($value["split"] == $value["split_transfer"])
                            {
                                $intCount++;
                                continue;
                            }

                            for ($ii = 0; $ii < $value["splitcount"]; $ii++)
                            {
                                $arrResponse = $this->objSyncCtoCommunicationClient->sendFile($GLOBALS['SYC_PATH']['tmp'] . $key, $value["splitname"] . ".sync" . $ii, "", SyncCtoEnum::UPLOAD_SYNC_SPLIT, $value["splitname"]);
                                $this->arrListCompare[$key]["split_transfer"] = $ii + 1;
                            }

                            break;
                        }

                        if ($intCount != $intCountSplit)
                        {
                            $mixStepPool["step"] = 4;
                            $arrContenData["data"][3]["description"] = vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_2'], array($intCount, $intCountSplit));
                        }
                        else
                        {
                            $mixStepPool["step"] = 5;
                            $arrContenData["data"][3]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_3'];
                        }

                        break;

                    case 5:
                        $intCountSplit = 0;
                        $intCount = 0;

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
                            $mixStepPool["step"] = 5;
                            $arrContenData["data"][3]["description"] = vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_4'], array($intCount, $intCountSplit));
                        }
                        else
                        {
                            $arrContenData["data"][3]["state"] = $GLOBALS['TL_LANG']['MSC']['ok'];
                            $arrContenData["data"][3]["description"] = vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_4'], array($intCount, $intCountSplit));

                            $arrContenData["data"][4]["state"] = $GLOBALS['TL_LANG']['MSC']['progress'];
                            $arrContenData["data"][4]["title"] = $GLOBALS['TL_LANG']['MSC']['step'] . " 4";
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

                $arrContenData["error"] = true;
                $arrContenData["data"][3]["state"] = $GLOBALS['TL_LANG']['MSC']['error'];
                $arrContenData["error_msg"] = $exc->getMessage();
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
    }

    /**
     * Build SQL zip and send it to the client
     */
    private function pageSyncToShowStep4()
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
        $arrContenData["error"] = false;
        $arrContenData["error_msg"] = "";
        $arrContenData["data"][4]["state"] = $GLOBALS['TL_LANG']['MSC']['progress'];


        $arrTables = $this->Session->get("syncCto_SyncTables");

        /* ---------------------------------------------------------------------
         * Run page
         */

        // Check if there is a tablelist
        if (count($arrTables) == 0 || $arrTables == FALSE)
        {
            $arrContenData["data"][4]["state"] = $GLOBALS['TL_LANG']['MSC']['skipped'];

            $arrContenData["data"][5]["state"] = $GLOBALS['TL_LANG']['MSC']['progress'];
            $arrContenData["data"][5]["title"] = $GLOBALS['TL_LANG']['MSC']['step'] . " 5";
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
                        $mixStepPool["step"] = 2;
                        break;

                    /**
                     * Build SQL Zip File
                     */
                    case 2:
                        $mixStepPool["zipname"] = $this->objSyncCtoDatabase->runDump($arrTables, true);
                        $arrContenData["data"][4]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_4"]['description_2'];
                        $mixStepPool["step"] = 3;
                        break;

                    /**
                     * Send file to client
                     */
                    case 3:
                        $arrResponse = $this->objSyncCtoCommunicationClient->sendFile($GLOBALS['SYC_PATH']['tmp'], $mixStepPool["zipname"], "", SyncCtoEnum::UPLOAD_SQL_TEMP);

                        // Check if the file was send and saved.
                        if (!is_array($arrResponse) || count($arrResponse) == 0)
                        {
                            throw new Exception("Empty file list from client. Maybe file send was not complet.");
                        }

                        $arrContenData["data"][4]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_4"]['description_3'];
                        $mixStepPool["step"] = 4;
                        break;

                    /**
                     * Import on client side
                     */
                    case 4:
                        $this->objSyncCtoCommunicationClient->startSQLImport($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "sql", $mixStepPool["zipname"]));

                        $arrContenData["data"][4]["state"] = $GLOBALS['TL_LANG']['MSC']['ok'];
                        $arrContenData["data"][4]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_4"]['description_4'];

                        $arrContenData["data"][5]["state"] = $GLOBALS['TL_LANG']['MSC']['progress'];
                        $arrContenData["data"][5]["title"] = $GLOBALS['TL_LANG']['MSC']['step'] . " 5";
                        $arrContenData["data"][5]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_1'];

                        $this->intStep++;
                        $mixStepPool == FALSE;

                        break;
                }
            }
            catch (Exception $exc)
            {
                $this->log(vsprintf("Error on synchronization client ID %s", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "ERROR");

                $arrContenData["error"] = true;
                $arrContenData["data"][4]["state"] = $GLOBALS['TL_LANG']['MSC']['error'];
                $arrContenData["error_msg"] = $exc->getMessage();
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
    private function pageSyncToShowStep5()
    {
        /* ---------------------------------------------------------------------
         * Init
         */

        // State save for this step
        $mixStepPool = $this->Session->get("syncCto_StepPool5");
        if ($mixStepPool == FALSE)
            $mixStepPool = array("step" => 1);

        // Load content
        $arrContenData = $this->Session->get("syncCto_Content");
        $arrContenData["error"] = false;
        $arrContenData["data"][5]["state"] = $GLOBALS['TL_LANG']['MSC']['progress'];

        // Needed files/information        
        $intSyncTyp = $this->Session->get("syncCto_Typ");
        $arrTables = $this->Session->get("syncCto_SyncTables");

        // Count files
        if (count($this->arrListCompare) != 0 && $this->arrListCompare != false && is_array($this->arrListCompare))
        {
            $intSkippCount = 0;
            $intSendCount = 0;
            $intWaitCount = 0;
            $intDelCount = 0;

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
        if (count($this->arrListCompare) == 0 || !is_array($this->arrListCompare) && $mixStepPool["step"] == 1)
        {
            $mixStepPool["step"] = 5;
        }

        try
        {
            $intStart = time();

            switch ($mixStepPool["step"])
            {
                /** ------------------------------------------------------------
                 * Check client parameter
                 */
                case 1:
                    // Load parameter from client
                    $arrClientParameter = $this->objSyncCtoCommunicationClient->getClientParameter();

                    // Check if everthing is okay
                    if ($arrClientParameter['file_uploads'] != 1)
                    {
                        $arrContenData["error"] = true;
                        $arrContenData["error_msg"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']['error_step_5']['upload_ini'];
                        $arrContenData["data"][5]["state"] = $GLOBALS['TL_LANG']['MSC']['error'];

                        break;
                    }

                    $mixStepPool["step"] = 2;
                    $mixStepPool["files_send"] = 0;
                    break;

                /** ------------------------------------------------------------
                 * Send files
                 */
                case 2:
                    // Send allfiles exclude the big things
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
                            $this->objSyncCtoCommunicationClient->sendFile(dirname($value["path"]), basename($value["path"]), $value["checksum"], SyncCtoEnum::UPLOAD_SYNC_TEMP);
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
                        $mixStepPool["step"] = 2;
                        $arrContenData["data"][5]["description"] = vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_2'], array($intSendCount, count($this->arrListCompare)));
                    }
                    else
                    {
                        $mixStepPool["step"] = 3;
                        $arrContenData["data"][5]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_1'];
                    }

                    break;

                /** ------------------------------------------------------------
                 * Import Files
                 */
                case 3:
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
                            $arrTransmission = $this->objSyncCtoCommunicationClient->startFileImport($arrImport);

                            foreach ($arrTransmission as $key => $value)
                            {
                                $this->arrListCompare[$key] = $arrTransmission[$key];
                            }
                        }
                    }

                    $mixStepPool["step"] = 4;
                    break;

                /** ------------------------------------------------------------
                 * Delete files
                 */
                case 4:
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
                    }

                    $arrContenData["data"][5]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_3'];
                    $mixStepPool["step"] = 5;
                    break;

                /** ------------------------------------------------------------
                 * Import Config
                 */
                case 5:
                    if ($intSyncTyp == SYNCCTO_FULL)
                    {
                        $this->objSyncCtoCommunicationClient->startLocalConfigImport();
                        $mixStepPool["step"] = 6;
                    }

                    $arrContenData["data"][5]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_1'];

                /** ------------------------------------------------------------
                 * Cleanup
                 */
                case 6:
                    $this->objSyncCtoCommunicationClient->purgeTemp();
                    $this->objSyncCtoFiles->purgeTemp();
                    $this->objSyncCtoCommunicationClient->refererEnable();

                    $this->log(vsprintf("Successfully finishing of synchronization client ID %s.", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "INFO");

                /** ------------------------------------------------------------
                 * Show information
                 */
                case 7:
                    if ($intSyncTyp == SYNCCTO_SMALL && ( (count($this->arrListCompare) == 0 || $this->arrListCompare == FALSE) && !is_array($this->arrListCompare)))
                    {
                        $mixStepPool["step"] = 7;

                        $arrContenData["data"][5]["html"] = "";
                        $arrContenData["data"][5]["state"] = $GLOBALS['TL_LANG']['MSC']['skipped'];
                        $arrContenData["data"][5]["description"] = "";
                        $arrContenData["finished"] = true;

                        break;
                    }
                    else if (count($this->arrListCompare) == 0 || $this->arrListCompare == FALSE || !is_array($this->arrListCompare))
                    {
                        $mixStepPool["step"] = 7;

                        $arrContenData["data"][5]["html"] = "";
                        $arrContenData["data"][5]["state"] = $GLOBALS['TL_LANG']['MSC']['ok'];
                        $arrContenData["data"][5]["description"] = "";
                        $arrContenData["finished"] = true;

                        break;
                    }
                    else
                    {
                        $mixStepPool["step"] = 7;

                        $arrContenData["data"][5]["description"] = vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_2'], array($intSendCount, count($this->arrListCompare)));
                        $arrContenData["data"][5]["state"] = $GLOBALS['TL_LANG']['MSC']['ok'];
                        $arrContenData["finished"] = true;
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
                                $compare .= "<li>" . htmlentities($valueInner) . "</li>";
                            }
                            $compare .= "</ul>";
                            $compare .= "</li>";
                        }
                        $compare .= "</ul>";
                    }

                    // Show filelist only in debug mode
                    if ($GLOBALS['TL_CONFIG']['syncCto_debug_filelist'] == true)
                    {
                        if (count($this->arrListCompare) != 0 && is_array($this->arrListCompare))
                        {
                            // Send Part

                            $compare .= '<br /><p class="tl_help">' . $intSendCount . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_5'] . '</p>';

                            $arrSort = array();

                            $compare .= '<ul class="fileinfo">';

                            $compare .= "<li>";
                            $compare .= '<strong>' . $GLOBALS['TL_LANG']['syncCto']['uploaded_files_list'] . '</strong>';
                            $compare .= "<ul>";

                            foreach ($this->arrListCompare as $key => $value)
                            {
                                if ($value["transmission"] != SyncCtoEnum::FILETRANS_SEND)
                                    continue;

                                if ($value["state"] == SyncCtoEnum::FILESTATE_DELETE)
                                    continue;

                                $compare .= "<li>";
                                $compare .= htmlentities($value["path"]);
                                $compare .= "</li>";
                            }
                            $compare .= "</ul>";
                            $compare .= "</li>";
                            $compare .= "</ul>";

                            //---------

                            $compare .= '<ul class="fileinfo">';

                            $compare .= "<li>";
                            $compare .= '<strong>' . $GLOBALS['TL_LANG']['syncCto']['deleted_files_list'] . '</strong>';
                            $compare .= "<ul>";

                            foreach ($this->arrListCompare as $key => $value)
                            {
                                if ($value["transmission"] != SyncCtoEnum::FILETRANS_SEND)
                                    continue;

                                if ($value["state"] != SyncCtoEnum::FILESTATE_DELETE)
                                    continue;

                                $compare .= "<li>";
                                $compare .= htmlentities($value["path"]);
                                $compare .= "</li>";
                            }
                            $compare .= "</ul>";
                            $compare .= "</li>";
                            $compare .= "</ul>";

                            // Not sended, still waiting

                            $compare .= '<br /><p class="tl_help">' . $intWaitCount . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_6'] . '</p>';

                            $arrSort = array();

                            $compare .= '<ul class="fileinfo">';

                            $compare .= "<li>";
                            $compare .= '<strong>' . $GLOBALS['TL_LANG']['syncCto']['uploaded_files'] . '</strong>';
                            $compare .= "<ul>";

                            foreach ($this->arrListCompare as $key => $value)
                            {
                                if ($value["transmission"] != SyncCtoEnum::FILETRANS_WAITING)
                                    continue;

                                $compare .= "<li>";
                                $compare .= htmlentities($value["path"]);
                                $compare .= "</li>";
                            }
                            $compare .= "</ul>";
                            $compare .= "</li>";
                            $compare .= "</ul>";
                        }
                    }

                    $arrContenData["data"][5]["html"] = $compare;
                    break;
            }
        }
        catch (Exception $exc)
        {
            $this->log(vsprintf("Error on synchronization client ID %s", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "ERROR");

            $arrContenData["error"] = true;
            $arrContenData["data"][5]["state"] = $GLOBALS['TL_LANG']['MSC']['error'];
            $arrContenData["error_msg"] = $exc->getMessage();
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

        // Load content
        $arrContenData = $this->Session->get("syncCto_Content");

        // Set content back to normale mode
        $arrContenData["error"] = false;
        $arrContenData["error_msg"] = "";
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
                    $arrContenData["data"][1]["title"] = $GLOBALS['TL_LANG']['MSC']['step'] . " 1";
                    $arrContenData["data"][1]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_1'];
                    $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['MSC']['progress'];

                    $mixStepPool["step"] = 2;
                    break;

                /**
                 * Referer check deactivate
                 */
                case 2:
                    if (!$this->objSyncCtoCommunicationClient->refererDisable())
                    {
                        $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['MSC']['error'];
                        $arrContenData["error"] = true;
                        $arrContenData["error_msg"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']['error_step_1']['referer'];

                        break;
                    }

                    $mixStepPool["step"] = 3;
                    break;


                /**
                 * Check version
                 */
                case 3:
                    $strVersion = $this->objSyncCtoCommunicationClient->getVersionSyncCto();

                    if (!version_compare($strVersion, $GLOBALS['SYC_VERSION'], "="))
                    {
                        $this->log(vsprintf("Not the same version from syncCto on synchronization client ID %s. Serverversion: %s. Clientversion: %s", array($this->Input->get("id"), $GLOBALS['SYC_VERSION'], $strVersion)), __CLASS__ . " " . __FUNCTION__, "INFO");

                        $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['MSC']['error'];
                        $arrContenData["error"] = true;
                        $arrContenData["error_msg"] = vsprintf($GLOBALS['TL_LANG']['ERR']['version'], array("syncCto", $GLOBALS['SYC_VERSION'], $strVersion));

                        break;
                    }

                    $strVersion = $this->objSyncCtoCommunicationClient->getVersionContao();

                    if (!version_compare($strVersion, VERSION, "="))
                    {
                        $this->log(vsprintf("Not the same version from contao on synchronization client ID %s. Serverversion: %s. Clientversion: %s", array($this->Input->get("id"), $GLOBALS['SYC_VERSION'], $strVersion)), __CLASS__ . " " . __FUNCTION__, "INFO");

                        $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['MSC']['error'];
                        $arrContenData["error"] = true;
                        $arrContenData["error_msg"] = vsprintf($GLOBALS['TL_LANG']['ERR']['version'], array("Contao", VERSION, $strVersion));

                        break;
                    }

                    $arrContenData["data"][1]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_2'];

                    $mixStepPool["step"] = 4;
                    break;

                /**
                 * Clear client and server temp folder  
                 */
                case 4:
                    $this->objSyncCtoCommunicationClient->purgeTemp();
                    $this->objSyncCtoFiles->purgeTemp();

                    // Current step is okay.
                    $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['MSC']['ok'];
                    $arrContenData["data"][1]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_1'];

                    // Create next step.
                    $arrContenData["data"][2]["state"] = $GLOBALS['TL_LANG']['MSC']['progress'];
                    $arrContenData["data"][2]["title"] = $GLOBALS['TL_LANG']['MSC']['step'] . " 2";
                    $arrContenData["data"][2]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1'];

                    $mixStepPool = FALSE;

                    $this->intStep++;

                    break;
            }
        }
        catch (Exception $exc)
        {
            $this->log(vsprintf("Error on synchronization client ID %s", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "ERROR");

            $arrContenData["error"] = true;
            $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['MSC']['error'];
            $arrContenData["error_msg"] = $exc->getMessage();
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
        $mixFilelist = $this->Session->get("syncCto_Filelist");
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
        $arrContenData["error"] = false;
        $arrContenData["error_msg"] = "";
        $arrContenData["data"][2]["state"] = $GLOBALS['TL_LANG']['MSC']['progress'];

        /* ---------------------------------------------------------------------
         * Run page
         */

        // Check if there is a filelist
        if ($mixFilelist == FALSE && $intSyncTyp == SYNCCTO_SMALL)
        {
            $mixStepPool = FALSE;

            // Set current step informations
            $arrContenData["data"][2]["state"] = $GLOBALS['TL_LANG']['MSC']['skipped'];

            // Set next step information
            $arrContenData["data"][3]["state"] = $GLOBALS['TL_LANG']['MSC']['progress'];
            $arrContenData["data"][3]["title"] = $GLOBALS['TL_LANG']['MSC']['step'] . " 3";
            $arrContenData["data"][3]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_1'];

            $this->intStep++;
        }
        else
        {
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
                        if ($intSyncTyp == SYNCCTO_FULL && $intSyncTyp != SYNCCTO_SMALL)
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
                     * Check files
                     */
                    case 3:
                        $this->arrListCompare = $this->objSyncCtoFiles->runCecksumCompare($this->arrListFile);

                        $arrContenData["data"][2]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_2'];
                        $mixStepPool["step"] = 4;

                        break;

                    /**
                     * Check for deleted files
                     */
                    case 4:
                        switch ($intSyncTyp)
                        {
                            case SYNCCTO_FULL:
                                $arrChecksumClient = (array) $this->objSyncCtoFiles->runChecksumCore();
                                $this->arrListCompare = array_merge($this->arrListCompare, $this->objSyncCtoCommunicationClient->checkDeleteFiles($arrChecksumClient));

                            case SYNCCTO_SMALL:
                                $arrChecksumClient = (array) $this->objSyncCtoFiles->runChecksumFiles();
                                $this->arrListCompare = array_merge($this->arrListCompare, $this->objSyncCtoFiles->checkDeleteFiles($arrChecksumClient));

                            default:
                                break;
                        }

                        $arrContenData["data"][2]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_3'];
                        $mixStepPool["step"] = 5;

                        break;

                    /**
                     * Set CSS
                     */
                    case 5:
                        foreach ($this->arrListCompare as $key => $value)
                        {
                            switch ($value["state"])
                            {
                                case SyncCtoEnum::FILESTATE_BOMBASTIC_BIG:
                                    $this->arrListCompare[$key]["css"] = "unknown";
                                    break;

                                case SyncCtoEnum::FILESTATE_NEED:
                                case SyncCtoEnum::FILESTATE_TOO_BIG_NEED:
                                    $this->arrListCompare[$key]["css"] = "modified";
                                    break;

                                case SyncCtoEnum::FILESTATE_MISSING:
                                case SyncCtoEnum::FILESTATE_TOO_BIG_MISSING:
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

                        $mixStepPool["step"] = 6;
                        break;

                    /**
                     * Show list with files and count
                     */
                    case 6:
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
                            $arrContenData["data"][2]["state"] = $GLOBALS['TL_LANG']['MSC']['ok'];
                            $arrContenData["data"][2]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1'];
                            $arrContenData["data"][2]["html"] = "";
                            $arrContenData["refresh"] = true;

                            // Set next step information
                            $arrContenData["data"][3]["state"] = $GLOBALS['TL_LANG']['MSC']['progress'];
                            $arrContenData["data"][3]["title"] = $GLOBALS['TL_LANG']['MSC']['step'] . " 3";
                            $arrContenData["data"][3]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_1'];

                            $this->intStep++;
                            $mixStepPool = false;
                            break;
                        }

                        // Counter
                        $intCountMissing = 0;
                        $intCountNeed = 0;
                        $intCountIgnored = 0;
                        $intCountDelete = 0;

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
                        $mixStepPool["need"] = $intCountNeed;
                        $mixStepPool["ignored"] = $intCountIgnored;
                        $mixStepPool["delete"] = $intCountDelete;

                        // Save files and go on or skip here
                        if ($intCountMissing == 0 && $intCountNeed == 0 && $intCountIgnored == 0 && $intCountDelete == 0)
                        {
                            // Set current step informations
                            $arrContenData["data"][2]["state"] = $GLOBALS['TL_LANG']['MSC']['skipped'];
                            $arrContenData["data"][2]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1'];
                            $arrContenData["data"][2]["html"] = "";
                            $arrContenData["refresh"] = true;

                            // Set next step information
                            $arrContenData["data"][3]["state"] = $GLOBALS['TL_LANG']['MSC']['progress'];
                            $arrContenData["data"][3]["title"] = $GLOBALS['TL_LANG']['MSC']['step'] . " 3";
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
                        $objTemp->direction = "To";
                        $objTemp->compare_complex = false;

                        // Build content                       
                        $arrContenData["data"][2]["description"] = vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_4'], array($intCountMissing, $intCountNeed, $intCountDelete, $intCountIgnored));
                        $arrContenData["data"][2]["html"] = $objTemp->parse();
                        $arrContenData["refresh"] = false;

                        $mixStepPool["step"] = 6;

                        break;
                }
            }
            catch (Exception $exc)
            {
                $this->log(vsprintf("Error on synchronization client ID %s", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "ERROR");

                $arrContenData["error"] = true;
                $arrContenData["data"][2]["state"] = $GLOBALS['TL_LANG']['MSC']['error'];
                $arrContenData["error_msg"] = $exc->getMessage();
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

        $this->Session->set("syncCto_Content", $arrContenData);
        $this->Session->set("syncCto_StepPool2", $mixStepPool);
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

?>