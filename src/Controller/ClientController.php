<?php

namespace MenAtWork\SyncCto\Controller;

use Contao\BackendTemplate;
use Contao\BackendUser;
use Contao\Config;
use Contao\CoreBundle\Controller\AbstractBackendController;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Database;
use Contao\Environment;
use Contao\File;
use Contao\FilesModel;
use Contao\Input;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;
use ContentData;
use Exception;
use LimitIterator;
use MenAtWork\SyncCto\Contao\API as SyncCtoContaoApi;
use MenAtWork\SyncCto\Sync\FileList\Base;
use Psr\Log\LogLevel;
use StepPool;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use SyncCtoCommunicationClient;
use SyncCtoDatabase;
use SyncCtoEnum;
use SyncCtoFiles;
use SyncCtoHelper;
use SyncCtoModuleCheck;
use SyncCtoStats;
use SyncCtoStepDatabaseDiff;


#[Route('%contao.backend.route_prefix%/runsynccto', name: self::class, defaults: ['_scope' => 'backend'])]
class ClientController extends AbstractBackendController
{
    /**
     * Name of the session for the step pool.
     *
     * @var string
     */
    static private string $sessionName = 'syncCto_%s_StepPool%s_%s';

    /**
     * Name of the session for the values of a step pool.
     *
     * @var string
     */
    static private string $sessionNameValue = 'Values';

    // Vars
    protected $strTemplate = 'be_syncCto_steps';
    protected $objTemplateContent;
    protected $intClientID;
    protected $blnAllMode  = false;
    // Helper Classes
    protected $objSyncCtoCommunicationClient;
    protected $objSyncCtoDatabase;
    protected $objSyncCtoFiles;
    protected $objSyncCtoHelper;
    protected $objSyncCtoMeasurement;
    protected $User = null;
    // Content data
    protected $booError;
    protected $booAbort;
    protected $booFinished;
    protected $booRefresh;
    protected $strError;
    protected $strUrl;
    protected $strGoBack;
    protected $strHeadline;
    protected $strInformation;
    protected $intStep;
    protected $floStart;
    // Temp data
    protected $arrListFile;
    protected $arrListCompare;
    // Config
    protected $arrSyncSettings;
    protected $arrClientInformation;
    protected $arrModeAll;

    /**
     * @var ContentData
     */
    protected $objData;

    /**
     * @var StepPool
     */
    protected $objStepPool;

    /* -------------------------------------------------------------------------
     * Getter / Setter
     */

    /**
     * @var mixed|SessionInterface
     */
    private mixed $session;

    private                 $Template;
    private array           $templateVars;
    private RouterInterface $router;

    /**
     * @return int
     */
    public function getClientID()
    {
        return $this->intClientID;
    }

    /**
     * @return array
     */
    public function getSyncSettings()
    {
        return $this->arrSyncSettings;
    }

    /**
     * @param array $arrSyncSettings
     */
    public function setSyncSettings($arrSyncSettings)
    {
        $this->arrSyncSettings = $arrSyncSettings;
    }

    /**
     * @return StepPool
     */
    public function getStepPool()
    {
        return $this->objStepPool;
    }

    /**
     * @return ContentData
     */
    public function getData()
    {
        return $this->objData;
    }

    /**
     * Get the client informations
     *
     * @return array
     */
    public function getClientInformation()
    {
        return $this->arrClientInformation;
    }

    // Template getter / setter ------------------------------------------------

    public function isError()
    {
        return $this->booError;
    }

    public function setError($booError)
    {
        $this->booError = $booError;
    }

    public function isAbort()
    {
        return $this->booAbort;
    }

    public function setAbort($booAbort)
    {
        $this->booAbort = $booAbort;
    }

    public function isFinished()
    {
        return $this->booFinished;
    }

    public function setFinished($booFinished)
    {
        $this->booFinished = $booFinished;
    }

    public function isRefresh()
    {
        return $this->booRefresh;
    }

    public function setRefresh($booRefresh)
    {
        $this->booRefresh = $booRefresh;
    }

    public function getErrorMsg()
    {
        return $this->strError;
    }

    public function setErrorMsg($strError)
    {
        $this->strError = $strError;
    }

    public function getUrl()
    {
        return $this->strUrl;
    }

    public function setUrl($strUrl)
    {
        $this->strUrl = $strUrl;
    }

    public function getGoBack()
    {
        return $this->strGoBack;
    }

    public function setGoBack($strGoBack)
    {
        $this->strGoBack = $strGoBack;
    }

    public function getHeadline()
    {
        return $this->strHeadline;
    }

    public function setHeadline($strHeadline)
    {
        $this->strHeadline = $strHeadline;
    }

    public function getInformation()
    {
        return $this->strInformation;
    }

    public function setInformation($strInformation)
    {
        $this->strInformation = $strInformation;
    }

    public function getStep()
    {
        return $this->intStep;
    }

    public function setStep($intStep)
    {
        $this->intStep = $intStep;
    }

    public function getStart()
    {
        return $this->floStart;
    }

    public function setStart($floStart)
    {
        $this->floStart = $floStart;
    }

    public function addStep()
    {
        $this->intStep++;
    }

    public function __construct()
    {
        dd(
             \Contao\Config::get('timeFormat'),
             \Contao\Config::get('dateFormat'),
        );

        // Load helper
        $this->router = System::getContainer()->get('router');
        $this->User = BackendUser::getInstance();
        $this->twig = System::getContainer()->get('twig');
        $this->objSyncCtoDatabase = SyncCtoDatabase::getInstance();
        $this->objSyncCtoFiles = SyncCtoFiles::getInstance();
        $this->objSyncCtoCommunicationClient = SyncCtoCommunicationClient::getInstance();
        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();

        $container = System::getContainer();
        /** @var RequestStack $requestStack */
        $requestStack = $container->get('request_stack');
        $this->session = $requestStack->getSession();

        $this->templateVars = [];

        // Load language
        System::loadLanguageFile("tl_syncCto_steps");
        System::loadLanguageFile("tl_syncCto_check");

        // Load CSS
        $GLOBALS['TL_CSS'][] = 'bundles/synccto/css/steps.css';
    }

    protected function log($msg, $context = [], $logLevel = LogLevel::INFO): void
    {
        // ToDo we should replace the log function. This is only a placeholder
        // to fix it later.
    }

    /**
     * Contao Callback support. Take it and redirect it to the right address.
     *
     * @return void
     * @see \MenAtWork\SyncCto\Controller\ClientController::__invoke
     *
     */
    public function generate(): void
    {
        if (Input::get("act") == "start" && Input::get('table') == 'tl_syncCto_clients_showExtern') {
            $controllerUrl = $this->router->generate(self::class);
            $redirectUrl = $controllerUrl;
            $urlParam = [];
            foreach (['table', 'act', 'do', 'id'] as $paramName) {
                $urlParam[$paramName] = Input::get($paramName);
            }
            throw new RedirectResponseException($redirectUrl . '?' . http_build_query($urlParam), 302);
        }

        Message::addError($GLOBALS['TL_LANG']['ERR']['call_directly']);
        throw new RedirectResponseException("/contao?do=synccto_clients", 302);
    }

    /**
     * New start point for the sync. We run our own controller now :)
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function __invoke(): Response
    {
        // Check if start is set
        if (Input::get("act") != "start" && Input::get('table') != 'tl_syncCto_clients_showExtern') {
            Message::addError($GLOBALS['TL_LANG']['ERR']['call_directly']);
            return $this->redirect("/contao?do=synccto_clients");
        }

        // Get step
        if (Input::get("step") == "" || Input::get("step") == null) {
            $this->intStep = 0;
        } else {
            $this->intStep = intval(Input::get("step"));
        }

        // Get Client id or check if we in allmode
        if (strlen(Input::get("id")) != 0 && Input::get("mode") != 'all') {
            $this->intClientID = intval(Input::get("id"));
        } elseif (strlen(Input::get("id")) != 0 && Input::get("mode") == 'all' && Input::get("next") != '1') {
            $this->blnAllMode = true;
            $this->intClientID = intval(Input::get("id"));
        } elseif (Input::get("mode") == 'all') {
            $this->blnAllMode = true;
            $this->initModeAll();
        } else {
            Message::addError($GLOBALS['TL_LANG']['ERR']['call_directly']);
            return $this->redirect("/contao?do=synccto_clients");
        }

        // Set client for communication
        try {
            $arrClientInformation = $this->objSyncCtoCommunicationClient->setClientBy(intval($this->intClientID));
            $this->templateVars['clientName'] = $arrClientInformation["title"];
        } catch (Exception $exc) {
            Message::addError($GLOBALS['TL_LANG']['ERR']['client_set']);
//            $this->log($exc->getMessage(), __CLASS__ . " | " . __FUNCTION__, TL_ERROR);
//            $this->redirect("/contao?do=synccto_clients");
            throw $exc;
        }

        // Set template
        $this->templateVars['showControl'] = true;
        $this->templateVars['showNextControl'] = false;
        $this->templateVars['modeAll'] = false;
        $this->templateVars['tryAgainLink'] = Environment::get('requestUri') . (($this->blnAllMode) ? '&mode=all' : '');
        $this->templateVars['abortLink'] = Environment::get('requestUri') . "&abort=true" . (($this->blnAllMode) ? '&mode=all' : '');
        $this->templateVars['nextClientLink'] = Environment::get('requestUri') . "&abort=true" . (($this->blnAllMode) ? '&mode=all&next=1' : '');

        // Load content from session
        if ($this->intStep != 0) {
            $this->loadContenData();
        }

        // Load settings from dca
        $this->loadSyncSettings();
        $this->loadClientInformation();

        // Set time out for database. Ticket #2653
        $tmpResult = Database::getInstance()
                             ->execute('SELECT @@SESSION.wait_timeout as wTimeout, @@SESSION.interactive_timeout as iTimeout')
        ;

        $waitTimeOut = $tmpResult->wTimeout;
        $interactiveTimeout = $tmpResult->iTimeout;

        //overwrite the default values if higher ones are defined in the settings
        if (isset($GLOBALS['TL_CONFIG']['syncCto_custom_settings'])
            && $GLOBALS['TL_CONFIG']['syncCto_custom_settings']
            && ((int) $GLOBALS['TL_CONFIG']['syncCto_wait_timeout']) > 0
            && ((int) $GLOBALS['TL_CONFIG']['syncCto_interactive_timeout']) > 0
        ) {
            $waitTimeOut = max($waitTimeOut, (int) $GLOBALS['TL_CONFIG']['syncCto_wait_timeout']);
            $interactiveTimeout = max($interactiveTimeout, (int) $GLOBALS['TL_CONFIG']['syncCto_interactive_timeout']);
        }

        Database::getInstance()
                ->prepare('SET SESSION wait_timeout = CONVERT(?, SIGNED), SESSION interactive_timeout = CONVERT(?, SIGNED);')
                ->execute(
                    (int) $waitTimeOut,
                    (int) $interactiveTimeout
                )
        ;

        if (Input::get("abort") == "true") {
            // Load content from session
            $this->loadContenData();
            // So abort page
            $this->pageSyncAbort();
            // Save content in session
            $this->saveContentData();
            // Set template vars
            $this->setTemplateVars();
            // Hidden control
            $this->templateVars['showControl'] = false;

            $this->setTemplateVars();
            return $this->render(
                '@SyncCto/be_syncCto_steps.html.twig',
                $this->templateVars
            );
        }

        // Which table is in use
        $tableInput = Input::get("table");
        if ($tableInput == "tl_syncCto_clients_syncTo") {
            $this->pageSyncTo();
        } elseif ($tableInput == "tl_syncCto_clients_syncFrom") {
            $this->pageSyncFrom();
        } elseif ($tableInput == "tl_syncCto_clients_showExtern") {
            $this->pageShowExtern();
        } else {
            $_SESSION["TL_ERROR"][] = $GLOBALS['TL_LANG']['ERR']['unknown_function'];
            $this->redirect("/contao?do=synccto_clients");
        }

        // Save content in session
        $this->saveContentData();
        $this->saveClientInformation();
        $this->saveSyncSettings();

        // Save the informations for mode all.
        if (Input::get("mode") == 'all') {
            $this->saveStepPoolAll();
        }

        // Set Vars for the template
        $this->setTemplateVars();
        return $this->render(
            '@SyncCto/be_syncCto_steps.html.twig',
            $this->templateVars
        );
    }

    /**
     * Init the data array for syncAll or load it from session.
     *
     * @return void
     */
    protected function initModeAll()
    {
        $this->arrModeAll = [
            'clientIds' => [],
            'index'     => -1,
            'count'     => 0,
        ];

        // Check if we have a init call.
        if (Input::get('init') == 1) {
            // Get a list with all client.
            $objClients = Database::getInstance()->query('SELECT id FROM tl_synccto_clients');

            // ToDo: Add a check for premissions.

            $this->arrModeAll = [
                'clientIds' => $objClients->fetchEach('id'),
                'index'     => -1,
                'count'     => 0,
            ];
        } // Or load all from Session.
        else {
            // Load from Session.
            $this->loadStepPoolAll();
        }

        // Set client id.
        if ($this->arrModeAll['index'] == -1) {
            $this->intClientID = $this->arrModeAll['clientIds'][0];
            $this->arrModeAll['count'] = 0;
            $this->arrModeAll['index'] = 0;
        } // Set client id.
        else {
            if (Input::get('next') == 1) {
                // Increase everything.
                $this->arrModeAll['index']++;
                $this->arrModeAll['count']++;

                // Set new Client and step to 0 for init.
                $this->intClientID = $this->arrModeAll['clientIds'][0];
                $this->intStep = 0;
            } else {
                $this->intClientID = $this->arrModeAll['clientIds'][$this->arrModeAll['index']];
            }
        }

        // Save all to session
        $this->saveStepPoolAll();
    }

    /* -------------------------------------------------------------------------
     * Helper function for session/tempfiles etc.
     */

    /**
     * Build the vars for the template.
     *
     * @return void
     */
    protected function setTemplateVars(): void
    {
        // There is a problem, where some users got a contao/contao in the url.
        // So we clean it up.
        $baseUrl = Environment::get('base');
        if (str_ends_with($baseUrl, '/')) {
            $baseUrl = substr($baseUrl, 0, -1);
        }
        if (str_ends_with($baseUrl, '/contao')) {
            $baseUrl = substr($baseUrl, 0, -7);
        }
        $controllerUrl = $this->router->generate(self::class);

        $this->templateVars['base'] = $baseUrl;
        $this->templateVars['url'] = $controllerUrl . '?' . $this->strUrl;
        $this->templateVars['goBack'] = $this->strGoBack;
        $this->templateVars['data'] = $this->objData->getArrValues();
        $this->templateVars['step'] = $this->intStep;
        $this->templateVars['subStep'] = $this->objStepPool->step;
        $this->templateVars['error'] = $this->booError;
        $this->templateVars['error_msg'] = $this->strError;
        $this->templateVars['refresh'] = $this->booRefresh;
        $this->templateVars['start'] = $this->floStart;
        $this->templateVars['headline'] = $this->strHeadline;
        $this->templateVars['information'] = $this->strInformation;
        $this->templateVars['finished'] = $this->booFinished;
        $this->templateVars['allMode'] = $this->blnAllMode;
        $this->templateVars['language'] = [
            'goBack'    => $GLOBALS['TL_LANG']['MSC']['backBT'],
            'error'     => $GLOBALS['TL_LANG']['MSC']['error'],
            'abort'     => $GLOBALS['TL_LANG']['MSC']['abort_sync'],
            'repeat'    => $GLOBALS['TL_LANG']['MSC']['repeat_sync'],
            'next_sync' => $GLOBALS['TL_LANG']['MSC']['next_sync']
        ];

        if (Input::get('table') == 'tl_syncCto_clients_syncTo') {
            $this->templateVars['direction'] = 'to';
        } elseif (Input::get('table') == 'tl_syncCto_clients_syncFrom') {
            $this->templateVars['direction'] = 'from';
        } else {
            $this->templateVars['direction'] = 'na';
        }
    }

    /**
     * Save the current state of the page/sychronization
     */
    protected function saveContentData()
    {
        $arrContenData = [
            "error"       => $this->booError,
            "error_msg"   => $this->strError,
            "refresh"     => $this->booRefresh,
            "finished"    => $this->booFinished,
            "step"        => $this->intStep,
            "url"         => $this->strUrl,
            "goBack"      => $this->strGoBack,
            "start"       => $this->floStart,
            "headline"    => $this->strHeadline,
            "information" => $this->strInformation,
            "data"        => $this->objData->getArrValues(),
            "abort"       => $this->booAbort,
        ];

        $this->session->set("syncCto_Content", $arrContenData);
    }

    /**
     * Load the current state of the page/synchronization
     */
    protected function loadContenData()
    {
        $arrContenData = $this->session->get("syncCto_Content");

        if (is_array($arrContenData) && count((array) $arrContenData) != 0) {
            $this->booError = $arrContenData["error"];
            $this->booAbort = $arrContenData["abort"];
            $this->booFinished = $arrContenData["finished"];
            $this->booRefresh = $arrContenData["refresh"];
            $this->strError = $arrContenData["error_msg"];
            $this->strUrl = $arrContenData["url"];
            $this->strGoBack = $arrContenData["goBack"];
            $this->strHeadline = $arrContenData["headline"];
            $this->strInformation = $arrContenData["information"];
            $this->intStep = $arrContenData["step"];
            $this->floStart = $arrContenData["start"];
            $this->objData = new ContentData($arrContenData["data"], $this->intStep);
        } else {
            $this->booError = false;
            $this->booAbort = false;
            $this->booFinished = false;
            $this->booRefresh = false;
            $this->strError = "";
            $this->strUrl = "";
            $this->strGoBack = "";
            $this->strHeadline = "";
            $this->strInformation = "";
            $this->intStep = 0;
            $this->floStart = 0;
            $this->objData = new ContentData([], $this->intStep);
        }
    }

    protected function loadStepPool(): void
    {
        // Load values.
        $values = $this->session->get(
            sprintf(
                self::$sessionName,
                $this->intClientID,
                self::$sessionNameValue,
                $this->intStep
            )
        );
        if (empty($values) || !is_array($values)) {
            $values = [];
        }

        $this->objStepPool = new StepPool($values, $this->intStep);
    }

    protected function saveStepPool(): void
    {
        // Don't use the "$this->step" var here.
        $this->session->set(
            sprintf(
                self::$sessionName,
                $this->intClientID,
                self::$sessionNameValue,
                $this->objStepPool->getStepID()
            ),
            $this->objStepPool->getValues()
        );

        $this->session->save();
    }

    protected function resetStepPool(): void
    {
//        $this->session->remove(
//            sprintf(
//                self::$sessionName,
//                $this->intClientID,
//                self::$sessionNameValue,
//                $this->intStep
//            )
//        );
    }

    protected function resetStepPoolByID(array $ids): void
    {
        foreach ($ids as $id) {
            $this->session->remove(
                sprintf(
                    self::$sessionName,
                    $this->intClientID,
                    self::$sessionNameValue,
                    $id
                )
            );
        }
    }

    protected function saveStepPoolAll()
    {
        $this->session->set("syncCto_All_StepPool", $this->arrModeAll);
    }

    protected function loadStepPoolAll()
    {
        $arrStepPool = $this->session->get("syncCto_All_StepPool");

        if ($arrStepPool == false || !is_array($arrStepPool)) {
            $arrStepPool = [];
        }

        $this->arrModeAll = $arrStepPool;
    }

    protected function initTempLists()
    {
        // Load Files
        $path = $this
            ->objSyncCtoHelper
            ->standardizePath(
                $GLOBALS['SYC_PATH']['tmp'],
                "syncfilelist-ID-" . $this->intClientID . ".txt"
            )
        ;
        if (file_exists($this->objSyncCtoHelper->getContaoRoot() . DIRECTORY_SEPARATOR . $path)) {
            $objFileList = new File($path);
            $objFileList->delete();
        }

        $path = $this
            ->objSyncCtoHelper
            ->standardizePath(
                $GLOBALS['SYC_PATH']['tmp'],
                "synccomparelist-ID-" . $this->intClientID . ".txt"
            )
        ;
        if (file_exists($this->objSyncCtoHelper->getContaoRoot() . DIRECTORY_SEPARATOR . $path)) {
            $objCompareList = new File($path);
            $objCompareList->delete();
        }
    }

    /**
     * Load the temp files into the memory.
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function loadTempLists()
    {
        // Load Files
        $filePath = $this->objSyncCtoHelper->standardizePath(
            $GLOBALS['SYC_PATH']['tmp'],
            "syncfilelist-ID-" . $this->intClientID . ".txt"
        );
        $strContent = '';

        if (file_exists($this->objSyncCtoHelper->getContaoRoot() . DIRECTORY_SEPARATOR . $filePath)) {
            $objFileList = new File($filePath);
            $strContent = $objFileList->getContent();
        }

        if (strlen($strContent) == 0) {
            $this->arrListFile = [];
        } else {
            $this->arrListFile = unserialize($strContent);
        }

        // Load Files
        $filePath = $this->objSyncCtoHelper->standardizePath(
            $GLOBALS['SYC_PATH']['tmp'],
            "synccomparelist-ID-" . $this->intClientID . ".txt"
        );
        $strContent = '';

        if (file_exists($this->objSyncCtoHelper->getContaoRoot() . DIRECTORY_SEPARATOR . $filePath)) {
            $objCompareList = new File($filePath);
            $strContent = $objCompareList->getContent();
        }

        if (strlen($strContent) == 0) {
            $this->arrListCompare = [];
        } else {
            $this->arrListCompare = unserialize($strContent);
        }
    }

    protected function saveTempLists()
    {
        $objFileList = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "syncfilelist-ID-" . $this->intClientID . ".txt"));
        $objFileList->write(serialize($this->arrListFile));
        $objFileList->close();

        $objCompareList = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "synccomparelist-ID-" . $this->intClientID . ".txt"));
        $objCompareList->write(serialize($this->arrListCompare));
        $objCompareList->close();
    }

    protected function loadSyncSettings()
    {
        $this->arrSyncSettings = $this->session->get("syncCto_SyncSettings_" . $this->intClientID);

        if (!is_array($this->arrSyncSettings)) {
            $this->arrSyncSettings = [];
        }
    }

    protected function saveSyncSettings()
    {
        if (!is_array($this->arrSyncSettings)) {
            $this->arrSyncSettings = [];
        }

        $this->session->set("syncCto_SyncSettings_" . $this->intClientID, $this->arrSyncSettings);
    }

    protected function loadClientInformation()
    {
        $this->arrClientInformation = $this->session->get("syncCto_ClientInformation_" . $this->intClientID);

        if (!is_array($this->arrClientInformation)) {
            $this->arrClientInformation = [];
        }
    }

    protected function saveClientInformation()
    {
        $this->session->set("syncCto_ClientInformation_" . $this->intClientID, $this->arrClientInformation);
    }

    protected function resetClientInformation()
    {
        $this->session->set("syncCto_ClientInformation_" . $this->intClientID, false);
    }

    /* -------------------------------------------------------------------------
     * Helper function for sync settings
     */

    protected function checkSyncFileList()
    {
        if (!array_key_exists("syncCto_Type", $this->arrSyncSettings) || count((array) $this->arrSyncSettings["syncCto_Type"]) == 0) {
            return false;
        }

        $arrCheck = [
            'core_change',
            'core_delete',
            'user_change',
            'user_delete',
        ];

        foreach ($arrCheck as $value) {
            if (in_array($value, $this->arrSyncSettings["syncCto_Type"])) {
                return true;
            }
        }
    }

    protected function checkSyncDatabase()
    {
        if (!array_key_exists('syncCto_SyncDatabase', $this->arrSyncSettings)) {
            return false;
        }

        return $this->arrSyncSettings['syncCto_SyncDatabase'];
    }

    /* -------------------------------------------------------------------------
     * Functions for comunication
     */

    /**
     * Setup for page syncTo
     */
    private function pageSyncTo()
    {
        // Init | Set Step to 1
        if ($this->intStep == 0) {
            // Init content
            $this->booError = false;
            $this->booAbort = false;
            $this->booFinished = false;
            $this->strError = "";
            $this->booRefresh = true;
            $this->strUrl = "do=synccto_clients&amp;table=tl_syncCto_clients_syncTo&amp;act=start&amp;id=" . $this->intClientID;
            $this->strGoBack = Environment::get('base') . "contao?do=synccto_clients";
            $this->strHeadline = $GLOBALS['TL_LANG']['tl_syncCto_sync']['edit'];
            $this->strInformation = "";
            $this->intStep = 1;
            $this->floStart = microtime(true);
            $this->objData = new ContentData([], $this->intStep);

            // If mode all, add it to url.
            if ($this->blnAllMode) {
                $this->strUrl .= '&amp;mode=all';
            }

            // Init tmep files
            $this->initTempLists();

            // Update last sync
            Database::getInstance()->prepare("UPDATE `tl_synccto_clients` %s WHERE `tl_synccto_clients`.`id` = ?")
                    ->set(["syncTo_user" => BackendUser::getInstance()->id, "syncTo_tstamp" => time()])
                    ->execute($this->intClientID)
            ;

            // Add stats
            SyncCtoStats::getInstance()->addStartStat(BackendUser::getInstance()->id, $this->intClientID, time(), $this->arrSyncSettings, SyncCtoStats::SYNCDIRECTION_TO);

            // Write log
//            $this->log(vsprintf("Start synchronization client ID %s.", [Input::get("id")]), __CLASS__ . " " . __FUNCTION__, "INFO");

            // Reset some Sessions
            $this->resetStepPoolByID([1, 2, 3, 4, 5, 6, 7]);
            $this->resetClientInformation();

            $this->session->set("SyncCto_FileLock_ID" . $this->intClientID, ["lock" => false]);
        }

        // Check if we have to do the current step
        switch ($this->intStep) {
            // Nothing to do
            case 1:
                break;

            // Check if we have files
            case 2:
                if (!$this->checkSyncFileList()) {
                    $this->intStep++;
                    $this->objData->nextStep();
                } else {
                    break;
                }

            // Check if we have files and some big ones
            case 3:
                $this->loadTempLists();

                if (!$this->checkSyncFileList()) {
                    $this->intStep++;
                    $this->objData->nextStep();
                } else {
                    if (count((array) $this->arrListCompare) == 0) {
                        $this->intStep++;
                        $this->objData->nextStep();
                    } else {
                        break;
                    }
                }

            // Check if some tables are choosen
            case 4:
                if (!$this->checkSyncDatabase()) {
                    $this->intStep++;
                    $this->objData->nextStep();
                } else {
                    break;
                }

            // Check if we have pro features
            case 5:
                if (array_key_exists('SyncCtoProBundle', System::getContainer()->getParameter('kernel.bundles'))) {
                    $objStepPro = SyncCtoStepDatabaseDiff::getInstance();
                    $objStepPro->setSyncCto($this);

                    if (!$objStepPro->checkSyncTo()) {
                        $this->intStep++;
                        $this->objData->nextStep();
                    } else {
                        break;
                    }
                } else {
                    $this->intStep++;
                    $this->objData->nextStep();
                }

            // Check if we have to run the import step
            case 6:
                $this->loadTempLists();

                if (count((array) $this->arrListCompare) == 0
                    && !in_array("localconfig_update", $this->arrSyncSettings["syncCto_Type"])
                    && $this->arrSyncSettings["syncCto_ShowError"] != true
                    && $this->arrSyncSettings["syncCto_AttentionFlag"] != true
                    && count((array) $this->arrSyncSettings["syncCto_Systemoperations_Maintenance"]) == 0
                    && !in_array("temp_folders", $this->arrSyncSettings["syncCto_Systemoperations_Maintenance"])
                ) {
                    $this->intStep++;
                    $this->objData->nextStep();
                } else {
                    break;
                }
        }

        // Load step pool for current step
        $this->loadStepPool();

        // Load Step
        switch ($this->intStep) {
            // Init|Check
            case 1:
                $this->pageSyncShowStep1();
                break;

            // Filelists
            case 2:
                $this->loadTempLists();
                $this->pageSyncToShowStep2();
                $this->saveTempLists();
                break;

            // File send
            case 3:
                $this->loadTempLists();
                $this->pageSyncToShowStep3();
                $this->saveTempLists();
                break;

            // Database
            case 4:
                $this->pageSyncToShowStep4();
                break;

            // Run pro features
            case 5:
                $this->pageSyncToShowStepPro();
                break;

            // Import Files | Import Config | etc.
            case 6:
                $this->loadTempLists();
                $this->pageSyncToShowStep6();
                $this->saveTempLists();
                break;

            // Cleanup | Show informations
            case 7:
                $this->loadTempLists();
                $this->pageSyncToShowStep7();
                $this->saveTempLists();
                break;

            default:
                $_SESSION["TL_ERROR"] = ["Unknown step for sync."];
                $this->redirect("/contao?do=synccto_clients");
                break;
        }

        // Save step pool for current step
        $this->saveStepPool();
    }

    /**
     * Setup for page syncFrom
     *
     * @todo
     */
    private function pageSyncFrom()
    {
        // Init | Set Step to 1
        if ($this->intStep == 0) {
            // Init content
            $this->booError = false;
            $this->booAbort = false;
            $this->booFinished = false;
            $this->strError = "";
            $this->booRefresh = true;
            $this->strUrl = "do=synccto_clients&amp;table=tl_syncCto_clients_syncFrom&amp;act=start&amp;id=" . $this->intClientID;
            $this->strGoBack = Environment::get('base') . "contao?do=synccto_clients";
            $this->strHeadline = $GLOBALS['TL_LANG']['tl_syncCto_sync']['edit'];
            $this->strInformation = "";
            $this->intStep = 1;
            $this->floStart = microtime(true);
            $this->objData = new ContentData([], $this->intStep);

            // Init tmep files
            $this->initTempLists();

            // Update last sync
            Database::getInstance()->prepare("UPDATE `tl_synccto_clients` %s WHERE `tl_synccto_clients`.`id` = ?")
                    ->set(["syncFrom_user" => BackendUser::getInstance()->id, "syncFrom_tstamp" => time()])
                    ->execute($this->intClientID)
            ;

            // Add stats
            SyncCtoStats::getInstance()->addStartStat(BackendUser::getInstance()->id, $this->intClientID, time(), $this->arrSyncSettings, SyncCtoStats::SYNCDIRECTION_FROM);

            // Write log
            $this->log(vsprintf("Start synchronization server with client ID %s.", [Input::get("id")]), __CLASS__ . " " . __FUNCTION__, "INFO");

            // Reset some Sessions
            $this->resetStepPoolByID([1, 2, 3, 4, 5, 6, 7]);
            $this->resetClientInformation();

            $this->session->set("SyncCto_FileLock_ID" . $this->intClientID, ["lock" => false]);
        }

        // Check if we have to do the current step
        switch ($this->intStep) {
            // Nothing to do
            case 1:
                break;

            // Check if we have files
            case 2:
                if (!$this->checkSyncFileList()) {
                    $this->intStep++;
                    $this->objData->nextStep();
                } else {
                    break;
                }

            // Check if we have files and some big ones
            case 3:
                $this->loadTempLists();

                if (!$this->checkSyncFileList()) {
                    $this->intStep++;
                    $this->objData->nextStep();
                } else {
                    if (count((array) $this->arrListCompare) == 0) {
                        $this->intStep++;
                        $this->objData->nextStep();
                    } else {
                        break;
                    }
                }

            // Check if some tables are choosen
            case 4:
                if (!$this->checkSyncDatabase()) {
                    $this->intStep++;
                    $this->objData->nextStep();
                } else {
                    break;
                }


            // Check if we have pro features
            case 5:
                if (array_key_exists('SyncCtoProBundle', System::getContainer()->getParameter('kernel.bundles'))) {
                    $objStepPro = SyncCtoStepDatabaseDiff::getInstance();
                    $objStepPro->setSyncCto($this);

                    if (!$objStepPro->checkSyncTo()) {
                        $this->intStep++;
                        $this->objData->nextStep();
                    } else {
                        break;
                    }
                } else {
                    $this->intStep++;
                    $this->objData->nextStep();
                }

            // Check if we have to run the import step
            case 6:
                $this->loadTempLists();

                if (count((array) $this->arrListCompare) == 0
                    && !in_array("localconfig_update", $this->arrSyncSettings["syncCto_Type"])
                    && $this->arrSyncSettings["syncCto_AttentionFlag"] != true
                    && count((array) $this->arrSyncSettings["syncCto_Systemoperations_Maintenance"]) == 0
                    && !in_array("temp_folders", $this->arrSyncSettings["syncCto_Systemoperations_Maintenance"])
                ) {
                    $this->intStep++;
                    $this->objData->nextStep();
                } else {
                    break;
                }
        }

        // Load step pool for current step
        $this->loadStepPool();

        // Load Step
        switch ($this->intStep) {
            // Init|Check
            case 1:
                $this->pageSyncShowStep1();
                break;

            // Filelists
            case 2:
                $this->loadTempLists();
                $this->pageSyncFromShowStep2();
                $this->saveTempLists();
                break;

            // File send
            case 3:
                $this->loadTempLists();
                $this->pageSyncFromShowStep3();
                $this->saveTempLists();
                break;

            // Database
            case 4:
                $this->pageSyncFromShowStep4();
                break;

            // Run pro features
            case 5:
                $this->pageSyncFromShowStepPro();
                break;

            // Import Files | Import Config | etc.
            case 6:
                $this->loadTempLists();
                $this->pageSyncFromShowStep6();
                $this->saveTempLists();
                break;

            // Show informations
            case 7:
                $this->loadTempLists();
                $this->pageSyncFromShowStep7();
                $this->saveTempLists();
                break;

            default:
                $_SESSION["TL_ERROR"] = ["Unknown step for sync."];
                $this->redirect("/contao?do=synccto_clients");
                break;
        }

        // Save step pool for current step
        $this->saveStepPool();
    }

    /**
     * Setup for page showExtern
     */
    private function pageShowExtern()
    {
        // Init | Set Step to 1
        if ($this->intStep == 0) {
            // Init content
            $this->booError = false;
            $this->booAbort = false;
            $this->booFinished = false;
            $this->strError = "";
            $this->booRefresh = true;
            $this->strUrl = "do=synccto_clients&amp;table=tl_syncCto_clients_showExtern&amp;act=start&amp;id=" . $this->intClientID;
            $this->strGoBack = Environment::get('base') . "contao?do=synccto_clients";
            $this->strHeadline = $GLOBALS['TL_LANG']['tl_syncCto_check']['check'];
            $this->strInformation = "";
            $this->intStep = 1;
            $this->floStart = microtime(true);
            $this->objData = new ContentData([], $this->intStep);

            // Init tmep files
            $this->initTempLists();

            // Add stats
            SyncCtoStats::getInstance()->addStartStat(BackendUser::getInstance()->id, $this->intClientID, time(), [], SyncCtoStats::SYNCDIRECTION_CHECK);

            // Reset some Sessions
            $this->resetStepPoolByID([1, 2, 3, 4, 5, 6, 7]);
            $this->resetClientInformation();
        }

        // Load step pool for current step
        $this->loadStepPool();

        /* ---------------------------------------------------------------------
         * Run page
         */

        // Init
        if ($this->objStepPool->step == null) {
            $this->objStepPool->step = 1;
        }

        // Set content back to normale mode
        $this->strError = "";
        $this->booError = false;
        $this->objData->setState(SyncCtoEnum::WORK_WORK);

        try {
            switch ($this->objStepPool->step) {
                /**
                 * Show step
                 */
                case 1:
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1_show"]['description_1']);
                    $this->objData->setState(SyncCtoEnum::WORK_WORK);

                    $this->objStepPool->increaseSubStep();
                    break;

                /**
                 * Start connection
                 */
                case 2:
                    $this->objSyncCtoCommunicationClient->startConnection();

                    $this->objStepPool->increaseSubStep();
                    break;

                /**
                 * Referer check deactivate
                 */
                case 3:
                    if (!$this->objSyncCtoCommunicationClient->referrerDisable()) {
                        $this->objData->setState(SyncCtoEnum::WORK_ERROR);
                        $this->booError = true;
                        $this->strError = $GLOBALS['TL_LANG']['ERR']['referer'];

                        break;
                    }

                    $this->objStepPool->increaseSubStep();
                    break;

                /**
                 * Get informations and close connection
                 */
                case 4:
                    // Get infomations
                    $arrConfigurations = $this->objSyncCtoCommunicationClient->getPhpConfigurations();
                    $arrFunctions = $this->objSyncCtoCommunicationClient->getPhpFunctions();
                    $arrProFunctions = $this->objSyncCtoCommunicationClient->getProFunctions();
                    $arrExtendedInformation = $this->objSyncCtoCommunicationClient->getExtendedInformation(\Contao\Config::get('datimFormat'));
                    $strVersion = $this->objSyncCtoCommunicationClient->getVersionSyncCto();

                    // Stop connection
                    $this->objSyncCtoCommunicationClient->referrerEnable();
                    $this->objSyncCtoCommunicationClient->stopConnection();
                    SyncCtoStats::getInstance()->addEndStat(time());

                    // Load module for html
                    $objCheck = new SyncCtoModuleCheck();
                    $objCheckTemplate = new BackendTemplate('be_syncCto_smallCheck');
                    $objCheckTemplate->checkPhpConfiguration = $objCheck->checkPhpConfiguration($arrConfigurations);
                    $objCheckTemplate->checkPhpFunctions = $objCheck->checkPhpFunctions($arrFunctions);
                    $objCheckTemplate->checkProFunctions = $objCheck->checkProFunctions($arrProFunctions);
                    $objCheckTemplate->checkExtendedInformation = $objCheck->compareExtendedInformation($objCheck->getExtendedInformation(), $arrExtendedInformation);
                    $objCheckTemplate->syc_version = $strVersion;

                    // Show information
                    $this->objData->setState(SyncCtoEnum::WORK_OK);
                    $this->objData->setHtml($objCheckTemplate->parse());

                    $this->booFinished = true;
                    $this->booRefresh = false;
                    $this->templateVars['showControl'] = false;

                    $this->objStepPool->increaseSubStep();

                case 5:
                    $this->objData->setState(SyncCtoEnum::WORK_OK);
                    $this->booFinished = true;
                    $this->booRefresh = false;
                    $this->templateVars['showControl'] = false;
                    break;
            }
        } catch (Exception $exc) {
//            $this->log(vsprintf("Error on synchronization client ID %s", [Input::get("id")]), __CLASS__ . " " . __FUNCTION__, "ERROR");

            $this->booError = true;
            $this->strError = $exc->getMessage();

            $this->objData->setState(SyncCtoEnum::WORK_ERROR);
        }

        // Save step pool for current step
        $this->saveStepPool();
    }

    /* -------------------------------------------------------------------------
     * Step function for SyncTo AND SyncFrom
     */

    private function initStep()
    {
        // Init
        if (empty($this->objStepPool->getSubStep())) {
            $this->objStepPool->initSubStep();
        }

        // Set content back to normal mode
        $this->strError = "";
        $this->booError = false;
        $this->objData->setState(SyncCtoEnum::WORK_WORK);
    }

    /**
     * Start the connection and save some parameter to session
     */
    private function pageSyncShowStep1()
    {
        $this->initStep();

        try {
            switch ($this->objStepPool->getSubStep()) {
                /**
                 * Show step
                 */
                case 1:
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_1']);
                    $this->objData->setState(SyncCtoEnum::WORK_WORK);

                    $this->objStepPool->increaseSubStep();
                    break;

                /**
                 * Start connection
                 */
                case 2:
                    $this->objSyncCtoCommunicationClient->startConnection();

                    $this->objStepPool->increaseSubStep();
                    // Skip Step 3.
                    $this->objStepPool->increaseSubStep();
                    break;

                /**
                 * Referer check deactivate
                 */
                case 3:
                    if (!$this->objSyncCtoCommunicationClient->referrerDisable()) {
                        $this->objData->setState(SyncCtoEnum::WORK_ERROR);
                        $this->booError = true;
                        $this->strError = $GLOBALS['TL_LANG']['ERR']['referer'];

                        break;
                    }

                    $this->objStepPool->increaseSubStep();
                    break;

                /**
                 * Clear client and server temp folder
                 */
                case 4:
                    $this->objSyncCtoCommunicationClient->purgeTempFolder();
                    $this->objSyncCtoFiles->purgeTemp();

                    // Current step is okay.
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_1']);

                    $this->objStepPool->increaseSubStep();

                    break;

                /**
                 * Load parameter from client
                 */
                case 5:
                    // Load the folder settings. Temp Folder | Backup Folder | Log Folder etc.
                    $arrFolders = $this->objSyncCtoCommunicationClient->getPathList();
                    $this->arrClientInformation["folders"] = $arrFolders;

                    // Get parameter for upload and co
                    $arrClientParameter = $this->objSyncCtoCommunicationClient->getClientParameter();

                    $this->arrClientInformation["upload_Parameter"] = $arrClientParameter;

                    // Check if everything is okay
                    if ($arrClientParameter['file_uploads'] != 1) {
                        $this->objData->setState(SyncCtoEnum::WORK_ERROR);
                        $this->booError = true;
                        $this->strError = $GLOBALS['TL_LANG']['ERR']['upload_ini'];

                        break;
                    }

                    $intClientUploadLimit = static::parseSize($arrClientParameter['upload_max_filesize']);
                    $intClientMemoryLimit = static::parseSize($arrClientParameter['memory_limit']);
                    $intClientPostLimit = static::parseSize($arrClientParameter['post_max_size']);
                    $intLocalMemoryLimit = static::parseSize(ini_get('memory_limit'));

                    // Check if memory limit on server and client is enough for upload
                    $intLimit = min($intClientUploadLimit, $intClientMemoryLimit, $intClientPostLimit, $intLocalMemoryLimit);

                    // Limit
                    if ($intLimit > 1073741824) { // 1GB
                        $intPercent = 10;
                    } else {
                        if ($intLimit > 524288000) { // 500MB
                            $intPercent = 10;
                        } else {
                            if ($intLimit > 209715200) { // 200MB
                                $intPercent = 10;
                            } else {
                                $intPercent = 80;
                            }
                        }
                    }

                    $intLimit = $intLimit / 100 * $intPercent;

                    $this->arrClientInformation["upload_sizeLimit"] = $intLimit;
                    $this->arrClientInformation["upload_sizePercent"] = $intPercent;

                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_1']['description_3']);
                    $this->objStepPool->increaseSubStep();

                    break;

                case 6:
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_1"]['description_1']);
                    $this->objData->setState(SyncCtoEnum::WORK_OK);
                    $this->intStep++;
                    break;
            }
        } catch (Exception $exc) {
//            $this->log(vsprintf("Error on synchronization client ID %s with msg: %s", [Input::get("id"), $exc->getMessage()]), __CLASS__ . " " . __FUNCTION__, "ERROR");

            $this->booError = true;
            $this->strError = $exc->getMessage();

            $this->objData->setState(SyncCtoEnum::WORK_ERROR);
        }
    }

    /**
     * Abort function
     */
    private function pageSyncAbort()
    {
        if ($this->booAbort == false) {
            // Set content back to normale mode
            $this->booError = false;
            $this->strError = "";
            $this->booAbort = true;
            $this->booRefresh = false;

            // Reset Session
            $this->resetStepPoolByID([1, 2, 3, 4, 5, 6]);

            try {
                $this->objSyncCtoCommunicationClient->stopConnection();
            } catch (Exception $exc) {
                // Nothing to do
            }

            try {
                $this->objSyncCtoCommunicationClient->referrerEnable();
            } catch (Exception $exc) {
                // Nothing to do
            }

            // Set stats
            SyncCtoStats::getInstance()->addAbortStat(time(), $this->intStep);

            // Set stepe
            $this->intStep = 99;

            // Set last to skipped
            $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
            $this->objData->setHtml("");

            // Set Abort information
            $this->objData->setStep(99);
            $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['abort']);
            $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['abort']);
            $this->objData->setState(SyncCtoEnum::WORK_ERROR);
        }
    }

    /* -------------------------------------------------------------------------
     * Start SyncCto syncTo
     */

    /**
     * Pro Version - Sync To
     */
    private function pageSyncToShowStepPro()
    {
        $objStepPro = SyncCtoStepDatabaseDiff::getInstance();
        $objStepPro->setSyncCto($this);
        $objStepPro->syncTo();
    }

    /**
     * Pro Version - Sync From
     */
    private function pageSyncFromShowStepPro()
    {
        $objStepPro = SyncCtoStepDatabaseDiff::getInstance();
        $objStepPro->setSyncCto($this);
        $objStepPro->syncFrom();
    }

    /**
     * Build checksum list and ask client
     */
    private function pageSyncToShowStep2()
    {
        $this->initStep();

        // Run page
        try {
            switch ($this->objStepPool->getSubStep()) {
                /**
                 * Show step
                 */
                case 1:
                    $this->objData->setState(SyncCtoEnum::WORK_WORK);
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1']);
                    $this->objStepPool->increaseSubStep();

                    break;

                /**
                 * Build checksum list for Contao core
                 */
                case 2:
                    if (in_array("core_change", $this->arrSyncSettings["syncCto_Type"])) {
                        $this->arrListFile['core'] = $this->objSyncCtoFiles->runChecksumCore();
                    } else {
                        $this->arrListFile['core'] = [];
                    }

                    $this->objStepPool->increaseSubStep();
                    break;

                /**
                 * Build checksum list for 'files'
                 */
                case 3:
                    if (in_array("user_change", $this->arrSyncSettings["syncCto_Type"])) {
                        $this->arrListFile['files'] = $this->objSyncCtoFiles->runChecksumFiles();
                    } else {
                        $this->arrListFile['files'] = [];
                    }

                    $this->objStepPool->increaseSubStep();
                    break;

                /**
                 * Send it to the client
                 */
                case 4:
                    if (in_array("core_change", $this->arrSyncSettings["syncCto_Type"])) {
                        $this->arrListCompare['core'] = $this->objSyncCtoCommunicationClient->runCecksumCompare($this->arrListFile['core']);
                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_2']);
                    } else {
                        $this->arrListCompare['core'] = [];
                    }

                    $this->objStepPool->increaseSubStep();
                    break;

                case 5:
                    if (in_array("user_change", $this->arrSyncSettings["syncCto_Type"])) {
                        $this->arrListCompare['files'] = (array) $this->objSyncCtoCommunicationClient->runCecksumCompare($this->arrListFile['files'], !!$this->arrSyncSettings['automode']);
                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_2']);
                    } else {
                        $this->arrListCompare['files'] = [];
                    }

                    $this->objStepPool->increaseSubStep();
                    break;

                /**
                 * Check for deleted files
                 */
                case 6:
                    if (in_array("core_delete", $this->arrSyncSettings["syncCto_Type"])) {
                        $arrChecksumClient = $this->objSyncCtoCommunicationClient->getChecksumCore();
                        $this->arrListCompare['core'] = array_merge((array) $this->arrListCompare['core'], $this->objSyncCtoFiles->checkDeleteFiles($arrChecksumClient));
                    }

                    $this->objStepPool->increaseSubStep();
                    break;

                case 7:
                    if (in_array("user_delete", $this->arrSyncSettings["syncCto_Type"])) {
                        $arrChecksumClient = $this->objSyncCtoCommunicationClient->getChecksumFiles();
                        $this->arrListCompare['files'] = array_merge((array) $this->arrListCompare['files'], $this->objSyncCtoFiles->checkDeleteFiles($arrChecksumClient));
                    }

                    $this->objStepPool->increaseSubStep();
                    break;

                /**
                 * Check folders
                 */
                case 8:
                    if (in_array("core_delete", $this->arrSyncSettings["syncCto_Type"])) {
                        $arrChecksumClient = $this->objSyncCtoCommunicationClient->getChecksumFolderCore();
                        $this->arrListCompare['core'] = array_merge((array) $this->arrListCompare['core'], $this->objSyncCtoFiles->searchDeleteFolders($arrChecksumClient));
                    }

                    $this->objStepPool->increaseSubStep();
                    break;

                case 9:
                    if (in_array("user_delete", $this->arrSyncSettings["syncCto_Type"])) {
                        $arrChecksumClient = $this->objSyncCtoCommunicationClient->getChecksumFolderFiles();
                        $this->arrListCompare['files'] = array_merge((array) $this->arrListCompare['files'], $this->objSyncCtoFiles->searchDeleteFolders($arrChecksumClient));
                    }

                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_3']);
                    $this->objStepPool->increaseSubStep();
                    break;

                /**
                 * Set CSS and search for bigfiles
                 */
                case 10:
                    foreach ($this->arrListCompare as $strType => $arrLists) {
                        foreach ($arrLists as $key => $value) {
                            switch ($value["state"]) {
                                case SyncCtoEnum::FILESTATE_BOMBASTIC_BIG:
                                    $this->arrListCompare[$strType][$key]["css"] = "unknown";
                                    $this->arrListCompare[$strType][$key]["css_big"] = "ignored";
                                    break;

                                case SyncCtoEnum::FILESTATE_TOO_BIG_NEED:
                                    $this->arrListCompare[$strType][$key]["css_big"] = "ignored";
                                case SyncCtoEnum::FILESTATE_NEED:
                                    $this->arrListCompare[$strType][$key]["css"] = "modified";
                                    break;

                                case SyncCtoEnum::FILESTATE_TOO_BIG_MISSING:
                                    $this->arrListCompare[$strType][$key]["css_big"] = "ignored";
                                case SyncCtoEnum::FILESTATE_MISSING:
                                    $this->arrListCompare[$strType][$key]["css"] = "new";
                                    break;

                                case SyncCtoEnum::FILESTATE_DELETE:
                                    $this->arrListCompare[$strType][$key]["css"] = "deleted";
                                    break;

                                case SyncCtoEnum::FILESTATE_DBAFS_CONFLICT:
                                    $this->arrListCompare[$strType][$key]["css"] = "conflict";
                                    break;

                                default:
                                    $this->arrListCompare[$strType][$key]["css"] = "unknown";
                                    break;
                            }

                            if ($value["state"] != SyncCtoEnum::FILESTATE_DBAFS_CONFLICT && (isset($value["dbafs_state"]) || isset($value["dbafs_tail_state"]))) {
                                $this->arrListCompare[$strType][$key]["css"] .= " conflict";
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
                                    $this->arrListCompare[$strType][$key]["split"] = true;
                                }
                            }
                        }
                    }

                    $this->objStepPool->increaseSubStep();
                    break;

                /**
                 * Show files form
                 */
                case 11:
                    // Counter
                    $intCountMissing = 0;
                    $intCountNeed = 0;
                    $intCountIgnored = 0;
                    $intCountDelete = 0;
                    $intCountDbafsConflict = 0;

                    $intTotalSizeNew = 0;
                    $intTotalSizeDel = 0;
                    $intTotalSizeChange = 0;

                    // Count files
                    foreach ($this->arrListCompare as $strType => $arrLists) {
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

                    $this->objStepPool->missing = $intCountMissing;
                    $this->objStepPool->need = $intCountNeed;
                    $this->objStepPool->ignored = $intCountIgnored;
                    $this->objStepPool->delete = $intCountDelete;
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
                        if (count((array) $this->arrListCompare) == 0 || array_key_exists("skip", $_POST)) {
                            $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
                            $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1']);
                            $this->objData->setHtml("");
                            $this->booRefresh = true;
                            $this->intStep++;

                            $this->arrListCompare = [];

                            break;
                        } else {
                            if (($this->arrSyncSettings["automode"] || array_key_exists("forward", $_POST)) && count((array) $this->arrListCompare) != 0) {
                                $this->objData->setState(SyncCtoEnum::WORK_OK);
                                $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_4'], [$intCountMissing, $intCountNeed, $intCountDelete, $intCountIgnored, SyncCtoContaoApi::getReadableSize($intTotalSizeNew), SyncCtoContaoApi::getReadableSize($intTotalSizeChange), SyncCtoContaoApi::getReadableSize($intTotalSizeDel)]));
                                $this->objData->setHtml("");
                                $this->booRefresh = true;
                                $this->intStep++;

                                break;
                            }
                        }
                    }

                    $objTemp = new BackendTemplate("be_syncCto_form");
                    $objTemp->id = $this->intClientID;
                    $objTemp->step = $this->intStep;
                    $objTemp->direction = "To";
                    $objTemp->headline = $GLOBALS['TL_LANG']['MSC']['totalsize'];
                    $objTemp->cssId = 'syncCto_filelist_form';
                    $objTemp->forwardValue = $GLOBALS['TL_LANG']['MSC']['apply'];
                    $objTemp->popupClassName = 'popup/files';

                    // Build content
                    $this->objData->setDescription(
                        vsprintf(
                            $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_4'],
                            [
                                $intCountMissing,
                                $intCountNeed,
                                $intCountDelete,
                                $intCountIgnored,
                                SyncCtoContaoApi::getReadableSize($intTotalSizeNew),
                                SyncCtoContaoApi::getReadableSize($intTotalSizeChange),
                                SyncCtoContaoApi::getReadableSize($intTotalSizeDel)
                            ]
                        )
                    );
                    $this->objData->setHtml($objTemp->parse());
                    $this->booRefresh = false;

                    break;
            }
        } catch (Exception $exc) {
            // If an error occurs skip the whole step
            $this->arrListCompare = [];

            $objErrTemplate = new BackendTemplate('be_syncCto_error');
            $objErrTemplate->strErrorMsg = $exc->getMessage();

            $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
            $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1']);
            $this->objData->setHtml($objErrTemplate->parse());
            $this->booRefresh = true;
            $this->intStep++;

//            $this->log(vsprintf("Error on synchronization client ID %s with msg: %s", [Input::get("id"), $exc->getMessage()]), __CLASS__ . " " . __FUNCTION__, "ERROR");
        }
    }

    /**
     * Send Files / Split Files
     */
    private function pageSyncToShowStep3()
    {
        // Init
        if ($this->objStepPool->step == null) {
            $this->objStepPool->step = 1;
        }

        // Set content back to normale mode
        $this->booError = false;
        $this->strError = "";
        $this->objData->setState(SyncCtoEnum::WORK_WORK);

        // Count files
        if (is_array($this->arrListCompare) && (count((array) $this->arrListCompare['core']) != 0 || count((array) $this->arrListCompare['files']) != 0)) {
            $intSkippCount = 0;
            $intSendCount = 0;
            $intWaitCount = 0;
            $intDelCount = 0;
            $intSplitCount = 0;

            foreach ($this->arrListCompare as $strType => $arrLists) {
                foreach ($arrLists as $key => $value) {
                    if ($value['state'] == SyncCtoEnum::FILESTATE_DBAFS_CONFLICT) {
                        continue;
                    }

                    switch ($value["transmission"]) {
                        case SyncCtoEnum::FILETRANS_SEND:
                            $intSendCount++;
                            break;

                        case SyncCtoEnum::FILETRANS_SKIPPED:
                            $intSkippCount++;
                            break;

                        case SyncCtoEnum::FILETRANS_WAITING:
                            $intWaitCount++;
                            break;
                    }

                    if ($value["state"] == SyncCtoEnum::FILESTATE_DELETE || $value["state"] == SyncCtoEnum::FILESTATE_FOLDER_DELETE) {
                        $intDelCount++;
                    }

                    if ($value["split"] == true) {
                        $intSplitCount++;
                    }
                }
            }
        }

        try {
            // Timer
            $intStart = time();

            switch ($this->objStepPool->step) {
                /**
                 * Show step
                 */
                case 1:
                    $this->objData->setState(SyncCtoEnum::WORK_WORK);
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_1']);

                    $this->objStepPool->increaseSubStep();
                    break;

                /**
                 * Send normal files
                 */
                case 2:
                    // Send all files exclude the big ones
                    $intCountTransfer = 1;

                    foreach ($this->arrListCompare as $strType => $arrLists) {
                        foreach ($arrLists as $key => $value) {
                            if ($value["transmission"] == SyncCtoEnum::FILETRANS_SEND || $value["transmission"] == SyncCtoEnum::FILETRANS_SKIPPED) {
                                continue;
                            }

                            if (in_array($value["state"], [SyncCtoEnum::FILESTATE_DELETE, SyncCtoEnum::FILESTATE_FOLDER_DELETE, SyncCtoEnum::FILESTATE_DBAFS_CONFLICT])) {
                                continue;
                            }

                            if ($value["skipped"] == true) {
                                continue;
                            }

                            if ($value["split"] == true) {
                                continue;
                            }

                            if ($value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG
                                || $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_DELETE
                                || $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_NEED
                                || $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_MISSING
                                || $value["state"] == SyncCtoEnum::FILESTATE_BOMBASTIC_BIG
                            ) {
                                $this->arrListCompare[$strType][$key]["skipreason"] = $GLOBALS['TL_LANG']['ERR']['maximum_filesize'];
                                $this->arrListCompare[$strType][$key]["transmission"] = SyncCtoEnum::FILETRANS_SKIPPED;

                                continue;
                            }

                            try {
                                // Send files
                                $this->objSyncCtoCommunicationClient->sendFile(dirname($value["path"]), str_replace(dirname($value["path"]) . "/", "", $value["path"]), $value["checksum"], SyncCtoEnum::UPLOAD_SYNC_TEMP);
                                $this->arrListCompare[$strType][$key]["transmission"] = SyncCtoEnum::FILETRANS_SEND;
                            } catch (Exception $exc) {
                                $this->arrListCompare[$strType][$key]["transmission"] = SyncCtoEnum::FILETRANS_SKIPPED;
                                $this->arrListCompare[$strType][$key]["skipreason"] = $exc->getMessage();
                            }

                            $intCountTransfer++;

                            if ($intCountTransfer == 201 || $intStart < (time() - 30)) {
                                break;
                            }
                        }
                    }

                    if ($intWaitCount - ($intDelCount + $intSplitCount + $intSkippCount) > 0) {
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_2'], [$intSendCount, (count((array) $this->arrListCompare['core']) + count((array) $this->arrListCompare['files'])) - ($intDelCount + $intSplitCount + $intSkippCount)]));
                    } else {
                        foreach ($this->arrListCompare as $strType => $arrLists) {
                            foreach ($arrLists as $key => $value) {
                                if ($value["split"] == true) {
                                    $this->objStepPool->increaseSubStep();
                                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_3']);

                                    return;
                                }
                            }
                        }

                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_1']);
                        $this->objData->setState(SyncCtoEnum::WORK_OK);
                        $this->intStep++;
                    }

                    break;

                /**
                 * Split files
                 */
                case 3:
                    $intCountSplit = 0;
                    $intCount = 0;

                    foreach ($this->arrListCompare as $strType => $arrLists) {
                        foreach ($arrLists as $key => $value) {
                            if ($value['state'] == SyncCtoEnum::FILESTATE_DBAFS_CONFLICT) {
                                continue;
                            }

                            if ($value["split"] == true) {
                                $intCountSplit++;
                            }
                        }
                    }

                    foreach ($this->arrListCompare as $strType => $arrLists) {
                        foreach ($arrLists as $key => $value) {
                            if ($value["split"] != true) {
                                continue;
                            }

                            if ($value['state'] == SyncCtoEnum::FILESTATE_DBAFS_CONFLICT) {
                                continue;
                            }

                            if ($value["split"] != 0 && $value["splitname"] != "") {
                                $intCount++;
                                continue;
                            }

                            // Splitt file
                            $intSplits = $this->objSyncCtoFiles->splitFiles($value["path"], $GLOBALS['SYC_PATH']['tmp'] . $key, $key, ($this->arrClientInformation["upload_sizeLimit"] / 100 * $this->arrClientInformation["upload_sizePercent"]));

                            $this->arrListCompare[$strType][$key]["splitcount"] = $intSplits;
                            $this->arrListCompare[$strType][$key]["splitname"] = $key;

                            break;
                        }
                    }

                    if ($intCount != $intCountSplit) {
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_6'], [$intCount, $intCountSplit]));
                    } else {
                        $this->objStepPool->increaseSubStep();
                        $this->objData->setState(SyncCtoEnum::WORK_WORK);
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_4'], [$intCount, $intCountSplit]));
                    }

                    break;

                /**
                 * Send bigfiles
                 */
                case 4:
                    $intCountSplit = 0;
                    $intCount = 0;

                    foreach ($this->arrListCompare as $strType => $arrLists) {
                        foreach ($arrLists as $key => $value) {
                            if ($value['state'] == SyncCtoEnum::FILESTATE_DBAFS_CONFLICT) {
                                continue;
                            }

                            if ($value["split"] == true) {
                                $intCountSplit++;
                            }
                        }
                    }

                    foreach ($this->arrListCompare as $strType => $arrLists) {
                        foreach ($arrLists as $key => $value) {
                            if ($value["split"] != true) {
                                continue;
                            }

                            if (in_array(
                                $value["state"],
                                [
                                    SyncCtoEnum::FILESTATE_TOO_BIG_DELETE,
                                    SyncCtoEnum::FILESTATE_TOO_BIG_MISSING,
                                    SyncCtoEnum::FILESTATE_TOO_BIG_NEED,
                                    SyncCtoEnum::FILESTATE_TOO_BIG_SAME,
                                    SyncCtoEnum::FILESTATE_BOMBASTIC_BIG,
                                    SyncCtoEnum::FILESTATE_DELETE,
                                    SyncCtoEnum::FILESTATE_FOLDER_DELETE,
                                    SyncCtoEnum::FILESTATE_DBAFS_CONFLICT,
                                ]
                            )
                            ) {
                                continue;
                            }

                            if (!empty($value["split_transfer"]) && $value["splitcount"] == $value["split_transfer"]) {
                                $intCount++;
                                continue;
                            }

                            if (empty($value["split_transfer"])) {
                                $value["split_transfer"] = 0;
                            }

                            for ($ii = $value["split_transfer"]; $ii < $value["splitcount"]; $ii++) {
                                // Max limit for file send, 10 minutes
                                set_time_limit(7200);

                                // Send file to client
                                $arrResponse = $this->objSyncCtoCommunicationClient->sendFile($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], $key), $value["splitname"] . ".sync" . $ii, "", SyncCtoEnum::UPLOAD_SYNC_SPLIT, $value["splitname"]);

                                $this->arrListCompare[$strType][$key]["split_transfer"] = $ii + 1;

                                // check time limit 30 secs
                                if ($intStart + 30 < time()) {
                                    break;
                                }
                            }

                            break;
                        }
                    }

                    if ($intCount != $intCountSplit) {
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_7'], [$intCount, $intCountSplit]));
                    } else {
                        $this->objStepPool->increaseSubStep();
                        $this->objData->setState(SyncCtoEnum::WORK_WORK);
                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_5']);
                    }

                    break;

                /**
                 * Rebuild split files
                 */
                case 5:
                    $intCountSplit = 0;
                    $intCount = 0;

                    foreach ($this->arrListCompare as $strType => $arrLists) {
                        foreach ($arrLists as $key => $value) {
                            if ($value['state'] == SyncCtoEnum::FILESTATE_DBAFS_CONFLICT) {
                                continue;
                            }

                            if ($value["split"] == true) {
                                $intCountSplit++;
                            }
                        }
                    }

                    foreach ($this->arrListCompare as $strType => $arrLists) {
                        foreach ($arrLists as $key => $value) {
                            if ($value["split"] != true) {
                                continue;
                            }

                            if (in_array(
                                $value["state"], [
                                                   SyncCtoEnum::FILESTATE_TOO_BIG_DELETE,
                                                   SyncCtoEnum::FILESTATE_TOO_BIG_MISSING,
                                                   SyncCtoEnum::FILESTATE_TOO_BIG_NEED,
                                                   SyncCtoEnum::FILESTATE_TOO_BIG_SAME,
                                                   SyncCtoEnum::FILESTATE_BOMBASTIC_BIG,
                                                   SyncCtoEnum::FILESTATE_DELETE,
                                                   SyncCtoEnum::FILESTATE_FOLDER_DELETE,
                                                   SyncCtoEnum::FILESTATE_DBAFS_CONFLICT,
                                               ]
                            )
                            ) {
                                continue;
                            }

                            if ($value["transmission"] == SyncCtoEnum::FILETRANS_SEND) {
                                $intCount++;
                                continue;
                            }

                            if (!$this->objSyncCtoCommunicationClient->buildSingleFile($value["splitname"], $value["splitcount"], $value["path"], $value["checksum"])) {
                                throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['rebuild'], [$value["path"]]));
                            }

                            $this->arrListCompare[$strType][$key]["transmission"] = SyncCtoEnum::FILETRANS_SEND;

                            break;
                        }
                    }

                    if ($intCount != $intCountSplit) {
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_8'], [$intCount, $intCountSplit]));
                    } else {
                        $this->objData->setState(SyncCtoEnum::WORK_OK);
                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_1']);

                        $this->intStep++;
                    }

                    break;
            }
        } catch (Exception $exc) {
            // If an error occurs skip the whole step
            $objErrTemplate = new BackendTemplate('be_syncCto_error');
            $objErrTemplate->strErrorMsg = $exc->getMessage();

            $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
            $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_1']);
            $this->objData->setHtml($objErrTemplate->parse());
            $this->booRefresh = true;
            $this->intStep++;

//            $this->log(vsprintf("Error on synchronization client ID %s with msg: %s", [Input::get("id"), $exc->getMessage()]), __CLASS__ . " " . __FUNCTION__, "ERROR");
        }
    }

    /**
     * Build SQL zip and send it to the client
     */
    private function pageSyncToShowStep4()
    {
        $this->initStep();

        try {
            switch ($this->objStepPool->step) {
                /**
                 * Init
                 */
                case 1:
                    $this->objData->setState(SyncCtoEnum::WORK_WORK);
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_1']);
                    $this->objStepPool->increaseSubStep();
                    break;

                case 2:
                    $isAdmin = BackendUser::getInstance()->isAdmin;
                    $userTables = BackendUser::getInstance()->syncCto_tables;

                    if ($isAdmin || !empty($userTables)) {
                        // Load allowed tables for this user
                        if (BackendUser::getInstance()->isAdmin) {
                            $arrAllowedTables = true;
                        } else {
                            $arrAllowedTables = BackendUser::getInstance()->syncCto_tables;
                        }

                        $arrClientTableR = $this->objSyncCtoCommunicationClient->getRecommendedTables();
                        $arrClientTableNR = $this->objSyncCtoCommunicationClient->getNoneRecommendedTables();
                        $arrClientTableH = $this->objSyncCtoCommunicationClient->getHiddenTables();
                        $arrClientTableHP = $this->objSyncCtoCommunicationClient->getPreparedHiddenTablesPlaceholder();
                        $arrClientTimestamp = $this->objSyncCtoCommunicationClient->getClientTimestamp([]);

                        $arrServerTableR = $this->objSyncCtoHelper->databaseTablesRecommended();
                        $arrServerTableNR = $this->objSyncCtoHelper->databaseTablesNoneRecommended();
                        $arrServerTableH = $this->objSyncCtoHelper->getTablesHidden();
                        $arrServerTableHP = $this->objSyncCtoHelper->getPreparedHiddenTablesPlaceholder();
                        $arrServerTimestamp = $this->objSyncCtoHelper->getDatabaseTablesTimestamp();

                        // clean up tables. Use user rights.
                        if ($arrAllowedTables !== true) {
                            foreach ($arrClientTableR as $key => $value) {
                                if (!in_array($value['name'], $arrAllowedTables)) {
                                    unset($arrClientTableR[$key]);
                                }
                            }

                            foreach ($arrClientTableNR as $key => $value) {
                                if (!in_array($value['name'], $arrAllowedTables)) {
                                    unset($arrClientTableNR[$key]);
                                }
                            }

                            foreach ($arrClientTableH as $key => $value) {
                                if (!in_array($value, $arrAllowedTables)) {
                                    unset($arrClientTableH[$key]);
                                }
                            }

                            foreach ($arrServerTableR as $key => $value) {
                                if (!in_array($value['name'], $arrAllowedTables)) {
                                    unset($arrServerTableR[$key]);
                                }
                            }

                            foreach ($arrServerTableNR as $key => $value) {
                                if (!in_array($value['name'], $arrAllowedTables)) {
                                    unset($arrServerTableNR[$key]);
                                }
                            }

                            foreach ($arrServerTableH as $key => $value) {
                                if (!in_array($value, $arrAllowedTables)) {
                                    unset($arrServerTableH[$key]);
                                }
                            }
                        }

                        // Merge all together
                        foreach ($arrServerTableR as $key => $value) {
                            $arrServerTableR[$key]['type'] = 'recommended';
                        }

                        foreach ($arrClientTableR as $key => $value) {
                            $arrClientTableR[$key]['type'] = 'recommended';
                        }

                        foreach ($arrServerTableNR as $key => $value) {
                            $arrServerTableNR[$key]['type'] = 'nonRecommended';
                        }

                        foreach ($arrClientTableNR as $key => $value) {
                            $arrClientTableNR[$key]['type'] = 'nonRecommended';
                        }

                        $arrServerTables = array_merge($arrServerTableR, $arrServerTableNR);
                        $arrClientTables = array_merge($arrClientTableR, $arrClientTableNR);
                        $arrHiddenTables = array_keys(array_flip(array_merge($arrServerTableH, $arrClientTableH)));
                        $arrHiddenTablesPlaceholder = array_keys(array_flip(array_merge($arrClientTableHP, $arrServerTableHP)));
                        $arrAllTimeStamps = $this->objSyncCtoDatabase->getAllTimeStamps(
                            $arrServerTimestamp,
                            $arrClientTimestamp,
                            $this->intClientID
                        );

                        $arrCompareList = \MenAtWork\SyncCto\Database\Diff::getFormatedCompareList(
                            $arrServerTables,
                            $arrClientTables,
                            $arrHiddenTables,
                            $arrHiddenTablesPlaceholder,
                            $arrAllTimeStamps['server'],
                            $arrAllTimeStamps['client'],
                            $arrAllowedTables,
                            'server',
                            'client'
                        );

                        // Get the tables count.
                        $countRecommended = count((array) $arrCompareList['recommended']);
                        $countNoneRecommended = count((array) $arrCompareList['none_recommended']);

                        // Check the next step.
                        if ($countRecommended == 0 && $countNoneRecommended == 0) {
                            if ($this->arrSyncSettings['syncCto_SyncTlFiles'] || $this->arrSyncSettings['automode']) {
                                $this->arrSyncSettings['syncCto_SyncTables']['tl_files'] = 'tl_files';
                                $this->objStepPool->step = ($this->objStepPool->step + 1);

                                break;
                            }

                            $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
                            $this->objData->setHtml("");
                            $this->intStep++;

                            break;
                        }

                        $this->arrSyncSettings['syncCto_CompareTables'] = $arrCompareList;

                        // If automode set all tabels as transferd.
                        if ($this->arrSyncSettings['automode']) {
                            $this->arrSyncSettings['syncCto_SyncDeleteTables'] = [];
                            $this->arrSyncSettings['syncCto_SyncTables'] = [];

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
                        }

                        // Set the tl_files if we have the automode or the checkbox is activate.
                        if ($this->arrSyncSettings['automode'] || $this->arrSyncSettings['syncCto_SyncTlFiles']) {
                            $this->arrSyncSettings['syncCto_SyncTables']['tl_files'] = 'tl_files';
                        }

                        $this->objStepPool->increaseSubStep();
                    } else {
                        if (empty($userTables) && ($this->arrSyncSettings['syncCto_SyncTlFiles'] || $this->arrSyncSettings['automode'])) {
                            $this->arrSyncSettings['syncCto_SyncTables']['tl_files'] = 'tl_files';
                            $this->objStepPool->step = ($this->objStepPool->step + 2);

                            break;
                        } else {
                            $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
                            $this->objData->setHtml("");
                            $this->intStep++;
                        }
                    }

                    break;

                case 3:
                    // Unset some tables for pro feature
                    if (!$this->arrSyncSettings["automode"]
                        && array_key_exists('SyncCtoProBundle', System::getContainer()->getParameter('kernel.bundles'))
                        && array_key_exists('forward', $_POST)
                        && $this->arrSyncSettings['post_data']['database_pages_check'] == true) {
                        if (($mixKey = array_search('tl_page', $this->arrSyncSettings['syncCto_SyncTables'])) !== false) {
                            unset($this->arrSyncSettings['syncCto_SyncTables'][$mixKey]);
                            $this->arrSyncSettings['syncCtoPro_tables_checked'][] = 'tl_page';
                        }

                        if (($mixKey = array_search('tl_article', $this->arrSyncSettings['syncCto_SyncTables'])) !== false) {
                            unset($this->arrSyncSettings['syncCto_SyncTables'][$mixKey]);
                            $this->arrSyncSettings['syncCtoPro_tables_checked'][] = 'tl_article';
                        }

                        if (($mixKey = array_search('tl_content', $this->arrSyncSettings['syncCto_SyncTables'])) !== false) {
                            unset($this->arrSyncSettings['syncCto_SyncTables'][$mixKey]);
                            $this->arrSyncSettings['syncCtoPro_tables_checked'][] = 'tl_content';
                        }
                    }

                    // Check the post vars.
                    if (array_key_exists("forward", $_POST) || $this->arrSyncSettings["automode"]) {
                        // Add the tl_files table to the list.
                        if ($this->arrSyncSettings['syncCto_SyncTlFiles'] || $this->arrSyncSettings["automode"]) {
                            $this->arrSyncSettings['syncCto_SyncTables']['tl_files'] = 'tl_files';
                        }

                        // Count the tables.
                        $countTables = count((array) $this->arrSyncSettings['syncCto_SyncTables']);
                        $countDeleteTables = count((array) $this->arrSyncSettings['syncCto_SyncDeleteTables']);

                        // Check if we have some tables.
                        if (!($countTables == 0 && $countDeleteTables == 0)) {
                            // Go to next step
                            $this->objData->setState(SyncCtoEnum::WORK_WORK);
                            $this->objData->setHtml("");
                            $this->booRefresh = true;
                            $this->objStepPool->increaseSubStep();

                            break;
                        } else {
                            // Skip if no tables are selected
                            $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
                            $this->objData->setHtml("");
                            $this->booRefresh = true;
                            $this->intStep++;

                            break;
                        }
                    } else {
                        if (array_key_exists("skip", $_POST)) {
                            $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
                            $this->objData->setHtml("");
                            $this->booRefresh = true;
                            $this->intStep++;

                            break;
                        }
                    }

                    $objTemp = new BackendTemplate("be_syncCto_form");
                    $objTemp->id = $this->intClientID;
                    $objTemp->step = $this->intStep;
                    $objTemp->direction = "To";
                    $objTemp->headline = $GLOBALS['TL_LANG']['MSC']['totalsize'];
                    $objTemp->cssId = 'syncCto_database_form';
                    $objTemp->forwardValue = $GLOBALS['TL_LANG']['MSC']['apply'];
                    $objTemp->popupClassName = 'popup/database';
                    $objTemp->requestToken = System::getContainer()
                                                   ->get('contao.csrf.token_manager')
                                                   ->getDefaultTokenValue()
                    ;

                    // Build content
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_1']);
                    $this->objData->setHtml($objTemp->parse());
                    $this->booRefresh = false;

                    break;

                /**
                 * Build SQL Zip File
                 */
                case 4:
                    if (count((array) $this->arrSyncSettings['syncCto_SyncTables']) != 0) {
                        $this->objStepPool->zipname = $this->objSyncCtoDatabase->runDump($this->arrSyncSettings['syncCto_SyncTables'], true, true);

                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_2']);
                        $this->objStepPool->increaseSubStep();
                    } else {
                        $this->objStepPool->step = 8;
                    }

                    break;

                /**
                 * Send file to client
                 */
                case 5:

                    $arrResponse = $this->objSyncCtoCommunicationClient->sendFile($GLOBALS['SYC_PATH']['tmp'], $this->objStepPool->zipname, "", SyncCtoEnum::UPLOAD_SQL_TEMP);

                    // Check if the file was send and saved.
                    if (!is_array($arrResponse) || count((array) $arrResponse) == 0) {
                        throw new Exception("Empty file list from client. Maybe file send was not complet.");
                    }

                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_3']);
                    $this->objStepPool->increaseSubStep();

                    break;

                /**
                 * Import on client side
                 */
                case 6:

                    $arrSQL = [];

                    if (isset($GLOBALS['TL_HOOKS']['syncDBUpdateBeforeDrop']) && is_array($GLOBALS['TL_HOOKS']['syncDBUpdateBeforeDrop'])) {
                        foreach ($GLOBALS['TL_HOOKS']['syncDBUpdateBeforeDrop'] as $callback) {
                            if (!class_exists($callback[0])) {
                                continue;
                            }

                            if (method_exists($callback[0], 'getInstance')) {
                                $objCallbackClass = $callback[0]::getInstance();
                            } else {
                                $objCallbackClass = new $callback[0]();
                            }

                            $mixReturn = $objCallbackClass->{$callback[1]}($this->intClientID, $this->arrSyncSettings['syncCto_SyncTables'], $arrSQL);

                            if (!empty($mixReturn) && is_array($mixReturn)) {
                                $arrSQL = $mixReturn;
                            }
                        }
                    }

                    // Import SQL zip
                    $this->objSyncCtoCommunicationClient->runSQLImport($this->objSyncCtoHelper->standardizePath($this->arrClientInformation["folders"]["tmp"], "sql", $this->objStepPool->zipname), $arrSQL);

                    $this->objStepPool->increaseSubStep();

                    break;

                /**
                 * Drop Tables
                 */
                case 7:
                    if (count((array) $this->arrSyncSettings['syncCto_SyncDeleteTables']) != 0) {
                        $arrKnownTables = Database::getInstance()->listTables();

                        foreach ($this->arrSyncSettings['syncCto_SyncDeleteTables'] as $key => $value) {
                            if (in_array($value, $arrKnownTables)) {
                                unset($this->arrSyncSettings['syncCto_SyncDeleteTables'][$key]);
                            }
                        }

                        $this->objSyncCtoCommunicationClient->dropTable($this->arrSyncSettings['syncCto_SyncDeleteTables'], true);

                        // Show step information
                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_4']);
                        $this->objStepPool->increaseSubStep();

                        break;
                    }

                /**
                 * Hook for custom sql code
                 */
                case 8:
                    if (isset($GLOBALS['TL_HOOKS']['syncDBUpdate']) && is_array($GLOBALS['TL_HOOKS']['syncDBUpdate'])) {
                        $arrSQL = [];

                        foreach ($GLOBALS['TL_HOOKS']['syncDBUpdate'] as $callback) {
                            if (!class_exists($callback[0])) {
                                continue;
                            }

                            if (method_exists($callback[0], 'getInstance')) {
                                $objCallbackClass = $callback[0]::getInstance();
                            } else {
                                $objCallbackClass = new $callback[0]();
                            }

                            $mixReturn = $objCallbackClass->{$callback[1]}($this->intClientID, $arrSQL);

                            if (!empty($mixReturn) && is_array($mixReturn)) {
                                $arrSQL = $mixReturn;
                            }
                        }

                        if (count((array) $arrSQL) != 0) {
                            $this->objSyncCtoCommunicationClient->executeSQL($arrSQL);
                        }
                    }

                    $this->objStepPool->increaseSubStep();

                    break;

                /**
                 * Set timestamps
                 */
                case 9:
                    $arrTableTimestamp = [
                        'server' => $this->objSyncCtoHelper->getDatabaseTablesTimestamp($this->arrSyncSettings['syncCto_SyncTables']),
                        'client' => $this->objSyncCtoCommunicationClient->getClientTimestamp($this->arrSyncSettings['syncCto_SyncTables']),
                    ];

                    foreach ($arrTableTimestamp as $location => $arrTimeStamps) {
                        // Update timestamp
                        $mixLastTableTimestamp = Database::getInstance()
                                                         ->prepare("SELECT " . $location . "_timestamp FROM tl_synccto_clients WHERE id=?")
                                                         ->limit(1)
                                                         ->execute($this->intClientID)
                                                         ->fetchAllAssoc()
                        ;

                        if (strlen($mixLastTableTimestamp[0][$location . "_timestamp"]) != 0) {
                            $arrLastTableTimestamp = unserialize($mixLastTableTimestamp[0][$location . "_timestamp"]);
                        } else {
                            $arrLastTableTimestamp = [];
                        }

                        foreach ($arrTimeStamps as $key => $value) {
                            $arrLastTableTimestamp[$key] = $value;
                        }

                        // Search for old entries
                        $arrTables = Database::getInstance()->listTables();
                        foreach ($arrLastTableTimestamp as $key => $value) {
                            if (!in_array($key, $arrTables)) {
                                unset($arrLastTableTimestamp[$key]);
                            }
                        }

                        Database::getInstance()
                                ->prepare("UPDATE tl_synccto_clients SET " . $location . "_timestamp = ? WHERE id = ? ")
                                ->execute(serialize($arrLastTableTimestamp), $this->intClientID)
                        ;
                    }

                    // Show step information
                    $this->objData->setState(SyncCtoEnum::WORK_OK);
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_4']);

                    $this->intStep++;

                    break;

            }
        } catch (Exception $exc) {
            // If an error occurs skip the whole step
            $this->arrSyncSettings['syncCto_SyncDeleteTables'] = [];
            $this->arrSyncSettings['syncCto_CompareTables'] = [];

            $objErrTemplate = new BackendTemplate('be_syncCto_error');
            $objErrTemplate->strErrorMsg = $exc->getMessage();

            $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
            $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_4"]['description_1']);
            $this->objData->setHtml($objErrTemplate->parse());
            $this->booRefresh = true;
            $this->intStep++;

//            $this->log(vsprintf("Error on synchronization client ID %s with msg: %s", [Input::get("id"), $exc->getMessage()]), __CLASS__ . " " . __FUNCTION__, "ERROR");
        }
    }

    /**
     * Last Steps for all functions
     */
    private function pageSyncToShowStep6()
    {
        /* ---------------------------------------------------------------------
         * Init
         */

        if ($this->objStepPool->step == null) {
            $this->objStepPool->step = 1;
        }

        // Set content back to normale mode
        $this->booError = false;
        $this->strError = "";
        $this->objData->setState(SyncCtoEnum::WORK_WORK);

        // Get the file list.
        $fileList = new Base($this->arrListCompare);

        /* ---------------------------------------------------------------------
         * Run page
         */

        try {
            switch ($this->objStepPool->step) {
                /**
                 * Init
                 */
                case 1:
                    $this->objData->setState(SyncCtoEnum::WORK_WORK);
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_2']);
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
                    $this->objStepPool->increaseSubStep();
                    break;

                case 2:
                    $this->objSyncCtoCommunicationClient->purgeCache();
                    $this->objStepPool->increaseSubStep();
                    break;

                /**
                 * Import files
                 */
                case 3:
                    // Reset the msg.
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_2']);
                    $this->setErrorMsg('');

                    try {
                        // Get the file list.
                        $itCore = $fileList->getTransferCore(true, false);
                        $itPrivate = $fileList->getTransferPrivate(true, false);
                        $itDbafs = $fileList->getDbafs(true, false);
                        $itOverall = $fileList->getTransferFiles(true, true);

                        // Count some values.
                        $waitingFiles = iterator_count($itCore)
                                        + iterator_count($itPrivate)
                                        + iterator_count($itDbafs);
                        $overallFiles = iterator_count($itOverall);

                        // Add the status.
                        $this->objData->setDescription
                        (
                            sprintf
                            (
                                $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_2'],
                                ($overallFiles - $waitingFiles),
                                $overallFiles
                            )
                        );

                        // Check if we have some files.
                        if ($waitingFiles == 0) {
                            $this->objData->setHtml('');
                            $this->objStepPool->increaseSubStep();
                            break;
                        }

                        // Check for endless run.
                        if ($waitingFiles == $this->arrSyncSettings['last_transfer']) {
                            $this->objData->setHtml('');
                            $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_1']);
                            $this->setError(true);
                            $this->setErrorMsg('Error on moving files. Some files could not be moved.');
                            break;
                        }

                        // Add the current count to the config.
                        $this->arrSyncSettings['last_transfer'] = $waitingFiles;

                        // Run core if we have files.
                        if (iterator_count($itCore) != 0) {
                            $arrTransmission = $this
                                ->objSyncCtoCommunicationClient
                                ->runFileImport(iterator_to_array($itCore), false)
                            ;

                            foreach ($arrTransmission as $key => $value) {
                                $this->arrListCompare['core'][$key] = $value;
                            }
                        } elseif (iterator_count($itPrivate) != 0) { // Run private if we have files.
                            // Get only 100 files.
                            $itSupSet = new LimitIterator($itPrivate, 0, 100);
                            $itSupSet = iterator_to_array($itSupSet);

                            // Get the dbafs information.
                            foreach ($itSupSet as $key => $value) {
                                // Get the information from the tl_files.
                                $objModel = FilesModel::findByPath($value['path']);

                                // Okay we have the file ...
                                if ($objModel != null) {
                                    $arrModelData = $objModel->row();

                                    // PHP 7 compatibility
                                    // See #309 (https://github.com/contao/core-bundle/issues/309)
                                    $arrModelData['pid'] = (strlen($arrModelData['pid'])) ? StringUtil::binToUuid($arrModelData['pid']) : $arrModelData['pid'];
                                    $arrModelData['uuid'] = StringUtil::binToUuid($arrModelData['uuid']);
                                } // if not add it to the current DBAFS.
                                else {
                                    $objModel = \Dbafs::addResource($value['path']);
                                    if ($objModel !== null) {
                                        $arrModelData = $objModel->row();

                                        // PHP 7 compatibility
                                        // See #309 (https://github.com/contao/core-bundle/issues/309)
                                        $arrModelData['pid'] = (strlen($arrModelData['pid'])) ? StringUtil::binToUuid($arrModelData['pid']) : $arrModelData['pid'];
                                        $arrModelData['uuid'] = StringUtil::binToUuid($arrModelData['uuid']);

                                        $itSupSet[$key]['tl_files'] = $arrModelData;
                                    } else {
                                        $itSupSet[$key]['tl_files'] = [];
                                    }
                                }
                            }

                            // Send the data to the client.
                            $arrTransmission = $this
                                ->objSyncCtoCommunicationClient
                                ->runFileImport($itSupSet, true)
                            ;

                            // Add the information to the current list.
                            foreach ($arrTransmission as $key => $value) {
                                $this->arrListCompare['files'][$key] = $value;
                            }
                        } elseif (iterator_count($itDbafs) != 0) { // Run private if we have files.
                            // Get only 100 files.
                            $itSupSet = new LimitIterator($itDbafs, 0, 100);

                            // Send it to the client.
                            $arrTransmission = $this
                                ->objSyncCtoCommunicationClient
                                ->updateDbafs(iterator_to_array($itSupSet))
                            ;

                            // Update the current list.
                            foreach ($arrTransmission as $key => $value) {
                                // Set the state.
                                if ($value['saved']) {
                                    $value["transmission"] = SyncCtoEnum::FILETRANS_SEND;
                                } else {
                                    $value["transmission"] = SyncCtoEnum::FILETRANS_SKIPPED;
                                }

                                $this->arrListCompare['files'][$key] = $value;
                            }
                        }
                    } catch (Exception $e) {
                        $this->objData->setHtml('');
                        $this->objData->setDescription($e->getMessage());
                        $this->setError(true);
                        $this->setErrorMsg('Error on moving files. Some files could not be moved.');
                    }
                    break;

                /**
                 * Delete Files
                 */
                case 4:
                    // Reset the msg.
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_2']);
                    $this->setErrorMsg('');

                    try {
                        // Get the file list.
                        $itCore = $fileList->getDeletedCore(true);
                        $itPrivate = $fileList->getDeletedPrivate(true);
                        $itOverall = $fileList->getDeletedFiles(false);

                        // Count some values.
                        $waitingFiles = iterator_count($itCore) + iterator_count($itPrivate);
                        $overallFiles = iterator_count($itOverall);

                        // Add the status.
                        $this->objData->setDescription
                        (
                            sprintf
                            (
                                $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_2'],
                                ($overallFiles - $waitingFiles),
                                $overallFiles
                            )
                        );

                        // Check if we have some files.
                        if ($waitingFiles == 0) {
                            $this->objData->setHtml('');
                            $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_1']);
                            $this->objStepPool->increaseSubStep();
                            break;
                        }

                        // Check for endless run.
                        if ($waitingFiles == $this->arrSyncSettings['last_delete']) {
                            $this->objData->setHtml('');
                            $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_1']);
                            $this->setError(true);
                            $this->setErrorMsg('Error on deleting files. Some files could not be deleted.');
                            break;
                        }

                        // Add the current count to the config.
                        $this->arrSyncSettings['last_delete'] = $waitingFiles;

                        // Run core if we have files.
                        if (iterator_count($itCore) != 0) {
                            // Get only 100 files.
                            $itSupSet = new LimitIterator($itCore, 0, 100);

                            // Send them to the client.
                            $arrTransmission = $this
                                ->objSyncCtoCommunicationClient
                                ->deleteFiles(iterator_to_array($itSupSet), false)
                            ;

                            // Add all information to the file list.
                            foreach ($arrTransmission as $key => $value) {
                                $this->arrListCompare['core'][$key] = $value;
                            }
                        } // Run private if we have files.
                        else {
                            if (iterator_count($itPrivate) != 0) {
                                // Get only 100 files.
                                $itSupSet = new LimitIterator($itPrivate, 0, 100);

                                // Send them to the client.
                                $arrTransmission = $this
                                    ->objSyncCtoCommunicationClient
                                    ->deleteFiles(iterator_to_array($itSupSet), false)
                                ;

                                // Add all information to the file list.
                                foreach ($arrTransmission as $key => $value) {
                                    $this->arrListCompare['files'][$key] = $value;
                                }
                            }
                        }
                    } catch (Exception $e) {
                        // If there was an error just go on. The endless protection will
                        // handle any problem.
                    }
                    break;

                case 5:
                    $this->objSyncCtoCommunicationClient->createCache();
                    $this->objStepPool->increaseSubStep();
                    break;

                /**
                 * Import Config
                 */
                case 6:
                    if ($this->arrSyncSettings["syncCto_Type"] == 'all' || in_array("localconfig_update", $this->arrSyncSettings["syncCto_Type"])) {
                        $this->objSyncCtoCommunicationClient->runLocalConfigImport();
                        $this->objStepPool->increaseSubStep();
                        break;
                    }

                    $this->objStepPool->increaseSubStep();

                /**
                 * Import Config / Set show error
                 */
                case 7:
                    $this->objSyncCtoCommunicationClient->setDisplayErrors($this->arrSyncSettings["syncCto_ShowError"]);
                    $this->objStepPool->increaseSubStep();
                    break;

                /**
                 * Import Config / Set referrer check
                 */
                case 8:
                    if (is_array($this->arrSyncSettings["syncCto_Systemoperations_Maintenance"]) && count((array) $this->arrSyncSettings["syncCto_Systemoperations_Maintenance"]) != 0) {
                        $this->objSyncCtoCommunicationClient->runMaintenance($this->arrSyncSettings["syncCto_Systemoperations_Maintenance"]);
                    }

                    $this->objStepPool->increaseSubStep();
                    break;

                case 9:
                    if ($this->arrSyncSettings["syncCto_AttentionFlag"] == true) {
                        $this->objSyncCtoCommunicationClient->setAttentionFlag(false);
                    }

                    $this->log(vsprintf("Successfully finishing of synchronization client ID %s.", [Input::get("id")]), __CLASS__ . " " . __FUNCTION__, "INFO");

                /**
                 * Cleanup
                 */
                case 10:
                    if (in_array("temp_folders", $this->arrSyncSettings["syncCto_Systemoperations_Maintenance"])) {
                        $this->objSyncCtoCommunicationClient->purgeTempFolder();
                        $this->objSyncCtoFiles->purgeTemp();
                    }

                    $this->objStepPool->increaseSubStep();
                    $this->objData->setState(SyncCtoEnum::WORK_OK);
                    $this->objData->setHtml("");
                    $this->booRefresh = true;
                    $this->intStep++;

                    break;

                default:
                    $this->objData->setState(SyncCtoEnum::WORK_OK);
                    $this->objData->setHtml("");
                    $this->booRefresh = true;
                    $this->intStep++;
                    break;
            }
        } catch (Exception $exc) {
            $this->objStepPool->increaseSubStep();

            $this->log(vsprintf("Error on synchronization client ID %s with msg: %s", [Input::get("id"), $exc->getMessage()]), __CLASS__ . " " . __FUNCTION__, "ERROR");
        }
    }

    /**
     * Last Step
     */
    private function pageSyncToShowStep7()
    {
        /* ---------------------------------------------------------------------
         * Init
         */

        if ($this->objStepPool->step == null) {
            $this->objStepPool->step = 1;
        }

        // Set content back to normale mode
        $this->booError = false;
        $this->strError = "";
        $this->objData->setState(SyncCtoEnum::WORK_WORK);

        /* ---------------------------------------------------------------------
         * Run page
         */

        try {
            switch ($this->objStepPool->step) {
                /**
                 * Init
                 */
                case 1:
                    $this->objData->setState(SyncCtoEnum::WORK_WORK);
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_1']['description_2']);
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
                    $this->objStepPool->increaseSubStep();
                    break;

                /**
                 * Call the final operations hook for client
                 */
                case 2:
                    $arrResponse = $this->objSyncCtoCommunicationClient->runFinalOperations();
                    $this->objStepPool->increaseSubStep();
                    break;

                /**
                 * Call some functions on the server.
                 */
                case 3:
                    $mixCurrentAdditionalStep = $this->objStepPool->additionalStep;

                    if (empty($mixCurrentAdditionalStep)) {
                        $mixCurrentAdditionalStep = 0;
                    }

                    // HOOK: do some last operations
                    if (isset($GLOBALS['TL_HOOKS']['syncAdditionalFunctions']) && is_array($GLOBALS['TL_HOOKS']['syncAdditionalFunctions'])) {
                        $arrKeys = array_keys($GLOBALS['TL_HOOKS']['syncAdditionalFunctions']);

                        if (($mixCurrentAdditionalStep + 1) > count((array) $arrKeys)) {
                            $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_1']['description_2']);
                            $this->objStepPool->increaseSubStep();
                            break;
                        }

                        $mixCurrentKey = $arrKeys[$mixCurrentAdditionalStep];
                        $arrCurrentFunction = $GLOBALS['TL_HOOKS']['syncAdditionalFunctions'][$mixCurrentKey];

                        try {
                            if (method_exists($arrCurrentFunction[0], 'getInstance')) {
                                $objCallbackClass = $arrCurrentFunction[0]::getInstance();
                            } else {
                                $objCallbackClass = new $arrCurrentFunction[0]();
                            }

                            $objCallbackClass->{$arrCurrentFunction[1]}($this, $this->intClientID);
                        } catch (Exception $exc) {
                            $this->log("Error by: TL_HOOK $arrCurrentFunction[0] | $arrCurrentFunction[1] with Msg: " . $exc->getMessage(), __CLASS__ . "|" . __FUNCTION__, TL_ERROR);
                        }

                        $this->objStepPool->additionalStep = $mixCurrentAdditionalStep + 1;
                    } else {
                        $this->objStepPool->increaseSubStep();
                    }

                    break;

                case 4:
                    $this->objSyncCtoCommunicationClient->referrerEnable();
                    $this->objStepPool->increaseSubStep();
                    break;

                case 5:
                    $this->objSyncCtoCommunicationClient->stopConnection();
                    SyncCtoStats::getInstance()->addEndStat(time());
                    $this->objStepPool->increaseSubStep();
                    break;

                /**
                 * Show information
                 */
                case 6:
                    // Count files
                    if (is_array($this->arrListCompare) && (count((array) $this->arrListCompare['core']) != 0 || count((array) $this->arrListCompare['files']) != 0)) {
                        $intSkippCount = 0;
                        $intSendCount = 0;
                        $intWaitCount = 0;
                        $intDelCount = 0;
                        $intSplitCount = 0;

                        foreach ($this->arrListCompare as $strType => $arrLists) {
                            foreach ($arrLists as $key => $value) {
                                switch ($value["transmission"]) {
                                    case SyncCtoEnum::FILETRANS_SEND:
                                    case SyncCtoEnum::FILETRANS_MOVED:
                                        $intSendCount++;
                                        break;

                                    case SyncCtoEnum::FILETRANS_SKIPPED:
                                        $intSkippCount++;
                                        break;

                                    case SyncCtoEnum::FILETRANS_WAITING:
                                        $intWaitCount++;
                                        break;
                                }

                                if ($value["state"] == SyncCtoEnum::FILESTATE_DELETE || $value["state"] == SyncCtoEnum::FILESTATE_FOLDER_DELETE) {
                                    $intDelCount++;
                                }

                                if ($value["split"] == true) {
                                    $intSplitCount++;
                                }
                            }
                        }
                    }

                    // Hide control div
                    $this->templateVars['showControl'] = false;
                    $this->templateVars['showNextControl'] = true;

                    // If no files are send show success msg
                    if (!is_array((array) $this->arrListCompare) || (count((array) $this->arrListCompare['core']) == 0 && count((array) $this->arrListCompare['files']) == 0)) {
                        $this->objData->setHtml("");
                        $this->objData->setState(SyncCtoEnum::WORK_OK);
                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_1']['description_2']);
                        $this->booFinished = true;

                        // Set finished msg
                        // Set success information
                        $arrClientLink = Database::getInstance()
                                                 ->prepare("SELECT * FROM tl_synccto_clients WHERE id=?")
                                                 ->limit(1)
                                                 ->execute($this->intClientID)
                                                 ->fetchAllAssoc()
                        ;

                        $strLink = vsprintf('<a href="%s:%s%s" target="_blank" style="text-decoration:underline;">', [$arrClientLink[0]['address'], $arrClientLink[0]['port'], $arrClientLink[0]['path']]);

                        $this->objData->nextStep();
                        $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['complete']);
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']['complete_client'], [$strLink, "</a>"]));
                        $this->objData->setState(SyncCtoEnum::WORK_OK);

                        break;
                    } // If files was send, show more information.
                    else {
                        if (is_array($this->arrListCompare) && ((array) count((array) $this->arrListCompare['core']) != 0 || count((array) $this->arrListCompare['files']) != 0)) {
                            $this->objData->setHtml("");
                            $this->objData->setState(SyncCtoEnum::WORK_OK);
                            $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_2'], [$intSendCount, (count((array) $this->arrListCompare['core']) + count((array) $this->arrListCompare['files']))]));
                            $this->booFinished = true;
                        } else {
                            $this->objData->setHtml("");
                            $this->objData->setState(SyncCtoEnum::WORK_OK);
                            $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_2'], [$intSendCount, (count((array) $this->arrListCompare['core']) + count((array) $this->arrListCompare['files']))]));
                            $this->booFinished = true;
                        }
                    }

                    $compare = '';

                    // Check if there are some skipped files
                    if ($intSkippCount != 0) {
                        $compare .= '<br /><p class="tl_help">' . $intSkippCount . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_3'] . '</p>';

                        $arrSort = [];

                        foreach ($this->arrListCompare as $strType => $arrLists) {
                            foreach ($arrLists as $value) {
                                if ($value["transmission"] != SyncCtoEnum::FILETRANS_SKIPPED) {
                                    continue;
                                }

                                $skipreason = preg_replace("/(RPC Call:.*|\<br\>|\<br\/\>)/i", " ", $value["skipreason"]);

                                $arrSort[$skipreason][] = $value;
                            }
                        }

                        $compare .= '<ul class="fileinfo">';
                        foreach ($arrSort as $strMsg => $arrFiles) {
                            $compare .= "<li>";
                            $compare .= '<strong>' . $strMsg . '</strong>';
                            $compare .= "<ul>";
                            foreach ($arrFiles as $arrFile) {
                                $compare .= sprintf('<li title="%s">%s</li>', $arrFile['error'], $arrFile['path']);
                            }
                            $compare .= "</ul>";
                            $compare .= "</li>";
                        }
                        $compare .= "</ul>";
                    }

                    // Write some information about the dbafs.
                    if (is_array($this->arrListCompare) && count((array) $this->arrListCompare['files']) != 0) {
                        $arrDbafsFiles = [];
                        foreach ($this->arrListCompare['files'] as $value) {
                            // Skip files without the dbafs information and no problems with the dbafs.
                            if (!isset($value['dbafs']) || $value['dbafs']['state'] != SyncCtoEnum::DBAFS_CONFLICT) {
                                continue;
                            }

                            // Add entries to the list.
                            $arrDbafsFiles[$value['dbafs']['msg']][] = $value;
                        }

                        $compare .= '<ul class="dbafs_info">';
                        $compare .= '<li class="tl_help">';

                        if (count((array) $arrDbafsFiles) == 0) {
                            $compare .= $GLOBALS['TL_LANG']['MSC']['dbafs_all_green'];
                        } else {
                            $compare .= $GLOBALS['TL_LANG']['ERR']['dbafs_error'];

                            $compare .= '<ul>';

                            foreach ($arrDbafsFiles as $strMsg => $arrFiles) {
                                $compare .= '<li class="tl_help">';
                                $compare .= sprintf('<p class="tl_help">%s</p>', $strMsg);
                                $compare .= '<ul>';

                                foreach ($arrFiles as $arrFile) {
                                    $compare .= sprintf('<li title="%s">', $arrFile['dbafs']['error']);
                                    $compare .= $arrFile['path'];
                                    $compare .= '</li>';
                                }

                                $compare .= '</ul>';
                                $compare .= '</li>';
                            }
                            $compare .= '</ul>';
                        }

                        $compare .= "</li>";
                        $compare .= "</ul>";
                    }

                    // Show file list only in debug mode
                    if ($GLOBALS['TL_CONFIG']['syncCto_debug_mode'] == true) {
                        if (is_array($this->arrListCompare) && (count((array) $this->arrListCompare['core']) != 0 || count((array) $this->arrListCompare['files']) != 0)) {
                            $compare .= '<br /><p class="tl_help">' . $intSendCount . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_4'] . '</p>';

                            if (($intSendCount - $intDelCount) != 0) {
                                $compare .= '<ul class="fileinfo">';

                                $compare .= "<li>";
                                $compare .= '<strong>' . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_6'] . '</strong>';
                                $compare .= "<ul>";

                                foreach ($this->arrListCompare as $strType => $arrLists) {
                                    foreach ($arrLists as $key => $value) {
                                        if ($value["transmission"] != SyncCtoEnum::FILETRANS_SEND && $value["transmission"] != SyncCtoEnum::FILETRANS_MOVED) {
                                            continue;
                                        }

                                        if ($value["state"] == SyncCtoEnum::FILESTATE_DELETE) {
                                            continue;
                                        }

                                        $compare .= "<li>";
                                        $compare .= (mb_check_encoding($value["path"], 'UTF-8')) ? $value["path"] : utf8_encode($value["path"]);
                                        $compare .= "</li>";
                                    }
                                }
                                $compare .= "</ul>";
                                $compare .= "</li>";
                                $compare .= "</ul>";
                            }

                            if ($intDelCount != 0) {
                                $compare .= '<ul class="fileinfo">';

                                $compare .= "<li>";
                                $compare .= '<strong>' . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_7'] . '</strong>';
                                $compare .= "<ul>";

                                $compare .= "<li>";
                                $compare .= '<strong>' . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_7'] . '</strong>';
                                $compare .= "<ul>";

                                foreach ($this->arrListCompare as $strType => $arrLists) {
                                    foreach ($arrLists as $key => $value) {
                                        if ($value["transmission"] != SyncCtoEnum::FILETRANS_SEND) {
                                            continue;
                                        }

                                        if ($value["state"] != SyncCtoEnum::FILESTATE_DELETE) {
                                            continue;
                                        }

                                        $compare .= "<li>";
                                        $compare .= (mb_check_encoding($value["path"], 'UTF-8')) ? $value["path"] : utf8_encode($value["path"]);
                                        $compare .= "</li>";
                                    }
                                }

                                $compare .= "</ul>";
                                $compare .= "</li>";

                                $compare .= "<li>";
                                $compare .= '<strong>' . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_9'] . '</strong>';
                                $compare .= "<ul>";

                                foreach ($this->arrListCompare as $strType => $arrLists) {
                                    foreach ($arrLists as $key => $value) {
                                        if ($value["transmission"] != SyncCtoEnum::FILETRANS_SEND && $value["transmission"] != SyncCtoEnum::FILETRANS_MOVED) {
                                            continue;
                                        }

                                        if ($value["state"] != SyncCtoEnum::FILESTATE_FOLDER_DELETE) {
                                            continue;
                                        }

                                        $compare .= "<li>";
                                        $compare .= (mb_check_encoding($value["path"], 'UTF-8')) ? $value["path"] : utf8_encode($value["path"]);
                                        $compare .= "</li>";
                                    }
                                }

                                $compare .= "</ul>";
                                $compare .= "</li>";

                                $compare .= "</ul>";
                                $compare .= "</li>";
                                $compare .= "</ul>";
                            }

                            // Not send and still waiting
                            if ($intWaitCount != 0) {
                                $compare .= '<br /><p class="tl_help">' . $intWaitCount . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_5'] . '</p>';
                                $compare .= '<ul class="fileinfo">';
                                $compare .= "<li>";
                                $compare .= '<strong>' . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_8'] . '</strong>';
                                $compare .= "<ul>";

                                foreach ($this->arrListCompare as $strType => $arrLists) {
                                    foreach ($arrLists as $key => $value) {
                                        if ($value["transmission"] != SyncCtoEnum::FILETRANS_WAITING) {
                                            continue;
                                        }

                                        $compare .= "<li>";
                                        $compare .= (mb_check_encoding($value["path"], 'UTF-8')) ? $value["path"] : utf8_encode($value["path"]);
                                        $compare .= "</li>";
                                    }
                                }

                                $compare .= "</ul>";
                                $compare .= "</li>";
                                $compare .= "</ul>";
                            }
                        }
                    }

                    $this->objData->setHtml($compare);

                    // Set finished msg
                    $arrClientLink = Database::getInstance()
                                             ->prepare("SELECT * FROM tl_synccto_clients WHERE id=?")
                                             ->limit(1)
                                             ->execute($this->intClientID)
                                             ->fetchAllAssoc()
                    ;

                    $strLink = vsprintf('<a href="%s:%s%s" target="_blank" style="text-decoration:underline;">', [$arrClientLink[0]['address'], $arrClientLink[0]['port'], $arrClientLink[0]['path']]);

                    $this->objData->nextStep();
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['complete']);
                    $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']['complete_client'], [$strLink, "</a>"]));
                    $this->objData->setState(SyncCtoEnum::WORK_OK);

                    break;
            }
        } catch (Exception $exc) {
            $this->objStepPool->increaseSubStep();

            $this->log(vsprintf("Error on synchronization client ID %s with msg: %s", [Input::get("id"), $exc->getMessage()]), __CLASS__ . " " . __FUNCTION__, "ERROR");
        }
    }

    /*
     * End syncTo
     * -------------------------------------------------------------------------
     */

    /* -------------------------------------------------------------------------
     * Start syncFrom
     */

    /**
     * Build checksum list and ask client
     */
    private function pageSyncFromShowStep2()
    {
        // Init
        if ($this->objStepPool->step == null) {
            $this->objStepPool->step = 1;
        }

        // Set content back to normale mode
        $this->booError = false;
        $this->strError = "";
        $this->objData->setState(SyncCtoEnum::WORK_WORK);

        // Run page
        try {
            switch ($this->objStepPool->step) {
                /**
                 * Show step
                 */
                case 1:
                    $this->objData->setState(SyncCtoEnum::WORK_WORK);
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1']);

                    $this->objStepPool->increaseSubStep();

                    break;

                /**
                 * Build checksum list for Conta core
                 */
                case 2:
                    if (in_array("core_change", $this->arrSyncSettings["syncCto_Type"])) {
                        $this->arrListFile['core'] = array_merge($this->arrListFile, $this->objSyncCtoCommunicationClient->getChecksumCore());
                        $this->objStepPool->increaseSubStep();
                        break;
                    } else {
                        $this->arrListFile['core'] = [];
                    }

                /**
                 * Build checksum list for 'files'
                 */
                case 3:
                    if (in_array("user_change", $this->arrSyncSettings["syncCto_Type"])) {
                        $this->arrListFile['files'] = $this->objSyncCtoCommunicationClient->getChecksumFiles([]);
                        $this->objStepPool->increaseSubStep();
                        break;
                    } else {
                        $this->arrListFile['files'] = [];
                    }

                /**
                 * Check List
                 */
                case 4:
                    if (in_array("core_change", $this->arrSyncSettings["syncCto_Type"])) {
                        $this->arrListCompare['core'] = $this->objSyncCtoFiles->runCecksumCompare($this->arrListFile['core']);
                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_2']);
                        $this->objStepPool->increaseSubStep();
                        break;
                    } else {
                        $this->arrListCompare['core'] = [];
                    }

                case 5:
                    if (in_array("user_change", $this->arrSyncSettings["syncCto_Type"])) {
                        $this->arrListCompare['files'] = $this->objSyncCtoFiles->runCecksumCompare($this->arrListFile['files'], !!$this->arrSyncSettings['automode']);
                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_2']);
                        $this->objStepPool->increaseSubStep();
                        break;
                    } else {
                        $this->arrListCompare['files'] = [];
                    }

                /**
                 * Check for deleted files
                 */
                case 6:
                    if (in_array("core_delete", $this->arrSyncSettings["syncCto_Type"])) {
                        $arrChecksumClient = $this->objSyncCtoFiles->runChecksumCore();
                        $arrChecksumClient = $this->objSyncCtoCommunicationClient->checkDeleteFiles($arrChecksumClient);
                        if (count((array) $arrChecksumClient) != 0) {
                            $this->arrListCompare['core'] = array_merge((array) $this->arrListCompare['core'], $arrChecksumClient);
                        }

                        $this->objStepPool->increaseSubStep();
                        break;
                    }

                case 7:
                    if (in_array("user_delete", $this->arrSyncSettings["syncCto_Type"])) {
                        $arrChecksumClient = $this->objSyncCtoFiles->runChecksumFiles();
                        $arrChecksumClient = $this->objSyncCtoCommunicationClient->checkDeleteFiles($arrChecksumClient);

                        if (count((array) $arrChecksumClient) != 0) {
                            $this->arrListCompare['files'] = array_merge((array) $this->arrListCompare['files'], $arrChecksumClient);
                        }

                        $this->objStepPool->increaseSubStep();
                        break;
                    }

                /**
                 * Check folders
                 */
                case 8:
                    if (in_array("core_delete", $this->arrSyncSettings["syncCto_Type"])) {
                        $arrChecksumClienta = $this->objSyncCtoFiles->runChecksumFolderCore();
                        $arrChecksumClientb = $this->objSyncCtoCommunicationClient->searchDeleteFolders($arrChecksumClienta);

                        if (count((array) $arrChecksumClientb) != 0) {
                            $this->arrListCompare['core'] = array_merge((array) $this->arrListCompare['core'], $arrChecksumClientb);
                        }

                        $this->objStepPool->increaseSubStep();
                        break;
                    }

                case 9:
                    if (in_array("user_delete", $this->arrSyncSettings["syncCto_Type"])) {
                        $arrChecksumClient = $this->objSyncCtoFiles->runChecksumFolderFiles();
                        $arrChecksumClient = $this->objSyncCtoCommunicationClient->searchDeleteFolders($arrChecksumClient);

                        if (count((array) $arrChecksumClient) != 0) {
                            $this->arrListCompare['files'] = array_merge((array) $this->arrListCompare['files'], $arrChecksumClient);
                        }
                    }

                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_3']);
                    $this->objStepPool->increaseSubStep();
                    break;

                /**
                 * Set CSS and search for bigfiles
                 */
                case 10:
                    foreach ($this->arrListCompare as $strType => $arrLists) {
                        foreach ($arrLists as $key => $value) {
                            switch ($value["state"]) {
                                case SyncCtoEnum::FILESTATE_BOMBASTIC_BIG:
                                    $this->arrListCompare[$strType][$key]["css"] = "unknown";
                                    $this->arrListCompare[$strType][$key]["css_big"] = "ignored";
                                    break;

                                case SyncCtoEnum::FILESTATE_TOO_BIG_NEED:
                                    $this->arrListCompare[$strType][$key]["css_big"] = "ignored";
                                case SyncCtoEnum::FILESTATE_NEED:
                                    $this->arrListCompare[$strType][$key]["css"] = "modified";
                                    break;

                                case SyncCtoEnum::FILESTATE_TOO_BIG_MISSING:
                                    $this->arrListCompare[$strType][$key]["css_big"] = "ignored";
                                case SyncCtoEnum::FILESTATE_MISSING:
                                    $this->arrListCompare[$strType][$key]["css"] = "new";
                                    break;

                                case SyncCtoEnum::FILESTATE_DELETE:
                                case SyncCtoEnum::FILESTATE_FOLDER_DELETE:
                                    $this->arrListCompare[$strType][$key]["css"] = "deleted";
                                    break;

                                default:
                                    $this->arrListCompare[$strType][$key]["css"] = "unknown";
                                    break;
                            }

                            if ($value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_DELETE
                                || $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_MISSING
                                || $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_NEED
                                || $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_SAME
                                || $value["state"] == SyncCtoEnum::FILESTATE_BOMBASTIC_BIG
                                || $value["state"] == SyncCtoEnum::FILESTATE_DELETE
                                || $value["state"] == SyncCtoEnum::FILESTATE_FOLDER_DELETE
                                || $value["state"] == SyncCtoEnum::FILESTATE_FOLDER
                            ) {
                                continue;
                            } else {
                                if ($value["size"] > $this->arrClientInformation["upload_sizeLimit"]) {
                                    $this->arrListCompare[$strType][$key]["split"] = true;
                                }
                            }
                        }
                    }

                    $this->objStepPool->increaseSubStep();
                    break;

                /**
                 * Show files form
                 */
                case 11:
                    // Counter
                    $intCountMissing = 0;
                    $intCountNeed = 0;
                    $intCountIgnored = 0;
                    $intCountDelete = 0;

                    $intTotalSizeNew = 0;
                    $intTotalSizeDel = 0;
                    $intTotalSizeChange = 0;

                    // Count files
                    foreach ($this->arrListCompare as $strType => $arrLists) {
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
                        }
                    }

                    $this->objStepPool->missing = $intCountMissing;
                    $this->objStepPool->need = $intCountNeed;
                    $this->objStepPool->ignored = $intCountIgnored;
                    $this->objStepPool->delete = $intCountDelete;

                    // Save files and go on or skip here
                    if ($intCountMissing == 0 && $intCountNeed == 0 && $intCountIgnored == 0 && $intCountDelete == 0) {
                        // Set current step informations
                        $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1']);
                        $this->objData->setHtml("");
                        $this->booRefresh = true;
                        $this->intStep++;

                        break;
                    } else {
                        if (count((array) $this->arrListCompare) == 0 || array_key_exists("skip", $_POST)) {
                            $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
                            $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1']);
                            $this->objData->setHtml("");
                            $this->booRefresh = true;
                            $this->intStep++;

                            $this->arrListCompare = [];

                            break;
                        } else {
                            if (($this->arrSyncSettings["automode"] || array_key_exists("forward", $_POST)) && count((array) $this->arrListCompare) != 0) {
                                $this->objData->setState(SyncCtoEnum::WORK_OK);
                                $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_4'], [$intCountMissing, $intCountNeed, $intCountDelete, $intCountIgnored, SyncCtoContaoApi::getReadableSize($intTotalSizeNew), SyncCtoContaoApi::getReadableSize($intTotalSizeChange), SyncCtoContaoApi::getReadableSize($intTotalSizeDel)]));
                                $this->objData->setHtml("");
                                $this->booRefresh = true;
                                $this->intStep++;

                                break;
                            }
                        }
                    }

                    $objTemp = new BackendTemplate("be_syncCto_form");
                    $objTemp->id = $this->intClientID;
                    $objTemp->step = $this->intStep;
                    $objTemp->direction = "From";
                    $objTemp->headline = $GLOBALS['TL_LANG']['MSC']['totalsize'];
                    $objTemp->cssId = 'syncCto_filelist_form';
                    $objTemp->forwardValue = $GLOBALS['TL_LANG']['MSC']['apply'];
                    $objTemp->popupClassName = 'popup/files';

                    // Build content
                    $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_4'], [$intCountMissing, $intCountNeed, $intCountDelete, $intCountIgnored, SyncCtoContaoApi::getReadableSize($intTotalSizeNew), SyncCtoContaoApi::getReadableSize($intTotalSizeChange), SyncCtoContaoApi::getReadableSize($intTotalSizeDel)]));
                    $this->objData->setHtml($objTemp->parse());
                    $this->booRefresh = false;

                    break;
            }
        } catch (Exception $exc) {
            // If an error occurs skip the whole step
            $this->arrListCompare = [];

            $objErrTemplate = new BackendTemplate('be_syncCto_error');
            $objErrTemplate->strErrorMsg = $exc->getMessage();

            $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
            $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_2"]['description_1']);
            $this->objData->setHtml($objErrTemplate->parse());
            $this->booRefresh = true;
            $this->intStep++;

            $this->log(vsprintf("Error on synchronization client ID %s with msg: %s", [Input::get("id"), $exc->getMessage()]), __CLASS__ . " " . __FUNCTION__, "ERROR");
        }
    }

    /**
     * Send Files / Split Files
     */
    private function pageSyncFromShowStep3()
    {
        // Init
        if ($this->objStepPool->step == null) {
            $this->objStepPool->step = 1;
        }

        // Set content back to normale mode
        $this->booError = false;
        $this->strError = "";
        $this->objData->setState(SyncCtoEnum::WORK_WORK);

        // Count files
        if (is_array($this->arrListCompare) && count((array) $this->arrListCompare) != 0 && $this->arrListCompare != false) {
            $intSkippCount = 0;
            $intSendCount = 0;
            $intWaitCount = 0;
            $intDelCount = 0;
            $intSplitCount = 0;

            foreach ($this->arrListCompare as $strType => $arrLists) {
                foreach ($arrLists as $key => $value) {
                    switch ($value["transmission"]) {
                        case SyncCtoEnum::FILETRANS_SEND:
                            $intSendCount++;
                            break;

                        case SyncCtoEnum::FILETRANS_SKIPPED:
                            $intSkippCount++;
                            break;

                        case SyncCtoEnum::FILETRANS_WAITING:
                            $intWaitCount++;
                            break;
                    }

                    if ($value["state"] == SyncCtoEnum::FILESTATE_DELETE || $value["state"] == SyncCtoEnum::FILESTATE_FOLDER_DELETE) {
                        $intDelCount++;
                    }

                    if ($value["split"] == true) {
                        $intSplitCount++;
                    }
                }
            }
        }

        try {
            // Timer
            $intStart = time();

            switch ($this->objStepPool->step) {
                /**
                 * Show step
                 */
                case 1:
                    $this->objData->setState(SyncCtoEnum::WORK_WORK);
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_1']);

                    $this->objStepPool->increaseSubStep();
                    break;

                /**
                 * Get normal files
                 */
                case 2:
                    // Send allfiles exclude the big thing ones
                    $intCountTransfer = 1;

                    foreach ($this->arrListCompare as $strType => $arrLists) {
                        foreach ($arrLists as $key => $value) {
                            if ($value["transmission"] == SyncCtoEnum::FILETRANS_SEND || $value["transmission"] == SyncCtoEnum::FILETRANS_SKIPPED) {
                                continue;
                            }

                            if (in_array($value["state"], [SyncCtoEnum::FILESTATE_DELETE, SyncCtoEnum::FILESTATE_FOLDER_DELETE])) {
                                continue;
                            }

                            if ($value["skipped"] == true) {
                                continue;
                            }

                            if ($value["split"] == true) {
                                continue;
                            }

                            if ($value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG
                                || $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_DELETE
                                || $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_NEED
                                || $value["state"] == SyncCtoEnum::FILESTATE_TOO_BIG_MISSING
                                || $value["state"] == SyncCtoEnum::FILESTATE_BOMBASTIC_BIG
                            ) {
                                $this->arrListCompare[$strType][$key]["skipreason"] = $GLOBALS['TL_LANG']['ERR']['maximum_filesize'];
                                $this->arrListCompare[$strType][$key]["transmission"] = SyncCtoEnum::FILETRANS_SKIPPED;

                                continue;
                            }

                            try {
                                // Get files
                                $booResponse = $this->objSyncCtoCommunicationClient->getFile($value["path"], $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "sync", $value["path"]));

                                // Check if the file was send and saved.
                                if ($booResponse != true) {
                                    throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['unknown_file'], $value["path"]));
                                }

                                $this->arrListCompare[$strType][$key]["transmission"] = SyncCtoEnum::FILETRANS_SEND;
                            } catch (Exception $exc) {
                                $this->arrListCompare[$strType][$key]["transmission"] = SyncCtoEnum::FILETRANS_SKIPPED;
                                $this->arrListCompare[$strType][$key]["skipreason"] = $exc->getMessage();
                            }

                            $intCountTransfer++;

                            if ($intCountTransfer == 201 || $intStart < (time() - 30)) {
                                break;
                            }
                        }
                    }

                    if ($intWaitCount - ($intDelCount + $intSplitCount + $intSkippCount) > 0) {
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_2'], [$intSendCount, count((array) $this->arrListCompare) - ($intDelCount + $intSplitCount + $intSkippCount)]));
                    } else {
                        foreach ($this->arrListCompare as $strType => $arrLists) {
                            foreach ($arrLists as $key => $value) {
                                if ($value["split"] == true) {
                                    $this->objStepPool->increaseSubStep();
                                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_3']);

                                    return;
                                }
                            }
                        }

                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_1']);
                        $this->objData->setState(SyncCtoEnum::WORK_OK);
                        $this->intStep++;
                    }

                    break;

                /**
                 * Split files
                 */
                case 3:
                    $intCountSplit = 0;
                    $intCount = 0;

                    foreach ($this->arrListCompare as $strType => $arrLists) {
                        foreach ($arrLists as $key => $value) {
                            if ($value["split"] == true) {
                                $intCountSplit++;
                            }
                        }
                    }

                    foreach ($this->arrListCompare as $strType => $arrLists) {
                        foreach ($arrLists as $key => $value) {
                            if ($value["split"] != true) {
                                continue;
                            }

                            if ($value["split"] != 0 && $value["splitname"] != "") {
                                $intCount++;
                                continue;
                            }

                            $strSavePath = $this->objSyncCtoHelper->standardizePath($this->arrClientInformation["folders"]['tmp'], $key);

                            // Splitt file
                            $intSplits = $this->objSyncCtoCommunicationClient->runSplitFiles($value["path"], $strSavePath, $key, ($this->arrClientInformation["upload_sizeLimit"] / 100 * $this->arrClientInformation["upload_sizePercent"]));

                            $this->arrListCompare[$strType][$key]["splitcount"] = $intSplits;
                            $this->arrListCompare[$strType][$key]["splitname"] = $key;

                            break;
                        }
                    }

                    if ($intCount != $intCountSplit) {
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_6'], [$intCount, $intCountSplit]));
                    } else {
                        $this->objStepPool->increaseSubStep();
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_4'], [$intCount, $intCountSplit]));
                    }

                    break;

                /**
                 * Get bigfiles
                 */
                case 4:
                    $intCountSplit = 0;
                    $intCount = 0;

                    foreach ($this->arrListCompare as $strType => $arrLists) {
                        foreach ($arrLists as $key => $value) {
                            if ($value["split"] == true) {
                                $intCountSplit++;
                            }
                        }
                    }

                    foreach ($this->arrListCompare as $strType => $arrLists) {
                        foreach ($arrLists as $key => $value) {
                            if ($value["split"] != true) {
                                continue;
                            }

                            if (in_array(
                                $value["state"], [
                                                   SyncCtoEnum::FILESTATE_TOO_BIG_DELETE,
                                                   SyncCtoEnum::FILESTATE_TOO_BIG_MISSING,
                                                   SyncCtoEnum::FILESTATE_TOO_BIG_NEED,
                                                   SyncCtoEnum::FILESTATE_TOO_BIG_SAME,
                                                   SyncCtoEnum::FILESTATE_BOMBASTIC_BIG,
                                                   SyncCtoEnum::FILESTATE_DELETE,
                                                   SyncCtoEnum::FILESTATE_FOLDER_DELETE,
                                               ]
                            )
                            ) {
                                continue;
                            }

                            if (!empty($value["split_transfer"]) && $value["splitcount"] == $value["split_transfer"]) {
                                $intCount++;
                                continue;
                            }

                            if (empty($value["split_transfer"])) {
                                $value["split_transfer"] = 0;
                            }

                            for ($ii = $value["split_transfer"]; $ii < $value["splitcount"]; $ii++) {
                                // Max limit for file send, 10 minutes
                                set_time_limit(7200);

                                // Send file to client
                                $booResponse = $this->objSyncCtoCommunicationClient->getFile($this->objSyncCtoHelper->standardizePath($this->arrClientInformation["folders"]['tmp'], $key, $value["splitname"] . ".sync$ii"), $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], $key, $value["splitname"] . ".sync$ii"));

                                // Check if the file was send and saved.
                                if ($booResponse != true) {
                                    throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['unknown_file'], $value["path"]));
                                }

                                $this->arrListCompare[$strType][$key]["split_transfer"] = $ii + 1;

                                // check time limit 30 secs
                                if ($intStart + 30 < time()) {
                                    break;
                                }
                            }

                            break;
                        }
                    }

                    if ($intCount != $intCountSplit) {
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_7'], [$intCount, $intCountSplit]));
                    } else {
                        $this->objStepPool->increaseSubStep();
                        $this->objData->setState(SyncCtoEnum::WORK_WORK);
                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_5']);
                    }

                    break;

                /**
                 * Rebuild split files
                 */
                case 5:
                    $intCountSplit = 0;
                    $intCount = 0;

                    foreach ($this->arrListCompare as $strType => $arrLists) {
                        foreach ($arrLists as $key => $value) {
                            if ($value["split"] == true) {
                                $intCountSplit++;
                            }
                        }
                    }

                    foreach ($this->arrListCompare as $strType => $arrLists) {
                        foreach ($arrLists as $key => $value) {
                            if ($value["split"] != true) {
                                continue;
                            }

                            if (in_array(
                                $value["state"], [
                                                   SyncCtoEnum::FILESTATE_TOO_BIG_DELETE,
                                                   SyncCtoEnum::FILESTATE_TOO_BIG_MISSING,
                                                   SyncCtoEnum::FILESTATE_TOO_BIG_NEED,
                                                   SyncCtoEnum::FILESTATE_TOO_BIG_SAME,
                                                   SyncCtoEnum::FILESTATE_BOMBASTIC_BIG,
                                                   SyncCtoEnum::FILESTATE_DELETE,
                                                   SyncCtoEnum::FILESTATE_FOLDER_DELETE,
                                               ]
                            )
                            ) {
                                continue;
                            }

                            if ($value["transmission"] == SyncCtoEnum::FILETRANS_SEND) {
                                $intCount++;
                                continue;
                            }

                            if (!$this->objSyncCtoFiles->rebuildSplitFiles($value["splitname"], $value["splitcount"], $value["path"], $value["checksum"])) {
                                throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['rebuild'], [$value["path"]]));
                            }

                            $this->arrListCompare[$strType][$key]["transmission"] = SyncCtoEnum::FILETRANS_SEND;

                            if ($intStart < time() - 30) {
                                break;
                            }
                        }
                    }

                    if ($intCount != $intCountSplit) {
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_8'], [$intCount, $intCountSplit]));
                    } else {
                        $this->objData->setState(SyncCtoEnum::WORK_OK);
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_1'], [$intCount, $intCountSplit]));

                        $this->intStep++;
                    }

                    break;
            }
        } catch (Exception $exc) {

            // If an error occurs skip the whole step
            $objErrTemplate = new BackendTemplate('be_syncCto_error');
            $objErrTemplate->strErrorMsg = $exc->getMessage();

            $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
            $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_1']);
            $this->objData->setHtml($objErrTemplate->parse());
            $this->booRefresh = true;
            $this->intStep++;

            $this->log(vsprintf("Error on synchronization client ID %s with msg: %s", [Input::get("id"), $exc->getMessage()]), __CLASS__ . " " . __FUNCTION__, "ERROR");
        }
    }

    /**
     * Build SQL zip and send it to the client
     */
    private function pageSyncFromShowStep4()
    {
        /* ---------------------------------------------------------------------
         * Init
         */

        if ($this->objStepPool->step == null) {
            $this->objStepPool->step = 1;
        }

        // Set content back to normale mode
        if ($this->booError == true) {
            $this->booError = false;
            $this->strError = "";
            $this->objData->setState(SyncCtoEnum::WORK_WORK);

            $this->objStepPool->step = 1;
        }

        /* ---------------------------------------------------------------------
         * Run page
         */

        try {
            switch ($this->objStepPool->step) {
                /**
                 * Init
                 */
                case 1:
                    $this->objData->setState(SyncCtoEnum::WORK_WORK);
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_1']);
                    $this->objStepPool->increaseSubStep();

                    break;

                case 2:
                    // Check user
                    if (BackendUser::getInstance()->isAdmin || BackendUser::getInstance()->syncCto_tables != null) {
                        // Load allowed tables for this user
                        if (BackendUser::getInstance()->isAdmin) {
                            $arrAllowedTables = true;
                        } else {
                            $arrAllowedTables = BackendUser::getInstance()->syncCto_tables;
                        }

                        $arrClientTableR = $this->objSyncCtoCommunicationClient->getRecommendedTables();
                        $arrClientTableNR = $this->objSyncCtoCommunicationClient->getNoneRecommendedTables();
                        $arrClientTableH = $this->objSyncCtoCommunicationClient->getHiddenTables();
                        $arrClientTableHP = $this->objSyncCtoCommunicationClient->getPreparedHiddenTablesPlaceholder();
                        $arrClientTimestamp = $this->objSyncCtoCommunicationClient->getClientTimestamp([]);

                        $arrServerTableR = $this->objSyncCtoHelper->databaseTablesRecommended();
                        $arrServerTableNR = $this->objSyncCtoHelper->databaseTablesNoneRecommended();
                        $arrServerTableH = $this->objSyncCtoHelper->getTablesHidden();
                        $arrServerTableHP = $this->objSyncCtoHelper->getPreparedHiddenTablesPlaceholder();
                        $arrServerTimestamp = $this->objSyncCtoHelper->getDatabaseTablesTimestamp();

                        // clean up tables. Use user rights.
                        if ($arrAllowedTables !== true) {
                            foreach ($arrClientTableR as $key => $value) {
                                if (!in_array($value['name'], $arrAllowedTables)) {
                                    unset($arrClientTableR[$key]);
                                }
                            }

                            foreach ($arrClientTableNR as $key => $value) {
                                if (!in_array($value['name'], $arrAllowedTables)) {
                                    unset($arrClientTableNR[$key]);
                                }
                            }

                            foreach ($arrClientTableH as $key => $value) {
                                if (!in_array($value, $arrAllowedTables)) {
                                    unset($arrClientTableH[$key]);
                                }
                            }

                            foreach ($arrServerTableR as $key => $value) {
                                if (!in_array($value['name'], $arrAllowedTables)) {
                                    unset($arrServerTableR[$key]);
                                }
                            }

                            foreach ($arrServerTableNR as $key => $value) {
                                if (!in_array($value['name'], $arrAllowedTables)) {
                                    unset($arrServerTableNR[$key]);
                                }
                            }

                            foreach ($arrServerTableH as $key => $value) {
                                if (!in_array($value, $arrAllowedTables)) {
                                    unset($arrServerTableH[$key]);
                                }
                            }
                        }

                        // Merge all together
                        foreach ($arrServerTableR as $key => $value) {
                            $arrServerTableR[$key]['type'] = 'recommended';
                        }

                        foreach ($arrClientTableR as $key => $value) {
                            $arrClientTableR[$key]['type'] = 'recommended';
                        }

                        foreach ($arrServerTableNR as $key => $value) {
                            $arrServerTableNR[$key]['type'] = 'nonRecommended';
                        }

                        foreach ($arrClientTableNR as $key => $value) {
                            $arrClientTableNR[$key]['type'] = 'nonRecommended';
                        }

                        $arrServerTables = array_merge($arrServerTableR, $arrServerTableNR);
                        $arrClientTables = array_merge($arrClientTableR, $arrClientTableNR);
                        $arrHiddenTables = array_keys(array_flip(array_merge($arrServerTableH, $arrClientTableH)));
                        $arrHiddenTablesPlaceholder = array_keys(array_flip(array_merge($arrClientTableHP, $arrServerTableHP)));
                        $arrAllTimeStamps = $this->objSyncCtoDatabase->getAllTimeStamps($arrServerTimestamp, $arrClientTimestamp, $this->intClientID);

                        $arrCompareList = $this->objSyncCtoDatabase->getFormatedCompareList($arrClientTables, $arrServerTables, $arrHiddenTables, $arrHiddenTablesPlaceholder, $arrAllTimeStamps['client'], $arrAllTimeStamps['server'], $arrAllowedTables, 'client', 'server');

                        if (count((array) $arrCompareList['recommended']) == 0 && count((array) $arrCompareList['none_recommended']) == 0) {
                            $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
                            $this->objData->setHtml("");
                            $this->intStep++;

                            break;
                        }

                        $this->arrSyncSettings['syncCto_CompareTables'] = $arrCompareList;

                        // If automode set all tabels as transferd.
                        if ($this->arrSyncSettings['automode']) {
                            $this->arrSyncSettings['syncCto_SyncDeleteTables'] = [];
                            $this->arrSyncSettings['syncCto_SyncTables'] = [];

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
                        }

                        // Set the tl_files if we have the automode or the checkbox is activate.
                        if ($this->arrSyncSettings['automode'] || $this->arrSyncSettings['syncCto_SyncTlFiles']) {
                            $this->arrSyncSettings['syncCto_SyncTables'][] = 'tl_files';
                        }

                        $this->objStepPool->increaseSubStep();
                    } else {
                        $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
                        $this->objData->setHtml("");
                        $this->intStep++;
                    }

                    break;

                case 3:

                    if (($this->arrSyncSettings['automode'] || array_key_exists("forward", $_POST)) && !(count((array) $this->arrSyncSettings['syncCto_SyncTables']) == 0 && count((array) $this->arrSyncSettings['syncCto_SyncDeleteTables']) == 0)) {
                        // Go to next step
                        $this->objData->setState(SyncCtoEnum::WORK_WORK);
                        $this->objData->setHtml("");
                        $this->booRefresh = true;
                        $this->objStepPool->increaseSubStep();

                        break;
                    } else {
                        if (($this->arrSyncSettings['automode'] || array_key_exists("forward", $_POST)) && count((array) $this->arrSyncSettings['syncCto_SyncTables']) == 0 && count((array) $this->arrSyncSettings['syncCto_SyncDeleteTables']) == 0) {
                            // Skip if no tables are selected
                            $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
                            $this->objData->setHtml("");
                            $this->booRefresh = true;
                            $this->intStep++;

                            break;
                        } else {
                            if (key_exists("skip", $_POST)) {
                                $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
                                $this->objData->setHtml("");
                                $this->booRefresh = true;
                                $this->intStep++;

                                break;
                            }
                        }
                    }

                    $objTemp = new BackendTemplate("be_syncCto_form");
                    $objTemp->id = $this->intClientID;
                    $objTemp->step = $this->intStep;
                    $objTemp->direction = "From";
                    $objTemp->headline = $GLOBALS['TL_LANG']['MSC']['totalsize'];
                    $objTemp->cssId = 'syncCto_database_form';
                    $objTemp->forwardValue = $GLOBALS['TL_LANG']['MSC']['apply'];
                    $objTemp->popupClassName = 'popup/database';

                    // Build content
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_1']);
                    $this->objData->setHtml($objTemp->parse());
                    $this->booRefresh = false;

                    break;

                /**
                 * Build SQL Zip File
                 */
                case 4:
                    if (count((array) $this->arrSyncSettings['syncCto_SyncTables']) != 0) {
                        $this->objStepPool->zipname = $this->objSyncCtoCommunicationClient->runDatabaseDump($this->arrSyncSettings['syncCto_SyncTables'], true, true);

                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_2']);
                        $this->objStepPool->increaseSubStep();
                    } else {
                        $this->objStepPool->step = 8;
                    }

                    break;

                /**
                 * Get file to client
                 */
                case 5:

                    $strFrom = $this->objSyncCtoHelper->standardizePath($this->arrClientInformation["folders"]['tmp'], $this->objStepPool->zipname);
                    $strTo = $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "sql", $this->objStepPool->zipname);
                    $booResponse = $this->objSyncCtoCommunicationClient->getFile($strFrom, $strTo);

                    // Check if the file was send and saved.
                    if ($booResponse != true) {
                        throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['unknown_file'], $strFrom));
                    }

                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_3']);
                    $this->objStepPool->increaseSubStep();

                    break;

                /**
                 * Import on server side
                 */
                case 6:
                    $arrSQL = [];

                    if (isset($GLOBALS['TL_HOOKS']['syncDBUpdateBeforeDrop']) && is_array($GLOBALS['TL_HOOKS']['syncDBUpdateBeforeDrop'])) {
                        foreach ($GLOBALS['TL_HOOKS']['syncDBUpdateBeforeDrop'] as $callback) {
                            if (!class_exists($callback[0])) {
                                continue;
                            }

                            if (method_exists($callback[0], 'getInstance')) {
                                $objCallbackClass = $callback[0]::getInstance();
                            } else {
                                $objCallbackClass = new $callback[0]();
                            }

                            $mixReturn = $objCallbackClass->{$callback[1]}($this->intClientID, $this->arrSyncSettings['syncCto_SyncTables'], $arrSQL);

                            if (!empty($mixReturn) && is_array($mixReturn)) {
                                $arrSQL = $mixReturn;
                            }
                        }
                    }

                    $strSrc = $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "sql", $this->objStepPool->zipname);
                    $this->objSyncCtoDatabase->runRestore($strSrc, $arrSQL);

                    $this->objStepPool->increaseSubStep();

                    break;

                /**
                 * Set timestamps
                 */
                case 7:

                    $arrTableTimestamp = [
                        'server' => $this->objSyncCtoHelper->getDatabaseTablesTimestamp($this->arrSyncSettings['syncCto_SyncTables']),
                        'client' => $this->objSyncCtoCommunicationClient->getClientTimestamp($this->arrSyncSettings['syncCto_SyncTables']),
                    ];

                    foreach ($arrTableTimestamp as $location => $arrTimeStamps) {
                        // Update Timestamp
                        $mixLastTableTimestamp = Database::getInstance()
                                                         ->prepare("SELECT " . $location . "_timestamp FROM tl_synccto_clients WHERE id=?")
                                                         ->limit(1)
                                                         ->execute($this->intClientID)
                                                         ->fetchAllAssoc()
                        ;

                        if (strlen($mixLastTableTimestamp[0][$location . "_timestamp"]) != 0) {
                            $arrLastTableTimestamp = unserialize($mixLastTableTimestamp[0][$location . "_timestamp"]);
                        } else {
                            $arrLastTableTimestamp = [];
                        }

                        foreach ($arrTimeStamps as $key => $value) {
                            $arrLastTableTimestamp[$key] = $value;
                        }

                        // Search for old entries
                        $arrTables = Database::getInstance()->listTables();
                        foreach ($arrLastTableTimestamp as $key => $value) {
                            if (!in_array($key, $arrTables)) {
                                unset($arrLastTableTimestamp[$key]);
                            }
                        }

                        Database::getInstance()
                                ->prepare("UPDATE tl_synccto_clients SET " . $location . "_timestamp = ? WHERE id = ? ")
                                ->execute(serialize($arrLastTableTimestamp), $this->intClientID)
                        ;
                    }

                    $this->objStepPool->increaseSubStep();

                    break;

                /**
                 * Drop Tables
                 */
                case 8:
                    if (count((array) $this->arrSyncSettings['syncCto_SyncDeleteTables']) != 0) {
                        $this->objSyncCtoDatabase->dropTable($this->arrSyncSettings['syncCto_SyncDeleteTables'], true);
                    }

                    // Show step information
                    $this->objData->setState(SyncCtoEnum::WORK_OK);
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_4']);

                    $this->intStep++;

                    break;
            }
        } catch (Exception $exc) {
            // If an error occurs skip the whole step
            $this->arrSyncSettings['syncCto_SyncDeleteTables'] = [];
            $this->arrSyncSettings['syncCto_CompareTables'] = [];

            $objErrTemplate = new BackendTemplate('be_syncCto_error');
            $objErrTemplate->strErrorMsg = $exc->getMessage();

            $this->objData->setState(SyncCtoEnum::WORK_SKIPPED);
            $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_1']);
            $this->objData->setHtml($objErrTemplate->parse());
            $this->booRefresh = true;
            $this->intStep++;

            $this->log(vsprintf("Error on synchronization client ID %s with msg: %s", [Input::get("id"), $exc->getMessage()]), __CLASS__ . " " . __FUNCTION__, "ERROR");
        }
    }

    /**
     * File send part have fun, much todo here so let`s play a round :P
     */
    private function pageSyncFromShowStep6()
    {
        /* ---------------------------------------------------------------------
         * Init
         */

        if ($this->objStepPool->step == null) {
            $this->objStepPool->step = 1;
        }

        // Set content back to normale mode
        $this->booError = false;
        $this->strError = "";
        $this->objData->setState(SyncCtoEnum::WORK_WORK);

        /* ---------------------------------------------------------------------
         * Run page
         */

        try {
            switch ($this->objStepPool->step) {
                /**
                 * Init
                 */
                case 1:
                    $this->objData->setState(SyncCtoEnum::WORK_WORK);
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_1']);
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
                    $this->objStepPool->increaseSubStep();
                    break;

                /**
                 * Delete files
                 */
                case 2:
                    if (is_array($this->arrListCompare) && (count((array) $this->arrListCompare['core']) != 0 || count((array) $this->arrListCompare['files']) != 0)) {
                        $arrDelete = [];

                        foreach ($this->arrListCompare as $strType => $arrLists) {
                            foreach ($arrLists as $key => $value) {
                                if (in_array($value["state"], [SyncCtoEnum::FILESTATE_DELETE, SyncCtoEnum::FILESTATE_FOLDER_DELETE])) {
                                    $arrDelete[$strType][$key] = $arrLists[$key];
                                }
                            }
                        }

                        // Execute the list with deleted files.
                        if (is_array($arrDelete['core']) && count((array) $arrDelete['core']) > 0) {
                            $arrResponseDelete = $this->objSyncCtoFiles->deleteFiles($arrDelete['core'], false);
                            foreach ($arrResponseDelete as $key => $value) {
                                $this->arrListCompare['core'][$key] = $value;
                            }
                        }

                        // Execute the list with deleted files.
                        if (is_array($arrDelete['files']) && count((array) $arrDelete['files']) > 0) {
                            $arrResponseDelete = $this->objSyncCtoFiles->deleteFiles($arrDelete['files'], true);
                            foreach ($arrResponseDelete as $key => $value) {
                                $this->arrListCompare['files'][$key] = $value;
                            }
                        }
                    }

                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_2']);
                    $this->objStepPool->increaseSubStep();
                    break;

                /**
                 * Import Files
                 */
                case 3:
                    if (is_array($this->arrListCompare) && (count((array) $this->arrListCompare['core']) != 0 || count((array) $this->arrListCompare['files']) != 0)) {
                        $arrImport = [];

                        // For core file do it like all the time SIMPEL ....
                        foreach ($this->arrListCompare['core'] as $key => $value) {
                            // Skip some values.
                            if (in_array($value["state"], [SyncCtoEnum::FILESTATE_DELETE, SyncCtoEnum::FILESTATE_FOLDER_DELETE, SyncCtoEnum::FILESTATE_TOO_BIG_DELETE])) {
                                continue;
                            }

                            if ($value["transmission"] == SyncCtoEnum::FILETRANS_SEND) {
                                $arrImport['core'][$key] = $value;
                            }
                        }

                        // ...and now the support for the uuid und dbafs system
                        foreach ($this->arrListCompare['files'] as $key => $value) {
                            // Skip some values.
                            if (in_array($value["state"], [SyncCtoEnum::FILESTATE_DELETE, SyncCtoEnum::FILESTATE_FOLDER_DELETE, SyncCtoEnum::FILESTATE_TOO_BIG_DELETE])) {
                                continue;
                            }

                            // Only add valid ones.
                            if ($value["transmission"] == SyncCtoEnum::FILETRANS_SEND) {
                                // Add the file to the import array.
                                $arrImport['files'][$key] = $value;
                            }
                        }

                        // Import all core data and write the data back in the compare list.
                        if (is_array($arrImport['core']) && count((array) $arrImport['core']) > 0) {
                            $arrResultFiles = $this->objSyncCtoFiles->moveTempFile($arrImport['core'], false);
                            foreach ($arrResultFiles as $key => $value) {
                                $this->arrListCompare['core'][$key] = $value;
                            }
                        }

                        // Import all files data and write the data back in the compare list.
                        if (is_array($arrImport['files']) && count((array) $arrImport['files']) > 0) {
                            // Get the DBAFS information from the client for the locale import.
                            $arrImport['files'] = $this->objSyncCtoCommunicationClient->getDbafsInformationFor($arrImport['files']);

                            // Move the files with DBAFS support.
                            $arrResultFiles = $this->objSyncCtoFiles->moveTempFile($arrImport['files'], true);
                            foreach ($arrResultFiles as $key => $value) {
                                $this->arrListCompare['files'][$key] = $value;
                            }
                        }

                        $this->objStepPool->increaseSubStep();
                        break;
                    }

                    $this->objStepPool->increaseSubStep();


                /**
                 * Import Config
                 */
                case 4:
                    if (in_array("localconfig_update", $this->arrSyncSettings["syncCto_Type"])) {
                        $arrLocalconfig = $this->objSyncCtoCommunicationClient->getLocalConfig();
                        if (count((array) $arrLocalconfig) != 0) {
                            $this->objSyncCtoHelper->importConfig($arrLocalconfig);
                        }

                        $this->objStepPool->increaseSubStep();
                        break;
                    }

                    $this->objStepPool->increaseSubStep();

                /**
                 * Import Config / Set referer check
                 */
                case 5:
                    if (is_array($this->arrSyncSettings["syncCto_Systemoperations_Maintenance"]) && count((array) $this->arrSyncSettings["syncCto_Systemoperations_Maintenance"]) != 0) {
                        $this->objSyncCtoFiles->runMaintenance($this->arrSyncSettings["syncCto_Systemoperations_Maintenance"]);
                    }

                    $this->objStepPool->increaseSubStep();
                    break;

                case 6:
                    if ($this->arrSyncSettings["syncCto_AttentionFlag"] == true) {
                        $this->objSyncCtoCommunicationClient->setAttentionFlag(true);
                    } else {
                        $this->objSyncCtoCommunicationClient->setAttentionFlag(false);
                    }

                    $this->log(vsprintf("Successfully finishing of synchronization client ID %s.", [Input::get("id")]), __CLASS__ . " " . __FUNCTION__, "INFO");

                    $this->objData->setState(SyncCtoEnum::WORK_OK);
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_1']);
                    $this->objData->setHtml("");
                    $this->booRefresh = true;
                    $this->intStep++;
            }
        } catch (Exception $exc) {
            $this->objStepPool->increaseSubStep();

            $this->log(vsprintf("Error on synchronization client ID %s with msg: %s", [Input::get("id"), $exc->getMessage()]), __CLASS__ . " " . __FUNCTION__, "ERROR");
        }
    }

    /**
     * File send part have fun, much todo here so let`s play a round :P
     */
    private function pageSyncFromShowStep7()
    {
        /* ---------------------------------------------------------------------
         * Init
         */

        if ($this->objStepPool->step == null) {
            $this->objStepPool->step = 1;
        }

        // Set content back to normale mode
        $this->booError = false;
        $this->strError = "";
        $this->objData->setState(SyncCtoEnum::WORK_WORK);

        /* ---------------------------------------------------------------------
         * Run page
         */

        try {
            switch ($this->objStepPool->step) {
                /**
                 * Init
                 */
                case 1:
                    $this->objData->setState(SyncCtoEnum::WORK_WORK);
                    $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_1']['description_2']);
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['step'] . " %s");
                    $this->objStepPool->increaseSubStep();
                    break;

                /**
                 * Cleanup
                 */
                case 2:
                    $this->objSyncCtoCommunicationClient->purgeTempFolder();
                    $this->objSyncCtoFiles->purgeTemp();
                    $this->objStepPool->increaseSubStep();
                    break;

                /**
                 * Call the final operations hook
                 */
                case 3:
                    $arrResponse = $this->objSyncCtoHelper->executeFinalOperations();
                    $this->objStepPool->increaseSubStep();
                    break;

                case 4:
                    $this->objSyncCtoCommunicationClient->referrerEnable();
                    $this->objStepPool->increaseSubStep();
                    break;

                case 5:
                    $this->objSyncCtoCommunicationClient->stopConnection();
                    SyncCtoStats::getInstance()->addEndStat(time());
                    $this->objStepPool->increaseSubStep();
                    break;

                /**
                 * Show information
                 */
                case 6:
                    // Count files
                    if (is_array($this->arrListCompare) && (count((array) $this->arrListCompare['core']) != 0 || count((array) $this->arrListCompare['files']) != 0)) {
                        $intSkippCount = 0;
                        $intSendCount = 0;
                        $intWaitCount = 0;
                        $intDelCount = 0;
                        $intSplitCount = 0;

                        foreach ($this->arrListCompare as $strType => $arrLists) {
                            foreach ($arrLists as $key => $value) {
                                switch ($value["transmission"]) {
                                    case SyncCtoEnum::FILETRANS_SEND:
                                        $intSendCount++;
                                        break;

                                    case SyncCtoEnum::FILETRANS_SKIPPED:
                                        $intSkippCount++;
                                        break;

                                    case SyncCtoEnum::FILETRANS_WAITING:
                                        $intWaitCount++;
                                        break;
                                }

                                if ($value["state"] == SyncCtoEnum::FILESTATE_DELETE || $value["state"] == SyncCtoEnum::FILESTATE_FOLDER_DELETE) {
                                    $intDelCount++;
                                }

                                if ($value["split"] == true) {
                                    $intSplitCount++;
                                }
                            }
                        }
                    }

                    // Hide control div
                    $this->templateVars['showControl'] = false;
                    $this->templateVars['showNextControl'] = true;

                    // If no files are send show success msg
                    if (!is_array($this->arrListCompare) || (count((array) $this->arrListCompare['core']) == 0 && count((array) $this->arrListCompare['files']) == 0)) {
                        $this->objData->setHtml("");
                        $this->objData->setState(SyncCtoEnum::WORK_OK);
                        $this->objData->setDescription($GLOBALS['TL_LANG']['tl_syncCto_sync']['step_1']['description_2']);
                        $this->booFinished = true;

                        // Set finished msg
                        // Set success information
                        $arrClientLink = Database::getInstance()
                                                 ->prepare("SELECT * FROM tl_synccto_clients WHERE id=?")
                                                 ->limit(1)
                                                 ->execute($this->intClientID)
                                                 ->fetchAllAssoc()
                        ;

                        $strLink = vsprintf('<a href="%s:%s%s" target="_blank" style="text-decoration:underline;">', [$arrClientLink[0]['address'], $arrClientLink[0]['port'], $arrClientLink[0]['path']]);

                        $this->objData->nextStep();
                        $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['complete']);
                        $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']['complete_client'], [$strLink, "</a>"]));
                        $this->objData->setState(SyncCtoEnum::WORK_OK);

                        break;
                    } // If files was send, show more information.
                    else {
                        if (is_array($this->arrListCompare) && (count((array) $this->arrListCompare['core']) != 0 || count((array) $this->arrListCompare['files']) != 0)) {
                            $this->objData->setHtml("");
                            $this->objData->setState(SyncCtoEnum::WORK_OK);
                            $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']["step_3"]['description_2'], [$intSendCount, (count((array) $this->arrListCompare['core']) + count((array) $this->arrListCompare['files']))]));
                            $this->booFinished = true;
                        }
                    }

                    $compare = '';

                    // Check if there are some skipped files
                    if ($intSkippCount != 0) {
                        $compare .= '<br /><p class="tl_help">' . $intSkippCount . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_3'] . '</p>';

                        $arrSort = [];

                        foreach ($this->arrListCompare as $strType => $arrLists) {
                            foreach ($arrLists as $value) {
                                if ($value["transmission"] != SyncCtoEnum::FILETRANS_SKIPPED) {
                                    continue;
                                }

                                $skipreason = preg_replace("/(RPC Call:.*|\<br\>|\<br\/\>)/i", " ", $value["skipreason"]);

                                $arrSort[$skipreason][] = $value;
                            }
                        }

                        $compare .= '<ul class="fileinfo">';
                        foreach ($arrSort as $strMsg => $arrFiles) {
                            $compare .= "<li>";
                            $compare .= '<strong>' . $strMsg . '</strong>';
                            $compare .= "<ul>";
                            foreach ($arrFiles as $arrFile) {
                                $compare .= sprintf('<li title="%s">%s</li>', $arrFile['error'], $arrFile['path']);
                            }
                            $compare .= "</ul>";
                            $compare .= "</li>";
                        }
                        $compare .= "</ul>";
                    }

                    // Write some information about the dbafs.
                    if (is_array($this->arrListCompare) && count((array) $this->arrListCompare['files']) != 0) {
                        $arrDbafsFiles = [];
                        foreach ($this->arrListCompare['files'] as $key => $value) {
                            // Skip files without the dbafs information and no problems with the dbafs.
                            if (!isset($value['dbafs']) || $value['dbafs']['state'] != SyncCtoEnum::DBAFS_CONFLICT) {
                                continue;
                            }

                            // Add entries to the list.
                            $arrDbafsFiles[$value['dbafs']['msg']][] = $value;
                        }

                        $compare .= '<ul class="dbafsinfo">';
                        $compare .= "<li>";

                        if (count((array) $arrDbafsFiles) == 0) {
                            $compare .= $GLOBALS['TL_LANG']['MSC']['dbafs_all_green'];
                        } else {
                            $compare .= $GLOBALS['TL_LANG']['ERR']['dbafs_error'];
                            $compare .= '<ul>';

                            foreach ($arrDbafsFiles as $strMsg => $arrFiles) {
                                $compare .= '<li>';
                                $compare .= sprintf('<p>%s</p>', $strMsg);
                                $compare .= '<ul>';

                                foreach ($arrFiles as $arrFile) {
                                    $compare .= sprintf('<li title="%s">', $arrFile['dbafs']['error']);
                                    $compare .= $arrFile['path'];
                                    $compare .= '</li>';
                                }

                                $compare .= '</ul>';
                                $compare .= '</li>';
                            }
                            $compare .= '</ul>';
                        }

                        $compare .= "</li>";
                        $compare .= "</ul>";
                    }

                    // Show file list only in debug mode
                    if ($GLOBALS['TL_CONFIG']['syncCto_debug_mode'] == true) {
                        if (is_array($this->arrListCompare) && (count((array) $this->arrListCompare['core']) != 0 || count((array) $this->arrListCompare['files']) != 0)) {
                            $compare .= '<br /><p class="tl_help">' . $intSendCount . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_4'] . '</p>';

                            if (($intSendCount - $intDelCount) != 0) {
                                $compare .= '<ul class="fileinfo">';

                                $compare .= "<li>";
                                $compare .= '<strong>' . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_6'] . '</strong>';
                                $compare .= "<ul>";

                                foreach ($this->arrListCompare as $strType => $arrLists) {
                                    foreach ($arrLists as $key => $value) {
                                        if ($value["transmission"] != SyncCtoEnum::FILETRANS_SEND) {
                                            continue;
                                        }

                                        if ($value["state"] == SyncCtoEnum::FILESTATE_DELETE) {
                                            continue;
                                        }

                                        $compare .= "<li>";
                                        $compare .= (mb_check_encoding($value["path"], 'UTF-8')) ? $value["path"] : utf8_encode($value["path"]);
                                        $compare .= "</li>";
                                    }
                                }
                                $compare .= "</ul>";
                                $compare .= "</li>";
                                $compare .= "</ul>";
                            }

                            if ($intDelCount != 0) {
                                $compare .= '<ul class="fileinfo">';

                                $compare .= "<li>";
                                $compare .= '<strong>' . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_7'] . '</strong>';
                                $compare .= "<ul>";

                                $compare .= "<li>";
                                $compare .= '<strong>' . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_7'] . '</strong>';
                                $compare .= "<ul>";

                                foreach ($this->arrListCompare as $strType => $arrLists) {
                                    foreach ($arrLists as $key => $value) {
                                        if ($value["transmission"] != SyncCtoEnum::FILETRANS_SEND) {
                                            continue;
                                        }

                                        if ($value["state"] != SyncCtoEnum::FILESTATE_DELETE) {
                                            continue;
                                        }

                                        $compare .= "<li>";
                                        $compare .= (mb_check_encoding($value["path"], 'UTF-8')) ? $value["path"] : utf8_encode($value["path"]);
                                        $compare .= "</li>";
                                    }
                                }

                                $compare .= "</ul>";
                                $compare .= "</li>";

                                $compare .= "<li>";
                                $compare .= '<strong>' . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_9'] . '</strong>';
                                $compare .= "<ul>";

                                foreach ($this->arrListCompare as $strType => $arrLists) {
                                    foreach ($arrLists as $key => $value) {
                                        if ($value["transmission"] != SyncCtoEnum::FILETRANS_SEND) {
                                            continue;
                                        }

                                        if ($value["state"] != SyncCtoEnum::FILESTATE_FOLDER_DELETE) {
                                            continue;
                                        }

                                        $compare .= "<li>";
                                        $compare .= (mb_check_encoding($value["path"], 'UTF-8')) ? $value["path"] : utf8_encode($value["path"]);
                                        $compare .= "</li>";
                                    }
                                }

                                $compare .= "</ul>";
                                $compare .= "</li>";

                                $compare .= "</ul>";
                                $compare .= "</li>";
                                $compare .= "</ul>";
                            }

                            // Not send and still waiting
                            if ($intWaitCount != 0) {
                                $compare .= '<br /><p class="tl_help">' . $intWaitCount . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_5'] . '</p>';
                                $compare .= '<ul class="fileinfo">';
                                $compare .= "<li>";
                                $compare .= '<strong>' . $GLOBALS['TL_LANG']['tl_syncCto_sync']["step_5"]['description_8'] . '</strong>';
                                $compare .= "<ul>";

                                foreach ($this->arrListCompare as $strType => $arrLists) {
                                    foreach ($arrLists as $key => $value) {
                                        if ($value["transmission"] != SyncCtoEnum::FILETRANS_WAITING) {
                                            continue;
                                        }

                                        $compare .= "<li>";
                                        $compare .= (mb_check_encoding($value["path"], 'UTF-8')) ? $value["path"] : utf8_encode($value["path"]);
                                        $compare .= "</li>";
                                    }
                                }

                                $compare .= "</ul>";
                                $compare .= "</li>";
                                $compare .= "</ul>";
                            }
                        }
                    }

                    $this->objData->setHtml($compare);

                    // Set finished msg
                    $arrClientLink = Database::getInstance()
                                             ->prepare("SELECT * FROM tl_synccto_clients WHERE id=?")
                                             ->limit(1)
                                             ->execute($this->intClientID)
                                             ->fetchAllAssoc()
                    ;

                    $strLink = vsprintf('<a href="%s:%s%s" target="_blank" style="text-decoration:underline;">', [$arrClientLink[0]['address'], $arrClientLink[0]['port'], $arrClientLink[0]['path']]);

                    $this->objData->nextStep();
                    $this->objData->setTitle($GLOBALS['TL_LANG']['MSC']['complete']);
                    $this->objData->setDescription(vsprintf($GLOBALS['TL_LANG']['tl_syncCto_sync']['complete_client'], [$strLink, "</a>"]));
                    $this->objData->setState(SyncCtoEnum::WORK_OK);

                    break;
            }
        } catch (Exception $exc) {
            $this->objStepPool->increaseSubStep();

            $this->log(vsprintf("Error on synchronization client ID %s with msg: %s", [Input::get("id"), $exc->getMessage()]), __CLASS__ . " " . __FUNCTION__, "ERROR");
        }
    }


    /**
     * Parse size
     *
     * @see http://us2.php.net/manual/en/function.ini-get.php#example-501
     */
    static public function parseSize($size)
    {
        if (!is_string($size)) {
            $size = (string) $size;
        }

        if ((int) $size === -1) {
            return PHP_INT_MAX;
        }

        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1]);
        $size = (int) $size;
        switch ($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $size *= 1024;
            case 'm':
                $size *= 1024;
            case 'k':
                $size *= 1024;
        }

        return $size;
    }

    /*
     * End SyncCto Sync. From
     * -------------------------------------------------------------------------
     */
}