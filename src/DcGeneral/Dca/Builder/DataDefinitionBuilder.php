<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2023 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MenAtWork\SyncCto\DcGeneral\Dca\Builder;

use Contao\BackendUser;
use Contao\Message;
use ContaoCommunityAlliance\DcGeneral\Contao\Dca\Builder\Legacy\DcaReadingDataDefinitionBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DataProviderDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;

/**
 * Build the container config from legacy DCA syntax.
 */
class DataDefinitionBuilder extends DcaReadingDataDefinitionBuilder
{
    /**
     * Build a data definition and store it into the environments container.
     *
     * @param ContainerInterface       $container The data definition container to populate.
     *
     * @param BuildDataDefinitionEvent $event     The event that has been triggered.
     *
     * @return void
     */
    public function build(ContainerInterface $container, BuildDataDefinitionEvent $event)
    {
        $providerDefinition   = $container->getDataProviderDefinition();
        $palettesDefinition   = $container->getPalettesDefinition()->getPaletteByName('default');
        $backendUser          = BackendUser::getInstance();
        $groupRightForceFiles = $backendUser->syncCto_force_dbafs_overwrite;
        $groupRightForceDiff  = $backendUser->syncCto_force_diff;

        // Check if we have to force the file overwrite.
        if (
            (
                $groupRightForceFiles == true
                || (
                        \is_array($groupRightForceFiles)
                        && !empty($groupRightForceFiles[0])
                        && $groupRightForceFiles[0] == true
                )
            )
            && $palettesDefinition
            && $palettesDefinition->hasLegend('table')
            && $palettesDefinition->getLegend('table')->hasProperty('tl_files_check')
        ) {
            Message::addInfo('tl_Files overwrite is per default enabled.');
            $property = $palettesDefinition
                ->getLegend('table')
                ->getProperty('tl_files_check');
            $palettesDefinition
                ->getLegend('table')
                ->removeProperty($property);
        }

        // Check if we have to force the diff.
        if (
            ($groupRightForceDiff == true || (\is_array($groupRightForceDiff) && !empty($groupRightForceDiff[0])))
            && $palettesDefinition
            && $palettesDefinition->hasLegend('table')
            && $palettesDefinition->getLegend('table')->hasProperty('database_pages_check')
        ) {
            Message::addInfo('Diff-Sync is per default enabled.');
            $property = $palettesDefinition
                ->getLegend('table')
                ->getProperty('database_pages_check');
            $palettesDefinition
                ->getLegend('table')
                ->removeProperty($property);
        }

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
