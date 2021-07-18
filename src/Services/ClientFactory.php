<?php

namespace MenAtWork\SyncCto\Services;

use MenAtWork\SyncCto\Clients\IClient;
use Doctrine\DBAL\Connection;
use MenAtWork\SyncCto\Clients\IRemote;
use MenAtWork\SyncCto\Clients\Locale;
use MenAtWork\SyncCto\Clients\Remote;

/**
 * Class ClientFactory
 */
class ClientFactory
{
    /**
     * @var Connection
     */
    private $databaseConnection;

    /**
     * ClientFactory constructor.
     *
     * @param Connection $databaseConnection
     */
    public function __construct(Connection $databaseConnection)
    {
        $this->databaseConnection = $databaseConnection;
    }

    /**
     * @return \MenAtWork\SyncCto\Clients\IClient
     */
    public function getLocaleClient(): IClient
    {
        $locale = new Locale();
        $locale->setTitle('Locale');

        return $locale;
    }

    /**
     * @param int $clientId
     *
     * @return \MenAtWork\SyncCto\Clients\IClient
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function getRemoteClient(int $clientId): IClient
    {
        $client = new Remote();
        $this->setClientBy($clientId, $client);

        return $client;
    }

    /**
     * Set client by id.
     *
     * @param int     $id
     *
     * @param IRemote $client
     *
     * @return void
     *
     * @throws \Doctrine\DBAL\Exception
     */
    protected function setClientBy(int $id, IRemote $client): void
    {
        $clientData = $this->databaseConnection
            ->createQueryBuilder()
            ->select('c.*')
            ->from('tl_synccto_clients', 'c')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->execute();

        $clientData = $clientData->fetchAssociative();
        if (0 === count($clientData)) {
            throw new \RuntimeException($GLOBALS['TL_LANG']['ERR']['unknown_client']);
        }

        $client->setTitle($clientData['title']);

        $strUrl = $clientData['address'] . ":" . ($clientData['port'] ?? 80) . '/ctoCommunication';
        $client->setUrl($strUrl);
        $client->setPort($clientData['port']);
        $client->setApiKey($clientData['apikey']);
        $client->setCodifyEngine($clientData['codifyengine']);
        if (true == $clientData['http_auth']) {
            $client->setHttpAuth($clientData['http_username'], $clientData['http_password']);
        }

        // Set debug modus for ctoCom.
        if ($GLOBALS['TL_CONFIG']['syncCto_debug_mode'] == true) {
//            $client->setDebug(true);
//            $client->setMeasurement(true);
//            $client->setFileDebug($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['debug'],
//                "CtoComDebug.txt"));
//            $client->setFileMeasurement($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['debug'],
//                "CtoComMeasurement.txt"));
        }
    }
}