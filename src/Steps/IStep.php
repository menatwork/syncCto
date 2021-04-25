<?php


namespace MenAtWork\SyncCto\Steps;

use MenAtWork\SyncCto\Clients\IClient;

interface IStep
{
    /**
     * @param ContentData $container
     */
    public function setFrontendContainer(ContentData $container);

    /**
     * @param StepData $container
     */
    public function setStepContainer(StepData $container);

    /**
     * Run some actions before the sync starts.
     */
    public function beforeSyncStart();

    /**
     * Run some actions after sync is finished.
     */
    public function afterSyncFinished();

    /**
     * Run some actions after a abort.
     */
    public function afterSyncAbort();

    /**
     * Run some action if the step start the run.
     */
    public function beforeRun();

    /**
     * Run some action after the run.
     */
    public function afterRun();

    /**
     * Check if the step must run or not.
     */
    public function mustRun();

    /**
     * Setup the current step with title etc.
     */
    public function setupStep();

    /**
     * Run the step.
     *
     * @param IClient $sourceClient
     *
     * @param IClient $destinationClient
     */
    public function run($sourceClient, $destinationClient);
}