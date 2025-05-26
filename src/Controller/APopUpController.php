<?php

namespace MenAtWork\SyncCto\Controller;

use Contao\Backend;
use Contao\BackendUser;
use Contao\Input;
use Contao\System;

abstract class APopUpController
{

    public function initSystem()
    {
        System::getContainer()->get('contao.framework')->initialize();
        System::loadLanguageFile('default');
    }

    public function setupTemplate()
    {
        // Setup.
        \define('TL_ASSETS_URL', '');

        // Set language from get or user
        if (Input::get('language') != '') {
            $GLOBALS['TL_LANGUAGE'] = Input::get('language');
        } else {
            $GLOBALS['TL_LANGUAGE'] = BackendUser::getInstance()->language;
        }

        // Clear all we want a clear array for this windows.
        $GLOBALS['TL_CSS'] = [];
        $GLOBALS['TL_JAVASCRIPT'] = [];

        // Set stylesheets.
        $GLOBALS['TL_CSS'][] = 'system/themes/' . Backend::getTheme() . '/basic.css';
        $GLOBALS['TL_CSS'][] = 'bundles/synccto/css/compare.css';

        // Set javascript.
        $GLOBALS['TL_JAVASCRIPT'][] = 'assets/mootools/js/mootools-core.min.js';
        $GLOBALS['TL_JAVASCRIPT'][] = 'assets/mootools/js/mootools-more.min.js';
        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/synccto/js/compare.js';
        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/synccto/js/htmltable.js';
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
}