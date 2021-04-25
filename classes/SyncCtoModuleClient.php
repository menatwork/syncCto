<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

use \Contao\Controller;
use \Contao\CoreBundle\Framework\ContaoFramework;
use \Contao\Input;
use \Contao\System;
use \MenAtWork\SyncCto\Services\ClientFactory;
use MenAtWork\SyncCto\Services\ContentDataFactory;
use MenAtWork\SyncCto\Services\RunnerDataFactory;
use MenAtWork\SyncCto\Steps\Runner;
use MenAtWork\SyncCto\Steps\StepData;

/**
 * Class for client interaction
 */
class SyncCtoModuleClient extends \Contao\BackendModule
{
    /**
     * Template for the steps.
     *
     * @var string
     */
    protected $strTemplate = 'be_syncCto_steps';

    /**
     * @var bool
     */
    private $allMode = false;

    /**
     * @var Input
     */
    private $input;

    /**
     * @var Controller
     */
    private $controller;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var ContentDataFactory
     */
    private $contentDataFactory;

    /**
     * @var RunnerDataFactory
     */
    private $runnerDataFactory;

    /**
     * @var Runner
     */
    private $runner;

    /**
     * @var array
     */
    private $syncSettings;

    /**
     * @var array
     */
    private $clientInformation;

    /**
     * Constructor
     *
     * @param DataContainer $objDc
     */
    public function __construct(DataContainer $objDc = null)
    {
        parent::__construct($objDc);

        /** @var ContaoFramework $contaoFramework */
        $contaoFramework  = System::getContainer()->get('contao.framework');
        $this->input      = $contaoFramework->getAdapter(Input::class);
        $this->controller = $contaoFramework->getAdapter(Controller::class);
        $this->session    = System::getContainer()->get('session');

        System::loadLanguageFile('tl_syncCto_steps');
        System::loadLanguageFile('tl_syncCto_check');

        $this->clientFactory      = System::getContainer()->get(ClientFactory::class);
        $this->contentDataFactory = System::getContainer()->get(ContentDataFactory::class);
        $this->runnerDataFactory  = System::getContainer()->get(RunnerDataFactory::class);
    }

    /**
     * Load the settings from the mask.
     *
     * @param $clientId
     */
    protected function loadSyncSettings($clientId)
    {
        $this->syncSettings = $this->session->get("syncCto_SyncSettings_" . $clientId);

        if (!is_array($this->syncSettings)) {
            $this->syncSettings = [];
        }
    }

    /**
     * Save the settings from the mask.
     *
     * @param $clientId
     */
    protected function saveSyncSettings($clientId)
    {
        if (!is_array($this->syncSettings)) {
            $this->syncSettings = [];
        }

        $this->session->set("syncCto_SyncSettings_" . $clientId, $this->syncSettings);
    }

    /**
     * Load some meta information.
     *
     * @param $clientId
     */
    protected function loadClientInformation($clientId)
    {
        $this->clientInformation = $this->session->get("syncCto_ClientInformation_" . $clientId);

        if (!is_array($this->clientInformation)) {
            $this->clientInformation = [];
        }
    }

    /**
     * Save the data nin the session.
     *
     * @param $clientID
     */
    protected function saveClientInformation($clientID)
    {
        $this->session->set("syncCto_ClientInformation_" . $clientID, $this->clientInformation);
    }

    /**
     * @param $clientID
     */
    protected function resetClientInformation($clientID)
    {
        $this->session->set("syncCto_ClientInformation_" . $clientID, false);
    }

    /**
     * @inheritDoc
     *
     * @throws \Doctrine\DBAL\Exception
     */
    protected function compile()
    {
        $id        = (int)$this->input->get('id');
        $act       = $this->input->get("act");
        $table     = $this->input->get('table');
        $mode      = $this->input->get('mode');
        $step      = (int)$this->input->get('step');
        $next      = $this->input->get('next');
        $direction = 'none';

        // Check if start is set.
        if ($act != "start" && $table != 'tl_syncCto_clients_showExtern') {
            $_SESSION["TL_ERROR"] = [$GLOBALS['TL_LANG']['ERR']['call_directly']];
            $this->controller->redirect("contao/main.php?do=synccto_clients");
        }

        // We need the id, 'cause it is the client id.
        if (empty($id)) {
            $_SESSION["TL_ERROR"] = [$GLOBALS['TL_LANG']['ERR']['call_directly']];
            $this->controller->redirect("contao/main.php?do=synccto_clients");
        }

        $this->startRun($id, $table, $step);

        $this->runner->run();

        $this->endRun($id);
        $this->setTemplateVars();
    }

    /**
     * @param int    $clientId
     *
     * @param string $function
     *
     * @param int    $step
     *
     * @throws \Doctrine\DBAL\Exception
     */
    private function startRun(int $clientId, string $function, int $step): void
    {
        $this->runner = new Runner();

        switch ($function) {
            case "tl_syncCto_clients_syncTo":
                $this->runner->setSource($this->clientFactory->getLocaleClient());
                $this->runner->setDestination($this->clientFactory->getRemoteClient($clientId));
                break;

            case "tl_syncCto_clients_syncFrom":
                $this->runner->setSource($this->clientFactory->getRemoteClient($clientId));
                $this->runner->setDestination($this->clientFactory->getLocaleClient());
                break;

            default :
                $_SESSION["TL_ERROR"][] = $GLOBALS['TL_LANG']['ERR']['unknown_function'];
                $this->controller->redirect("contao/main.php?do=synccto_clients");
                break;
        }

        if (0 == $step) {
            $step      = 1;
            $url       = sprintf(
                'contao/main.php?do=synccto_clients&amp;table=tl_syncCto_clients_syncTo&amp;act=start&amp;id=%s',
                $clientId
            );
            $goBackUrl = \Environment::get('base') . "contao/main.php?do=synccto_clients";

            $contentContainer = $this->contentDataFactory->createNewContainer();
            $contentContainer->setError(false);
            $contentContainer->setErrorMessage('');
            $contentContainer->setAbort(false);
            $contentContainer->setFinished(false);
            $contentContainer->setRefresh(false);
            $contentContainer->setUrl($url);
            $contentContainer->setGoBack($goBackUrl);
            $contentContainer->setHeadline($GLOBALS['TL_LANG']['tl_syncCto_sync']['edit'] ?? '');
            $contentContainer->setInformation('');
            $contentContainer->setStep($step);
            $contentContainer->setStart(microtime(true));

            $this->resetClientInformation($clientId);
        } else {
            $contentContainer = $this->contentDataFactory->loadContainer();
            $this->loadClientInformation($clientId);
        }

        $this->runner->setContentData($contentContainer);
        $this->loadSyncSettings($clientId);
    }

    /**
     * @param int $clientId
     */
    private function endRun(int $clientId): void
    {
        $this->contentDataFactory->saveContainer($this->runner->getContentData());
        $this->saveSyncSettings($clientId);
        $this->saveClientInformation($clientId);
    }

    /**
     * Set all vars for the template.
     */
    private function setTemplateVars()
    {
        // Controlling and links.
        $this->Template->showControl    = true;
        $this->Template->tryAgainLink   = \Environment::get('requestUri') . (($this->allMode) ? '&mode=all' : '');
        $this->Template->abortLink      = \Environment::get('requestUri') . "&abort=true" . (($this->allMode) ? '&mode=all' : '');
        $this->Template->nextClientLink = \Environment::get('requestUri') . "&abort=true" . (($this->allMode) ? '&mode=all&next=1' : '');

        $this->Template->sourceClient      = $this->runner->getSource()->getTitle();
        $this->Template->destinationClient = $this->runner->getDestination()->getTitle();

        $this->Template->frontendData = $this->runner->getContentData();
        $this->Template->steps        = $this->runner->getContentData()->getAllStepContent();

        $contentData            = $this->runner->getContentData();
        $this->Template->goBack = $contentData->getGoBack();
        $this->Template->data   = $contentData->getAllStepContent();
//        $this->Template->subStep     = $this->objStepPool->step;
        $this->Template->error       = $contentData->isError();
        $this->Template->error_msg   = $contentData->getErrorMessage();
        $this->Template->refresh     = $contentData->isRefresh();
        $this->Template->url         = $contentData->getUrl();
        $this->Template->start       = $contentData->getStart();
        $this->Template->headline    = $contentData->getHeadline();
        $this->Template->information = $contentData->getInformation();
        $this->Template->finished    = $contentData->isFinished();
        $this->Template->allMode     = false; // TODO
    }
}

