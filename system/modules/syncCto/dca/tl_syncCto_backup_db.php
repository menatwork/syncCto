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
 
$GLOBALS['TL_DCA']['tl_syncCto_backup_db'] = array(
    // Config
    'config' => array
        (
        'dataContainer' => 'Memory',
        'closed' => true,
        'disableSubmit' => false,
        'onload_callback' => array(
            array('tl_syncCto_backup_db', 'onload_callback'),
        ),
        'onsubmit_callback' => array(
            array('tl_syncCto_backup_db', 'onsubmit_callback'),
        )
    ),
    // Palettes
    'palettes' => array
        (
        'default' => '{table_recommend_legend},table_list_recommend;{table_none_recommend_legend:hide},table_list_none_recommend;'
    ),
    // Fields
    'fields' => array(
        'table_list_recommend' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['table_list_recommend'],
            'inputType' => 'checkbox',
            'eval' => array('multiple' => true),
            'options_callback' => array('SyncCtoCallback', 'databaseTablesRecommended'),
        ),
        'table_list_none_recommend' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['table_list_none_recommend'],
            'inputType' => 'checkbox',
            'eval' => array('multiple' => true),
            'options_callback' => array('SyncCtoCallback', 'databaseTablesNoneRecommended'),
        ),
    )
);

class tl_syncCto_backup_db extends Backend
{

    public function onload_callback(DataContainer $dc)
    {
        $dc->removeButton('save');
        $dc->removeButton('saveNclose');

        $arrData = array
            (
            'id' => 'start_backup',
            'formkey' => 'start_backup',
            'class' => '',
            'accesskey' => 'g',
            'value' => specialchars($GLOBALS['TL_LANG']['MSC']['start_backup']),
            'button_callback' => array('tl_syncCto_backup_db', 'onsubmit_callback')
        );

        $dc->addButton('start_backup', $arrData);
    }
	
	public function onsubmit_callback(DataContainer $dc)
    {
        $arrStepPool = $this->Session->get("SyncCto_DB_StepPool");
        
        if(!is_array($arrStepPool))
            $arrStepPool = array();

        // Check Table list
        if ($this->Input->post("table_list_recommend") == "" && $this->Input->post("table_list_none_recommend") == "")
        {
            $_SESSION["TL_ERROR"] = array("Missing Tables");
            return;
        }

        // Merge recommend and none recommend post arrays
        if ($this->Input->post("table_list_recommend") != "" && $this->Input->post("table_list_none_recommend") != "")
            $arrTablesBackup = array_merge($this->Input->post("table_list_recommend"), $this->Input->post("table_list_none_recommend"));
        else if ($this->Input->post("table_list_recommend"))
            $arrTablesBackup = $this->Input->post("table_list_recommend");
        else if ($this->Input->post("table_list_none_recommend"))
            $arrTablesBackup = $this->Input->post("table_list_none_recommend");
        
        $arrStepPool["tables"] = $arrTablesBackup;
        
        $this->Session->set("SyncCto_DB_StepPool", $arrStepPool);
        
        $this->redirect($this->Environment->base . "contao/main.php?do=syncCto_backups&table=tl_syncCto_backup_db&act=start");
    }

}

?>