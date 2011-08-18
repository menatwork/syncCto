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
 * Register for RPC-Call functions
 */
$GLOBALS["CTO_COMMUNICTAION"]["RPC_FUNCTION"][] = array(
    "RPC_AUTH" => array(
        "auth" => false,
        "class" => "SyncCtoRPCFunctions",
        "function" => "rpc_auth",
        "parameter" => false,
        "typ" => "POST"
    ),
    "RPC_LOGIN" => array(
        "auth" => false,
        "class" => "SyncCtoRPCFunctions",
        "function" => "rpc_login",
        "parameter" => array("username", "password"),
        "typ" => "POST"
    ),
    "RPC_LOGOUT" => array(
        "auth" => false,
        "class" => "SyncCtoRPCFunctions",
        "function" => "rpc_logout",
        "parameter" => false,
        "typ" => "POST"
    ),
    "RPC_REFERER_DISABLE" => array(
        "auth" => false,
        "class" => "SyncCtoRPCFunctions",
        "function" => "rpc_referer_disable",
        "parameter" => false,
        "typ" => "GET"
    ),
    "RPC_REFERER_ENABLE" => array(
        "auth" => false,
        "class" => "SyncCtoRPCFunctions",
        "function" => "rpc_referer_enable",
        "parameter" => false,
        "typ" => "GET"
    ),
    //-------
    "RPC_CALC" => array(
        "auth" => true,
        "class" => "SyncCtoRPCFunctions",
        "function" => "rpc_calc",
        "parameter" => array("RPC_CALC"),
        "typ" => "POST"
    ),
    "RPC_VERSION" => array(
        "auth" => false,
        "class" => "SyncCtoRPCFunctions",
        "function" => "rpc_version",
        "parameter" => false,
        "typ" => "POST"
    ),
    "RPC_PARAMETER" => array(
        "auth" => true,
        "class" => "SyncCtoRPCFunctions",
        "function" => "rpc_parameter",
        "parameter" => false,
        "typ" => "POST"
    ),
    //-------
    "RPC_CHECKSUM_CHECK" => array(
        "auth" => true,
        "class" => "SyncCtoRPCFunctions",
        "function" => "rpc_parameter",
        "parameter" => array("RPC_CHECKSUM_CHECK"),
        "typ" => "POST"
    ),
    "RPC_CHECKSUM_CHECK" => array(
        "auth" => true,
        "class" => "SyncCtoRPCFunctions",
        "function" => "rpc_checksum_check",
        "parameter" => array("RPC_CHECKSUM_CHECK"),
        "typ" => "POST"
    ),
    "RPC_CLEAR_TEMP" => array(
        "auth" => false,
        "class" => "SyncCtoRPCFunctions",
        "function" => "rpc_clear_temp",
        "parameter" => false,
        "typ" => "POST"
    ),
    "RPC_CHECKSUM_CORE" => array(
        "auth" => false,
        "class" => "SyncCtoRPCFunctions",
        "function" => "rpc_checksum_core",
        "parameter" => false,
        "typ" => "POST"
    ),
    "RPC_CHECKSUM_TLFILES" => array(
        "auth" => true,
        "class" => "SyncCtoRPCFunctions",
        "function" => "rpc_checksum_tlfiles",
        "parameter" => false,
        "typ" => "POST"
    ),
    "RPC_SQL_ZIP" => array(
        "auth" => true,
        "class" => "SyncCtoRPCFunctions",
        "function" => "rpc_sql_zip",
        "parameter" => false,
        "typ" => "POST"
    ),
    "RPC_SQL_SCRIPT" => array(
        "auth" => true,
        "class" => "SyncCtoRPCFunctions",
        "function" => "rpc_sql_script",
        "parameter" => array("name", "tables"),
        "typ" => "POST"
    ),
    "RPC_SQL_SYNCSCRIPT" => array(
        "auth" => true,
        "class" => "SyncCtoRPCFunctions",
        "function" => "rpc_sql_syncscript",
        "parameter" => array("name", "tables"),
        "typ" => "POST"
    ),
    "RPC_SQL_CHECK" => array(
        "auth" => true,
        "class" => "SyncCtoRPCFunctions",
        "function" => "rpc_sql_check",
        "parameter" => array("name"),
        "typ" => "POST"
    ),
    //-------
    "RPC_RUN_SQL" => array(
        "auth" => true,
        "class" => "SyncCtoRPCFunctions",
        "function" => "rpc_run_sql",
        "parameter" => array("RPC_RUN_SQL"),
        "typ" => "POST"
    ),
    "RPC_RUN_FILE" => array(
        "auth" => true,
        "class" => "SyncCtoRPCFunctions",
        "function" => "rpc_run_file",
        "parameter" => array("RPC_RUN_FILE"),
        "typ" => "POST"
    ),
    "RPC_RUN_LOCALCONFIG" => array(
        "auth" => true,
        "class" => "SyncCtoRPCFunctions",
        "function" => "rpc_run_localconfig",
        "parameter" => array("RPC_RUN_LOCALCONFIG"),
        "typ" => "POST"
    ),
    "RPC_RUN_SPLITFILE" => array(
        "auth" => true,
        "class" => "SyncCtoRPCFunctions",
        "function" => "rpc_run_splitfile",
        "parameter" => array("splitname", "splitcount", "movepath", "md5"),
        "typ" => "POST"
    ),
    //--------------
    //- FILE -------
    "RPC_FILE" => array(
        "auth" => true,
        "class" => "SyncCtoRPCFunctions",
        "function" => "rpc_file",
        "parameter" => array("metafiles"),
        "typ" => "POST"
    ),
    "RPC_FILE_GET" => array(
        "auth" => true,
        "class" => "SyncCtoRPCFunctions",
        "function" => "rpc_file_get",
        "parameter" => array("path"),
        "typ" => "POST"
    ),
    "RPC_FILE_DELETE" => array(
        "auth" => true,
        "class" => "SyncCtoRPCFunctions",
        "function" => "rpc_file_delete",
        "parameter" => array("list"),
        "typ" => "POST"
    ),
    "RPC_FILE_SPLIT" => array(
        "auth" => true,
        "class" => "SyncCtoRPCFunctions",
        "function" => "rpc_file_split",
        "parameter" => array("srcfile", "desfolder", "desfile", "size"),
        "typ" => "POST"
    ),
    //--------------
);
?>