<?php declare(strict_types=1);

namespace MenAtWork\SyncCto\Services;

use MenAtWork\SyncCto\StepHandling\SyncDataContainer;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class ContentDataFactory
 *
 * @package MenAtWork\SyncCto\Services
 */
class SyncDataFactory
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * FrontendDataFactory constructor.
     *
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Create a new container.
     *
     * @return SyncDataContainer
     */
    public function createNewContainer(): SyncDataContainer
    {
        return new SyncDataContainer();
    }

    /**
     * Load container from session.
     *
     * @return SyncDataContainer
     */
    public function loadContainer(): SyncDataContainer
    {
        $sessionData = $this->session->get('syncCto_Content');
        if (empty($sessionData)) {
            return $this->createNewContainer();
        }

        $object = \unserialize($sessionData, [SyncDataContainer::class]);
        if (null == $object || SyncDataContainer::class != get_class($object)) {
            return $this->createNewContainer();
        }

        return $object;
    }

    /**
     * Save container back to session.
     *
     * @param SyncDataContainer $container
     */
    public function saveContainer(SyncDataContainer $container): void
    {
        $this->session->set('syncCto_Content', serialize($container));
    }
}