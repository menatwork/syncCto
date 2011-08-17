<?php

if (!defined('TL_ROOT'))
    die('You cannot access this file directly!');

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
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */
class SyncCtoCommunication extends Backend
{

    protected $mixOutput;
    protected $arrError;
    protected $arrCookie;
    //-------
    protected $arrDebug;
    //-------
    protected $intClient;
    protected $strAddress;
    protected $strPath;
    protected $intPort;
    protected $strUsername;
    protected $strPassword;
    protected $strSecKey;
    //-------    
    protected $objSyncCtoCodifyengine;
    protected $objSyncCtoDatabase;
    protected $objSyncCtoMeasurement;
    //-------
    protected $BackendUser;
    protected $Encryption;
    protected $Config;
    //-------
    protected static $instance = null;
    //-------
    protected $rpclist = array(
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

    /**
     * Constructor
     */
    function __construct()
    {
        // Import Contao classes
        $this->BackendUser = BackendUser::getInstance();
        $this->Encryption = Encryption::getInstance();
        $this->Config = Config::getInstance();

        parent::__construct();

        // Import SyncCto classes
        $this->objSyncCtoCodifyengine = SyncCtoCodifyengineFactory::getEngine();
        $this->objSyncCtoDatabase = SyncCtoDatabase::getInstance();
        $this->objSyncCtoMeasurement = SyncCtoMeasurement::getInstance();
        
        // Init vars
        $this->mixOutput = array();
        $this->arrError = array();
        $this->arrDebug = array();

        // Load Vars      
        $this->loadLanguageFile("syncCto");
    }

    public function __destruct()
    {
        if ($GLOBALS['TL_CONFIG']['syncCto_debug_log'] != true)
            return;

        if (count($this->arrDebug) == 0)
            return;

        $intTime = time();

        if (file_exists(TL_ROOT . $GLOBALS['syncCto']['path']['debug'] . "debug.txt") == true)
        {
            //unlink(TL_ROOT . $GLOBALS['syncCto']['path']['debug'] . "debug.txt");
        }

        $fileHand = fopen(TL_ROOT . $GLOBALS['syncCto']['path']['debug'] . "debug.txt", "a+");
        fwrite($fileHand, "\n<|++++++++++++++++++++++++++++++++++++++++++++++++++++++|>");
        fwrite($fileHand, "\n  + Hinweis:");
        fwrite($fileHand, "\n<|++++++++++++++++++++++++++++++++++++++++++++++++++++++|>\n\n");
        fwrite($fileHand, ">>|------------------------------------------------------");
        fwrite($fileHand, "\n>>|-- Start Log at " . date("H:i:s d.m.Y", $intTime));
        fwrite($fileHand, "\n>>");
        fwrite($fileHand, "\n");

        foreach ($this->arrDebug as $key => $value)
        {
            fwrite($fileHand, "\n");
            fwrite($fileHand, "<|-- Start " . $key . " -----------------------------------|>");
            fwrite($fileHand, "\n\n");

            fwrite($fileHand, trim($value));

            fwrite($fileHand, "\n\n");
            fwrite($fileHand, "<|-- End " . $key . " -------------------------------------|>");
            fwrite($fileHand, "\n");
            fwrite($fileHand, "\n");
        }

        fwrite($fileHand, "\n");
        fwrite($fileHand, "\n>>");
        fwrite($fileHand, "\n>>|-- Close Log at " . date("H:i:s d.m.Y", $intTime));
        fwrite($fileHand, "\n>>|------------------------------------------------------\n");

        fclose($fileHand);
    }

    /**
     * Singelton Pattern
     * 
     * @return SyncCtoCommunication 
     */
    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new SyncCtoCommunication();

        return self::$instance;
    }

    /**
     * Set the client for the connection.
     *
     * @param int $id ID from client
     */
    public function setClient($id)
    {
        // Load Client from database
        $objClient = $this->Database->prepare("SELECT * FROM tl_synccto_clients WHERE id = %s")
                ->limit(1)
                ->execute((int) $id);

        // Check if a client was loaded
        if ($objClient->numRows == 0)
            throw new Exception("Unknown Client.");

        // init
        $this->intClient = (int) $id;

        // Cutting the "http:" and hostadress
        $arrAdress = explode("/", $objClient->address);
        if (count($arrAdress) > 3)
        {
            for ($i = 3; $i < count($arrAdress); $i++)
            {
                if ($arrAdress[$i] == "")
                    continue;

                $strPath .= $arrAdress[$i] . "/";
            }

            $strPath .= $objClient->path;
        }
        else
        {
            $strPath .= $objClient->path;
        }

        // Set all informtaion
        $this->strAddress = $arrAdress[2];
        $this->strPath = $strPath;
        $this->intPort = $objClient->port;

        $this->arrCookie = deserialize($objClient->cookie);
        $this->strUsername = $objClient->username;

        if (!function_exists("mdecrypt_generic"))
        {
            $this->objSyncCtoCodifyengine->setKey($GLOBALS['TL_CONFIG']['encryptionKey']);
            $this->strPassword = $this->objSyncCtoCodifyengine->Decrypt($objClient->password);
            $this->objSyncCtoCodifyengine->resetKey();
        }
        else
        {
            $this->strPassword = $this->Encryption->decrypt($objClient->password);
        }

        $this->strSecKey = $objClient->seckey;

        $this->objSyncCtoCodifyengine = SyncCtoCodifyengineFactory::getEngine($objClient->codifyengine);
    }

    public function setCodifyEngine($strName = "")
    {
        if ($strName == "")
        {
            $this->objSyncCtoCodifyengine = SyncCtoCodifyengineFactory::getEngine();
        }
        else
        {
            $this->objSyncCtoCodifyengine = SyncCtoCodifyengineFactory::getEngine($strName);
        }
    }

    /*
     * -------------------------------------------------------------------------
     * -------------------------------------------------------------------------
     * 
     * Server / Client Run Functions
     * 
     * -------------------------------------------------------------------------
     * -------------------------------------------------------------------------
     */

    /**
     * Run the communication as server
     *
     * @param string $rpc
     * @param array $arrData
     * @param bool $isFileUpload
     * @return array
     */
    protected function runServer($rpc, $arrData = array(), $isGET = FALSE)
    {
        $this->objSyncCtoMeasurement->startMeasurement(__CLASS__, __FUNCTION__, "RPC: " . $rpc);
        $this->objSyncCtoCodifyengine->setKey($this->strSecKey);

        for ($i = 0; $i < 2; $i++)
        {
            $arrResponse = $this->communication($arrData, $rpc, $isGET);

            if ($arrResponse["error"] == 303)
            {
                if ($i > 0)
                {
                    $this->objSyncCtoMeasurement->stopMeasurement(__CLASS__, __FUNCTION__);
                    throw new Exception($GLOBALS['TL_LANG']['syncCto']['rpc_maximum_logins']);
                }

                $arrDataLogin = array(
                    array(
                        "name" => "username",
                        "value" => $this->strUsername,
                    ),
                    array(
                        "name" => "password",
                        "value" => $this->strPassword,
                    ),
                );

                $arrResponseLogin = $this->communication($arrDataLogin, "RPC_LOGIN");

                if ($arrResponseLogin["success"] != 1)
                {
                    $this->objSyncCtoMeasurement->stopMeasurement(__CLASS__, __FUNCTION__);
                    throw new Exception($arrResponseLogin["error"][0]["msg"]);
                }

                $arrResponse = $this->communication($arrData, $rpc, $isGET);
            }
            else if ($arrResponse["success"] == 1)
            {
                $this->objSyncCtoMeasurement->stopMeasurement(__CLASS__, __FUNCTION__);
                return $arrResponse["response"];
            }
            else if ($arrResponse["success"] == 0)
            {
                $this->objSyncCtoMeasurement->stopMeasurement(__CLASS__, __FUNCTION__);
                throw new Exception($arrResponse["error"][0]["msg"]);
            }
        }

        $this->objSyncCtoMeasurement->stopMeasurement(__CLASS__, __FUNCTION__);

        throw new Exception($GLOBALS['TL_LANG']['syncCto']['rpc_maximum_calls']);
    }

    /**
     * Run the communication as client
     *
     * @return null
     */
    public function runClient()
    {
        // Start measurement
        $this->objSyncCtoMeasurement->startMeasurement(__CLASS__, __FUNCTION__, "RPC: " . $this->Input->get("act"));
        
        /** --------------------------------------------------------------------
         * Chanke Codifyengine if set
         */        
        if (strlen($this->Input->get("engine")) != 0)
        {
            // Try to change codifyengine
            try
            {
                // Set new an reload key
                $this->setCodifyEngine($this->Input->get("engine"));
                $this->objSyncCtoCodifyengine->setKey($GLOBALS['TL_CONFIG']['syncCto_seckey']);
            }
            // Error by setting new enigne. Send msg in cleartext
            catch (Exception $exc)
            {
                $this->arrError[] = array(
                    "language" => "codifyengine_unknown",
                    "id" => 10,
                    "object" => "",
                    "msg" => "Unknown Codifyengine",
                    "rpc" => "",
                    "class" => "",
                    "function" => "",
                );

                // Set cleartext engine
                $this->setCodifyEngine("Empty");

                // Return
                $this->objSyncCtoMeasurement->stopMeasurement(__CLASS__, __FUNCTION__);
                return $this->generateOutput();
            }
        }
        else
        {
            $this->setCodifyEngine("Empty");
            $this->objSyncCtoCodifyengine->setKey($GLOBALS['TL_CONFIG']['syncCto_seckey']);
        }

        /** --------------------------------------------------------------------
         * Run RPC-Check function
         */
        $mixRPCCall = $this->Input->get("act");
        // Check if act is set
        if (strlen($mixRPCCall) == 0)
        {
            $this->arrError[] = array(
                "language" => "rpc_missing",
                "id" => 1,
                "object" => "",
                "msg" => "Missing RPC Call",
                "rpc" => $mixRPCCall,
                "class" => "",
                "function" => "",
            );
        }
        else
        {
            if (!key_exists($mixRPCCall, $this->rpclist))
            {
                $this->arrError[] = array(
                    "language" => "rpc_unknown",
                    "id" => 1,
                    "object" => "",
                    "msg" => "Unknown RPC Call",
                    "rpc" => $mixRPCCall,
                    "class" => "",
                    "function" => "",
                );
            }
            else
            {
                if ($this->rpclist[$mixRPCCall]["auth"] == TRUE)
                {
                    //$this->auth();
                }

                $arrParameter = array();

                if ($this->rpclist[$mixRPCCall]["parameter"] != FALSE && is_array($this->rpclist[$mixRPCCall]["parameter"]))
                {
                    switch ($this->rpclist[$mixRPCCall]["typ"])
                    {
                        case "POST":
                            // Decode post 
                            foreach ($_POST as $key => $value)
                            {
                                $mixPost = $this->Input->post($key);
                                $mixPost = $this->objSyncCtoCodifyengine->Decrypt($mixPost);
                                $mixPost = deserialize($mixPost);
                                $mixPost = $mixPost["data"];

                                $this->Input->setPost($key, $mixPost);
                            }

                            // Check if all post are set
                            foreach ($this->rpclist[$mixRPCCall]["parameter"] as $value)
                            {
                                if (!$this->Input->post($value))
                                {
                                    $this->arrError[] = array(
                                        "language" => "rpc_data_missing",
                                        "id" => 2,
                                        "object" => $value,
                                        "msg" => "Missing data for " . $value,
                                        "rpc" => $mixRPCCall,
                                        "class" => $this->rpclist[$mixRPCCall]["class"],
                                        "function" => $this->rpclist[$mixRPCCall]["function"],
                                    );
                                }
                                else
                                {
                                    $arrParameter[$value] = $this->Input->post($value);
                                }
                            }
                            break;

                        default:
                            break;
                    }
                }
            }

            if (count($this->arrError) != 0)
            {
                $this->objSyncCtoMeasurement->stopMeasurement(__CLASS__, __FUNCTION__);
                return $this->generateOutput();
            }

            /** ----------------------------------------------------------------
             * Try to execute rpc call
             */
            try
            {
                if ($this->rpclist[$mixRPCCall]["class"] == "this")
                {
                    $this->objSyncCtoMeasurement->startMeasurement(__CLASS__, $this->rpclist[$mixRPCCall]["function"]);

                    $this->mixOutput = call_user_func_array(array($this, $this->rpclist[$mixRPCCall]["function"]), $arrParameter);

                    $this->objSyncCtoMeasurement->stopMeasurement(__CLASS__, $this->rpclist[$mixRPCCall]["function"]);
                }
                else
                {
                    $this->objSyncCtoMeasurement->startMeasurement($this->rpclist[$mixRPCCall]["class"], $this->rpclist[$mixRPCCall]["function"]);

                    $class = new $this->rpclist[$mixRPCCall]["class"];
                    $this->mixOutput = call_user_func_array(array($class, $this->rpclist[$mixRPCCall]["function"]), $arrParameter);

                    $this->objSyncCtoMeasurement->stopMeasurement($this->rpclist[$mixRPCCall]["class"], $this->rpclist[$mixRPCCall]["function"]);
                }
            }
            catch (Exception $exc)
            {
                $this->arrError[] = array(
                    "language" => "rpc_unknown_exception",
                    "id" => 3,
                    "object" => "",
                    "msg" => $exc->getMessage(),
                    "rpc" => $mixRPCCall,
                    "class" => $this->rpclist[$mixRPCCall]["class"],
                    "function" => $this->rpclist[$mixRPCCall]["function"],
                );
            }
        }

        $this->objSyncCtoMeasurement->stopMeasurement(__CLASS__, __FUNCTION__);
        return $this->generateOutput();
    }

    /* --------------------------------------------------------------------------
     * Helper functions
     */

    /**
     * Bau die Antwort als Array auf und serialize es.
     *
     * @return string
     */
    protected function generateOutput()
    {
        $this->objSyncCtoMeasurement->startMeasurement(__CLASS__, __FUNCTION__);

        if (count($this->arrError) == 0)
        {
            $strOutput = serialize(array(
                "success" => 1,
                "error" => "",
                "response" => $this->mixOutput,
                    ));
        }
        else
        {
            $strOutput = serialize(array(
                "success" => 0,
                "error" => $this->arrError,
                "response" => "",
                    ));
        }

        $strOutput = $this->objSyncCtoCodifyengine->Encrypt($strOutput);

        $this->objSyncCtoMeasurement->stopMeasurement(__CLASS__, __FUNCTION__);

        return "<|@|" . $strOutput . "|@|>";
    }

    /**
     * Funktion die auswählt welche form des Post genutzt werden soll, sowie
     * die Ver / Entschlüsselung vornimmt und überprüft ob ein Array zurück
     * gegeben wurde. Sollte dies nicht der Fall sein wird solange die
     * Anfrage wiederholt bis eine Array zurück gegeben wird oder
     * die maximale Aufrufanzahl erreicht wurde.
     *
     * @param array $arrData Daten zum senden
     * @param string $strRpc ID der RPC. Siehe RPC defines
     * @param bool $isFile Wird eine Datei gesendet
     * @return array("success"=>[0|1],"error"=>[""|Array],"response"=>[""|Array])
     */
    protected function communication($arrData, $strRpc, $isGET = FALSE)
    {
        // Codify
        if (is_array($arrData) && count($arrData) != 0 && $isGET == FALSE)
        {
            $this->objSyncCtoMeasurement->startMeasurement("SyncCtoCodifyengineInterface", "Encrypt");

            foreach ($arrData as $key => $value)
            {
                if (isset($value["filename"]) && strlen($value["filename"]) != 0)
                    continue;

                $arrData[$key]["value"] = $this->objSyncCtoCodifyengine->Encrypt(serialize(array("data" => $value["value"])));
            }

            $this->objSyncCtoMeasurement->stopMeasurement("SyncCtoCodifyengineInterface", "Encrypt");
        }

        if ($isGET)
        {
            $arrResponse = $this->parseResponse($this->requestGet($strRpc, $arrData));
        }
        else
        {
            $arrResponse = $this->parseResponse($this->requestPostMultipart($arrData, $strRpc, $isClearText));
        }

        return $arrResponse;
    }

    protected function requestGet($strRpc = null, $arrData = null)
    {
        $this->objSyncCtoMeasurement->startMeasurement(__CLASS__, __FUNCTION__, "RPC: " . $strRpc);

        try
        {
            // Init
            $boundary = "---------------------------103832778631715";

            // Make a string from cookie array for post sending
            if (!empty($this->arrCookie) && count($this->arrCookie) != 0)
            {
                $strCookie = "";
                foreach ($this->arrCookie as $key => $value)
                {
                    $strCookie .= " " . $key . "=" . $value . ";";
                }
            }

            // ---------------------------------------------------------------------
            // Build Header
            $header = "";
            $strData = "";

            if ($arrData != null)
            {
                foreach ($arrData as $key => $value)
                {
                    if ($strData == "")
                        $strData .= $value["name"] . "=" . $value["value"];
                    else
                        $strData .= "&" . $value["name"] . "=" . $value["value"];
                }
            }

            if ($strRpc == null)
            {
                if ($strData != "")
                    $strData = "?" . $strData;
            }
            else
            {
                if ($strData != "")
                    $strData = "?engine=" . $this->objSyncCtoCodifyengine->getName() . "&act=" . $strRpc . "&" . $strData;
                else
                    $strData = "?engine=" . $this->objSyncCtoCodifyengine->getName() . "&act=" . $strRpc;
            }

            $header .= "GET /" . $this->strPath . $strData . " HTTP/1.1\r\n";
            $header .= "Host: " . $this->strAddress . "\n";
            $header .= "Referer: " . $this->Environment->__get("base") . "\n";
            if ($strCookie != "")
                $header .= "Cookie:" . $strCookie . "\r\n";
            $header .= "Connection: close\r\n\r\n";

            $this->arrDebug["Sendpart RPC " . $strRpc . " " . microtime(true)] = $header . $content2;

            try
            {
                // Open Socket
                $this->fsocket = fsockopen($this->strAddress, $this->intPort, $errno, $errstr, 5);
            }
            catch (Exception $exc)
            {
                $this->log("Error by sending data over fsocket. Error: " . $exc->getMessage());
                throw new Exception("Error by sending data over fsocket. Error: " . $exc->getMessage());
            }

            //Check Socket
            if (!$this->fsocket)
            {
                $this->log("Error by sending data over fsocket. Errno: " . $errno . " Errmsg: " . $errstr, __CLASS__ . " " . __FUNCTION__, "SyncCto");
                throw new Exception("Error by sending data over fsocket. Errno: " . $errno . " Errmsg: " . $errstr);
            }

//        if (!stream_set_timeout($this->fsocket, 1))
//            throw new Exception("Error by setting timeout for stream.");

            $this->objSyncCtoMeasurement->startMeasurement(__CLASS__, __FUNCTION__ . " WRITE", "RPC: " . $strRpc);

            //send data
            fputs($this->fsocket, $header);
            fputs($this->fsocket, $content);

            $this->objSyncCtoMeasurement->stopMeasurement(__CLASS__, __FUNCTION__ . " WRITE");

            $this->objSyncCtoMeasurement->startMeasurement(__CLASS__, __FUNCTION__ . " READ++", "RPC: " . $strRpc);

            stream_set_timeout($this->fsocket, 8);

            $strResponse = "";
            while (true)
            {
                $res = fread($this->fsocket, 8000);

                $strResponse .= $res;

                $stream_meta_data = stream_get_meta_data($this->fsocket);
                if ($stream_meta_data['timed_out'] == 1)
                    break;

                if (strlen($res) < 8000)
                    break;
            }

            $this->objSyncCtoMeasurement->stopMeasurement(__CLASS__, __FUNCTION__ . " READ++");

            fclose($this->fsocket);

            $this->objSyncCtoMeasurement->stopMeasurement(__CLASS__, __FUNCTION__);

            return $strResponse;
        }
        catch (Exception $exc)
        {
            // Write ContaoLog
            $this->log("Error by sending data over fsocket. " . $exc->getMessage(), __CLASS__ . " " . __FUNCTION__, __FUNCTION__);
            throw $exc;
        }
    }

    protected function requestPostMultipart($arrData, $strRpc = null)
    {
        $this->objSyncCtoMeasurement->startMeasurement(__CLASS__, __FUNCTION__, "RPC: " . $strRpc);

        // Init
        $boundary = "---------------------------103832778631715";

        // Make a string from cookie array for post sending
        if (!empty($this->arrCookie) && count($this->arrCookie) != 0)
        {
            $strCookie = "";
            foreach ($this->arrCookie as $key => $value)
            {
                $strCookie .= " " . $key . "=" . $value . ";";
            }
        }

        // ---------------------------------------------------------------------
        // Build Content
        $content = "";
        $content2 = "";

        foreach ($arrData as $key => $value)
        {
            $content .= vsprintf("--%s\n", array($boundary));
            $content2 .= vsprintf("--%s\n", array($boundary));

            if (isset($value["filename"]) == true && strlen($value["filename"]) != 0)
            {
                $content .= vsprintf("Content-Disposition: form - data; name=\"%s\"; filename=\"%s\"\n", array($value["name"], $value["filename"]));
                $content .= "Content-Type: " . $value["mime"] . "\n";

                $content2 .= vsprintf("Content-Disposition: form-data; name=\"%s\"; filename=\"%s\"\n", array($value["name"], $value["filename"]));
                $content2 .= "Content-Type: " . $value["mime"] . "\n";
            }
            else
            {
                $content .= vsprintf("Content-Disposition: form-data; name=\"%s\"\n", array($value["name"]));
                $content2 .= vsprintf("Content-Disposition: form-data; name=\"%s\"\n", array($value["name"]));
            }

            $content .= "\n";
            $content2 .= "\n";

            if (strlen($value["value"]) < 4098)
            {
                $content .= $value["value"] . "\n";
                $content2 .= $value["value"] . "\n";
            }
            else
            {
                $content .= $value["value"] . "\n";
                $content2 .= "skipped" . "\n";
            }
        }

        $content .= vsprintf("--%s--\n", array($boundary));
        $content2 .= vsprintf("--%s--\n", array($boundary));

        // Build Header
        $header = "";

        if ($strRpc != null)
        {
            $header .= "POST /" . $this->strPath . "?engine=" . $this->objSyncCtoCodifyengine->getName() . "&act=" . $strRpc . " HTTP/1.1\r\n";
        }
        else
        {
            $header .= "POST /" . $this->strPath . "?engine=" . $this->objSyncCtoCodifyengine->getName() . " HTTP/1.1\r\n";
        }

        $header .= "Host: " . $this->strAddress . "\n";
        $header .= "Referer: " . $this->Environment->__get("base") . "\n";
        if ($strCookie != "")
            $header .= "Cookie:" . $strCookie . "\r\n";
        $header .= "Content-type: multipart/form-data; boundary=$boundary\n";
        $header .= "Content-length: " . strlen($content) . " \n";
        $header .= "\n";

        $this->arrDebug["Sendpart RPC " . $strRpc . " " . microtime(true)] = $header . $content2;

        try
        {
            // Open Socket
            $this->fsocket = fsockopen($this->strAddress, $this->intPort, $errno, $errstr, 5);
        }
        catch (Exception $exc)
        {
            $this->log("Error by sending data over fsocket. Error: " . $exc->getMessage());
            throw new Exception("Error by sending data over fsocket. Error: " . $exc->getMessage());
        }

        //Check Socket
        if (!$this->fsocket)
        {
            $this->log("Error by sending data over fsocket. Error No.: " . $errno . " Errmsg: " . $errstr, __CLASS__ . " " . __FUNCTION__, "SyncCto");
            throw new Exception("Error by sending data over fsocket. Error No.: " . $errno . " Errmsg: " . $errstr);
        }

        $this->objSyncCtoMeasurement->startMeasurement(__CLASS__, __FUNCTION__ . " WRITE", "RPC: " . $strRpc);

        //send data
        fputs($this->fsocket, $header);
        fputs($this->fsocket, $content);

        $this->objSyncCtoMeasurement->stopMeasurement(__CLASS__, __FUNCTION__ . " WRITE");

        $this->objSyncCtoMeasurement->startMeasurement(__CLASS__, __FUNCTION__ . " READ", "RPC: " . $strRpc);

        stream_set_timeout($this->fsocket, 2);

        $strResponse = "";
        $intBlank = 0;
        for ($i = 0; $i < 1024000; $i++)
        {
            $res = fread($this->fsocket, 512);

            $strResponse .= $res;

            if (strpos($strResponse, "<|@|") !== FALSE && strpos($strResponse, "|@|>") !== FALSE && strlen($res) == 0)
                break;

            if (strlen($res) == 0)
                $intBlank++;

            if ($intBlank == 10000)
                break;

            //usleep(100);
        }

        $this->objSyncCtoMeasurement->stopMeasurement(__CLASS__, __FUNCTION__ . " READ");

        fclose($this->fsocket);

        $this->objSyncCtoMeasurement->stopMeasurement(__CLASS__, __FUNCTION__);

        return $strResponse;
    }

    /**
     * Wandel die HTTP Antwort des Servers um und prÃ¼fe ob alles okay ist.
     *
     * @param string $strResponse HTTP Header + Daten
     * @return string serArray
     */
    protected function parseResponse($strResponse)
    {
        print_r($strResponse);

        $this->objSyncCtoMeasurement->startMeasurement(__CLASS__, __FUNCTION__);

        // Explode
        $arrResponse = explode("\r\n", $strResponse);
        $arrHTTPInformation = explode(" ", $arrResponse[0]);

        // Get HTTP No
        $intHTTPNo = $arrHTTPInformation[1];
        // Get HTTP Status
        $strHTTPStatus = $arrHTTPInformation[2];

        // Check if move on
        if ($intHTTPNo == 303)
        {
            // Debuging
            if ($GLOBALS['TL_CONFIG']['syncCto_debug_log'] == true)
                $this->arrDebug["Responsepart " . microtime(true)] = substr($strResponse, 0, 4098);;

            return array(
                "success" => 0,
                "error" => 303,
                "response" => "",
            );
        }

        // Check if everthing is okay
//        if ( $strHTTPStatus != "OK" )
//        {
//            // Debuging
//            $this->arrDebug["Responsepart " . microtime(true)] = $strResponse;
//
//            $this->log(vsprintf("Error by sending XML to Server. Server answer with %s, %s", array($intHTTPNo, $strHTTPStatus)), __CLASS__ . " " . __FUNCTION__, __FUNCTION__);
//            throw new Exception(vsprintf("Error by sending XML to Server. Server answer with %s, %s", array($intHTTPNo, $strHTTPStatus)));
//        }
//        if ( $intHTTPNo != 200 )
//        {
//            // Debuging
//            $this->arrDebug["Responsepart " . microtime(true)] = $strResponse;
//
//            $this->log(vsprintf("Error by sending XML to Server. Server answer with %s, %s", array($intHTTPNo, $strHTTPStatus)), __CLASS__ . " " . __FUNCTION__, __FUNCTION__);
//            throw new Exception(vsprintf("Error by sending XML to Server. Server answer with %s, %s", array($intHTTPNo, $strHTTPStatus)));
//        }

        $booEncodeChunk = false;
        $intContentStart = -1;

        for ($i = 0; $i < count($arrResponse); $i++)
        {
            if (strpos($arrResponse[$i], "Transfer-Encoding: chunked") !== FALSE)
            {
                $booEncodeChunk = true;
                continue;
            }
            else if (strpos($arrResponse[$i], "Content-Type: text/html") !== FALSE)
            {
                $intContentStart = $i;
                continue;
            }
            else if (strpos($arrResponse[$i], "Set-Cookie:") !== FALSE)
            {
                $mixCookie = str_replace("Set-Cookie:", "", $arrResponse[$i]);
                $mixCookie = explode("; ", $mixCookie);

                foreach ($mixCookie as $itCookie)
                {
                    $itCookie = explode("=", $itCookie);
                    if ($itCookie[1] == "deleted")
                        unset($this->arrCookie[trim($itCookie[0])]);
                    else
                        $this->arrCookie[trim($itCookie[0])] = trim($itCookie[1]);

                    $this->Database->prepare("UPDATE tl_synccto_clients %s WHERE id = ?")
                            ->set(array("cookie" => serialize($this->arrCookie)))
                            ->execute($this->intClient);
                }
            }
        }

        $strContentPart = "";
        $strChunkPart = "";
        $intChunkLength = 0;
        $booStartContent = false;

        for ($i = 0; $i < count($arrResponse); $i++)
        {
            if ($booEncodeChunk == TRUE)
            {
                if ($booStartContent == false && $arrResponse[$i] != "")
                {
                    continue;
                }
                else if ($booStartContent == false && $arrResponse[$i] == "")
                {
                    $booStartContent = true;
                    continue;
                }

                if ($intChunkLength == 0)
                {
                    $intChunkLength = hexdec($arrResponse[$i]);

                    if ($intChunkLength == 0)
                        break;
                }
                else
                {
                    $strChunkPart .= trim($arrResponse[$i]);

                    if (strlen($strChunkPart) == $intChunkLength)
                    {
                        $intChunkLength = 0;
                        $strContentPart .= $strChunkPart;
                        $strChunkPart = "";
                    }
                }
            }
            else
            {
                $strContentPart .= $arrResponse[$i];
            }
        }

        if (($intStart = strpos($strContentPart, "<|@|")) !== FALSE)
        {
            if (($intEnd = strpos($strContentPart, "|@|>")) !== FALSE)
            {
                $strContent = $this->objSyncCtoCodifyengine->Decrypt(substr($strContentPart, $intStart + 4, $intEnd - $intStart));
            }
            else
            {
                // Debuging
                if ($GLOBALS['TL_CONFIG']['syncCto_debug_log'] == true)
                    $this->arrDebug["Responsepart " . microtime(true)] = substr($strResponse, 0, 4098);

                throw new Exception($GLOBALS['TL_LANG']['syncCto']['rpc_missing_endtag']);
            }
        }
        else
        {
            // Debuging
            if ($GLOBALS['TL_CONFIG']['syncCto_debug_log'] == true)
                $this->arrDebug["Responsepart " . microtime(true)] = substr($strResponse, 0, 4098);

            throw new Exception($GLOBALS['TL_LANG']['syncCto']['rpc_missing_starttag']);
        }

        $arrContent = deserialize($strContent);

        if (!is_array($arrContent))
        {
            // Debuging
            if ($GLOBALS['TL_CONFIG']['syncCto_debug_log'] == true)
                $this->arrDebug["Responsepart " . microtime(true)] = substr($strResponse, 0, 4098);

            throw new Exception($GLOBALS['TL_LANG']['syncCto']['rpc_answer_no_array']);
        }

        // Debuging
        if ($GLOBALS['TL_CONFIG']['syncCto_debug_log'] == true)
            $this->arrDebug["Responsepart " . microtime(true)] = substr($strResponse, 0, 4098) . "\n\nDecryption:\n" . $strContent;

        $this->objSyncCtoMeasurement->stopMeasurement(__CLASS__, __FUNCTION__);

        // Return content
        return deserialize($arrContent);
    }

    /**
     * Authenticate the user.
     */
    private function auth()
    {
        // Try to authenticate user
        if ($this->BackendUser->authenticate() === FALSE)
            throw new Exception("Auth fail.");

        return;
    }

}

?>