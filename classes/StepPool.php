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
    /**
     * Contains the values for the current step.
     *
     * @var array
     */
    protected array $values = [];

    /**
     * The current step counter.
     *
     * @var int
     */
    protected int $stepID = 0;


    /**
     * @param array $data
     *
     * @param int   $stepID This it the Main step and not the sub step in one function.
     */
    public function __construct(array $data, int $stepID)
    {
        $this->values = $data;
        $this->stepID = $stepID;
    }

    public function __get($name)
    {
        if (empty($this->values)) {
            return null;
        }

        if (key_exists($name, $this->values)) {
            return $this->values[$name];
        } else {
            return null;
        }
    }

    public function __set($name, $value)
    {
        if (empty($this->values)) {
            $this->values = array();
        }

        return $this->values[$name] = $value;
    }

    /**
     * Get the current step.
     *
     * @return int
     */
    public function getStepID(): int
    {
        return $this->stepID;
    }

    /**
     * Set the current step.
     *
     * @param int $stepID
     *
     * @return void
     */
    public function setStepID(int $stepID): void
    {
        $this->stepID = $stepID;
    }

    /**
     * @param int|null $step
     *
     * @return void
     */
    public function initSubStep(?int $step = null): void
    {
        $this->values['step'] = $step ?? 1;
    }

    public function getSubStep(): int
    {
        return $this->values['step'] ?? 0;
    }

    public function setSubStep($step): void
    {
        $this->values['step'] = $step;
    }

    public function increaseSubStep(): void
    {
        if (empty($this->getSubStep())) {
            $this->setSubStep(1);
        } else {
            $this->setSubStep($this->getSubStep() + 1);
        }
    }

    /**
     * Get the values.
     *
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Set the values.
     *
     * @param array $values
     *
     * @return void
     */
    public function setValues(array $values): void
    {
        $this->values = $values;
    }


    /**
     * @return array
     *
     * @deprecated Will be removed
     */
    public function getArrValues()
    {
        return $this->values;
    }

    /**
     * @param $values
     *
     * @return void
     *
     * @deprecated Will be removed.
     */
    public function setArrValues($values)
    {
        $this->values = $values;
    }

    /**
     * @return int
     *
     * @deprecated Will be removed.
     */
    public function getIntStepID()
    {
        return $this->stepID;
    }

    /**
     * @param $stepID
     *
     * @return void
     *
     * @deprecated Will be removed.
     */
    public function setIntStepID($stepID)
    {
        $this->stepID = $stepID;
    }


}