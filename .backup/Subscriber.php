<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2015
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

namespace MenAtWork\SyncCto\DcGeneral\Events;

use ContaoCommunityAlliance\Contao\EventDispatcher\Event\CreateEventDispatcherEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetEditModeButtonsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PrePersistModelEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


/**
 * Central event subscriber implementation.
 *
 * @package MetaModels\DcGeneral\Events
 *
 *          TODO: Can be removed.
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
        if (TL_MODE == 'FE') {
            return;
        }

        // Get the event dispatcher from the current event.
        $dispatcher = $event->getEventDispatcher();

        /*
         * Save and load callbacks.
         */

//        self::registerListeners(
//            array(
////                GetEditModeButtonsEvent::NAME
////                => array(new To(), 'addButton'),
////                PrePersistModelEvent::NAME
////                => array(new To(), 'submit')
//            ),
//            $dispatcher,
//            To::PRIORITY
//        );

//        self::registerListeners(
//            array(
////                GetEditModeButtonsEvent::NAME
////                => array(new From(), 'addButton'),
////                PrePersistModelEvent::NAME
////                => array(new From(), 'submit')
//            ),
//            $dispatcher,
//            From::PRIORITY
//        );

//        self::registerListeners(
//            array(
//                GetEditModeButtonsEvent::NAME
//                => array(new Database(), 'addButtonBackup'),
//                PrePersistModelEvent::NAME
//                => array(new Database(), 'submitBackup')
//            ),
//            $dispatcher,
//            Database::PRIORITY
//        );
//
//        self::registerListeners(
//            array(
//                GetEditModeButtonsEvent::NAME
//                => array(new Database(), 'addButtonRestore'),
//                PrePersistModelEvent::NAME
//                => array(new Database(), 'submitRestore')
//            ),
//            $dispatcher,
//            Database::PRIORITY
//        );

//        self::registerListeners(
//            array(
//                GetEditModeButtonsEvent::NAME
//                => array(new File(), 'addButtonBackup'),
//                PrePersistModelEvent::NAME
//                => array(new File(), 'submitBackup')
//            ),
//            $dispatcher,
//            File::PRIORITY
//        );
//
//        self::registerListeners(
//            array(
//                GetEditModeButtonsEvent::NAME
//                => array(new File(), 'addButtonRestore'),
//                PrePersistModelEvent::NAME
//                => array(new File(), 'submitRestore')
//            ),
//            $dispatcher,
//            File::PRIORITY
//        );

        /*
         * Data callback for widgets.
         */

//        self::registerListeners(
//            array(
//                GetPropertyOptionsEvent::NAME => array(new Database(), 'databaseTablesRecommended')
//            ),
//            $dispatcher
//        );
//
//        self::registerListeners(
//            array(
//                GetPropertyOptionsEvent::NAME => array(new Database(), 'databaseTablesNoneRecommendedWithHidden')
//            ),
//            $dispatcher
//        );
    }

    /**
     * Register multiple event listeners.
     *
     * @param array                    $listeners  The listeners to register.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher to which the events shall be registered.
     *
     * @param int                      $priority   The priority.
     *
     * @return void
     */
    public static function registerListeners($listeners, $dispatcher, $priority = 200)
    {
        foreach ($listeners as $event => $listener) {
            $dispatcher->addListener($event, $listener, $priority);
        }
    }

}
