<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2013 
 * @package    SyncCtoAutoUpdater
 * @license    GNU/LGPL 
 * @filesource
 */

/**
 * RPC  
 */
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_AUTO_UPDATE"] = array(
    "class" => "SyncCtoAutoUpdater",
    "function" => "update",
    "typ" => "POST",
    "parameter" => array("zipfile"),
);

?>