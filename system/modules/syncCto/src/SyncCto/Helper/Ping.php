<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2016
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

namespace SyncCto\Helper;

use Contao\Controller;
use Contao\Database;
use Contao\Input;
use Contao\Request;
use SyncCto\Contao\Communicator\Client;

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
     * @var \Database\Result
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
     * Get the client ID from post.
     *
     * @return bool Return true if we have a number or false on error.
     */
    protected function loadClientId()
    {
        $clientId = Input::post('clientID');

        if (empty($clientId)) {
            return false;
        }

        $this->clientID = (int) $clientId;

        return true;
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
            ->prepare('SELECT * FROM tl_synccto_clients WHERE id = %s')
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
        $this->request = new Request();

        if ($this->client->http_auth == true) {
            $this->request->username = $this->client->http_username;
            $this->request->password = \Encryption::decrypt($this->client->http_password);
        }

        // Build base link.
        $this->client->path = \preg_replace("/\/\z/i", "", $this->client->path);
        $this->client->path = \preg_replace("/ctoCommunication.php\z/", "", $this->client->path);

        $this->clientBaseUrl = $this->client->address . ":" . $this->client->port;
        if (\strlen($this->client->path)) {
            $this->clientBaseUrl .= $this->client->path;
        }
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
     * @param string $strAction
     */
    public function pingClientStatus($strAction)
    {
        // Close the session handling.
        \session_write_close();

        // Init some more things.
        Controller::loadLanguageFile('tl_synccto_clients');

        // Check if the current call is one for use.
        if ($strAction != 'syncCtoPing') {
            return;
        }

        // Check if we have the id. If not end here.
        if (!$this->loadClientId()) {
            $this->addFatalError('Missing client id.');
            $this->output();
        }

        // Check if we have the entry in the database.
        if (!$this->loadClient()) {
            $this->addFatalError('Unknown client id.');
            $this->output();
        }

        // Run all pings.
        $this->ping();
    }

    /**
     * Run all sub tasks for the ping.
     */
    protected function ping()
    {
        try {
            $this->initRequest();
            $this->pingContaoBackend();
            $this->pingCtoCom();
            $this->pingSyncCtoSystem();

            // State: Green => All systems ready.
            $this->addState(4, $GLOBALS['TL_LANG']['tl_synccto_clients']['state']['green'], '');
        } catch (\Exception $exc) {
            // Error.
            $this->addFatalError($exc->getMessage() . " " . $exc->getFile() . " on " . $exc->getLine());
        }

        $this->output();
    }

    /**
     * Ping the Contao Backend.
     */
    protected function pingContaoBackend()
    {
        $this->sendRequest('/contao/index.php');
        if ($this->request->code != '200') {
            $this->addState
            (
                1,
                $GLOBALS['TL_LANG']['tl_synccto_clients']['state']['red'],
                'Missing contao.'
            );
            $this->output();
        }
    }

    /**
     * Ping the ctoCom class.
     */
    protected function pingCtoCom()
    {
        $this->sendRequest('/ctoCommunication.php?act=ping');
        if ($this->request->code != '200') {
            $this->addState
            (
                2,
                $GLOBALS['TL_LANG']['tl_synccto_clients']['state']['blue'],
                'Missing ctoCommunication.php'
            );
            $this->output();
        }
    }

    /**
     * Ping the SyncCto sub system.
     */
    protected function pingSyncCtoSystem()
    {
        $objSyncCtoClient = Client::getInstance();
        $objSyncCtoClient->setClientBy($this->clientID);

        try {
            $objSyncCtoClient->startConnection();

            // Check Version of syncCto.
            try {
                $mixVersion = $objSyncCtoClient->getVersionSyncCto();
                if (\strlen($mixVersion) == 0) {
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
                $this->output();
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
            $this->output();
        }
    }

    /**
     * Output function for the ajax request.
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

        echo \json_encode($output);
        exit();
    }
}
