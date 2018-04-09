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
 * Headline
 */
$GLOBALS['TL_LANG']['tl_syncCto_check']['check']                    = 'Systemcheck';
$GLOBALS['TL_LANG']['tl_syncCto_check']['configuration']            = 'PHP Konfigurationen';
$GLOBALS['TL_LANG']['tl_syncCto_check']['functions']                = 'PHP Funktionen';
$GLOBALS['TL_LANG']['tl_syncCto_check']['proFunctions']             = 'Pro Funktionen';
$GLOBALS['TL_LANG']['tl_syncCto_check']['extendedInformation']      = 'Zusätzliche Informationen';

/**
 * Table
 */
$GLOBALS['TL_LANG']['tl_syncCto_check']['parameter']                = 'Parameter';
$GLOBALS['TL_LANG']['tl_syncCto_check']['value']                    = 'Wert';
$GLOBALS['TL_LANG']['tl_syncCto_check']['value_server']             = 'Werte Server';
$GLOBALS['TL_LANG']['tl_syncCto_check']['value_client']             = 'Werte Client';
$GLOBALS['TL_LANG']['tl_syncCto_check']['description']              = 'Beschreibung';
$GLOBALS['TL_LANG']['tl_syncCto_check']['description']              = 'Beschreibung';
$GLOBALS['TL_LANG']['tl_syncCto_check']['on']                       = 'An';
$GLOBALS['TL_LANG']['tl_syncCto_check']['off']                      = 'Aus';
$GLOBALS['TL_LANG']['tl_syncCto_check']['safemode']                 = 'Safe mode';
$GLOBALS['TL_LANG']['tl_syncCto_check']['met']                      = array('Maximum execution time', 'Die empfohlene Einstellung ist 0, 30 oder höher.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['memory_limit']             = array('Memory limit', 'Die empfohlene Einstellung ist 128,0 MiB oder höher.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['register_globals']         = 'Register globals';
$GLOBALS['TL_LANG']['tl_syncCto_check']['file_uploads']             = 'File uploads';
$GLOBALS['TL_LANG']['tl_syncCto_check']['umf']                      = array('Upload maximum filesize', 'Die empfohlene Einstellung ist 8,0 MiB oder höher.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['pms']                      = array('Post maximum size', 'Die empfohlene Einstellung ist 8,0 MiB oder höher.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['mit']                      = array('Maximum input time', 'Die empfohlene Einstellung ist -1, 60 oder höher.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['dst']                      = array('Default socket timeout', 'Die empfohlene Einstellung ist 30 oder höher.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['fsocket']                  = 'Fsockopen';
$GLOBALS['TL_LANG']['tl_syncCto_check']['zip_archive']              = 'ZipArchive';
$GLOBALS['TL_LANG']['tl_syncCto_check']['bcmath']                   = 'BC Math';
$GLOBALS['TL_LANG']['tl_syncCto_check']['gmp']                      = 'GMP';
$GLOBALS['TL_LANG']['tl_syncCto_check']['mcrypt']                   = 'Mcrypt';
$GLOBALS['TL_LANG']['tl_syncCto_check']['xmlreader']                = 'XMLReader';
$GLOBALS['TL_LANG']['tl_syncCto_check']['xmlwriter']                = 'XMLWriter';
$GLOBALS['TL_LANG']['tl_syncCto_check']['suhosin']                  = 'Suhosin';
$GLOBALS['TL_LANG']['tl_syncCto_check']['trigger']                  = 'MySQL Trigger';
$GLOBALS['TL_LANG']['tl_syncCto_check']['trigger_information']      = 'Für mehr Informationen schauen Sie bitte in die <a href="contao/main.php?do=log">Systemlog</a> im Backend.';

$GLOBALS['TL_LANG']['tl_syncCto_check']['setting_off']              = 'Die empfohlene Einstellung ist Aus.';
$GLOBALS['TL_LANG']['tl_syncCto_check']['setting_on']               = 'Die empfohlene Einstellung ist An.';

/**
 * Text
 */
$GLOBALS['TL_LANG']['tl_syncCto_check']['safemodehack']             = 'syncCto kann aufgrund fehlender Schreibrechte nicht ausgeführt werden.';

/**
 * Extended information
 */
$GLOBALS['TL_LANG']['tl_syncCto_check']['extendedInformation_desc']['date_default_timezone'] = 'Zeitzone (System)';
$GLOBALS['TL_LANG']['tl_syncCto_check']['extendedInformation_desc']['date_ini_timezone']     = 'Zeitzone (PHP.ini)';
$GLOBALS['TL_LANG']['tl_syncCto_check']['extendedInformation_desc']['server_software']       = 'Serversoftware';
$GLOBALS['TL_LANG']['tl_syncCto_check']['extendedInformation_desc']['php_version']           = 'PHP-Version';
$GLOBALS['TL_LANG']['tl_syncCto_check']['extendedInformation_desc']['current_time']          = 'Serverzeit';
