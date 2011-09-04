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
$GLOBALS['TL_DCA']['tl_syncCto_restore_file'] = array(
    // Config
    'config' => array
        (
        'dataContainer' => 'Memory',
        'closed' => true,
        'disableSubmit' => false,
        'onload_callback' => array(
            array('tl_syncCto_restore_file', 'onload_callback'),
        ),
        'onsubmit_callback' => array(
            array('tl_syncCto_restore_file', 'onsubmit_callback'),
        )
    ),
    // Palettes
    'palettes' => array
        (
        'default' => '{filelist_legend},filelist;',
    ),
    // Fields
    'fields' => array(
        'filelist' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_restore_file']['filelist'],
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => array('files' => true, 'filesOnly' => true, 'fieldType' => 'radio', 'path' => 'tl_files/syncCto_backups/files', 'extensions' => 'rar,zip'),
        ),
    )
);

class tl_syncCto_restore_file extends Backend
{

    public function onload_callback(DataContainer $dc)
    {
        $dc->removeButton('save');
        $dc->removeButton('saveNclose');

        $arrData = array
            (
            'id' => 'restore_backup',
            'formkey' => 'restore_backup',
            'class' => '',
            'accesskey' => 'g',
            'value' => specialchars($GLOBALS['TL_LANG']['MSC']['restore_backup']),
            'button_callback' => array('tl_syncCto_restore_file', 'onsubmit_callback')
        );

        $dc->addButton('restore_backup', $arrData);
    }

    public function onsubmit_callback(DataContainer $dc)
    {
        $arrStepPool = $this->Session->get("SyncCto_File_StepPool");

        if (!is_array($arrStepPool))
            $arrStepPool = array();

        if ($this->Input->post("filelist") == "")
        {
            $_SESSION["TL_ERROR"] = array(vsprintf($GLOBALS['TL_LANG']['ERR']['sync_no_file_found'], array($this->Input->post("filelist"))));
            return;
        }
        
        $arrStepPool["file"] = $this->Input->post("filelist");

        $this->Session->set("SyncCto_File_StepPool", $arrStepPool);

        $this->redirect($this->Environment->base . "contao/main.php?do=syncCto_backups&amp;table=tl_syncCto_restore_file&amp;act=start");
    }

}

?>