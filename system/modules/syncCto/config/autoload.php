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
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2014-2018 MEN AT WORK.
 * @license    https://github.com/menatwork/syncCto/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

/**
 * Register the namespaces
 */
\Contao\ClassLoader::addNamespaces(array
(
    'SyncCto',
));


/**
 * Register the classes
 */
\Contao\ClassLoader::addClasses(array
(
    // Not yet moved.
    'GeneralDataSyncCto'                                  => 'system/modules/syncCto/GeneralDataSyncCto.php',
    'SyncCtoFilterIteratorFolder'                         => 'system/modules/syncCto/SyncCtoFilterIteratorFolder.php',
    'SyncCtoUpdater'                                      => 'system/modules/syncCto/SyncCtoUpdater.php',
    'SyncCtoRunOnceEr'                                    => 'system/modules/syncCto/SyncCtoRunOnceEr.php',
    'InterfaceSyncCtoStep'                                => 'system/modules/syncCto/InterfaceSyncCtoStep.php',
    'SyncCtoTableSettings'                                => 'system/modules/syncCto/SyncCtoTableSettings.php',
    'SyncCtoPopupFiles'                                   => 'system/modules/syncCto/SyncCtoPopupFiles.php',
    'SyncCtoModuleCheck'                                  => 'system/modules/syncCto/SyncCtoModuleCheck.php',
    'SyncCtoEnum'                                         => 'system/modules/syncCto/SyncCtoEnum.php',
    'SyncCtoCommunicationClient'                          => 'system/modules/syncCto/SyncCtoCommunicationClient.php',
    'SyncCtoFilterIteratorBase'                           => 'system/modules/syncCto/SyncCtoFilterIteratorBase.php',
    'SyncCtoHelper'                                       => 'system/modules/syncCto/SyncCtoHelper.php',
    'SyncCtoModuleClient'                                 => 'system/modules/syncCto/SyncCtoModuleClient.php',
    'SyncCtoStats'                                        => 'system/modules/syncCto/SyncCtoStats.php',
    'SyncCtoDatabase'                                     => 'system/modules/syncCto/SyncCtoDatabase.php',
    'SyncCtoContaoAutomator'                              => 'system/modules/syncCto/SyncCtoContaoAutomator.php',
    'SyncCtoFilterIteratorFiles'                          => 'system/modules/syncCto/SyncCtoFilterIteratorFiles.php',
    'SyncCtoModuleBackup'                                 => 'system/modules/syncCto/SyncCtoModuleBackup.php',
    'SyncCtoAjax'                                         => 'system/modules/syncCto/SyncCtoAjax.php',
    'SyncCtoFiles'                                        => 'system/modules/syncCto/SyncCtoFiles.php',
    'SyncCtoRPCFunctions'                                 => 'system/modules/syncCto/SyncCtoRPCFunctions.php',
    'SyncCtoPopupDB'                                      => 'system/modules/syncCto/SyncCtoPopupDB.php',
    'SyncCtoDatabaseUpdater'                              => 'system/modules/syncCto/SyncCtoDatabaseUpdater.php',
    'ContentData'                                         => 'system/modules/syncCto/ContentData.php',
    'StepPool'                                            => 'system/modules/syncCto/StepPool.php',
    // Contao
    'SyncCto\Contao\API'                                  => 'system/modules/syncCto/src/SyncCto/Contao/API.php',
    // Helper
    'SyncCto\Helper\PathBuilder'                          => 'system/modules/syncCto/src/SyncCto/Helper/PathBuilder.php',
    'SyncCto\Helper\Ping'                                 => 'system/modules/syncCto/src/SyncCto/Helper/Ping.php',
    // FileList
    'SyncCto\Sync\FileList\Base'                          => 'system/modules/syncCto/src/SyncCto/Sync/FileList/Base.php',
    'SyncCto\Sync\FileList\FilterIterator\Base'           => 'system/modules/syncCto/src/SyncCto/Sync/FileList/FilterIterator/Base.php',
    // DCA - Builder
    'SyncCto\DcGeneral\Dca\Builder\DataDefinitionBuilder' => 'system/modules/syncCto/src/SyncCto/DcGeneral/Dca/Builder/DataDefinitionBuilder.php',
    // ActionHandler
    'SyncCto\DcGeneral\ActionHandler\SyncEditHandler'     => 'system/modules/syncCto/src/SyncCto/DcGeneral/ActionHandler/SyncEditHandler.php',
    'SyncCto\DcGeneral\ActionHandler\BackupEditHandler'  => 'system/modules/syncCto/src/SyncCto/DcGeneral/ActionHandler/BackupEditHandler.php',
    // Events
    'SyncCto\DcGeneral\Events\Base'                       => 'system/modules/syncCto/src/SyncCto/DcGeneral/Events/Base.php',
    'SyncCto\DcGeneral\Events\Backup\Database'            => 'system/modules/syncCto/src/SyncCto/DcGeneral/Events/Backup/Database.php',
    'SyncCto\DcGeneral\Events\Backup\File'                => 'system/modules/syncCto/src/SyncCto/DcGeneral/Events/Backup/File.php',
    'SyncCto\DcGeneral\Events\Sync\From'                  => 'system/modules/syncCto/src/SyncCto/DcGeneral/Events/Sync/From.php',
    'SyncCto\DcGeneral\Events\Sync\To'                    => 'system/modules/syncCto/src/SyncCto/DcGeneral/Events/Sync/To.php'
));


/**
 * Register the templates
 */
\Contao\TemplateLoader::addFiles(array
(
    'be_syncCto_steps'      => 'system/modules/syncCto/templates',
    'be_syncCto_check'      => 'system/modules/syncCto/templates',
    'be_syncCto_attention'  => 'system/modules/syncCto/templates',
    'be_syncCto_error'      => 'system/modules/syncCto/templates',
    'be_syncCto_smallCheck' => 'system/modules/syncCto/templates',
    'be_syncCto_popup'      => 'system/modules/syncCto/templates',
    'be_syncCto_backup'     => 'system/modules/syncCto/templates',
    'be_syncCto_form'       => 'system/modules/syncCto/templates',
    'be_syncCto_database'   => 'system/modules/syncCto/templates',
    'be_syncCto_files'      => 'system/modules/syncCto/templates',
    'be_syncCto_legend'     => 'system/modules/syncCto/templates',
));
