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

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetEditModeButtonsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\Event\PrePersistModelEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use SyncCto\Contao\DataProvider\General\AllowBackButtonOnlyListener;
use SyncCto\DcGeneral\ActionHandler\BackupEditHandler;
use SyncCto\DcGeneral\ActionHandler\SyncEditHandler;
use SyncCto\DcGeneral\Dca\Builder\DataDefinitionBuilder;
use SyncCto\DcGeneral\Events\Backup\Database;
use SyncCto\DcGeneral\Events\Backup\File;
use SyncCto\DcGeneral\Events\Sync\From;
use SyncCto\DcGeneral\Events\Sync\To;

$result = array();

// Overall events.
$result[BuildDataDefinitionEvent::NAME] = array(
    array(
        array(new DataDefinitionBuilder(), 'process'),
        DataDefinitionBuilder::PRIORITY
    ),

);

// Register the action handler for the backend scope only.
if ('BE' === TL_MODE) {
    $result[DcGeneralEvents::ACTION] = array(
        array(
            array(new SyncEditHandler(), 'handleEvent'),
            SyncEditHandler::PRIORITY
        ),
        array(
            array(new BackupEditHandler(), 'handleEvent'),
            BackupEditHandler::PRIORITY
        ),
    );
}

// Register load listener for backend scope only.
if ('BE' === TL_MODE) {
    $result[GetEditModeButtonsEvent::NAME] = array (
        array(
            array(new To(), 'addButton'),
            To::PRIORITY
        ),
        array(
            array(new From(), 'addButton'),
            From::PRIORITY
        ),
        array(
            array(new Database(), 'addButtonBackup'),
            Database::PRIORITY
        ),
        array(
            array(new Database(), 'addButtonRestore'),
            Database::PRIORITY
        ),
        array(
            array(new File(), 'addButtonBackup'),
            File::PRIORITY
        ),
        array(
            array(new File(), 'addButtonRestore'),
            File::PRIORITY
        )
    );
}

// Register save listener for backend scope only.
if ('BE' === TL_MODE) {
    $result[PrePersistModelEvent::NAME] = array (
        array(
            array(new To(), 'submit'),
            To::PRIORITY
        ),
        array(
            array(new From(), 'submit'),
            From::PRIORITY
        ),
        array(
            array(new Database(), 'submitBackup'),
            Database::PRIORITY
        ),
        array(
            array(new Database(), 'submitRestore'),
            Database::PRIORITY
        ),
        array(
            array(new File(), 'submitBackup'),
            File::PRIORITY
        ),
        array(
            array(new File(), 'submitRestore'),
            File::PRIORITY
        )
    );
}

// Register widget listener for backend scope only.
if ('BE' === TL_MODE) {
    $result[GetPropertyOptionsEvent::NAME] = array (
        array(
            array(new Database(), 'databaseTablesRecommended'),
            Database::PRIORITY
        ),
        array(
            array(new Database(), 'databaseTablesNoneRecommendedWithHidden'),
            Database::PRIORITY
        )
    );
}

// Register global buttons listener for backend scope only.
if ('BE' === TL_MODE) {
    $result[GetGlobalButtonsEvent::NAME] = array (
        array(
            array(new AllowBackButtonOnlyListener('tl_syncCto_backup_db'), 'handle'),
            AllowBackButtonOnlyListener::PRIORITY
        ),
        array(
            array(new AllowBackButtonOnlyListener('tl_syncCto_backup_file'), 'handle'),
            AllowBackButtonOnlyListener::PRIORITY
        ),
        array(
            array(new AllowBackButtonOnlyListener('tl_syncCto_restore_db'), 'handle'),
            AllowBackButtonOnlyListener::PRIORITY
        ),
        array(
            array(new AllowBackButtonOnlyListener('tl_syncCto_restore_file'), 'handle'),
            AllowBackButtonOnlyListener::PRIORITY
        )
    );
}

return $result;
