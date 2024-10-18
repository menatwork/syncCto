<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2016
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

namespace MenAtWork\SyncCto\Helper;

use Contao\Controller;
use Contao\Database;
use Contao\Database\Result;

/**
 * Class Ping
 *
 * @package MenAtWork\SyncCto\Helper
 */
class Ping
{
    /**
     * The current client ID.
     *
     * @var integer
     */
    protected $clientID;

    /**
     * Holds the current client data from the database.
     *
     * @var Result
     */
    protected $client;

    /**
     * Class for simple http request.
     *
     * @var \Request
     */
    protected $request;

    /**
     * Holds the base urls.
     *
     * @var string
     */
    protected $clientBaseUrl;

    /**
     * Flag if every things works.
     *
     * @var bool
     */
    protected $success = false;

    /**
     * Flag id we have an error.
     *
     * @var bool
     */
    protected $hasFatalErrors = false;

    /**
     * The current state.
     *
     * @var int
     */
    protected $state = 0;

    /**
     * Holds the error msg.
     *
     * @var string
     */
    protected $errorMsg = '';

    /**
     * Holds the msg.
     *
     * @var string
     */
    protected $msg = '';

    /**
     * Ping constructor.
     */
    public function __construct()
    {
        // Init some more things.
        Controller::loadLanguageFile('tl_synccto_clients');
    }

    /**
     * Add the error and set all flags.
     *
     * @param string $msg The messages.
     */
    protected function addFatalError($msg)
    {
        $this->success        = false;
        $this->hasFatalErrors = true;
        $this->errorMsg       = $msg;
    }

    /**
     * Add the state.
     *
     * @param int    $state    The id of the state.
     *
     * @param string $msg      The messages.
     *
     * @param string $errorMsg The error message.
     */
    protected function addState($state, $msg, $errorMsg)
    {
        $this->success  = true;
        $this->state    = $state;
        $this->msg      = $msg;
        $this->errorMsg = $errorMsg;
    }

    /**
     * Load the data for the client from the database.
     *
     * @return bool True means we have some data, false no data found.
     */
    protected function loadClient()
    {
        // Load Client from database.
        $objClient = Database::getInstance()
                              ->prepare('SELECT * FROM tl_synccto_clients WHERE id = ?')
                              ->limit(1)
                              ->execute($this->clientID);

        // Check if a client was loaded
        if ($objClient->numRows == 0) {
            return false;
        }

        $this->client = $objClient;

        return true;
    }

    /**
     * Setup the request class.
     */
    protected function initRequest()
    {
        // Setup request class.
        $this->request = new \Request();

        if ($this->client->http_auth == true) {
            $this->request->username = $this->client->http_username;
            $this->request->password = $this->client->http_password;
        }

        $this->clientBaseUrl = $this->client->address . ":" . $this->client->port . '/ctoCommunication';
    }

    /**
     * Send the request.
     *
     * @param $url
     */
    protected function sendRequest($url)
    {
        $this->request->send($this->clientBaseUrl . $url);
    }

    /**
     * Ping the current client status
     *
     * @param int $clientId The client id to check.
     *
     * @return array An array with th status information.
     */
    public function pingClientStatus($clientId)
    {
        // Check if we have the id. If not end here.
        $this->clientID = intval($clientId);

        // Check if we have the entry in the database.
        if (!$this->loadClient()) {
            $this->addFatalError('Unknown client id.');

            return $this->output();
        }

        try {
            $this->initRequest();
            $this->pingCtoCom();
            $this->pingSyncCtoSystem();

            // State: Green => All systems ready.
            $this->addState(4, $GLOBALS['TL_LANG']['tl_synccto_clients']['state']['green'], '');
        } catch (\ErrorException $exc) {
            // Nothing to do, just don't run the default exception.
        } catch (\Exception $exc) {
            // Error.
            $this->addFatalError($exc->getMessage() . " " . $exc->getFile() . " on " . $exc->getLine());
        }

        return $this->output();
    }

    /**
     * Ping the ctoCom class.
     *
     * @return void
     *
     * @throws \ErrorException
     */
    protected function pingCtoCom()
    {
        $this->sendRequest('?act=ping');
        if ($this->request->code != '200') {
            $this->addState
            (
                2,
                $GLOBALS['TL_LANG']['tl_synccto_clients']['state']['blue'],
                'Missing ctoCommunication'
            );

            throw new \ErrorException();
        }
    }

    /**
     * Ping the SyncCto sub system.
     *
     * @throws \Exception
     */
    protected function pingSyncCtoSystem()
    {
        $objSyncCtoClient = \SyncCtoCommunicationClient::getInstance();
        $objSyncCtoClient->setClientBy($this->clientID);

        try {
            $objSyncCtoClient->startConnection();

            // Check Version of syncCto.
            try {
                $mixVersion = $objSyncCtoClient->getVersionSyncCto();
                if (strlen($mixVersion) == 0) {
                    throw new \Exception('Missing syncCto Version.');
                }
            } catch (\Exception $exc) {
                // State: Blue => SyncCto missing.
                $this->addState
                (
                    2,
                    $GLOBALS['TL_LANG']['tl_synccto_clients']['state']['blue'],
                    $exc->getMessage()
                );

                throw new \ErrorException();
            }

            $objSyncCtoClient->stopConnection();
        } catch (\Exception $exc) {
            // State: Orange => Key Error.
            $this->addState
            (
                3,
                $GLOBALS['TL_LANG']['tl_synccto_clients']['state']['orange'],
                $exc->getMessage()
            );

            throw new \ErrorException();
        }
    }

    /**
     * Output function for the ajax request.
     *
     * @return array An array with the status information.
     */
    protected function output()
    {
        // Build the basic array.
        $output = array
        (
            'success' => false,
            'value'   => 0,
            'error'   => '',
            'msg'     => $GLOBALS['TL_LANG']['tl_synccto_clients']['state']['gray'],
            'token'   => REQUEST_TOKEN
        );

        // Add error.
        if ($this->hasFatalErrors) {
            $output['success'] = true;
            $output['error']   = $this->errorMsg;
        } else { // Or the default output.
            $output['success'] = $this->success;
            $output['value']   = $this->state;
            $output['error']   = $this->errorMsg;
            $output['msg']     = $this->msg;
        }

        return $output;
    }
}
