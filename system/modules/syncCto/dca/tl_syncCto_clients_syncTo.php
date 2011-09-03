<?php

if (!defined('TL_ROOT'))
    die('You can not access this file directly!');

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
$GLOBALS['TL_DCA']['tl_syncCto_clients_syncTo'] = array(
    // Config
    'config' => array
        (
        'dataContainer' => 'Memory',
        'closed' => true,
        'disableSubmit' => false,
        'onload_callback' => array(
            array('tl_syncCto_clients_syncTo', 'onload_callback'),
            array('tl_syncCto_clients_syncTo', 'checkPermission'),
        ),
        'onsubmit_callback' => array(
            array('tl_syncCto_clients_syncTo', 'onsubmit_callback'),
        )
    ),
    // Palettes
    'palettes' => array
        (
        'default' => '{sync_legend},sync_type;{table_recommend_legend},database_tables_recommended;{table_none_recommend_legend},database_tables_none_recommended;{filelist_legend},filelist',
    ),
    // Fields
    'fields' => array(
        'sync_type' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['sync_type'],
            'inputType' => 'select',
            'exclude' => true,
            'eval' => array('helpwizard' => true),
            'reference' => &$GLOBALS['TL_LANG']['SYC'],
            'options_callback' => array('SyncCtoCallback', 'getSyncType'),
        ),
        'database_tables_recommended' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['database_tables_recommended'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'eval' => array('multiple' => true),
            'options_callback' => array('SyncCtoCallback', 'databaseTablesRecommended'),
        ),
        'database_tables_none_recommended' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['database_tables_none_recommended'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'eval' => array('multiple' => true),
            'options_callback' => array('SyncCtoCallback', 'databaseTablesNoneRecommended'),
        ),
        'filelist' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['filelist'],
            'inputType' => 'fileTree',
            'exclude' => true,
            'eval' => array('files' => true, 'filesOnly' => false, 'fieldType' => 'checkbox'),
        ),
    )
);

class tl_syncCto_clients_syncTo extends Backend
{

    // Constructor and singelten pattern
    public function __construct()
    {
        // Import Contao classes
        $this->BackendUser = BackendUser::getInstance();

        parent::__construct();
    }

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
            'value' => specialchars($GLOBALS['TL_LANG']['MSC']['syncTo']),
            'button_callback' => array('tl_syncCto_clients_syncTo', 'onsubmit_callback')
        );

        $dc->addButton('start_sync', $arrData);
    }

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
                $_SESSION["TL_ERROR"] = array($GLOBALS['TL_LANG']['syncCto']['unknown_method']);
                $this->redirect("contao/main.php?do=synccto_clients");
            }
        }
        else
        {
            $this->Session->set("syncCto_Typ", SYNCCTO_SMALL);
        }

        // Load table lists and merge them
        if ($this->Input->post("database_tables_recommended") != "" || $this->Input->post("database_tables_none_recommended") != "")
        {
            if ($this->Input->post("database_tables_recommended") != "" && $this->Input->post("database_tables_none_recommended") != "")
                $arrSyncTables = array_merge($this->Input->post("database_tables_recommended"), $this->Input->post("database_tables_none_recommended"));
            else if ($this->Input->post("database_tables_recommended"))
                $arrSyncTables = $this->Input->post("database_tables_recommended");
            else if ($this->Input->post("database_tables_none_recommended"))
                $arrSyncTables = $this->Input->post("database_tables_none_recommended");

            $this->Session->set("syncCto_SyncTables", $arrSyncTables);
        }
        else
        {
            $this->Session->set("syncCto_SyncTables", FALSE);
        }

        // Files for backup tl_files       
        if (is_array($this->Input->post('filelist')) && count($this->Input->post('filelist')) != 0)
        {
            $this->Session->set("syncCto_Filelist", $this->Input->post('filelist'));
        }
        else
        {
            $this->Session->set("syncCto_Filelist", FALSE);
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
            "url" => "contao/main.php?do=synccto_clients&amp;table=tl_syncCto_clients_syncTo&amp;act=start&amp;id=" . (int) $this->Input->get("id"),
            "goBack" => "contao/main.php?do=synccto_clients",
            "start" => microtime(true),
            "headline" => $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['edit'],
            "information" => "",
            "data" => array()
        );

        $this->Session->set("syncCto_Content", $arrContenData);

        $this->redirect($this->Environment->base . "contao/main.php?do=synccto_clients&amp;table=tl_syncCto_clients_syncTo&amp;act=start&amp;id=" . $this->Input->get("id"));
    }

    public function checkPermission()
    {
        if ($this->BackendUser->isAdmin)
        {
            return;
        }

        $GLOBALS['TL_DCA']['tl_syncCto_clients_syncTo']['list']['sorting']['root'] = $this->BackendUser->filemounts;
    }

}

?>