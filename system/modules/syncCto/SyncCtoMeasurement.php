<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

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

class SyncCtoMeasurement extends Backend
{

    protected static $instance = null;
    protected $fileHand;
    //----
    protected $objSyncCtoFiles;
    //----
    protected $arrInformation;
    protected $arrOutput;
    //----
    protected $arrOnlyClass = array(
    );
    protected $arrOnlyFunctions = array(
    );

    protected function __construct()
    {
        parent::__construct();

        $this->arrInformation = array();
        $this->arrOutput = array();

        $this->objSyncCtoFiles = SyncCtoFiles::getInstance();
    }

    public function __destruct()
    {
        if ($GLOBALS['TL_CONFIG']['syncCto_measurement_log'] != true)
        {
            return;
        }

        $intTime = time();

        $strFilepath = $this->objSyncCtoFiles->buildPath($GLOBALS['SYC_PATH']['debug'], "measurement.txt");

        $this->fileHand = fopen($strFilepath, "a+");

        if ($this->arrOutput != "")
        {
            fwrite($this->fileHand, "\n>>|------------------------------------------------------");
            fwrite($this->fileHand, "\n>>|-- Start Measurement Core at " . date("H:i:s d.m.Y", $intTime));
            fwrite($this->fileHand, "\n>>\n\n");

            foreach (array_reverse($this->arrOutput) as $key => $value)
            {
                fwrite($this->fileHand, $value);
            }

            fwrite($this->fileHand, "\n>>");
            fwrite($this->fileHand, "\n>>|-- Close Measurement Core at " . date("H:i:s d.m.Y", $intTime));
            fwrite($this->fileHand, "\n>>|------------------------------------------------------\n\n");
        }

        fclose($this->fileHand);
    }

    /**
     *
     * @return SyncCtoMeasurement 
     */
    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new SyncCtoMeasurement();
        }

        return self::$instance;
    }

    private function writeData($arrData)
    {
        $this->arrOutput[] = "Class: " . $arrData["class"] . "\tFunction: " . $arrData["function"] . "\tInformation: " . $arrData["information"] .
                "\n\t\tStart: " . $arrData["start"] . "\tEnd: " . $arrData["stop"] . "\tExecutiontime: " . number_format($arrData["time"], 5, ",", ".") . " Sekunden" .
                "\n\t\tStartMem: " . round($arrData["mem_start"] / 1048576, 4) . "MB\tEndMem: " . round($arrData["mem_end"] / 1048576, 4) . "MB\t\tPeakMem: " . round($arrData["mem_peak"] / 1048576, 4) . " MB" .
                "\n|----\n";
    }

    //--------------------------------------------------------------------------

    public function startMeasurement($strClass, $strFunction, $strInformation = "")
    {
        if ($GLOBALS['TL_CONFIG']['syncCto_measurement_log'] != true)
        {
            return;
        }

        if (count($this->arrOnlyClass) != 0)
        {
            if (in_array($strClass, $this->arrOnlyClass))
            {
                return;
            }
        }

        if (count($this->arrOnlyFunctions) != 0)
        {
            if (in_array($strFunction, $this->arrOnlyFunctions))
            {
                return;
            }
        }

        $this->arrInformation[$strClass . "|" . $strFunction] = array(
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
        if ($GLOBALS['TL_CONFIG']['syncCto_measurement_log'] != true)
        {
            return;
        }

        $floStop = microtime(true);
        $floTime = $floStop - $this->arrInformation[$strClass . "|" . $strFunction]["start"];

        if (count($this->arrOnlyClass) != 0)
        {
            if (in_array($strClass, $this->arrOnlyClass))
            {
                return;
            }
        }

        if (count($this->arrOnlyFunctions) != 0)
        {
            if (in_array($strFunction, $this->arrOnlyFunctions))
            {
                return;
            }
        }

        $this->arrInformation[$strClass . "|" . $strFunction] = array_merge($this->arrInformation[$strClass . "|" . $strFunction], array("stop" => $floStop, "time" => $floTime, "mem_end" => memory_get_usage(true), "mem_peak" => memory_get_peak_usage(true)));

        $this->writeData($this->arrInformation[$strClass . "|" . $strFunction]);
        unset($this->arrInformation[$strClass . "|" . $strFunction]);
    }

}

?>