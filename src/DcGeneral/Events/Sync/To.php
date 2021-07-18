<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

namespace MenAtWork\SyncCto\DcGeneral\Events\Sync;

use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetEditModeButtonsEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Event\PrePersistModelEvent;
use MenAtWork\SyncCto\Contao\API;
use RuntimeException;
use MenAtWork\SyncCto\DcGeneral\Events\Base;
use SyncCtoHelper;

/**
 * Class for syncTo configurations
 */
class To extends Base
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
        return 'tl_syncCto_clients_syncTo';
    }

    /**
     * @param GetEditModeButtonsEvent $objEvent
     */
    public function addButton(GetEditModeButtonsEvent $objEvent)
    {
        if (!$this->isRightContext($objEvent->getEnvironment())) {
            return;
        }

        // Check the file cache.
        $strInitFilePath = '/system/config/initconfig.php';
        if (file_exists(TL_ROOT . $strInitFilePath)) {
            $strFile        = new \File($strInitFilePath);
            $arrFileContent = $strFile->getContentAsArray();
            foreach ($arrFileContent as $strContent) {
                if (!preg_match("/(\/\*|\*|\*\/|\/\/)/", $strContent)) {
                    //system/tmp.
                    if (preg_match("/system\/tmp/", $strContent)) {
                        // Set data.
                        \Message::addInfo($GLOBALS['TL_LANG']['MSC']['disabled_cache']);
                    }
                }
            }
        }

        // Update a field with last sync information
        $objSyncTime = \Database::getInstance()
                                ->prepare("SELECT cl.syncTo_tstamp as syncTo_tstamp, user.name as syncTo_user, user.username as syncTo_alias
                            FROM tl_synccto_clients as cl
                            INNER JOIN tl_user as user
                            ON cl.syncTo_user = user.id
                            WHERE cl.id = ?")
                                ->limit(1)
                                ->execute(\Input::get("id"));

        if ($objSyncTime->syncTo_tstamp != 0 && strlen($objSyncTime->syncTo_user) != 0 && strlen($objSyncTime->syncTo_alias) != 0) {
            $strLastSync = vsprintf($GLOBALS['TL_LANG']['MSC']['last_sync'], array(
                    date($GLOBALS['TL_CONFIG']['timeFormat'], $objSyncTime->syncTo_tstamp),
                    date($GLOBALS['TL_CONFIG']['dateFormat'], $objSyncTime->syncTo_tstamp),
                    $objSyncTime->syncTo_user,
                    $objSyncTime->syncTo_alias
                )
            );

            // Set data
            \Message::addInfo($strLastSync);
        }

        // Set buttons.
        $objEvent->setButtons(array
            (
                'start_sync'     => '<input type="submit" name="start_sync" id="start_sync" class="tl_submit" accesskey="s" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['sync']) . '" />',
                'start_sync_all' => '<input type="submit" name="start_sync_all" id="start_sync_all" class="tl_submit" accesskey="o" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['syncAll']) . '" />'
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
    public function submit(PrePersistModelEvent $objEvent)
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

        if (isset($_POST['start_sync'])) {
            $this->runSync($arrData);
        } elseif (isset($_POST['start_sync_all'])) {
            $this->runSyncAll($arrData);
        } else {
            throw new RuntimeException('Unknown submit.');
        }
    }

    /**
     * Handle syncTo configurations
     *
     * @param array $arrData
     *
     * @return array
     */
    protected function runSync($arrData)
    {
        $arrSyncSettings = [
            'post_data'                            => $arrData,
            'automode'                             => false,
            'syncCto_Type'                         => [],
            'syncCto_SyncDatabase'                 => false,
            'syncCto_SyncTlFiles'                  => false,
            'syncCto_Systemoperations_Maintenance' => [],
            'syncCto_AttentionFlag'                => false,
            'syncCto_ShowError'                    => false,
            'run_id'                               => uniqid('sync.runner.to:', false)
        ];

        // Synchronization type.
        if (isset($arrData['sync_options'])) {
            $arrSyncSettings["syncCto_Type"] = $arrData['sync_options'];
        }

        // Database.
        if (isset($arrData['database_check'])) {
            $arrSyncSettings["syncCto_SyncDatabase"] = true;
        }

        // Database - tl_files
        if (isset($arrData['tl_files_check'])) {
            $arrSyncSettings["syncCto_SyncTlFiles"] = true;
        }

        // Systemoperation execute.
        if (isset($arrData['systemoperations_check']) && isset($arrData['systemoperations_maintenance'])) {
            $arrSyncSettings["syncCto_Systemoperations_Maintenance"] = $arrData['systemoperations_maintenance'];
        }

        // Attention flag.
        if (isset($arrData['attentionFlag'])) {
            $arrSyncSettings["syncCto_AttentionFlag"] = true;
        }

        // Error msg.
        if (isset($arrData['localconfig_error'])) {
            $arrSyncSettings["syncCto_ShowError"] = true;
        }

        // Save Session.
        $id = ModelId::fromSerialized(\Input::get('id'));
        API::setSessionData(
            \sprintf(
                'syncCto_SyncSettings_%s',
                $id->getId()
            ),
            $arrSyncSettings
        );

        // Check the vars.
        $this->objSyncCtoHelper->checkSubmit(
            [
                'postUnset'   => ['start_sync'],
                'error'       =>
                    [
                        'key'     => 'syncCto_submit_false',
                        'message' => $GLOBALS['TL_LANG']['ERR']['no_functions']
                    ],
                'redirectUrl' => \Environment::get('base') .
                    "contao/main.php?do=synccto_clients&amp;table=tl_syncCto_clients_syncTo&amp;act=start&amp;step=0&amp;id=" . $id->getId()
            ],
            $arrSyncSettings
        );
    }

    /**
     * Handle syncTo configurations.
     *
     * @param array $arrData
     *
     * @return array
     */
    protected function runSyncAll($arrData)
    {
        $id              = ModelId::fromSerialized(\Input::get('id'));
        $arrSyncSettings = array();

        // Set array.
        $arrSyncSettings["automode"]                             = true;
        $arrSyncSettings["syncCto_Type"]                         = array(
            'core_change',
            'core_delete',
            'user_change',
            'user_delete',
            'localconfig_update'
        );
        $arrSyncSettings["syncCto_SyncDatabase"]                 = true;
        $arrSyncSettings["syncCto_SyncTlFiles"]                  = true;
        $arrSyncSettings["syncCto_Systemoperations_Maintenance"] = array();
        $arrSyncSettings["syncCto_AttentionFlag"]                = false;
        $arrSyncSettings["syncCto_ShowError"]                    = false;

        // Save Session
        \Session::getInstance()->set("syncCto_SyncSettings_" . $id->getId(), $arrSyncSettings);

        $this->objSyncCtoHelper->checkSubmit(array(
            'postUnset'   => array('start_sync'),
            'error'       => array(
                'key'     => 'syncCto_submit_false',
                'message' => $GLOBALS['TL_LANG']['ERR']['missing_tables']
            ),
            'redirectUrl' => \Environment::get('base') . "contao/main.php?do=synccto_clients&amp;table=tl_syncCto_clients_syncTo&amp;act=start&amp;step=0&amp;id=" . $id->getId()
        ),
            $arrSyncSettings
        );
    }
}
