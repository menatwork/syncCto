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
 * @copyright  MEN AT WORK 2011
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_syncCto_clients_syncFrom'] = array(
    // Config
    'config' => array
        (
        'dataContainer' => 'Memory',
        'closed' => true,
        'disableSubmit' => false,
        'onload_callback' => array(
            array('tl_syncCto_clients_syncFrom', 'onload_callback'),
            array('tl_syncCto_clients_syncFrom', 'checkPermission'),
        ),
        'onsubmit_callback' => array(
            array('tl_syncCto_clients_syncFrom', 'onsubmit_callback'),
        )
    ),
    // Palettes
    'palettes' => array
        (
        'default' => '{sync_legend},lastSync,sync_type;{table_recommend_legend},database_tables_recommended;{table_none_recommend_legend},database_tables_none_recommended;',
    ),
    // Fields
    'fields' => array(
        'sync_type' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['sync_type'],
            'inputType' => 'select',
            'exclude' => true,
            'eval' => array('helpwizard' => true),
            'reference' => &$GLOBALS['TL_LANG']['SYC'],
            'options_callback' => array('SyncCtoHelper', 'getSyncType'),
        ),
        'lastSync' => array
            (
            'label' => " ",
            'exclude' => true,
            'inputType' => 'statictext',
        ),
        'database_tables_recommended' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['database_tables_recommended'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'eval' => array('multiple' => true),
            'options_callback' => array('SyncCtoHelper', 'getRecommendedDatabaseTablesClient'),
        ),
        'database_tables_none_recommended' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['database_tables_none_recommended'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'eval' => array('multiple' => true),
            'options_callback' => array('SyncCtoHelper', 'getNoneRecommendedDatabaseTablesClient'),
        ),
    )
);

/**
 * Class for syncFrom configurations
 */
class tl_syncCto_clients_syncFrom extends Backend
{

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->BackendUser = BackendUser::getInstance();

        parent::__construct();
    }

    /**
     * Set new and remove old buttons
     * 
     * @param DataContainer $dc 
     */
    public function onload_callback(DataContainer $dc)
    {
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
        
        if (strlen($objSyncTime->syncFrom_tstamp) != 0 && strlen($objSyncTime->syncFrom_user) != 0 && strlen($objSyncTime->syncFrom_alias) != 0)
        {
            $strLastSync = vsprintf($GLOBALS['TL_LANG']['MSC']['information_last_sync'], array(
                date($GLOBALS['TL_CONFIG']['timeFormat'], $objSyncTime->syncFrom_tstamp),
                date($GLOBALS['TL_CONFIG']['dateFormat'], $objSyncTime->syncFrom_tstamp),
                $objSyncTime->syncFrom_user,
                $objSyncTime->syncFrom_alias)
            );

            // Set data
            $dc->setData("lastSync", "<p class='tl_info'>" . $strLastSync . "</p><br />");
        }
        else
        {
            $GLOBALS['TL_DCA']['tl_syncCto_clients_syncFrom']['palettes']['default'] = str_replace(",lastSync", "", $GLOBALS['TL_DCA']['tl_syncCto_clients_syncFrom']['palettes']['default']);
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
        // Check sync. typ
        if (strlen($this->Input->post('sync_type')) != 0)
        {
            if ($this->Input->post('sync_type') == SYNCCTO_FULL || $this->Input->post('sync_type') == SYNCCTO_SMALL)
            {
                $this->Session->set("syncCto_Typ", $this->Input->post('sync_type'));
            }
            else
            {
                $_SESSION["TL_ERROR"][] = $GLOBALS['TL_LANG']['ERR']['unknown_function'];
                return;
            }
        }
        else
        {
            $this->Session->set("syncCto_Typ", SYNCCTO_SMALL);
        }
        
        

        // Load table lists
        if ($this->Input->post("database_tables_recommended") != "" || $this->Input->post("database_tables_none_recommended") != "")
        {
            if (is_array($this->Input->post("database_tables_recommended")))
            {
                $arrSyncTables = $this->Input->post("database_tables_recommended");
            }
            else
            {
                $arrSyncTables = array();
            }

            if (is_array($this->Input->post("database_tables_none_recommended")))
            {
                $arrSyncTables = array_merge($arrSyncTables, $this->Input->post("database_tables_none_recommended"));
            }
            
            $this->Session->set("syncCto_SyncTables", $arrSyncTables);
        }
        else
        {
            $this->Session->set("syncCto_SyncTables", FALSE);
        }

        $this->Session->set("syncCto_Start", microtime(true));

        // Step 1
        $this->Session->set("syncCto_StepPool1", FALSE);
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

        $arrContenData = array(
            "error" => false,
            "error_msg" => "",
            "refresh" => true,
            "finished" => false,
            "step" => 1,
            "url" => "contao/main.php?do=synccto_clients&amp;table=tl_syncCto_clients_syncFrom&amp;act=start&amp;id=" . (int) $this->Input->get("id"),
            "goBack" => "contao/main.php?do=synccto_clients",
            "start" => microtime(true),
            "headline" => $GLOBALS['TL_LANG']['tl_syncCto_clients_syncFrom']['edit'],
            "information" => "",
            "data" => array(),
            "abort" => false,
        );

        $this->Session->set("syncCto_Content", $arrContenData);

        $this->redirect($this->Environment->base . "contao/main.php?do=synccto_clients&amp;table=tl_syncCto_clients_syncFrom&amp;act=start&amp;id=" . $this->Input->get("id"));
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