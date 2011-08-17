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
// Include Simple HTML Dom
include_once 'SyncCtoSimpleHtmlDom.php';

/**
 * Cllback Class for SyncCto
 */
class SyncCtoCallback extends Backend
{

    //Variablen    
    protected $objSyncCtoHelper;
    protected $objSyncCtoCodifyengine;
    //-----
    protected $BackendUser;
    //-----
    protected static $instance = null;

    // Constructer and singelten pattern
    public function __construct()
    {
        // Import Contao classes
        $this->BackendUser = BackendUser::getInstance();

        parent::__construct();

        // Import SyncCto classes
        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();
        $this->objSyncCtoCodifyengine = SyncCtoCodifyengineFactory::getEngine();

        $this->loadLanguageFile("SyncCto");
    }

    /**
     *
     * @return SyncCtoCallback
     */
    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new SyncCtoCallback();

        return self::$instance;
    }

    /* -------------------------------------------------------------------------
     * Magical functions
     */

    /**
     * User for premissioncheck from operation callbacks.
     * 
     * @param string $name Name of function
     * @param array $arguments Arguments
     * @return mixed 
     */
    public function __call($name, $arguments)
    {
        $arrSplitName = explode("_", $name);

        //checkPermission_clients_edit
        if ($arrSplitName[0] == 'checkPermission' && $arrSplitName[1] == "client")
        {
            return $this->checkPermissionClientButton($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4], $arguments[5], $arrSplitName[2]);
        }
    }

    /* -------------------------------------------------------------------------
     * Permisson check for the Client overview page.
     * 
     * Typ : DCA Callback
     * Class : tl_syncCto_clients
     * Path : system/modules/syncCto/dca/tl_syncCto_clients.php
     */

    /**
     * @TODO - Doku schreiben
     * 
     * @param type $row
     * @param type $href
     * @param type $label
     * @param type $title
     * @param type $icon
     * @param type $attributes
     * @param type $operations
     * @return type 
     */
    public function checkPermissionClientButton($row, $href, $label, $title, $icon, $attributes, $operations)
    {
        if ($this->BackendUser->hasAccess($operations, 'syncCto_clients_p') == true)
        {
            return '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $this->generateImage($icon, $label) . '</a> ';
        }
        else
        {
            switch ($operations)
            {
                case 'syncTo' :
                case 'syncFrom' :
                    return $this->generateImage(preg_replace('/\.png$/i', '_.png', $icon)) . ' ';
                    break;

                default:
                    return $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
                    break;
            }
        }
    }

    /**
     * Check permissions to edit table tl_content
     */
    public function checkPermissionClient()
    {
        if ($this->BackendUser->isAdmin)
        {
            return;
        }

        // Set root IDs
        if (!is_array($this->BackendUser->syncCto_clients) || count($this->BackendUser->syncCto_clients) < 1)
        {
            $root = array(0);
        }
        else
        {
            $root = $this->BackendUser->syncCto_clients;
        }

        $GLOBALS['TL_DCA']['tl_synccto_clients']['list']['sorting']['root'] = $root;

        if ($this->BackendUser->hasAccess($this->Input->get('act'), 'syncCto_clients_p') == true || strlen($this->Input->get('act')) == 0)
        {
            return;
        }
        else
        {
            $this->log('Not enough permissions to ' . $this->Input->get('act') . ' syncCto clients', 'tl_syncCto_clients checkPermissionClient', TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }
    }

    public function checkPermissionClientCreate()
    {
        if (!$this->BackendUser->hasAccess('create', 'syncCto_clients_p'))
            $GLOBALS['TL_DCA']['tl_synccto_clients']['config'] = array_unique(array_merge(array('closed' => true), $GLOBALS['TL_DCA']['tl_synccto_clients']['config']));
    }

    public function checkPermissionFiletree()
    {
        if ($this->BackendUser->isAdmin)
        {
            return;
        }

        $GLOBALS['TL_DCA']['tl_syncCto_clients_syncTo']['list']['sorting']['root'] = $this->BackendUser->filemounts;
    }

    /* -------------------------------------------------------------------------
     * Generate the sec key for server
     * 
     * Typ : DCA Save Callback
     * Class : tl_syncCto_settings
     * Path : system/modules/syncCto/dca/tl_syncCto_settings.php
     */

    public function savecallSecKey($varValue, DataContainer $dca)
    {
        if ($varValue == "")
        {
            $objKey = $this->Database->prepare("SELECT UUID() as uid")->execute();
            return $objKey->uid;
        }

        return $varValue;
    }

    /* -------------------------------------------------------------------------
     * Encrypt the user password for the client
     * 
     * Typ : DCA Save Callback
     * Class : tl_synccto_clients
     * Path : system/modules/syncCto/dca/tl_synccto_clients.php
     */

    public function savecallUserPassword($varValue, DataContainer $dca)
    {
        if ($varValue != "")
        {
            $this->objSyncCtoCodifyengine->setKey($GLOBALS['TL_CONFIG']['encryptionKey']);
            $strEncrypt = $this->objSyncCtoCodifyengine->Encrypt($varValue);
            $this->objSyncCtoCodifyengine->resetKey();

            return $strEncrypt;
        }

        return $varValue;
    }

    /* -------------------------------------------------------------------------
     * Load call backs for syncCto settings
     * 
     * Typ : DCA Load Callback
     * Class : tl_syncCto_settings
     * Path : system/modules/syncCto/dca/tl_syncCto_settings.php
     */

    public function loadcallLocalConfig($strValue)
    {
        return $this->objSyncCtoHelper->getBlacklistLocalconfig();
    }

    public function loadcallFolderBlacklist($strValue)
    {
        return $this->objSyncCtoHelper->getBlacklistFolder();
    }

    public function loadcallFileBlacklist($strValue)
    {
        return $this->objSyncCtoHelper->getBlacklistFile();
    }

    public function loadcallFolderWhitelist($strValue)
    {
        return $this->objSyncCtoHelper->getWhitelistFolder();
    }

    public function loadcallTableHidden($strValue)
    {
        return $this->objSyncCtoHelper->getTablesHidden();
    }

    /* -------------------------------------------------------------------------
     * Loadcallback for decrypt of the userpassword from the client
     * 
     * Typ : DCA Load Callback
     * Class : tl_synccto_clients
     * Path : system/modules/syncCto/dca/tl_synccto_clients.php
     */

    public function loadcallUserPassword($strValue)
    {
        $this->objSyncCtoCodifyengine->setKey($GLOBALS['TL_CONFIG']['encryptionKey']);
        $strEncrypt = $this->objSyncCtoCodifyengine->Decrypt($strValue);
        $this->objSyncCtoCodifyengine->resetKey();

        return $strEncrypt;
    }

    /* -------------------------------------------------------------------------
     * Return all sync types as array
     * 
     * Typ : DCA Options Callback
     * Class : tl_syncFrom/syncTo
     * Path : system/modules/syncCto/dca/tl_syncFrom/syncTo.php
     */

    public function getSyncType()
    {
        $groups = array();

        foreach ($GLOBALS['TL_SYC'] as $key => $value)
        {
            foreach ($value as $key2 => $value2)
            {
                $groups[$key][$value2] = $key2;
            }
        }

        return $groups;
    }

    /* -------------------------------------------------------------------------
     * Load options for list
     * 
     * Typ : DCA Option Callback
     * Class : tl_syncCto_settings
     * Path : system/modules/syncCto/dca/tl_syncCto_settings.php
     */

    public function optioncallLocalConfig()
    {
        // Read the local configuration file
        $strMode = 'top';
        $resFile = fopen(TL_ROOT . '/system/config/localconfig.php', 'rb');

        $arrData = array();

        while (!feof($resFile))
        {
            $strLine = fgets($resFile);
            $strTrim = trim($strLine);

            if ($strTrim == '?>')
            {
                continue;
            }

            if ($strTrim == '### INSTALL SCRIPT START ###')
            {
                $strMode = 'data';
                continue;
            }

            if ($strTrim == '### INSTALL SCRIPT STOP ###')
            {
                $strMode = 'bottom';
                continue;
            }

            if ($strMode == 'top')
            {
                $this->strTop .= $strLine;
            }
            elseif ($strMode == 'bottom')
            {
                $this->strBottom .= $strLine;
            }
            elseif ($strTrim != '')
            {
                $arrChunks = array_map('trim', explode('=', $strLine, 2));


                $arrData[] = str_replace(array("$", "GLOBALS['TL_CONFIG']['", "']"), array("", "", ""), $arrChunks[0]);
            }
        }

        fclose($resFile);

        return $arrData;
    }

    //Function for recommend and not recommend tables

    /**
     * Returns a whole list of all tables in the database
     * @return array 
     */
    public function optioncallHiddenTables()
    {
        $arrTables = array();

        foreach ($this->Database->listTables() as $key => $value)
        {
            $arrTables[] = $value;
        }

        return $arrTables;
    }

    /**
     * Returns a list without the hidden tables.
     * 
     * @return array 
     */
    public function optioncallTables()
    {
        $arrTables = array();
        $arrTablesHidden = $this->objSyncCtoHelper->getTablesHidden();

        foreach ($this->Database->listTables() as $key => $value)
        {
            // Check if table is a hidden one.
            if (in_array($value, $arrTablesHidden))
                continue;

            $arrTables[] = $value;
        }

        return $arrTables;
    }

    public function optioncallTablesRecommend()
    {
        // SyncCto Blacklist
        $arrBlacklist = deserialize($GLOBALS['TL_CONFIG']['syncCto_table_list']);
        if (!is_array($arrBlacklist))
        {
            $arrBlacklist = array();
        }

        $arrTablesPermission = $this->BackendUser->syncCto_tables;

        $arrTables = array();

        foreach ($this->optioncallTables() as $key => $value)
        {
            if (in_array($value, $arrBlacklist))
                continue;

            if (is_array($arrTablesPermission) && !in_array($value, $arrTablesPermission) && $this->BackendUser->isAdmin != true)
                continue;

            $arrTables[] = $value;
        }

        return $arrTables;
    }

    public function optioncallTablesNoneRecommend()
    {
        $arrBlacklist = deserialize($GLOBALS['TL_CONFIG']['syncCto_table_list']);
        if (!is_array($arrBlacklist))
        {
            $arrBlacklist = array();
        }

        $arrTablesPermission = $this->BackendUser->syncCto_tables;

        $arrTables = array();

        foreach ($this->optioncallTables() as $key => $value)
        {
            if (!in_array($value, $arrBlacklist))
                continue;

            if (is_array($arrTablesPermission) && !in_array($value, $arrTablesPermission) && $this->BackendUser->isAdmin != true)
                continue;

            $arrTables[] = $value;
        }

        return $arrTables;
    }

    public function optioncallCodifyengines()
    {
        $arrReturn = array();

        foreach ($GLOBALS["syncCto"]["codifyengine"] as $key => $value)
        {
            $arrReturn[$key] = $value["name"];
        }
        
        asort($arrReturn);

        return $arrReturn;
    }

    /* -------------------------------------------------------------------------
     * Hook Callback Backenend Output
     */

    /**
     * Change submit mask from dca to fit oure idears.
     *
     * @param string $strContent HTML for the page
     * @param string $strTemplate Template name
     * @return string
     */
    function outputBackendTemplate($strContent, $strTemplate)
    {
        /*
          if (( $this->Input->get("do") == "group" && $this->Input->get("act") == 'edit' ) || ( $this->Input->get("do") == "user" && $this->Input->get("act") == 'edit' ))
          {

          $objDom = SyncCtoSimpleHtmlDomHelper::str_get_dom($strContent);

          // Images after checkboxes hidden
          $arrImg = $objDom->find('div#ctrl_syncCto_tables img.tl_checkbox_wizard_img');
          foreach ($arrImg as $value)
          {
          $value->class = "hidden";
          }

          // Extra class for span
          $arrSpan = $objDom->find('div#ctrl_syncCto_tables span');
          foreach ($arrSpan as $value)
          {
          $value->class = "syncCto";
          }

          return $objDom->__toString();
          }
         */

        if (($this->Input->get("do") == "syncCto_backups" || $this->Input->get("do") == "synccto_clients") && strlen($this->Input->get("act")) == 0 && strlen($this->Input->get("table")) != 0)
        {
            $objDom = SyncCtoSimpleHtmlDomHelper::str_get_dom($strContent);

            switch ($this->Input->get("table"))
            {
                case "tl_syncCto_backup_db":
                    // First input new name, second input hidden
                    $objDom->find('input#saveNclose', 0)->class = "none hidden";
                    $objDom->find('input#save', 0)->value = $GLOBALS['TL_LANG']['syncCto']['start_backup'];
                    $objDom->find('input#save', 0)->name = "BackupStart";

                    // Images after checkboxes hidden
                    $arrImg = $objDom->find('a[onclick*=Backend.checkboxWizard]');
                    foreach ($arrImg as $value)
                    {
                        $value->class = "none";
                    }

                    // Extra class for span
                    $arrSpan = $objDom->find('div.tl_checkbox_container span');
                    foreach ($arrSpan as $value)
                    {
                        $value->class = "syncCto";
                    }

                    // Form new link
                    $objDom->find('form#tl_syncCto_backup_db', 0)->action = $this->Environment->script . "?do=syncCto_backups&amp;table=tl_syncCto_backup_db&amp;act=start";
                    break;

                case "tl_syncCto_backup_file":
                    // First input new name, second input hidden
                    $objDom->find('input#saveNclose', 0)->class = "none hidden";
                    $objDom->find('input#save', 0)->value = $GLOBALS['TL_LANG']['syncCto']['start_backup'];
                    $objDom->find('input#save', 0)->name = "BackupStart";

                    // Images after checkboxes hidden
                    $arrImg = $objDom->find('a[onclick*=Backend.checkboxWizard]');
                    foreach ($arrImg as $value)
                    {
                        $value->class = "none";
                    }

                    // Form new link
                    $objDom->find('form#tl_syncCto_backup_file', 0)->action = $this->Environment->script . "?do=syncCto_backups&amp;table=tl_syncCto_backup_file&amp;act=start";
                    break;

                case "tl_syncCto_restore_db":
                    // First input new name, second input hidden
                    $objDom->find('input#saveNclose', 0)->class = "none hidden";
                    $objDom->find('input#save', 0)->value = $GLOBALS['TL_LANG']['syncCto']['restore_backup'];
                    $objDom->find('input#save', 0)->name = "BackupRestore";

                    // Form new link
                    $objDom->find('form#tl_syncCto_restore_db', 0)->action = $this->Environment->script . "?do=syncCto_backups&amp;table=tl_syncCto_restore_db&amp;act=start";
                    break;

                case "tl_syncCto_restore_file":
                    // First input new name, second input hidden
                    $objDom->find('input#saveNclose', 0)->class = "none hidden";
                    $objDom->find('input#save', 0)->value = $GLOBALS['TL_LANG']['syncCto']['restore_backup'];
                    $objDom->find('input#save', 0)->name = "BackupRestore";

                    // Form new link
                    $objDom->find('form#tl_syncCto_restore_file', 0)->action = $this->Environment->script . "?do=syncCto_backups&amp;table=tl_syncCto_restore_file&amp;act=start";
                    break;

                case "tl_syncCto_clients_syncTo":
                    // First input new name, second input hidden
                    $objDom->find('input#saveNclose', 0)->class = "none hidden";
                    $objDom->find('input#save', 0)->value = $GLOBALS['TL_LANG']['syncCto']['sync_client'];
                    $objDom->find('input#save', 0)->name = "SyncStart";

                    // Images after checkboxes hidden
                    $arrImg = $objDom->find('a[onclick*=Backend.checkboxWizard]');
                    foreach ($arrImg as $value)
                    {
                        $value->class = "none";
                    }

                    // Extra class for span
                    $arrSpan = $objDom->find('div.tl_checkbox_container span');
                    foreach ($arrSpan as $value)
                    {
                        $value->class = "syncCto";
                    }

                    // Form new link
                    $objDom->find('form#tl_syncCto_clients_syncTo', 0)->action = $this->Environment->script . "?do=synccto_clients&amp;table=tl_syncCto_clients_syncTo&amp;act=start&amp;id=" . $this->Input->get("id");

                    // Last sync with time and user information
                    $objSyncTime = $this->Database->prepare("SELECT cl.syncTo_tstamp as syncTo_tstamp, user.name as syncTo_user, user.username as syncTo_alias
                                            FROM tl_synccto_clients as cl 
                                            INNER JOIN tl_user as user
                                            ON cl.syncTo_user = user.id
                                            WHERE cl.id = ?")
                            ->limit(1)
                            ->execute($this->Input->get("id"));

                    if ($objSyncTime->syncTo_tstamp != "" && $objSyncTime->syncTo_user != "" && $objSyncTime->syncTo_tstamp != '0' && $objSyncTime->syncTo_user != '0')
                    {
                        $objInformation = $objDom->find("div.tl_formbody_edit", 0);
                        $objInformation->innertext = vsprintf('<p class="tl_info">' . $GLOBALS['TL_LANG']['syncCto']['information_last_sync'] . '</p> %s', array(
                            date($GLOBALS['TL_CONFIG']['timeFormat'], $objSyncTime->syncTo_tstamp),
                            date($GLOBALS['TL_CONFIG']['dateFormat'], $objSyncTime->syncTo_tstamp),
                            $objSyncTime->syncTo_user,
                            $objSyncTime->syncTo_alias,
                            $objInformation->innertext)
                        );
                    }

                    break;

                case "tl_syncCto_clients_syncFrom":
                    // First input new name, second input hidden
                    $objDom->find('input#saveNclose', 0)->class = "none";
                    $objDom->find('input#save', 0)->value = $GLOBALS['TL_LANG']['syncCto']['sync_server'];
                    $objDom->find('input#save', 0)->name = "SyncStart";

                    // Form new link
                    $objDom->find('form#tl_syncCto_clients_syncFrom', 0)->action = $this->Environment->script . "?do=synccto_clients&amp;table=tl_syncCto_clients_syncFrom&amp;act=start&amp;id=" . $this->Input->get("id");

                    // Last sync with time and user information
                    $objSyncTime = $this->Database->prepare("SELECT cl.syncFrom_tstamp as syncFrom_tstamp, user.name as syncFrom_user
                                            FROM tl_synccto_clients as cl 
                                            INNER JOIN tl_user as user
                                            ON cl.syncFrom_user = user.id
                                            WHERE cl.id = ?")
                            ->limit(1)
                            ->execute($this->Input->get("id"));

                    if ($objSyncTime->syncFrom_tstamp != "" && $objSyncTime->syncFrom_user != "" && $objSyncTime->syncFrom_tstamp != '0' && $objSyncTime->syncFrom_user != '0')
                    {
                        $objInformation = $objDom->find("div.tl_formbody_edit", 0);
                        $objInformation->innertext = vsprintf('<p class="tl_info">' . $GLOBALS['TL_LANG']['syncCto']['information_last_sync'] . '</p> %s', array(
                            date($GLOBALS['TL_CONFIG']['timeFormat'], $objSyncTime->syncFrom_tstamp),
                            date($GLOBALS['TL_CONFIG']['dateFormat'], $objSyncTime->syncFrom_tstamp),
                            $objSyncTime->syncFrom_user,
                            $objSyncTime->syncTo_alias,
                            $objInformation->innertext)
                        );
                    }
                    break;

                default:
                    return $strContent;
                    break;
            }

            return $objDom->__toString();
        }

        return $strContent;
    }

}

?>