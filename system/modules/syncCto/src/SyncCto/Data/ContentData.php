<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

namespace SyncCto\Data;

/**
 * Class for step information
 */
class ContentData
{
    protected $arrValues;
    protected $intStep;

    /**
     *
     * @param type $arrContentData
     * @param type $intStep
     */
    public function __construct($arrContentData, $intStep)
    {
        $this->arrValues = $arrContentData;

        if (!\is_array($this->arrValues))
        {
            $this->arrValues = array();
        }

        $this->intStep = $intStep;
    }

    public function getArrValues()
    {
        return $this->arrValues;
    }

    public function setArrValues($arrValues)
    {
        $this->arrValues = $arrValues;
    }

    public function nextStep()
    {
        $this->intStep++;
    }

    public function getTitle()
    {
        return $this->arrValues[$this->intStep]["title"];
    }

    public function setTitle($title)
    {
        $this->arrValues[$this->intStep]["title"] = $title;
    }

    public function getState()
    {
        return $this->arrValues[$this->intStep]["state"];
    }

    public function setState($state)
    {
        $this->arrValues[$this->intStep]["state"] = $state;
    }

    public function getDescription()
    {
        return $this->arrValues[$this->intStep]["description"];
    }

    public function setDescription($description)
    {
        $this->arrValues[$this->intStep]["description"] = $description;
    }

    public function getMsg()
    {
        return $this->arrValues[$this->intStep]["msg"];
    }

    public function setMsg($msg)
    {
        $this->arrValues[$this->intStep]["msg"] = $msg;
    }

    public function getHtml()
    {
        return $this->arrValues[$this->intStep]["html"];
    }

    public function setHtml($html)
    {
        $this->arrValues[$this->intStep]["html"] = $html;
    }

    public function setStep($intStep)
    {
        $this->intStep = $intStep;
    }

    public function __get($name)
    {
        throw new \Exception("Unknown key for datacontent $name");
    }

    public function __set($name, $value)
    {
        throw new \Exception("Unknown key for datacontent $name");
    }
}
