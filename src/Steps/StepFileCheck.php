<?php declare(strict_types=1);


namespace MenAtWork\SyncCto\Steps;

use Contao\File;
use MenAtWork\SyncCto\Helper\PathBuilder;
use SyncCtoEnum;

/**
 * Class StepFileCheck
 *
 * @package MenAtWork\SyncCto\Steps
 */
class StepFileCheck extends StepDefault
{
    /**
     * List of allowed functions.
     *
     * @var array|string[]
     */
    private array $allowedFunctions = [
        'core_change',
        'core_delete',
        'user_change',
        'user_delete',
    ];

    /**
     * Helper for path building and so on.
     *
     * @var PathBuilder
     */
    private PathBuilder $pathBuilder;

    /**
     * List of the file elements.
     *
     * @var array
     */
    private array       $listFile = [
        'files' => [],
        'core'  => []
    ];

    /**
     * List of the compare elements.
     *
     * @var array
     */
    private array       $listCompare = [
        'files' => [],
        'core'  => []
    ];

    /**
     * StepFileCheck constructor.
     *
     * Init some parts we still need like some contao parts.
     * Note: Maybe we can add the steps as service to build the steps with all services needed.
     * Note: Since this step is totally made for Contao we can use some Contao settings.
     */
    public function __construct()
    {
        $this->pathBuilder = new PathBuilder();
    }

    /**
     * Check if the function is set in the setting.
     *
     * @param string $functionName The name of the function.
     *
     * @return bool True => Yes | False => Nope.
     */
    private function isFunctionSet(string $functionName): bool
    {
        return in_array($functionName, $this->syncSettings['syncCto_Type'], false);
    }

    /**
     * Build the path to the file, where all the data are saved.
     *
     * @param bool $generateFullPath
     *
     * @return string The path with or without the TL_ROOT.
     */
    private function getFileListPath(bool $generateFullPath = false): string
    {
        $path = $this->pathBuilder->standardizePath(
            $GLOBALS['SYC_PATH']['tmp'],
            "syncfilelist-ID-" . $this->syncSettings['run_id'] . ".txt"
        );

        if (false == $generateFullPath) {
            return $path;
        }

        return TL_ROOT . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * Build the path to the file, where all the data are saved.
     *
     * @param bool $generateFullPath
     *
     * @return string The path with or without the TL_ROOT.
     */
    private function getCompareListPath(bool $generateFullPath = false): string
    {
        $path = $this->pathBuilder->standardizePath(
            $GLOBALS['SYC_PATH']['tmp'],
            "synccomparelist-ID-" . $this->syncSettings['run_id'] . ".txt"
        );

        if (false == $generateFullPath) {
            return $path;
        }

        return TL_ROOT . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * @inheritDoc
     */
    public function mustRun(): bool
    {
        if (
            !array_key_exists('syncCto_Type', $this->syncSettings)
            || !is_array($this->syncSettings['syncCto_Type'])
            || count($this->syncSettings['syncCto_Type']) == 0
        ) {
            return false;
        }

        foreach ($this->allowedFunctions as $value) {
            if (in_array($value, $this->syncSettings['syncCto_Type'], false)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     *
     * @throws \Exception
     */
    public function beforeRun(): void
    {
        // Get file list.
        $content = '';
        if (file_exists($this->getFileListPath(true))) {
            $fileList = new File($this->getFileListPath());
            $content  = $fileList->getContent();
            $fileList->close();
        }

        if ($content === '') {
            $this->listFile = [
                'files' => [],
                'core'  => []
            ];
        } else {
            $this->listFile = (array)\StringUtil::deserialize($content, true);
        }

        // Get compare list-
        $content = '';
        if (file_exists($this->getCompareListPath(true))) {
            $compareList = new File($this->getCompareListPath());
            $content     = $compareList->getContent();
            $compareList->close();
        }

        if ($content === '') {
            $this->listCompare = [
                'files' => [],
                'core'  => []
            ];
        } else {
            $this->listCompare = (array)\StringUtil::deserialize($content, true);
        }

        // Cleanup to save memory.
        unset($content, $compareList, $fileList);
    }

    /**
     * @inheritDoc
     */
    public function afterRun(): void
    {
        $objFileList = new File($this->getFileListPath());
        $objFileList->write(serialize($this->listFile));
        $objFileList->close();

        $objCompareList = new File($this->getCompareListPath());
        $objCompareList->write(serialize($this->listCompare));
        $objCompareList->close();
    }

    /**
     * @inheritDoc
     */
    public function setupStep(): void
    {
        $this->stepContainer->setStep(1);
        $this->stepContainer->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
        $this->stepContainer->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1']);
        $this->stepContainer->setState(SyncCtoEnum::WORK_WORK);

        $this->syncDataContainer->setError(false);
        $this->syncDataContainer->setErrorMessage('');
    }

    /**
     * @inheritDoc
     */
    public function run($sourceClient, $destinationClient): void
    {
        try {
            switch ($this->stepContainer->getStep()) {
                /** @noinspection PhpMissingBreakStatementInspection */
                case 1:
                    if ($this->isFunctionSet('core_change')) {
                        $this->listFile['core'] = $sourceClient->getChecksumCore();

                        $this->stepContainer->nextStep();
                        break;
                    }

                /** @noinspection PhpMissingBreakStatementInspection */
                case 2:
                    if ($this->isFunctionSet('user_change')) {
                        $this->listFile['files'] = $sourceClient->getChecksumFiles();

                        $this->stepContainer->nextStep();
                        break;
                    }

                /** @noinspection PhpMissingBreakStatementInspection */
                case 3:
                    if ($this->isFunctionSet('core_change')) {
                        $this->listCompare['core'] = $destinationClient->runCecksumCompare($this->listFile['core']);
                        $this
                            ->stepContainer
                            ->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_2']);

                        $this->stepContainer->nextStep();
                        break;
                    }

                /** @noinspection PhpMissingBreakStatementInspection */
                case 4:
                    if ($this->isFunctionSet('user_change')) {
                        $this->listCompare['files'] = $destinationClient
                            ->runCecksumCompare(
                                $this->listFile['files'],
                                (isset($this->syncSettings['automode']) && $this->syncSettings['automode'] == true)
                            );
                        $this
                            ->stepContainer
                            ->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_2']);

                        $this->stepContainer->nextStep();
                        break;
                    }

                /** @noinspection PhpMissingBreakStatementInspection */
                case 5:
                    if ($this->isFunctionSet('core_delete')) {
                        $arrChecksumClient         = $destinationClient->getChecksumCore();
                        $this->listCompare['core'] = array_merge(
                            (array)$this->listCompare['core'],
                            $this->objSyncCtoFiles->checkDeleteFiles($arrChecksumClient)
                        );

                        $this->stepContainer->nextStep();
                        break;
                    }

                /** @noinspection PhpMissingBreakStatementInspection */
                case 6:
                    if ($this->isFunctionSet('user_delete')) {
                        $arrChecksumClient          = $destinationClient->getChecksumFiles();
                        $this->listCompare['files'] = array_merge(
                            (array)$this->listCompare['files'],
                            $this->objSyncCtoFiles->checkDeleteFiles($arrChecksumClient)
                        );

                        $this->stepContainer->nextStep();
                        break;
                    }

                /** @noinspection PhpMissingBreakStatementInspection */
                case 7:
                    if ($this->isFunctionSet('core_delete')) {
                        $arrChecksumClient         = $destinationClient->getChecksumFolderCore();
                        $this->listCompare['core'] = array_merge(
                            (array)$this->listCompare['core'],
                            $this->objSyncCtoFiles->searchDeleteFolders($arrChecksumClient)
                        );

                        $this->stepContainer->nextStep();
                        break;
                    }

                case 8:
                    if ($this->isFunctionSet('user_delete')) {
                        $arrChecksumClient          = $destinationClient->getChecksumFolderFiles();
                        $this->listCompare['files'] = array_merge(
                            (array)$this->listCompare['files'],
                            $this->objSyncCtoFiles->searchDeleteFolders($arrChecksumClient));
                    }

                    $this
                        ->stepContainer
                        ->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_3']);

                    $this->stepContainer->nextStep();
                    break;

                /**
                 * Set CSS and search for bigfiles
                 */
                case 10:
                    foreach ($this->listCompare as $strType => $arrLists) {
                        foreach ($arrLists as $key => $value) {
                            switch ($value["state"]) {
                                case SyncCtoEnum::FILESTATE_BOMBASTIC_BIG:
                                    $this->listCompare[$strType][$key]["css"]     = "unknown";
                                    $this->listCompare[$strType][$key]["css_big"] = "ignored";
                                    break;

                                case SyncCtoEnum::FILESTATE_TOO_BIG_NEED:
                                    $this->listCompare[$strType][$key]["css_big"] = "ignored";
                                case SyncCtoEnum::FILESTATE_NEED:
                                    $this->listCompare[$strType][$key]["css"] = "modified";
                                    break;

                                case SyncCtoEnum::FILESTATE_TOO_BIG_MISSING:
                                    $this->listCompare[$strType][$key]["css_big"] = "ignored";
                                case SyncCtoEnum::FILESTATE_MISSING:
                                    $this->listCompare[$strType][$key]["css"] = "new";
                                    break;

                                case SyncCtoEnum::FILESTATE_DELETE:
                                    $this->listCompare[$strType][$key]["css"] = "deleted";
                                    break;

                                case SyncCtoEnum::FILESTATE_DBAFS_CONFLICT:
                                    $this->listCompare[$strType][$key]["css"] = "conflict";
                                    break;

                                default:
                                    $this->listCompare[$strType][$key]["css"] = "unknown";
                                    break;
                            }

                            if ($value["state"] != SyncCtoEnum::FILESTATE_DBAFS_CONFLICT && (isset($value["dbafs_state"]) || isset($value["dbafs_tail_state"]))) {
                                $this->listCompare[$strType][$key]["css"] .= " conflict";
                            }

                            if ($value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_DELETE
                                || $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_MISSING
                                || $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_NEED
                                || $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_SAME
                                || $value["state"] == SyncCtoEnum::FILESTATE_BOMBASTIC_BIG
                                || $value["state"] == SyncCtoEnum::FILESTATE_DELETE
                            ) {
                                continue;
                            } else {
                                if ($value["size"] > $this->arrClientInformation["upload_sizeLimit"]) {
                                    $this->listCompare[$strType][$key]["split"] = true;
                                }
                            }
                        }
                    }

                    $this->objStepPool->step++;
                    break;

                /**
                 * Show files form
                 */
                case 11:
                    // Counter
                    $intCountMissing       = 0;
                    $intCountNeed          = 0;
                    $intCountIgnored       = 0;
                    $intCountDelete        = 0;
                    $intCountDbafsConflict = 0;

                    $intTotalSizeNew    = 0;
                    $intTotalSizeDel    = 0;
                    $intTotalSizeChange = 0;

                    // Count files
                    foreach ($this->listCompare as $strType => $arrLists) {
                        foreach ($arrLists as $key => $value) {
                            switch ($value['state']) {
                                case SyncCtoEnum::FILESTATE_MISSING:
                                    $intCountMissing++;
                                    $intTotalSizeNew += $value["size"];
                                    break;

                                case SyncCtoEnum::FILESTATE_NEED:
                                    $intCountNeed++;
                                    $intTotalSizeChange += $value["size"];
                                    break;

                                case SyncCtoEnum::FILESTATE_DELETE:
                                case SyncCtoEnum::FILESTATE_FOLDER_DELETE:
                                    $intCountDelete++;
                                    $intTotalSizeDel += $value["size"];
                                    break;

                                case SyncCtoEnum::FILESTATE_BOMBASTIC_BIG:
                                case SyncCtoEnum::FILESTATE_TOO_BIG_NEED:
                                case SyncCtoEnum::FILESTATE_TOO_BIG_MISSING:
                                case SyncCtoEnum::FILESTATE_TOO_BIG_DELETE :
                                    $intCountIgnored++;
                                    break;
                            }

                            if ($value["state"] == SyncCtoEnum::FILESTATE_DBAFS_CONFLICT
                                || isset($value["dbafs_state"])
                                || isset($value["dbafs_tail_state"])
                            ) {
                                $intCountDbafsConflict++;
                            }
                        }
                    }

                    $this->objStepPool->missing  = $intCountMissing;
                    $this->objStepPool->need     = $intCountNeed;
                    $this->objStepPool->ignored  = $intCountIgnored;
                    $this->objStepPool->delete   = $intCountDelete;
                    $this->objStepPool->conflict = $intCountDbafsConflict;

                    // Save files and go on or skip here
                    if ($intCountMissing == 0 && $intCountNeed == 0 && $intCountIgnored == 0 && $intCountDelete == 0 && $intCountDbafsConflict == 0) {
                        // Set current step information
                        $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1']);
                        $this->objData->setHtml("");
                        $this->booRefresh = true;
                        $this->intStep++;

                        break;
                    } else {
                        if (count((array)$this->listCompare) == 0 || array_key_exists("skip", $_POST)) {
                            $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
                            $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1']);
                            $this->objData->setHtml("");
                            $this->booRefresh = true;
                            $this->intStep++;

                            $this->listCompare = [];

                            break;
                        } else {
                            if (($this->arrSyncSettings["automode"] || array_key_exists("forward",
                                        $_POST)) && count((array)$this->listCompare) != 0) {
                                $this->objData->setState(SyncCtoEnum::WORK_OK);
                                $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_4'],
                                    [
                                        $intCountMissing,
                                        $intCountNeed,
                                        $intCountDelete,
                                        $intCountIgnored,
                                        $this->getReadableSize($intTotalSizeNew),
                                        $this->getReadableSize($intTotalSizeChange),
                                        $this->getReadableSize($intTotalSizeDel)
                                    ]));
                                $this->objData->setHtml("");
                                $this->booRefresh = true;
                                $this->intStep++;

                                break;
                            }
                        }
                    }

                    $objTemp                 = new BackendTemplate("be_syncCto_form");
                    $objTemp->id             = $this->intClientID;
                    $objTemp->step           = $this->intStep;
                    $objTemp->direction      = "To";
                    $objTemp->headline       = $GLOBALS['TL_LANG']['MSC']['totalsize'];
                    $objTemp->cssId          = 'syncCto_filelist_form';
                    $objTemp->forwardValue   = $GLOBALS['TL_LANG']['MSC']['apply'];
                    $objTemp->popupClassName = 'popup/files';

                    // Build content
                    $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_4'],
                        [
                            $intCountMissing,
                            $intCountNeed,
                            $intCountDelete,
                            $intCountIgnored,
                            $this->getReadableSize($intTotalSizeNew),
                            $this->getReadableSize($intTotalSizeChange),
                            $this->getReadableSize($intTotalSizeDel)
                        ]));
                    $this->objData->setHtml($objTemp->parse());
                    $this->booRefresh = false;

                    break;
            }
        } catch (\Exception $exc) {
//            $this->log(vsprintf("Error on synchronization client ID %s with msg: %s",
//                [\Input::get("id"), $exc->getMessage()]), __CLASS__ . " " . __FUNCTION__, "ERROR");

            $this->syncDataContainer->setError(true);
            $this->syncDataContainer->setErrorMessage($exc->getMessage());

            $this->stepContainer->setState(SyncCtoEnum::WORK_ERROR);
        }
    }
}