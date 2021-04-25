<?php

namespace MenAtWork\SyncCto\Steps;

class ContentData
{
    /**
     * @var bool
     */
    private $error;

    /**
     * @var string
     */
    private $errorMessage;

    /**
     * @var bool
     */
    private $abort;

    /**
     * @var object
     */
    private $data;

    /**
     * @var string
     */
    private $information;

    /**
     * @var string
     */
    private $headline;

    /**
     * @var float
     */
    private $start;

    /**
     * @var string
     */
    private $goBack;

    /**
     * @var string
     */
    private $url;

    /**
     * @var int
     */
    private $step;

    /**
     * @var bool
     */
    private $finished;

    /**
     * @var bool
     */
    private $refresh;

    /**
     * @var StepData[]
     */
    private $stepContent;

    /**
     * @return bool
     */
    public function isError(): bool
    {
        return $this->error;
    }

    /**
     * @param bool $error
     *
     * @return ContentData
     */
    public function setError(bool $error): ContentData
    {
        $this->error = $error;

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
     * @return ContentData
     */
    public function setErrorMessage(string $errorMessage): ContentData
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAbort(): bool
    {
        return $this->abort;
    }

    /**
     * @param bool $abort
     *
     * @return ContentData
     */
    public function setAbort(bool $abort): ContentData
    {
        $this->abort = $abort;

        return $this;
    }

    /**
     * @return object
     */
    public function getData(): object
    {
        return $this->data;
    }

    /**
     * @param object $data
     *
     * @return ContentData
     */
    public function setData(object $data): ContentData
    {
        $this->data = $data;

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
     * @return ContentData
     */
    public function setInformation(string $information): ContentData
    {
        $this->information = $information;

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
     * @return ContentData
     */
    public function setHeadline(string $headline): ContentData
    {
        $this->headline = $headline;

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
     * @return ContentData
     */
    public function setStart(float $start): ContentData
    {
        $this->start = $start;

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
     * @return ContentData
     */
    public function setGoBack(string $goBack): ContentData
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
     * @return ContentData
     */
    public function setUrl(string $url): ContentData
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return int
     */
    public function getStep(): int
    {
        return $this->step;
    }

    /**
     * @param int $step
     *
     * @return ContentData
     */
    public function setStep(int $step): ContentData
    {
        $this->step = $step;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFinished(): bool
    {
        return $this->finished;
    }

    /**
     * @param bool $finished
     *
     * @return ContentData
     */
    public function setFinished(bool $finished): ContentData
    {
        $this->finished = $finished;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRefresh(): bool
    {
        return $this->refresh;
    }

    /**
     * @param bool $refresh
     *
     * @return ContentData
     */
    public function setRefresh(bool $refresh): ContentData
    {
        $this->refresh = $refresh;

        return $this;
    }

    /**
     * @param int $step The id of the step.
     *
     * @return StepData
     */
    public function getStepContent($step): StepData
    {
        if (!isset($this->stepContent[$step])) {
            $stepContent              = new StepData([], 0);
            $this->stepContent[$step] = $stepContent;
        }

        return $this->stepContent[$step];
    }

    /**
     * @return array
     */
    public function getAllStepContent()
    {
        return $this->stepContent;
    }

    /**
     * @param StepData $stepContent
     */
    public function setStepContent(StepData $stepContent): void
    {
//        $this->stepContent = $stepContent;
    }


}