<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2013
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

namespace MenAtWork\SyncCto\Controller;

use Contao\BackendTemplate;
use Contao\File;
use Contao\Input;
use SyncCtoEnum;
use SyncCtoHelper;

/**
 * Class SyncCtoPopupFiles
 */
class FilePopupController extends APopUpController
{
    // Vars
    protected string|int $intClientID;
    // Helper Classes
    protected SyncCtoHelper $objSyncCtoHelper;
    // Temp data
    protected array $arrListFile;
    protected array $arrListCompare;
    protected array $arrClientInformation;

    // defines
    const STEP_SHOW_FILES  = 'Sf';
    const STEP_CLOSE_FILES = 'cl';
    const STEP_ERROR_FILES = 'er';

    /**
     * Init get parameter
     */
    protected function initGetParams(): void
    {
        // Get Client id
        if (strlen(Input::get("id")) != 0) {
            $this->intClientID = intval(Input::get("id"));
        } else {
            $this->mixStep = self::STEP_ERROR_FILES;

            return;
        }

        // Load information
        $this->loadClientInformation();

        // Get next step
        if (strlen(Input::get("step")) != 0) {
            $this->mixStep = Input::get("step");
        } else {
            $this->mixStep = self::STEP_SHOW_FILES;
        }
    }

    /**
     * Load the template list and go through the steps
     */
    public function doAction(): string
    {
        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();
        $this->initGetParams();

        if ($this->mixStep == self::STEP_SHOW_FILES) {
            $this->loadTempLists();
            $this->showFiles();
            $this->saveTempLists();
            unset($_POST);
        }

        if ($this->mixStep == self::STEP_CLOSE_FILES) {
            $this->showClose();
        }

        if ($this->mixStep == self::STEP_ERROR_FILES) {
            $this->showError();
        }

        $this->Template->id = $this->intClientID;
        $this->Template->step = $this->mixStep;

        return $this->Template->getResponse()->getContent();
    }

    protected function showFiles()
    {
        // Delete functinality
        if (array_key_exists("delete", $_POST)) {
            foreach ($_POST as $key => $value) {
                if (isset($this->arrListCompare['core'][$value])) {
                    unset($this->arrListCompare['core'][$value]);
                }
                if (isset($this->arrListCompare['files'][$value])) {
                    unset($this->arrListCompare['files'][$value]);
                }
            }
        } // Close functinality
        else {
            if (array_key_exists("transfer", $_POST)) {
                $this->mixStep = self::STEP_CLOSE_FILES;

                return;
            }
        }

        // Check if filelist is empty and close
        if (count($this->arrListCompare['core']) == 0 && count($this->arrListCompare['files']) == 0) {
            $this->mixStep = self::STEP_CLOSE_FILES;

            return;
        }

        // Counter
        $intCountMissing = 0;
        $intCountNeed = 0;
        $intCountIgnored = 0;
        $intCountDelete = 0;
        $intCountDbafsConflict = 0;

        $intTotalSizeNew = 0;
        $intTotalSizeDel = 0;
        $intTotalSizeChange = 0;

        // Lists
        $arrNormalFiles = [];
        $arrBigFiles = [];

        // Build list
        foreach ($this->arrListCompare as $strType => $arrLists) {
            foreach ($arrLists as $key => $value) {
                switch ($value['state']) {
                    case SyncCtoEnum::FILESTATE_TOO_BIG_MISSING:
                    case SyncCtoEnum::FILESTATE_MISSING:
                        $intCountMissing++;
                        $intTotalSizeNew += $value["size"];
                        break;

                    case SyncCtoEnum::FILESTATE_TOO_BIG_NEED:
                    case SyncCtoEnum::FILESTATE_NEED:
                        $intCountNeed++;
                        $intTotalSizeChange += $value["size"];
                        break;

                    case SyncCtoEnum::FILESTATE_TOO_BIG_DELETE :
                    case SyncCtoEnum::FILESTATE_DELETE:
                    case SyncCtoEnum::FILESTATE_FOLDER_DELETE:
                        $intCountDelete++;
                        $intTotalSizeDel += $value["size"];
                        break;

                    case SyncCtoEnum::FILESTATE_BOMBASTIC_BIG:
                        $intCountIgnored++;
                        break;
                }

                // Check for dbafs conflict.
                if ($value["state"] == SyncCtoEnum::FILESTATE_DBAFS_CONFLICT || isset($value["dbafs_state"]) || isset($value["dbafs_tail_state"])) {
                    $intCountDbafsConflict++;
                    $value['dbafs_conflict'] = true;
                }

                if (in_array(
                    $value["state"],
                    [
                        SyncCtoEnum::FILESTATE_TOO_BIG_DELETE,
                        SyncCtoEnum::FILESTATE_TOO_BIG_MISSING,
                        SyncCtoEnum::FILESTATE_TOO_BIG_NEED,
                        SyncCtoEnum::FILESTATE_TOO_BIG_SAME,
                        SyncCtoEnum::FILESTATE_BOMBASTIC_BIG,
                    ]
                )
                ) {
                    $arrBigFiles[$key] = $value;
                } else {
                    if ($value["split"] == 1) {
                        $arrBigFiles[$key] = $value;
                    } elseif ($value["size"] > $this->arrClientInformation["upload_sizeLimit"]) {
                        $arrBigFiles[$key] = $value;
                    } else {
                        $arrNormalFiles[$key] = $value;
                    }
                }
            }
        }

        uasort($arrBigFiles, [$this, 'sort']);
        uasort($arrNormalFiles, [$this, 'sort']);

        // Language array for filestate
        $arrLanguageTags = [];
        $arrLanguageTags[SyncCtoEnum::FILESTATE_MISSING] = $GLOBALS['TL_LANG']['MSC']['create'];
        $arrLanguageTags[SyncCtoEnum::FILESTATE_NEED] = $GLOBALS['TL_LANG']['MSC']['overrideSelected'];
        $arrLanguageTags[SyncCtoEnum::FILESTATE_DELETE] = $GLOBALS['TL_LANG']['MSC']['delete'];
        $arrLanguageTags[SyncCtoEnum::FILESTATE_FOLDER_DELETE] = $GLOBALS['TL_LANG']['MSC']['delete'];
        $arrLanguageTags[SyncCtoEnum::FILESTATE_TOO_BIG_MISSING] = $GLOBALS['TL_LANG']['MSC']['skipped'];
        $arrLanguageTags[SyncCtoEnum::FILESTATE_TOO_BIG_NEED] = $GLOBALS['TL_LANG']['MSC']['skipped'];
        $arrLanguageTags[SyncCtoEnum::FILESTATE_TOO_BIG_DELETE] = $GLOBALS['TL_LANG']['MSC']['skipped'];
        $arrLanguageTags[SyncCtoEnum::FILESTATE_BOMBASTIC_BIG] = $GLOBALS['TL_LANG']['MSC']['ignored'];
        $arrLanguageTags[SyncCtoEnum::FILESTATE_DBAFS_CONFLICT] = $GLOBALS['TL_LANG']['MSC']['dbafs_conflict'];

        // Set template
        $this->Template = new BackendTemplate('be_syncCto_files');
        $this->Template->maxLength = 55;
        $this->Template->arrLangStates = $arrLanguageTags;
        $this->Template->normalFilelist = $arrNormalFiles;
        $this->Template->bigFilelist = $arrBigFiles;
        $this->Template->totalsizeNew = $intTotalSizeNew;
        $this->Template->totalsizeDel = $intTotalSizeDel;
        $this->Template->totalsizeChange = $intTotalSizeChange;
        $this->Template->compare_complex = false;
        $this->Template->close = false;
        $this->Template->error = false;
    }

    /**
     * Close popup and go throug next syncCto step
     */
    public function showClose()
    {
        $this->Template = new BackendTemplate("be_syncCto_files");
        $this->Template->headline = $GLOBALS['TL_LANG']['MSC']['backBT'];
        $this->Template->close = true;
        $this->Template->error = false;
    }

    /**
     * Show errors
     */
    public function showError()
    {
        $this->Template = new BackendTemplate("be_syncCto_files");
        $this->Template->headline = $GLOBALS['TL_LANG']['MSC']['error'];
        $this->Template->text = $GLOBALS['TL_LANG']['ERR']['general'];
        $this->Template->close = false;
        $this->Template->error = true;
    }

    // Helper functions --------------------------------------------------------

    /**
     * Load temporary filelist
     *
     * @throws \Exception
     */
    protected function loadTempLists()
    {
        $objFileList = new File(
            $this->objSyncCtoHelper->standardizePath(
                $GLOBALS['SYC_PATH']['tmp'],
                "syncfilelist-ID-" . $this->intClientID . ".txt"
            )
        );
        $strContent = $objFileList->getContent();
        if (strlen($strContent) == 0) {
            $this->arrListFile = [];
        } else {
            $this->arrListFile = \Contao\StringUtil::deserialize($strContent);
        }

        $objCompareList = new File(
            $this->objSyncCtoHelper->standardizePath(
                $GLOBALS['SYC_PATH']['tmp'],
                "synccomparelist-ID-" . $this->intClientID . ".txt"
            )
        );
        $strContent = $objCompareList->getContent();
        if (strlen($strContent) == 0) {
            $this->arrListCompare = [];
        } else {
            $this->arrListCompare = \Contao\StringUtil::deserialize($strContent);
        }
    }

    /**
     * Save temporary filelist
     *
     * @throws \Exception
     */
    protected function saveTempLists()
    {
        $objFileList = new File(
            $this->objSyncCtoHelper->standardizePath(
                $GLOBALS['SYC_PATH']['tmp'],
                "syncfilelist-ID-" . $this->intClientID . ".txt"
            )
        );
        $objFileList->write(serialize($this->arrListFile));
        $objFileList->close();

        $objCompareList = new File(
            $this->objSyncCtoHelper->standardizePath(
                $GLOBALS['SYC_PATH']['tmp'],
                "synccomparelist-ID-" . $this->intClientID . ".txt"
            )
        );
        $objCompareList->write(serialize($this->arrListCompare));
        $objCompareList->close();
    }

    /**
     * Load information from client
     */
    protected function loadClientInformation()
    {
        $this->arrClientInformation = $this->session->get("syncCto_ClientInformation_" . $this->intClientID);

        if (!is_array($this->arrClientInformation)) {
            $this->arrClientInformation = [];
        }
    }

    /**
     * Sort function
     *
     * @param mixed $a
     * @param mixed $b
     *
     * @return int
     */
    public function sort($a, $b): int
    {
        if ($a["state"] == $b["state"]) {
            return 0;
        }

        return ($a["state"] < $b["state"]) ? -1 : 1;
    }
}