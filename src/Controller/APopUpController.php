<?php

namespace MenAtWork\SyncCto\Controller;

use Contao\Backend;
use Contao\BackendPopup;
use Contao\BackendTemplate;
use Contao\BackendUser;
use Contao\Config;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Image\Preview\MissingPreviewProviderException;
use Contao\CoreBundle\Image\Preview\UnableToGeneratePreviewException;
use Contao\Database;
use Contao\Date;
use Contao\Dbafs;
use Contao\Environment;
use Contao\File;
use Contao\FilesModel;
use Contao\Folder;
use Contao\Image\PictureConfiguration;
use Contao\Image\PictureConfigurationItem;
use Contao\Image\ResizeConfiguration;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

abstract class APopUpController extends Backend
{
    /**
     * The title of the popup.
     *
     * @var string
     */
    protected string $title = 'SyncCto';

    /**
     * The template to use.
     *
     * @var string
     */
    protected string $template = 'be_syncCto_popup';

    /**
     * The session.
     *
     * @var SessionInterface
     */
    protected SessionInterface $session;

    /**
     * The template object.
     *
     * @var BackendTemplate
     */
    protected BackendTemplate $baseTemplate;

    /**
     * APopUpController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if (!System::getContainer()->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException('Access denied');
        }

        // Set language from get or user
        if (Input::get('language') != '') {
            $GLOBALS['TL_LANGUAGE'] = Input::get('language');
        } else {
            $GLOBALS['TL_LANGUAGE'] = BackendUser::getInstance()->language;
        }
        System::loadLanguageFile('default');

        /** @var RequestStack $requestStack */
        $requestStack = System::getContainer()->get('request_stack');
        $this->session = $requestStack->getSession();
    }

    /**
     * Get teh request token.
     *
     * @return string
     */
    public function getRequestToken(): string
    {
        return System::getContainer()
                     ->get('contao.csrf.token_manager')
                     ->getDefaultTokenValue()
        ;
    }

    /**
     * Do the action.
     */
    abstract protected function doAction(): string;

    /**
     * Run the controller and parse the template
     *
     * @return Response
     */
    public function runAction(): Response
    {
        $container = System::getContainer();
        $this->baseTemplate = new BackendTemplate($this->template);

        // Clear all we want a clear array for this windows.
        if (empty($GLOBALS['TL_CSS'])) {
            $GLOBALS['TL_CSS'] = [];
        }
        if (empty($GLOBALS['TL_JAVASCRIPT'])) {
            $GLOBALS['TL_JAVASCRIPT'] = [];
        }

        $this->baseTemplate->content = $this->doAction();

        // Set stylesheets.
        $GLOBALS['TL_CSS'][] = 'bundles/synccto/css/compare.css';

        // Set javascript.
        $GLOBALS['TL_JAVASCRIPT'][] = 'assets/mootools/js/mootools-core.min.js';
        $GLOBALS['TL_JAVASCRIPT'][] = 'assets/mootools/js/mootools-more.min.js';
        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/synccto/js/compare.js';
        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/synccto/js/htmltable.js';

        $this->baseTemplate->theme = Backend::getTheme();
        $this->baseTemplate->base = Environment::get('base');
        $this->baseTemplate->language = $GLOBALS['TL_LANGUAGE'];
        $this->baseTemplate->title = StringUtil::specialchars(
            ($this->title ?? $GLOBALS['TL_CONFIG']['websiteTitle'])
        );
        $this->baseTemplate->headline = basename($this->strFile ?? '');
        $this->baseTemplate->host = Backend::getDecodedHostname();
        $this->baseTemplate->charset = $container->getParameter('kernel.charset');
        $this->baseTemplate->labels = (object) $GLOBALS['TL_LANG']['MSC'];
        $this->baseTemplate->requestToken = $this->getRequestToken();;

        return $this->baseTemplate->getResponse();
    }
}