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
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\Database;
use Contao\Database\Result;
use Contao\System;
use ErrorException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use SyncCtoCommunicationClient;

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
     * @var int|string
     */
    protected string|int $clientID;

    /**
     * Holds the current client data from the database.
     *
     * @var Result
     */
    protected Result $client;


    /**
     * Holds the base urls.
     *
     * @var string
     */
    protected string $clientBaseUrl;

    /**
     * Flag if every things works.
     *
     * @var bool
     */
    protected bool $success = false;

    /**
     * Flag id we have an error.
     *
     * @var bool
     */
    protected bool $hasFatalErrors = false;

    /**
     * The current state.
     *
     * @var int
     */
    protected int $state = 0;

    /**
     * Holds the error msg.
     *
     * @var string
     */
    protected string $errorMsg = '';

    /**
     * Holds the msg.
     *
     * @var string
     */
    protected string $msg = '';

    /**
     * @var ContaoCsrfTokenManager
     */
    private ?object $tokenManager;

    /**
     * Ping constructor.
     */
    public function __construct()
    {
        // Init some more things.
        Controller::loadLanguageFile('tl_synccto_clients');

        /** @var ContaoCsrfTokenManager $tokenManager */
        $this->tokenManager = System::getContainer()->get('contao.csrf.token_manager');
    }

    /**
     * Add the error and set all flags.
     *
     * @param string $msg The messages.
     */
    protected function addFatalError(string $msg): void
    {
        $this->success = false;
        $this->hasFatalErrors = true;
        $this->errorMsg = $msg;
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
    protected function addState(int $state, string $msg, string $errorMsg): void
    {
        $this->success = true;
        $this->state = $state;
        $this->msg = $msg;
        $this->errorMsg = $errorMsg;
    }

    /**
     * Load the data for the client from the database.
     *
     * @return bool True means we have some data, false no data found.
     */
    protected function loadClient(): bool
    {
        // Load Client from database.
        $objClient = Database::getInstance()
                             ->prepare('SELECT * FROM tl_synccto_clients WHERE id = ?')
                             ->limit(1)
                             ->execute($this->clientID)
        ;

        // Check if a client was loaded
        if ($objClient->numRows == 0) {
            return false;
        }

        $this->client = $objClient;

        return true;
    }

    /**
     * Set up the request class.
     *
     * @param string $url The GET parameter.
     *
     * @return ResponseInterface
     *
     * @throws GuzzleException
     */
    protected function sendRequest(string $url): ResponseInterface
    {
        $this->clientBaseUrl = sprintf(
            '%s:%s/ctoCommunication%s',
            $this->client->address,
            $this->client->port,
            $url
        );

        $client = new Client();

        $options = [
            'timeout'         => 5,
            'connect_timeout' => 5,
            'http_errors'     => false
        ];

        if ($this->client->http_auth) {
            $options['auth'] = [
                $this->client->http_username,
                $this->client->http_password
            ];
        }

        return $request = $client->get(
            $this->clientBaseUrl,
            $options
        );
    }


    /**
     * Ping the current client status
     *
     * @param int|string $clientId The client id to check.
     *
     * @return array An array with th status information.
     *
     * @throws GuzzleException
     */
    public function pingClientStatus(int|string $clientId): array
    {
        // Check if we have the id. If not end here.
        $this->clientID = intval($clientId);

        // Check if we have the entry in the database.
        if (!$this->loadClient()) {
            $this->addFatalError('Unknown client id.');
            return $this->output();
        }

        try {
            $this->pingCtoCom();
            $this->pingSyncCtoSystem();

            // State: Green => All systems ready.
            $this->addState(4, $GLOBALS['TL_LANG']['tl_synccto_clients']['state']['green'], '');
        } catch (ErrorException $exc) {
            // Nothing to do, just don't run the default exception.
        } catch (Exception $exc) {
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
     * @throws ErrorException
     * @throws GuzzleException
     */
    protected function pingCtoCom(): void
    {
        $response = $this->sendRequest('?act=ping');
        if ($response->getStatusCode() != '200') {
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
    protected function pingSyncCtoSystem(): void
    {
        $objSyncCtoClient = SyncCtoCommunicationClient::getInstance();
        $objSyncCtoClient->setClientBy($this->clientID);

        try {
            $objSyncCtoClient->startConnection();

            // Check Version of syncCto.
            try {
                $mixVersion = $objSyncCtoClient->getVersionSyncCto();
                if (strlen($mixVersion) == 0) {
                    throw new Exception('Missing syncCto Version.');
                }
            } catch (Exception $exc) {
                // State: Blue => SyncCto missing.
                $this->addState
                (
                    2,
                    $GLOBALS['TL_LANG']['tl_synccto_clients']['state']['blue'],
                    $exc->getMessage()
                );

                throw new ErrorException();
            }

            $objSyncCtoClient->stopConnection();
        } catch (Exception $exc) {
            // State: Orange => Key Error.
            $this->addState
            (
                3,
                $GLOBALS['TL_LANG']['tl_synccto_clients']['state']['orange'],
                $exc->getMessage()
            );

            throw new ErrorException();
        }
    }

    /**
     * Output function for the ajax request.
     *
     * @return array An array with the status information.
     */
    protected function output(): array
    {
        // Build the basic array.
        return [
            'success' => $this->hasFatalErrors ? true : $this->success,
            'value'   => $this->state ?? 0,
            'error'   => $this->errorMsg ?? '',
            'msg'     => $this->msg ?? $GLOBALS['TL_LANG']['tl_synccto_clients']['state']['gray'],
            'token'   => $this->tokenManager->getDefaultTokenValue()
        ];
    }
}
