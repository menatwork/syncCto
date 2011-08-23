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
class CtoComDebug extends Backend
{

    //- Singelten pattern --------
    protected static $instance = null;
    //- Vars ---------------------
    protected $arrMeasurement;
    protected $arrDebug;
    //- Config -------------------
    protected $booMeasurement;
    protected $booDebug;
    protected $strPathMeasurement;
    protected $strPathDebug;

    /* -------------------------------------------------------------------------
     * Core
     */

    protected function __construct()
    {
        parent::__construct();

        $this->strPathDebug = "system/tmp/CtoComDebug.txt";
        $this->strPathMeasurement = "system/tmp/CtoComMeasurement.txt";

        $this->booDebug = false;
        $this->booMeasurement = false;
    }

    public function __destruct()
    {
        if ($this->booDebug)
            $this->writeDebug();

        if ($this->booMeasurement)
            $this->writeMeasurement();
    }

    /**
     * Get instance. 
     * 
     * @return CtoComDebug 
     */
    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new CtoComDebug();

        return self::$instance;
    }

    public function __get($name)
    {
        switch ($name)
        {
            case "activateMeasurement":
                return $this->booMeasurement;

            case "activateDebug":
                return $this->booDebug;

            case "pathMeasurement":
                return $this->strPathMeasurement;

            case "pathDebug":
                return $this->strPathDebug;

            default:
                return null;
        }
    }

    public function __set($name, $value)
    {
        switch ($name)
        {
            case "activateMeasurement":
                $this->booMeasurement = (boolean) $value;
                break;

            case "activateDebug":
                $this->booDebug = (boolean) $value;
                break;

            case "pathMeasurement":
                $this->strPathMeasurement = $value;
                break;

            case "pathDebug":
                $this->strPathDebug = $value;
                break;

            default:
                throw new Exception("Unknown set typ: " . $name);
        }
    }

    /* -------------------------------------------------------------------------
     * Mesurement and Debug Call Functions
     */

    public function startMeasurement($strClass, $strFunction, $strInformation = "")
    {
        if (!$this->booMeasurement)
            return;

        $this->arrMeasurement[$strClass . "|" . $strFunction] = array(
            "class" => $strClass,
            "function" => $strFunction,
            "information" => $strInformation,
            "start" => microtime(true),
            "mem_peak" => 0,
            "mem_start" => memory_get_usage(true),
            "mem_end" => 0,
        );
    }

    public function stopMeasurement($strClass, $strFunction)
    {
        if (!$this->booMeasurement)
            return;

        $floStop = microtime(true);
        $floTime = $floStop - $this->arrMeasurement[$strClass . "|" . $strFunction]["start"];

        $this->arrMeasurement[$strClass . "|" . $strFunction] = array_merge($this->arrMeasurement[$strClass . "|" . $strFunction], array(
            "stop" => $floStop,
            "time" => $floTime,
            "mem_end" => memory_get_usage(true),
            "mem_peak" => memory_get_peak_usage(true))
        );
    }

    public function addDebug($Debugname, $value)
    {
        $this->arrDebug[$Debugname . microtime(true)] = $value;
    }

    /* -------------------------------------------------------------------------
     * Write Functions
     */

    protected function writeMeasurement()
    {
        try
        {
            $objFile = new File($this->strPathMeasurement);
            $objFile->delete();

            $intTime = time();

            if (count($this->arrMeasurement) == 0)
            {
                $objFile->close();
                return;
            }

            $strContent = "";
            $strContent .= ">>|------------------------------------------------------";
            $strContent .= ">>|-- Start Measurement Core at " . date("H:i:s d.m.Y", $intTime);
            $strContent .= ">>\n";

            foreach ($this->arrMeasurement as $key => $value)
            {
                $strContent .= "Class: " . $value["class"] . "\tFunction: " . $value["function"] . "\tInformation: " . $value["information"] .
                        "\n\t\tStart: " . $value["start"] . "\tEnd: " . $value["stop"] . "\tExecutiontime: " . number_format($value["time"], 5, ",", ".") . " Sekunden" .
                        "\n\t\tStartMem: " . round($value["mem_start"] / 1048576, 4) . "MB\tEndMem: " . round($value["mem_end"] / 1048576, 4) . "MB\t\tPeakMem: " . round($value["mem_peak"] / 1048576, 4) . " MB" .
                        "\n|----\n";
            }

            $strContent .= ">>";
            $strContent .= ">>|-- Close Measurement Core at " . date("H:i:s d.m.Y", $intTime);
            $strContent .= ">>|------------------------------------------------------\n";

            if (!$objFile->write($varData))
                $this->log("Could not write CtoCom Measurement file.", __FUNCTION__ . " | " . __CLASS__, TL_ERROR);

            $objFile->close();
        }
        catch (Exception $exc)
        {
            $this->log("Could not write CtoCom Measurement file. Exit with error: " . $exc->getMessage(), __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
        }
    }

    protected function writeDebug()
    {
        try
        {
            $objFile = new File($this->strPathDebug);
            $objFile->delete();

            $intTime = time();

            if (count($this->arrDebug) == 0)
            {
                $objFile->close();
                return;
            }

            $strContent = "";

            $strContent .= "\n<|++++++++++++++++++++++++++++++++++++++++++++++++++++++|>";
            $strContent .="\n  + Hinweis:";
            $strContent .="\n<|++++++++++++++++++++++++++++++++++++++++++++++++++++++|>\n\n";
            $strContent .=">>|------------------------------------------------------";
            $strContent .="\n>>|-- Start Log at " . date("H:i:s d.m.Y", $intTime);
            $strContent .="\n>>";
            $strContent .="\n";

            foreach ($this->arrDebug as $key => $value)
            {
                $$strContent .="\n";
                $strContent .="<|-- Start " . $key . " -----------------------------------|>";
                $strContent .="\n\n";

                $strContent .=trim($value);

                $strContent .="\n\n";
                $strContent .="<|-- End " . $key . " -------------------------------------|>";
                $strContent .="\n";
                $strContent .="\n";
            }

            $strContent .="\n";
            $strContent .="\n>>";
            $strContent .="\n>>|-- Close Log at " . date("H:i:s d.m.Y", $intTime);
            $strContent .="\n>>|------------------------------------------------------\n";

            if (!$objFile->write($varData))
                $this->log("Could not write CtoCom Debug file.", __FUNCTION__ . " | " . __CLASS__, TL_ERROR);

            $objFile->close();
        }
        catch (Exception $exc)
        {
            $this->log("Could not write CtoCom Measurement file. Exit with error: " . $exc->getMessage(), __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
        }
    }

}

?>