<?php


namespace MenAtWork\SyncCto\Services;


use Contao\CoreBundle\Framework\Adapter;
use Contao\StringUtil;
use MenAtWork\SyncCto\Steps\ContentData;
use MenAtWork\SyncCto\Steps\RunnerData;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class RunnerDataFactory
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var Adapter|StringUtil
     */
    private $stringUtil;

    /**
     * FrontendDataFactory constructor.
     *
     * @param SessionInterface $session
     * @param Adapter          $stringUtil
     */
    public function __construct(SessionInterface $session, Adapter $stringUtil)
    {
        $this->session    = $session;
        $this->stringUtil = $stringUtil;
    }

    /**
     * @return RunnerData
     */
    public function createNewContainer()
    {
        return new RunnerData();
    }

    /**
     * @return RunnerData
     */
    public function loadContainer()
    {
        $sessionData = $this->session->get('syncCto_Content');
        if (empty($sessionData)) {
            return $this->createNewContainer();
        }

        $object = $this->stringUtil::deserialize($sessionData);
        if (null == $object || get_class($object) != RunnerData::class) {
            return $this->createNewContainer();
        }

        return $object;
    }

    /**
     * @param RunnerData $container
     */
    public function saveContainer(RunnerData $container)
    {
        $this->session->set('syncCto_Content', serialize($container));
    }
}