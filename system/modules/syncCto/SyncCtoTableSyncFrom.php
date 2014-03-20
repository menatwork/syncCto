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
 * Class for syncFrom configurations
 */
class SyncCtoTableSyncFrom extends Backend
{

    // Helper Classes
    protected $objSyncCtoHelper;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->BackendUser      = BackendUser::getInstance();
        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();

        parent::__construct();
    }

    /**
     * Set new and remove old buttons
     *
     * @param DataContainer $dc
     */
    public function onload_callback(DataContainer $dc)
    {
        if (Input::getInstance()->get('act') == 'start' || get_class($dc) != 'DC_General')
        {
            return;
        }

        $strInitFilePath = '/system/config/initconfig.php';

        if (file_exists(TL_ROOT . $strInitFilePath))
        {
            $strFile        = new File($strInitFilePath);
            $arrFileContent = $strFile->getContentAsArray();
            $booLocated     = false;
            foreach ($arrFileContent AS $strContent)
            {
                if (!preg_match("/(\/\*|\*|\*\/|\/\/)/", $strContent))
                {
                    //system/tmp.
                    if (preg_match("/system\/tmp/", $strContent))
                    {
                        // Set data.
                        $this->addInfoMessage($GLOBALS['TL_LANG']['MSC']['disabled_cache']);
                        $booLocated = true;
                    }
                }
            }
        }

        $dc->removeButton('save');
        $dc->removeButton('saveNclose');

        // Sync normal
        $arrData = array
        (
            'id'              => 'start_sync',
            'formkey'         => 'start_sync',
            'class'           => '',
            'accesskey'       => 'g',
            'value'           => specialchars($GLOBALS['TL_LANG']['MSC']['sync']),
            'button_callback' => array('SyncCtoTableSyncFrom', 'onsubmit_callback')
        );

        $dc->addButton('start_sync', $arrData);

        // SyncAll
        $arrData = array
        (
            'id'              => 'start_sync_all',
            'formkey'         => 'start_sync_all',
            'class'           => '',
            'accesskey'       => 'g',
            'value'           => specialchars($GLOBALS['TL_LANG']['MSC']['syncAll']),
            'button_callback' => array('SyncCtoTableSyncFrom', 'onsubmit_callback_all')
        );

        $dc->addButton('start_sync_all', $arrData);


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
            $this->addInfoMessage($strLastSync);
        }
    }

    /**
     * Handle syncFrom configurations
     *
     * @param DataContainer $dc
     *
     * @return array
     */
    public function onsubmit_callback(DataContainer $dc)
    {
        $strWidgetID     = $dc->getWidgetID();
        $arrSyncSettings = array();

        // Automode off.
        $arrSyncSettings["automode"] = false;

        // Synchronization type
        if (is_array($this->Input->post("sync_options_" . $strWidgetID)) && count($this->Input->post("sync_options_" . $strWidgetID)) != 0)
        {
            $arrSyncSettings["syncCto_Type"] = $this->Input->post('sync_options_' . $strWidgetID);
        }
        else
        {
            $arrSyncSettings["syncCto_Type"] = array();
        }

        if ($this->Input->post("database_check_" . $strWidgetID) == 1)
        {
            $arrSyncSettings["syncCto_SyncDatabase"] = true;
        }
        else
        {
            $arrSyncSettings["syncCto_SyncDatabase"] = false;
        }

        // Systemoperation execute
        if ($this->Input->post("systemoperations_check_" . $strWidgetID) == 1)
        {
            if (is_array($this->Input->post("systemoperations_maintenance_" . $strWidgetID)) && count($this->Input->post("systemoperations_maintenance_" . $strWidgetID)) != 0)
            {
                $arrSyncSettings["syncCto_Systemoperations_Maintenance"] = $this->Input->post("systemoperations_maintenance_" . $strWidgetID);
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
        if ($this->Input->post("attentionFlag_" . $strWidgetID) == 1)
        {
            $arrSyncSettings["syncCto_AttentionFlag"] = true;
        }
        else
        {
            $arrSyncSettings["syncCto_AttentionFlag"] = false;
        }

        // Write all data
        foreach ($_POST as $key => $value)
        {
            $strClearKey                                = str_replace("_" . $strWidgetID, "", $key);
            $arrSyncSettings["post_data"][$strClearKey] = $this->Input->post($key);
        }

        $this->Session->set("syncCto_SyncSettings_" . $dc->id, $arrSyncSettings);

        $this->objSyncCtoHelper->checkSubmit(array(
            'postUnset'   => array('start_sync'),
            'error'       => array(
                'key'     => 'syncCto_submit_false',
                'message' => $GLOBALS['TL_LANG']['ERR']['missing_tables']
            ),
            'redirectUrl' => $this->Environment->base . "contao/main.php?do=synccto_clients&amp;table=tl_syncCto_clients_syncFrom&amp;act=start&amp;step=0&amp;id=" . $this->Input->get("id")
        ));
    }

    /**
     * Handle syncTo configurations.
     *
     * @param DataContainer $dc
     *
     * @return array
     */
    public function onsubmit_callback_all(DataContainer $dc)
    {
        $strWidgetID     = $dc->getWidgetID();
        $arrSyncSettings = array();

        // Set array.
        $arrSyncSettings["automode"]                             = true;
        $arrSyncSettings["syncCto_Type"]                         = array(
            'core_change',
            'core_delete',
            'user_change',
            'user_delete',
            'localconfig_update'
        );
        $arrSyncSettings["syncCto_SyncDatabase"]                 = true;
        $arrSyncSettings["syncCto_Systemoperations_Maintenance"] = array();
        $arrSyncSettings["syncCto_AttentionFlag"]                = false;
        $arrSyncSettings["syncCto_ShowError"]                    = false;

        // Write all data
        foreach ($_POST as $key => $value)
        {
            $strClearKey                                = str_replace("_" . $strWidgetID, "", $key);
            $arrSyncSettings["post_data"][$strClearKey] = $this->Input->post($key);
        }

        // Save Session
        $this->Session->set("syncCto_SyncSettings_" . $dc->id, $arrSyncSettings);

        $this->objSyncCtoHelper->checkSubmit(array(
            'postUnset'   => array('start_sync'),
            'error'       => array(
                'key'     => 'syncCto_submit_false',
                'message' => $GLOBALS['TL_LANG']['ERR']['missing_tables']
            ),
            'redirectUrl' => $this->Environment->base . "contao/main.php?do=synccto_clients&amp;table=tl_syncCto_clients_syncFrom&amp;act=start&amp;step=0&amp;id=" . $this->Input->get("id")
        ));
    }

}