<?php

namespace MenAtWork\SyncCto\Contao\Table;

use Contao\Backend;
use Contao\BackendUser;
use Contao\Config;
use Contao\DataContainer;
use Contao\Image;
use Contao\StringUtil;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use SyncCtoHelper;

class SyncCtoClients extends Backend
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
        $this->blnUserBackendHistory = false;

        // Check if we have 'BackendUserHistory'
//        if (in_array('backendUserHistory', Config::getInstance()->getActiveModules()))
//        {
//            $this->blnUserBackendHistory = true;
//            $this->objBackendHistory     = BackendUserHistory::getInstance();
//        }
    }

    public function checkPermission_client_edit()
    {
        $arguments = func_get_args();
        return $this->checkPermissionClientButton(
            $arguments[0],
            $arguments[1],
            $arguments[2],
            $arguments[3],
            $arguments[4],
            $arguments[5],
            'edit'
        );
    }

    public function checkPermission_client_copy()
    {
        $arguments = func_get_args();
        return $this->checkPermissionClientButton(
            $arguments[0],
            $arguments[1],
            $arguments[2],
            $arguments[3],
            $arguments[4],
            $arguments[5],
            'copy'
        );
    }

    public function checkPermission_client_delete()
    {
        $arguments = func_get_args();
        return $this->checkPermissionClientButton(
            $arguments[0],
            $arguments[1],
            $arguments[2],
            $arguments[3],
            $arguments[4],
            $arguments[5],
            'delete'
        );
    }

    public function checkPermission_client_showExtern()
    {
        $arguments = func_get_args();
        return $this->checkPermissionClientButton(
            $arguments[0],
            $arguments[1],
            $arguments[2],
            $arguments[3],
            $arguments[4],
            $arguments[5],
            'showExtern'
        );
    }

    public function checkPermission_client_syncFrom()
    {
        $arguments = func_get_args();
        return $this->checkPermissionClientButton(
            $arguments[0],
            $arguments[1],
            $arguments[2],
            $arguments[3],
            $arguments[4],
            $arguments[5],
            'syncFrom'
        );
    }

    public function checkPermission_client_syncTo()
    {
        $arguments = func_get_args();
        return $this->checkPermissionClientButton(
            $arguments[0],
            $arguments[1],
            $arguments[2],
            $arguments[3],
            $arguments[4],
            $arguments[5],
            'syncTo'
        );
    }

    /**
     * Set the js and css files for client ping
     */
    public function checkClientStatus()
    {
        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/synccto/js/ping.js';
        $GLOBALS['TL_CSS'][] = 'bundles/synccto/css/legend.css';
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
        if ($this->blnUserBackendHistory) {
            $objResult = $this->objBackendHistory->searchUser(array('%synccto_clients%', "%$operations%", '%' . $row['id'] . '%'));

            if ($objResult->numRows != 0) {
                while ($objResult->next()) {
                    $blnFoundOne = false;
                    $arrUrl = unserialize($objResult->url);

                    // Check do and id
                    if ($arrUrl['do'] == 'synccto_clients' && $arrUrl['id'] == $row['id']) {
                        if ($operations == 'edit' && $arrUrl['act'] == $operations && empty($arrUrl['table'])) {
                            $blnFoundOne = true;
                        } else {
                            if ($operations == 'showExtern' && $arrUrl['table'] == 'tl_syncCto_clients_showExtern') {
                                $blnFoundOne = true;
                            } else {
                                if ($operations == 'showExtern' && $arrUrl['table'] == 'tl_syncCto_clients_showExtern') {
                                    $blnFoundOne = true;
                                } else {
                                    if ($operations == 'syncFrom' && $arrUrl['table'] == 'tl_syncCto_clients_syncFrom') {
                                        $blnFoundOne = true;
                                    } else {
                                        if ($operations == 'syncTo' && $arrUrl['table'] == 'tl_syncCto_clients_syncTo') {
                                            $blnFoundOne = true;
                                        }
                                    }
                                }
                            }
                        }

                        if ($blnFoundOne == true) {
                            $blnUserIsWorking = true;
                            $arrNotices[] = sprintf(
                                $GLOBALS['TL_LANG']['MSC']['editWarning'],
                                $objResult->username,
                                date(\Contao\Config::get('timeFormat'), $objResult->tstamp),
                                $row['id']
                            );
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

        if ($this->objBackendUser->hasAccess($operations, 'syncCto_clients_p') == true) {
            if ($blnUserIsWorking) {
                switch ($icon) {
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
                $url = $this->addToUrl(
                    $href
                    . '&amp;'
                    . $strIdName
                    . '='
                    . $this->getID($row['id'], $tableName)
                );
                return '<a class="user-history" href="'
                       . $url
                       . '" title="'
                       . StringUtil::specialchars($title)
                       . '"'
                       . $attributes
                       . '>'
                       . Image::getHtml($icon, $label)
                       . '</a> ';
            } else {
                return \sprintf(
                    '<a href="%s" title="%s" %s>%s</a> ',
                    $this->addToUrl($href . '&amp;' . $strIdName . '=' . $this->getID($row['id'], $tableName)),
                    StringUtil::specialchars($title),
                    $attributes,
                    Image::getHtml($icon, $label)
                );
            }
        } else {
            if (preg_match("/\.png/i", $icon)) {
                return Image::getHtml(preg_replace('/\.png$/i', '_.png', $icon)) . ' ';
            } else {
                if (preg_match("/\.gif/i", $icon)) {
                    return Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
                } else {
                    return Image::getHtml($icon) . ' ';
                }
            }
        }
    }

    /**
     * Build the ID.
     *
     * @param string $id        The id.
     *
     * @param string $tableName The table name.
     *
     * @return string The full id.
     */
    private function getID($id, $tableName)
    {
        // If we have no table just return the id.
        if (empty($tableName)) {
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
        if ($this->objBackendUser->isAdmin) {
            return;
        }

        // Set root IDs
        if (!is_array($this->objBackendUser->syncCto_clients) || count($this->objBackendUser->syncCto_clients) < 1) {
            $root = array(0);
        } else {
            $root = $this->objBackendUser->syncCto_clients;
        }

        $GLOBALS['TL_DCA']['tl_synccto_clients']['list']['sorting']['root'] = $root;

        $table = $this->Input->get('table');
        $table = str_replace('tl_syncCto_clients_', '', $table);
        if ($this->objBackendUser->hasAccess($table, 'syncCto_clients_p') == true || strlen($this->Input->get('act')) == 0) {
            return;
        } else {
            $this->log('Not enough permissions to ' . $this->Input->get('act') . ' syncCto clients', 'tl_syncCto_clients checkPermissionClient', TL_ERROR);
            $this->redirect('contao?act=error');
        }
    }

    /**
     * Check user permissions on every client
     */
    public function checkPermissionClientCreate()
    {
        if (!$this->objBackendUser->hasAccess('create', 'syncCto_clients_p')) {
            $GLOBALS['TL_DCA']['tl_synccto_clients']['config'] = array_unique(array_merge(array('closed' => true), $GLOBALS['TL_DCA']['tl_synccto_clients']['config']));
        }
    }

    /**
     * Call ctoCommunication engines
     */
    public function callCodifyengines()
    {
        $arrReturn = array();

        foreach ($GLOBALS["CTOCOM_ENGINE"] as $key => $value) {
            if ($value["invisible"] == TRUE) {
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
     * @param string        $strValue
     * @param DataContainer $dc
     *
     * @return string
     */
    public function checkFirstSlash($strValue, DataContainer $dc)
    {
        if (empty($strValue)) {
            return "";
        } else {
            if (preg_match("/^\//", $strValue)) {
                return $strValue;
            } else {
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
        $intLeft = $intMaxChars - (strlen($row['title']) + strlen($row['id']));
        $intLeft = max($intLeft, $intMinChars);

        $strAddress = SyncCtoHelper::getInstance()->substrCenter($row['address'] . ':' . $row['port'], $intLeft, ' [...] ');
        return str_replace('[URL]', $strAddress, $label);
    }
}