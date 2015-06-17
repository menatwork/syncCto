<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL 
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_syncCto_clients_showExtern'] = array
(
    // Config
    'config' => array
    (
        'dataContainer'           => 'General',
        'closed'                  => true,
        'disableSubmit'           => false
    ),
    'dca_config' => array
    (
        'data_provider' => array
        (
            'default' => array
            (
                'class'  => 'ContaoCommunityAlliance\DcGeneral\Data\NoOpDataProvider',
                'source' => 'tl_syncCto_clients_showExtern'
            ),
        ),
    ),
);