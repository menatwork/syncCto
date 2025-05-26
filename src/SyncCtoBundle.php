<?php

namespace MenAtWork\SyncCto;

use MenAtWork\SyncCto\DependencyInjection\SyncCtoExtension;
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

    public function getContainerExtension(): \Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        return new SyncCtoExtension();
    }
}
