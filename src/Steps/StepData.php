<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

namespace MenAtWork\SyncCto\Steps;

/**
 * Class for step information
 */
class StepData
{
    /**
     * @var array
     */
    protected $values;

    /**
     * @var int
     */
    protected $step;

    /**
     * @param array $contentData
     *
     * @param int   $step
     */
    public function __construct($contentData, $step)
    {
        $this->values = $contentData;

        if (!is_array($this->values)) {
            $this->values = array();
        }

        $this->step = $step;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function setValues($values)
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

    public function getStep()
    {
        return $this->step;
    }
}