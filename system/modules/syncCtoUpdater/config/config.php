<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    SyncCtoAutoUpdater
 * @license    GNU/LGPL 
 * @filesource
 */

/**
 * RPC  
 */
$GLOBALS["CTOCOM_FUNCTIONS"]["SYNCCTO_AUTO_UPDATE"] = array
(
    "class"             => "SyncCtoAutoUpdater",
    "function"          => "update",
    "typ"               => "POST",
    "parameter"         => array("zipfile"),
);