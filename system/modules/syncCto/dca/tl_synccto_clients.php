<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_synccto_clients'] = array
(
    // Config
    'config' => array
    (
        'dataContainer'           => 'Table',
        'enableVersioning'        => true,
        'onload_callback' => array
        (
            array('tl_synccto_clients', 'checkClientStatus'),
            array('tl_synccto_clients', 'checkPermissionClient'),
            array('tl_synccto_clients', 'checkPermissionClientCreate'),
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary'
            )
        )
    ),
    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                => 1,
            'fields'              => array('title'),
            'flag'                => 2,
            'panelLayout'         => 'filter;search,limit',
        ),
        'label' => array
        (
            'fields'              => array('title', 'id', 'address', 'path', 'id'),
            'format'              => '<img class="ping" src="system/modules/syncCto/assets/images/js/gray.png" alt="" /> %s <span style="color: #aaaaaa; padding-left: 3px;">(' . $GLOBALS['TL_LANG']['tl_syncCto_clients']['id'][0] . ': %s, ' . $GLOBALS['TL_LANG']['tl_syncCto_clients']['address'][0] . ': <span title="%s%s">[URL]</span><span class="client-id invisible">%s</span>)</span>',
            'label_callback'      => array('tl_synccto_clients', 'setLabel')
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'label'           => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['all'],
                'href'            => 'act=select',
                'class'           => 'header_edit_all',
                'attributes'      => 'onclick="Backend.getScrollOffset();"'
            ),
            /* 'syncToAll' => array
            (
                'label'           => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['syncToAll'],
                'href'            => '?do=synccto_clients&table=tl_syncCto_clients_syncTo&act=start&step=0&mode=all&init=1',
                'class'           => 'header_sync_all',
                'icon'            => 'system/modules/syncCto/assets/images/nav/iconSyncTo.png',
            ) */
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'           => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['edit'],
                'href'            => 'act=edit',
                'icon'            => 'edit.gif',
                'button_callback' => array('tl_synccto_clients', 'checkPermission_client_edit'),
            ),
            'copy' => array
            (
                'label'           => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['copy'],
                'href'            => 'act=copy',
                'icon'            => 'copy.gif',
                'button_callback' => array('tl_synccto_clients', 'checkPermission_client_copy'),
            ),
            'delete' => array
            (
                'label'           => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['delete'],
                'href'            => 'act=delete',
                'icon'            => 'delete.gif',
                'attributes'      => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
                'button_callback' => array('tl_synccto_clients', 'checkPermission_client_delete'),
            ),
            'showExtern' => array
            (
                'label'           => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['showExtern'],
                'href'            => '&table=tl_syncCto_clients_showExtern&act=start',
                'icon'            => 'system/modules/syncCto/assets/images/nav/iconCheck.png',
                'button_callback' => array('tl_synccto_clients', 'checkPermission_client_showExtern'),
            ),
            'syncFrom' => array
            (
                'label'           => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['syncFrom'],
                'href'            => '&table=tl_syncCto_clients_syncFrom&act=startSync',
                'icon'            => 'system/modules/syncCto/assets/images/nav/iconSyncFrom.png',
                'attributes'      => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['tl_syncCto_clients']['syncFromConfirm'] . '\')) return false; Backend.getScrollOffset();"',
                'button_callback' => array('tl_synccto_clients', 'checkPermission_client_syncFrom'),
            ),
            'syncTo' => array
            (
                'label'           => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['syncTo'],
                'href'            => '&table=tl_syncCto_clients_syncTo&act=startSync',
                'icon'            => 'system/modules/syncCto/assets/images/nav/iconSyncTo.png',
                'button_callback' => array('tl_synccto_clients', 'checkPermission_client_syncTo'),
            ),
        )
    ),
    // Palettes
    'palettes' => array
    (
        '__selector__'            => array('http_auth'),
        'default'                 => '{client_legend},apikey,title;{connection_legend},address,path,port,codifyengine;{expert_legend:hide},http_auth',
    ),
    'subpalettes' => array
    (
        'http_auth'               => 'http_username,http_password',
    ),
    // Fields
    'fields' => array
    (
        'id'               => array(
            'sql'                 => 'int(10) unsigned NOT NULL auto_increment'
        ),
        'tstamp'           => array(
            'sql'                 => "int(10) unsigned NOT NULL default '0'"
        ),
        'cookie'           => array(
            'sql'                 => 'longtext NULL'
        ),
        'syncTo_user'      => array(
            'sql'                 => "int(10) unsigned NOT NULL default '0'"
        ),
        'syncFrom_user'    => array(
            'sql'                 => "int(10) unsigned NOT NULL default '0'"
        ),
        'syncTo_tstamp'    => array(
            'sql'                 => "int(10) unsigned NOT NULL default '0'"
        ),
        'syncFrom_tstamp'  => array(
            'sql'                 => "int(10) unsigned NOT NULL default '0'"
        ),
        'client_timestamp' => array(
            'sql'                 => 'blob NULL'
        ),
        'server_timestamp' => array(
            'sql'                 => 'blob NULL'
        ),
        'apikey' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['apikey'],
            'explanation'         => 'apiKey',
            'inputType'           => 'text',
            'exclude'             => true,
            'eval'                => array('helpwizard' => true, 'mandatory' => true, 'maxlength' => '64', 'tl_class' => 'w50'),
            'sql'                 => "varchar(64) NOT NULL default ''"
        ),
        'title' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['title'],
            'inputType'           => 'text',
            'search'              => true,
            'exclude'             => true,
            'eval'                => array('mandatory' => true, 'maxlength' => '64', 'tl_class' => 'w50'),
            'sql'                 => "varchar(64) NOT NULL default ''"
        ),
        'address' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['address'],
            'inputType'           => 'text',
            'default'             => 'http://',
            'search'              => true,
            'exclude'             => true,
            'eval'                => array('trailingSlash' => false, 'mandatory' => true, 'tl_class' => 'w50'),
            'sql'                 => 'text NOT NULL'
        ),
        'path' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['path'],
            'inputType'           => 'text',
            'exclude'             => true,
            'eval'                => array('trailingSlash' => false, 'tl_class' => 'w50'),
            'save_callback' => array
            (
                array('tl_synccto_clients', 'checkFirstSlash')
            ),
            'sql'                 => "varchar(255) NOT NULL default ''"
        ),
        'port' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['port'],
            'inputType'           => 'text',
            'search'              => true,
            'default'             => '80',
            'exclude'             => true,
            'eval'                => array('rgxp' => 'digit', 'mandatory' => true, 'tl_class' => 'w50'),
            'sql'                 => "int(10) unsigned NOT NULL default '0'"
        ),
        'codifyengine' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['codifyengine'],
            'inputType'           => 'select',
            'explanation'         => 'security',
            'exclude'             => true,
            'options_callback'    => array("tl_synccto_clients", "callCodifyengines"),
            'eval'                => array('mandatory'  => true, 'tl_class'   => 'w50', 'helpwizard' => true),
            'sql'                 => "varchar(128) NOT NULL default ''"
        ),
        'http_auth'  => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['http_auth'],
            'inputType'           => 'checkbox',
            'exclude'             => true,
            'eval'                => array('submitOnChange' => true, 'tl_class'       => 'clr'),
            'sql'                 => "char(1) NOT NULL default ''"
        ),
        'http_username' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['http_username'],
            'inputType'           => 'text',
            'exclude'             => true,
            'eval'                => array('mandatory'     => true, 'tl_class'      => 'w50'),
            'sql'                 => "varchar(128) NOT NULL default ''"
        ),
        'http_password' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['http_password'],
            'inputType'           => 'text',
            'exclude'             => true,
            'eval'                => array('mandatory' => true, 'encrypt'   => true, 'tl_class'  => 'w50'),
            'sql'                 => "varchar(128) NOT NULL default ''"
        )
    )
);

/**
 * Class for syncCto clients
 *
 * @deprecated This class is deprecated since 3.3 and where remove in 4.0.
 *             Use \SyncCto\Contao\DataProvider\Table\SyncctoClients instead.
 */
class tl_synccto_clients extends \SyncCto\Contao\DataProvider\Table\SyncctoClients
{
}
