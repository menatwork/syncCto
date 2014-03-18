<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL 
 * @filesource
 */

if (SyncCtoHelper::isDcGeneralC3Version())
{
    $strDataProvider = 'GeneralDataSyncCto';
}
else
{
    $strDataProvider = 'GeneralDataSyncCtoC2';
}

$GLOBALS['TL_DCA']['tl_syncCto_restore_file'] = array
(
    // Config
    'config' => array
    (
        'dataContainer'           => 'General',
        'disableSubmit'           => false,
        'onload_callback' => array
        (
            array('tl_syncCto_restore_file', 'onload_callback'),
        ),
        'onsubmit_callback' => array
        (
            array('tl_syncCto_restore_file', 'onsubmit_callback'),
        ),
    ),
    'dca_config'  => array
    (
        'data_provider' => array
        (
            'default' => array
            (
                'class'           => $strDataProvider,
                'source'          => 'tl_syncCto_restore_file'
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
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_restore_file']['filelist'],
            'inputType'           => 'fileTree',
            'eval' => array
            (
                'files'           => true,
                'filesOnly'       => true,
                'fieldType'       => 'radio',
                'path'            => $GLOBALS['TL_CONFIG']['uploadPath'] . '/syncCto_backups/files', 
                'extensions'      => 'rar,zip'
            ),
        ),
    )
);

/**
 * Class for restore files
 */
class tl_syncCto_restore_file extends Backend
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
            'button_callback' => array('tl_syncCto_restore_file', 'onsubmit_callback')
        );

        $dc->addButton('restore_backup', $arrData);
    }

    /**
     * Handle restore files configurations
     * 
     * @param DataContainer $dc
     * @return array
     */
    public function onsubmit_callback(DataContainer $dc)
    {
        $strWidgetID     = $dc->getWidgetID();
        
        // Check if a file was selected
        if ($this->Input->post("filelist_" . $strWidgetID) == "")
        {
            $_SESSION["TL_ERROR"] = array(vsprintf($GLOBALS['TL_LANG']['ERR']['unknown_file'], array($this->Input->post("filelist"))));
            $this->redirect($this->Environment->base . "contao/main.php?do=syncCto_backups&table=tl_syncCto_restore_file");
        }

        $arrBackupSettings                = array();
        $arrBackupSettings['backup_file'] = $this->Input->post("filelist_" . $strWidgetID);

        // If we have a Contao 3 version resolve id to path.
        if (version_compare(VERSION, '3.0', '>='))
        {
            $arrBackupSettings['backup_file'] = Contao\FilesModel::findByPk($arrBackupSettings['backup_file'])->path;
        }

        $this->Session->set("syncCto_BackupSettings", $arrBackupSettings);

        $this->redirect($this->Environment->base . "contao/main.php?do=syncCto_backups&amp;table=tl_syncCto_restore_file&amp;act=start");
    }

}