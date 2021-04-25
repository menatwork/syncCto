<?php

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
    public function setupStep()
    {
        $this->stepContainer->setStep(1);
        $this->stepContainer->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
        $this->stepContainer->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_1']);
        $this->stepContainer->setState(SyncCtoEnum::WORK_WORK);

        $this->frontendContainer->setError(false);
        $this->frontendContainer->setErrorMessage('');
    }

    /**
     * @inheritDoc
     */
    public function run($sourceClient, $destinationClient)
    {
        try {
            switch ($this->stepContainer->getStep()) {
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

            $this->frontendContainer->setError(true);
            $this->frontendContainer->setErrorMessage($exc->getMessage());

            $this->stepContainer->setState(SyncCtoEnum::WORK_ERROR);
        }
    }

    /**
     * Start connection.
     *
     * @param IClient $sourceClient
     * @param IClient $destinationClient
     */
    private function step1($sourceClient, $destinationClient)
    {
        $sourceClient->startConnection();
        $destinationClient->startConnection();

        $this->stepContainer->nextStep();
    }

    /**
     * Referer check deactivate
     *
     * @param IClient $sourceClient
     * @param IClient $destinationClient
     */
    private function step2($sourceClient, $destinationClient)
    {
        $success = $sourceClient->referrerDisable() && $destinationClient->referrerDisable();

        if (!$success) {
            $this->stepContainer->setState(SyncCtoEnum::WORK_ERROR);
            $this->frontendContainer->setError(true);
            $this->frontendContainer->setErrorMessage($GLOBALS['TL_LANG']['ERR']['referer']);

            return;
        }

        $this->stepContainer->nextStep();
    }

    /**
     * Check version
     *
     * @param IClient $sourceClient
     * @param IClient $destinationClient
     */
    private function step3($sourceClient, $destinationClient)
    {
        $sourceVersion      = $sourceClient->getVersionContao();
        $destinationVersion = $destinationClient->getVersionContao();

        $sourceVersion      = explode('.', $sourceVersion);
        $destinationVersion = explode('.', $destinationVersion);

        if ($sourceVersion[0] != $destinationVersion[0]) {
            $this->stepContainer->setState(SyncCtoEnum::WORK_ERROR);
            $this->frontendContainer->setError(true);
            $this->frontendContainer->setErrorMessage(vsprintf(
                $GLOBALS['TL_LANG']['ERR']['version'],
                [
                    "Contao",
                    $sourceVersion,
                    $destinationVersion
                ]
            ));

            return;
        }

        $this->stepContainer->setDescription(
            $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_2']
        );

        $this->stepContainer->nextStep();
    }

    /**
     * Clear client and server temp folder
     *
     * @param IClient $sourceClient
     * @param IClient $destinationClient
     */
    private function step4($sourceClient, $destinationClient)
    {
        $sourceClient->purgeTempFolder();
        $destinationClient->purgeTempFolder();

        // Current step is okay.
        $this->stepContainer->setDescription(
            $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_1']
        );

        $this->stepContainer->nextStep();
    }

    /**
     * Load parameter from client.
     *
     * @param IClient $sourceClient
     * @param IClient $destinationClient
     */
    private function step5($sourceClient, $destinationClient)
    {
        // ToDo: Read all data set for later testing and var handling.

        $this->stepContainer->nextStep();
    }

    /**
     * Final.
     *
     * @param $sourceClient
     * @param $destinationClient
     */
    private function step6($sourceClient, $destinationClient)
    {
        $this->stepContainer->setDescription(
            $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_1']
        );

        $this->stepContainer->setState(SyncCtoEnum::WORK_OK);
        $this->stepContainer->nextStep();
    }
}