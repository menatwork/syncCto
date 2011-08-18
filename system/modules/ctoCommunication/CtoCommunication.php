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
class CtoCommunication extends Backend
{
    /* -------------------------------------------------------------------------
     * Vars
     */

    //- Singelten pattern --------
    protected static $instance = null;
    //- Vars ---------------------
    private $arrParameter = array();
    //- Objects ------------------
    protected $objCodifyengine;

    /* -------------------------------------------------------------------------
     * Core
     */

    /**
     * Constructor
     */
    protected function __construct()
    {
        parent::__construct();

        $this->objCodifyengine = CtoCodifyengineFactory::getEngine();
    }

    /**
     * Singelton Pattern
     * 
     * @return CtoCommunication 
     */
    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new CtoCommunication();

        return self::$instance;
    }

    /**
     * Magical function for setter
     * 
     * @param string $name
     * @param mix $value 
     */
    public function __set($name, $value)
    {
        switch ($name)
        {
            case "srtUrl":
                $this->arrParameter["strUrl"] = $value;
                break;

            case "srtKey":
                $this->arrParameter["srtKey"] = $value;
                break;

            case "strCodifyEngine":
                $this->setCodifyEngine($value);
                break;

            default:
                break;
        }
    }

    /**
     * Magical functions for getter
     * 
     * @param string $name
     * @return mix 
     */
    public function __get($name)
    {
        switch ($name)
        {
            case "srtUrl":
                return $this->arrParameter["strUrl"];

            case "srtKey":
                return $this->arrParameter["srtKey"];

            case "strCodifyEngine":
                return $this->objCodifyengine->getName();

            default:
                return null;
        }
    }

    /* -------------------------------------------------------------------------
     * Getter / Setter
     */

    /**
     * Set the client for the connection.
     *
     * @param int $id ID from client
     */
    public function setClient($strUrl, $strKey, $strCodifyEngine = "Blowfish")
    {
        $this->arrParameter["strUrl"] = $strUrl;
        $this->arrParameter["srtKey"] = $strKey;

        $this->setCodifyEngine($strCodifyEngine);
    }

    public function setCodifyEngine($strName = Null)
    {
        $this->objSyncCtoCodifyengine = CtoCodifyengineFactory::getEngine($strName);
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

    public function runServer($rpc, $arrData = array(), $isGET = FALSE)
    {
        $this->objCodifyengine->setKey($this->arrParameter["strKey"]);

        for ($i = 0; $i < 2; $i++)
        {
            // New Request
            $objRequest = new Request();      
            
            // Which method get or post
            if($isGET)
            {
                $objRequest->method = "GET";
            }
            else
            {
                $objRequest->method = "POST";
            }
            
            // Send new request
            $objRequest->send($this->arrParameter["strUrl"]);
            
            // Check if evething is okay for connection
            if($objRequest->error != "")
            {
                throw new Exception("Error by connection to Client", "000", $objRequest->error);
            }
            
            // Check if request is okay
            
            
            
            
            print_r($objRequest->code);
            echo "<br>";            
            print_r($objRequest->request);
            echo "<br>";
            print_r($objRequest->response);
            echo "<br>";
            
        }
    }

    //--------------------------------------------------------------------------
    // ALT
    //--------------------------------------------------------------------------
    
    /**
     * Run the communication as server
     *
     * @param string $rpc
     * @param array $arrData
     * @param bool $isFileUpload
     * @return array
     */
    protected function runServerOld($rpc, $arrData = array(), $isGET = FALSE)
    {
        $this->objSyncCtoCodifyengine->setKey($this->arrParameter["strKey"]);

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
                    $this->auth();
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
        try
        {
            $this->BackendUser->authenticate();
        }
        catch (Exception $exc)
        {
            
        }
    }

}

?>