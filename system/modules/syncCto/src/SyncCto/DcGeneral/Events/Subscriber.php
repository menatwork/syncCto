<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2015
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

namespace SyncCto\DcGeneral\Events;

use ContaoCommunityAlliance\Contao\EventDispatcher\Event\CreateEventDispatcherEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetEditModeButtonsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PrePersistModelEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use SyncCto\DcGeneral\Events\Backup\Database;
use SyncCto\DcGeneral\Events\Backup\File;
use SyncCto\DcGeneral\Events\Sync\From;
use SyncCto\DcGeneral\Events\Sync\To;

/**
 * Central event subscriber implementation.
 *
 * @package MetaModels\DcGeneral\Events
 */
class Subscriber
{
    /**
     * Register all listeners to handle creation of a data container.
     *
     * @param CreateEventDispatcherEvent $event The event.
     *
     * @return void
     */
    public static function registerEvents(CreateEventDispatcherEvent $event)
    {
        // Only register the events for the backend.
        if(TL_MODE == 'FE'){
            return;
        }

        // Get the event dispatcher from the current event.
        $dispatcher = $event->getEventDispatcher();

        /*
         * Save and load callbacks.
         */

        self::registerListeners(
            array(
                GetEditModeButtonsEvent::NAME
                => array(new To(), 'addButton'),
                PrePersistModelEvent::NAME
                => array(new To(), 'submit')
            ),
            $dispatcher,
            array('tl_syncCto_clients_syncTo'),
            To::PRIORITY
        );

        self::registerListeners(
            array(
                GetEditModeButtonsEvent::NAME
                => array(new From(), 'addButton'),
                PrePersistModelEvent::NAME
                => array(new From(), 'submit')
            ),
            $dispatcher,
            array('tl_syncCto_clients_syncFrom'),
            From::PRIORITY
        );

        self::registerListeners(
            array(
                GetEditModeButtonsEvent::NAME
                => array(new Database(), 'addButtonBackup'),
                PrePersistModelEvent::NAME
                => array(new Database(), 'submitBackup')
            ),
            $dispatcher,
            array('tl_syncCto_backup_db'),
            Database::PRIORITY
        );

        self::registerListeners(
            array(
                GetEditModeButtonsEvent::NAME
                => array(new Database(), 'addButtonRestore'),
                PrePersistModelEvent::NAME
                => array(new Database(), 'submitRestore')
            ),
            $dispatcher,
            array('tl_syncCto_restore_db'),
            Database::PRIORITY
        );

        self::registerListeners(
            array(
                GetEditModeButtonsEvent::NAME
                => array(new File(), 'addButtonBackup'),
                PrePersistModelEvent::NAME
                => array(new File(), 'submitBackup')
            ),
            $dispatcher,
            array('tl_syncCto_backup_file'),
            File::PRIORITY
        );

        self::registerListeners(
            array(
                GetEditModeButtonsEvent::NAME
                => array(new File(), 'addButtonRestore'),
                PrePersistModelEvent::NAME
                => array(new File(), 'submitRestore')
            ),
            $dispatcher,
            array('tl_syncCto_restore_file'),
            File::PRIORITY
        );

        /*
         * Data callback for widgets.
         */

        self::registerListeners(
            array(
                GetPropertyOptionsEvent::NAME => array(new Database(), 'databaseTablesRecommended')
            ),
            $dispatcher,
            array('tl_syncCto_backup_db', 'database_tables_recommended')
        );

        self::registerListeners(
            array(
                GetPropertyOptionsEvent::NAME => array(new Database(), 'databaseTablesNoneRecommendedWithHidden')
            ),
            $dispatcher,
            array('tl_syncCto_backup_db', 'database_tables_none_recommended')
        );
    }

    /**
     * Register multiple event listeners.
     *
     * @param array                    $listeners  The listeners to register.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher to which the events shall be registered.
     *
     * @param string[]                 $suffixes   The suffixes for the event names to use.
     *
     * @param int                      $priority   The priority.
     *
     * @return void
     */
    public static function registerListeners($listeners, $dispatcher, $suffixes = array(), $priority = 200)
    {
        foreach ($listeners as $event => $listener) {
            $dispatcher->addListener($event, $listener, $priority);
        }
    }

}
