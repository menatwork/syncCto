<?php

/**
 * This file is part of menatwork/synccto.
 *
 * (c) 2014-2018 MEN AT WORK.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    menatwork/synccto
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2014-2018 MEN AT WORK.
 * @license    https://github.com/menatwork/syncCto/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace SyncCto\DcGeneral\Events\Sync;

use Contao\Database;
use Contao\Environment;
use Contao\File;
use Contao\Input;
use Contao\Message;
use Contao\Session;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetEditModeButtonsEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Event\PrePersistModelEvent;
use RuntimeException;
use SyncCto\DcGeneral\Events\Base;
use SyncCto\Helper\Helper;

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
        $this->objSyncCtoHelper = Helper::getInstance();
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
        if (\file_exists(TL_ROOT . $strInitFilePath)) {
            $strFile        = new File($strInitFilePath);
            $arrFileContent = $strFile->getContentAsArray();
            foreach ($arrFileContent AS $strContent) {
                if (!\preg_match("/(\/\*|\*|\*\/|\/\/)/", $strContent)) {
                    //system/tmp.
                    if (\preg_match("/system\/tmp/", $strContent)) {
                        // Set data.
                        Message::addInfo($GLOBALS['TL_LANG']['MSC']['disabled_cache']);
                    }
                }
            }
        }

        // Update a field with last sync information
        $objSyncTime = Database::getInstance()
            ->prepare("SELECT cl.syncTo_tstamp as syncTo_tstamp, user.name as syncTo_user, user.username as syncTo_alias
                            FROM tl_synccto_clients as cl
                            INNER JOIN tl_user as user
                            ON cl.syncTo_user = user.id
                            WHERE cl.id = ?")
            ->limit(1)
            ->execute(Input::get("id"));

        if ($objSyncTime->syncTo_tstamp != 0 && \strlen($objSyncTime->syncTo_user) != 0 && \strlen($objSyncTime->syncTo_alias) != 0) {
            $strLastSync = \vsprintf($GLOBALS['TL_LANG']['MSC']['last_sync'], array(
                    \date($GLOBALS['TL_CONFIG']['timeFormat'], $objSyncTime->syncTo_tstamp),
                    \date($GLOBALS['TL_CONFIG']['dateFormat'], $objSyncTime->syncTo_tstamp),
                    $objSyncTime->syncTo_user,
                    $objSyncTime->syncTo_alias
                )
            );

            // Set data
            Message::addInfo($strLastSync);
        }

        // Set buttons.
        $objEvent->setButtons(array
            (
                'start_sync'     => '<input type="submit" name="start_sync" id="start_sync" class="tl_submit" accesskey="s" value="' . \specialchars($GLOBALS['TL_LANG']['MSC']['sync']) . '" />',
                'start_sync_all' => '<input type="submit" name="start_sync_all" id="start_sync_all" class="tl_submit" accesskey="o" value="' . \specialchars($GLOBALS['TL_LANG']['MSC']['syncAll']) . '" />'
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
        $id                           = ModelId::fromSerialized(Input::get('cid'));
        $arrSyncSettings              = array();
        $arrSyncSettings["post_data"] = $arrData;

        // Automode off.
        $arrSyncSettings["automode"] = false;

        // Synchronization type.
        if (isset($arrData['sync_options'])) {
            $arrSyncSettings["syncCto_Type"] = $arrData['sync_options'];
        } else {
            $arrSyncSettings["syncCto_Type"] = array();
        }

        // Database.
        if (isset($arrData['database_check'])) {
            $arrSyncSettings["syncCto_SyncDatabase"] = true;
        } else {
            $arrSyncSettings["syncCto_SyncDatabase"] = false;
        }

        // Database - tl_files
        if (isset($arrData['tl_files_check'])) {
            $arrSyncSettings["syncCto_SyncTlFiles"] = true;
        } else {
            $arrSyncSettings["syncCto_SyncTlFiles"] = false;
        }

        // Systemoperation execute.
        if (isset($arrData['systemoperations_check']) && isset($arrData['systemoperations_maintenance'])) {
            $arrSyncSettings["syncCto_Systemoperations_Maintenance"] = $arrData['systemoperations_maintenance'];
        } else {
            $arrSyncSettings["syncCto_Systemoperations_Maintenance"] = array();
        }

        // Attention flag.
        if (isset($arrData['attentionFlag'])) {
            $arrSyncSettings["syncCto_AttentionFlag"] = true;
        } else {
            $arrSyncSettings["syncCto_AttentionFlag"] = false;
        }

        // Error msg.
        if (isset($arrData['localconfig_error'])) {
            $arrSyncSettings["syncCto_ShowError"] = true;
        } else {
            $arrSyncSettings["syncCto_ShowError"] = false;
        }

        // Save Session.
        Session::getInstance()->set("syncCto_SyncSettings_" . $id->getId(), $arrSyncSettings);

        // Check the vars.
        $this->objSyncCtoHelper->checkSubmit(array(
            'postUnset'   => array('start_sync'),
            'error'       => array(
                'key'     => 'syncCto_submit_false',
                'message' => $GLOBALS['TL_LANG']['ERR']['no_functions']
            ),
            'redirectUrl' => Environment::get('base') . "contao/main.php?do=synccto_clients&amp;table=tl_syncCto_clients_syncTo&amp;act=start&amp;step=0&amp;id=" . $id->getId()
        ),
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
        $id              = ModelId::fromSerialized(Input::get('cid'));
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
        Session::getInstance()->set("syncCto_SyncSettings_" . $id->getId(), $arrSyncSettings);

        $this->objSyncCtoHelper->checkSubmit(array(
            'postUnset'   => array('start_sync'),
            'error'       => array(
                'key'     => 'syncCto_submit_false',
                'message' => $GLOBALS['TL_LANG']['ERR']['missing_tables']
            ),
            'redirectUrl' => Environment::get('base') . "contao/main.php?do=synccto_clients&amp;table=tl_syncCto_clients_syncTo&amp;act=start&amp;step=0&amp;id=" . $id->getId()
        ),
            $arrSyncSettings
        );
    }
}
