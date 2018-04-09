<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

namespace SyncCto\DcGeneral\Events\Backup;

use Contao\Controller;
use Contao\Environment;
use Contao\FilesModel;
use Contao\Message;
use Contao\Session;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetEditModeButtonsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PrePersistModelEvent;
use RuntimeException;
use SyncCto\DcGeneral\Events\Base;
use SyncCto\Helper\Helper;

/**
 * Class for syncFrom configurations
 */
class Database extends Base
{

    // Vars
    protected $objSyncCtoHelper;

    /**
     * Priority for the event.
     */
    const PRIORITY = 200;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->objSyncCtoHelper = Helper::getInstance();
    }

    /**
     * @inheritdoc
     */
    public function getContextProviderName()
    {
        return 'tl_syncCto_backup_db';
    }

    /**
     * @param GetEditModeButtonsEvent $objEvent
     */
    public function addButtonBackup(GetEditModeButtonsEvent $objEvent)
    {
        if (!$this->isRightContext($objEvent->getEnvironment(), 'tl_syncCto_backup_db')) {
            return;
        }

        $objEvent->setButtons(array
            (
                'start_backup' => '<input type="submit" name="start_backup" id="start_backup" class="tl_submit" accesskey="s" value="' . \specialchars($GLOBALS['TL_LANG']['MSC']['apply']) . '" />'
            )
        );
    }

    /**
     * @param GetEditModeButtonsEvent $objEvent
     */
    public function addButtonRestore(GetEditModeButtonsEvent $objEvent)
    {
        if (!$this->isRightContext($objEvent->getEnvironment(), 'tl_syncCto_restore_db')) {
            return;
        }

        $objEvent->setButtons(array
            (
                'start_backup' => '<input type="submit" name="restore_backup" id="restore_backup" class="tl_submit" accesskey="s" value="' . \specialchars($GLOBALS['TL_LANG']['MSC']['restore']) . '" />'
            )
        );
    }

    /**
     * Function for exporting languages
     *
     * @param PrePersistModelEvent $objEvent
     *
     * @throws RuntimeException If the submit type is unknown.
     */
    public function submitBackup(PrePersistModelEvent $objEvent)
    {
        if (!$this->isRightContext($objEvent->getEnvironment(), 'tl_syncCto_backup_db')) {
            return;
        }

        // Get the data from the DC.
        $arrData = $objEvent->getModel()->getPropertiesAsArray();
        foreach ($arrData as $strKey => $mixData) {
            if (empty($mixData)) {
                unset($arrData[$strKey]);
            }
        }

        // Merge recommend and none recommend post arrays
        $arrBackupSettings['syncCto_BackupTables'] = array();
        if (isset($arrData['database_tables_recommended'])) {
            $arrBackupSettings['syncCto_BackupTables'] = $arrData['database_tables_recommended'];
        }

        if (isset($arrData['database_tables_none_recommended'])) {
            $arrBackupSettings['syncCto_BackupTables'] = \array_merge($arrBackupSettings['syncCto_BackupTables'],
                $arrData['database_tables_none_recommended']);
        }

        Session::getInstance()->set('syncCto_BackupSettings', $arrBackupSettings);

        // Check the vars.
        $this->objSyncCtoHelper->checkSubmit(array(
            'postUnset'   => array('start_backup'),
            'error'       => array(
                'key'     => 'syncCto_submit_false',
                'message' => $GLOBALS['TL_LANG']['ERR']['no_functions']
            ),
            'redirectUrl' => Environment::get('base') . "contao/main.php?do=syncCto_backups&table=tl_syncCto_backup_db&act=start"
        ),
            $arrData
        );
    }

    /**
     * Function for exporting languages
     *
     * @param PrePersistModelEvent $objEvent
     *
     * @throws RuntimeException If the submit type is unknown.
     */
    public function submitRestore(PrePersistModelEvent $objEvent)
    {
        if (!$this->isRightContext($objEvent->getEnvironment(), 'tl_syncCto_restore_db')) {
            return;
        }

        // Get the data from the DC.
        $arrData = $objEvent->getModel()->getPropertiesAsArray();
        foreach ($arrData as $strKey => $mixData) {
            if (empty($mixData)) {
                unset($arrData[$strKey]);
            }
        }

        // Check if a file is selected
        if ($arrData['filelist'] == '') {
            Message::addError($GLOBALS['TL_LANG']['ERR']['missing_file_selection']);
            Controller::redirect(Environment::get('base') . "contao/main.php?do=syncCto_backups&table=tl_syncCto_restore_db");
        }

        $objFileModel = \FilesModel::findByPk($arrData['filelist']);
        if ($objFileModel == null) {
            Message::addError(\sprintf($GLOBALS['TL_LANG']['ERR']['unknown_file'], $arrData['filelist']));
            Controller::redirect(Environment::get('base') . "contao/main.php?do=syncCto_backups&table=tl_syncCto_restore_db");
        }

        // Check if file exists
        $arrData['filelist'] = FilesModel::findByPk($arrData['filelist'])->path;
        if (!\file_exists(TL_ROOT . "/" . $arrData['filelist'])) {
            Message::addError(\sprintf($GLOBALS['TL_LANG']['ERR']['unknown_file'], $arrData['filelist']));
            Controller::redirect(Environment::get('base') . "contao/main.php?do=syncCto_backups&table=tl_syncCto_restore_db");
        }

        // Save in session
        $arrBackupSettings                        = array();
        $arrBackupSettings['syncCto_restoreFile'] = $arrData['filelist'];
        Session::getInstance()->set("syncCto_BackupSettings", $arrBackupSettings);

        // Redirect to the restore page.
        Controller::redirect(Environment::get('base') . "contao/main.php?do=syncCto_backups&table=tl_syncCto_restore_db&act=start");
    }

    /**
     * Get database tables recommended array
     *
     * @param GetPropertyOptionsEvent $event
     *
     * @return array
     */
    public function databaseTablesRecommended(GetPropertyOptionsEvent $event)
    {
        if (!$this->isRightContext($event->getEnvironment())) {
            return;
        }

        if ($event->getPropertyName() != 'database_tables_recommended') {
            return;
        }

        $arrTableRecommended = $this->objSyncCtoHelper->databaseTablesRecommended();

        $arrStyledTableRecommended = array();
        foreach ($arrTableRecommended AS $strTableName => $arrTable) {
            $arrStyledTableRecommended[$strTableName] = $this->objSyncCtoHelper->getStyledTableMeta($arrTable);
        }

        $event->setOptions($arrStyledTableRecommended);
    }

    /**
     * Get database tables none recommended with hidden array
     *
     * @param GetPropertyOptionsEvent $event
     *
     * @return array
     */
    public function databaseTablesNoneRecommendedWithHidden(GetPropertyOptionsEvent $event)
    {
        if (!$this->isRightContext($event->getEnvironment())) {
            return;
        }

        if ($event->getPropertyName() != 'database_tables_none_recommended') {
            return;
        }

        $arrTableRecommended = $this->objSyncCtoHelper->databaseTablesNoneRecommendedWithHidden();

        $arrStyledTableRecommended = array();
        foreach ($arrTableRecommended AS $strTableName => $arrTable) {
            $arrStyledTableRecommended[$strTableName] = $this->objSyncCtoHelper->getStyledTableMeta($arrTable);
        }

        $event->setOptions($arrStyledTableRecommended);
    }
}
