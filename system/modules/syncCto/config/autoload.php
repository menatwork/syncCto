<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2013 
 * @package    syncCto
 * @license    GNU/LGPL 
 * @filesource
 */

/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	'CronDbBackups'              => 'system/modules/syncCto/cron/CronDbBackups.php',
	'CronDeleteDbBackups'        => 'system/modules/syncCto/cron/CronDeleteDbBackups.php',
	'CronFileBackups'            => 'system/modules/syncCto/cron/CronFileBackups.php',
	'CronDeleteFileBackups'      => 'system/modules/syncCto/cron/CronDeleteFileBackups.php',
	'SyncCtoAjax'                => 'system/modules/syncCto/SyncCtoAjax.php',
	'SyncCtoFiles'               => 'system/modules/syncCto/SyncCtoFiles.php',
	'SyncCtoCommunicationClient' => 'system/modules/syncCto/SyncCtoCommunicationClient.php',
	'SyncCtoEnum'                => 'system/modules/syncCto/SyncCtoEnum.php',
	'SyncCtoUpdater'             => 'system/modules/syncCto/SyncCtoUpdater.php',
	'SyncCtoDatabaseUpdater'     => 'system/modules/syncCto/SyncCtoDatabaseUpdater.php',
	'SyncCtoModuleCheck'         => 'system/modules/syncCto/SyncCtoModuleCheck.php',
	'SyncCtoModuleClient'        => 'system/modules/syncCto/SyncCtoModuleClient.php',
	'SyncCtoDatabase'            => 'system/modules/syncCto/SyncCtoDatabase.php',
	'InterfaceSyncCtoStep'       => 'system/modules/syncCto/InterfaceSyncCtoStep.php',
	'SyncCtoDatabaseUpdater3'    => 'system/modules/syncCto/SyncCtoDatabaseUpdater3.php',
	'GeneralDataSyncCto'         => 'system/modules/syncCto/GeneralDataSyncCto.php',
	'GeneralDataSyncCtoC2'       => 'system/modules/syncCto/GeneralDataSyncCtoC2.php',
	'SyncCtoHelper'              => 'system/modules/syncCto/SyncCtoHelper.php',
	'SyncCtoRPCFunctions'        => 'system/modules/syncCto/SyncCtoRPCFunctions.php',
	'SyncCtoModuleBackup'        => 'system/modules/syncCto/SyncCtoModuleBackup.php',
	'SyncCtoStats'               => 'system/modules/syncCto/SyncCtoStats.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'be_syncCto_smallCheck' => 'system/modules/syncCto/templates',
	'be_syncCto_files'      => 'system/modules/syncCto/templates',
	'be_syncCto_attention'  => 'system/modules/syncCto/templates',
	'be_syncCto_database'   => 'system/modules/syncCto/templates',
	'be_syncCto_backup'     => 'system/modules/syncCto/templates',
	'be_syncCto_error'      => 'system/modules/syncCto/templates',
	'be_syncCto_check'      => 'system/modules/syncCto/templates',
	'be_syncCto_steps'      => 'system/modules/syncCto/templates',
	'be_syncCto_popup'      => 'system/modules/syncCto/templates',
	'be_syncCto_form'       => 'system/modules/syncCto/templates',
));
