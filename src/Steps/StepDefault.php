<?php


namespace MenAtWork\SyncCto\Steps;

use MenAtWork\SyncCto\Clients\IClient;

abstract class StepDefault implements IStep
{
    /**
     * @var ContentData
     */
    protected $frontendContainer;

    /**
     * @var StepData
     */
    protected $stepContainer;

    /**
     * @inheritDoc
     */
    public function setFrontendContainer(ContentData $container)
    {
        $this->frontendContainer = $container;
    }

    /**
     * @inheritDoc
     */
    public function setStepContainer(StepData $container)
    {
        $this->stepContainer = $container;
    }

    /**
     * @inheritDoc
     */
    public function beforeSyncStart()
    {
        return;
    }

    /**
     * @inheritDoc
     */
    public function afterSyncFinished()
    {
        return;
    }

    /**
     * @inheritDoc
     */
    public function afterSyncAbort()
    {
        return;
    }

    /**
     * @inheritDoc
     */
    public function beforeRun()
    {
        return;
    }

    /**
     * @inheritDoc
     */
    public function afterRun()
    {
        return;
    }

    /**
     * @inheritDoc
     */
    public function mustRun()
    {
        return;
    }

    /**
     * @inheritDoc
     */
    abstract public function setupStep();

    /**
     * @inheritDoc
     */
    abstract public function run($sourceClient, $destinationClient);
}