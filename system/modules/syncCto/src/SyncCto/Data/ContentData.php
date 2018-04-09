<?php

/**
 * This file is part of menatwork/synccto.
 *
 * (c) 2014-2018 MEN AT WORK.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    menatwork/synccto
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2014-2018 MEN AT WORK.
 * @license    https://github.com/menatwork/syncCto/blob/master/LICENSE LGPL-3.0-or-later
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
