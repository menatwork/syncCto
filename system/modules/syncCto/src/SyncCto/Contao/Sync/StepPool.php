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

namespace SyncCto\Contao\Sync;

/**
 * Class for step information
 */
class StepPool
{

    protected $arrValues;
    protected $intStepID;

    /**
     *
     * @param type $arrStepPool
     */
    public function __construct($arrStepPool, $intStepID)
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
        if ($this->arrValues == false || !\is_array($this->arrValues))
        {
            return null;
        }

        if (\array_key_exists($name, $this->arrValues))
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
        if ($this->arrValues == false || !\is_array($this->arrValues))
        {
            $this->arrValues = array();
        }

        return $this->arrValues[$name] = $value;
    }

}
