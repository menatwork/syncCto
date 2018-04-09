<?php

/**
 * This file is part of menatwork/synccto.
 *
 * (c) 2014-2018 MEN AT WORK.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    menatwork/synccto
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2014-2018 MEN AT WORK.
 * @license    https://github.com/menatwork/syncCto/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace SyncCto\DcGeneral\Dca\Builder;

use ContaoCommunityAlliance\DcGeneral\Contao\Dca\Builder\Legacy\DcaReadingDataDefinitionBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DataProviderDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;

/**
 * Build the container config from legacy DCA syntax.
 */
class DataDefinitionBuilder extends DcaReadingDataDefinitionBuilder
{
    const PRIORITY = -200;

    /**
     * Build a data definition and store it into the environments container.
     *
     * @param ContainerInterface $container The data definition container to populate.
     *
     * @param BuildDataDefinitionEvent $event The event that has been triggered.
     *
     * @return void
     */
    public function build(ContainerInterface $container, BuildDataDefinitionEvent $event)
    {
        $providerDefinition = $container->getDataProviderDefinition();

        // SyncTo setup.
        if ($providerDefinition->hasInformation('tl_syncCto_clients_syncTo')) {
            $this->setupDriver($providerDefinition, 'tl_syncCto_clients_syncTo');
        }

        // SyncFrom setup.
        if ($providerDefinition->hasInformation('tl_syncCto_clients_syncFrom')) {
            $this->setupDriver($providerDefinition, 'tl_syncCto_clients_syncFrom');
        }
    }

    /**
     * Setup some missing data for the data provider.
     *
     * @param DataProviderDefinitionInterface $providerDefinition
     *
     * @return void
     */
    private function setupDriver(DataProviderDefinitionInterface $providerDefinition, $table)
    {
        $providerInformation = $providerDefinition->getInformation($table);
        $providerInformation->setClassName('ContaoCommunityAlliance\DcGeneral\Data\NoOpDataProvider');
    }
}
