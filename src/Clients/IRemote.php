<?php declare(strict_types=1);

namespace MenAtWork\SyncCto\Clients;

/**
 * Interface IRemote
 *
 * @package MenAtWork\SyncCto\Clients
 */
interface IRemote extends IClient
{
    /**
     * @param string $url
     *
     * @return \MenAtWork\SyncCto\Clients\IRemote
     */
    public function setUrl(string $url): IRemote;

    /**
     * @param int $port
     *
     * @return \MenAtWork\SyncCto\Clients\IRemote
     */
    public function setPort(int $port): IRemote;

    /**
     * @param string $apikey
     *
     * @return \MenAtWork\SyncCto\Clients\IRemote
     */
    public function setApiKey(string $apikey): IRemote;

    /**
     * @param string $codifyEngine
     *
     * @return \MenAtWork\SyncCto\Clients\IRemote
     */
    public function setCodifyEngine(string $codifyEngine): IRemote;

    /**
     * @param string $http_username
     *
     * @param string $http_password
     *
     * @return \MenAtWork\SyncCto\Clients\IRemote
     */
    public function setHttpAuth(string $http_username, string $http_password): IRemote;
}