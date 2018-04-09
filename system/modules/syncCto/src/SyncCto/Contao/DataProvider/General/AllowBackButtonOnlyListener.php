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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2014-2018 MEN AT WORK.
 * @license    https://github.com/menatwork/syncCto/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace SyncCto\Contao\DataProvider\General;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonsEvent;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * This trait has the basic method to allow only the back button.
 */
class AllowBackButtonOnlyListener
{
    const PRIORITY = 200;

    /**
     * The data provider name.
     *
     * @var string
     */
    private $providerName;

    /**
     * The constructor.
     *
     * @param string $providerName The data provider name.
     */
    public function __construct($providerName)
    {

        $this->providerName = $providerName;
    }

    /**
     * Handle the event.
     *
     * @param GetGlobalButtonsEvent $event The event.
     *
     * @return void
     */
    public function handle(GetGlobalButtonsEvent $event)
    {
        if (!$this->wantToHandle($event->getEnvironment())) {
            return;
        }

        $buttons = $event->getButtons();

        $event->setButtons(['back_button' => $buttons['back_button']]);

        $event->stopPropagation();
    }

    /**
     * Want to handle the event.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return bool
     */
    private function wantToHandle(EnvironmentInterface $environment)
    {
        return !(null === $this->providerName)
               && ($this->providerName === $environment->getDataDefinition()->getName());
    }
}
