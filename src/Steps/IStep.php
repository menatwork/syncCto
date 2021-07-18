<?php declare(strict_types=1);

namespace MenAtWork\SyncCto\Steps;

use MenAtWork\SyncCto\Clients\IClient;
use MenAtWork\SyncCto\StepHandling\SyncDataContainer;

/**
 * Interface IStep
 *
 * @package MenAtWork\SyncCto\Steps
 */
interface IStep
{
    /**
     * Set up the frontend container.
     *
     * @param SyncDataContainer $container
     *
     * @return void
     */
    public function setSyncContainer(SyncDataContainer $container): void;

    /**
     * Set's the settings for this sync.
     *
     * @param array $syncSettings The settings.
     *
     * @return void
     */
    public function setSyncSettings(array $syncSettings): void;

    /**
     * Run some actions before the sync starts.
     *
     * @return void
     */
    public function beforeSyncStart(): void;

    /**
     * Run some actions after sync is finished.
     *
     * @return void
     */
    public function afterSyncFinished(): void;

    /**
     * Run some actions after a abort.
     *
     * @return void
     */
    public function afterSyncAbort(): void;

    /**
     * Run some action if the step start the run.
     *
     * @return void
     */
    public function beforeRun(): void;

    /**
     * Run some action after the run.
     *
     * @return void
     */
    public function afterRun(): void;

    /**
     * Check if the step must run or not.
     *
     * @return bool Flag if the step have to be run or not.
     */
    public function mustRun(): bool;

    /**
     * Setup the current step with title etc.
     *
     * @return void
     */
    public function setupStep(): void;

    /**
     * Run the step.
     *
     * @param IClient $sourceClient      The source client to get the information.
     *
     * @param IClient $destinationClient The destination to send the data.
     *
     * @return void
     */
    public function run(IClient $sourceClient, IClient $destinationClient): void;
}