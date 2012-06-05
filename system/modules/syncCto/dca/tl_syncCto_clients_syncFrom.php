<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
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
 * @license    GNU/LGPL
 * @filesource
 */
$GLOBALS['TL_DCA']['tl_syncCto_clients_syncFrom'] = array(
    // Config
    'config' => array(
        'dataContainer' => 'Memory',
        'closed' => true,
        'disableSubmit' => false,
        'onload_callback' => array(
            array('tl_syncCto_clients_syncFrom', 'onload_callback')
        ),
        'onsubmit_callback' => array(
            array('tl_syncCto_clients_syncFrom', 'onsubmit_callback'),
        )
    ),
    // Palettes
    'palettes' => array(
        '__selector__' => array('database_check', 'systemoperations_check'),
        'default' => '{sync_legend},lastSync,disabledCache,sync_options;{table_legend},database_check;{systemoperations_legend},systemoperations_check,attentionFlag;',
    ),
    // Sub Palettes
    'subpalettes' => array(
        'database_check' => 'database_tables_recommended,database_tables_none_recommended',
        'systemoperations_check' => 'systemoperations_maintenance',
    ),
    // Fields
    'fields' => array(
        'lastSync' => array
            (
            'label' => " ",
            'inputType' => 'statictext',
        ),
        'disabledCache' => array
            (
            'label' => " ",
            'inputType' => 'statictext',
        ),        
        'sync_options' => array(
            'label' => $GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['sync_options'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'reference' => &$GLOBALS['TL_LANG']['SYC'],
            'options_callback' => array('SyncCtoHelper', 'getFileSyncOptions'),
            'eval' => array(
                'multiple' => true
            ),
        ),
        'database_check' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['database_check'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'eval' => array(
                'submitOnChange' => true,
                'tl_class' => 'clr',
            ),
        ),
        'database_tables_recommended' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['database_tables_recommended'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'eval' => array(
                'multiple' => true
            ),
            'options_callback' => array('tl_syncCto_clients_syncFrom', 'databaseTablesRecommended'),
            'reference' => &$GLOBALS['TL_LANG']['SYC'],
            'explanation' => 'syncCto_database',
        ),
        'database_tables_none_recommended' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['database_tables_none_recommended'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'eval' => array(
                'multiple' => true
            ),
            'options_callback' => array('tl_syncCto_clients_syncFrom', 'databaseTablesNoneRecommended'),
            'reference' => &$GLOBALS['TL_LANG']['SYC'],
            'explanation' => 'syncCto_database',
        ),
        'systemoperations_check' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['systemoperations_check'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'eval' => array(
                'submitOnChange' => true,
                'tl_class' => 'clr'
            ),
        ),
        'systemoperations_maintenance' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['systemoperations_maintenance'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'reference' => &$GLOBALS['TL_LANG']['SYC'],
            'eval' => array(
                'multiple' => true,
                'checkAll' => true
            ),
            'options_callback' => array('SyncCtoHelper', 'getMaintanceOptions'),
        ),
        'attentionFlag' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['attention_flag'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'eval' => array(
                'multiple' => false
            ),
        ),
    )
);

/**
 * Class for syncFrom configurations
 */
class tl_syncCto_clients_syncFrom extends Backend
{

    /**
     *
     * @var SyncCtoCommunicationClient 
     */
    protected $objSyncCtoCommunicationClient = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->BackendUser = BackendUser::getInstance();

        parent::__construct();
    }

    public function __destruct()
    {
        $this->stopConnection();
    }

    /**
     * Set new and remove old buttons
     * 
     * @param DataContainer $dc 
     */
    public function onload_callback(DataContainer $dc)
    {
        $strInitFilePath = '/system/config/initconfig.php';

        if(file_exists(TL_ROOT . $strInitFilePath))
        {
            $strFile = new File($strInitFilePath);
            $arrFileContent = $strFile->getContentAsArray();
            $booLocated = FALSE;
            foreach($arrFileContent AS $strContent)
            {
                if(!preg_match("/(\/\*|\*|\*\/|\/\/)/", $strContent))
                {                                
                    //system/tmp
                    if(preg_match("/system\/tmp/", $strContent))
                    {                            
                        // Set data
                        $dc->setData("disabledCache", "<p class='tl_info'>" . $GLOBALS['TL_LANG']['MSC']['disabled_cache'] . "</p>");                                    
                        $booLocated = TRUE;
                    }
                }
            }
            
            if(!$booLocated)
            {
                $GLOBALS['TL_DCA']['tl_syncCto_clients_syncFrom']['palettes']['default'] = str_replace(",disabledCache", "", $GLOBALS['TL_DCA']['tl_syncCto_clients_syncFrom']['palettes']['default']);
            }
        }
        else
        {
            $GLOBALS['TL_DCA']['tl_syncCto_clients_syncFrom']['palettes']['default'] = str_replace(",disabledCache", "", $GLOBALS['TL_DCA']['tl_syncCto_clients_syncFrom']['palettes']['default']);
        }
        
        $dc->removeButton('save');
        $dc->removeButton('saveNclose');

        $arrData = array
            (
            'id' => 'start_sync',
            'formkey' => 'start_sync',
            'class' => '',
            'accesskey' => 'g',
            'value' => specialchars($GLOBALS['TL_LANG']['MSC']['syncFrom']),
            'button_callback' => array('tl_syncCto_clients_syncFrom', 'onsubmit_callback')
        );

        $dc->addButton('start_sync', $arrData);

        // Update a field with last sync information
        $objSyncTime = $this->Database->prepare("SELECT cl.syncFrom_tstamp as syncFrom_tstamp, user.name as syncFrom_user, user.username as syncFrom_alias
                                            FROM tl_synccto_clients as cl 
                                            INNER JOIN tl_user as user
                                            ON cl.syncTo_user = user.id
                                            WHERE cl.id = ?")
                ->limit(1)
                ->execute($this->Input->get("id"));

        if ($objSyncTime->syncFrom_tstamp != 0 && strlen($objSyncTime->syncFrom_user) != 0 && strlen($objSyncTime->syncFrom_alias) != 0)
        {
            $strLastSync = vsprintf($GLOBALS['TL_LANG']['MSC']['last_sync'], array(
                date($GLOBALS['TL_CONFIG']['timeFormat'], $objSyncTime->syncFrom_tstamp),
                date($GLOBALS['TL_CONFIG']['dateFormat'], $objSyncTime->syncFrom_tstamp),
                $objSyncTime->syncFrom_user,
                $objSyncTime->syncFrom_alias)
            );

            // Set data
            $dc->setData("lastSync", "<p class='tl_info'>" . $strLastSync . "</p>");
        }
        else
        {
            $GLOBALS['TL_DCA']['tl_syncCto_clients_syncFrom']['palettes']['default'] = str_replace(",lastSync", "", $GLOBALS['TL_DCA']['tl_syncCto_clients_syncFrom']['palettes']['default']);
        }
    }

    public function startConnection()
    {
        if ($this->objSyncCtoCommunicationClient == null)
        {
            // Build communication class
            $this->objSyncCtoCommunicationClient = SyncCtoCommunicationClient::getInstance();
            $this->objSyncCtoCommunicationClient->setClientBy($this->Input->get("id"));

            // Start connection
            $this->objSyncCtoCommunicationClient->startConnection();
        }
        else
        {
            return;
        }
    }

    public function stopConnection()
    {
        if ($this->objSyncCtoCommunicationClient != null)
        {
            try
            {
                // Stop connection
                $this->objSyncCtoCommunicationClient->stopConnection();
            }
            catch (Exception $exc)
            {
                // Do nothing
            }
        }
    }

    public function databaseTablesNoneRecommended()
    {
        try
        {
            // Check connection
            $this->startConnection();

            // Get Tables
            $arrTablesClient = $this->objSyncCtoCommunicationClient->getNoneRecommendedTables();

            // Check if we have a array 
            if (!is_array($arrTablesClient))
            {
                $arrTablesClient = array();
            }

            return $arrTablesClient;
        }
        catch (Exception $exc)
        {
            $_SESSION["TL_ERROR"][] = $exc->getMessage();
            return array();
        }
    }

    public function databaseTablesRecommended()
    {
        try
        {
            // Check connection
            $this->startConnection();

            // Get Tables
            $arrTablesClient = $this->objSyncCtoCommunicationClient->getRecommendedTables();

            // Check if we have a array 
            if (!is_array($arrTablesClient))
            {
                $arrTablesClient = array();
            }

            return $arrTablesClient;
        }
        catch (Exception $exc)
        {
            $_SESSION["TL_ERROR"][] = $exc->getMessage();
            return array();
        }
    }

    /**
     * Handle syncFrom configurations
     * 
     * @param DataContainer $dc
     * @return array 
     */
    public function onsubmit_callback(DataContainer $dc)
    {
        $arrSyncSettings = array();
        
        // Synchronization type
        if (is_array($this->Input->post("sync_options")) && count($this->Input->post("sync_options")) != 0)
        {
            $arrSyncSettings["syncCto_Type"] = $this->Input->post('sync_options');
        }
        else
        {
            $arrSyncSettings["syncCto_Type"] = array();
        }

        // Synchronization for database
        if ($this->Input->post("database_check") == 1)
        {
            $arrTables = array();

            if (is_array($this->Input->post("database_tables_recommended")) && count($this->Input->post("database_tables_recommended")) != 0)
            {
                $arrTables = $this->Input->post("database_tables_recommended");
            }

            if (is_array($this->Input->post("database_tables_none_recommended")) && count($this->Input->post("database_tables_none_recommended")) != 0)
            {
                $arrTables = array_merge($arrTables, $this->Input->post("database_tables_none_recommended"));
            }

            $arrSyncSettings["syncCto_SyncTables"] = $arrTables;
        }
        else
        {
            $arrSyncSettings["syncCto_SyncTables"] = array();
        }

        // Systemoperation execute
        if ($this->Input->post("systemoperations_check") == 1)
        {
            if (is_array($this->Input->post("systemoperations_maintenance")) && count($this->Input->post("systemoperations_maintenance")) != 0)
            {
                $arrSyncSettings["syncCto_Systemoperations_Maintenance"] = $this->Input->post("systemoperations_maintenance");
            }
            else
            {
                $arrSyncSettings["syncCto_Systemoperations_Maintenance"] = array();
            }
        }
        else
        {
            $arrSyncSettings["syncCto_Systemoperations_Maintenance"] = array();
        }

        // Attention flag
        if ($this->Input->post("attentionFlag") == 1)
        {
            $arrSyncSettings["syncCto_AttentionFlag"] = true;
        }
        else
        {
            $arrSyncSettings["syncCto_AttentionFlag"] = false;
        }

        $this->Session->set("syncCto_SyncSettings_" . $dc->id, $arrSyncSettings);

        $this->redirect($this->Environment->base . "contao/main.php?do=synccto_clients&amp;table=tl_syncCto_clients_syncFrom&amp;act=start&amp;step=0&amp;id=" . $this->Input->get("id"));
    }

    public function checkPermission()
    {
        if ($this->BackendUser->isAdmin)
        {
            return;
        }

        $GLOBALS['TL_DCA']['tl_syncCto_clients_syncFrom']['list']['sorting']['root'] = $this->BackendUser->filemounts;
    }

}

?>