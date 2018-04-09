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
 * @author     Patrick Kahl <kahl.patrick@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2014-2018 MEN AT WORK.
 * @license    https://github.com/menatwork/syncCto/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['sync_legend']                         = 'File synchronization';
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['table_legend']                        = 'Database synchronization';
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['systemoperations_legend']             = 'Maintenance';
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['edit']                                = 'Client synchronization';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['sync_options']                        = array('Synchronize files', 'Here you can select which files should be synchronized.');
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['database_check']                      = array('Synchronize database', 'Choose this option for database synchronization.');
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['tl_files_check']                      = array('Overwrite \'tl-files\'', 'Choose this option for the tl_files table synchronization.');
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['systemoperations_check']              = array('Activate maintenance', 'Choose these options to activate the system maintenance.');
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['systemoperations_maintenance']        = array('Purge data', 'Please select the data you want to purge.');
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['attention_flag']                      = array('Deactivate warning notice', 'Choose this option to deactivate the syncronisation warning on the client.');
$GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['localconfig_error']                   = array('Display error messages', 'Print error messages to the screen (not recommended for productional sites).');
