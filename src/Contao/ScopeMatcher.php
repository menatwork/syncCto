<?php

namespace MenAtWork\SyncCto\Contao;

use Contao\CoreBundle\Routing\ScopeMatcher as BaseScopeMatcher;
use Symfony\Component\HttpFoundation\RequestStack;

class ScopeMatcher
{
    private RequestStack     $requestStack;
    private BaseScopeMatcher $scopeMatcher;

    public function __construct(RequestStack $requestStack, BaseScopeMatcher $scopeMatcher)
    {
        $this->requestStack = $requestStack;
        $this->scopeMatcher = $scopeMatcher;
    }

    public function isBackend(): bool
    {
        return $this->scopeMatcher->isBackendRequest($this->requestStack->getCurrentRequest());
    }

    public function isFrontend(): bool
    {
        return $this->scopeMatcher->isFrontendRequest($this->requestStack->getCurrentRequest());
    }
}