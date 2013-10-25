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
 * Headline
 */
$GLOBALS['TL_LANG']['tl_syncCto_check']['check']                    = 'System check';
$GLOBALS['TL_LANG']['tl_syncCto_check']['configuration']            = 'PHP configurations';
$GLOBALS['TL_LANG']['tl_syncCto_check']['functions']                = 'PHP functions';
$GLOBALS['TL_LANG']['tl_syncCto_check']['proFunctions']             = 'Pro functions';
$GLOBALS['TL_LANG']['tl_syncCto_check']['extendedInformation']      = 'Additional information';

/**
 * Table
 */
$GLOBALS['TL_LANG']['tl_syncCto_check']['parameter']                = 'Parameter';
$GLOBALS['TL_LANG']['tl_syncCto_check']['value']                    = 'Value';
$GLOBALS['TL_LANG']['tl_syncCto_check']['value_server']             = 'Value Server';
$GLOBALS['TL_LANG']['tl_syncCto_check']['value_client']             = 'Value Client';
$GLOBALS['TL_LANG']['tl_syncCto_check']['description']              = 'Description';
$GLOBALS['TL_LANG']['tl_syncCto_check']['on']                       = 'On';
$GLOBALS['TL_LANG']['tl_syncCto_check']['off']                      = 'Off';
$GLOBALS['TL_LANG']['tl_syncCto_check']['safemode']                 = 'Safe mode';
$GLOBALS['TL_LANG']['tl_syncCto_check']['met']                      = array('Maximum execution time', 'Recommended setting is 0, 30 or greater.'); 
$GLOBALS['TL_LANG']['tl_syncCto_check']['memory_limit']             = array('Memory limit', 'Recommended setting is 128,0 MB or greater.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['register_globals']         = 'Register globals'; 
$GLOBALS['TL_LANG']['tl_syncCto_check']['file_uploads']             = 'File uploads'; 
$GLOBALS['TL_LANG']['tl_syncCto_check']['umf']                      = array('Upload maximum filesize', 'Recommended setting is 8,0 MB or greater.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['pms']                      = array('Post maximum size', 'Recommended setting is 8,0 MB or greater.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['mit']                      = array('Maximum input time', 'Recommended setting is -1, 60 or greater.'); 
$GLOBALS['TL_LANG']['tl_syncCto_check']['dst']                      = array('Default socket timeout', 'Recommended setting is 30 or greater.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['fsocket']                  = 'Fsockopen';
$GLOBALS['TL_LANG']['tl_syncCto_check']['zip_archive']              = 'ZipArchive';
$GLOBALS['TL_LANG']['tl_syncCto_check']['bcmath']                   = 'BC Math';
$GLOBALS['TL_LANG']['tl_syncCto_check']['gmp']                      = 'GMP';
$GLOBALS['TL_LANG']['tl_syncCto_check']['mcrypt']                   = 'Mcrypt';
$GLOBALS['TL_LANG']['tl_syncCto_check']['xmlreader']                = 'XMLReader';
$GLOBALS['TL_LANG']['tl_syncCto_check']['xmlwriter']                = 'XMLWriter';
$GLOBALS['TL_LANG']['tl_syncCto_check']['suhosin']                  = 'Suhosin';
$GLOBALS['TL_LANG']['tl_syncCto_check']['trigger']                  = 'MySQL Trigger';
$GLOBALS['TL_LANG']['tl_syncCto_check']['trigger_information']      = 'For more information take a look at the <a href="contao/main.php?do=log">system log</a>.';

$GLOBALS['TL_LANG']['tl_syncCto_check']['setting_off']              = 'Recommended setting is Off.';
$GLOBALS['TL_LANG']['tl_syncCto_check']['setting_on']               = 'Recommended setting is On.';

/**
 * Text
 */
$GLOBALS['TL_LANG']['tl_syncCto_check']['safemodehack']             = 'syncCto cannot be used because of missing write permissions.';

/**
 * Extended information
 */
$GLOBALS['TL_LANG']['tl_syncCto_check']['extendedInformation_desc']['date_default_timezone'] = 'Time zone (System)';
$GLOBALS['TL_LANG']['tl_syncCto_check']['extendedInformation_desc']['date_ini_timezone']     = 'Time zone (PHP.ini)';
$GLOBALS['TL_LANG']['tl_syncCto_check']['extendedInformation_desc']['server_software']       = 'Server software';
$GLOBALS['TL_LANG']['tl_syncCto_check']['extendedInformation_desc']['php_version']           = 'PHP-Version';
$GLOBALS['TL_LANG']['tl_syncCto_check']['extendedInformation_desc']['current_time']          = 'Server time';
