<?php

use Contao\Backend;
use Contao\System;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */
class SyncCtoStats extends Backend
{
    /*
     * Constants
     */

    const SYNCDIRECTION_TO    = 1;
    const SYNCDIRECTION_FROM  = 2;
    const SYNCDIRECTION_CHECK = 3;

    /**
     * Instance
     *
     * @var SyncCtoStats
     */
    protected static $objInstance = null;

    /**
     * Key for the session name
     *
     * @var string
     */
    protected $syncCtoStats = "syncCtoStats";

    /**
     * Data
     *
     * @var array
     */
    protected $arrData = array();

    /**
     * List with skipped post values
     *
     * @var array
     */
    protected $arrSkippedValues = array(
        'FORM_SUBMIT',
        'REQUEST_TOKEN',
        'FORM_INPUTS',
        'start_sync'
    );
    /**
     * @var mixed|\Symfony\Component\HttpFoundation\Session\SessionInterface|null
     */
    protected mixed $session;

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();

        $container = System::getContainer();
        /** @var RequestStack $requestStack */
        $requestStack = $container->get('request_stack');
        $this->session = $requestStack->getSession();

        $this->loadSession();
    }

    /**
     * Get current instance
     *
     * @return SyncCtoStats
     */
    public static function getInstance()
    {
        if (is_null(self::$objInstance)) {
            self::$objInstance = new self();
        }

        return self::$objInstance;
    }

    // Session functions -------------------------------------------------------

    /**
     * Load the session data
     */
    protected function loadSession()
    {
        $this->arrData = $this->session->get($this->syncCtoStats);
    }

    /**
     * Save the session data
     */
    protected function saveSession()
    {
        $this->session->set($this->syncCtoStats, $this->arrData);
    }

    /**
     * Set the flag that a sync starts
     */
    protected function setStartFlag($intId)
    {
        $this->arrData['startFlag'] = true;
        $this->arrData['id'] = $intId;
        $this->saveSession();
    }

    /**
     * Get the id from session
     *
     * @return int
     */
    protected function getStartFlagId()
    {
        if ($this->arrData['startFlag'] == true) {
            return $this->arrData['id'];
        }

        return false;
    }

    /**
     * Reset the flag
     */
    protected function resetStartFlag()
    {
        $this->arrData['startFlag'] = false;
        $this->arrData['id'] = false;
        $this->saveSession();
    }

    // core functions ----------------------------------------------------------

    /**
     * Add a new sync to the stats
     *
     * @param int   $intUser    id of user
     * @param int   $intClient  id of client
     * @param int   $intStart   time
     * @param array $arrOptions array with all options
     */
    public function addStartStat($intUser, $intClient, $intStart, $arrOptions, $intDirection)
    {
        // ToDo: Fix it:
        return 1;

        // Clean sync options
        $arrSyncOptions = array();
        if (isset($arrOptions['post_data'])
            && is_array($arrOptions['post_data'])
            && !empty($arrOptions['post_data'])
        ) {
            foreach ($arrOptions['post_data'] as $key => $value) {
                if (in_array($key, $this->arrSkippedValues)) {
                    continue;
                }

                $arrSyncOptions[$key] = $value;
            }
        }

        // Build set array
        $arrSet = array(
            'tstamp'         => time(),
            'client_id'      => $intClient,
            'sync_user'      => $intUser,
            'sync_start'     => $intStart,
            'sync_direction' => $intDirection,
            'sync_options'   => $arrSyncOptions,
        );

        // Insert new row
        $objResult = $this->Database
            ->prepare("INSERT INTO tl_synccto_stats %s")
            ->set($arrSet)
            ->execute()
        ;

        $this->setStartFlag($objResult->insertId);
    }

    /**
     * Add the end time to the stats
     *
     * @param int $intEnd time
     */
    public function addEndStat($intEnd)
    {
        // ToDo: Fix it:
        return;

        $intId = $this->getStartFlagId();

        if ($intId !== false) {
            $this->Database
                ->prepare('UPDATE tl_synccto_stats %s WHERE id=?')
                ->set(array('sync_end' => $intEnd))
                ->execute($intId)
            ;

            $this->resetStartFlag();
        }
    }

    /**
     * Add the end time to the stats
     *
     * @param int $intEnd time
     */
    public function addAbortStat($intEnd, $intStep)
    {
        // ToDo: Fix it:
        return;

        $intId = $this->getStartFlagId();

        if ($intId !== false) {
            $this->Database
                ->prepare('UPDATE tl_synccto_stats %s WHERE id=?')
                ->set(
                    array(
                        'sync_abort'      => $intEnd,
                        'sync_abort_step' => $intStep,
                    )
                )
                ->execute($intId)
            ;

            $this->resetStartFlag();
        }
    }
}