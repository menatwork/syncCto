<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package SyncCto
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	'SyncCtoTableBackupFile'      => 'system/modules/syncCto/SyncCtoTableBackupFile.php',
	'GeneralDataSyncCto'          => 'system/modules/syncCto/GeneralDataSyncCto.php',
	'SyncCtoFilterIteratorFolder' => 'system/modules/syncCto/SyncCtoFilterIteratorFolder.php',
	'SyncCtoUpdater'              => 'system/modules/syncCto/SyncCtoUpdater.php',
	'SyncCtoRunOnceEr'            => 'system/modules/syncCto/SyncCtoRunOnceEr.php',
	'SyncCtoSubscriber'           => 'system/modules/syncCto/SyncCtoSubscriber.php',
	'InterfaceSyncCtoStep'        => 'system/modules/syncCto/InterfaceSyncCtoStep.php',
	'SyncCtoTableSettings'        => 'system/modules/syncCto/SyncCtoTableSettings.php',
	'SyncCtoPopupFiles'           => 'system/modules/syncCto/SyncCtoPopupFiles.php',
	'SyncCtoModuleCheck'          => 'system/modules/syncCto/SyncCtoModuleCheck.php',
	'SyncCtoEnum'                 => 'system/modules/syncCto/SyncCtoEnum.php',
	'SyncCtoCommunicationClient'  => 'system/modules/syncCto/SyncCtoCommunicationClient.php',
	'SyncCtoFilterIteratorBase'   => 'system/modules/syncCto/SyncCtoFilterIteratorBase.php',
	'StepPool'                    => 'system/modules/syncCto/StepPool.php',
	'SyncCtoHelper'               => 'system/modules/syncCto/SyncCtoHelper.php',
	'SyncCtoModuleClient'         => 'system/modules/syncCto/SyncCtoModuleClient.php',
	'SyncCtoStats'                => 'system/modules/syncCto/SyncCtoStats.php',
	'SyncCtoDatabase'             => 'system/modules/syncCto/SyncCtoDatabase.php',
	'SyncCtoTableSyncFrom'        => 'system/modules/syncCto/SyncCtoTableSyncFrom.php',
	'SyncCtoTableBackupDatabase'  => 'system/modules/syncCto/SyncCtoTableBackupDatabase.php',
	'SyncCtoFilterIteratorFiles'  => 'system/modules/syncCto/SyncCtoFilterIteratorFiles.php',
	'SyncCtoModuleBackup'         => 'system/modules/syncCto/SyncCtoModuleBackup.php',
	'SyncCtoAjax'                 => 'system/modules/syncCto/SyncCtoAjax.php',
	'SyncCtoTableBase'            => 'system/modules/syncCto/SyncCtoTableBase.php',
	'SyncCtoFiles'                => 'system/modules/syncCto/SyncCtoFiles.php',
	'SyncCtoRPCFunctions'         => 'system/modules/syncCto/SyncCtoRPCFunctions.php',
	'SyncCtoPopupDB'              => 'system/modules/syncCto/SyncCtoPopupDB.php',
	'SyncCtoDatabaseUpdater'      => 'system/modules/syncCto/SyncCtoDatabaseUpdater.php',
	'SyncCtoTableSyncTo'          => 'system/modules/syncCto/SyncCtoTableSyncTo.php',
	'ContentData'                 => 'system/modules/syncCto/ContentData.php',
    'SyncCtoContaoAutomator'      => 'system/modules/syncCto/SyncCtoContaoAutomator.php'
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
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
