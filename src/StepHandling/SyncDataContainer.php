<?php

namespace MenAtWork\SyncCto\StepHandling;

/**
 * Class SyncDataContainer
 *
 * @package StepHandling
 */
class SyncDataContainer
{
    // --- Steps state

    /**
     * Step is the main indicator which step is running like DB sync or file sync.
     *
     * @var int
     */
    protected int $step;

    /**
     * Iterator position pointer.
     *
     * @var int
     */
    protected int $currentStep = 0;

    /**
     * The step inside one step.
     *
     * @var int
     */
    protected int $subStep;

    // --- State flags

    /**
     * @var bool
     */
    protected bool $stateFinished;

    /**
     * @var bool
     */
    protected bool $stateRefresh;

    /**
     * @var bool
     */
    protected bool $stateAbort;

    /**
     * @var bool
     */
    protected bool $stateError;

    // --- Text

    /**
     * @var string
     */
    protected string $errorMessage;

    /**
     * @var string
     */
    private string $information;

    // --- Sub container

    /**
     * @var array
     */
    private array $syncSettings;

    /**
     * @var array
     */
    protected array $stepValues;

    // --- FE information

    /**
     * @var float
     */
    private float  $start;

    /**
     * @var string
     */
    private string $headline;

    /**
     * @var string
     */
    private string $goBack;

    /**
     * @var string
     */
    private string $url;

    // --- Iterator for steps

    /**
     * @inheritDoc
     */
    public function current()
    {
        if ($this->currentStep === -1) {
            return null;
        }

        $keys       = array_keys($this->stepValues);
        $currentKey = $keys[$this->currentStep];
        $this->step = $currentKey;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        $nextStep = ($this->currentStep + 1);
        $keys     = array_keys($this->stepValues);
        if (isset($keys[$nextStep])) {
            $this->currentStep = $nextStep;
        } else {
            $this->currentStep = -1;
        }
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        if ($this->currentStep === -1) {
            return false;
        }

        $keys = array_keys($this->stepValues);

        return $keys[$this->currentStep];
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        return $this->currentStep !== -1;
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $keys = array_keys($this->stepValues);
        if (isset($keys[0])) {
            $this->currentStep = 0;
        } else {
            $this->currentStep = -1;
        }
    }

    // --- Custom helper

    public function nextStep(): void
    {
        $this->step++;
    }

    public function nextSubStep(): void
    {
        if (!isset($this->stepValues[$this->step]['step'])) {
            $this->stepValues[$this->step]['step'] = 0;
        }

        $this->stepValues[$this->step]['step'] = $this->stepValues[$this->step]['step'] + 1;
    }

    // --- Step data

    public function getStepTitle(): string
    {
        return $this->stepValues[$this->step]['title'];
    }

    public function setStepTitle(string $title): void
    {
        $this->stepValues[$this->step]['title'] = $title;
    }

    public function getStepState(): string
    {
        return $this->stepValues[$this->step]['state'];
    }

    public function setStepState(string $state): void
    {
        $this->stepValues[$this->step]['state'] = $state;
    }

    public function getStepDescription(): string
    {
        return $this->stepValues[$this->step]['description'];
    }

    public function setStepDescription(string $description): void
    {
        $this->stepValues[$this->step]['description'] = $description;
    }

    public function getStepMsg(): string
    {
        return $this->stepValues[$this->step]['msg'];
    }

    public function setStepMsg(string $msg): void
    {
        $this->stepValues[$this->step]['msg'] = $msg;
    }

    public function getStepHtml(): string
    {
        return $this->stepValues[$this->step]['html'];
    }

    public function setStepHtml(string $html): void
    {
        $this->stepValues[$this->step]['html'] = $html;
    }

    // --- Getter // Setter

    /**
     * Set the step id.
     *
     * @param int $step
     *
     * @return self
     */
    public function setStep(int $step): self
    {
        $this->step = $step;

        return $this;
    }

    /**
     * Get the step id.
     *
     * @return int
     */
    public function getStep(): int
    {
        return $this->step;
    }

    /**
     * Set the sub step for a step.
     *
     * @param int $step
     *
     * @return self
     */
    public function setSubStep(int $step): self
    {
        $this->stepValues[$this->step]['step'] = $step;

        return $this;
    }

    /**
     * Get the sub step for a step.
     *
     * @return int
     */
    public function getSubStep(): int
    {
        if (!isset($this->stepValues[$this->step]['step'])) {
            $this->stepValues[$this->step]['step'] = 0;
        }

        return (int)$this->stepValues[$this->step]['step'];
    }

    /**
     * @return bool
     */
    public function isStateFinished(): bool
    {
        return $this->stateFinished;
    }

    /**
     * @param bool $stateFinished
     *
     * @return SyncDataContainer
     */
    public function setStateFinished(bool $stateFinished): SyncDataContainer
    {
        $this->stateFinished = $stateFinished;

        return $this;
    }

    /**
     * @return bool
     */
    public function isStateRefresh(): bool
    {
        return $this->stateRefresh;
    }

    /**
     * @param bool $stateRefresh
     *
     * @return SyncDataContainer
     */
    public function setStateRefresh(bool $stateRefresh): SyncDataContainer
    {
        $this->stateRefresh = $stateRefresh;

        return $this;
    }

    /**
     * @return bool
     */
    public function isStateAbort(): bool
    {
        return $this->stateAbort;
    }

    /**
     * @param bool $stateAbort
     *
     * @return SyncDataContainer
     */
    public function setStateAbort(bool $stateAbort): SyncDataContainer
    {
        $this->stateAbort = $stateAbort;

        return $this;
    }

    /**
     * @return bool
     */
    public function isStateError(): bool
    {
        return $this->stateError;
    }

    /**
     * @param bool $stateError
     *
     * @return SyncDataContainer
     */
    public function setStateError(bool $stateError): SyncDataContainer
    {
        $this->stateError = $stateError;

        return $this;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * @param string $errorMessage
     *
     * @return SyncDataContainer
     */
    public function setErrorMessage(string $errorMessage): SyncDataContainer
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    /**
     * @return string
     */
    public function getInformation(): string
    {
        return $this->information;
    }

    /**
     * @param string $information
     *
     * @return SyncDataContainer
     */
    public function setInformation(string $information): SyncDataContainer
    {
        $this->information = $information;

        return $this;
    }

    /**
     * @return float
     */
    public function getStart(): float
    {
        return $this->start;
    }

    /**
     * @param float $start
     *
     * @return SyncDataContainer
     */
    public function setStart(float $start): SyncDataContainer
    {
        $this->start = $start;

        return $this;
    }

    /**
     * @return string
     */
    public function getHeadline(): string
    {
        return $this->headline;
    }

    /**
     * @param string $headline
     *
     * @return SyncDataContainer
     */
    public function setHeadline(string $headline): SyncDataContainer
    {
        $this->headline = $headline;

        return $this;
    }

    /**
     * @return string
     */
    public function getGoBack(): string
    {
        return $this->goBack;
    }

    /**
     * @param string $goBack
     *
     * @return SyncDataContainer
     */
    public function setGoBack(string $goBack): SyncDataContainer
    {
        $this->goBack = $goBack;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return SyncDataContainer
     */
    public function setUrl(string $url): SyncDataContainer
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return array
     */
    public function getSyncSettings(): array
    {
        return $this->syncSettings;
    }

    /**
     * @param array $syncSettings
     *
     * @return SyncDataContainer
     */
    public function setSyncSettings(array $syncSettings): SyncDataContainer
    {
        $this->syncSettings = $syncSettings;

        return $this;
    }
}