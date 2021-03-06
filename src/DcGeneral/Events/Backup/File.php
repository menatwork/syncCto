<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

namespace MenAtWork\SyncCto\DcGeneral\Events\Backup;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetEditModeButtonsEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PrePersistModelEvent;
use RuntimeException;
use MenAtWork\SyncCto\DcGeneral\Events\Base;
use SyncCtoHelper;

/**
 * Class for syncFrom configurations
 */
class File extends Base
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
        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();
    }

    /**
     * @inheritdoc
     */
    public function getContextProviderName()
    {
        return 'tl_syncCto_backup_file';
    }

    /**
     * @param GetEditModeButtonsEvent $objEvent
     */
    public function addButtonBackup(GetEditModeButtonsEvent $objEvent)
    {
        if (!$this->isRightContext($objEvent->getEnvironment())) {
            return;
        }

        $objEvent->setButtons(array
            (
                'start_backup' => '<input type="submit" name="start_backup" id="start_backup" class="tl_submit" accesskey="s" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['apply']) . '" />'
            )
        );
    }

    /**
     * @param GetEditModeButtonsEvent $objEvent
     */
    public function addButtonRestore(GetEditModeButtonsEvent $objEvent)
    {
        if (!$this->isRightContext($objEvent->getEnvironment(), 'tl_syncCto_restore_file')) {
            return;
        }

        $objEvent->setButtons(array
            (
                'start_backup' => '<input type="submit" name="restore_backup" id="restore_backup" class="tl_submit" accesskey="s" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['restore']) . '" />'
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
        if (!$this->isRightContext($objEvent->getEnvironment())) {
            return;
        }

        // Get the data from the DC.
        $arrData = $objEvent->getModel()->getPropertiesAsArray();
        foreach ($arrData as $strKey => $mixData) {
            if (empty($mixData)) {
                unset($arrData[$strKey]);
            }
        }

        // Check if core or user backup is selected
        if (!isset($arrData['core_files']) && !isset($arrData['user_files'])) {
            \Message::addError($GLOBALS['TL_LANG']['ERR']['missing_file_selection']);
            \Controller::redirect(\Environment::get('base') . "contao/main.php?do=syncCto_backups&table=tl_syncCto_backup_file");
        }

        if (isset($arrData['user_files']) && is_array($arrData['filelist']) && count($arrData['filelist']) == 0) {
            \Message::addError($GLOBALS['TL_LANG']['ERR']['missing_file_selection']);
            \Controller::redirect(\Environment::get('base') . "contao/main.php?do=syncCto_backups&table=tl_syncCto_backup_file");
        }

        foreach ((array)$arrData['filelist'] as $key => $value) {
            $arrData['filelist'][$key] = \FilesModel::findByPk($value)->path;
        }

        \Session::getInstance()->set("syncCto_BackupSettings", $arrData);

        // Check the vars.
        $this->objSyncCtoHelper->checkSubmit(array(
            'postUnset'   => array('start_backup'),
            'error'       => array(
                'key'     => 'syncCto_submit_false',
                'message' => $GLOBALS['TL_LANG']['ERR']['missing_tables']
            ),
            'redirectUrl' => \Environment::get('base') . "contao/main.php?do=syncCto_backups&table=tl_syncCto_backup_file&act=start"
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
        if (!$this->isRightContext($objEvent->getEnvironment(), 'tl_syncCto_restore_file')) {
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
            \Message::addError($GLOBALS['TL_LANG']['ERR']['missing_file_selection']);
            \Controller::redirect(\Environment::get('base') . "contao/main.php?do=syncCto_backups&table=tl_syncCto_restore_db");
        }

        $objFileModel = \FilesModel::findByPk($arrData['filelist']);
        if ($objFileModel == null) {
            \Message::addError(sprintf($GLOBALS['TL_LANG']['ERR']['unknown_file'], $arrData['filelist']));
            \Controller::redirect(\Environment::get('base') . "contao/main.php?do=syncCto_backups&table=tl_syncCto_restore_db");
        }

        // Check if file exists
        $arrData['filelist'] = \FilesModel::findByPk($arrData['filelist'])->path;
        if (!file_exists(TL_ROOT . "/" . $arrData['filelist'])) {
            \Message::addError(sprintf($GLOBALS['TL_LANG']['ERR']['unknown_file'], $arrData['filelist']));
            \Controller::redirect(\Environment::get('base') . "contao/main.php?do=syncCto_backups&table=tl_syncCto_restore_db");
        }

        // Save in session
        $arrBackupSettings                        = array();
        $arrBackupSettings['syncCto_restoreFile'] = $arrData['filelist'];
        \Session::getInstance()->set("syncCto_BackupSettings", $arrBackupSettings);

        // Redirect to the restore page.
        \Controller::redirect(\Environment::get('base') . "contao/main.php?do=syncCto_backups&amp;table=tl_syncCto_restore_file&amp;act=start");
    }

}
