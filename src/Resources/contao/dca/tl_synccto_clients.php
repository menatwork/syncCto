<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;

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
            'fields'              => array('title', 'id', 'address', 'port', 'id'),
            'format'              => '<img class="ping" src="bundles/synccto/images/js/gray.png" alt="" /> %s <span style="color: #aaaaaa; padding-left: 3px;">(' . ($GLOBALS['TL_LANG']['tl_syncCto_clients']['id'][0] ?? '') . ': %s, ' . ($GLOBALS['TL_LANG']['tl_syncCto_clients']['address'][0] ?? '') . ': <span title="%s:%s">[URL]</span><span class="client-id invisible">%s</span>)</span>',
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
                'icon'            => 'bundles/synccto/images/nav/iconSyncTo.png',
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
                'attributes'      => 'onclick="if (!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\')) return false; Backend.getScrollOffset();"',
                'button_callback' => array('tl_synccto_clients', 'checkPermission_client_delete'),
            ),
            'showExtern' => array
            (
                'label'           => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['showExtern'],
                'href'            => '&table=tl_syncCto_clients_showExtern&act=start',
                'icon'            => 'bundles/synccto/images/nav/iconCheck.png',
                'button_callback' => array('tl_synccto_clients', 'checkPermission_client_showExtern'),
            ),
            'syncFrom' => array
            (
                'label'           => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['syncFrom'],
                'href'            => '&table=tl_syncCto_clients_syncFrom&act=startSync',
                'icon'            => 'bundles/synccto/images/nav/iconSyncFrom.png',
                'attributes'      => 'onclick="if (!confirm(\'' . ($GLOBALS['TL_LANG']['tl_syncCto_clients']['syncFromConfirm'] ?? '') . '\')) return false; Backend.getScrollOffset();"',
                'button_callback' => array('tl_synccto_clients', 'checkPermission_client_syncFrom'),
            ),
            'syncTo' => array
            (
                'label'           => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['syncTo'],
                'href'            => '&table=tl_syncCto_clients_syncTo&act=startSync',
                'icon'            => 'bundles/synccto/images/nav/iconSyncTo.png',
                'button_callback' => array('tl_synccto_clients', 'checkPermission_client_syncTo'),
            ),
        )
    ),
    // Palettes
    'palettes' => array
    (
        '__selector__'            => array('http_auth'),
        'default'                 => '{client_legend},apikey,title;{connection_legend},address,port,codifyengine;{expert_legend:hide},http_auth',
    ),
    'subpalettes' => array
    (
        'http_auth'               => 'http_username,http_password',
    ),
    // Fields
    'fields' => array
    (
        'apikey' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['apikey'],
            'explanation'         => 'apiKey',
            'inputType'           => 'text',
            'exclude'             => true,
            'eval'                => array('helpwizard' => true, 'mandatory' => true, 'maxlength' => '64', 'tl_class' => 'w50')
        ),
        'title' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['title'],
            'inputType'           => 'text',
            'search'              => true,
            'exclude'             => true,
            'eval'                => array('mandatory' => true, 'maxlength' => '64', 'tl_class' => 'w50')
        ),
        'address' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['address'],
            'inputType'           => 'text',
            'default'             => 'http://',
            'search'              => true,
            'exclude'             => true,
            'eval'                => array('trailingSlash' => false, 'mandatory' => true, 'tl_class' => 'w50')
        ),
        'port' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['port'],
            'inputType'           => 'text',
            'search'              => true,
            'default'             => '80',
            'exclude'             => true,
            'eval'                => array('rgxp' => 'digit', 'mandatory' => true, 'tl_class' => 'w50')
        ),
        'codifyengine' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['codifyengine'],
            'inputType'           => 'select',
            'explanation'         => 'security',
            'exclude'             => true,
            'options_callback'    => array("tl_synccto_clients", "callCodifyengines"),
            'eval'                => array('mandatory'  => true, 'tl_class'   => 'w50', 'helpwizard' => true),
        ),
        'http_auth'  => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['http_auth'],
            'inputType'           => 'checkbox',
            'exclude'             => true,
            'eval'                => array('submitOnChange' => true, 'tl_class'       => 'clr'),
        ),
        'http_username' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['http_username'],
            'inputType'           => 'text',
            'exclude'             => true,
            'eval'                => array('mandatory'     => true, 'tl_class'      => 'w50'),
        ),
        'http_password' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_syncCto_clients']['http_password'],
            'inputType'           => 'text',
            'exclude'             => true,
            'eval'                => array('mandatory' => true, 'tl_class'  => 'w50'),
        ),
    )
);

/**
 * Class for syncCto clients
 */
class tl_synccto_clients extends Backend
{

    // Objects
    protected $objBackendUser;
    protected $objBackendHistory;
    // Vars
    protected $blnUserBackendHistory = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->objBackendUser = BackendUser::getInstance();

        // Check if we have 'BackendUserHistory'
        if (in_array('backendUserHistory', Config::getInstance()->getActiveModules()))
        {
            $this->blnUserBackendHistory = true;
            $this->objBackendHistory     = BackendUserHistory::getInstance();
        }
    }

    /**
     * User for permission check from operation callbacks
     *
     * @param string $name Name of function
     * @param array $arguments Arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $arrSplitName = explode("_", $name);

        if (count($arrSplitName) != 3)
        {
            return FALSE;
        }

        //checkPermission_clients_edit
        if ($arrSplitName[0] == 'checkPermission' && $arrSplitName[1] == "client")
        {
            return $this->checkPermissionClientButton($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4], $arguments[5], $arrSplitName[2]);
        }
    }

    /**
     * Set the js and css files for client ping
     */
    public function checkClientStatus()
    {
        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/synccto/js/ping.js';
        $GLOBALS['TL_CSS'][]        = 'bundles/synccto/css/legend.css';
    }

    /**
     * Permission check for the client overview page
     *
     * @param type $row
     * @param type $href
     * @param type $label
     * @param type $title
     * @param type $icon
     * @param type $attributes
     * @param type $operations
     *
     * @return string
     */
    public function checkPermissionClientButton($row, $href, $label, $title, $icon, $attributes, $operations)
    {
        $blnUserIsWorking = false;
        $arrNotices = array();

        // Check if we have the userBackendHistory
        if ($this->blnUserBackendHistory)
        {
            $objResult = $this->objBackendHistory->searchUser(array('%synccto_clients%', "%$operations%", '%' . $row['id'] . '%'));

            if ($objResult->numRows != 0)
            {
                while ($objResult->next())
                {
                    $blnFoundOne = false;
                    $arrUrl = deserialize($objResult->url);

                    // Check do and id
                    if ($arrUrl['do'] == 'synccto_clients' && $arrUrl['id'] == $row['id'])
                    {
                        if ($operations == 'edit' && $arrUrl['act'] == $operations && empty($arrUrl['table']))
                        {
                            $blnFoundOne = true;
                        }
                        else if ($operations == 'showExtern' && $arrUrl['table'] == 'tl_syncCto_clients_showExtern')
                        {
                            $blnFoundOne = true;
                        }
                        else if ($operations == 'showExtern' && $arrUrl['table'] == 'tl_syncCto_clients_showExtern')
                        {
                            $blnFoundOne = true;
                        }
                        else if ($operations == 'syncFrom' && $arrUrl['table'] == 'tl_syncCto_clients_syncFrom')
                        {
                            $blnFoundOne = true;
                        }
                        else if ($operations == 'syncTo' && $arrUrl['table'] == 'tl_syncCto_clients_syncTo')
                        {
                            $blnFoundOne = true;
                        }

                        if($blnFoundOne == true)
                        {
                            $blnUserIsWorking = true;
                            $arrNotices[] = sprintf($GLOBALS['TL_LANG']['MSC']['editWarning'], $objResult->username,  date($GLOBALS['TL_CONFIG']['timeFormat'], $objResult->tstamp), $row['id']);
                        }
                    }
                }
            }
        }

        if (in_array($operations, array('syncTo', 'syncFrom'))) {
            $tableName = sprintf('tl_syncCto_clients_%s', $operations);
            $strIdName = 'id';
        } else {
            $tableName = '';
            $strIdName = 'id';
        }

        if ($this->objBackendUser->hasAccess($operations, 'syncCto_clients_p') == true)
        {
            if ($blnUserIsWorking)
            {
                switch ($icon)
                {
                    case 'bundles/synccto/images/nav/iconCheck.png':
                        $icon = 'bundles/synccto/images/nav/iconCheckDisabled.png';
                        break;

                    case 'bundles/synccto/images/nav/iconSyncFrom.png':
                    case 'bundles/synccto/images/nav/iconSyncTo.png':
                        $icon = 'bundles/synccto/images/nav/iconSyncDisabled.png';
                        break;

                    case 'edit.gif':
                        $icon = 'bundles/synccto/images/nav/iconEditDisabled.gif';
                        break;
                }

                $title = implode("<br/>", $arrNotices);
                return '<a class="user-history" href="' . $this->addToUrl($href . '&amp;' . $strIdName . '=' . $this->getID($row['id'], $tableName)) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $this->generateImage($icon, $label) . '</a> ';
            }
            else
            {
                return \sprintf(
                    '<a href="%s" title="%s" %s>%s</a> ',
                    $this->addToUrl($href . '&amp;' . $strIdName . '=' . $this->getID($row['id'], $tableName)),
                    specialchars($title),
                    $attributes,
                    $this->generateImage($icon, $label)
                );
            }
        }
        else if (preg_match("/\.png/i", $icon))
        {
            return $this->generateImage(preg_replace('/\.png$/i', '_.png', $icon)) . ' ';
        }
        else if (preg_match("/\.gif/i", $icon))
        {
            return $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
        }
        else
        {
            return $this->generateImage($icon) . ' ';
        }
    }

    /**
     * Build the ID.
     *
     * @param string $id The id.
     *
     * @param string $tableName The table name.
     *
     * @return string The full id.
     */
    private function getID($id, $tableName)
    {
        // If we have no table just return the id.
        if(empty($tableName)){
            return $id;
        }

        // Build the DCG like ID.
        $modelId = new ModelId($tableName, $id);

        return $modelId->getSerialized();
    }

    /**
     * Check permissions to edit table tl_content
     */
    public function checkPermissionClient()
    {
        if ($this->objBackendUser->isAdmin)
        {
            return;
        }

        // Set root IDs
        if (!is_array($this->objBackendUser->syncCto_clients) || count($this->objBackendUser->syncCto_clients) < 1)
        {
            $root = array(0);
        }
        else
        {
            $root = $this->objBackendUser->syncCto_clients;
        }

        $GLOBALS['TL_DCA']['tl_synccto_clients']['list']['sorting']['root'] = $root;

        $table = $this->Input->get('table');
		$table = str_replace('tl_syncCto_clients_', '', $table);
        if ($this->objBackendUser->hasAccess($table, 'syncCto_clients_p') == true || strlen($this->Input->get('act')) == 0)
        {
            return;
        }
        else
        {
            $this->log('Not enough permissions to ' . $this->Input->get('act') . ' syncCto clients', 'tl_syncCto_clients checkPermissionClient', TL_ERROR);
            $this->redirect('contao?act=error');
        }
    }

    /**
     * Check user permissions on every client
     */
    public function checkPermissionClientCreate()
    {
        if (!$this->objBackendUser->hasAccess('create', 'syncCto_clients_p'))
        {
            $GLOBALS['TL_DCA']['tl_synccto_clients']['config'] = array_unique(array_merge(array('closed' => true), $GLOBALS['TL_DCA']['tl_synccto_clients']['config']));
        }
    }

    /**
     * Call ctoCommunication engines
     */
    public function callCodifyengines()
    {
        $arrReturn = array();

        foreach ($GLOBALS["CTOCOM_ENGINE"] as $key => $value)
        {
            if ($value["invisible"] == TRUE)

            {
                continue;
            }

            $arrReturn[$key] = $value["name"];
        }

        asort($arrReturn);

        return $arrReturn;
    }

    /**
     * Check and delete the first slash
     *
     * @param string $strValue
     * @param DataContainer $dc
     * @return string
     */
    public function checkFirstSlash($strValue, DataContainer $dc)
    {
        if (empty($strValue))
        {
            return "";
        }
        else
        {
            if (preg_match("/^\//", $strValue))
            {
                return $strValue;
            }
            else
            {
                return "/" . $strValue;
            }
        }
    }

    /**
     * Add the address to the label.
     *
     * @param $row
     *
     * @param $label
     *
     * @return mixed
     */
    public function setLabel($row, $label)
    {
        $intMaxChars = 65;
        $intMinChars = 30;
        $intLeft     = $intMaxChars - (strlen($row['title']) + strlen($row['id']));
        $intLeft     = max($intLeft , $intMinChars);

        $strAddress  = SyncCtoHelper::getInstance()->substrCenter($row['address'] . ':' . $row['port'], $intLeft, ' [...] ');
        return str_replace('[URL]', $strAddress, $label);
    }
}
