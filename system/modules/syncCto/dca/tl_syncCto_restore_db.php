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

$GLOBALS['TL_DCA']['tl_syncCto_restore_db'] = array(
    // Config
    'config' => array(
        'dataContainer' => 'Memory',
        'closed' => true,
        'disableSubmit' => false,
        'onload_callback' => array(
            array('tl_syncCto_restore_db', 'onload_callback'),
        ),
        'onsubmit_callback' => array(
            array('tl_syncCto_restore_db', 'onsubmit_callback'),
        ),
        'dcMemory_show_callback' => array(
            array('tl_syncCto_restore_db', 'show_all')
        ),
        'dcMemory_showAll_callback' => array(
            array('tl_syncCto_restore_db', 'show_all')
        ),
    ),
    // Palettes
    'palettes' => array(
        'default' => '{filelist_legend},filelist;',
    ),
    // Fields
    'fields' => array(
        'filelist' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_syncCto_restore_db']['filelist'],
            'inputType' => 'fileTreeMemory',
            'eval' => array(
                'files' => true,
                'filesOnly' => true,
                'fieldType' => 'radio',
                'path' => $GLOBALS['TL_CONFIG']['uploadPath'] .
                '/syncCto_backups/database',
                'extensions' => 'zip,synccto'
            ),
        ),
    )
);

/**
 * Class for restore database
 */
class tl_syncCto_restore_db extends Backend
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

        $arrData = array
            (
            'id' => 'restore_backup',
            'formkey' => 'restore_backup',
            'class' => '',
            'accesskey' => 'g',
            'value' => specialchars($GLOBALS['TL_LANG']['MSC']['restore_backup']),
            'button_callback' => array('tl_syncCto_restore_db', 'onsubmit_callback')
        );

        $dc->addButton('restore_backup', $arrData);
    }

    /**
     * Handle restore database configurations
     * 
     * @param DataContainer $dc
     * @return array
     */
    public function onsubmit_callback(DataContainer $dc)
    {
        $strFile = $this->Input->post("filelist");

        // Check if a file is selected
        if ($strFile == "")
        {
            $_SESSION["TL_ERROR"][] = $GLOBALS['TL_LANG']['ERR']['missing_file_selection'];
            $this->redirect($this->Environment->base . "contao/main.php?do=syncCto_backups&table=tl_syncCto_restore_db");
        }

        // Check if file exists
        if (!file_exists(TL_ROOT . "/" . $strFile))
        {
            $_SESSION["TL_ERROR"] = array(vsprintf($GLOBALS['TL_LANG']['ERR']['unknown_file'], array($strFile)));
            $this->redirect($this->Environment->base . "contao/main.php?do=syncCto_backups&table=tl_syncCto_restore_db");
        }

        // Save in session
        $arrBackupSettings = array();
        $arrBackupSettings['syncCto_restoreFile'] = $strFile;
        $this->Session->set("syncCto_BackupSettings", $arrBackupSettings);

        $this->redirect($this->Environment->base . "contao/main.php?do=syncCto_backups&table=tl_syncCto_restore_db&act=start");
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