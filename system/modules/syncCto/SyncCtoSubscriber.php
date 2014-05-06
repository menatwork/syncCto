<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

use ContaoCommunityAlliance\Contao\EventDispatcher\Event\CreateEventDispatcherEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


/**
 * Central event subscriber implementation.
 *
 * @package MetaModels\DcGeneral\Events
 */
class SyncCtoSubscriber
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
        $dispatcher = $event->getEventDispatcher();

        // Save and load callbacks.
        self::registerListeners(
            array(
                \ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetEditModeButtonsEvent::NAME
                => array(new SyncCtoTableSyncTo(),'addButton'),
                \ContaoCommunityAlliance\DcGeneral\Event\PrePersistModelEvent::NAME
                => array(new SyncCtoTableSyncTo(), 'submit')
            ),
            $dispatcher,
            array('tl_syncCto_clients_syncTo'),
            SyncCtoTableSyncTo::PRIORITY
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
        $eventSuffix = '';
        foreach ($suffixes as $suffix)
        {
            $eventSuffix .= sprintf('[%s]', $suffix);
        }

        foreach ($listeners as $event => $listener)
        {
            $dispatcher->addListener($event . $eventSuffix, $listener, $priority);
        }
    }

}
