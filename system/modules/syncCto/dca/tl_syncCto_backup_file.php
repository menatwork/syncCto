<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2013 
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

$GLOBALS['TL_DCA']['tl_syncCto_backup_file'] = array(
    // Config
    'config' => array(
        'dataContainer'    => 'General',
//        'closed'          => true,
        'disableSubmit'    => false,
        'enableVersioning' => false,
        'onload_callback'  => array(
            array('tl_syncCto_backup_file', 'onload_callback'),
        ),
        'onsubmit_callback' => array(
            array('tl_syncCto_backup_file', 'onsubmit_callback'),
        ),        
    ),
    'dca_config'  => array(
        'data_provider' => array(
            'default' => array(
                'class'  => $strDataProvider,
                'source' => 'tl_syncCto_clients_syncTo'
            ),
        ),
    ),
    // Palettes
    'palettes' => array(
        '__selector__' => array('user_files'),
        'default'     => '{filelist_legend},core_files,user_files;{backup_legend},backup_name;',
    ),
    // Sub Palettes
    'subpalettes' => array(
        'user_files' => 'filelist'
    ),
    // Fields
    'fields'     => array(
        'core_files' => array(
            'label'      => &$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['core_files'],
            'inputType'  => 'checkbox',
            'exclude'    => true
        ),
        'user_files' => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['user_files'],
            'inputType' => 'checkbox',
            'exclude'   => true,
            'eval'      => array('submitOnChange' => true),
        ),
        'filelist'       => array
            (
            'label'     => &$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['filelist'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => array(
                'fieldType'  => 'checkbox',
                'files'      => true,
                'filesOnly'  => false,
                'tl_class'   => 'clr'
            ),
        ),
        'backup_name' => array
            (
            'label'     => &$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['backup_name'],
            'inputType' => 'text',
            'exclude'   => true,
            'eval'      => array('maxlength' => 32),
        ),
    )
);

/**
 * Class for backup files
 */
class tl_syncCto_backup_file extends Backend
{

    // Vars
    protected $objSyncCtoHelper;

    /**
     * Constructor
     */
    public function __construct()
    {
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
        if (get_class($dc) != 'DC_General')
        {
            return;
        }
        
        $dc->removeButton('save');
        $dc->removeButton('saveNclose');

        $arrData = array(
            'id'              => 'start_backup',
            'formkey'         => 'start_backup',
            'class'           => '',
            'accesskey'       => 'g',
            'value'           => specialchars($GLOBALS['TL_LANG']['MSC']['apply']),
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
        $strWidgetID     = $dc->getWidgetID();
        
        // Check if core or user backup is selected
        if ($this->Input->post('core_files_' . $strWidgetID) != 1 && $this->Input->post('user_files_' . $strWidgetID) != 1)
        {
            $_SESSION["TL_ERROR"][] = $GLOBALS['TL_LANG']['ERR']['missing_file_selection'];
            $this->redirect($this->Environment->base . "contao/main.php?do=syncCto_backups&table=tl_syncCto_backup_file");
        }

        // Check if we have a filelist for user files
        if ($this->Input->post('core_files_' . $strWidgetID) != 1
                && $this->Input->post('user_files_' . $strWidgetID) == 1
                && is_array($this->Input->post('filelist_' . $strWidgetID, true)) != true
                && count($this->Input->post('filelist_' . $strWidgetID, true)) == 0)
        {
            $_SESSION["TL_ERROR"][] = $GLOBALS['TL_LANG']['ERR']['missing_file_selection'];
            $this->redirect($this->Environment->base . "contao/main.php?do=syncCto_backups&table=tl_syncCto_backup_file");
        }

        $arrBackupSettings = array();

        $arrBackupSettings['core_files']    = $this->Input->post('core_files_' . $strWidgetID);
        $arrBackupSettings['user_files']    = $this->Input->post('user_files_' . $strWidgetID);
        $arrBackupSettings['user_filelist'] = $this->Input->post('filelist_' . $strWidgetID, true);
        $arrBackupSettings['backup_name']   = $this->Input->post('backup_name_' . $strWidgetID, true);

        // If we have a Contao 3 version resolve id to path.
        if (version_compare(VERSION, '3.0', '>='))
        {
            $arrBackupSettings['user_filelist'] = trimsplit(',', $arrBackupSettings['user_filelist']);            
            foreach ((array) $arrBackupSettings['user_filelist'] as $key => $value)
            {
                $arrBackupSettings['user_filelist'][$key] = Contao\FilesModel::findByPk($value)->path;
            }
        }
        
        $this->Session->set("syncCto_BackupSettings", $arrBackupSettings);

        $this->objSyncCtoHelper->checkSubmit(array(
            'postUnset' => array('start_backup'),
            'error' => array(
                'key'         => 'syncCto_submit_false',
                'message'     => $GLOBALS['TL_LANG']['ERR']['missing_tables']
            ),
            'redirectUrl' => $this->Environment->base . "contao/main.php?do=syncCto_backups&table=tl_syncCto_backup_file&act=start"
        ));
    }

}