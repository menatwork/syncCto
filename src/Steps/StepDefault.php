<?php declare(strict_types=1);


namespace MenAtWork\SyncCto\Steps;

use MenAtWork\SyncCto\StepHandling\SyncDataContainer;
use SyncCtoEnum;

abstract class StepDefault implements IStep
{
    /**
     * @var SyncDataContainer
     */
    protected SyncDataContainer $syncDataContainer;

    /**
     * @var array
     */
    protected array $syncSettings;

    /**
     * @inheritDoc
     */
    public function setSyncContainer(SyncDataContainer $container): void
    {
        $this->syncDataContainer = $container;
    }

    /**
     * @inheritDoc
     */
    public function setSyncSettings(array $syncSettings): void
    {
        $this->syncSettings = $syncSettings;
    }

    /**
     * This little helper will reset all the states back to normal.
     *
     * @return void
     */
    protected function resetRunState(): void
    {
        $this->syncDataContainer->setStateRefresh(true);
        $this->syncDataContainer->setStateFinished(false);
        $this->syncDataContainer->setStateError(false);
        $this->syncDataContainer->setErrorMessage('');
    }

    /**
     * Something bad happen so stop the run here.
     *
     * @param string $errorMessage The error message.
     *
     * @return void
     */
    protected function setErrorState(string $errorMessage): void
    {
        $this->syncDataContainer->setStateRefresh(false);
        $this->syncDataContainer->setStateFinished(false);
        $this->syncDataContainer->setStateError(true);
        $this->syncDataContainer->setErrorMessage($errorMessage);

        $this->syncDataContainer->setStepState(SyncCtoEnum::WORK_ERROR);
    }

    /**
     * @inheritDoc
     */
    public function beforeSyncStart(): void
    {
        return;
    }

    /**
     * @inheritDoc
     */
    public function afterSyncFinished(): void
    {
        return;
    }

    /**
     * @inheritDoc
     */
    public function afterSyncAbort(): void
    {
        return;
    }

    /**
     * @inheritDoc
     */
    public function beforeRun(): void
    {
        return;
    }

    /**
     * @inheritDoc
     */
    public function afterRun(): void
    {
        return;
    }

    /**
     * @inheritDoc
     */
    public function mustRun(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    abstract public function setupStep(): void;

    /**
     * @inheritDoc
     */
    abstract public function run($sourceClient, $destinationClient): void;
}