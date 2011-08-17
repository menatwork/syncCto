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

class SyncCtoModuleBackup extends BackendModule
{

    // Variablen
    protected $strTemplate = 'be_syncCto_empty';
    protected $objTemplateContent;
    // Helper Class
    protected $objSyncCtoDatabase;
    protected $objSyncCtoFiles;
    protected $objSyncCtoCallback;

    function __construct(DataContainer $objDc = null)
    {
        $this->import('BackendUser', 'User');
        parent::__construct($objDc);


        $this->objSyncCtoDatabase = SyncCtoDatabase::getInstance();
        $this->objSyncCtoFiles = SyncCtoFiles::getInstance();
        $this->objSyncCtoCallback = SyncCtoCallback::getInstance();

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
                            $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['unknown_function']);
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
                            $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['unknown_function']);
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
                            $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['unknown_function']);
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
                            $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['unknown_function']);
                            break;
                    }
                    break;

                default:
                    $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['unknown_tables']);
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
        $this->loadLanguageFile('tl_syncCto_backup_db');
        $this->objTemplateContent = new BackendTemplate('be_syncCto_backup_db');

        if ($this->Input->get("step") == "" || $this->Input->get("step") == null)
        {
            $step = 1;
        }
        else
        {
            $step = intval($this->Input->get("step"));
        }

        switch ($step)
        {
            case 1:
                if ($this->Input->post("table_list_recommend") == "" && $this->Input->post("table_list_none_recommend") == "")
                {
                    $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['no_backup_tables']);
                    return;
                }

                if ($this->Input->post("table_list_recommend") != "" && $this->Input->post("table_list_none_recommend") != "")
                    $arrTablesBackup = array_merge($this->Input->post("table_list_recommend"), $this->Input->post("table_list_none_recommend"));
                else if ($this->Input->post("table_list_recommend"))
                    $arrTablesBackup = $this->Input->post("table_list_recommend");
                else if ($this->Input->post("table_list_none_recommend"))
                    $arrTablesBackup = $this->Input->post("table_list_none_recommend");

                $this->Session->set("SyncCto_Tables", serialize($arrTablesBackup));

                $this->objTemplateContent->step = $step;
                $this->objTemplateContent->condition = array('1' => WORK);
                $this->objTemplateContent->refresh = true;

                return;

            case 2:
                $arrTablesBackup = deserialize($this->Session->get("SyncCto_Tables"));
                if (!is_array($arrTablesBackup) || $arrTablesBackup == "" || $arrTablesBackup == null)
                {
                    $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['restore_session_tables']);
                    return;
                }

                try
                {
                    $arrZip = $this->objSyncCtoDatabase->runCreateZip();
                                    
                    $this->Session->set("SyncCto_ZipId", $arrZip['id']);
                    $this->Session->set("SyncCto_ZipName", $arrZip['name']);
                }
                catch (Exception $exc)
                {
                    $this->objTemplateContent->step = $step - 1;
                    $this->objTemplateContent->condition = array('1' => ERROR);
                    $this->objTemplateContent->refresh = false;
                    $this->objTemplateContent->error = $exc->getMessage();
                    return;
                }

                $this->objTemplateContent->step = $step;
                $this->objTemplateContent->condition = array('1' => OK, '2' => WORK);
                $this->objTemplateContent->refresh = true;
                return;

            case 3:
                $arrTablesBackup = deserialize($this->Session->get("SyncCto_Tables"));               
                if (!is_array($arrTablesBackup) || $arrTablesBackup == "" || $arrTablesBackup == null)
                {
                    $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['restore_session_tables']);
                    return;
                }

                $strZipId = $this->Session->get("SyncCto_ZipId");                
                if ($strZipId == "" || $strZipId == null)
                {
                    $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['restore_session_zip_id']);
                    return;
                }

                $strZipName = $this->Session->get("SyncCto_ZipName");                
                if ($strZipName == "" || $strZipName == null)
                {
                    $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['restore_session_zip_name']);
                    return;
                }

                try
                {
                    $this->objSyncCtoDatabase->runDumpSQL($arrTablesBackup, $strZipName);
                    sleep(3);
                    $this->objSyncCtoDatabase->runDumpInsert($arrTablesBackup, $strZipName);
                }
                catch (Exception $exc)
                {
                    $this->objTemplateContent->step = $step - 1;
                    $this->objTemplateContent->condition = array('1' => OK, '2' => ERROR);
                    $this->objTemplateContent->refresh = false;
                    $this->objTemplateContent->error = $exc->getMessage();
                    return;
                }

                $this->objTemplateContent->step = $step;
                $this->objTemplateContent->condition = array('1' => OK, '2' => OK, '3' => WORK);
                $this->objTemplateContent->refresh = true;
                return;

            case 4:
                $strZipId = $this->Session->get("SyncCto_ZipId");
                if ($strZipId == "" || $strZipId == null)
                {
                    $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['restore_session_zip_id']);
                    return;
                }

                $strZipName = $this->Session->get("SyncCto_ZipName");
                if ($strZipName == "" || $strZipName == null)
                {
                    $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['restore_session_zip_name']);
                    return;
                }

                try
                {
                    $this->objSyncCtoDatabase->runCheckZip($strZipName, false);
                }
                catch (Exception $exc)
                {
                    $this->objTemplateContent->step = $step - 1;
                    $this->objTemplateContent->condition = array('1' => OK, '2' => OK, '3' => ERROR);
                    $this->objTemplateContent->refresh = false;
                    $this->objTemplateContent->error = $exc->getMessage();
                    return;
                }

                $this->objTemplateContent->step = $step + 1;
                $this->objTemplateContent->file = $strZipName;
                $this->objTemplateContent->condition = array('1' => OK, '2' => OK, '3' => OK);
                $this->objTemplateContent->refresh = false;
                return;

            default:
                $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['unknown_backup_step']);
                return;
        }

        $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['unknown_backup_error']);
    }

    /**
     * Datenbank wiederherstellen
     *
     * @return <type>
     */
    protected function parseDbRestorePage()
    {
        $this->loadLanguageFile('tl_syncCto_restore_db');

        if ($this->Input->get("step") == "" || $this->Input->get("step") == null)
        {
            $step = 1;
        }
        else
        {
            $step = intval($this->Input->get("step"));
        }

        $this->objTemplateContent = new BackendTemplate('be_syncCto_restore_db');

        switch ($step)
        {
            case 1:
                if ($this->Input->post("filelist") == "")
                {
                    $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['no_backup_file']);
                    return;
                }

                $this->Session->set("SyncCto_Restore", $this->Input->post("filelist"));

                $this->objTemplateContent->step = $step;
                $this->objTemplateContent->condition = array('1' => WORK);
                $this->objTemplateContent->refresh = true;

                return;

            case 2:
                $strRestoreFile = $this->Session->get("SyncCto_Restore");
                if ($strRestoreFile == "" || $strRestoreFile == null)
                {
                    $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['session_file_error']);
                    return;
                }


                if (!file_exists(TL_ROOT . "/" . $strRestoreFile))
                {
                    $this->objTemplateContent->step = $step - 1;
                    $this->objTemplateContent->condition = array('1' => ERROR);
                    $this->objTemplateContent->refresh = false;
                    $this->objTemplateContent->error = "Datei nicht gefunden.";
                    return;
                }

                $this->objTemplateContent->step = $step;
                $this->objTemplateContent->condition = array('1' => OK, '2' => WORK);
                $this->objTemplateContent->refresh = true;
                return;

            case 3:
                $strRestoreFile = $this->Session->get("SyncCto_Restore");
                if ($strRestoreFile == "" || $strRestoreFile == null)
                {
                    $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['session_file_error']);
                    return;
                }

                try
                {
                    $this->objSyncCtoDatabase->runCheckZip($strRestoreFile);
                }
                catch (Exception $exc)
                {
                    $this->objTemplateContent->step = $step - 1;
                    $this->objTemplateContent->condition = array('1' => OK, '2' => ERROR);
                    $this->objTemplateContent->refresh = false;
                    $this->objTemplateContent->error = $exc->getMessage();
                    return;
                }

                $this->objTemplateContent->step = $step;
                $this->objTemplateContent->condition = array('1' => OK, '2' => OK, '3' => WORK);
                $this->objTemplateContent->refresh = true;
                return;

            case 4:
                $strRestoreFile = $this->Session->get("SyncCto_Restore");
                if ($strRestoreFile == "" || $strRestoreFile == null)
                {
                    $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['session_file_error']);
                    return;
                }

                try
                {
                    $strZipId = $this->objSyncCtoDatabase->runRestore($strRestoreFile);
                }
                catch (Exception $exc)
                {
                    $this->objTemplateContent->step = $step - 1;
                    $this->objTemplateContent->condition = array('1' => OK, '2' => OK, '3' => ERROR);
                    $this->objTemplateContent->refresh = false;
                    $this->objTemplateContent->error = $exc->getMessage();
                    return;
                }

                $this->objTemplateContent->step = $step + 1;
                $this->objTemplateContent->file = $strZipName;
                $this->objTemplateContent->condition = array('1' => OK, '2' => OK, '3' => OK);
                $this->objTemplateContent->refresh = false;
                return;

            default:
                $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['unknown_backup_step']);
                return;
        }

        $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['unknown_restore_error']);
    }

    protected function parseFileBackupPage()
    {
        // Init
        $this->loadLanguageFile('tl_syncCto_backup_file');
        $this->objTemplateContent = new BackendTemplate('be_syncCto_backup_file');

        // Check Step
        if ($this->Input->get("step") == "" || $this->Input->get("step") == null)
        {
            $step = 1;
        }
        else
        {
            $step = intval($this->Input->get("step"));
        }

        // Load data
        if ($step == 1)
        {            
            
            // Check sync. typ
            if (strlen($this->Input->post('backupType')) != 0)
            {
                if ($this->Input->post('backupType') == SYNCCTO_FULL || $this->Input->post('backupType') == SYNCCTO_SMALL)
                {
                    $intBackupTyp = $this->Input->post('backupType');
                    $this->Session->set("syncCto_Typ", $this->Input->post('syncType'));
                }
                else
                {
                    $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['unknown_method']);
                    return;
                }
            }
            else
            {
                $intBackupTyp = SYNCCTO_SMALL;
                $this->Session->set("syncCto_Typ", SYNCCTO_SMALL);
            }

            $strBackupName = $this->Input->post('backupName');
            $arrFilelist = $this->Input->post('filelist');
            $arrCondition = array();

            $this->Session->set("SyncCto_Typ", $intBackupTyp);
            $this->Session->set("SyncCto_Name", $strBackupName);
            $this->Session->set("SyncCto_Filelist", serialize($arrFilelist));
            $this->Session->set("SyncCto_Condition", serialize($arrCondition));
        }
        else
        {
            $intBackupTyp = $this->Session->get("SyncCto_Typ");
            $strBackupName = $this->Session->get("SyncCto_Name");
            $arrFilelist = deserialize($this->Session->get("SyncCto_Filelist"));

            if ($arrFilelist == "s:0:\"\";")
                $arrFilelist = array();

            $arrCondition = deserialize($this->Session->get("SyncCto_Condition"));
            $strZipName = $this->Session->get("SyncCto_ZipName");
        }



        if ($arrFilelist == "" && $intBackupTyp == SYNCCTO_SMALL)
        {
            $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['no_backup_file']);
            return;
        }

        if ($arrCondition == "" && !is_array($arrCondition))
        {
            $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['unknown_error']);
            return;
        }

        if ($strZipName == "" && ($step == 3 || $step == 4))
        {
            $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['restore_session_zip_name']);
            return;
        }


        // Do function by step
        switch ($step)
        {
            case 1:
                $this->objTemplateContent->step = $step;
                $this->objTemplateContent->condition = $this->saveCondition($arrCondition, array('1' => WORK));
                $this->objTemplateContent->refresh = true;

                return;

            case 2:
                try
                {
                    $arrZip = $this->objSyncCtoFiles->runCreateZip($strBackupName);
                    
                    $this->Session->set("SyncCto_ZipId", $arrZip['id']);
                    $this->Session->set("SyncCto_ZipName", $arrZip['name']);
                }
                catch (Exception $exc)
                {
                    $this->objTemplateContent->step = $step - 1;
                    $this->objTemplateContent->condition = $this->saveCondition($arrCondition, array('1' => ERROR));
                    $this->objTemplateContent->refresh = false;
                    $this->objTemplateContent->error = $exc->getMessage();
                    return;
                }

                $this->objTemplateContent->step = $step;
                $this->objTemplateContent->condition = $this->saveCondition($arrCondition, array('1' => OK, '2' => WORK));
                $this->objTemplateContent->refresh = true;

                return;

            case 3:
                if ($intBackupTyp == SYNCCTO_SMALL)
                {
                    $this->objTemplateContent->step = $step;
                    $this->objTemplateContent->condition = $this->saveCondition($arrCondition, array('2' => SKIPPED, '3' => WORK));
                    $this->objTemplateContent->refresh = true;
                    return;
                }
                else
                {
                    try
                    {
                        $this->objSyncCtoFiles->runCoreFilesDump($strZipName);

                        $this->objTemplateContent->step = $step;
                        $this->objTemplateContent->condition = $this->saveCondition($arrCondition, array('2' => OK, '3' => WORK));
                        $this->objTemplateContent->refresh = true;
                        return;
                    }
                    catch (Exception $exc)
                    {
                        $this->objTemplateContent->step = $step - 1;
                        $this->objTemplateContent->condition = $this->saveCondition($arrCondition, array('2' => ERROR));
                        $this->objTemplateContent->refresh = false;
                        $this->objTemplateContent->error = $exc->getMessage();
                        return;
                    }
                }

                return;

            case 4:
                if (count($arrFilelist) == 0)
                {
                    $this->objTemplateContent->step = $step + 1;
                    $this->objTemplateContent->condition = $this->saveCondition($arrCondition, array('3' => SKIPPED));
                    $this->objTemplateContent->file = $strZipName;
                    $this->objTemplateContent->refresh = false;
                    return;
                }
                else
                {

                    try
                    {   
                        $this->objSyncCtoFiles->runTlFilesDump($strZipName, $arrFilelist);
                    }
                    catch (Exception $exc)
                    {
                        $this->objTemplateContent->step = $step - 1;
                        $this->objTemplateContent->condition = $this->saveCondition($arrCondition, array('3' => ERROR));
                        $this->objTemplateContent->refresh = false;
                        $this->objTemplateContent->error = $exc->getMessage();
                        return;
                    }

                    $this->objTemplateContent->step = $step + 1;
                    $this->objTemplateContent->file = $strZipName;
                    $this->objTemplateContent->condition = $this->saveCondition($arrCondition, array('3' => OK));
                    $this->objTemplateContent->refresh = false;
                    return;
                }

                return;

            default:
                $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['unknown_backup_step']);
                return;
        }
    }

    /**
     * Datenbank wiederherstellen
     *
     * @return <type>
     */
    protected function parseFileRestorePage()
    {
        $this->loadLanguageFile('tl_syncCto_restore_file');

        if ($this->Input->get("step") == "" || $this->Input->get("step") == null)
        {
            $step = 1;
        }
        else
        {
            $step = intval($this->Input->get("step"));
        }

        $this->objTemplateContent = new BackendTemplate('be_syncCto_restore_file');

        switch ($step)
        {
            case 1:
                if ($this->Input->post("filelist") == "")
                {
                    $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['no_backup_file']);
                    return;
                }

                $this->Session->set("SyncCto_Restore", $this->Input->post("filelist"));

                $this->objTemplateContent->step = $step;
                $this->objTemplateContent->condition = array('1' => WORK);
                $this->objTemplateContent->refresh = true;

                return;

            case 2:
                $strRestoreFile = $this->Session->get("SyncCto_Restore");
                if ($strRestoreFile == "" || $strRestoreFile == null)
                {
                    $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['session_file_error']);
                    return;
                }

                try
                {
                    $this->objSyncCtoFiles->runRestore($strRestoreFile);
                }
                catch (Exception $exc)
                {
                    $this->objTemplateContent->step = $step - 1;
                    $this->objTemplateContent->condition = array('1' => ERROR);
                    $this->objTemplateContent->refresh = false;
                    $this->objTemplateContent->error = $exc->getMessage();
                    return;
                }

                $this->objTemplateContent->step = $step + 1;
                $this->objTemplateContent->condition = array('1' => OK);
                $this->objTemplateContent->refresh = false;
                $this->objTemplateContent->file = $strRestoreFile;
                return;

            default:
                $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['unknown_backup_step']);
                return;
        }

        $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['unknown_restore_error']);
    }

    private function saveCondition($arrCondition, $arrNewCondition)
    {
        foreach ($arrNewCondition as $key => $value)
        {
            $arrCondition[$key] = $value;
        }
        $this->Session->set("SyncCto_Condition", serialize($arrCondition));
        return $arrCondition;
    }

}

?>