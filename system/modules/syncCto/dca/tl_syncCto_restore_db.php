<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL 
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_syncCto_restore_db'] = array
(
    // Config
    'config' => array
    (
        'dataContainer'           => 'General',
        'disableSubmit'           => false,
        'onload_callback' => array
        (
            array('tl_syncCto_restore_db', 'onload_callback'),
        ),
        'onsubmit_callback' => array
        (
            array('tl_syncCto_restore_db', 'onsubmit_callback'),
        ),
    ),
    'dca_config'  => array
    (
        'data_provider' => array
        (
            'default' => array
            (
                'class'           => 'GeneralDataSyncCto',
                'source'          => 'tl_syncCto_restore_db'
            ),
        ),
    ),
    // Palettes
    'palettes' => array
    (
        'default'                 => '{filelist_legend},filelist;',
    ),
    // Fields
    'fields' => array
    (
        'filelist' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_restore_db']['filelist'],
            'inputType'           => 'fileTree',
            'eval' => array
            (
                'files'           => true,
                'filesOnly'       => true,
                'fieldType'       => 'radio',
                'path'            => $GLOBALS['TL_CONFIG']['uploadPath'] . '/syncCto_backups/database',
                'extensions'      => 'zip,synccto'
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
        if (get_class($dc) != 'DC_General')
        {
            return;
        }
        
        $dc->removeButton('save');
        $dc->removeButton('saveNclose');

        $arrData = array
            (
            'id' => 'restore_backup',
            'formkey' => 'restore_backup',
            'class' => '',
            'accesskey' => 'g',
            'value' => specialchars($GLOBALS['TL_LANG']['MSC']['restore']),
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
        $strWidgetID = $dc->getWidgetID();
        $strFile     = $this->Input->post("filelist_" . $strWidgetID);

        // Check if a file is selected
        if ($strFile == "")
        {
            $_SESSION["TL_ERROR"][] = $GLOBALS['TL_LANG']['ERR']['missing_file_selection'];
            $this->redirect($this->Environment->base . "contao/main.php?do=syncCto_backups&table=tl_syncCto_restore_db");
        }

        // Save in session
        $arrBackupSettings                        = array();
        $arrBackupSettings['syncCto_restoreFile'] = $strFile;

        // If we have a Contao 3 version resolve id to path.
        $arrBackupSettings['syncCto_restoreFile'] = Contao\FilesModel::findByPk($arrBackupSettings['syncCto_restoreFile'])->path;

        // Check if file exists
        if (!file_exists(TL_ROOT . "/" . $arrBackupSettings['syncCto_restoreFile']))
        {
            $_SESSION["TL_ERROR"] = array(vsprintf($GLOBALS['TL_LANG']['ERR']['unknown_file'], array($strFile)));
            $this->redirect($this->Environment->base . "contao/main.php?do=syncCto_backups&table=tl_syncCto_restore_db");
        }

        $this->Session->set("syncCto_BackupSettings", $arrBackupSettings);

        $this->redirect($this->Environment->base . "contao/main.php?do=syncCto_backups&table=tl_syncCto_restore_db&act=start");
    }

}