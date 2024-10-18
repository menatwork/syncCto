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
class ContentData
{
    /**
     * @var array
     */
    protected array $values = array();

    /**
     * @var int|type
     */
    protected int $step = 0;

    /**
     * @param $contentData
     * @param $step
     */
    public function __construct(array $contentData, int $step)
    {
        $this->values = $contentData;
        $this->step = $step;
    }

    public function getArrValues()
    {
        return $this->values;
    }

    public function setArrValues($values)
    {
        $this->values = $values;
    }

    public function nextStep()
    {
        $this->step++;
    }

    public function getTitle()
    {
        return $this->values[$this->step]["title"];
    }

    public function setTitle($title)
    {
        $this->values[$this->step]["title"] = $title;
    }

    public function getState()
    {
        return $this->values[$this->step]["state"];
    }

    public function setState($state)
    {
        $this->values[$this->step]["state"] = $state;
    }

    public function getDescription()
    {
        return $this->values[$this->step]["description"];
    }

    public function setDescription($description)
    {
        $this->values[$this->step]["description"] = $description;
    }

    public function getMsg()
    {
        return $this->values[$this->step]["msg"];
    }

    public function setMsg($msg)
    {
        $this->values[$this->step]["msg"] = $msg;
    }

    public function getHtml()
    {
        return $this->values[$this->step]["html"];
    }

    public function setHtml($html)
    {
        $this->values[$this->step]["html"] = $html;
    }

    public function setStep($intStep)
    {
        $this->step = $intStep;
    }

    public function __get($name)
    {
        throw new Exception("Unknown key for datacontent $name");
    }

    public function __set($name, $value)
    {
        throw new Exception("Unknown key for datacontent $name");
    }
}