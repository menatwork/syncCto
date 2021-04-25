<?php


namespace MenAtWork\SyncCto\Services;


use Contao\CoreBundle\Framework\Adapter;
use Contao\StringUtil;
use MenAtWork\SyncCto\Steps\ContentData;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ContentDataFactory
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
     * @return ContentData
     */
    public function createNewContainer()
    {
        return new ContentData();
    }

    /**
     * @return ContentData
     */
    public function loadContainer()
    {
        $sessionData = $this->session->get('syncCto_Content');
        if (empty($sessionData)) {
            return $this->createNewContainer();
        }

        $object = $this->stringUtil::deserialize($sessionData);
        if (null == $object || get_class($object) != ContentData::class) {
            return $this->createNewContainer();
        }

        return $object;
    }

    /**
     * @param ContentData $container
     */
    public function saveContainer(ContentData $container)
    {
        $this->session->set('syncCto_Content', serialize($container));
    }
}