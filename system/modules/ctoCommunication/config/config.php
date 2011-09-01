<?php

if (!defined('TL_ROOT'))
    die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  MEN AT WORK 2011 
 * @package    ctoCommunication
 * @license    GNU/LGPL 
 * @filesource
 */

/**
 * Version from Cto Communication
 */
$GLOBALS["CTOCOM_VERSION"] = "0.0.1";

$GLOBALS["CTOCOM_ENGINE"] = array(
    "Empty" => array(
        "name" => &$GLOBALS['TL_LANG']['ctoCom']['codifyengine']["Empty"],
        "classname" => "CtoComCodifyengineImpl_Empty",
        "folder" => "system/modules/ctoCommunication",
    ),
    "Mcrypt" => array(
        "name" => &$GLOBALS['TL_LANG']['ctoCom']['codifyengine']["Mcrypt"],
        "classname" => "CtoComCodifyengineImpl_Mcrypt",
        "folder" => "system/modules/ctoCommunication",
    ),
    "Blowfish" => array(
        "name" => &$GLOBALS['TL_LANG']['ctoCom']['codifyengine']["Blowfish"],
        "classname" => "CtoComCodifyengineImpl_Blowfish",
        "folder" => "system/modules/ctoCommunication",
    ),
);

/**
 * Register for RPC-Call functions
 * 
 * Base configuration and CtoCommunication RPC Calls
 */
$GLOBALS["CTOCOM_FUNCTIONS"] = array(
    //- Referer Functions --------
    "CTOCOM_REFERER_DISABLE" => array(
        "class" => "CtoComRPCFunctions",
        "function" => "referer_disable",
        "typ" => "GET",
        "parameter" => false,
    ),
    "CTOCOM_REFERER_ENABLE" => array(
        "class" => "CtoComRPCFunctions",
        "function" => "referer_enable",
        "typ" => "GET",
        "parameter" => false,
    ),
    //- Version Functions --------
    "CTOCOM_VERSION" => array(
        "class" => "CtoComRPCFunctions",
        "function" => "getCtoComVersion",
        "typ" => "GET",
        "parameter" => false,
    ),
    "CONTAO_VERSION" => array(
        "class" => "CtoComRPCFunctions",
        "function" => "getContaoVersion",
        "typ" => "GET",
        "parameter" => false,
    ),
    
);
?>