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

$GLOBALS['TL_DCA']['tl_syncCto_backup_db'] = array
(
    // Config
    'config' => array
    (
        'dataContainer'           => 'General',
        'disableSubmit'           => false,
        'onload_callback' => array
        (
            array('tl_syncCto_backup_db', 'onload_callback'),
        ),
        'onsubmit_callback' => array
        (
            array('tl_syncCto_backup_db', 'onsubmit_callback'),
        )
    ),
    'dca_config' => array
    (
        'data_provider' => array
        (
            'default' => array
            (
                'class'           => $strDataProvider,
                'source'          => 'tl_syncCto_backup_db'
            ),
        ),
    ),
    // Palettes
    'palettes' => array
    (
        'default'                 => '{table_recommend_legend},database_tables_recommended;{table_none_recommend_legend:hide},database_tables_none_recommended;'
    ),
    // Fields
    'fields' => array
    (
        'database_tables_recommended' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['database_tables_recommended'],
            'inputType'           => 'checkbox',
            'exclude'             => true,
            'eval'                => array('multiple' => true),
            'options_callback'    => array('tl_syncCto_backup_db', 'databaseTablesRecommended'),
        ),
        'database_tables_none_recommended' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_backup_db']['database_tables_none_recommended'],
            'inputType'           => 'checkbox',
            'exclude'             => true,
            'eval'                => array('multiple' => true),
            'options_callback'    => array('tl_syncCto_backup_db', 'databaseTablesNoneRecommendedWithHidden'),
        )
    )
);

/**
 * Class for backup database
 */
class tl_syncCto_backup_db extends Backend
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
     * Get database tables recommended array
     * 
     * @return array 
     */
    public function databaseTablesRecommended()
    {
        $arrTableRecommended = $this->objSyncCtoHelper->databaseTablesRecommended();
        
        $arrStyledTableRecommended = array();
        foreach($arrTableRecommended AS $strTableName => $arrTable)
        {
            $arrStyledTableRecommended[$strTableName] = $this->objSyncCtoHelper->getStyledTableMeta($arrTable);
        }
        
        return $arrStyledTableRecommended;
    }
    
    /**
     * Get database tables none recommended with hidden array
     * 
     * @return array 
     */    
    public function databaseTablesNoneRecommendedWithHidden()
    {
        $arrTableRecommended = $this->objSyncCtoHelper->databaseTablesNoneRecommendedWithHidden();
        
        $arrStyledTableRecommended = array();
        foreach($arrTableRecommended AS $strTableName => $arrTable)
        {
            $arrStyledTableRecommended[$strTableName] = $this->objSyncCtoHelper->getStyledTableMeta($arrTable);
        }
        
        return $arrStyledTableRecommended;
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

        $arrData = array
        (
            'id' => 'start_backup',
            'formkey' => 'start_backup',
            'class' => '',
            'accesskey' => 'g',
            'value' => specialchars($GLOBALS['TL_LANG']['MSC']['apply']),
            'button_callback' => array('tl_syncCto_backup_db', 'onsubmit_callback')
        );

        $dc->addButton('start_backup', $arrData);
    }

    /**
     * Handle backup database configurations
     * 
     * @param DataContainer $dc
     * @return array
     */
    public function onsubmit_callback(DataContainer $dc)
    {   
        $strWidgetID     = $dc->getWidgetID();
        $arrBackupSettings = array();

        // Merge recommend and none recommend post arrays
        if ($this->Input->post("database_tables_recommended_" . $strWidgetID) != "" && $this->Input->post("database_tables_none_recommended_" . $strWidgetID) != "")
        {
            $arrBackupSettings["syncCto_BackupTables"] = array_merge($this->Input->post("database_tables_recommended_" . $strWidgetID), $this->Input->post("database_tables_none_recommended_" . $strWidgetID));
        }
        else if ($this->Input->post("database_tables_recommended_" . $strWidgetID))
        {
            $arrBackupSettings["syncCto_BackupTables"] = $this->Input->post("database_tables_recommended_" . $strWidgetID);
        }
        else if ($this->Input->post("database_tables_none_recommended_" . $strWidgetID))
        {
            $arrBackupSettings["syncCto_BackupTables"] = $this->Input->post("database_tables_none_recommended_" . $strWidgetID);
        }
        
        $this->Session->set("syncCto_BackupSettings", $arrBackupSettings);
        
        $this->objSyncCtoHelper->checkSubmit(array(
            'postUnset' => array('start_backup'),
            'error' => array(
                'key' => 'syncCto_submit_false',
                'message' => $GLOBALS['TL_LANG']['ERR']['missing_tables']
            ),
            'redirectUrl' => $this->Environment->base . "contao/main.php?do=syncCto_backups&table=tl_syncCto_backup_db&act=start"
        )); 
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