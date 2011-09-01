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
 * @package    ctoCommunication
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
    protected $strUrl;
    protected $strApiKey;
    protected $arrCookies;
    protected $arrRpcList;
    protected $arrError;
    protected $mixOutput;
    //- Objects ------------------
    protected $objCodifyengine;
    protected $objCodifyengineBlow;
    protected $objDebug;

    /* -------------------------------------------------------------------------
     * Core
     */

    /**
     * Constructor
     */
    protected function __construct()
    {
        parent::__construct();

        $this->objCodifyengine = CtoComCodifyengineFactory::getEngine();
        $this->objCodifyengineBlow = CtoComCodifyengineFactory::getEngine("Blowfish");
        $this->objDebug = CtoComDebug::getInstance();

        $this->arrRpcList = $GLOBALS["CTOCOM_FUNCTIONS"];
        $this->arrError = array();
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
            case "activateDebug":
                $this->objDebug->activateDebug = $value;
                break;

            case "activateMeasurement":
                $this->objDebug->activateMeasurement = $value;
                break;

            case "PathDebug":
                $this->objDebug->pathDebug = $value;
                break;

            case "PathMeasurement":
                $this->objDebug->pathMeasurement = $value;
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
            case "activateDebug":
                return $this->objDebug->activateDebug;

            case "activateMeasurement":
                return $this->objDebug->activateMeasurement;

            case "PathDebug":
                return $this->objDebug->pathDebug;

            case "PathMeasurement":
                return $this->objDebug->pathMeasurement;

            default:
                return null;
        }
    }

    /* -------------------------------------------------------------------------
     * Getter / Setter
     */

    //- Setter -------------------

    /**
     * Set the url for connection
     * 
     * @param type $strUrl 
     */
    public function setUrl($strUrl)
    {
        $this->strUrl = $strUrl;
    }

    /**
     * Set the API Key
     * 
     * @param stirng $strApiKey 
     */
    public function setApiKey($strApiKey)
    {
        $this->strApiKey = $strApiKey;
    }

    /**
     * Set the client for the connection.
     *
     * @param int $id ID from client
     */
    public function setClient($strUrl, $strCodifyEngine = "Blowfish")
    {
        $this->strUrl = $strUrl;

        $this->setCodifyengine($strCodifyEngine);
    }

    /**
     * Change codifyengine
     * 
     * @param string $strName 
     */
    public function setCodifyengine($strName = Null)
    {
        $this->objCodifyengine = CtoComCodifyengineFactory::getEngine($strName);
    }

    /**
     * Set Cookie information
     * 
     * @param string $name Key name of array
     * @param mix $value Value for Cookie 
     */
    public function setCookies($name, $value)
    {
        if ($value == "")
            unset($this->arrCookies[$name]);
        else
            $this->arrCookies[$name] = $value;
    }

    //- Getter -------------------

    /**
     * Retrun Url
     * 
     * @return string 
     */
    public function getUrl()
    {
        return $this->strUrl;
    }

    /**
     * Return Api Key
     * 
     * @return string 
     */
    public function getApiKey()
    {
        return $this->strApiKey;
    }

    /**
     * Return Cookies
     * 
     * @return array
     */
    public function getCookies()
    {
        return $this->arrCookies;
    }

    /**
     * Return name of the codifyengine
     * 
     * @return string 
     */
    public function getCodifyengine()
    {
        return $this->objCodifyengine->getName();
    }

    /* -------------------------------------------------------------------------
     * Server / Client Run Functions
     */

    public function runServer($rpc, $arrData = array(), $isGET = FALSE)
    {
        $this->objDebug->startMeasurement(__CLASS__, __FUNCTION__, "RPC: " . $rpc);

        // Check if everything is set
        if ($this->strApiKey == "" || $this->strApiKey == null)
            throw new Exception("The API Key is not set. Please set first API Key.");

        if ($this->strUrl == "" || $this->strUrl == null)
            throw new Exception("There is no URL set for connection. Please set first the url.");

        // Merge Cookie Array
        if ($arrCookies != null && count($arrCookies) != 0)
        {
            $this->arrCookies = array_unique(array_merge($this->arrCookies, $arrCookies));
        }

        // Add Get Parameter
        $strCryptApiKey = $this->objCodifyengineBlow->Encrypt($rpc . "@|@" . $this->strApiKey);
        $strCryptApiKey = urlencode($strCryptApiKey);

        if (strpos($this->strUrl, "?") !== FALSE)
        {
            $this->strUrl .= "&engine=" . $this->objCodifyengine->getName() . "&act=" . $rpc . "&apikey=" . $strCryptApiKey;
        }
        else
        {
            $this->strUrl .= "?engine=" . $this->objCodifyengine->getName() . "&act=" . $rpc . "&apikey=" . $strCryptApiKey;
        }

        // Set Key for codifyengine
        $this->objCodifyengine->setKey($this->strApiKey);

        // Last exception
        $objLastException = null;

        // New Request
        $objRequest = new RequestExtended();

        // Which method ? GET or POST
        if ($isGET)
        {
            $objRequest->method = "GET";
            foreach ($arrData as $key => $value)
            {
                $this->strUrl .= "&" . $value["name"] . "=" . $value["value"];
            }
        }
        else
        {
            // Build Multipart Post Data
            $objMultipartFormdata = new MultipartFormdata();
            foreach ($arrData as $key => $value)
            {
                if (isset($value["filename"]) == true && strlen($value["filename"]) != 0)
                {
                    // Set field for file
                    $objMultipartFormdata->setFileField($value["name"], $value["filename"], $value["mime"]);
                }
                else
                {
                    // Encrypt funktion
                    $strValue = $this->objCodifyengine->Encrypt(serialize(array("data" => $value["value"])));
                    // Set field
                    $objMultipartFormdata->setField($value["name"], $strValue);
                }
            }

            // Create HTTP Data code
            $objRequest->data = $objMultipartFormdata->compile();

            // Set typ and mime typ
            $objRequest->method = "POST";
            $objRequest->datamime = $objMultipartFormdata->getContentTypeHeader();
        }

        // Send new request
        $objRequest->send($this->strUrl);

        /* Debug */
        print_r($objRequest->request);
        echo "<br>|--|<br>";
        print_r($objRequest->response);
        echo "<br>|--|<br>";
        echo "<br>|--------------------------------|<br>";

        // Debug
        $this->objDebug->addDebug("Request", $objRequest->request);
        $this->objDebug->addDebug("Response", $objRequest->response);

        // Check if evething is okay for connection
        if ($objRequest->hasError())
        {
            $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__);
            throw new Exception("Error by sending request with measages: " . $objRequest->code . " " . $objRequest->error);
        }

        if (strlen($objRequest->response) == 0)
        {
            $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__);
            throw new Exception("We got a blank response from server.");
        }

        if (preg_match("/.*Fatal error:.*/i", $objRequest->response))
        {
            $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__);
            throw new Exception("We got a Fatal error on client site. " . $objRequest->response);
        }

        if (preg_match("^\<\|\@\|.*\|\@\|\>^i", $objRequest->response) == 0)
        {
            $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__);
            throw new Exception("Could not find start or endtag from response.");
        }

        $mixContent = $objRequest->response;

        $intStart = intval(strpos($mixContent, "<|@|") + 4);
        $intLength = intval(strpos($mixContent, "|@|>") - $intStart);

        $mixContent = $this->objCodifyengine->Decrypt(substr($mixContent, $intStart, $intLength));

        // Check response for ser. array
        if (preg_match("^a:.*:{.*}^i", $mixContent) == 0)
        {
            $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__);
            throw new Exception("Response is not a array. Maybe wrong key or codifyengine.");
        }

        $mixContent = deserialize($mixContent);
        if (is_array($mixContent) == false)
        {
            $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__);
            throw new Exception("Response is not a array. Maybe wrong key or codifyengine.");
        }

        if ($mixContent["success"] == 1)
        {
            $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__);
            return $mixContent["response"];
        }
        else
        {
            $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__);

            $string = vsprintf("There was a error on client site with message: %s. | RPC Call: %s | Class: %s | Function: %s", array(
                $mixContent["error"][0]["msg"],
                $mixContent["error"][0]["rpc"],
                $mixContent["error"][0]["class"],
                $mixContent["error"][0]["function"],
                    ));

            throw new Exception($string);
        }

        $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__);
    }

    /**
     * Run the communication as client
     *
     * @return null
     */
    public function runClient()
    {
        // Start measurement
        $this->objDebug->startMeasurement(__CLASS__, __FUNCTION__, "RPC: " . $this->Input->get("act"));

        /** --------------------------------------------------------------------
         * API Key - Check
         */
        if (strlen($this->Input->get("apikey")) == 0)
        {
            $this->log(vsprintf("Call from %s without a API Key.", $this->Environment->ip), __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
            exit();
        }

        $mixVar = html_entity_decode($this->Input->get("apikey"));
        $mixVar = $this->objCodifyengineBlow->Decrypt($this->Input->get("apikey"));
        $mixVar = trimsplit("@\|@", $mixVar);
        $strApiKey = $mixVar[1];
        $strAction = $mixVar[0];

        if ($strAction != $this->Input->get("act"))
        {
            $this->log(vsprintf("Error Api Key from %s. Request action: %s | Key action: %s | Api: %s", array(
                        $this->Environment->ip,
                        $this->Input->get("$strAction"),
                        $strAction,
                        $strApiKey
                    )), __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
            exit();
        }

        if ($GLOBALS['TL_CONFIG']['ctoCom_APIKey'] != $strApiKey)
        {
            $this->log(vsprintf("Call from %s with a wrong API Key: %s", array($this->Environment->ip, $this->Input->get("apikey"))), __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
            exit();
        }

        /** --------------------------------------------------------------------
         * Change the Codifyengine if set
         */
        if (strlen($this->Input->get("engine")) != 0)
        {
            // Try to change codifyengine
            try
            {
                // Set new an reload key
                $this->setCodifyengine($this->Input->get("engine"));
                $this->objCodifyengine->setKey($GLOBALS['TL_CONFIG']['ctoCom_APIKey']);
            }
            // Error by setting new enigne. Send msg in cleartext
            catch (Exception $exc)
            {
                $this->log("Try to load codifyengine for ctoCommunication with error: " . $exc->getMessage(), __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
                exit();
            }
        }
        else
        {
            $this->setCodifyengine("Blowfish");
            $this->objCodifyengine->setKey($GLOBALS['TL_CONFIG']['ctoCom_APIKey']);
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
            if (!key_exists($mixRPCCall, $this->arrRpcList))
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
                $arrParameter = array();

                if ($this->arrRpcList[$mixRPCCall]["parameter"] != FALSE && is_array($this->arrRpcList[$mixRPCCall]["parameter"]))
                {
                    switch ($this->arrRpcList[$mixRPCCall]["typ"])
                    {
                        case "POST":
                            // Decode post 
                            foreach ($_POST as $key => $value)
                            {
                                $mixPost = $this->Input->post($key);
                                $mixPost = $this->objCodifyengine->Decrypt($mixPost);
                                $mixPost = deserialize($mixPost);
                                $mixPost = $mixPost["data"];

                                $this->Input->setPost($key, $mixPost);
                            }

                            // Check if all post are set
                            foreach ($this->arrRpcList[$mixRPCCall]["parameter"] as $value)
                            {
                                if (!$this->Input->post($value))
                                {
                                    $this->arrError[] = array(
                                        "language" => "rpc_data_missing",
                                        "id" => 2,
                                        "object" => $value,
                                        "msg" => "Missing data for " . $value,
                                        "rpc" => $mixRPCCall,
                                        "class" => $this->arrRpcList[$mixRPCCall]["class"],
                                        "function" => $this->arrRpcList[$mixRPCCall]["function"],
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
                $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__);
                return $this->generateOutput();
            }

            /** ----------------------------------------------------------------
             * Try to execute rpc call
             */
            try
            {
                $this->objDebug->startMeasurement($this->arrRpcList[$mixRPCCall]["class"], $this->arrRpcList[$mixRPCCall]["function"]);

                $strClassname = $this->arrRpcList[$mixRPCCall]["class"];

                if (!class_exists($strClassname))
                {
                    $this->arrError[] = array(
                        "language" => "rpc_class_not_exists",
                        "id" => 4,
                        "object" => "",
                        "msg" => "The choosen class didn`t exists.",
                        "rpc" => $mixRPCCall,
                        "class" => $this->arrRpcList[$mixRPCCall]["class"],
                        "function" => $this->arrRpcList[$mixRPCCall]["function"],
                    );
                }

                $objReflection = new ReflectionClass($strClassname);
                if ($objReflection->hasMethod("getInstance"))
                {
                    $object = call_user_func_array(array($this->arrRpcList[$mixRPCCall]["class"], "getInstance"), array());
                    $this->mixOutput = call_user_func_array(array($object, $this->arrRpcList[$mixRPCCall]["function"]), $arrParameter);
                }
                else
                {
                    $object = new $this->arrRpcList[$mixRPCCall]["class"];
                    $this->mixOutput = call_user_func_array(array($object, $this->arrRpcList[$mixRPCCall]["function"]), $arrParameter);
                }

                $this->objDebug->stopMeasurement($this->arrRpcList[$mixRPCCall]["class"], $this->arrRpcList[$mixRPCCall]["function"]);
            }
            catch (Exception $exc)
            {
                $this->arrError[] = array(
                    "language" => "rpc_unknown_exception",
                    "id" => 3,
                    "object" => "",
                    "msg" => $exc->getTraceAsString(),
                    "rpc" => $mixRPCCall,
                    "class" => $this->arrRpcList[$mixRPCCall]["class"],
                    "function" => $this->arrRpcList[$mixRPCCall]["function"],
                );
            }
        }

        $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__);
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
        $this->objDebug->startMeasurement(__CLASS__, __FUNCTION__);

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

        $strOutput = $this->objCodifyengine->Encrypt($strOutput);

        $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__);

        return "<|@|" . $strOutput . "|@|>";
    }

}

?>