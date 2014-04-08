<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

/**
 * Class for step information
 */
class StepPool
{

    protected $arrValues;
    protected $intStepID;

    /**
     *
     * @param array $arrStepPool List with all data.
     *
     * @param int  $intStepID The current step.
     */
    public function __construct($arrStepPool, $intStepID = 0)
    {
        $this->arrValues = $arrStepPool;
        $this->intStepID = $intStepID;
    }

    public function getArrValues()
    {
        return $this->arrValues;
    }

    public function setArrValues($arrValues)
    {
        $this->arrValues = $arrValues;
    }

    public function getIntStepID()
    {
        return $this->intStepID;
    }

    public function setIntStepID($intStepID)
    {
        $this->intStepID = $intStepID;
    }

    public function __get($name)
    {
        if ($this->arrValues == false || !is_array($this->arrValues))
        {
            return null;
        }

        if (key_exists($name, $this->arrValues))
        {
            return $this->arrValues[$name];
        }
        else
        {
            return null;
        }
    }

    public function __set($name, $value)
    {
        if ($this->arrValues == false || !is_array($this->arrValues))
        {
            $this->arrValues = array();
        }

        return $this->arrValues[$name] = $value;
    }

}