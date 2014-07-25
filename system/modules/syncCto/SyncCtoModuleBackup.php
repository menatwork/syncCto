<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL 
 * @filesource
 */

/**
 * Class for backup functions
 */
class SyncCtoModuleBackup extends BackendModule
{
    /* -------------------------------------------------------------------------
     * Variablen
     */

    // Vars
    protected $strTemplate;
    protected $objTemplateContent;
    // Helper Class
    protected $objSyncCtoDatabase;
    protected $objSyncCtoFiles;
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
    // Config
    protected $arrBackupSettings;

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

        // Load helper
        $this->objSyncCtoDatabase = SyncCtoDatabase::getInstance();
        $this->objSyncCtoFiles = SyncCtoFiles::getInstance();

        // Load language 
        $this->loadLanguageFile('tl_syncCto_backup');
        $this->loadLanguageFile('tl_syncCto_steps');
        
        // Load CSS
        $GLOBALS['TL_CSS'][] = 'system/modules/syncCto/assets/css/steps.css';
               
        // Import
        $this->import('BackendUser', 'User');

        // Choose template
        if ($this->Input->get("table") == "" && $this->Input->get("act") == "")
        {
            $this->strTemplate = "be_syncCto_backup";
        }
        else if ($this->Input->get("table") != "" && $this->Input->get("act") != "")
        {
            $this->strTemplate = "be_syncCto_steps";
        }
        else
        {
            $this->strTemplate = "be_syncCto_backup";
        }
    }

    /**
     * Generate page
     */
    protected function compile()
    {
        // Choose template
        if ($this->Input->get("table") == "" && $this->Input->get("act") == "")
        {
            $this->compileStart();
        }
        else if ($this->Input->get("table") != "" && $this->Input->get("act") != "")
        {
            $this->compileBackup();
        }
        else
        {
            $this->compileStart($GLOBALS['TL_LANG']['ERR']['call_directly']);
        }
    }

    /**
     * Generate start page
     */
    protected function compileStart()
    {
        
    }

    /**
     * Generate backup page
     */
    protected function compileBackup()
    {
        // Check if start is set
        if ($this->Input->get("act") != "start" || $this->Input->get("do") != "syncCto_backups")
        {
            $_SESSION["TL_ERROR"] = array($GLOBALS['TL_LANG']['ERR']['call_directly']);
            $this->redirect("contao/main.php?do=syncCto_backups");
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

        // Set template
        $this->Template->showControl = false;

        // Load content from session
        if ($this->intStep != 0)
        {
            $this->loadContenData();
        }

        // Load settings from dca
        $this->loadBackupSettings();

        // Which table is in use
        switch ($this->Input->get("table"))
        {
            case 'tl_syncCto_backup_db':
                $this->pageDbBackup();
                break;

            case 'tl_syncCto_restore_db':
                $this->pageDbRestorePage();
                break;

            case 'tl_syncCto_backup_file':
                $this->pageFileBackupPage();
                break;

            case 'tl_syncCto_restore_file':
                $this->pageFileRestorePage();
                break;

            default :
                $_SESSION["TL_ERROR"][] = $GLOBALS['TL_LANG']['ERR']['unknown_function'];
                $this->redirect("contao/main.php?do=syncCto_backups");
                break;
        }

        // Save content in session
        $this->saveContentData();

        // Set Vars for the template
        $this->setTemplateVars();

        // Save Steppool
        $this->saveStepPool();
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
        $this->Template->subStep = 0;
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

        $this->Session->set("syncCto_Backup_Content", $arrContenData);
    }

    /**
     * Load the current state of the page/synchronization 
     */
    protected function loadContenData()
    {
        $arrContenData = $this->Session->get("syncCto_Backup_Content");

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
        $arrStepPool = $this->Session->get("syncCto_Backup_StepPool");

        if ($arrStepPool == false || !is_array($arrStepPool))
        {
            $arrStepPool = array();
        }

        $this->objStepPool = new StepPool($arrStepPool,  $this->intStep);
    }

    protected function saveStepPool()
    {
        $this->Session->set("syncCto_Backup_StepPool", $this->objStepPool->getArrValues());
    }

    protected function resetStepPool()
    {
        $this->Session->set("syncCto_Backup_StepPool", FALSE);
    }

    protected function initTempLists()
    {
        // Load Files
        $objFileList = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "syncfilelist-Backup.txt"));
        $objFileList->delete();
        $objFileList->close();
    }

    protected function loadTempLists()
    {
        // Load Files
        $objFileList = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "syncfilelist-Backup.txt"));
        $strContent  = $objFileList->getContent();
        if (strlen($strContent) == 0)
        {
            $this->arrListFile = array();
        }
        else
        {
            $this->arrListFile = unserialize($strContent);
        }
        $objFileList->close();
    }

    protected function saveTempLists()
    {
        $objFileList = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "syncfilelist-Backup.txt"));
        $objFileList->write(serialize($this->arrListFile));
        $objFileList->close();
    }

    protected function loadBackupSettings()
    {
        $this->arrBackupSettings = $this->Session->get("syncCto_BackupSettings");

        if (!is_array($this->arrBackupSettings))
        {
            $this->arrBackupSettings = array();
        }
    }

    /* -------------------------------------------------------------------------
     * Functions for Backup and Restore
     */

    /**
     * Backup database
     */
    protected function pageDbBackup()
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
            $this->strUrl = "contao/main.php?do=syncCto_backups&amp;table=tl_syncCto_backup_db&amp;act=start";
            $this->strGoBack = \Environment::get('base') . "contao/main.php?do=syncCto_backups&table=tl_syncCto_backup_db";
            $this->strHeadline = $GLOBALS['TL_LANG']['tl_syncCto_backup_db']['edit'];
            $this->strInformation = "";
            $this->intStep = 1;
            $this->floStart = microtime(true);
            $this->objData = new ContentData(array(), $this->intStep);

            // Reset some Sessions
            $this->resetStepPool();
        }

        // Load step pool
        $this->loadStepPool();

        // Set content back to normale mode
        $this->booRefresh = true;

        $this->objData->setStep(1);
        $this->objData->setState(SyncCtoEnum::WORK_WORK);
        $this->objData->setHtml("");

        try
        {
            switch ($this->intStep)
            {
                // Init Page 
                case 1:
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_backup_db']['step1']);
                    $this->objData->setState(SyncCtoEnum::WORK_WORK);

                    $this->intStep++;
                    break;

                // Run Dump
                case 2:
                    if(!file_exists(TL_ROOT . '/' . SyncCtoHelper::getInstance()->standardizePath($GLOBALS['SYC_PATH']['db'])))
                    {
                        throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['missing_file_folder'] , SyncCtoHelper::getInstance()->standardizePath($GLOBALS['SYC_PATH']['db'])));
                    }
                    
                    $this->objStepPool->zipname = $this->objSyncCtoDatabase->runDump($this->arrBackupSettings['syncCto_BackupTables'], false, false);
                    Dbafs::addResource(SyncCtoHelper::getInstance()->standardizePath($GLOBALS['SYC_PATH']['db'],$this->objStepPool->zipname));

                    $this->intStep++;
                    break;

                // Show last page
                case 3:
                    $this->booFinished = true;
                    $this->booRefresh  = false;

                    $this->objData->setStep(1);
                    $this->objData->setState(SyncCtoEnum::WORK_OK);

                    $strHTML = "<p class='tl_help'><br />";
                    $strHTML .= "<a onclick=\"Backend.openModalIframe({'width':600,'title':'" . $this->objStepPool->zipname . "','url':this.href,'height':216});return false\" href='contao/popup.php?src=" . base64_encode($GLOBALS['TL_CONFIG']['uploadPath'] . "/syncCto_backups/database/" . $this->objStepPool->zipname) . "'>" . $GLOBALS['TL_LANG']['MSC']['fileDownload'] . "</a>";
                    $strHTML .= "</p>";

                    $this->objData->setStep(2);
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['complete']);
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_backup_db']['complete'] . " " . $this->objStepPool->zipname);
                    $this->objData->setHtml($strHTML);
                    $this->objData->setState(SyncCtoEnum::WORK_OK);
                    break;
            }
        }
        catch (Exception $exc)
        {
            $objErrTemplate              = new BackendTemplate('be_syncCto_error');
            $objErrTemplate->strErrorMsg = $exc->getMessage();
            
            $this->booRefresh = false;
            $this->objData->setState(SyncCtoEnum::WORK_ERROR);
            $this->objData->setHtml($objErrTemplate->parse());
        }
    }

    /**
     * Restore database
     */
    protected function pageDbRestorePage()
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
            $this->strUrl = "contao/main.php?do=syncCto_backups&amp;table=tl_syncCto_restore_db&amp;act=start";
            $this->strGoBack =  \Environment::get('base') . "contao/main.php?do=syncCto_backups&table=tl_syncCto_restore_db";
            $this->strHeadline = $GLOBALS['TL_LANG']['tl_syncCto_restore_db']['edit'];
            $this->strInformation = "";
            $this->intStep = 1;
            $this->floStart = microtime(true);
            $this->objData = new ContentData(array(), $this->intStep);

            // Reset some Sessions
            $this->resetStepPool();
        }

        // Load step pool
        $this->loadStepPool();

        // Set content back to normale mode
        $this->booRefresh = true;

        $this->objData->setStep(1);
        $this->objData->setState(SyncCtoEnum::WORK_WORK);
        $this->objData->setHtml("");

        try
        {
            switch ($this->intStep)
            {
                case 1:
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_restore_db']['step1']);
                    $this->objData->setState(SyncCtoEnum::WORK_WORK);
                    $this->intStep++;
                    break;

                case 2:
                    $this->objSyncCtoDatabase->runRestore($this->arrBackupSettings['syncCto_restoreFile']);
                    $this->intStep++;
                    break;

                case 3:
                    $this->booFinished = true;
                    $this->booRefresh  = false;

                    $this->objData->setStep(1);
                    $this->objData->setState(SyncCtoEnum::WORK_OK);

                    $this->objData->setStep(2);
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['complete']);
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_restore_db']['complete'] . " " . $this->objStepPool->zipname);
                    $this->objData->setState(SyncCtoEnum::WORK_OK);
                    break;
            }
        }
        catch (Exception $exc)
        {
            $objErrTemplate              = new BackendTemplate('be_syncCto_error');
            $objErrTemplate->strErrorMsg = $exc->getMessage();

            $this->booRefresh = false;
            $this->objData->setState(SyncCtoEnum::WORK_ERROR);
            $this->objData->setHtml($objErrTemplate->parse());
        }
    }

    /**
     * Backup filesystem 
     */
    protected function pageFileBackupPage()
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
            $this->strUrl = "contao/main.php?do=syncCto_backups&amp;table=tl_syncCto_backup_file&amp;act=start";
            $this->strGoBack =  \Environment::get('base') . "contao/main.php?do=syncCto_backups&table=tl_syncCto_backup_file";
            $this->strHeadline = $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['edit'];
            $this->strInformation = "";
            $this->intStep = 1;
            $this->floStart = microtime(true);
            $this->objData = new ContentData(array(), $this->intStep);

            // Reset some Sessions
            $this->resetStepPool();
        }

        // Load step pool
        $this->loadStepPool();

        // Set content back to normale mode
        $this->booRefresh = true;
        
        $this->objData->setStep(1);
        $this->objData->setState(SyncCtoEnum::WORK_WORK);
        $this->objData->setHtml("");

        try
        {
            switch ($this->intStep)
            {
                case 1:
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_backup_file']['step1']);
                    $this->objData->setState(SyncCtoEnum::WORK_WORK);

                    $this->objStepPool->zipname      = "";
                    $this->objStepPool->skippedfiles = array();

                    $this->intStep++;
                    break;

                case 2:
                    if(!file_exists(TL_ROOT . '/' . SyncCtoHelper::getInstance()->standardizePath($GLOBALS['SYC_PATH']['file'])))
                    {
                        throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['missing_file_folder'] , SyncCtoHelper::getInstance()->standardizePath($GLOBALS['SYC_PATH']['file'])));
                    }
                    
                    $arrResult                       = $this->objSyncCtoFiles->runDump($this->arrBackupSettings['backup_name'], $this->arrBackupSettings['core_files'], $this->arrBackupSettings['filelist']);
                    $this->objStepPool->zipname      = $arrResult["name"];
                    $this->objStepPool->skippedfiles = $arrResult["skipped"];

                    \Dbafs::addResource(SyncCtoHelper::getInstance()->standardizePath($GLOBALS['SYC_PATH']['file'],$this->objStepPool->zipname));

                    $this->intStep++;
                    break;

                case 3:
                    $this->booFinished = true;
                    $this->booRefresh  = false;

                    $this->objData->setStep(1);
                    $this->objData->setState(SyncCtoEnum::WORK_OK);

                    $strHTML = "<p class='tl_help'><br />";
                    $strHTML .= "<a onclick=\"Backend.openModalIframe({'width':600,'title':'" . $this->objStepPool->zipname . "','url':this.href,'height':216});return false\" href='contao/popup.php?src=" . base64_encode($GLOBALS['TL_CONFIG']['uploadPath'] . "/syncCto_backups/files/" . $this->objStepPool->zipname) . "'>" . $GLOBALS['TL_LANG']['MSC']['fileDownload'] . "</a>";
                    $strHTML .= "</p>";

                    if (count($this->objStepPool->skippedfiles) != 0)
                    {
                        $strHTML = '<br /><p class="tl_help">' . count($this->objStepPool->skippedfiles) . $GLOBALS['TL_LANG']['MSC']['skipped_files'] . '</p>';

                        $strHTML .= '<ul class="fileinfo">';
                        foreach ($this->objStepPool->skippedfiles as $value)
                        {
                            $strHTML .= "<li>" . $value . "</li>";
                        }
                        $strHTML .= "</ul>";
                    }

                    $this->objData->setStep(2);
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['complete']);
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_backup_file']['complete'] . " " . $this->objStepPool->zipname);
                    $this->objData->setHtml($strHTML);
                    $this->objData->setState(SyncCtoEnum::WORK_OK);
                    break;
            }
        }
        catch (Exception $exc)
        {
            $objErrTemplate              = new BackendTemplate('be_syncCto_error');
            $objErrTemplate->strErrorMsg = $exc->getMessage();
            
            $this->booRefresh = false;
            $this->objData->setState(SyncCtoEnum::WORK_ERROR);
            $this->objData->setHtml($objErrTemplate->parse());
            
        }
    }

    protected function pageFileRestorePage()
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
            $this->strUrl = "contao/main.php?do=syncCto_backups&amp;table=tl_syncCto_restore_file&amp;act=start";
            $this->strGoBack =  \Environment::get('base') . "contao/main.php?do=syncCto_backups&table=tl_syncCto_restore_file";
            $this->strHeadline = $GLOBALS['TL_LANG']['tl_syncCto_restore_file']['edit'];
            $this->strInformation = "";
            $this->intStep = 1;
            $this->floStart = microtime(true);
            $this->objData = new ContentData(array(), $this->intStep);

            // Reset some Sessions
            $this->resetStepPool();
        }

        // Load step pool
        $this->loadStepPool();

        // Set content back to normale mode
        $this->booRefresh = true;

        $this->objData->setStep(1);
        $this->objData->setState(SyncCtoEnum::WORK_WORK);
        $this->objData->setHtml("");

        try
        {
            switch ($this->intStep)
            {
                case 1:
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_restore_file']['step1']);
                    $this->objData->setState(SyncCtoEnum::WORK_WORK);
                    $this->intStep++;
                    break;

                case 2:
                    try
                    {
                        $mixResponse = $this->objSyncCtoFiles->runRestore($this->arrBackupSettings['backup_file']);

                        if ($mixResponse !== true)
                        {
                            $strHTML = $GLOBALS['TL_LANG']['ERR']['cant_extract_file'];
                            $strHTML .= "<br />";
                            $strHTML .= "<ul>";
                            foreach ($mixResponse as $value)
                            {
                                $strHTML .= "<li>" . $value . "</li>";
                            }
                            $strHTML .= "</ul>";

                            $this->booError = true;
                            $this->strError = $strHTML;
                            $this->objData->setStep(1);
                            $this->objData->setDescription($GLOBALS['TL_LANG']['ERR']['cant_extract_file']);
                            break;
                        }

                        $this->intStep++;
                        break;
                    }
                    catch (Exception $exc)
                    {
                        $this->booError = true;
                        $this->strError = $exc->getMessage();
                        $this->objData->setStep(1);
                        $this->objData->setState(SyncCtoEnum::WORK_ERROR);
                        break;
                    }

                case 3:
                    $objDate = new Date();

                    $this->booFinished = true;
                    $this->booRefresh  = false;

                    $this->objData->setStep(1);
                    $this->objData->setState(SyncCtoEnum::WORK_OK);

                    $this->objData->setStep(2);
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['complete']);
                    $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_restore_file']['complete'], array($this->arrBackupSettings['backup_file'], $objDate->time, $objDate->date)));
                    $this->objData->setState(SyncCtoEnum::WORK_OK);
                    break;
            }
        }
        catch (Exception $exc)
        {
            $objErrTemplate              = new BackendTemplate('be_syncCto_error');
            $objErrTemplate->strErrorMsg = $exc->getMessage();

            $this->booRefresh = false;
            $this->objData->setState(SyncCtoEnum::WORK_ERROR);
            $this->objData->setHtml($objErrTemplate->parse());
        }
    }

}
