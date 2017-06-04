<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use SyncCto\DcGeneral\ActionHandler\SyncEditHandler;
use SyncCto\DcGeneral\Dca\Builder\DataDefinitionBuilder;

$result = array();

// Overall events.
$result[BuildDataDefinitionEvent::NAME] = array(
    array(
        array(new DataDefinitionBuilder(), 'process'),
        DataDefinitionBuilder::PRIORITY
    ),

);

// Be only events.
if ('BE' === TL_MODE) {
    $result[DcGeneralEvents::ACTION] = array(
        array(
            array(new SyncEditHandler(), 'handleEvent'),
            SyncEditHandler::PRIORITY
        ),

    );
}

return $result;