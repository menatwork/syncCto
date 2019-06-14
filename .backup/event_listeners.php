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
use MenAtWork\SyncCto\DcGeneral\ActionHandler\BackupEditHandler;
use MenAtWork\SyncCto\DcGeneral\ActionHandler\SyncEditHandler;
use MenAtWork\SyncCto\DcGeneral\Dca\Builder\DataDefinitionBuilder;

/** TODO: Replace this file with the symfony form. */
return [];

$result = array();

// Overall events.
//$result[BuildDataDefinitionEvent::NAME] = array(
//    array(
//        array(new DataDefinitionBuilder(), 'process'),
//        DataDefinitionBuilder::PRIORITY
//    ),
//
//);

// Be only events.
//if ('BE' === TL_MODE) {
//    $result[DcGeneralEvents::ACTION] = array(
//        array(
//            array(new SyncEditHandler(), 'handleEvent'),
//            SyncEditHandler::PRIORITY
//        ),
//        array(
//            array(new BackupEditHandler(), 'handleEvent'),
//            BackupEditHandler::PRIORITY
//        ),
//    );
//}

return $result;
