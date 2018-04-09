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

namespace SyncCto\DcGeneral\ActionHandler;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\ReloadEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LogEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\BaseView;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\EditMask;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\AbstractHandler;

/**
 * Class CreateHandler
 *
 * @package ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler
 */
class BackupEditHandler extends AbstractHandler
{
    const PRIORITY = 20000;

    /**
     * Handle the action.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException When the requested model could not be located in the database.
     */
    public function process()
    {
        $event        = $this->getEvent();
        $action       = $event->getAction();
        $environment  = $event
            ->getEnvironment();
        $dataProvider = $environment
            ->getDataProvider();
        $providerName = $dataProvider
            ->getEmptyModel()
            ->getProviderName();

        $allowedProviderName = [
            'tl_syncCto_backup_db',
            'tl_syncCto_backup_file',
            'tl_syncCto_restore_db',
            'tl_syncCto_restore_file',
        ];

        // Only handle if we do not have a manual sorting or we know where to insert.
        // Manual sorting is handled by clipboard.
        if ($action !== 'edit' && !\in_array($providerName, $allowedProviderName) ) {
            return;
        }

        if (false === $this->checkPermission()) {
            $event->stopPropagation();

            return;
        }

        $view = $environment->getView();
        if (!$view instanceof BaseView) {
            return;
        }

        $model = $dataProvider->getEmptyModel();
        $model->setId(1);
        $clone = $dataProvider->getEmptyModel();
        $clone->setId(1);

        $editMask = new EditMask($view, $model, $clone, null, null, $view->breadcrumb());
        $event->setResponse($editMask->execute());

        // Break all other events. This is our and nobody else should handle this.
        $event->stopPropagation();
    }

    /**
     * Check permission for edit a model.
     *
     * @return bool
     */
    private function checkPermission()
    {
        $environment     = $this->getEnvironment();
        $dataDefinition  = $environment->getDataDefinition();
        $basicDefinition = $dataDefinition->getBasicDefinition();

        if (true === $basicDefinition->isEditable()) {
            return true;
        }

        $inputProvider = $environment->getInputProvider();

        $modelId = ModelId::fromSerialized($inputProvider->getParameter('id'));

        $this->getEvent()->setResponse(
            \sprintf(
                '<div style="text-align:center; font-weight:bold; padding:40px;">
                    You have no permission for edit model %s.
                </div>',
                $modelId->getSerialized()
            )
        );

        return false;
    }

    /**
     * Check the submitted data if we want to restore a previous version of a model.
     *
     * If so, the model will get loaded and marked as active version in the data provider and the client will perform a
     * reload of the page.
     *
     * @param ModelId $modelId The model id.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException When the requested version could not be located in the database.
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    private function checkRestoreVersion(ModelId $modelId)
    {
        $environment   = $this->getEnvironment();
        $definition    = $environment->getDataDefinition();
        $inputProvider = $environment->getInputProvider();

        $dataProviderDefinition  = $definition->getDataProviderDefinition();
        $dataProvider            = $environment->getDataProvider($modelId->getDataProviderName());
        $dataProviderInformation = $dataProviderDefinition->getInformation($modelId->getDataProviderName());

        if ($dataProviderInformation->isVersioningEnabled()
            && ($inputProvider->getValue('FORM_SUBMIT') === 'tl_version')
            && ($modelVersion = $inputProvider->getValue('version')) !== null
        ) {
            $model = $dataProvider->getVersion($modelId->getId(), $modelVersion);

            if ($model === null) {
                $message = \sprintf(
                    'Could not load version %s of record ID %s from %s',
                    $modelVersion,
                    $modelId->getId(),
                    $modelId->getDataProviderName()
                );

                $environment->getEventDispatcher()->dispatch(
                    ContaoEvents::SYSTEM_LOG,
                    new LogEvent($message, TL_ERROR, 'DC_General - checkRestoreVersion()')
                );

                throw new DcGeneralRuntimeException($message);
            }

            $dataProvider->save($model);
            $dataProvider->setVersionActive($modelId->getId(), $modelVersion);
            $environment->getEventDispatcher()->dispatch(ContaoEvents::CONTROLLER_RELOAD, new ReloadEvent());
        }
    }
}
