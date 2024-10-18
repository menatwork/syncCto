<?php

namespace MenAtWork\SyncCto;

use MenAtWork\SyncCto\DependencyInjection\SyncCtoExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class MultiColumnWizardBundle
 *
 * @package MenAtWork\SyncCtoBundle
 */
class SyncCtoBundle extends Bundle
{
    /**
     * @var string
     */
    protected $name = 'SyncCto';

    /**
     * Returns the container extension that should be implicitly loaded.
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new SyncCtoExtension();
    }
}
