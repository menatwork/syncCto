<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

use Contao\DC_Table;
use MenAtWork\SyncCto\Contao\Table\SyncCtoClients;

$GLOBALS['TL_DCA']['tl_synccto_clients'] = [
    'config'      => [
        'dataContainer'    => DC_Table::class,
        'enableVersioning' => true,
        'onload_callback'  => [
            [SyncCtoClients::class, 'checkClientStatus'],
            [SyncCtoClients::class, 'checkPermissionClient'],
            [SyncCtoClients::class, 'checkPermissionClientCreate'],
        ],
        'sql'              => [
            'keys' => [
                'id'     => 'primary',
                'tstamp' => 'index',
            ]
        ]
    ],
    'list'        => [
        'sorting'           => [
            'mode'        => 1,
            'fields'      => ['title'],
            'flag'        => 2,
            'panelLayout' => 'filter;search,limit',
        ],
        'label'             => [
            'fields'         => ['title', 'id', 'address', 'port', 'id'],
            'format'         => '<img class="ping" src="bundles/synccto/images/js/gray.png" alt="" /> %s <span style="color: #aaaaaa; padding-left: 3px;">(' . ($GLOBALS['TL_LANG']['tl_syncCto_clients']['id'][0] ?? '') . ': %s, ' . ($GLOBALS['TL_LANG']['tl_syncCto_clients']['address'][0] ?? '') . ': <span title="%s:%s">[URL]</span><span class="client-id invisible">%s</span>)</span>',
            'label_callback' => [SyncCtoClients::class, 'setLabel']
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            ],
        ],
        'operations'        => [
            'edit'       => [
                'label'           => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['edit'],
                'href'            => 'act=edit',
                'icon'            => 'edit.gif',
                'button_callback' => [SyncCtoClients::class, 'checkPermission_client_edit'],
            ],
            'copy'       => [
                'label'           => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['copy'],
                'href'            => 'act=copy',
                'icon'            => 'copy.gif',
                'button_callback' => [SyncCtoClients::class, 'checkPermission_client_copy'],
            ],
            'delete'     => [
                'label'           => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['delete'],
                'href'            => 'act=delete',
                'icon'            => 'delete.gif',
                'attributes'      => 'onclick="if (!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\')) return false; Backend.getScrollOffset();"',
                'button_callback' => [SyncCtoClients::class, 'checkPermission_client_delete'],
            ],
            'showExtern' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['showExtern'],
                'href'            => '&table=tl_syncCto_clients_showExtern&act=start',
                'icon'            => 'bundles/synccto/images/nav/iconCheck.png',
                'button_callback' => [SyncCtoClients::class, 'checkPermission_client_showExtern'],
            ],
            'syncFrom'   => [
                'label'           => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['syncFrom'],
                'href'            => '&table=tl_syncCto_clients_syncFrom&act=startSync',
                'icon'            => 'bundles/synccto/images/nav/iconSyncFrom.png',
                'attributes'      => 'onclick="if (!confirm(\'' . ($GLOBALS['TL_LANG']['tl_syncCto_clients']['syncFromConfirm'] ?? '') . '\')) return false; Backend.getScrollOffset();"',
                'button_callback' => [SyncCtoClients::class, 'checkPermission_client_syncFrom'],
            ],
            'syncTo'     => [
                'label'           => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['syncTo'],
                'href'            => '&table=tl_syncCto_clients_syncTo&act=startSync',
                'icon'            => 'bundles/synccto/images/nav/iconSyncTo.png',
                'button_callback' => [SyncCtoClients::class, 'checkPermission_client_syncTo'],
            ],
        ]
    ],
    'palettes'    => [
        '__selector__' => ['http_auth'],
        'default'      => '{client_legend},apikey,title;{connection_legend},address,port,codifyengine;{expert_legend:hide},http_auth',
    ],
    'subpalettes' => [
        'http_auth' => 'http_username,http_password',
    ],
    'fields'      => [
        'id'               => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment'
        ],
        'tstamp'           => [
            'sql' => 'int(10) unsigned NOT NULL default \'0\''
        ],
        'cookie'           => [
            'sql' => 'longtext NULL'
        ],
        'syncTo_user'      => [
            'sql' => 'int(10) unsigned NOT NULL default \'0\''
        ],
        'syncFrom_user'    => [
            'sql' => 'int(10) unsigned NOT NULL default \'0\''
        ],
        'syncTo_tstamp'    => [
            'sql' => 'int(10) unsigned NOT NULL default \'0\''
        ],
        'syncFrom_tstamp'  => [
            'sql' => 'int(10) unsigned NOT NULL default \'0\''
        ],
        'client_timestamp' => [
            'sql' => 'blob NULL'
        ],
        'server_timestamp' => [
            'sql' => 'blob NULL'
        ],
        'apikey'           => [
            'label'       => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['apikey'],
            'explanation' => 'apiKey',
            'inputType'   => 'text',
            'exclude'     => true,
            'eval'        => ['helpwizard' => true, 'mandatory' => true, 'maxlength' => '64', 'tl_class' => 'w50'],
            'sql'         => 'varchar(64) NOT NULL default \'\''
        ],
        'title'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['title'],
            'inputType' => 'text',
            'search'    => true,
            'exclude'   => true,
            'eval'      => ['mandatory' => true, 'maxlength' => '64', 'tl_class' => 'w50'],
            'sql'       => 'varchar(64) NULL'
        ],
        'address'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['address'],
            'inputType' => 'text',
            'default'   => 'https://',
            'search'    => true,
            'exclude'   => true,
            'eval'      => ['trailingSlash' => false, 'mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => 'text NOT NULL'
        ],
        'port'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['port'],
            'inputType' => 'text',
            'search'    => true,
            'default'   => '80',
            'exclude'   => true,
            'eval'      => ['rgxp' => 'digit', 'mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => 'int(10) unsigned NOT NULL default \'0\''
        ],
        'codifyengine'     => [
            'label'            => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['codifyengine'],
            'inputType'        => 'select',
            'explanation'      => 'security',
            'exclude'          => true,
            'options_callback' => [SyncCtoClients::class, 'callCodifyengines'],
            'eval'             => ['mandatory' => true, 'tl_class' => 'w50', 'helpwizard' => true],
            'sql'              => 'varchar(128) NOT NULL default \'\''
        ],
        'http_auth'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['http_auth'],
            'inputType' => 'checkbox',
            'exclude'   => true,
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'clr'],
            'sql'       => 'char(1) NOT NULL default \'\''
        ],
        'http_username'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['http_username'],
            'inputType' => 'text',
            'exclude'   => true,
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => 'varchar(128) NOT NULL default \'\''
        ],
        'http_password'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['http_password'],
            'inputType' => 'text',
            'exclude'   => true,
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => 'varchar(128) NOT NULL default \'\''
        ],
    ]
];
