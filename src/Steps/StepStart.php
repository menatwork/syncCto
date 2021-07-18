<?php declare(strict_types=1);

namespace MenAtWork\SyncCto\Steps;

use MenAtWork\SyncCto\Clients\IClient;
use SyncCtoEnum;

/**
 * Class StepStart
 *
 * @package MenAtWork\SyncCto\Steps
 */
class StepStart extends StepDefault
{
    /**
     * @inheritDoc
     */
    public function setupStep(): void
    {
        $this->syncDataContainer->setSubStep(1);
        $this->syncDataContainer->setStepTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
        $this->syncDataContainer->setStepDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_1']);
        $this->syncDataContainer->setStepState(SyncCtoEnum::WORK_WORK);

        $this->resetRunState();
    }

    /**
     * @inheritDoc
     */
    public function beforeRun(): void
    {
        $this->resetRunState();
    }

    /**
     * @inheritDoc
     */
    public function run($sourceClient, $destinationClient): void
    {
        try {
            switch ($this->syncDataContainer->getSubStep()) {
                case 1:
                    $this->step1($sourceClient, $destinationClient);
                    break;

                case 2:
                    $this->step2($sourceClient, $destinationClient);
                    break;

                case 3:
                    $this->step3($sourceClient, $destinationClient);
                    break;

                case 4:
                    $this->step4($sourceClient, $destinationClient);
                    break;
            }
        } catch (\Exception $exc) {
//            $this->log(vsprintf("Error on synchronization client ID %s with msg: %s",
//                [\Input::get("id"), $exc->getMessage()]), __CLASS__ . " " . __FUNCTION__, "ERROR");

            $this->setErrorState($exc->getMessage());
        }
    }

    /**
     * Start connection.
     *
     * @param IClient $sourceClient      The source for the actions.
     *
     * @param IClient $destinationClient The destination for the actions.
     *
     * @return void
     */
    private function step1(IClient $sourceClient, IClient $destinationClient): void
    {
        $sourceClient->startConnection();
        $destinationClient->startConnection();

        $this->syncDataContainer->nextSubStep();
    }

    /**
     * Referer check deactivate
     *
     * @param IClient $sourceClient      The source for the actions.
     *
     * @param IClient $destinationClient The destination for the actions.
     *
     * @return void
     */
    private function step2(IClient $sourceClient, IClient $destinationClient): void
    {
        $success = $sourceClient->referrerDisable() && $destinationClient->referrerDisable();

        if (!$success) {
            $this->syncDataContainer->setStepState(SyncCtoEnum::WORK_ERROR);
            $this->syncDataContainer->setStateError(true);
            $this->syncDataContainer->setErrorMessage($GLOBALS['TL_LANG']['ERR']['referer']);

            return;
        }

        $this->syncDataContainer->nextSubStep();
    }

    /**
     * Check version
     *
     * @param IClient $sourceClient      The source for the actions.
     *
     * @param IClient $destinationClient The destination for the actions.
     *
     * @return void
     */
    private function step3(IClient $sourceClient, IClient $destinationClient): void
    {
        $sourceVersion      = $sourceClient->getVersionContao();
        $destinationVersion = $destinationClient->getVersionContao();

        $sourceVersion      = explode('.', $sourceVersion);
        $destinationVersion = explode('.', $destinationVersion);

        if ($sourceVersion[0] != $destinationVersion[0]) {
            $this->syncDataContainer->setStepState(SyncCtoEnum::WORK_ERROR);
            $this->syncDataContainer->setStateError(true);
            $this->syncDataContainer->setErrorMessage(vsprintf(
                $GLOBALS['TL_LANG']['ERR']['version'],
                [
                    "Contao",
                    $sourceVersion,
                    $destinationVersion
                ]
            ));

            return;
        }

        $this->syncDataContainer->setStepDescription(
            $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_2']
        );

        $this->syncDataContainer->nextSubStep();
    }

    /**
     * Clear client and server temp folder
     *
     * @param IClient $sourceClient      The source for the actions.
     *
     * @param IClient $destinationClient The destination for the actions.
     *
     * @return void
     */
    private function step4(IClient $sourceClient, IClient $destinationClient): void
    {
        $sourceClient->purgeTempFolder();
        $destinationClient->purgeTempFolder();

        // Current step is okay.
        $this->syncDataContainer->setStepDescription(
            $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_1']
        );

        $this->syncDataContainer->nextSubStep();
    }

    /**
     * Load parameter from client.
     *
     * @param IClient $sourceClient      The source for the actions.
     *
     * @param IClient $destinationClient The destination for the actions.
     *
     * @return void
     */
    private function step5(IClient $sourceClient, IClient $destinationClient): void
    {
        // ToDo: Read all data set for later testing and var handling.

        $this->syncDataContainer->nextSubStep();
    }

    /**
     * Final.
     *
     * @param IClient $sourceClient      The source for the actions.
     *
     * @param IClient $destinationClient The destination for the actions.
     *
     * @return void
     */
    private function step6(IClient $sourceClient, IClient $destinationClient): void
    {
        $this->syncDataContainer->setStepDescription(
            $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_1']
        );

        $this->syncDataContainer->setStepState(SyncCtoEnum::WORK_OK);
        $this->syncDataContainer->nextStep();
    }
}