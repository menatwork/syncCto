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
$GLOBALS['TL_DCA']['tl_syncCto_backup_file'] = array(
    // Config
    'config' => array(
        'dataContainer' => 'Memory',
        'closed' => true,
        'disableSubmit' => false,
        'onload_callback' => array(
            array('tl_syncCto_backup_file', 'onload_callback'),
        ),
        'onsubmit_callback' => array(
            array('tl_syncCto_backup_file', 'onsubmit_callback'),
        ),
        'dcMemory_show_callback' => array(
            array('tl_syncCto_backup_file', 'show_all')
        ),
        'dcMemory_showAll_callback' => array(
            array('tl_syncCto_backup_file', 'show_all')
        ),
    ),
    // Palettes
    'palettes' => array(
        '__selector__' => array('user_files'),
        'default' => '{filelist_legend},core_files,user_files;{backup_legend},backup_name;',
    ),
    // Sub Palettes
    'subpalettes' => array(
        'user_files' => 'filelist'
    ),
    // Fields
    'fields' => array(
        'core_files' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['core_files'],
            'inputType' => 'checkbox',
            'exclude' => true
        ),
        'user_files' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['user_files'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'eval' => array('submitOnChange' => true),
        ),
        'filelist' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['filelist'],
            'exclude' => true,
            'inputType' => 'fileTreeMemory',
            'eval' => array('fieldType' => 'checkbox', 'files' => true, 'filesOnly' => false, 'tl_class' => 'clr'),
        ),
        'backup_name' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['backup_name'],
            'inputType' => 'text',
            'exclude' => true,
            'eval' => array('maxlength' => 32),
        ),
    )
);

/**
 * Class for backup files
 */
class tl_syncCto_backup_file extends Backend
{

    /**
     * Set new and remove old buttons
     * 
     * @param DataContainer $dc 
     */
    public function onload_callback(DataContainer $dc)
    {
        $dc->removeButton('save');
        $dc->removeButton('saveNclose');

        $arrData = array(
            'id' => 'start_backup',
            'formkey' => 'start_backup',
            'class' => '',
            'accesskey' => 'g',
            'value' => specialchars($GLOBALS['TL_LANG']['MSC']['start_backup']),
            'button_callback' => array('tl_syncCto_backup_file', 'onsubmit_callback')
        );

        $dc->addButton('start_backup', $arrData);
    }

    /**
     * Handle backup files configurations
     * 
     * @param DataContainer $dc
     * @return array
     */
    public function onsubmit_callback(DataContainer $dc)
    {
        // Check if core or user backup is selected
        if ($this->Input->post('core_files') != 1 && $this->Input->post('user_files') != 1)
        {
            $_SESSION["TL_ERROR"][] = $GLOBALS['TL_LANG']['ERR']['missing_file_selection'];
            $this->redirect($this->Environment->base . "contao/main.php?do=syncCto_backups&table=tl_syncCto_backup_file");
        }

        // Check if we have a filelist for user files
        if ($this->Input->post('core_files') != 1 
                && $this->Input->post('user_files') == 1 
                && is_array($this->Input->post('filelist', true)) != true
                && count($this->Input->post('filelist', true)) == 0)
        {
            $_SESSION["TL_ERROR"][] = $GLOBALS['TL_LANG']['ERR']['missing_file_selection'];
            $this->redirect($this->Environment->base . "contao/main.php?do=syncCto_backups&table=tl_syncCto_backup_file");
        }

        $arrBackupSettings = array();

        $arrBackupSettings['core_files']    = $this->Input->post('core_files');
        $arrBackupSettings['user_files']    = $this->Input->post('user_files');
        $arrBackupSettings['user_filelist'] = $this->Input->post('filelist', true);
        $arrBackupSettings['backup_name']   = $this->Input->post('backup_name', true);

        $this->Session->set("syncCto_BackupSettings", $arrBackupSettings);

        $arrPostUnset = array('FORM_SUBMIT', 'FORM_FIELDS', 'REQUEST_TOKEN', 'start_backup');
        $arrPost = $_POST;
        
        foreach($arrPostUnset AS $value)
        {
            if(array_key_exists($value, $arrPost))
            {
                unset($arrPost[$value]);
            }
        }
        
        if(count($arrPost) > 0)
        {
            if (array_key_exists('syncCto_submit_false', $_SESSION["TL_ERROR"]))
            {
                unset($_SESSION["TL_ERROR"]['syncCto_submit_false']);
            }
            $this->redirect($this->Environment->base . "contao/main.php?do=syncCto_backups&amp;table=tl_syncCto_backup_file&amp;act=start");
        }
        else
        {
            if(!array_key_exists('syncCto_submit_false', $_SESSION["TL_ERROR"]))
            {
                $_SESSION["TL_ERROR"]['syncCto_submit_false'] = $GLOBALS['TL_LANG']['ERR']['missing_tables_selection'];
            }
        }        
    }

    /**
     * Change active mode to edit
     * 
     * @return string 
     */
    public function show_all($dc, $strReturn)
    {
        return $strReturn . $dc->edit();
    }

}

?>