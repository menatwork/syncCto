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

use Contao\BackendUser;
use Contao\Database;
use Contao\Environment;
use Contao\File;
use Contao\Input;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;
use Contao\User;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetEditModeButtonsEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Event\PrePersistModelEvent;
use RuntimeException;
use MenAtWork\SyncCto\DcGeneral\Events\Base;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use SyncCtoHelper;

/**
 * Class for syncFrom configurations
 */
class From extends Base
{
    /**
     * @var SyncCtoHelper|null
     */
    protected ?SyncCtoHelper $objSyncCtoHelper;

    /**
     * @var SessionInterface
     */
    private SessionInterface $session;

    /**
     * Priority for the event.
     */
    const PRIORITY = 200;

    /**
     * @var BackendUser|User
     */
    private BackendUser|User $BackendUser;

    /**
     * Constructor
     */
    public function __construct()
    {
        $container = System::getContainer();
        /** @var RequestStack $requestStack */
        $requestStack = $container->get('request_stack');
        $this->session = $requestStack->getSession();

        $this->BackendUser = BackendUser::getInstance();
        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();
    }

    public function getContextProviderName()
    {
        return 'tl_syncCto_clients_syncFrom';
    }

    /**
     * @param GetEditModeButtonsEvent $objEvent
     *
     * @throws \Exception
     */
    public function addButton(GetEditModeButtonsEvent $objEvent)
    {
        if (!$this->isRightContext($objEvent->getEnvironment())) {
            return;
        }

        // Check the file cache.
        $strInitFilePath = '/system/config/initconfig.php';
        if (file_exists($this->objSyncCtoHelper->getContaoRoot() . $strInitFilePath)) {
            $strFile = new File($strInitFilePath);
            $arrFileContent = $strFile->getContentAsArray();
            foreach ($arrFileContent as $strContent) {
                if (!preg_match("/(\/\*|\*|\*\/|\/\/)/", $strContent)) {
                    //system/tmp.
                    if (preg_match("/system\/tmp/", $strContent)) {
                        // Set data.
                        Message::addInfo($GLOBALS['TL_LANG']['MSC']['disabled_cache']);
                    }
                }
            }
        }

        // Update a field with last sync information
        $objSyncTime = Database::getInstance()
                               ->prepare(
                                   "SELECT cl.syncFrom_tstamp as syncFrom_tstamp, user.name as syncFrom_user, user.username as syncFrom_alias
                         FROM tl_synccto_clients as cl
                         INNER JOIN tl_user as user
                         ON cl.syncTo_user = user.id
                         WHERE cl.id = ?"
                               )
                               ->limit(1)
                               ->execute(Input::get("id"))
        ;

        if ($objSyncTime->syncFrom_tstamp != 0 && strlen($objSyncTime->syncFrom_user) != 0 && strlen($objSyncTime->syncFrom_alias) != 0) {
            $strLastSync = vsprintf(
                $GLOBALS['TL_LANG']['MSC']['last_sync'],
                array(
                    date($GLOBALS['TL_CONFIG']['timeFormat'], $objSyncTime->syncFrom_tstamp),
                    date($GLOBALS['TL_CONFIG']['dateFormat'], $objSyncTime->syncFrom_tstamp),
                    $objSyncTime->syncFrom_user,
                    $objSyncTime->syncFrom_alias
                )
            );

            // Set data
            Message::addInfo($strLastSync);
        }

        $backendUser = BackendUser::getInstance();
        $groupRightForceFiles = $backendUser->syncCto_hide_auto_sync;

        $buttons = [];
        $buttons['start_sync'] = '<input type="submit" name="start_sync" id="start_sync" class="tl_submit" accesskey="s" value="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['sync']) . '" />';
//        if ($groupRightForceFiles != true) {
//            $buttons['start_sync_all'] = '<input type="submit" name="start_sync_all" id="start_sync_all" class="tl_submit" accesskey="o" value="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['syncAll']) . '" />';
//        }

        // Set buttons.
        $objEvent->setButtons($buttons);
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
     * @param array $data
     *
     * @return void
     */
    protected function runSync(array $data): void
    {
        $id = ModelId::fromSerialized(Input::get('id'));
        $arrSyncSettings = array();
        $arrSyncSettings["post_data"] = $data;

        // Automode off.
        $arrSyncSettings["automode"] = false;

        // Synchronization type.
        if (isset($data['sync_options'])) {
            $arrSyncSettings["syncCto_Type"] = $data['sync_options'];
        } else {
            $arrSyncSettings["syncCto_Type"] = array();
        }

        // Database.
        if (isset($data['database_check'])) {
            $arrSyncSettings["syncCto_SyncDatabase"] = true;
        } else {
            $arrSyncSettings["syncCto_SyncDatabase"] = false;
        }

        // Database - tl_files
        if (isset($data['tl_files_check'])) {
            $arrSyncSettings["syncCto_SyncTlFiles"] = true;
        } else {
            $arrSyncSettings["syncCto_SyncTlFiles"] = false;
        }

        // Systemoperation execute.
        if (isset($data['systemoperations_check']) && isset($data['systemoperations_maintenance'])) {
            $arrSyncSettings["syncCto_Systemoperations_Maintenance"] = $data['systemoperations_maintenance'];
        } else {
            $arrSyncSettings["syncCto_Systemoperations_Maintenance"] = array();
        }

        // Attention flag.
        if (isset($data['attentionFlag'])) {
            $arrSyncSettings["syncCto_AttentionFlag"] = true;
        } else {
            $arrSyncSettings["syncCto_AttentionFlag"] = false;
        }

        // Error msg.
        if (isset($data['localconfig_error'])) {
            $arrSyncSettings["syncCto_ShowError"] = true;
        } else {
            $arrSyncSettings["syncCto_ShowError"] = false;
        }

        // Save Session.
        $this->session->set("syncCto_SyncSettings_" . $id->getId(), $arrSyncSettings);

        // Check the vars.
        $this->objSyncCtoHelper->checkSubmit(
            array(
                'postUnset'   => array('start_sync'),
                'error'       => array(
                    'key'     => 'syncCto_submit_false',
                    'message' => $GLOBALS['TL_LANG']['ERR']['no_functions']
                ),
                'redirectUrl' => Environment::get('base') . "contao/runsynccto?do=synccto_clients&amp;table=tl_syncCto_clients_syncFrom&amp;act=start&amp;step=0&amp;id=" . $id->getId()
            ),
            $arrSyncSettings
        );
    }

    /**
     * Handle syncTo configurations.
     *
     * @param array $data
     *
     * @return void
     */
    protected function runSyncAll(array $data): void
    {
        $id = ModelId::fromSerialized(Input::get('id'));
        $arrSyncSettings = array();

        // Set array.
        $arrSyncSettings["automode"] = true;
        $arrSyncSettings["syncCto_Type"] = array(
            'core_change',
            'core_delete',
            'user_change',
            'user_delete',
            'localconfig_update'
        );
        $arrSyncSettings["syncCto_SyncDatabase"] = true;
        $arrSyncSettings["syncCto_Systemoperations_Maintenance"] = array();
        $arrSyncSettings["syncCto_AttentionFlag"] = false;
        $arrSyncSettings["syncCto_ShowError"] = false;

        // Save Session
        $this->session->set("syncCto_SyncSettings_" . $id->getId(), $arrSyncSettings);

        $this->objSyncCtoHelper->checkSubmit(
            array(
                'postUnset'   => array('start_sync'),
                'error'       => array(
                    'key'     => 'syncCto_submit_false',
                    'message' => $GLOBALS['TL_LANG']['ERR']['missing_tables']
                ),
                'redirectUrl' => Environment::get('base') . "contao/runsynccto?do=synccto_clients&amp;table=tl_syncCto_clients_syncFrom&amp;act=start&amp;step=0&amp;id=" . $id->getId()
            ),
            $arrSyncSettings
        );
    }
}
