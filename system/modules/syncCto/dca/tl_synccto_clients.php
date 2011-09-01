<?php

if (!defined('TL_ROOT'))
    die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  MEN AT WORK 2011
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */
$GLOBALS['TL_DCA']['tl_synccto_clients'] = array(
    // Config
    'config' => array(
        'dataContainer' => 'Table',
        'enableVersioning' => true,
        'onload_callback' => array(
            array('tl_synccto_clients', 'checkPermissionClient'),
            array('tl_synccto_clients', 'checkPermissionClientCreate'),
        ),
    ),
    // List
    'list' => array(
        'sorting' => array(
            'mode' => 1,
            'fields' => array('title'),
            'flag' => 2,
            'panelLayout' => 'filter;search,limit',
        ),
        'label' => array(
            'fields' => array('title', 'id', 'address'),
            'format' => '<img class="ping" src="system/modules/syncCto/html/empty.png" alt="" /> %s <span style="color: #aaaaaa; padding-left: 3px;">(' . $GLOBALS['TL_LANG']['tl_synccto_clients']['id'][0] . ': %s, ' . $GLOBALS['TL_LANG']['tl_synccto_clients']['address'][0] . ': <span>%s</span>)</span>',
        ),
        'global_operations' => array(
            'all' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_synccto_clients']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            )
        ),
        'operations' => array(
            'edit' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_synccto_clients']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif',
                'button_callback' => array('tl_synccto_clients', 'checkPermission_client_edit'),
            ),
            'copy' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_synccto_clients']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.gif',
                'button_callback' => array('tl_synccto_clients', 'checkPermission_client_copy'),
            ),
            'delete' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_synccto_clients']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
                'button_callback' => array('tl_synccto_clients', 'checkPermission_client_delete'),
            ),
            'show' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_synccto_clients']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
                'button_callback' => array('tl_synccto_clients', 'checkPermission_client_show'),
            ),
            'syncFrom' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_synccto_clients']['syncFrom'],
                'href' => 'table=tl_syncCto_clients_syncFrom',
                'icon' => 'system/modules/syncCto/html/iconSyncFrom.png',
                'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['tl_synccto_clients']['syncFromConfirm'] . '\')) return false; Backend.getScrollOffset();"',
                'button_callback' => array('tl_synccto_clients', 'checkPermission_client_syncFrom'),
            ),
            'syncTo' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_synccto_clients']['syncTo'],
                'href' => '&table=tl_syncCto_clients_syncTo',
                'icon' => 'system/modules/syncCto/html/iconSyncTo.png',
                'button_callback' => array('tl_synccto_clients', 'checkPermission_client_syncTo'),
            ),
        )
    ),
    // Palettes
    'palettes' => array(
        'default' => '{title_legend},title,description;{connection_label},address,path,port,transmission,codifyengine;{user_label},seckey;'
    ),
    // Fields
    'fields' => array(
        'title' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_synccto_clients']['title'],
            'inputType' => 'text',
            'search' => true,
            'exclude' => true,
            'eval' => array('mandatory' => true, 'maxlength' => 64)
        ),
        'description' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_synccto_clients']['description'],
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => array('style' => 'height:80px')
        ),
        'address' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_synccto_clients']['address'],
            'inputType' => 'text',
            'default' => 'http://',
            'search' => true,
            'exclude' => true,
            'eval' => array('trailingSlash' => false, 'mandatory' => true)
        ),
        'port' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_synccto_clients']['port'],
            'inputType' => 'text',
            'search' => true,
            'default' => '80',
            'exclude' => true,
            'eval' => array('mandatory' => true)
        ),
        'path' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_synccto_clients']['path'],
            'inputType' => 'text',
            'default' => 'syncCto.php',
            'exclude' => true,
            'eval' => array('mandatory' => true)
        ),
        'codifyengine' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_synccto_clients']['codifyengine'],
            'inputType' => 'select',
            'exclude' => true,
            'options_callback' => array("tl_synccto_clients", "optioncallCodifyengines"),
            'eval' => array('mandatory' => true),
        ),
        'seckey' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_synccto_clients']['seckey'],
            'inputType' => 'text',
            'exclude' => true,
            'eval' => array('mandatory' => true, 'maxlength' => 64)
        ),
    )
);

/**
 * Callback and fallback if function mdecrypt not exists   
 */
if (!function_exists("mdecrypt_generic"))
{
    unset($GLOBALS['TL_DCA']['tl_synccto_clients']['fields']['password']['eval']['encrypt']);
    $GLOBALS['TL_DCA']['tl_synccto_clients']['fields']['password']['load_callback'] = array(array("SyncCtoCallback", "loadcallUserPassword"));
    $GLOBALS['TL_DCA']['tl_synccto_clients']['fields']['password']['save_callback'] = array(array("SyncCtoCallback", "savecallUserPassword"));
}

class tl_synccto_clients extends Backend
{

    // Constructor and singelten pattern
    public function __construct()
    {
        // Import Contao classes
        $this->BackendUser = BackendUser::getInstance();

        parent::__construct();
    }

    /* -------------------------------------------------------------------------
     * Ping client status
     */

    public function pingClientStatus($strAction)
    {
        if ($strAction == 'syncCtoPing')
        {
            $objRequest = new Request();
            $objRequest->send($this->Input->post('hostIP'));
            echo ($objRequest->code == '200') ? "true" : "false";
            exit();
        }
    }

    /**
     * User for permissioncheck from operation callbacks.
     * 
     * @param string $name Name of function
     * @param array $arguments Arguments
     * @return mixed 
     */
    public function __call($name, $arguments)
    {
        $arrSplitName = explode("_", $name);

        //checkPermission_clients_edit
        if ($arrSplitName[0] == 'checkPermission' && $arrSplitName[1] == "client")
        {
            return $this->checkPermissionClientButton($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4], $arguments[5], $arrSplitName[2]);
        }
    }

    /**
     * Permisson check for the client overview page.
     * 
     * @param type $row
     * @param type $href
     * @param type $label
     * @param type $title
     * @param type $icon
     * @param type $attributes
     * @param type $operations
     * @return type 
     */
    public function checkPermissionClientButton($row, $href, $label, $title, $icon, $attributes, $operations)
    {
        if ($this->BackendUser->hasAccess($operations, 'syncCto_clients_p') == true)
        {
            return '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $this->generateImage($icon, $label) . '</a> ';
        }
        else
        {
            switch ($operations)
            {
                case 'syncTo' :
                case 'syncFrom' :
                    return $this->generateImage(preg_replace('/\.png$/i', '_.png', $icon)) . ' ';
                    break;


                default:
                    return $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
                    break;
            }
        }
    }

    /**
     * Check permissions to edit table tl_content
     */
    public function checkPermissionClient()
    {
        if ($this->BackendUser->isAdmin)
        {
            return;
        }

        // Set root IDs
        if (!is_array($this->BackendUser->syncCto_clients) || count($this->BackendUser->syncCto_clients) < 1)
        {
            $root = array(0);
        }
        else
        {
            $root = $this->BackendUser->syncCto_clients;
        }

        $GLOBALS['TL_DCA']['tl_synccto_clients']['list']['sorting']['root'] = $root;

        if ($this->BackendUser->hasAccess($this->Input->get('act'), 'syncCto_clients_p') == true || strlen($this->Input->get('act')) == 0)
        {
            return;
        }
        else
        {
            $this->log('Not enough permissions to ' . $this->Input->get('act') . ' syncCto clients', 'tl_syncCto_clients checkPermissionClient', TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }
    }

    public function checkPermissionClientCreate()
    {
        if (!$this->BackendUser->hasAccess('create', 'syncCto_clients_p'))
            $GLOBALS['TL_DCA']['tl_synccto_clients']['config'] = array_unique(array_merge(array('closed' => true), $GLOBALS['TL_DCA']['tl_synccto_clients']['config']));
    }

    public function optioncallCodifyengines()
    {
        $arrReturn = array();

        foreach ($GLOBALS["CTOCOM_ENGINE"] as $key => $value)
        {
            $arrReturn[$key] = $value["name"];
        }

        asort($arrReturn);

        return $arrReturn;
    }

}

?>