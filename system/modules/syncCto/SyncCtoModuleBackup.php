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
class SyncCtoModuleBackup extends BackendModule
{

    // Variablen
    protected $strTemplate = 'be_syncCto_empty';
    protected $objTemplateContent;
    // Helper Class
    protected $objSyncCtoDatabase;
    protected $objSyncCtoFiles;

    function __construct(DataContainer $objDc = null)
    {
        $this->import('BackendUser', 'User');
        parent::__construct($objDc);

        $this->objSyncCtoDatabase = SyncCtoDatabase::getInstance();
        $this->objSyncCtoFiles = SyncCtoFiles::getInstance();

        $this->loadLanguageFile('tl_syncCto_backup');
        $this->loadLanguageFile('tl_syncCto_steps');
    }

    /* -------------------------------------------------------------------------
     * Core Functions
     */

    /**
     * Generate page
     */
    protected function compile()
    {
        if ($this->Input->get("do") == "syncCto_backups"
                && strlen($this->Input->get("act")) != 0
                && strlen($this->Input->get("table")) != 0)
        {
            // Which table is in use
            switch ($this->Input->get("table"))
            {
                case 'tl_syncCto_backup_db':
                    // Which function should be used
                    switch ($this->Input->get("act"))
                    {
                        case 'start':
                            $this->parseDbBackupPage();
                            break;

                        default:
                            $this->parseStartPage($GLOBALS['TL_LANG']['ERR']['unknown_function']);
                            break;
                    }
                    break;

                case 'tl_syncCto_restore_db':
                    // Which function should be used
                    switch ($this->Input->get("act"))
                    {
                        case 'start':
                            $this->parseDbRestorePage();
                            break;

                        default:
                            $this->parseStartPage($GLOBALS['TL_LANG']['ERR']['unknown_function']);
                            break;
                    }
                    break;

                case 'tl_syncCto_backup_file':
                    // Which function should be used
                    switch ($this->Input->get("act"))
                    {
                        case 'start':
                            $this->parseFileBackupPage();
                            break;

                        default:
                            $this->parseStartPage($GLOBALS['TL_LANG']['ERR']['unknown_function']);
                            break;
                    }
                    break;

                case 'tl_syncCto_restore_file':
                    // Which function should be used
                    switch ($this->Input->get("act"))
                    {
                        case 'start':
                            $this->parseFileRestorePage();
                            break;

                        default:
                            $this->parseStartPage($GLOBALS['TL_LANG']['ERR']['unknown_function']);
                            break;
                    }
                    break;

                default:
                    $this->parseStartPage($GLOBALS['TL_LANG']['ERR']['unknown_tables']);
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

    /* -------------------------------------------------------------------------
     * Functions for 'Backup' and 'Restore'
     */

    /**
     * Datenbank Backup
     *
     * @return <type>
     */
    protected function parseDbBackupPage()
    {
        // Init
        $this->objTemplateContent = new BackendTemplate('be_syncCto_steps');
        $this->loadLanguageFile("tl_syncCto_backup_db");

        if ($this->Input->get("step") == "" || $this->Input->get("step") == null)
            $step = 1;
        else
            $step = intval($this->Input->get("step"));

        $arrContenData = $this->Session->get("SyncCto_DB_Content");
        $arrStepPool = $this->Session->get("SyncCto_DB_StepPool");

        // Do something
        switch ($step)
        {
            // Ini Page 
            case 1:
                $arrContenData = array(
                    "error" => false,
                    "error_msg" => "",
                    "refresh" => true,
                    "finished" => false,
                    "step" => 2,
                    "url" => "contao/main.php?do=syncCto_backups&amp;table=tl_syncCto_backup_db&amp;act=start",
                    "start" => microtime(true),
                    "headline" => $GLOBALS['TL_LANG']['tl_syncCto_backup_db']['edit'],
                    "information" => "",
                    "data" => array(),
                    "goBack" => "contao/main.php?do=syncCto_backups&table=tl_syncCto_backup_db"
                );

                $arrContenData["data"][1] = array(
                    "title" => $GLOBALS['TL_LANG']['MSC']['step'] . " 1",
                    "description" => $GLOBALS['TL_LANG']['tl_syncCto_backup_db']['step1'],
                    "state" => $GLOBALS['TL_LANG']['MSC']['progress']
                );

                break;

            // Run Dump
            case 2:
                try
                {
                    $arrStepPool["zipname"] = $this->objSyncCtoDatabase->runDump($arrStepPool["tables"], false);

                    $arrContenData["step"]++;
                    break;
                }
                catch (Exception $exc)
                {
                    $arrContenData["error"] = true;
                    $arrContenData["error_msg"] = $exc->getMessage();
                    $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['MSC']['error'];

                    break;
                }

            // Show last page
            case 3:
                $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['MSC']['ok'];

                $arrContenData["finished"] = true;
                $arrContenData["data"][2]["title"] = $GLOBALS['TL_LANG']['MSC']['complete'];
                $arrContenData["data"][2]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_backup_db']['complete'] . " " . $arrStepPool["zipname"];
                $arrContenData["data"][2]["html"] = "<p class='tl_help'><br />";
                $arrContenData["data"][2]["html"] .= "<a onclick='Backend.openWindow(this, 600, 235); return false;' title='In einem neuen Fenster ansehen' href='contao/popup.php?src=" . $GLOBALS['TL_CONFIG']['uploadPath'] . "/syncCto_backups/database/" . $arrStepPool["zipname"] . "'>" . $GLOBALS['TL_LANG']['tl_syncCto_backup_db']['download_backup'] . "</a>";
                $arrContenData["data"][2]["html"] .= "</p>";

                $this->Session->set("SyncCto_DB_StepPool", "");
                break;

            // Deafult action
            default:
                $arrContenData["error"] = true;
                $arrContenData["error_msg"] = $GLOBALS['TL_LANG']['MSC']['unknown_step'];
                $arrContenData["data"] = array();
                break;
        }

        // Set templatevars and set session$this->objTemplateContent->goBack = $this->script . "contao/main.php?do=syncCto_backups&amp;table=tl_syncCto_backup_db&amp;act=edit";
        $this->objTemplateContent->data = $arrContenData["data"];
        $this->objTemplateContent->step = $arrContenData["step"];
        $this->objTemplateContent->error = $arrContenData["error"];
        $this->objTemplateContent->error_msg = $arrContenData["error_msg"];
        $this->objTemplateContent->refresh = $arrContenData["refresh"];
        $this->objTemplateContent->url = $arrContenData["url"];
        $this->objTemplateContent->start = $arrContenData["start"];
        $this->objTemplateContent->headline = $arrContenData["headline"];
        $this->objTemplateContent->information = $arrContenData["information"];
        $this->objTemplateContent->finished = $arrContenData["finished"];
        $this->objTemplateContent->goBack = $arrContenData["goBack"];

        $this->Session->set("SyncCto_DB_Content", $arrContenData);
        $this->Session->set("SyncCto_DB_StepPool", $arrStepPool);
    }

    /**
     * Datenbank wiederherstellen
     *
     * @return <type>
     */
    protected function parseDbRestorePage()
    {
        // Init
        $this->objTemplateContent = new BackendTemplate('be_syncCto_steps');
        $this->loadLanguageFile("tl_syncCto_restore_db");

        if ($this->Input->get("step") == "" || $this->Input->get("step") == null)
            $step = 1;
        else
            $step = intval($this->Input->get("step"));

        $arrContenData = $this->Session->get("SyncCto_DB_Content");
        $arrStepPool = $this->Session->get("SyncCto_DB_StepPool");

        switch ($step)
        {
            case 1:
                $arrContenData = array(
                    "error" => false,
                    "error_msg" => "",
                    "refresh" => true,
                    "finished" => false,
                    "step" => 2,
                    "url" => "contao/main.php?do=syncCto_backups&amp;table=tl_syncCto_restore_db&amp;act=start",
                    "start" => microtime(true),
                    "headline" => $GLOBALS['TL_LANG']['tl_syncCto_restore_db']['edit'],
                    "information" => "",
                    "data" => array(),
                    "goBack" => "contao/main.php?do=syncCto_backups&table=tl_syncCto_restore_db"
                );

                $arrContenData["data"][1] = array(
                    "title" => $GLOBALS['TL_LANG']['MSC']['step'] . " 1",
                    "description" => $GLOBALS['TL_LANG']['tl_syncCto_restore_db']['step1'],
                    "state" => $GLOBALS['TL_LANG']['MSC']['progress']
                );

                break;

            case 2:
                try
                {
                    $this->objSyncCtoDatabase->runRestore($arrStepPool["SyncCto_Restore"]);

                    $arrContenData["step"]++;
                    break;
                }
                catch (Exception $exc)
                {
                    $arrContenData["error"] = true;
                    $arrContenData["error_msg"] = $exc->getMessage();

                    break;
                }

            case 3:
                $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['MSC']['ok'];

                $arrContenData["finished"] = true;
                $arrContenData["data"][2]["title"] = $GLOBALS['TL_LANG']['MSC']['complete'];
                $arrContenData["data"][2]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_restore_db']['complete'];

                $this->Session->set("SyncCto_DB_StepPool", "");
                break;

            default:
                $arrContenData["error"] = true;
                $arrContenData["error_msg"] = $GLOBALS['TL_LANG']['MSC']['unknown_step'];
                $arrContenData["data"] = array();
                break;
        }

        // Set templatevars and set session
        $this->objTemplateContent->goBack = $this->script . "contao/main.php?do=syncCto_backups&amp;table=tl_syncCto_restore_db&amp;act=edit";
        $this->objTemplateContent->data = $arrContenData["data"];
        $this->objTemplateContent->step = $arrContenData["step"];
        $this->objTemplateContent->error = $arrContenData["error"];
        $this->objTemplateContent->error_msg = $arrContenData["error_msg"];
        $this->objTemplateContent->refresh = $arrContenData["refresh"];
        $this->objTemplateContent->url = $arrContenData["url"];
        $this->objTemplateContent->start = $arrContenData["start"];
        $this->objTemplateContent->headline = $arrContenData["headline"];
        $this->objTemplateContent->information = $arrContenData["information"];
        $this->objTemplateContent->finished = $arrContenData["finished"];
        $this->objTemplateContent->goBack = $arrContenData["goBack"];

        $this->Session->set("SyncCto_DB_Content", $arrContenData);
        $this->Session->set("SyncCto_DB_StepPool", $arrStepPool);
    }

    protected function parseFileBackupPage()
    {
        // Init 
        $this->objTemplateContent = new BackendTemplate('be_syncCto_steps');
        $this->loadLanguageFile("tl_syncCto_backup_file");

        if ($this->Input->get("step") == "" || $this->Input->get("step") == null)
            $step = 1;
        else
            $step = intval($this->Input->get("step"));

        $arrContenData = $this->Session->get("SyncCto_File_Content");
        $arrStepPool = $this->Session->get("SyncCto_File_StepPool");

        switch ($step)
        {
            case 1:
                $arrContenData = array(
                    "error" => false,
                    "error_msg" => "",
                    "refresh" => true,
                    "finished" => false,
                    "step" => 2,
                    "url" => "contao/main.php?do=syncCto_backups&amp;table=tl_syncCto_backup_file&amp;act=start",
                    "start" => microtime(true),
                    "headline" => $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['edit'],
                    "information" => "",
                    "data" => array(),
                    "goBack" => "contao/main.php?do=syncCto_backups&table=tl_syncCto_backup_file"
                );

                $arrContenData["data"][1] = array(
                    "title" => $GLOBALS['TL_LANG']['MSC']['step'] . " 1",
                    "description" => $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['step1'],
                    "state" => $GLOBALS['TL_LANG']['MSC']['progress']
                );

                break;

            case 2:
                if ($arrStepPool["syncCto_Typ"] == SYNCCTO_SMALL)
                {
                    $arrResult = $this->objSyncCtoFiles->runDumpFiles($arrStepPool["backup_name"], $arrStepPool["filelist"]);
                    $arrStepPool["zipname"] = $arrResult["name"];
                    $arrStepPool["skippedfiles"] = $arrResult["skipped"];
                }

                if ($arrStepPool["syncCto_Typ"] == SYNCCTO_FULL)
                {
                    $arrResult = $this->objSyncCtoFiles->runDump($arrStepPool["backup_name"], $arrStepPool["filelist"]);
                    $arrStepPool["zipname"] = $arrResult["name"];
                    $arrStepPool["skippedfiles"] = $arrResult["skipped"];
                }

                $arrContenData["step"]++;
                break;

            case 3:
                $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['MSC']['ok'];

                $arrContenData["finished"] = true;
                $arrContenData["data"][2]["title"] = $GLOBALS['TL_LANG']['MSC']['complete'];
                $arrContenData["data"][2]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['complete'] . " " . $arrStepPool["zipname"];
                $arrContenData["data"][2]["html"] = "<p class='tl_help'><br />";
                $arrContenData["data"][2]["html"] .= "<a onclick='Backend.openWindow(this, 600, 235); return false;' title='In einem neuen Fenster ansehen' href='contao/popup.php?src=" . $GLOBALS['TL_CONFIG']['uploadPath'] . "/syncCto_backups/files/" . $arrStepPool["zipname"] . "'>" . $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['download_backup'] . "</a>";
                $arrContenData["data"][2]["html"] .= "</p>";
                
                if (count($arrStepPool["skippedfiles"]) != 0)
                {
                    $strHTML = '<br /><p class="tl_help">' . count($arrStepPool["skippedfiles"]) . $GLOBALS['TL_LANG']['MSC']['skipped_files'] . '</p>';
                    $compare .= '<ul class="fileinfo">';
                    foreach ($arrStepPool["skippedfiles"] as $key => $value)
                    {                       
                            $compare .= "<li>" . $value . "</li>";                       
                    }
                    $compare .= "</ul>";
                    
                    $arrContenData["data"][2]["html"] .= $compare;
                }

                $this->Session->set("SyncCto_DB_StepPool", "");
                break;

            default:
                $arrContenData["error"] = true;
                $arrContenData["error_msg"] = $GLOBALS['TL_LANG']['MSC']['unknown_step'];
                $arrContenData["data"] = array();
                break;
        }

        // Set templatevars and set session
        $this->objTemplateContent->goBack = $this->script . "contao/main.php?do=syncCto_backups&amp;table=tl_syncCto_backup_file&amp;act=edit";
        $this->objTemplateContent->data = $arrContenData["data"];
        $this->objTemplateContent->step = $arrContenData["step"];
        $this->objTemplateContent->error = $arrContenData["error"];
        $this->objTemplateContent->error_msg = $arrContenData["error_msg"];
        $this->objTemplateContent->refresh = $arrContenData["refresh"];
        $this->objTemplateContent->url = $arrContenData["url"];
        $this->objTemplateContent->start = $arrContenData["start"];
        $this->objTemplateContent->headline = $arrContenData["headline"];
        $this->objTemplateContent->information = $arrContenData["information"];
        $this->objTemplateContent->finished = $arrContenData["finished"];
        $this->objTemplateContent->goBack = $arrContenData["goBack"];

        $this->Session->set("SyncCto_File_Content", $arrContenData);
        $this->Session->set("SyncCto_File_StepPool", $arrStepPool);
    }

    /**
     * Datenbank wiederherstellen
     *
     * @return <type>
     */
    protected function parseFileRestorePage()
    {
        $this->objTemplateContent = new BackendTemplate('be_syncCto_steps');
        $this->loadLanguageFile('tl_syncCto_restore_file');

        if ($this->Input->get("step") == "" || $this->Input->get("step") == null)
            $step = 1;
        else
            $step = intval($this->Input->get("step"));

        $arrContenData = $this->Session->get("SyncCto_File_Content");
        $arrStepPool = $this->Session->get("SyncCto_File_StepPool");

        switch ($step)
        {
            case 1:
                $arrContenData = array(
                    "error" => false,
                    "error_msg" => "",
                    "refresh" => true,
                    "finished" => false,
                    "step" => 2,
                    "url" => "contao/main.php?do=syncCto_backups&amp;table=tl_syncCto_restore_file&amp;act=start",
                    "start" => microtime(true),
                    "headline" => $GLOBALS['TL_LANG']['tl_syncCto_restore_file']['edit'],
                    "information" => "",
                    "data" => array(),
                    "goBack" => "contao/main.php?do=syncCto_backups&table=tl_syncCto_restore_file"
                );

                $arrContenData["data"][1] = array(
                    "title" => $GLOBALS['TL_LANG']['MSC']['step'] . " 1",
                    "description" => $GLOBALS['TL_LANG']['tl_syncCto_restore_file']['step1'],
                    "state" => $GLOBALS['TL_LANG']['MSC']['progress']
                );

                break;

            case 2:
                try
                {
                    $this->objSyncCtoFiles->runRestore($arrStepPool["file"]);

                    $arrContenData["step"]++;
                    break;
                }
                catch (Exception $exc)
                {
                    $arrContenData["error"] = true;
                    $arrContenData["error_msg"] = $exc->getMessage();
                    break;
                }

            case 3:
                $objDate = new Date();

                $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['MSC']['ok'];

                $arrContenData["finished"] = true;
                $arrContenData["data"][2]["title"] = $GLOBALS['TL_LANG']['MSC']['complete'];
                $arrContenData["data"][2]["description"] = vsprintf($GLOBALS['TL_LANG']['tl_syncCto_restore_file']['complete'], array($arrStepPool["file"], $objDate->time, $objDate->date));

                $this->Session->set("SyncCto_DB_StepPool", "");
                break;

            default:
                $arrContenData["error"] = true;
                $arrContenData["error_msg"] = $GLOBALS['TL_LANG']['MSC']['unknown_step'];
                $arrContenData["data"] = array();
                break;
        }

        // Set templatevars and set session
        $this->objTemplateContent->goBack = $this->script . "contao/main.php?do=syncCto_backups&amp;table=tl_syncCto_restore_file&amp;act=edit";
        $this->objTemplateContent->data = $arrContenData["data"];
        $this->objTemplateContent->step = $arrContenData["step"];
        $this->objTemplateContent->error = $arrContenData["error"];
        $this->objTemplateContent->error_msg = $arrContenData["error_msg"];
        $this->objTemplateContent->refresh = $arrContenData["refresh"];
        $this->objTemplateContent->url = $arrContenData["url"];
        $this->objTemplateContent->start = $arrContenData["start"];
        $this->objTemplateContent->headline = $arrContenData["headline"];
        $this->objTemplateContent->information = $arrContenData["information"];
        $this->objTemplateContent->finished = $arrContenData["finished"];
        $this->objTemplateContent->goBack = $arrContenData["goBack"];

        $this->Session->set("SyncCto_File_Content", $arrContenData);
        $this->Session->set("SyncCto_File_StepPool", $arrStepPool);
    }

}

?>