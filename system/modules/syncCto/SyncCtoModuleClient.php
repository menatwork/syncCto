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
 * Defines
 */
define("OK", 1);
define("ERROR", 2);
define("WORK", 3);
define("SKIPPED", 4);

class SyncCtoModuleClient extends BackendModule
{

    // Variablen
    protected $strTemplate = 'be_syncCto_empty';
    protected $objTemplateContent;
    protected $step;
    // Helper Class
    protected $objSyncCtoDatabase;
    protected $objSyncCtoFiles;
    protected $objSyncCtoCommunicationClient;
    protected $objSyncCtoCallback;
    protected $objSyncCtoHelper;
    protected $objSyncCtoMeasurement;
    // Defines step state
    public static $WORK_OK = 1;
    public static $WORK_ERROR = 2;
    public static $WORK_WORK = 3;
    public static $WORK_SKIPPED = 4;

    function __construct(DataContainer $objDc = null)
    {
        $this->import('BackendUser', 'User');
        parent::__construct($objDc);


        $this->objSyncCtoDatabase = SyncCtoDatabase::getInstance();
        $this->objSyncCtoFiles = SyncCtoFiles::getInstance();
        $this->objSyncCtoCallback = SyncCtoCallback::getInstance();
        $this->objSyncCtoCommunicationClient = SyncCtoCommunicationClient::getInstance();
        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();
        $this->objSyncCtoMeasurement = SyncCtoMeasurement::getInstance();

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
        if ($this->Input->get("do") == "synccto_clients"
                && strlen($this->Input->get("act")) != 0
                && strlen($this->Input->get("table")) != 0)
        {
            // Which table is in use
            switch ($this->Input->get("table"))
            {
                case "tl_syncCto_clients_syncTo":
                    $this->pageSyncTo();
                    break;

                case "tl_syncCto_clients_syncFrom":
                    $this->pageSyncFrom();
                    break;

                default:
                    $this->parseStartPage("Unbekannte Tabelle");
                    break;
            }
        }
        else
        {
            $this->parseStartPage();
        }

        $this->parseTemplate();
    }

    /**
     * Show main page of syncCto backup.
     * 
     * @param string $message - Error msg.
     */
    protected function parseStartPage($message = null)
    {
        $this->objTemplateContent = new BackendTemplate('be_syncCto_backup');
        $this->objTemplateContent->message = $message;
    }

    /**
     * Generate the pages
     */
    protected function parseTemplate()
    {
        $this->objTemplateContent->script = $this->Environment->script;
        $this->Template->content = $this->objTemplateContent->parse();
        $this->Template->script = $this->Environment->script;
    }

    private function parseSyncTo($arrContent)
    {
        $this->objTemplateContent->step = $this->step;
        $this->objTemplateContent->id = (int) $this->Input->get("id");
        $this->objTemplateContent->content = $arrContent;
    }

    private function parseSyncFrom($arrContent)
    {
        $this->objTemplateContent->step = $this->step;
        $this->objTemplateContent->id = (int) $this->Input->get("id");
        $this->objTemplateContent->content = $arrContent;
    }

    /* -------------------------------------------------------------------------
     * Functions for comunication
     */

    private function pageSyncTo()
    {
        // Build Step
        if ($this->Input->get("step") == "" || $this->Input->get("step") == null)
        {
            $this->step = 1;
        }
        else
        {
            $this->step = intval($this->Input->get("step"));
        }

        $this->objSyncCtoMeasurement->startMeasurement(__CLASS__, __FUNCTION__, "Step: " . $this->step);

        // Load language and template
        $this->loadLanguageFile('tl_syncCto_clients_syncTo');
        $this->loadLanguageFile('syncCto');
        $this->objTemplateContent = new BackendTemplate('be_syncCto_clients_syncTo');

        // Set client for communication
        try
        {
            $this->objSyncCtoCommunicationClient->setClient(intval($this->Input->get("id")));
            
        }
        catch (Exception $exc)
        {
            $this->parseStartPage($exc->getMessage());
            return;
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
                $this->parseStartPage("Unbekannter Schritt für Backup.");
                $this->objSyncCtoMeasurement->stopMeasurement(__CLASS__, __FUNCTION__);
                return;
                break;
        }

        $this->objSyncCtoMeasurement->stopMeasurement(__CLASS__, __FUNCTION__);
    }

    private function pageSyncFrom()
    {
        // Build Step
        if ($this->Input->get("step") == "" || $this->Input->get("step") == null)
        {
            $this->step = 1;
        }
        else
        {
            $this->step = intval($this->Input->get("step"));
        }

        // Load language and template
        $this->loadLanguageFile('tl_syncCto_clients_syncFrom');
        $this->objTemplateContent = new BackendTemplate('be_syncCto_clients_syncFrom');

        // Set client for communication
        try
        {
            $this->objSyncCtoCommunicationClient->setClient(intval($this->Input->get("id")));
        }
        catch (Exception $exc)
        {
            $this->parseStartPage($exc->getMessage());
            return;
        }

        // Do step x
        switch ($this->step)
        {
            case 1:
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

            case 6:
                $this->pageSyncFromShowStep6();
                break;

            default:
                $this->parseStartPage("Unbekannter Schritt für Backup.");
                return;
                break;
        }
    }

    /* -------------------------------------------------------------------------
     * Start SyncCto Sync. From
     */

    private function pageSyncFromShowStep1()
    {
        $this->log(vsprintf("Start synchronization server from client ID %s.", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "INFO");

        @set_time_limit(60);

        /* ---------------------------------------------------------------------
         * POST data validate and session save
         */

        // Check sync. typ
        if (strlen($this->Input->post('sync_type')) != 0)
        {
            if ($this->Input->post('sync_type') == SYNCCTO_FULL || $this->Input->post('sync_type') == SYNCCTO_SMALL)
            {
                $this->Session->set("syncCto_Typ", $this->Input->post('sync_type'));
            }
            else
            {
                $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['unknown_method']);
                return;
            }
        }
        else
        {
            $this->Session->set("syncCto_Typ", SYNCCTO_SMALL);
        }

        // Load table lists and merge them
        if ($this->Input->post("table_list_recommend") != "" || $this->Input->post("table_list_none_recommend") != "")
        {
            if ($this->Input->post("table_list_recommend") != "" && $this->Input->post("table_list_none_recommend") != "")
                $arrSyncTables = array_merge($this->Input->post("table_list_recommend"), $this->Input->post("table_list_none_recommend"));
            else if ($this->Input->post("table_list_recommend"))
                $arrSyncTables = $this->Input->post("table_list_recommend");
            else if ($this->Input->post("table_list_none_recommend"))
                $arrSyncTables = $this->Input->post("table_list_none_recommend");

            $this->Session->set("syncCto_SyncTables", serialize($arrSyncTables));
        }
        else
        {
            $this->Session->set("syncCto_SyncTables", FALSE);
        }

        // Files for backup tl_files       
        if (is_array($this->Input->post('filelist')) && count($this->Input->post('filelist')) != 0)
        {
            $this->Session->set("syncCto_Filelist", serialize($this->Input->post('filelist')));
        }
        else
        {
            $this->Session->set("syncCto_Filelist", FALSE);
        }

        $this->Session->set("syncCto_Start", microtime(true));

        /* ---------------------------------------------------------------------
         * None form data
         */

        $this->Session->set("syncCto_FileCount", 0);
        $this->Session->set("syncCto_FileSkipped", FALSE);
        $this->Session->set("syncCto_SplitFiles", FALSE);
        $this->Session->set("syncCto_SyncFiles", FALSE);

        /* ---------------------------------------------------------------------
         * Step Session 
         */

        // Step 2
        $this->Session->set("syncCto_StepPool2", FALSE);
        // Step 3
        $this->Session->set("syncCto_StepPool3", FALSE);
        // Step 4
        $this->Session->set("syncCto_StepPool4", FALSE);
        // Step 5
        $this->Session->set("syncCto_StepPool5", FALSE);
        // Step 6
        $this->Session->set("syncCto_StepPool6", FALSE);

        /* ---------------------------------------------------------------------
         * Build page
         */

        $this->parseSyncFrom($this->saveContent(array(), array(
                    1 => array(
                        "step" => $this->step,
                        "state" => WORK,
                        "msg" => "",
                        "error" => "",
                        "refresh" => TRUE,
                        "desc" => $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step1_help']
                    ),
                ))
        );
    }

    /**
     * Check client communication
     */
    private function pageSyncFromShowStep2()
    {
        set_time_limit(60);

        // State save for this step
        $mixStepPool2 = $this->Session->get("syncCto_StepPool2");

        if ($mixStepPool2 == FALSE)
            $mixStepPool2 = array("step" => 1);
        else
            $mixStepPool2 = deserialize($mixStepPool2);

        $arrContent = $this->loadContent();

        try
        {
            switch ($mixStepPool2["step"])
            {
                case 1:
                    $mixStepPool2["step"] = 2;
                    $this->Session->set("syncCto_StepPool2", serialize($mixStepPool2));

                    $arrContent[$this->step - 1]["state"] = WORK;
                    $arrContent[$this->step - 1]["msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step1_msg1'];

                    $this->step--;

                    break;

                case 2:
                    // Referer check deactivate
                    $this->objSyncCtoCommunicationClient->refererDisable();

                    $mixStepPool2["step"] = 3;
                    $this->Session->set("syncCto_StepPool2", serialize($mixStepPool2));

                    $arrContent[$this->step - 1]["state"] = WORK;
                    $arrContent[$this->step - 1]["msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step1_msg1'];

                    $this->step--;

                    break;

                case 3:
                    // Check magical number
                    $intMagicNumber = $this->objSyncCtoCommunicationClient->makeAGame();

                    $mixStepPool2["step"] = 4;
                    $this->Session->set("syncCto_StepPool2", serialize($mixStepPool2));

                    $arrContent[$this->step - 1]["state"] = WORK;
                    $arrContent[$this->step - 1]["msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step1_msg1'];

                    $this->step--;

                    break;

                case 4:
                    // Check version
                    $strVersion = $this->objSyncCtoCommunicationClient->getClientVersion();
                    if ($strVersion != SYNCCTO_GET_VERSION)
                    {
                        $this->log(vsprintf("Not the same version on synchronization client ID %s. Serverversion: %s. Clientversion: %s", array($this->Input->get("id"), SYNCCTO_GET_VERSION, $strVersion)), __CLASS__ . " " . __FUNCTION__, "INFO");

                        $arrContent[$this->step - 1]["state"] = ERROR;
                        $arrContent[$this->step - 1]["error"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step1_msg2'];
                        $arrContent[$this->step - 1]["refresh"] = false;

                        $this->parseSyncTo($this->saveContent($arrContent));
                        return;
                    }

                    $mixStepPool2["step"] = 5;
                    $this->Session->set("syncCto_StepPool2", serialize($mixStepPool2));

                    $arrContent[$this->step - 1]["state"] = WORK;
                    $arrContent[$this->step - 1]["msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step1_msg1'];

                    $this->step--;

                    break;

                case 5:
                    // Cleare client temp folder   
                    $this->objSyncCtoCommunicationClient->clearClientTempFolder();

                    $mixStepPool2["step"] = 6;
                    $this->Session->set("syncCto_StepPool2", serialize($mixStepPool2));

                    $arrContent[$this->step - 1]["state"] = WORK;
                    $arrContent[$this->step - 1]["msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step1_msg1'];

                    $this->step--;

                    break;

                case 6:
                    // Clear Temp Folder
                    $this->objSyncCtoFiles->purgeTemp();

                    $arrContent[$this->step - 1]["state"] = OK;
                    $arrContent[$this->step - 1]["msg"] = "";
                    $arrContent[] = array(
                        "step" => $this->step,
                        "state" => WORK,
                        "msg" => "",
                        "error" => "",
                        "refresh" => true,
                        "desc" => $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step2_help']
                    );

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

        $this->parseSyncTo($this->saveContent($arrContent));
    }

    /**
     * Build checksumlist and ask client
     */
    private function pageSyncFromShowStep3()
    {
        /* ---------------------------------------------------------------------
         * Init
         */

        // Time out 
        @set_time_limit(60);

        // State save for this step
        $mixStepPool3 = $this->Session->get("syncCto_StepPool3");

        if ($mixStepPool3 == FALSE)
            $mixStepPool3 = array("step" => 1);
        else
            $mixStepPool3 = deserialize($mixStepPool3);

        // Needed files/information
        $mixFilelist = $this->Session->get("syncCto_Filelist");
        $intSyncTyp = $this->Session->get("syncCto_Typ");

        // Content data for page
        $arrContent = $this->loadContent();

        /* ---------------------------------------------------------------------
         * Run page
         */

        try
        {
            switch ($mixStepPool3["step"])
            {
                case 1:
                    if ($intSyncTyp == SYNCCTO_FULL)
                    {
                        $mixStepPool3["core_checksum"] = $this->objSyncCtoCommunicationClient->getChecksumCore();
                        $mixStepPool3["step"] = 2;
                        $this->Session->set("syncCto_StepPool3", serialize($mixStepPool3));

                        $arrContent[$this->step - 1]["state"] = WORK;
                        $arrContent[$this->step - 1]["msg"] = "Have Corefile List. Next TL Files.";

                        $this->step--;

                        break;
                    }
                    else
                    {
                        $mixStepPool3["core_checksum"] = array();
                        $mixStepPool3["step"] = 2;
                    }

                case 2:
                    $mixStepPool3["tlfiles_checksum"] = $this->objSyncCtoCommunicationClient->getChecksumTlfiles();

                    $mixStepPool3["step"] = 3;
                    $this->Session->set("syncCto_StepPool3", serialize($mixStepPool3));

                    $arrContent[$this->step - 1]["state"] = WORK;
                    $arrContent[$this->step - 1]["msg"] = "Have TL FileList. Next check it.";

                    $this->step--;

                    break;

                case 3:
                    $arrChecksum = array_merge($mixStepPool3["tlfiles_checksum"], $mixStepPool3["core_checksum"]);

                    unset($mixStepPool3["tlfiles_checksum"]);
                    unset($mixStepPool3["core_checksum"]);
                    $mixStepPool3["checksum"] = $arrChecksum;

                    $mixStepPool3["sync_list"] = $this->objSyncCtoFiles->runCecksum($arrChecksum);
                    $mixStepPool3["step"] = 4;
                    $this->Session->set("syncCto_StepPool3", serialize($mixStepPool3));

                    $arrContent[$this->step - 1]["state"] = WORK;
                    $arrContent[$this->step - 1]["msg"] = "Build list. Pleas wait analyze it.";

                    $this->step--;

                    break;

                case 4:
                    // Save list
                    $arrSyncFileList = $mixStepPool3["sync_list"];
                    $arrChecksum = $mixStepPool3["checksum"];

                    // Counter
                    $intCountMissing = 0;
                    $intCountNeed = 0;
                    $intTotalSize = 0;

                    // Count files
                    foreach ($arrSyncFileList as $key => $value)
                    {
                        if ($value['state'] == FILE_MISSING)
                            $intCountMissing++;

                        if ($value['state'] == FILE_NEED)
                            $intCountNeed++;

                        $intTotalSize += $arrChecksum[$key]["size"];

                        $arrSyncFileList[$key]["checksum"] = $arrChecksum[$key]["checksum"];
                        $arrSyncFileList[$key]["size"] = $arrChecksum[$key]["size"];
                    }

                    $mixStepPool3["missing"] = $intCountMissing;
                    $mixStepPool3["need"] = $intCountNeed;

                    // Save files
                    if ($intCountMissing == 0 && $intCountNeed == 0)
                    {
                        $this->Session->set("syncCto_SyncFiles", false);

                        $arrContent[$this->step - 1]["state"] = OK;
                        $arrContent[$this->step - 1]["msg"] = vsprintf("We have %s missing and %s needing files.", array($mixStepPool3["missing"], $mixStepPool3["need"]));
                        $arrContent[] = array(
                            "step" => $this->step,
                            "state" => WORK,
                            "msg" => "",
                            "error" => "",
                            "refresh" => true,
                            "desc" => "Build and send SQL and import it."
                        );

                        $this->Session->set("syncCto_StepPool3", FALSE);

                        break;
                    }
                    else
                    {
                        $this->Session->set("syncCto_SyncFiles", serialize($arrSyncFileList));
                    }

                    $objTemp = new BackendTemplate("be_syncCto_clients_filelist");
                    $objTemp->filelist = $arrSyncFileList;
                    $objTemp->id = $this->Input->get("id");
                    $objTemp->step = $this->step;
                    $objTemp->totalsize = $intTotalSize;
                    $objTemp->direction = "From";

                    // Build content
                    $arrContent[$this->step - 1]["state"] = WORK;
                    $arrContent[$this->step - 1]["msg"] = vsprintf("We have %s missing and %s needing files. %s ", array($intCountMissing, $intCountNeed, $objTemp->parse()));
                    $arrContent[$this->step - 1]["refresh"] = false;

                    $this->step--;

                    $mixStepPool3["step"] = 5;
                    $this->Session->set("syncCto_StepPool3", serialize($mixStepPool3));

                    break;

                case 5:

                    $arrContent[$this->step - 1]["state"] = OK;
                    $arrContent[$this->step - 1]["msg"] = vsprintf("We have %s missing and %s needing files.", array($mixStepPool3["missing"], $mixStepPool3["need"]));
                    $arrContent[] = array(
                        "step" => $this->step,
                        "state" => WORK,
                        "msg" => "",
                        "error" => "",
                        "refresh" => true,
                        "desc" => "Build and send SQL and import it."
                    );

                    $this->Session->set("syncCto_StepPool3", FALSE);
            }
        }
        catch (Exception $exc)
        {
            $arrContent[$this->step - 1]["state"] = ERROR;
            $arrContent[$this->step - 1]["error"] = $exc->getMessage();
            $arrContent[$this->step - 1]["refresh"] = FALSE;
        }


        $this->parseSyncTo($this->saveContent($arrContent));
    }

    /**
     * Build SQL Zip and Send it to client
     */
    private function pageSyncFromShowStep4()
    {
        /* ---------------------------------------------------------------------
         * Init
         */

        // Time out 
        @set_time_limit(60);

        // State save for this step
        $mixStepPool4 = $this->Session->get("syncCto_StepPool4");

        if ($mixStepPool4 == FALSE)
            $mixStepPool4 = array("step" => 1);
        else
            $mixStepPool4 = deserialize($mixStepPool4);

        $mixSyncTable = $this->Session->get("syncCto_SyncTables");

        $arrContent = $this->loadContent();

        // Check if there is a filelist
        if ($mixSyncTable == FALSE)
        {
            $arrContent[$this->step - 1]["state"] = SKIPPED;
            $arrContent[] = array(
                "step" => $this->step,
                "state" => WORK,
                "msg" => "",
                "error" => "",
                "refresh" => true,
                "desc" => "Send BIG Files."
            );
        }
        else
        {
            try
            {
                $mixSyncTable = deserialize($mixSyncTable);
                $intStart = time();

                switch ($mixStepPool4["step"])
                {
                    case 1:
                        $arrZip = $this->objSyncCtoCommunicationClient->runClientSQLZip();

                        $mixStepPool4["zip"] = $arrZip;
                        $mixStepPool4["step"] = 2;
                        $this->Session->set("syncCto_StepPool4", serialize($mixStepPool4));

                        $arrContent[$this->step - 1]["state"] = WORK;
                        $arrContent[$this->step - 1]["msg"] = "Create Zip. Save Database next from client.";

                        $this->step--;

                        break;

                    case 2:
                        if ($this->objSyncCtoCommunicationClient->runClientSQLScript($mixStepPool4["zip"]["name"], $mixSyncTable))
                        {
                            $mixStepPool4["step"] = 3;
                            $this->Session->set("syncCto_StepPool4", serialize($mixStepPool4));

                            $arrContent[$this->step - 1]["state"] = WORK;
                            $arrContent[$this->step - 1]["msg"] = "Save SQL Script. Next Sync Script.";
                        }
                        else
                        {
                            $mixStepPool4["step"] = 2;
                            $this->Session->set("syncCto_StepPool4", serialize($mixStepPool4));

                            $arrContent[$this->step - 1]["state"] = WORK;
                            $arrContent[$this->step - 1]["msg"] = "Save SQL Script.";
                            $arrContent[$this->step - 1]["error"] = "Error by saving. Try again.";
                            $arrContent[$this->step - 1]["refresh"] = false;
                        }

                        $this->step--;
                        break;

                    case 3:
                        if ($this->objSyncCtoCommunicationClient->runClientSQLSyncscript($mixStepPool4["zip"]["name"], $mixSyncTable))
                        {
                            $mixStepPool4["step"] = 4;
                            $this->Session->set("syncCto_StepPool4", serialize($mixStepPool4));

                            $arrContent[$this->step - 1]["state"] = WORK;
                            $arrContent[$this->step - 1]["msg"] = "Save Sync Script. Next check Zip.";
                        }
                        else
                        {
                            $mixStepPool4["step"] = 3;
                            $this->Session->set("syncCto_StepPool4", serialize($mixStepPool4));

                            $arrContent[$this->step - 1]["state"] = WORK;
                            $arrContent[$this->step - 1]["msg"] = "Save Sync Script.";
                            $arrContent[$this->step - 1]["error"] = "Error by saving. Try again.";
                            $arrContent[$this->step - 1]["refresh"] = false;
                        }

                        $this->step--;
                        break;

                    case 4:
                        if ($this->objSyncCtoCommunicationClient->runClientSQLCheck($mixStepPool4["zip"]['name']))
                        {
                            $mixStepPool4["step"] = 5;
                            $this->Session->set("syncCto_StepPool4", serialize($mixStepPool4));

                            $arrContent[$this->step - 1]["state"] = WORK;
                            $arrContent[$this->step - 1]["msg"] = "Zip seems to be okay. Next, get it.";
                        }
                        else
                        {
                            $mixStepPool4["step"] = 4;
                            $this->Session->set("syncCto_StepPool4", serialize($mixStepPool4));

                            $arrContent[$this->step - 1]["state"] = WORK;
                            $arrContent[$this->step - 1]["msg"] = "Zip Check.";
                            $arrContent[$this->step - 1]["error"] = "Error by checking the zip. Try again.";
                            $arrContent[$this->step - 1]["refresh"] = false;
                        }

                        $this->step--;
                        break;

                    case 5:
                        $this->objSyncCtoCommunicationClient->getFile($GLOBALS['syncCto']['path']['tmp'] . $mixStepPool4["zip"]['name'], $GLOBALS['syncCto']['path']['tmp'] . "sql/" . $mixStepPool4["zip"]['name']);

                        $mixStepPool4["step"] = 6;
                        $this->Session->set("syncCto_StepPool4", serialize($mixStepPool4));

                        $arrContent[$this->step - 1]["state"] = WORK;
                        $arrContent[$this->step - 1]["msg"] = "Have File. Next make Backup.";

                        $this->step--;

                        break;

                    case 6:
                        $arrZip = $this->objSyncCtoDatabase->runCreateZip();
                        sleep(1);
                        $this->objSyncCtoDatabase->runDumpSQL($this->Database->listTables(), $arrZip["name"]);
                        sleep(1);
                        $this->objSyncCtoDatabase->runDumpInsert($this->Database->listTables(), $arrZip["name"]);

                        $mixStepPool4["step"] = 7;
                        $this->Session->set("syncCto_StepPool4", serialize($mixStepPool4));

                        $arrContent[$this->step - 1]["state"] = WORK;
                        $arrContent[$this->step - 1]["msg"] = "Make Backup. Now start Import.";

                        $this->step--;

                        break;

                    case 7:
                        $this->objSyncCtoDatabase->runRestore($GLOBALS['syncCto']['path']['tmp'] . "sql/" . $mixStepPool4["zip"]['name']);

                        $arrContent[$this->step - 1]["state"] = OK;
                        $arrContent[$this->step - 1]["msg"] = vsprintf("Send file %s with SQL scripten to client.", array($mixStepPool4["zip"]['name']));
                        $arrContent[] = array(
                            "step" => $this->step,
                            "state" => WORK,
                            "msg" => "",
                            "error" => "",
                            "refresh" => true,
                            "desc" => "Send BIG Files."
                        );

                        $this->Session->set("syncCto_StepPool4", FALSE);
                }
            }
            catch (Exception $exc)
            {
                $arrContent[$this->step - 1]["state"] = ERROR;
                $arrContent[$this->step - 1]["error"] = $exc->getMessage();
                $arrContent[$this->step - 1]["refresh"] = FALSE;
            }
        }

        $this->parseSyncTo($this->saveContent($arrContent));
    }

    /**
     * Split Files
     */
    private function pageSyncFromShowStep5()
    {
        /* ---------------------------------------------------------------------
         * Init
         */

        // Time out 
        @set_time_limit(60);

        // State save for this step
        $mixStepPool5 = $this->Session->get("syncCto_StepPool5");

        if ($mixStepPool5 == FALSE)
            $mixStepPool5 = array("step" => 1);
        else
            $mixStepPool5 = deserialize($mixStepPool5);

        $arrSplitFiles = $this->Session->get("syncCto_SplitFiles");

        if ($arrSplitFiles == false)
            $arrSplitFiles = array();
        else
            $arrSplitFiles = deserialize($arrSplitFiles);

        $mixSyncFiles = $this->Session->get("syncCto_SyncFiles");

        $arrContent = $this->loadContent();

        // Check if there is any file for upload
        if ($mixSyncFiles == false)
        {
            $arrContent[$this->step - 1]["state"] = SKIPPED;
            $arrContent[] = array(
                "step" => $this->step,
                "state" => WORK,
                "msg" => "",
                "error" => "",
                "refresh" => true,
                "desc" => "Send normal files to server."
            );
        }
        else
        {
            try
            {
                // Load sync list
                $mixSyncFiles = deserialize($mixSyncFiles);

                // Timer 
                $intStar = time();

                switch ($mixStepPool5["step"])
                {
                    case 1:
                        // Load parameter from client
                        $arrClientParameter = $this->objSyncCtoCommunicationClient->getClientParameter();

                        // Check if there is a file whih is too big                        
                        $intClientMemoryLimit = intval(str_replace("M", "000000", $arrClientParameter['memory_limit']));
                        $intLocalMemoryLimit = intval(str_replace("M", "000000", ini_get('memory_limit')));

                        // Check if memory limit on server is enoug for upload  
                        $intLimit = min($intClientMemoryLimit, $intLocalMemoryLimit);

                        //ToDo Prozentzahlen neu setzen.
                        if ($intLimit > 1000000000)
                            $intPercent = 45;
                        else if ($intLimit > 500000000)
                            $intPercent = 45;
                        else if ($intLimit > 100000000)
                            $intPercent = 45;
                        else if ($intLimit > 50000000)
                            $intPercent = 45;
                        else
                            $intPercent = 45;

                        $intLimit = $intLimit / 100 * $intPercent;

                        $mixStepPool5["limit"] = $intLimit;
                        $mixStepPool5["percent"] = $intPercent;
                        $mixStepPool5["step"] = 2;
                        $this->Session->set("syncCto_StepPool5", serialize($mixStepPool5));

                        $arrContent[$this->step - 1]["state"] = WORK;
                        $arrContent[$this->step - 1]["msg"] = "Search files which are too big.";

                        $this->step--;

                        break;

                    case 2:
                        foreach ($mixSyncFiles as $key => $value)
                        {
                            if ($value["size"] > $mixStepPool5["limit"])
                                $arrSplitFiles[] = $key;
                        }

                        $arrSplitFiles = array_unique($arrSplitFiles);

                        // Skip page if no big file is found
                        if (count($arrSplitFiles) == 0)
                        {
                            $arrContent[$this->step - 1]["state"] = SKIPPED;
                            $arrContent[$this->step - 1]["msg"] = "No too big files found.";
                            $arrContent[] = array(
                                "step" => $this->step,
                                "state" => WORK,
                                "msg" => "",
                                "error" => "",
                                "refresh" => true,
                                "desc" => "Send normal files to server."
                            );

                            $this->Session->set("syncCto_StepPool5", FALSE);

                            break;
                        }
                        else
                        {
                            $arrTempList = array();
                            $intTotalsize = 0;

                            foreach ($arrSplitFiles as $value)
                            {
                                $arrTempList[$value] = $mixSyncFiles[$value];
                                $intTotalsize += $mixSyncFiles[$value]["size"];
                            }

                            $mixStepPool5["step"] = 3;
                            $mixStepPool5["splitfiles"] = $arrSplitFiles;
                            $mixStepPool5["splitfiles_count"] = 0;
                            $mixStepPool5["splitfiles_send"] = 0;
                            $this->Session->set("syncCto_StepPool5", serialize($mixStepPool5));
                            $this->Session->set("syncCto_SplitFiles", serialize($arrSplitFiles));

                            $objTemp = new BackendTemplate("be_syncCto_clients_filelist");
                            $objTemp->filelist = $arrTempList;
                            $objTemp->id = $this->Input->get("id");
                            $objTemp->step = $this->step;
                            $objTemp->direction = "From";
                            $objTemp->totalsize = $intTotalsize;

                            $arrContent[$this->step - 1]["state"] = WORK;
                            $arrContent[$this->step - 1]["msg"] = vsprintf("Next split %s files. %s", array(count($arrSplitFiles)));
                            $arrContent[$this->step - 1]["refresh"] = FALSE;
                            $arrContent[$this->step - 1]["compare"] = $objTemp->parse();

                            $this->step--;

                            break;
                        }
                        break;

                    case 3:
                        $i = 0;

                        // Split files
                        foreach ($mixStepPool5["splitfiles"] as $value)
                        {
                            if ($mixStepPool5["splitfiles_count"] == count($mixStepPool5["splitfiles"]))
                                break;

                            if ($i < $mixStepPool5["splitfiles_count"])
                                continue;

                            $intSplits = $this->objSyncCtoCommunicationClient->runClientFileSplit($mixSyncFiles[$value]["path"], $GLOBALS['syncCto']['path']['tmp'] . $value, $value, ($mixStepPool5["limit"] / 100 * $mixStepPool5["percent"]));

                            $mixSyncFiles[$value]["split"] = true;
                            $mixSyncFiles[$value]["splitcount"] = $intSplits;
                            $mixSyncFiles[$value]["splitname"] = $value;

                            $i++;

                            $mixStepPool5["step"] = 3;
                            $mixStepPool5["splitfiles_count"] = $i;
                            $this->Session->set("syncCto_StepPool5", serialize($mixStepPool5));

                            $arrContent[$this->step - 1]["state"] = WORK;
                            $arrContent[$this->step - 1]["msg"] = "We have split " . $i . " from " . count($arrSplitFiles) . " files.";
                            $arrContent[$this->step - 1]["refresh"] = true;

                            $this->Session->set("syncCto_SyncFiles", serialize($mixSyncFiles));
                            $this->Session->set("syncCto_SplitFiles", serialize($arrSplitFiles));

                            $this->step--;

                            $this->parseSyncTo($this->saveContent($arrContent));
                            return;
                        }

                        $mixStepPool5["step"] = 4;
                        $mixStepPool5["splitfiles_count"] = $i;
                        $this->Session->set("syncCto_StepPool5", serialize($mixStepPool5));
                        $this->Session->set("syncCto_SyncFiles", serialize($mixSyncFiles));
                        $this->Session->set("syncCto_SplitFiles", serialize($arrSplitFiles));

                        $arrContent[$this->step - 1]["state"] = WORK;
                        $arrContent[$this->step - 1]["msg"] = "We have split " . $mixStepPool5["splitfiles_count"] . " from " . count($arrSplitFiles) . " files. Next get them.";
                        $arrContent[$this->step - 1]["refresh"] = true;

                        $this->step--;

                        break;

                    case 4:
                        $i = 0;

                        // Send bigfiles 
                        foreach ($mixStepPool5["splitfiles"] as $value)
                        {
                            if ($mixStepPool5["splitfiles_send"] == count($mixStepPool5["splitfiles"]))
                                break;

                            if ($i < $mixStepPool5["splitfiles_send"])
                                continue;

                            for ($ii = 0; $ii < $mixSyncFiles[$value]["splitcount"]; $ii++)
                            {
                                $strPath = $GLOBALS['syncCto']['path']['tmp'] . $mixSyncFiles[$value]["splitname"] . "/" . $mixSyncFiles[$value]["splitname"] . ".sync" . $i;
                                //$this->objSyncCtoCommunication->getFile($strPath, $strPath);
                            }

                            //$this->objSyncCtoCommunication->buildSingleFile($mixSyncFiles[$value]["splitname"], $mixSyncFiles[$value]["splitcount"], $mixSyncFiles[$value]["path"], $mixSyncFiles[$value]["checksum"]);

                            $i++;
                            $mixStepPool5["splitfiles_send"] = $i;

                            if ($intStar < time() - 15)
                            {
                                $mixStepPool5["step"] = 4;
                                $this->Session->set("syncCto_StepPool5", serialize($mixStepPool5));

                                $arrContent[$this->step - 1]["state"] = WORK;
                                $arrContent[$this->step - 1]["msg"] = "Send " . $i . " from " . count($arrSplitFiles) . " files.";

                                $this->Session->set("syncCto_SyncFiles", serialize($mixSyncFiles));
                                $this->Session->set("syncCto_SplitFiles", serialize($arrSplitFiles));

                                $this->step--;

                                $this->parseSyncTo($this->saveContent($arrContent));
                                return;
                            }
                        }

                        $arrContent[$this->step - 1]["state"] = OK;
                        $arrContent[$this->step - 1]["msg"] = "We have finished send file " . $mixStepPool5["splitfiles_send"] . " of " . count($arrSplitFiles) . " of big files.";
                        $arrContent[] = array(
                            "step" => $this->step,
                            "state" => WORK,
                            "msg" => "",
                            "error" => "",
                            "refresh" => true,
                            "desc" => "Send normal files to server."
                        );

                        $this->Session->set("syncCto_StepPool5", FALSE);
                }
            }
            catch (Exception $exc)
            {
                $arrContent[$this->step - 1]["state"] = ERROR;
                $arrContent[$this->step - 1]["error"] = $exc->getMessage();
                $arrContent[$this->step - 1]["refresh"] = FALSE;
            }
        }

        $this->parseSyncTo($this->saveContent($arrContent));
    }

    /**
     * File send part have fun, much todo here so let`s play a round :P
     */
    private function pageSyncFromShowStep6()
    {
        /* ---------------------------------------------------------------------
         * Init
         */

        // Time out 
        @set_time_limit(60);

        // State save for this step
        $mixStepPool6 = $this->Session->get("syncCto_StepPool6");

        if ($mixStepPool6 == FALSE)
            $mixStepPool6 = array("step" => 1);
        else
            $mixStepPool6 = deserialize($mixStepPool6);

        $arrSplitFiles = $this->Session->get("syncCto_SplitFiles");

        if ($arrSplitFiles == false)
            $arrSplitFiles = array();
        else
            $arrSplitFiles = deserialize($arrSplitFiles);

        $mixSyncFiles = $this->Session->get("syncCto_SyncFiles");

        $arrContent = $this->loadContent();

        /* ---------------------------------------------------------------------
         * RUN
         */
        // Check if there is any file for upload
        if ($mixSyncFiles == false)
        {
            $arrContent[$this->step - 1]["state"] = SKIPPED;
            $arrContent[$this->step - 1]["refresh"] = false;

            $this->parseSyncTo($this->saveContent($arrContent));
            return;
        }

        $mixSyncFiles = deserialize($mixSyncFiles);

        if (count($mixSyncFiles) == count($arrSplitFiles))
        {
//            try
//            {
//                $arrResponse = $this->objSyncCtoCommunication->startFileImport($mixSyncFiles);
//                $this->objSyncCtoCommunication->startLocalConfigImport();
//                $this->objSyncCtoCommunication->clearClientTempFolder();
//                $this->objSyncCtoCommunication->refererEnable();
//
//                $this->objSyncCtoHelper->pergeTemp();
//
//                $arrContent[$this->step - 1]["state"] = OK;
//                $arrContent[$this->step - 1]["msg"] = "Import File - OK. Import Config - OK. Cleanup - OK.";
//                $arrContent[$this->step - 1]["refresh"] = false;
//            }
//            catch (Exception $exc)
//            {
//                $arrContent[$this->step - 1]["state"] = ERROR;
//                $arrContent[$this->step - 1]["error"] = $exc->getMessage();
//                $arrContent[$this->step - 1]["refresh"] = FALSE;
//            }
        }
        else
        {
            try
            {
                $intStart = time();

                switch ($mixStepPool6["step"])
                {
                    /** --------------------------------------------------------
                     * Get files
                     */
                    case 1:
                        $i = 0;
                        $intCountTransfer = 0;

                        // Send allfiles exclude the big things
                        foreach ($mixSyncFiles as $key => $value)
                        {

                            if ($mixStepPool6["files_send"] == count($mixSyncFiles))
                            {
                                break;
                            }


                            if ($i < $mixStepPool6["files_send"])
                            {
                                $i++;
                                continue;
                            }

                            if (in_array($key, $arrSplitFiles))
                            {
                                $i++;
                                continue;
                            }

                            try
                            {
                                $this->objSyncCtoCommunicationClient->getFile($value["path"], TL_ROOT . $GLOBALS['syncCto']['path']['tmp'] . "sync/" . $value["path"]);

                                $intCountTransfer++;
                                if ($intCountTransfer == 100)
                                {
                                    $intCountTransfer = 0;
                                    sleep(1);
                                }
                            }
                            catch (Exception $exc)
                            {
                                try
                                {
                                    sleep(1);
                                    $this->objSyncCtoCommunicationClient->getFile("/" . $value["path"], $GLOBALS['syncCto']['path']['tmp'] . "sync/" . $value["path"]);
                                }
                                catch (Exception $exc)
                                {
                                    $mixStepPool6["skipped"][] = $value['path'] . " Error: " . $exc->getMessage();
                                }
                            }

                            $i++;
                            $mixStepPool6["files_send"] = $i;

                            if ($intStart < (time() - 20))
                            {
                                $mixStepPool6["step"] = 1;
                                $this->Session->set("syncCto_StepPool6", serialize($mixStepPool6));

                                $arrContent[$this->step - 1]["state"] = WORK;

                                if ($i < count($arrSplitFiles))
                                {
                                    $msg = vsprintf("Send %s from %s files.", array($i - count($mixStepPool6["skipped"]), count($mixSyncFiles) - count($arrSplitFiles)));
                                }
                                else
                                {
                                    $msg = vsprintf("Send %s from %s files.", array($i - count($arrSplitFiles) - count($mixStepPool6["skipped"]), count($mixSyncFiles) - count($arrSplitFiles)));
                                }

                                if (count($mixStepPool6["skipped"]) != 0)
                                {
                                    $msg .= "<br/>Skipped files " . count($mixStepPool6["skipped"]);

                                    foreach ($mixStepPool6["skipped"] as $value)
                                    {
                                        $msg .= "<br/>" . $value;
                                    }
                                }

                                $arrContent[$this->step - 1]["msg"] = $msg;

                                $this->step--;

                                $this->parseSyncTo($this->saveContent($arrContent));
                                return;
                            }
                        }

                        $mixStepPool6["step"] = 2;
                        $this->Session->set("syncCto_StepPool6", serialize($mixStepPool6));

                        $arrContent[$this->step - 1]["state"] = WORK;

                        if ($mixStepPool6["files_send"] < count($arrSplitFiles))
                        {
                            $msg = vsprintf("Send %s from %s files.", array($mixStepPool6["files_send"], count($mixSyncFiles) - count($arrSplitFiles)));
                        }
                        else
                        {
                            $msg = vsprintf("Send %s from %s files.", array($mixStepPool6["files_send"] - count($arrSplitFiles), count($mixSyncFiles) - count($arrSplitFiles)));
                        }

                        if (count($mixStepPool6["skipped"]) != 0)
                        {
                            $msg .= "<br/>Skipped files " . count($mixStepPool6["skipped"]);

                            foreach ($mixStepPool6["skipped"] as $value)
                            {
                                $msg .= "<br/>" . $value;
                            }
                        }

                        $this->step--;

                        $this->parseSyncTo($this->saveContent($arrContent));
                        break;

                    /** --------------------------------------------------------
                     * Import Files
                     */
                    case 2:
                        $this->objSyncCtoFiles->moveFile($mixSyncFiles);

                        $mixStepPool6["step"] = 3;
                        $this->Session->set("syncCto_StepPool6", serialize($mixStepPool6));

                        $arrContent[$this->step - 1]["state"] = WORK;
                        $arrContent[$this->step - 1]["msg"] = "File import - OK. Next Config Import.";

                        $this->step--;

                        break;

                    /** --------------------------------------------------------
                     * Import Config
                     */
                    case 3:
                        $arrConfig = $this->objSyncCtoCommunicationClient->getClientLocalconfig();

                        $arrConfigBlacklist = $this->objSyncCtoHelper->getBlacklistLocalconfig();

                        foreach ($arrConfig as $key => $value)
                        {
                            if (in_array($key, $arrConfigBlacklist))
                                unset($arrConfig[$key]);
                        }

                        foreach ($arrConfig as $key => $value)
                        {
                            if (in_array($key, $arrLocalConfig))
                            {
                                $this->Config->update("\$GLOBALS['TL_CONFIG']['" . $key . "']", $value);
                            }
                            else
                            {
                                $this->Config->add("\$GLOBALS['TL_CONFIG']['" . $key . "']", $value);
                            }
                        }

                        $mixStepPool6["step"] = 4;
                        $this->Session->set("syncCto_StepPool6", serialize($mixStepPool6));

                        $arrContent[$this->step - 1]["state"] = WORK;
                        $arrContent[$this->step - 1]["msg"] = "Config import - OK. Next clean up.";

                        $this->step--;

                        break;

                    /** --------------------------------------------------------
                     * Cleanup
                     */
                    case 4:
                        $this->objSyncCtoCommunicationClient->clearClientTempFolder();
                        $this->objSyncCtoFiles->purgeTemp();
                        $this->objSyncCtoCommunicationClient->refererEnable();

                        $msg = "Done. ";

                        if ($mixStepPool6["files_send"] < count($arrSplitFiles))
                        {
                            $msg .= vsprintf("Send %s from %s files.", array($mixStepPool6["files_send"] - count($mixStepPool6["skipped"]), count($mixSyncFiles) - count($arrSplitFiles)));
                        }
                        else
                        {
                            $msg .= vsprintf("Send %s from %s files.", array($mixStepPool6["files_send"] - count($arrSplitFiles) - count($mixStepPool6["skipped"]), count($mixSyncFiles) - count($arrSplitFiles)));
                        }

                        if (count($mixStepPool6["skipped"]) != 0)
                        {
                            $msg .= "<br/><br/>But with errors :<br/><br/><b>Skipped files " . count($mixStepPool6["skipped"]) . "</b><br/>";

                            foreach ($mixStepPool6["skipped"] as $value)
                            {
                                $msg .= "<br/>" . $value;
                            }
                        }

                        $mixStepPool6["step"] = 5;
                        $this->Session->set("syncCto_StepPool6", serialize($mixStepPool6));

                        $arrContent[$this->step - 1]["state"] = OK;
                        $arrContent[$this->step - 1]["msg"] = $msg;
                        $arrContent[$this->step - 1]["refresh"] = false;

                        $this->step--;

                        break;
                }
            }
            catch (Exception $exc)
            {
                print_r($exc);
                exit();

                $arrContent[$this->step - 1]["state"] = ERROR;
                $arrContent[$this->step - 1]["error"] = $exc->getMessage();
                $arrContent[$this->step - 1]["refresh"] = FALSE;
            }
        }

        $this->parseSyncTo($this->saveContent($arrContent));
    }

    /*
     * End SyncCto Sync. From
     * -------------------------------------------------------------------------
     */


    /* -------------------------------------------------------------------------
     * Start SyncCto Sync. To
     */

    private function pageSyncToShowStep1()
    {
        $this->log(vsprintf("Start synchronization client ID %s.", array($this->Input->get("id"))), __CLASS__ . " " . __FUNCTION__, "INFO");

        /* ---------------------------------------------------------------------
         * POST data validate and session save
         */

        // Check sync. typ
        if (strlen($this->Input->post('sync_type')) != 0)
        {
            if ($this->Input->post('sync_type') == SYNCCTO_FULL || $this->Input->post('sync_type') == SYNCCTO_SMALL)
            {
                $this->set("syncCto_Typ", $this->Input->post('sync_type'));
            }
            else
            {
                $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['unknown_method']);
                return;
            }
        }
        else
        {
            $this->set("syncCto_Typ", SYNCCTO_SMALL);
        }

        // Load table lists and merge them
        if ($this->Input->post("table_list_recommend") != "" || $this->Input->post("table_list_none_recommend") != "")
        {
            if ($this->Input->post("table_list_recommend") != "" && $this->Input->post("table_list_none_recommend") != "")
                $arrSyncTables = array_merge($this->Input->post("table_list_recommend"), $this->Input->post("table_list_none_recommend"));
            else if ($this->Input->post("table_list_recommend"))
                $arrSyncTables = $this->Input->post("table_list_recommend");
            else if ($this->Input->post("table_list_none_recommend"))
                $arrSyncTables = $this->Input->post("table_list_none_recommend");

            $this->set("syncCto_SyncTables", $arrSyncTables);
        }
        else
        {
            $this->set("syncCto_SyncTables", FALSE);
        }

        // Files for backup tl_files       
        if (is_array($this->Input->post('filelist')) && count($this->Input->post('filelist')) != 0)
        {
            $this->set("syncCto_Filelist", $this->Input->post('filelist'));
        }
        else
        {
            $this->set("syncCto_Filelist", FALSE);
        }

        $this->set("syncCto_Start", microtime(true));

        /* ---------------------------------------------------------------------
         * None form data
         */


        /* ---------------------------------------------------------------------
         * Step Session 
         */

        // Step 2
        $this->set("syncCto_StepPool2", FALSE);
        // Step 3
        $this->set("syncCto_StepPool3", FALSE);
        // Step 4
        $this->set("syncCto_StepPool4", FALSE);
        // Step 5
        $this->set("syncCto_StepPool5", FALSE);
        // Step 6
        $this->set("syncCto_StepPool6", FALSE);

        /* ---------------------------------------------------------------------
         * Build page
         */

        $this->parseSyncTo($this->saveContent(array(), array(
                    1 => array(
                        "step" => $this->step,
                        "state" => WORK,
                        "msg" => "",
                        "error" => "",
                        "refresh" => TRUE,
                        "desc" => $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step1_help']
                    ),
                ))
        );
    }

    /**
     * Check client communication
     */
    private function pageSyncToShowStep2()
    {
        /* ---------------------------------------------------------------------
         * Init
         */
        // Set Timeout
        set_time_limit(300);

        // State save for this step
        $mixStepPool2 = $this->get("syncCto_StepPool2");
        if ($mixStepPool2 == FALSE)
            $mixStepPool2 = array("step" => 1);

        // Load content
        $arrContent = $this->loadContent();

        /* ---------------------------------------------------------------------
         * Run page
         */
        try
        {
            switch ($mixStepPool2["step"])
            {
                /**
                 * Show step
                 */
                case 1:
                    $mixStepPool2["step"] = 2;

                    $arrContent[$this->step - 1]["state"] = WORK;
                    $arrContent[$this->step - 1]["msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step1_msg1'];

                    $this->step--;

                    break;

                /**
                 * Referer check deactivate
                 */
                case 2:
                    $this->objSyncCtoCommunicationClient->refererDisable();

                    $mixStepPool2["step"] = 3;

                    $arrContent[$this->step - 1]["state"] = WORK;
                    $arrContent[$this->step - 1]["msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step1_msg1'];

                    $this->step--;

                    break;

                /**
                 * Check magical number
                 */
                case 3:
                    $intMagicNumber = $this->objSyncCtoCommunicationClient->makeAGame();

                    $mixStepPool2["step"] = 4;

                    $arrContent[$this->step - 1]["state"] = WORK;
                    $arrContent[$this->step - 1]["msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step1_msg1'];

                    $this->step--;

                    break;

                /**
                 * Check version
                 */
                case 4:
                    $strVersion = $this->objSyncCtoCommunicationClient->getClientVersion();
                    if ($strVersion != SYNCCTO_GET_VERSION)
                    {
                        $this->log(vsprintf("Not the same version on synchronization client ID %s. Serverversion: %s. Clientversion: %s", array($this->Input->get("id"), SYNCCTO_GET_VERSION, $strVersion)), __CLASS__ . " " . __FUNCTION__, "INFO");

                        $mixStepPool2 = FALSE;
                        $arrContent[$this->step - 1]["state"] = ERROR;
                        $arrContent[$this->step - 1]["error"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step1_msg2'];
                        $arrContent[$this->step - 1]["refresh"] = false;

                        break;
                    }

                    $mixStepPool2["step"] = 5;
                    $arrContent[$this->step - 1]["state"] = WORK;
                    $arrContent[$this->step - 1]["msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step1_msg1'];
                    $this->step--;

                    break;

                /**
                 * Clear client temp folder  
                 */
                case 5:
                    $this->objSyncCtoCommunicationClient->clearClientTempFolder();

                    $mixStepPool2["step"] = 6;
                    $arrContent[$this->step - 1]["state"] = WORK;
                    $arrContent[$this->step - 1]["msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step1_msg1'];
                    $this->step--;

                    break;

                /**
                 * Clear Temp Folder
                 */
                case 6:
                    $this->objSyncCtoFiles->purgeTemp();

                    $mixStepPool2 = FALSE;
                    $arrContent[$this->step - 1]["state"] = OK;
                    $arrContent[$this->step - 1]["msg"] = "";
                    $arrContent[] = array(
                        "step" => $this->step,
                        "state" => WORK,
                        "msg" => "",
                        "error" => "",
                        "refresh" => true,
                        "desc" => $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step2_help']
                    );

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

        $this->set("syncCto_StepPool2", $mixStepPool2);

        $this->parseSyncTo($this->saveContent($arrContent));
    }

    /**
     * Build checksumlist and ask client
     */
    private function pageSyncToShowStep3()
    {
        /* ---------------------------------------------------------------------
         * Init
         */
        // Time out 
        @set_time_limit(60);

        // State save for this step
        $mixStepPool3 = $this->get("syncCto_StepPool3");
        if ($mixStepPool3 == FALSE)
            $mixStepPool3 = array("step" => 1);

        // Needed files/information
        $mixFilelist = $this->get("syncCto_Filelist");
        $intSyncTyp = $this->get("syncCto_Typ");

        // Content data for page
        $arrContent = $this->loadContent();

        /* ---------------------------------------------------------------------
         * Run page
         */
        // Check if there is a filelist
        if ($mixFilelist == FALSE && $intSyncTyp == SYNCCTO_SMALL)
        {
            $mixStepPool3 = FALSE;
            $arrContent[$this->step - 1]["state"] = SKIPPED;
            $arrContent[$this->step - 1]["compare"] = "";
            $arrContent[] = array(
                "step" => $this->step,
                "state" => WORK,
                "msg" => "",
                "error" => "",
                "refresh" => true,
                "desc" => $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step3_help']
            );
        }
        else
        {
            try
            {
                switch ($mixStepPool3["step"])
                {
                    /**
                     * Build checksum for TL_Files
                     */
                    case 1:
                        if ($mixFilelist != false && is_array($mixFilelist) && ( $intSyncTyp == SYNCCTO_SMALL || $intSyncTyp == SYNCCTO_FULL ))
                        {
                            $mixStepPool3["tlfiles_checksum"] = $this->objSyncCtoFiles->runTlFilesChecksum(deserialize($mixFilelist));
                            $mixStepPool3["step"] = 2;

                            $arrContent[$this->step - 1]["state"] = WORK;
                            $arrContent[$this->step - 1]["msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step2_msg2'];

                            $this->step--;

                            break;
                        }
                        else
                        {
                            $mixStepPool3["tlfiles_checksum"] = array();
                            $mixStepPool3["step"] = 2;
                        }

                    /**
                     * Build checksum for Conta core
                     */
                    case 2:
                        if ($intSyncTyp == SYNCCTO_FULL && $intSyncTyp != SYNCCTO_SMALL)
                        {
                            $mixStepPool3["core_checksum"] = $this->objSyncCtoFiles->runCoreFilesChecksum();
                            $mixStepPool3["step"] = 3;

                            $arrContent[$this->step - 1]["state"] = WORK;
                            $arrContent[$this->step - 1]["msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step2_msg3'];

                            $this->step--;
                            
                            break;
                        }
                        else
                        {
                            $mixStepPool3["core_checksum"] = array();
                            $mixStepPool3["step"] = 3;
                        }

                    /**
                     * Merge both lists and send it to the client
                     */
                    case 3:                        
                        $arrChecksum = array_merge($mixStepPool3["tlfiles_checksum"], $mixStepPool3["core_checksum"]);
                        
                        unset($mixStepPool3["tlfiles_checksum"]);
                        unset($mixStepPool3["core_checksum"]);
                        $mixStepPool3["checksum"] = $arrChecksum;
                        
                        $mixStepPool3["sync_list"] = $this->objSyncCtoCommunicationClient->sendChecksumList($arrChecksum);
                        
                        $mixStepPool3["step"] = 4;

                        $arrContent[$this->step - 1]["state"] = WORK;
                        $arrContent[$this->step - 1]["msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step2_msg4'];
                                                
                        $this->step--;

                        break;

                    /**
                     * Check for deleted files
                     */
                    case 4:
                        $arrSyncFileList = $mixStepPool3["sync_list"];

                        $arrChecksumClient = (array) $this->objSyncCtoCommunicationClient->getChecksumCore();

                        foreach ($arrChecksumClient as $key => $value)
                        {
                            if (!file_exists($this->objSyncCtoFiles->buildPath($value["path"])))
                            {
                                $arrSyncFileList[$key] = $arrChecksumClient[$key];
                                $arrSyncFileList[$key]["state"] = SyncCtoEnum::FILESTATE_DELETE;
                                $arrSyncFileList[$key]["raw"] = "delete";
                                $arrSyncFileList[$key]["css"] = "deleted";
                            }
                        }

                        $mixStepPool3["sync_list"] = $arrSyncFileList;
                        $mixStepPool3["step"] = 5;

                        $arrContent[$this->step - 1]["state"] = WORK;
                        $arrContent[$this->step - 1]["msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step2_msg4'];

                        $this->step--;

                        break;

                    /**
                     * Set CSS
                     */
                    case 5:
                        $arrSyncFileList = $mixStepPool3["sync_list"];

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

                        $mixStepPool3["sync_list"] = $arrSyncFileList;
                        $mixStepPool3["step"] = 6;

                        $arrContent[$this->step - 1]["state"] = WORK;
                        $arrContent[$this->step - 1]["msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step2_msg4'];

                        $this->step--;

                        break;

                    /**
                     * Show list with files and count
                     */
                    case 6:
                        // Save list
                        $arrSyncFileList = $mixStepPool3["sync_list"];

                        // Del and submit Function
                        $arrDel = $_POST;

                        if (key_exists("delete", $arrDel))
                        {
                            foreach ($arrDel as $key => $value)
                            {
                                unset($arrSyncFileList[$value]);
                                unset($mixStepPool3["sync_list"][$value]);
                            }
                        }
                        else if (key_exists("transfer", $arrDel))
                        {
                            $arrContent[$this->step - 1]["state"] = OK;
                            $arrContent[$this->step - 1]["msg"] = "";
                            $arrContent[$this->step - 1]["compare"] = "";
                            $arrContent[] = array(
                                "step" => $this->step,
                                "state" => WORK,
                                "msg" => "",
                                "error" => "",
                                "refresh" => true,
                                "desc" => $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step3_msg1']
                            );

                            $mixStepPool3 = false;

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

                        $mixStepPool3["missing"] = $intCountMissing;
                        $mixStepPool3["need"] = $intCountNeed;
                        $mixStepPool3["ignored"] = $intCountIgnored;
                        $mixStepPool3["delete"] = $intCountDelete;

                        // Save files and go on or skip here
                        if ($intCountMissing == 0 && $intCountNeed == 0 && $intCountIgnored == 0 && $intCountDelete == 0)
                        {
                            $arrContent[$this->step - 1]["state"] = OK;
                            $arrContent[$this->step - 1]["msg"] = "";
                            $arrContent[$this->step - 1]["compare"] = "";
                            $arrContent[] = array(
                                "step" => $this->step,
                                "state" => WORK,
                                "msg" => "",
                                "error" => "",
                                "refresh" => true,
                                "desc" => $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step3_msg1']
                            );

                            $arrSyncFileList = false;
                            $mixStepPool3 = false;

                            break;
                        }

                        $objTemp = new BackendTemplate("be_syncCto_clients_filelist");
                        $objTemp->filelist = $arrSyncFileList;
                        $objTemp->id = $this->Input->get("id");
                        $objTemp->step = $this->step;
                        $objTemp->totalsize = $intTotalSize;
                        $objTemp->direction = "To";
                        $objTemp->compare_complex = false;

                        // Build content
                        $arrContent[$this->step - 1]["state"] = WORK;
                        $arrContent[$this->step - 1]["msg"] = vsprintf($GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step2_msg1'], array($mixStepPool3["missing"], $mixStepPool3["need"], $mixStepPool3["delete"], $mixStepPool3["ignored"]));
                        $arrContent[$this->step - 1]["refresh"] = false;
                        $arrContent[$this->step - 1]["compare"] = $objTemp->parse();

                        $this->step--;

                        $mixStepPool3["step"] = 6;

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

        $this->set("syncCto_StepPool3", $mixStepPool3);
        $this->set("syncCto_SyncFiles", $arrSyncFileList);

        $this->parseSyncTo($this->saveContent($arrContent));
    }

    /**
     * Build SQL Zip and Send it to client
     */
    private function pageSyncToShowStep4()
    {
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
                        $this->objSyncCtoCommunicationClient->sendFile($GLOBALS['syncCto']['path']['tmp'], $mixStepPool4["zip"]['name'], "", SyncCtoEnum::UPLOAD_SQL_TEMP);

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
     * Split Files
     */
    private function pageSyncToShowStep5()
    {
        /* ---------------------------------------------------------------------
         * INIT
         */

        // Time out 
        @set_time_limit(120);

        // State save for this step
        $mixStepPool5 = $this->get("syncCto_StepPool5");
        if ($mixStepPool5 == FALSE)
            $mixStepPool5 = array("step" => 1);

        // Filelist
        $mixSyncFiles = $this->get("syncCto_SyncFiles");

        //Content
        $arrContent = $this->loadContent();

        /* ---------------------------------------------------------------------
         * RUN
         */

        // Check if there is any file for upload
        if ($mixSyncFiles == false)
        {
            $arrContent[$this->step - 1]["state"] = SKIPPED;
            $arrContent[$this->step - 1]["msg"] = "";
            $arrContent[$this->step - 1]["compare"] = "";
            $arrContent[] = array(
                "step" => $this->step,
                "state" => WORK,
                "msg" => "",
                "error" => "",
                "refresh" => true,
                "desc" => $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step5_help']
            );
        }
        else
        {
            try
            {
                // Timer 
                $intStar = time();

                switch ($mixStepPool5["step"])
                {
                    /**
                     * Load parameter from client
                     */
                    case 1:
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

                        $mixStepPool5["limit"] = $intLimit;
                        $mixStepPool5["percent"] = $intPercent;
                        $mixStepPool5["step"] = 2;

                        $arrContent[$this->step - 1]["state"] = WORK;
                        $arrContent[$this->step - 1]["msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step4_msg1'];

                        $this->step--;

                        break;

                    /**
                     * Search for big file
                     */
                    case 2:
                        foreach ($mixSyncFiles as $key => $value)
                        {
                            if ($value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_DELETE ||
                                    $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_MISSING ||
                                    $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_NEED ||
                                    $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_SAME ||
                                    $value["state"] == SyncCtoEnum::FILESTATE_BOMBASTIC_BIG ||
                                    $value["state"] == SyncCtoEnum::FILESTATE_DELETE)
                            {
                                continue;
                            }
                            else if ($value["size"] > $mixStepPool5["limit"])
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
                            $arrContent[$this->step - 1]["state"] = SKIPPED;
                            $arrContent[$this->step - 1]["msg"] = "";
                            $arrContent[$this->step - 1]["compare"] = "";
                            $arrContent[] = array(
                                "step" => $this->step,
                                "state" => WORK,
                                "msg" => "",
                                "error" => "",
                                "refresh" => true,
                                "desc" => $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step5_help']
                            );

                            $mixStepPool5 = FALSE;

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
                                $mixStepPool5["step"] = 3;

                                $arrContent[$this->step - 1]["state"] = WORK;
                                $arrContent[$this->step - 1]["msg"] = vsprintf($GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step4_msg3'], array(0, $intCountSplit));
                                $arrContent[$this->step - 1]["refresh"] = true;
                                $arrContent[$this->step - 1]["compare"] = "";

                                $this->step--;
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
                                $arrContent[$this->step - 1]["state"] = SKIPPED;
                                $arrContent[$this->step - 1]["msg"] = "";
                                $arrContent[$this->step - 1]["compare"] = "";
                                $arrContent[] = array(
                                    "step" => $this->step,
                                    "state" => WORK,
                                    "msg" => "",
                                    "error" => "",
                                    "refresh" => true,
                                    "desc" => $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step5_help']
                                );

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

                            $this->Session->set("syncCto_StepPool5", FALSE);

                            $mixStepPool5["step"] = 2;
                            $mixStepPool5["splitfiles"] = $mixSplitFiles;
                            $mixStepPool5["splitfiles_count"] = 0;
                            $mixStepPool5["splitfiles_send"] = 0;

                            $objTemp = new BackendTemplate("be_syncCto_clients_filelist");
                            $objTemp->filelist = $arrTempList;
                            $objTemp->id = $this->Input->get("id");
                            $objTemp->step = $this->step;
                            $objTemp->totalsize = $intTotalsize;
                            $objTemp->direction = "To";
                            $objTemp->compare_complex = true;

                            $arrContent[$this->step - 1]["state"] = WORK;
                            $arrContent[$this->step - 1]["msg"] = $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step4_msg2'];
                            $arrContent[$this->step - 1]["refresh"] = FALSE;
                            $arrContent[$this->step - 1]["compare"] = $objTemp->parse();

                            $this->step--;

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
                                    splitFiles($mixSyncFiles[$key]["path"], $GLOBALS['syncCto']['path']['tmp'] . $key, $key, ($mixStepPool5["limit"] / 100 * $mixStepPool5["percent"]));

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
                                $this->objSyncCtoCommunicationClient->sendFile($GLOBALS['syncCto']['path']['tmp'] . $key, $value["splitname"] . ".sync" . $ii, "", UPLOAD_SYNC_SPLIT, $value["splitname"]);
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

        $this->set("syncCto_StepPool5", $mixStepPool5);
        $this->set("syncCto_SyncFiles", $mixSyncFiles);

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

    /* -------------------------------------------------------------------------
     * Helper function
     */

    private function saveContent($arrContent, $arrNewContent = null)
    {
        if ($arrNewContent != null && is_array($arrNewContent))
        {
            foreach ($arrNewContent as $key => $value)
            {
                $arrContent[$key] = $value;
            }
        }

        $this->Session->set("syncCto_Content", serialize($arrContent));

        return $arrContent;
    }

    private function loadContent()
    {
        $arrContent = deserialize($this->Session->get("syncCto_Content"));

        if (!is_array($arrContent))
            $arrContent = array();

        return $arrContent;
    }

    /**
     * Extended the session with typ casting for array, boolean and mix types.
     * 
     * @param string $strName
     * @param mixed $mixVar 
     */
    private function set($strName, $mixVar)
    {
        if ($mixVar === FALSE)
            $this->Session->set($strName, $mixVar);

        elseif (is_array($mixVar))
            $this->Session->set($strName, serialize($mixVar));

        elseif ($mixVar === 0)
            return (int) 0;

        else
            $this->Session->set($strName, $mixVar);
    }

    /**
     * Extended the session with typ casting for array, boolean and mix types.
     * 
     * @param string $strName
     * @return mixed 
     */
    private function get($strName)
    {
        $mixVar = $this->Session->get($strName);

        if ($mixVar === FALSE || $mixVar == "b:0;")
            return FALSE;

        elseif (is_array(deserialize($mixVar)))
            return deserialize($mixVar);

        elseif ($mixVar === 0)
            return (int) 0;

        else
            return $mixVar;
    }

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