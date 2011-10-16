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
class SyncCtoModuleClient extends BackendModule
{

    // Variablen
    protected $strTemplate = 'be_syncCto_steps';
    protected $objTemplateContent;
    protected $step;
    protected $clientID;
    // Helper Class
    protected $objSyncCtoDatabase;
    protected $objSyncCtoFiles;
    protected $objSyncCtoCommunicationClient;
    protected $objSyncCtoCallback;
    protected $objSyncCtoHelper;
    protected $objSyncCtoMeasurement;

    function __construct(DataContainer $objDc = null)
    {
        parent::__construct($objDc);

        // Load Helper
        $this->objSyncCtoDatabase = SyncCtoDatabase::getInstance();
        $this->objSyncCtoFiles = SyncCtoFiles::getInstance();
        $this->objSyncCtoCallback = SyncCtoCallback::getInstance();
        $this->objSyncCtoCommunicationClient = SyncCtoCommunicationClient::getInstance();
        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();

        // Load language
        $this->loadLanguageFile('SyncCto');
    }

    /* -------------------------------------------------------------------------
     * Core Functions
     */

    /**
     * Generate page
     */
    protected function compile()
    {
        if ($this->Input->get("act") != "start")
        {
            $_SESSION["TL_ERROR"] = array("Don't try to call the sync function directly.");
            $this->redirect("contao/main.php?do=synccto_clients");
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
        }
    }

    /* -------------------------------------------------------------------------
     * Functions for comunication
     */

    private function pageSyncTo()
    {
        // Build Step
        if ($this->Input->get("step") == "" || $this->Input->get("step") == null)
            $this->step = 1;
        else
            $this->step = intval($this->Input->get("step"));

        // Load language and template
        $this->loadLanguageFile('tl_syncCto_clients_syncTo');
        $this->loadLanguageFile('syncCto');
        $this->loadLanguageFile('tl_syncCto_steps');

        // Set client for communication
        try
        {
            $this->objSyncCtoCommunicationClient->setClientBy(intval($this->Input->get("id")));
        }
        catch (Exception $exc)
        {
            $_SESSION["TL_ERROR"] = array("Could not set client");
            $this->redirect("contao/main.php?do=synccto_clients");
        }

        // Do step x
        switch ($this->step)
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
    }

    /* -------------------------------------------------------------------------
     * Start SyncCto Sync. To
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
            $mixStepPool = array("step" => 1);

        // Load content
        $arrContenData = $this->Session->get("syncCto_Content");
        $arrContenData["error"] = false;
        $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['progress'];

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
                    $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['progress'];
                    $arrContenData["data"][1]["title"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['step'] . " 1";
                    $arrContenData["data"][1]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step1_msg1'];

                    $arrContenData["step"] = $this->step - 1;
                    $mixStepPool["step"] = 2;
                    break;

                /**
                 * Referer check deactivate
                 */
                case 2:
                    if (!$this->objSyncCtoCommunicationClient->refererDisable())
                    {
                        $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['error'];
                        $arrContenData["error"] = true;
                        $arrContenData["error_msg"] = "Could not deactivate refferenz check.";

                        break;
                    }

                    $arrContenData["step"] = $this->step - 1;
                    $mixStepPool["step"] = 3;
                    break;


                /**
                 * Check version
                 */
                case 3:
                    $strVersion = $this->objSyncCtoCommunicationClient->getVersionSyncCto();
                    if ($strVersion != $GLOBALS['SYC_VERSION'])
                    {
                        $this->log(vsprintf("Not the same version on synchronization client ID %s. Serverversion: %s. Clientversion: %s", array($this->Input->get("id"), $strVersion, $strVersion)), __CLASS__ . " " . __FUNCTION__, "INFO");

                        $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['error'];
                        $arrContenData["error"] = true;
                        $arrContenData["error_msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step1_msg2'] . 1;

                        break;
                    }

                    $arrContenData["step"] = $this->step - 1;
                    $mixStepPool["step"] = 4;
                    break;

                /**
                 * Clear client and server temp folder  
                 */
                case 4:
                    $this->objSyncCtoCommunicationClient->purgeTemp();
                    $this->objSyncCtoFiles->purgeTemp();

                case 5:

                    $mixStepPool["step"] = 5;

                    // Current step is okay.
                    $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['ok'];
                    $arrContenData["step"] = $this->step;
                    // Create next step.
                    $arrContenData["data"][2]["state"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['progress'];
                    $arrContenData["data"][2]["title"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['step'] . " 2";
                    $arrContenData["data"][2]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step2_help'];

                    $arrContenData["step"] = $this->step;

                    break;
            }
        }
        catch (Exception $exc)
        {
            $this->log(vsprintf("Error on synchronization client ID %s", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "ERROR");

            $arrContenData["error"] = true;
            $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['error'];
            $arrContenData["error_msg"] = $exc->getMessage();
        }

        $this->Template->goBack = $this->script . $arrContenData["goBack"];
        $this->Template->data = $arrContenData["data"];
        $this->Template->step = $arrContenData["step"];
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
     * Build checksumlist and ask client
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
            $mixStepPool = array("step" => 1);

        // Load content
        $arrContenData = $this->Session->get("syncCto_Content");
        $arrContenData["error"] = false;
        $arrContenData["data"][2]["state"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['progress'];
        $arrContenData["step"] = $this->step - 1;

        /* ---------------------------------------------------------------------
         * Run page
         */

        // Check if there is a filelist
        if ($mixFilelist == FALSE && $intSyncTyp == SYNCCTO_SMALL)
        {
            $mixStepPool = FALSE;

            // Set current step informations
            $arrContenData["data"][2]["state"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['skipped'];
            $arrContenData["data"][2]["title"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['step'] . " 2";
            $arrContenData["data"][2]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step2_help'];

            // Set next step information
            $arrContenData["data"][3]["state"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['progress'];
            $arrContenData["data"][3]["title"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['step'] . " 3";
            $arrContenData["data"][3]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step5_help'];

            $arrContenData["step"]++;
        }
        else
        {
            try
            {
                switch ($mixStepPool["step"])
                {
                    // Build checksum list for 'tl_files'
                    case 1:
                        if ($mixFilelist != false && is_array($mixFilelist) && ( $intSyncTyp == SYNCCTO_SMALL || $intSyncTyp == SYNCCTO_FULL ))
                        {
                            $mixStepPool["tlfiles_checksum"] = $this->objSyncCtoFiles->runChecksumTlFiles($mixFilelist);
                            $mixStepPool["step"] = 2;
                            break;
                        }
                        else
                        {
                            $mixStepPool["tlfiles_checksum"] = array();
                            $mixStepPool["step"] = 2;
                            break;
                        }

                    // Build checksum list for Conta core
                    case 2:
                        if ($intSyncTyp == SYNCCTO_FULL && $intSyncTyp != SYNCCTO_SMALL)
                        {
                            $mixStepPool["core_checksum"] = $this->objSyncCtoFiles->runChecksumCore();
                            $mixStepPool["step"] = 3;
                            break;
                        }
                        else
                        {
                            $mixStepPool["core_checksum"] = array();
                            $mixStepPool["step"] = 3;
                            break;
                        }

                    // Merge both lists and send it to the client
                    case 3:
                        $arrChecksum = array_merge($mixStepPool["tlfiles_checksum"], $mixStepPool["core_checksum"]);

                        unset($mixStepPool["tlfiles_checksum"]);
                        unset($mixStepPool["core_checksum"]);

                        $mixStepPool["checksum"] = $arrChecksum;
                        $mixStepPool["sync_list"] = $this->objSyncCtoCommunicationClient->runCecksumCompare($arrChecksum);

                        $mixStepPool["step"] = 4;

                        break;

                    // Check for deleted files
                    case 4:
                        $arrSyncFileList = $mixStepPool["sync_list"];

                        $arrChecksumClient = (array) $this->objSyncCtoCommunicationClient->getChecksumCore();

                        foreach ($arrChecksumClient as $keyItem => $valueItem)
                        {
                            if (!file_exists($this->objSyncCtoHelper->buildPath($valueItem["path"])))
                            {
                                $arrSyncFileList[$keyItem] = $valueItem;
                                $arrSyncFileList[$keyItem]["state"] = SyncCtoEnum::FILESTATE_DELETE;
                                $arrSyncFileList[$keyItem]["css"] = "deleted";
                            }
                        }

                        unset($arrChecksumClient);

                        $mixStepPool["step"] = 5;
                        $mixStepPool["sync_list"] = $arrSyncFileList;

                        break;

                    // Set CSS
                    case 5:
                        $arrSyncFileList = $mixStepPool["sync_list"];

                        foreach ($arrSyncFileList as $key => $value)
                        {
                            switch ($value["state"])
                            {
                                case SyncCtoEnum::FILESTATE_BOMBASTIC_BIG:
                                    $arrSyncFileList[$key]["css"] = "unknown";
                                    break;

                                case SyncCtoEnum::FILESTATE_NEED:
                                case SyncCtoEnum::FILESTATE_TOO_BIG_NEED:
                                    $arrSyncFileList[$key]["css"] = "modified";
                                    break;

                                case SyncCtoEnum::FILESTATE_MISSING:
                                case SyncCtoEnum::FILESTATE_TOO_BIG_MISSING:
                                    $arrSyncFileList[$key]["css"] = "new";
                                    break;

                                case SyncCtoEnum::FILESTATE_DELETE:
                                    $arrSyncFileList[$key]["css"] = "deleted";
                                    break;

                                default:
                                    $arrSyncFileList[$key]["css"] = "unknown";
                                    break;
                            }
                        }

                        $mixStepPool["sync_list"] = $arrSyncFileList;
                        $mixStepPool["step"] = 6;

                        $arrContenData["step"] = $this->step - 1;

                        break;

                    // Show list with files and count
                    case 6:
                        $arrSyncFileList = $mixStepPool["sync_list"];

                        // Del and submit Function
                        $arrDel = $_POST;

                        if (key_exists("delete", $arrDel))
                        {
                            foreach ($arrDel as $key => $value)
                            {
                                unset($arrSyncFileList[$value]);
                                unset($mixStepPool["sync_list"][$value]);
                            }
                        }
                        else if (key_exists("transfer", $arrDel))
                        {

                            $arrContenData["data"][2]["state"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['ok'];
                            $arrContenData["step"] = $this->step;
                            $arrContenData["refresh"] = true;

                            // Set next step information
                            $arrContenData["data"][3]["state"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['progress'];
                            $arrContenData["data"][3]["title"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['step'] . " 3";
                            $arrContenData["data"][3]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step5_help'];

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
                        foreach ($arrSyncFileList as $key => $value)
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

                            $intTotalSize += $value["size"];
                        }

                        $mixStepPool["missing"] = $intCountMissing;
                        $mixStepPool["need"] = $intCountNeed;
                        $mixStepPool["ignored"] = $intCountIgnored;
                        $mixStepPool["delete"] = $intCountDelete;

                        // Save files and go on or skip here
                        if ($intCountMissing == 0 && $intCountNeed == 0 && $intCountIgnored == 0 && $intCountDelete == 0)
                        {
                            $arrContenData["data"][2]["state"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['skipped'];
                            $arrContenData["step"] = $this->step;
                            $arrContenData["refresh"] = true;

                            // Set next step information
                            $arrContenData["data"][3]["state"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['progress'];
                            $arrContenData["data"][3]["title"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['step'] . " 3";
                            $arrContenData["data"][3]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step5_help'];

                            $mixStepPool = false;
                            $arrSyncFileList = false;

                            break;
                        }

                        $objTemp = new BackendTemplate("be_syncCto_filelist");
                        $objTemp->filelist = $arrSyncFileList;
                        $objTemp->id = $this->Input->get("id");
                        $objTemp->step = $this->step;
                        $objTemp->totalsize = $intTotalSize;
                        $objTemp->direction = "To";
                        $objTemp->compare_complex = false;

                        // Build content                       
                        $arrContenData["data"][2]["html"] = $objTemp->parse();
                        $arrContenData["refresh"] = false;

                        $mixStepPool["step"] = 6;
                        $arrContenData["step"] = $this->step - 1;

                        break;
                }
            }
            catch (Exception $exc)
            {
                $this->log(vsprintf("Error on synchronization client ID %s", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "ERROR");

                $arrContenData["error"] = true;
                $arrContenData["data"][2]["state"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['error'];
                $arrContenData["error_msg"] = $exc->getMessage();
            }
        }

        $this->Template->goBack = $this->script . $arrContenData["goBack"];
        $this->Template->data = $arrContenData["data"];
        $this->Template->step = $arrContenData["step"];
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

        print(count($arrSyncFileList));
        echo " | ";
        print(count($this->Session->get("syncCto_SyncFiles")));

        $this->Session->set("syncCto_SyncFiles", $arrSyncFileList);
    }

    /**
     * Split Files
     */
    private function pageSyncToShowStep3()
    {
        // State save for this step
        $mixStepPool = $this->Session->get("syncCto_StepPool3");
        if ($mixStepPool == FALSE)
            $mixStepPool = array("step" => 1);

        // Filelist
        $arrSyncFileList = $this->Session->get("syncCto_SyncFiles");
        //var_dump($this->Session->get("syncCto_SyncFiles"));
        //exit();


        // Load content
        $arrContenData = $this->Session->get("syncCto_Content");
        $arrContenData["error"] = false;
        $arrContenData["data"][3]["state"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['progress'];
        $arrContenData["step"] = $this->step - 1;


        // Check if there is any file for upload
        if ($arrSyncFileList == false)
        {
            $arrContenData["data"][3]["state"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['skipped'];
            echo "empty";
            exit();
        }
        else
        {
            try
            {
                // Timer 
                $intStar = time();

                switch ($mixStepPool["step"])
                {
                    // Load parameter from client
                    case 1:
                        $arrClientParameter = $this->objSyncCtoCommunicationClient->getClientParameter();

                        // Check if everthing is okay
                        if ($arrClientParameter['file_uploads'] != 1)
                        {
                            $arrContenData["error"] = true;
                            $arrContenData["error_msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step5_msg2'];
                            $arrContenData["data"][3]["state"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['error'];

                            break;
                        }

                        $intClientUploadLimit = intval(str_replace("M", "000000", $arrClientParameter['upload_max_filesize']));
                        $intClientMemoryLimit = intval(str_replace("M", "000000", $arrClientParameter['memory_limit']));
                        $intClientPostLimit = intval(str_replace("M", "000000", $arrClientParameter['post_max_size']));
                        $intLocalMemoryLimit = intval(str_replace("M", "000000", ini_get('memory_limit')));

                        // Check if memory limit on server and client is enough for upload  
                        $intLimit = min($intClientUploadLimit, $intClientMemoryLimit, $intClientPostLimit, $intLocalMemoryLimit);

                        // Limit
                        if ($intLimit > 1000000000)
                            $intPercent = 40;
                        else if ($intLimit > 500000000)
                            $intPercent = 40;
                        else if ($intLimit > 100000000)
                            $intPercent = 50;
                        else if ($intLimit > 50000000)
                            $intPercent = 60;
                        else
                            $intPercent = 70;

                        $intLimit = $intLimit / 100 * $intPercent;

                        $mixStepPool["limit"] = $intLimit;
                        $mixStepPool["percent"] = $intPercent;
                        $mixStepPool["step"] = 2;

                        var_dump($mixStepPool);

                        exit();


                        break;

                    /**
                     * Search for big file
                     */
                    case 2:
                        foreach ($mixSyncFiles as $key => $value)
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
                                $mixSyncFiles[$key]["split"] = true;
                            }
                        }

                        $intCountSplit = 0;

                        foreach ($mixSyncFiles as $key => $value)
                        {
                            if ($value["split"] == true)
                                $intCountSplit++;
                        }

                        // Skip page if no big file is found
                        if ($intCountSplit == 0)
                        {
                            $arrContenData["data"][3]["state"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['skipped'];
                            $arrContenData["data"][3]["html"] = "";
                            $arrContenData["step"]++;

                            // TODO Next Step her 

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
                                    if (key_exists($value, $mixSyncFiles))
                                    {
                                        unset($mixSyncFiles[$value]);
                                    }
                                }
                            }
                            else if (is_array($arrDel) && key_exists("transfer", $arrDel))
                            {
                                $mixStepPool["step"] = 3;

                                $arrContenData["data"][3]["description"] = vsprintf($GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step4_msg3'], array(0, $intCountSplit));
                                $arrContenData["data"][3]["html"] = "";

                                break;
                            }

                            $intCountSplit = 0;

                            foreach ($mixSyncFiles as $key => $value)
                            {
                                if ($value["split"] == true)
                                    $intCountSplit++;
                            }

                            if ($intCountSplit == 0)
                            {
                                $arrContenData["data"][3]["state"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['skipped'];
                                $arrContenData["data"][3]["html"] = "";
                                $arrContenData["step"]++;

                                // TODO Next Step her 

                                break;
                            }

                            // Build list
                            foreach ($mixSyncFiles as $key => $value)
                            {
                                if ($value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_DELETE ||
                                        $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_MISSING ||
                                        $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_NEED ||
                                        $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_SAME ||
                                        $value["state"] == SyncCtoEnum::FILESTATE_BOMBASTIC_BIG ||
                                        $value["state"] == SyncCtoEnum::FILESTATE_DELETE)
                                {
                                    $arrTempList[$key] = $mixSyncFiles[$key];
                                    $intTotalsize += $value["size"];
                                }
                                else if ($value["split"] == 1)
                                {
                                    $arrTempList[$key] = $mixSyncFiles[$key];
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
                            $objTemp->step = $this->step;
                            $objTemp->totalsize = $intTotalsize;
                            $objTemp->direction = "To";
                            $objTemp->compare_complex = true;

                            $arrContenData["data"][3]["html"] = $objTemp->parse();
                            $arrContenData["data"][3]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step4_msg2'];
                            $arrContenData["refresh"] = false;

                            break;
                        }
                        break;

                    /**
                     * Split files
                     */
                    case 3:
                        foreach ($mixSyncFiles as $key => $value)
                        {
                            if ($value["split"] != true)
                                continue;

                            if ($mixSyncFiles["split"] != 0 && $mixSyncFiles["splitname"] != "")
                                continue;

                            // Splitt file
                            $intSplits = $this->objSyncCtoFiles->
                                    splitFiles($mixSyncFiles[$key]["path"], $GLOBALS['SYC_PATH']['tmp'] . $key, $key, ($mixStepPool5["limit"] / 100 * $mixStepPool5["percent"]));

                            $mixSyncFiles[$key]["splitcount"] = $intSplits;
                            $mixSyncFiles[$key]["splitname"] = $key;

                            // Check if we are in time or show page
                            if ($intStar < time() - 30)
                            {
                                break;
                            }
                        }

                        if ($intStar < time() - 30)
                        {
                            $mixStepPool5["step"] = 3;
                        }
                        else
                        {
                            $mixStepPool5["step"] = 4;
                        }

                        $arrContent[$this->step - 1]["state"] = WORK;
                        $arrContent[$this->step - 1]["msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step4_msg2'];

                        $this->step--;

                        break;

                    /**
                     * Send bigfiles 
                     */
                    case 4:
                        foreach ($mixSyncFiles as $key => $value)
                        {
                            if ($value["split"] != true)
                                continue;

                            if ($value["transmission"] == SyncCtoEnum::FILETRANS_SEND)
                                continue;

                            if ($value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_DELETE ||
                                    $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_MISSING ||
                                    $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_NEED ||
                                    $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_SAME ||
                                    $value["state"] == SyncCtoEnum::FILESTATE_BOMBASTIC_BIG ||
                                    $value["state"] == SyncCtoEnum::FILESTATE_DELETE)
                                continue;

                            for ($ii = 0; $ii < $value["splitcount"]; $ii++)
                            {
                                $this->objSyncCtoCommunicationClient->sendFile($GLOBALS['SYC_PATH']['tmp'] . $key, $value["splitname"] . ".sync" . $ii, "", UPLOAD_SYNC_SPLIT, $value["splitname"]);
                            }

                            $this->objSyncCtoCommunicationClient->buildSingleFile($value["splitname"], $value["splitcount"], $value["path"], $value["checksum"]);

                            $mixSyncFiles[$key]["transmission"] = SyncCtoEnum::FILETRANS_SEND;

                            if ($intStar < time() - 30)
                            {
                                break;
                            }
                        }

                        if ($intStar < time() - 30)
                        {
                            $mixStepPool5["step"] = 4;

                            $arrContent[$this->step - 1]["state"] = WORK;
                            $arrContent[$this->step - 1]["msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step4_msg2'];

                            $this->step--;
                        }
                        else
                        {

                            $intCountSplitTotal = 0;
                            $intCountSplitSend = 0;

                            foreach ($mixSyncFiles as $key => $value)
                            {
                                if ($value["split"] == true)
                                    $intCountSplitTotal++;

                                if ($value["split"] == true && $value["transmission"] == SyncCtoEnum::FILETRANS_SEND)
                                    $intCountSplitSend++;
                            }

                            $arrContent[$this->step - 1]["state"] = OK;
                            $arrContent[$this->step - 1]["msg"] = vsprintf($GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step4_msg3'], array($intCountSplitSend, $intCountSplitTotal));
                            $arrContent[] = array(
                                "step" => $this->step,
                                "state" => WORK,
                                "msg" => "",
                                "error" => "",
                                "refresh" => true,
                                "desc" => $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step5_msg1']
                            );

                            $this->Session->set("syncCto_StepPool5", FALSE);
                        }

                        break;
                }
            }
            catch (Exception $exc)
            {
                $this->log(vsprintf("Error on synchronization client ID %s", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "ERROR");

                $arrContent[$this->step - 1]["state"] = ERROR;
                $arrContent[$this->step - 1]["error"] = $exc->getMessage();
                $arrContent[$this->step - 1]["refresh"] = FALSE;
            }
        }

        $this->Template->goBack = $this->script . $arrContenData["goBack"];
        $this->Template->data = $arrContenData["data"];
        $this->Template->step = $arrContenData["step"];
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
        $this->Session->set("syncCto_SyncFiles", $arrSyncFileList);
    }

    /**
     * Build SQL Zip and Send it to client
     */
    private function pageSyncToShowStep16()
    {
        echo "step 3";
        exit(1);

        /* ---------------------------------------------------------------------
         * Init
         */

        // Time out 
        @set_time_limit(60);

        // State save for this step
        $mixStepPool4 = $this->get("syncCto_StepPool4");
        if ($mixStepPool4 == FALSE)
            $mixStepPool4 = array("step" => 1);

        $mixSyncTable = $this->get("syncCto_SyncTables");

        $arrContent = $this->loadContent();

        /* ---------------------------------------------------------------------
         * Run page
         */

        // Check if there is a tablelist
        if ($mixSyncTable == FALSE)
        {
            $mixStepPool4 = FALSE;

            $arrContent[$this->step - 1]["state"] = SKIPPED;
            $arrContent[] = array(
                "step" => $this->step,
                "state" => WORK,
                "msg" => "",
                "error" => "",
                "refresh" => true,
                "desc" => $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step4_help']
            );
        }
        else
        {
            try
            {
                $intStart = time();

                switch ($mixStepPool4["step"])
                {
                    case 1:
                        $arrZip = $this->objSyncCtoDatabase->runCreateZip(true);

                        $mixStepPool4["zip"] = $arrZip;
                        $mixStepPool4["step"] = 2;

                        $arrContent[$this->step - 1]["state"] = WORK;
                        $arrContent[$this->step - 1]["msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step3_msg2'];

                        $this->step--;

                        break;

                    case 2:
                        $this->objSyncCtoDatabase->runDumpSQL($mixSyncTable, $mixStepPool4["zip"]['name'], TRUE);

                        if ($intStart < (time() - 30))
                        {
                            $mixStepPool4["step"] = 3;

                            $arrContent[$this->step - 1]["state"] = WORK;
                            $arrContent[$this->step - 1]["msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step3_msg3'];

                            $this->step--;

                            break;
                        }

                    case 3:
                        $this->objSyncCtoDatabase->runDumpInsert($mixSyncTable, $mixStepPool4["zip"]['name'], TRUE);

                        if ($intStart < (time() - 30))
                        {
                            $mixStepPool4["step"] = 4;

                            $arrContent[$this->step - 1]["state"] = WORK;
                            $arrContent[$this->step - 1]["msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step3_msg3'];

                            $this->step--;

                            break;
                        }

                    case 4:
                        $this->objSyncCtoDatabase->runCheckZip($mixStepPool4["zip"]['name'], false, true);

                        $mixStepPool4["step"] = 5;

                        $arrContent[$this->step - 1]["state"] = WORK;
                        $arrContent[$this->step - 1]["msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step3_msg5'];

                        $this->step--;

                        break;

                    case 5:
                        $this->objSyncCtoCommunicationClient->sendFile($GLOBALS['SYC_PATH']['tmp'], $mixStepPool4["zip"]['name'], "", SyncCtoEnum::UPLOAD_SQL_TEMP);

                        if ($intStart < (time() - 30))
                        {
                            $mixStepPool4["step"] = 6;

                            $arrContent[$this->step - 1]["state"] = WORK;
                            $arrContent[$this->step - 1]["msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step3_msg5'];

                            $this->step--;

                            break;
                        }

                    case 6:
                        $this->objSyncCtoCommunicationClient->startSQLImport($mixStepPool4["zip"]['name']);

                        $arrContent[$this->step - 1]["state"] = OK;
                        $arrContent[$this->step - 1]["msg"] = vsprintf($GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step3_msg6'], array($mixStepPool4["zip"]['name']));
                        $arrContent[] = array(
                            "step" => $this->step,
                            "state" => WORK,
                            "msg" => "",
                            "error" => "",
                            "refresh" => true,
                            "desc" => $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step4_help']
                        );

                        $mixSyncTable = FALSE;
                        $mixStepPool4 = FALSE;
                }
            }
            catch (Exception $exc)
            {
                $this->log(vsprintf("Error on synchronization client ID %s", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "ERROR");

                $arrContent[$this->step - 1]["state"] = ERROR;
                $arrContent[$this->step - 1]["error"] = $exc->getMessage();
                $arrContent[$this->step - 1]["refresh"] = FALSE;
            }
        }

        $this->set("syncCto_StepPool4", $mixStepPool4);

        $this->parseSyncTo($this->saveContent($arrContent));
    }

    /**
     * File send part have fun, much todo here so let`s play a round :P
     */
    private function pageSyncToShowStep6()
    {
        /* ---------------------------------------------------------------------
         * INIT
         */

        // Time out 
        @set_time_limit(60);

        // State save for this step
        $mixStepPool6 = $this->get("syncCto_StepPool6");
        if ($mixStepPool6 == FALSE)
            $mixStepPool6 = array("step" => 1);

        // Load sync filelist
        $mixSyncFiles = $this->get("syncCto_SyncFiles");

        // Needed files/information        
        $intSyncTyp = $this->get("syncCto_Typ");

        // Load content
        $arrContent = $this->loadContent();

        // Count files
        if (is_array($mixSyncFiles) && $mixSyncFiles != FALSE)
        {
            $intSkippCount = 0;
            $intSendCount = 0;
            $intWaitCount = 0;

            foreach ($mixSyncFiles as $value)
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
            }
        }

        /* ---------------------------------------------------------------------
         * RUN
         */
        // Check if there is any file for upload
        if ($mixSyncFiles == false && $mixStepPool6["step"] == 1)
        {
            $mixStepPool6["step"] = 4;
        }

        try
        {
            $intStart = time();

            switch ($mixStepPool6["step"])
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
                        $arrContent[$this->step - 1]["state"] = ERROR;
                        $arrContent[$this->step - 1]["error"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step5_msg2'];
                        $arrContent[$this->step - 1]["refresh"] = FALSE;

                        $this->parseSyncTo($this->saveContent($arrContent));
                        return;
                    }

                    $mixStepPool6["step"] = 2;
                    $mixStepPool6["files_send"] = 0;
                    $this->Session->set("syncCto_StepPool6", serialize($mixStepPool6));

                    $this->step--;

                    break;

                /** ------------------------------------------------------------
                 * Send files
                 */
                case 2:
                    // Send allfiles exclude the big things
                    $intCountTransfer = 1;

                    foreach ($mixSyncFiles as $key => $value)
                    {
                        if ($value["transmission"] == SyncCtoEnum::FILETRANS_SEND
                                || $value["transmission"] == SyncCtoEnum::FILETRANS_SKIPPED)
                        {
                            continue;
                        }

                        if ($value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG
                                || $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_DELETE
                                || $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_NEED
                                || $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_MISSING
                                || $value["state"] == SyncCtoEnum::FILESTATE_BOMBASTIC_BIG)
                        {
                            $mixSyncFiles[$key]["skipreason"] = $GLOBALS['TL_LANG']['syncCto']['maximum_filesize'];
                            $mixSyncFiles[$key]["transmission"] = SyncCtoCommunication::FILETRANS_SKIPPED;

                            continue;
                        }

                        if ($value["state"] == SyncCtoEnum::FILESTATE_DELETE)
                            continue;

                        try
                        {
                            $arrPathInfo = pathinfo(TL_ROOT . "/" . $value['path']);
                            $this->objSyncCtoCommunicationClient->sendFile(str_replace(TL_ROOT, "", $arrPathInfo["dirname"]), $arrPathInfo["basename"], $value["checksum"], SyncCtoEnum::UPLOAD_SYNC_TEMP);
                            $mixSyncFiles[$key]["transmission"] = SyncCtoEnum::FILETRANS_SEND;

                            if ($intCountTransfer == 100)
                            {
                                $intCountTransfer = 0;
                                sleep(1);
                            }
                        }
                        catch (Exception $exc)
                        {
                            $mixSyncFiles[$key]["transmission"] = SyncCtoEnum::FILETRANS_SKIPPED;
                            $mixSyncFiles[$key]["skipreason"] = $exc->getMessage();
                        }

                        $intCountTransfer++;

                        if ($intStart < (time() - 20))
                        {
                            break;
                        }
                    }

                    if ($intStart < (time() - 20))
                    {
                        $mixStepPool6["step"] = 2;
                    }
                    else
                    {
                        $mixStepPool6["step"] = 3;
                    }

                    $arrContent[$this->step - 1]["state"] = WORK;
                    $arrContent[$this->step - 1]["msg"] = vsprintf($GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step5_msg3'], array($intSendCount, count($mixSyncFiles)));

                    $this->step--;

                    break;

                /** ------------------------------------------------------------
                 * Import Files
                 */
                case 3:
                    if (count($mixSyncFiles) != 0)
                    {
                        $arrImport = array();

                        foreach ($mixSyncFiles as $key => $value)
                        {
                            if ($value["transmission"] == SyncCtoEnum::FILETRANS_SEND)
                            {
                                $arrImport[$key] = $mixSyncFiles[$key];
                            }
                        }

                        if (count($arrImport) > 0)
                        {
                            $arrTransmission = $this->objSyncCtoCommunicationClient->startFileImport($arrImport);
                            $arrTransmission = deserialize($arrTransmission);

                            foreach ($arrTransmission as $key => $value)
                            {
                                $mixSyncFiles[$key] = $arrTransmission[$key];
                            }
                        }
                    }

                    $mixStepPool6["step"] = 4;

                    $arrContent[$this->step - 1]["state"] = WORK;
                    $arrContent[$this->step - 1]["msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step5_msg5'];

                    $this->step--;

                    break;

                /** ------------------------------------------------------------
                 * Delete files
                 */
                case 4:
                    if (count($mixSyncFiles) != 0 && is_array($mixSyncFiles))
                    {
                        $arrDelete = array();

                        foreach ($mixSyncFiles as $key => $value)
                        {
                            if ($value["state"] == SyncCtoEnum::FILESTATE_DELETE)
                            {
                                $arrDelete[$key] = $mixSyncFiles[$key];
                            }
                        }

                        if (count($arrDelete) > 0)
                        {
                            $arrDelete = $this->objSyncCtoCommunicationClient->deleteFiles($arrDelete);

                            foreach ($arrDelete as $key => $value)
                            {
                                $mixSyncFiles[$key] = $value;
                            }
                        }
                    }

                    $mixStepPool6["step"] = 5;

                    $arrContent[$this->step - 1]["state"] = WORK;
                    $arrContent[$this->step - 1]["msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step5_msg5'];

                    $this->step--;

                    break;

                /** ------------------------------------------------------------
                 * Import Config
                 */
                case 5:
                    if ($intSyncTyp == SYNCCTO_FULL)
                    {
                        $this->objSyncCtoCommunicationClient->startLocalConfigImport();

                        $mixStepPool6["step"] = 6;

                        $arrContent[$this->step - 1]["state"] = WORK;
                        $arrContent[$this->step - 1]["msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step5_msg5'];

                        $this->step--;

                        break;
                    }

                    $mixStepPool6["step"] = 6;

                /** ------------------------------------------------------------
                 * Cleanup
                 */
                case 6:
                    $this->objSyncCtoCommunicationClient->clearClientTempFolder();
                    $this->objSyncCtoFiles->purgeTemp();
                    $this->objSyncCtoCommunicationClient->refererEnable();

                    $this->log(vsprintf("Successfully finishing of synchronization client ID %s.", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "INFO");

                /** ------------------------------------------------------------
                 * Show information
                 */
                case 7:
                    if ($intSyncTyp == SYNCCTO_SMALL && ( count($mixSyncFiles) == 0 || $mixSyncFiles == FALSE ))
                    {
                        $mixStepPool6["step"] = 7;

                        $arrContent[$this->step - 1]["state"] = SKIPPED;
                        $arrContent[$this->step - 1]["refresh"] = false;
                        $arrContent[$this->step - 1]["finished"] = TRUE;
                        $arrContent[$this->step - 1]["msg"] = "";

                        break;
                    }
                    else if (count($mixSyncFiles) == 0 || $mixSyncFiles == FALSE)
                    {
                        $mixStepPool6["step"] = 7;

                        $arrContent[$this->step - 1]["state"] = OK;
                        $arrContent[$this->step - 1]["refresh"] = false;
                        $arrContent[$this->step - 1]["finished"] = TRUE;
                        $arrContent[$this->step - 1]["msg"] = "";

                        break;
                    }

                    $mixStepPool6["step"] = 7;

                    $arrContent[$this->step - 1]["state"] = OK;
                    $arrContent[$this->step - 1]["refresh"] = false;
                    $arrContent[$this->step - 1]["finished"] = TRUE;
                    $arrContent[$this->step - 1]["msg"] = vsprintf($GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step5_msg3'], array($intSendCount, count($mixSyncFiles)));

                    if ($intSkippCount != 0)
                    {
                        $compare .= '<br /><p class="tl_help">' . $intSkippCount . $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step5_msg4'] . '</p>';

                        $arrSort = array();

                        foreach ($mixSyncFiles as $key => $value)
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
                        if (count($mixSyncFiles) != 0 && is_array($mixSyncFiles))
                        {
                            // Send Part

                            $compare .= '<br /><p class="tl_help">' . $intSendCount . $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step5_msg6'] . '</p>';

                            $arrSort = array();

                            $compare .= '<ul class="fileinfo">';

                            $compare .= "<li>";
                            $compare .= '<strong>' . $GLOBALS['TL_LANG']['syncCto']['uploaded_files_list'] . '</strong>';
                            $compare .= "<ul>";

                            foreach ($mixSyncFiles as $key => $value)
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

                            foreach ($mixSyncFiles as $key => $value)
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

                            $compare .= '<br /><p class="tl_help">' . $intWaitCount . $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step5_msg6'] . '</p>';

                            $arrSort = array();

                            $compare .= '<ul class="fileinfo">';

                            $compare .= "<li>";
                            $compare .= '<strong>' . $GLOBALS['TL_LANG']['syncCto']['uploaded_files'] . '</strong>';
                            $compare .= "<ul>";

                            foreach ($mixSyncFiles as $key => $value)
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

                    $arrContent[$this->step - 1]["compare"] = $compare;

                    $this->step--;
                    break;
            }
        }
        catch (Exception $exc)
        {
            $this->log(vsprintf("Error on synchronization client ID %s", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "ERROR");

            $arrContent[$this->step - 1]["state"] = ERROR;
            $arrContent[$this->step - 1]["error"] = $exc->getMessage();
            $arrContent[$this->step - 1]["refresh"] = FALSE;
        }

        $this->set("syncCto_StepPool6", $mixStepPool6);
        $this->set("syncCto_SyncFiles", $mixSyncFiles);

        $this->parseSyncTo($this->saveContent($arrContent));
    }

    /*
     * End SyncCto Sync. To
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
        return 0;

    return ($a["state"] < $b["state"]) ? -1 : 1;
}

?>