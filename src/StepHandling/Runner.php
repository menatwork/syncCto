<?php

namespace MenAtWork\SyncCto\StepHandling;

use \MenAtWork\SyncCto\Clients\IClient;
use MenAtWork\SyncCto\Steps\IStep;
use MenAtWork\SyncCto\Steps\StepFileCheck;
use MenAtWork\SyncCto\Steps\StepStart;

/**
 * Class Runner
 *
 * @package MenAtWork\SyncCto\StepHandling
 */
class Runner
{
    /**
     * @var string[]
     */
    protected array $stepClasses = [];

    /**
     * @var SyncDataContainer
     */
    private SyncDataContainer $syncData;

    /**
     * @var IClient
     */
    private IClient $source;

    /**
     * @var IClient
     */
    private IClient $destination;

    /**
     * Runner constructor.
     */
    public function __construct()
    {
        $this->stepClasses = [
            1 => StepStart::class,
            2 => StepFileCheck::class
        ];
    }

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
     * @return SyncDataContainer
     */
    public function getSyncData(): SyncDataContainer
    {
        return $this->syncData;
    }

    /**
     * @param SyncDataContainer $syncData
     *
     * @return Runner
     */
    public function setSyncData(SyncDataContainer $syncData): Runner
    {
        $this->syncData = $syncData;

        return $this;
    }

    /**
     * @param int $step
     *
     * @return IStep
     */
    private function getStepInstance(int $step): IStep
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
     * Before the syncStart trigger all steps.
     */
    protected function beforeSyncStarts()
    {
        $syncData = $this->getSyncData();
        $mainStep = $syncData->getStep();

        foreach ($this->stepClasses as $class) {
            $stepInstance = $this->getStepInstance($mainStep);
            $stepInstance->setSyncContainer($syncData);
            $stepInstance->beforeSyncStart();
        }
    }

    /**
     * Do the job like a pro.
     *
     * @return void
     */
    public function run(): void
    {
        $syncData = $this->getSyncData();
        $mainStep = $syncData->getStep();

        $stepInstance = $this->getStepInstance($mainStep);
        $stepInstance->setSyncContainer($syncData);

        // If we have a new container, the sub step is 0.
        // So we can check the before state.
        dump($this->getSyncData());die();
        if (0 == $this->getSyncData()->getSubStep()) {
            $stepInstance->setupStep();
        }

        // If the must run say no, go to the next one.
        if (!$stepInstance->mustRun()) {
            $syncData->nextStep();

            return;
        }

        $stepInstance->beforeRun();
        $stepInstance->run($this->getSource(), $this->getDestination());
        $stepInstance->afterRun();
    }
}