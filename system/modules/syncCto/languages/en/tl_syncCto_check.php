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
$GLOBALS['TL_LANG']['tl_syncCto_check']['configuration']            = 'PHP configuration';
$GLOBALS['TL_LANG']['tl_syncCto_check']['functions']                = 'PHP functions';
$GLOBALS['TL_LANG']['tl_syncCto_check']['specialFunctions']         = 'PHP special functions';

/**
 * Table
 */
$GLOBALS['TL_LANG']['tl_syncCto_check']['parameter']                = 'Parameter';
$GLOBALS['TL_LANG']['tl_syncCto_check']['value']                    = 'Value';
$GLOBALS['TL_LANG']['tl_syncCto_check']['description']              = 'Description';
$GLOBALS['TL_LANG']['tl_syncCto_check']['on']                       = 'On';
$GLOBALS['TL_LANG']['tl_syncCto_check']['off']                      = 'Off';
$GLOBALS['TL_LANG']['tl_syncCto_check']['safemode']                 = array('Safe mode', 'Recommended setting is Off.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['met']                      = array('Maximum execution time', 'Recommended setting is 0, 30 or greater.'); 
$GLOBALS['TL_LANG']['tl_syncCto_check']['memory_limit']             = array('Memory limit', 'Recommended setting is 128,0 MB or greater.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['register_globals']         = array('Register globals', 'Recommended setting is Off.'); 
$GLOBALS['TL_LANG']['tl_syncCto_check']['file_uploads']             = array('File uploads', 'Recommended setting is On.'); 
$GLOBALS['TL_LANG']['tl_syncCto_check']['umf']                      = array('Upload maximum filesize', 'Recommended setting is 8,0 MB or greater.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['pms']                      = array('Post maximum size', 'Recommended setting is 8,0 MB or greater.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['mit']                      = array('Maximum input time', 'Recommended setting is -1, 60 or greater.'); 
$GLOBALS['TL_LANG']['tl_syncCto_check']['dst']                      = array('Default socket timeout', 'Recommended setting is 30 or greater.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['fsocket']                  = array('Fsockopen', 'Recommended setting is On.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['bcmath']                   = array('BC Math', 'Recommended setting is On.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['gmp']                      = array('GMP', 'Recommended setting is On.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['mcrypt']                   = array('Mcrypt', 'Recommended setting is On. (Alternative phpseclib AES)');
$GLOBALS['TL_LANG']['tl_syncCto_check']['zip_archive']              = array('ZipArchive', 'Recommended setting is On.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['xmlreader']                = array('XMLReader', 'Recommended setting is On.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['xmlwriter']                = array('XMLWriter', 'Recommended setting is On.');
$GLOBALS['TL_LANG']['tl_syncCto_check']['suhosin']                  = array('Suhosin', 'Recommended setting is Off.');

/**
 * Text
 */
$GLOBALS['TL_LANG']['tl_syncCto_check']['other_sync_issues']        = 'Other known issues';
$GLOBALS['TL_LANG']['tl_syncCto_check']['explanation_sync_issues']  = 'Some server configuration settings are preventing the synchronization, which cannot be detected by the system check.';
$GLOBALS['TL_LANG']['tl_syncCto_check']['known_issues']             = 'Some known settings are:';
$GLOBALS['TL_LANG']['tl_syncCto_check']['suhosin_issue']            = 'Suhosin is preventing the synchronization';
$GLOBALS['TL_LANG']['tl_syncCto_check']['mrl_issue']                = 'The MaxRequestLen is too low';

$GLOBALS['TL_LANG']['tl_syncCto_check']['safemodehack']             = 'syncCto cannot be used because of missing write permissions.';

?>