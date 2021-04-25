<?php

namespace MenAtWork\SyncCto\Steps;

use \MenAtWork\SyncCto\Clients\IClient;
use \MenAtWork\SyncCto\Steps\ContentData;

class Runner
{
    /**
     * @var IClient
     */
    private $source;

    /**
     * @var IClient
     */
    private $destination;

    /**
     * @var ContentData
     */
    private $contentData;

    /**
     * @return IClient
     */
    public function getSource(): IClient
    {
        return $this->source;
    }

    /**
     * @param IClient $source
     */
    public function setSource(IClient $source): void
    {
        $this->source = $source;
    }

    /**
     * @return IClient
     */
    public function getDestination(): IClient
    {
        return $this->destination;
    }

    /**
     * @param IClient $destination
     */
    public function setDestination(IClient $destination): void
    {
        $this->destination = $destination;
    }

    /**
     * @return ContentData
     */
    public function getContentData(): \MenAtWork\SyncCto\Steps\ContentData
    {
        return $this->contentData;
    }

    /**
     * @param ContentData $contentData
     */
    public function setContentData(\MenAtWork\SyncCto\Steps\ContentData $contentData): void
    {
        $this->contentData = $contentData;
    }

    /**
     * @var string[]
     */
    protected $stepClasses = [
        1 => StepStart::class
    ];

    /**
     * @param int $step
     *
     * @return \MenAtWork\SyncCto\Steps\IStep
     */
    private function getStepInstance($step)
    {
        if (!isset($this->stepClasses[$step])) {
            throw new \RuntimeException('Could not find the Step ');
        }

        /** @var IStep $stepInstance */
        $stepClass    = $this->stepClasses[$step];
        $stepInstance = new $stepClass();
        if (!($stepInstance instanceof IStep)) {
            throw new \RuntimeException(sprintf('Given step %s is not of typ IStep.', $stepClass));
        }

        return $stepInstance;
    }

    /**
     * Do the job like a pro.
     */
    public function run()
    {
        $frontendData = $this->getContentData();
        $mainStep     = $frontendData->getStep();
        $stepData     = $frontendData->getStepContent($mainStep);

        $stepInstance = $this->getStepInstance($mainStep);
        $stepInstance->setFrontendContainer($frontendData);
        $stepInstance->setStepContainer($stepData);

        // If we have a new container, the step is 0.
        // So we can check the before state.
        if($stepData->getStep() == 0){
            $stepInstance->setupStep();
            $stepInstance->beforeSyncStart();
        }

        // If the must run say no, go to the next one.
        if (!$stepInstance->mustRun()) {
            $frontendData->setStep($mainStep++);

            return;
        }

        $stepInstance->beforeRun();
        $stepInstance->run($this->getSource(), $this->getDestination());
        $stepInstance->afterRun();
    }
}