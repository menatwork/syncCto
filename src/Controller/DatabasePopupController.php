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
use Contao\Backend;
use Contao\Config;
use Contao\Environment;
use Contao\Input;
use Contao\System;
use SyncCtoHelper;

/**
 * Class SyncCtoPopup
 */
class DatabasePopupController
{
    // Vars
    protected $intClientID;
    protected $strMode;
    // Helper Classes
    protected $objSyncCtoHelper;
    // Temp data
    protected $arrSyncSettings = array();
    protected $arrErrors       = array();
    // Intern var.
    protected $mixStep;
    protected $strFile;
    protected $Template;

    /**
     * @var \Contao\BackendTemplate
     */
    private $popupTemplate;

    // defines
    const STEP_NORMAL_DB = 'nd';
    const STEP_CLOSE_DB  = 'cl';
    const STEP_ERROR_DB  = 'er';

    /**
     * DatabasePopupController constructor.
     */
    public function __construct()
    {
        \define('TL_ASSETS_URL', '');
    }

    /**
     * Load the template list and go through the steps
     */
    public function runAction()
    {
        \System::getContainer()->get('contao.framework')->initialize();

        // Check user auth
        \BackendUser::getInstance()->authenticate();

        // Set language from get or user
        if (Input::get('language') != '') {
            $GLOBALS['TL_LANGUAGE'] = Input::get('language');
        } else {
            $GLOBALS['TL_LANGUAGE'] = \BackendUser::getInstance()->language;
        }

        // Load language
        System::loadLanguageFile('default');
        System::loadLanguageFile("modules");
        System::loadLanguageFile("tl_syncCto_database");

        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();

        $this->initGetParams();


        if ($this->mixStep == self::STEP_NORMAL_DB) {
            // Set client for communication
            try {
                $this->loadSyncSettings();
                $this->showNormalDatabase();
                $this->saveSyncSettings();

                unset($_POST);
            } catch (\Exception $exc) {
                $this->arrErrors[] = $exc->getMessage();
                $this->mixStep     = self::STEP_ERROR_DB;
            }
        }

        if ($this->mixStep == self::STEP_CLOSE_DB) {
            $this->showClose();
        }

        if ($this->mixStep == self::STEP_ERROR_DB) {
            $this->showError();
        }

        return $this->getResponse();
    }

    /**
     * Output templates
     */
    public function getResponse()
    {
        // Clear all we want a clear array for this windows.
        $GLOBALS['TL_CSS']        = array();
        $GLOBALS['TL_JAVASCRIPT'] = array();

        // Set stylesheets
        $GLOBALS['TL_CSS'][] = 'system/themes/' . Backend::getTheme() . '/basic.css';
        $GLOBALS['TL_CSS'][] = 'bundles/synccto/css/compare.css';

        // Set javascript
        $GLOBALS['TL_JAVASCRIPT'][] = 'assets/mootools/js/mootools-core.min.js';
        $GLOBALS['TL_JAVASCRIPT'][] = 'assets/mootools/js/mootools-more.min.js';
        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/synccto/js/compare.js';
        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/synccto/js/htmltable.js';

        // Set wrapper template information
        $this->popupTemplate           = new BackendTemplate("be_syncCto_popup");
        $this->popupTemplate->theme    = Backend::getTheme();
        $this->popupTemplate->base     = Environment::get('base');
        $this->popupTemplate->language = $GLOBALS['TL_LANGUAGE'];
        $this->popupTemplate->title    = $GLOBALS['TL_CONFIG']['websiteTitle'];
        $this->popupTemplate->charset  = $GLOBALS['TL_CONFIG']['characterSet'];
        $this->popupTemplate->headline = basename(
            utf8_convert_encoding(
                $this->strFile,
                $GLOBALS['TL_CONFIG']['characterSet']
            )
        );

        // Set default information
        $this->Template->id        = $this->intClientID;
        $this->Template->step      = $this->mixStep;
        $this->Template->direction = $this->strMode;

        // Output template
        $this->popupTemplate->content = $this->Template->parse();

        return $this->popupTemplate->getResponse();
    }

    /**
     * Show database server and client compare list
     */
    public function showNormalDatabase()
    {
        // Delete functionality.
        if (array_key_exists("delete", $_POST)) {
            // Make a array from 'serverTables' and 'serverDeleteTables'
            $arrRemoveTables = array();

            if (is_array(Input::post('serverTables')) && count(Input::post('serverTables')) != 0) {
                $arrRemoveTables = Input::post('serverTables');
            }

            if (is_array(Input::post('serverDeleteTables')) && count(Input::post('serverDeleteTables')) != 0) {
                $arrRemoveTables = array_merge($arrRemoveTables, Input::post('serverDeleteTables'));
            }

            // Remove tables from the list.
            foreach ($arrRemoveTables as $value) {
                if (isset($this->arrSyncSettings['syncCto_CompareTables']['recommended']) && array_key_exists($value,
                        $this->arrSyncSettings['syncCto_CompareTables']['recommended'])) {
                    unset($this->arrSyncSettings['syncCto_CompareTables']['recommended'][$value]);
                } else {
                    if (isset($this->arrSyncSettings['syncCto_CompareTables']['nonRecommended']) && array_key_exists($value,
                            $this->arrSyncSettings['syncCto_CompareTables']['nonRecommended'])) {
                        unset($this->arrSyncSettings['syncCto_CompareTables']['nonRecommended'][$value]);
                    }
                }
            }
        } // Close functionality.
        else {
            if (array_key_exists("transfer", $_POST)) {
                foreach ($this->arrSyncSettings['syncCto_CompareTables'] as $arrType) {
                    foreach ($arrType as $keyTable => $valueTable) {
                        if ($valueTable['del'] == true) {
                            $this->arrSyncSettings['syncCto_SyncDeleteTables'][] = $keyTable;
                        } else {
                            $this->arrSyncSettings['syncCto_SyncTables'][] = $keyTable;
                        }
                    }
                }

                unset($this->arrSyncSettings['syncCto_CompareTables']);

                $this->mixStep = self::STEP_CLOSE_DB;

                return;
            }
        }

        // If no table is found skip the view
        if (count($this->arrSyncSettings['syncCto_CompareTables']['recommended']) == 0
            && count($this->arrSyncSettings['syncCto_CompareTables']['nonRecommended']) == 0
        ) {
            unset($this->arrSyncSettings['syncCto_CompareTables']);
            $this->arrSyncSettings['syncCto_SyncDeleteTables'] = array();
            $this->arrSyncSettings['syncCto_SyncTables']       = array();

            $this->mixStep = self::STEP_CLOSE_DB;

            return;
        }

        // Make a look up
        foreach ((array)$this->arrSyncSettings['syncCto_CompareTables']['recommended'] as $strKey => $arrValueA) {
            $arrTransServer = $this->lookUpName($arrValueA['server']['name']);
            $arrTransClient = $this->lookUpName($arrValueA['client']['name']);

            $this->arrSyncSettings['syncCto_CompareTables']['recommended'][$strKey]['server']['tname'] = $arrTransServer['tname'];
            $this->arrSyncSettings['syncCto_CompareTables']['recommended'][$strKey]['server']['iname'] = $arrTransServer['iname'];
            $this->arrSyncSettings['syncCto_CompareTables']['recommended'][$strKey]['client']['tname'] = $arrTransClient['tname'];
            $this->arrSyncSettings['syncCto_CompareTables']['recommended'][$strKey]['client']['iname'] = $arrTransClient['iname'];
        }

        foreach ((array)$this->arrSyncSettings['syncCto_CompareTables']['nonRecommended'] as $strKey => $arrValueA) {
            $arrTransServer = $this->lookUpName($arrValueA['server']['name']);
            $arrTransClient = $this->lookUpName($arrValueA['client']['name']);

            $this->arrSyncSettings['syncCto_CompareTables']['nonRecommended'][$strKey]['server']['tname'] = $arrTransServer['tname'];
            $this->arrSyncSettings['syncCto_CompareTables']['nonRecommended'][$strKey]['server']['iname'] = $arrTransServer['iname'];
            $this->arrSyncSettings['syncCto_CompareTables']['nonRecommended'][$strKey]['client']['tname'] = $arrTransClient['tname'];
            $this->arrSyncSettings['syncCto_CompareTables']['nonRecommended'][$strKey]['client']['iname'] = $arrTransClient['iname'];
        }

        $this->Template                 = new BackendTemplate("be_syncCto_database");
        $this->Template->headline       = $GLOBALS['TL_LANG']['MSC']['comparelist'];
        $this->Template->arrCompareList = $this->arrSyncSettings['syncCto_CompareTables'];
        $this->Template->close          = false;
        $this->Template->error          = false;

        $objExtern = \Database::getInstance()
            ->prepare('SELECT address, port FROM tl_synccto_clients WHERE id=?')
            ->execute($this->intClientID);

        $this->Template->clientPath = $objExtern->address . ':' . $objExtern->port . '/ctoCommunication';
        $this->Template->serverPath = Environment::get('base');
    }

    /**
     * Make a lookup for a human readable table name.
     * First syncCto language
     * Second the mapping for mod language
     * Last the mod language
     *
     * @param string $strName Name of table
     *
     * @return string
     */
    public function lookUpName($strName)
    {
        $strBase = str_replace('tl_', "", $strName);

        // If empty return a array.
        if ($strName == '-') {
            return array(
                'tname' => '-',
                'iname' => '-'
            );
        }

        // Make a lookup in synccto language files
        if (is_array($GLOBALS['TL_LANG']['tl_syncCto_database']) && array_key_exists($strName,
                $GLOBALS['TL_LANG']['tl_syncCto_database'])) {
            if (is_array($GLOBALS['TL_LANG']['tl_syncCto_database'][$strName])) {
                return $this->formateLookUpName($strName, $GLOBALS['TL_LANG']['tl_syncCto_database'][$strName][0]);
            } else {
                return $this->formateLookUpName($strName, $GLOBALS['TL_LANG']['tl_syncCto_database'][$strName]);
            }
        }

        // Get MM name
        if (in_array('metamodels', Config::getInstance()->getActiveModules()) && preg_match("/^mm_/i", $strName)) {
            try {
//                if (!is_null(\MetaModels\Factory::byTableName($strName)))
//                {
//                    $objDCABuilder     = \MetaModels\Dca\MetaModelDcaBuilder::getInstance();
//                    $objMetaModels     = \MetaModels\Factory::byTableName($strName);
//                    $arrDCA            = $objDCABuilder->getDca($objMetaModels->get('id'));
//                    $arrBackendcaption = deserialize($arrDCA['backendcaption']);
//
//                    $strReturn = $objMetaModels->getName();
//
//                    foreach ((array)$arrBackendcaption as $value)
//                    {
//                        if ($value['langcode'] == \BackendUser::getInstance()->language)
//                        {
//                            $strReturn = $value['label'];
//                            break;
//                        }
//                    }
//
//                    return $this->formateLookUpName($strName, $strReturn);
//                }
            } catch (\Exception $exc) {
                // Nothing to do;
            }
        }

        // Little mapping for names
        if (is_array($GLOBALS['SYC_CONFIG']['database_mapping']) && array_key_exists($strName,
                $GLOBALS['SYC_CONFIG']['database_mapping'])) {
            $strRealSystemName = $GLOBALS['SYC_CONFIG']['database_mapping'][$strName];

            if (is_array($GLOBALS['TL_LANG']['MOD'][$strRealSystemName])) {
                return $this->formateLookUpName($strName, $GLOBALS['TL_LANG']['MOD'][$strRealSystemName][0]);
            } else {
                return $this->formateLookUpName($strName, $GLOBALS['TL_LANG']['MOD'][$strRealSystemName]);
            }
        }

        // Search in mod language array for a translation
        if (array_key_exists($strBase, $GLOBALS['TL_LANG']['MOD'])) {
            if (is_array($GLOBALS['TL_LANG']['MOD'][$strBase])) {
                return $this->formateLookUpName($strName, $GLOBALS['TL_LANG']['MOD'][$strBase][0]);
            } else {
                return $this->formateLookUpName($strName, $GLOBALS['TL_LANG']['MOD'][$strBase]);
            }
        }

        return $this->formateLookUpName($strName, $strName);
    }

    /**
     * Return a array with tahble names
     *
     * @param string $strTableName    real name like 'tl_contetn'
     * @param string $strReadableName readable name like 'Conten Elements'
     *
     * @return array('tname' => [for table], 'iname' => [for title/info])
     */
    protected function formateLookUpName($strTableName, $strReadableName)
    {
        // Check if the function is activate
        if (\BackendUser::getInstance()->syncCto_useTranslatedNames) {
            return array(
                'tname' => $strReadableName,
                'iname' => $strTableName
            );
        } else {
            return array(
                'tname' => $strTableName,
                'iname' => $strReadableName
            );
        }
    }

    /**
     * Close popup and go throug next syncCto step
     */
    public function showClose()
    {
        $this->Template           = new BackendTemplate("be_syncCto_database");
        $this->Template->headline = $GLOBALS['TL_LANG']['MSC']['backBT'];
        $this->Template->close    = true;
        $this->Template->error    = false;
    }

    /**
     * Show errors
     */
    public function showError()
    {
        $this->Template           = new BackendTemplate("be_syncCto_database");
        $this->Template->headline = $GLOBALS['TL_LANG']['MSC']['error'];
        $this->Template->arrError = $this->arrErrors;
        $this->Template->text     = $GLOBALS['TL_LANG']['ERR']['general'];
        $this->Template->close    = false;
        $this->Template->error    = true;
    }

    // Helper functions --------------------------------------------------------

    /**
     * Initianize get parameter
     */
    protected function initGetParams()
    {
        // Get Client id
        if (strlen(Input::get('id')) != 0) {
            $this->intClientID = intval(Input::get('id'));
        } else {
            $this->mixStep = self::STEP_ERROR_DB;

            return;
        }

        // Get next step
        if (strlen(Input::get('step')) != 0) {
            $this->mixStep = Input::get('step');
        } else {
            $this->mixStep = self::STEP_NORMAL_DB;
        }

        // Get direction
        if (strlen(Input::get('direction')) != 0) {
            $this->strMode = Input::get('direction');
        }

    }

    protected function loadSyncSettings()
    {
        $this->arrSyncSettings = \Session::getInstance()->get("syncCto_SyncSettings_" . $this->intClientID);

        if (!is_array($this->arrSyncSettings)) {
            $this->arrSyncSettings = array();
        }
    }

    protected function saveSyncSettings()
    {
        if (!is_array($this->arrSyncSettings)) {
            $this->arrSyncSettings = array();
        }

        \Session::getInstance()->set("syncCto_SyncSettings_" . $this->intClientID, $this->arrSyncSettings);
    }

}
