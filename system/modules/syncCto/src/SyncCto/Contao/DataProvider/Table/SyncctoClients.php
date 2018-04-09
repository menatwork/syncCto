<?php

/**
 * This file is part of menatwork/synccto.
 *
 * (c) 2014-2018 MEN AT WORK.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    menatwork/synccto
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @author     Patrick Kahl <kahl.patrick@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2014-2018 MEN AT WORK.
 * @license    https://github.com/menatwork/syncCto/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace SyncCto\Contao\DataProvider\Table;

use Contao\Backend;
use Contao\BackendUser;
use Contao\DataContainer;
use Contao\ModuleLoader;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use SyncCto\Helper\Helper;

/**
 * Class for syncCto clients
 */
class SyncctoClients extends Backend
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
        if (\in_array('backendUserHistory', ModuleLoader::getActive()))
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
        $arrSplitName = \explode("_", $name);

        if (\count($arrSplitName) != 3)
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
        $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/syncCto/assets/js/ping.js';
        $GLOBALS['TL_CSS'][]        = 'system/modules/syncCto/assets/css/legend.css';
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
     * @return type
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
                    $arrUrl = \deserialize($objResult->url);

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
                            $arrNotices[] = \sprintf($GLOBALS['TL_LANG']['MSC']['editWarning'], $objResult->username,  \date($GLOBALS['TL_CONFIG']['timeFormat'], $objResult->tstamp), $row['id']);
                        }
                    }
                }
            }
        }

        if (\in_array($operations, array('syncTo', 'syncFrom'))) {
            $tableName = \sprintf('tl_syncCto_clients_%s', $operations);
            $strIdName = 'cid';
        } else {
            $strIdName = 'id';
        }

        if ($this->objBackendUser->hasAccess($operations, 'syncCto_clients_p') == true)
        {
            if ($blnUserIsWorking)
            {
                switch ($icon)
                {
                    case 'system/modules/syncCto/assets/images/nav/iconCheck.png':
                        $icon = 'system/modules/syncCto/assets/images/nav/iconCheckDisabled.png';
                        break;

                    case 'system/modules/syncCto/assets/images/nav/iconSyncFrom.png':
                    case 'system/modules/syncCto/assets/images/nav/iconSyncTo.png':
                        $icon = 'system/modules/syncCto/assets/images/nav/iconSyncDisabled.png';
                        break;

                    case 'edit.gif':
                        $icon = 'system/modules/syncCto/assets/images/nav/iconEditDisabled.gif';
                        break;
                }

                $title = \implode("<br/>", $arrNotices);
                return '<a class="user-history" href="' . $this->addToUrl($href . '&amp;' . $strIdName . '=' . $this->getID($row['id'], $tableName)) . '" title="' . \specialchars($title) . '"' . $attributes . '>' . $this->generateImage($icon, $label) . '</a> ';
            }
            else
            {
                return '<a href="' . $this->addToUrl($href . '&amp;' . $strIdName . '=' . $this->getID($row['id'], $tableName)) . '" title="' . \specialchars($title) . '"' . $attributes . '>' . $this->generateImage($icon, $label) . '</a> ';
            }
        }
        else if (\preg_match("/\.png/i", $icon))
        {
            return $this->generateImage(\preg_replace('/\.png$/i', '_.png', $icon)) . ' ';
        }
        else if (\preg_match("/\.gif/i", $icon))
        {
            return $this->generateImage(\preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
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
        if (!\is_array($this->objBackendUser->syncCto_clients) || \count($this->objBackendUser->syncCto_clients) < 1)
        {
            $root = array(0);
        }
        else
        {
            $root = $this->objBackendUser->syncCto_clients;
        }

        $GLOBALS['TL_DCA']['tl_synccto_clients']['list']['sorting']['root'] = $root;

        if ($this->objBackendUser->hasAccess($this->Input->get('act'), 'syncCto_clients_p') == true || \strlen($this->Input->get('act')) == 0)
        {
            return;
        }
        else
        {
            $this->log('Not enough permissions to ' . $this->Input->get('act') . ' syncCto clients', 'tl_syncCto_clients checkPermissionClient', TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }
    }

    /**
     * Check user permissions on every client
     */
    public function checkPermissionClientCreate()
    {
        if (!$this->objBackendUser->hasAccess('create', 'syncCto_clients_p'))
        {
            $GLOBALS['TL_DCA']['tl_synccto_clients']['config'] = \array_unique(\array_merge(array('closed' => true), $GLOBALS['TL_DCA']['tl_synccto_clients']['config']));
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

        \asort($arrReturn);

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
            if (\preg_match("/^\//", $strValue))
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
        $intLeft     = $intMaxChars - (\strlen($row['title']) + \strlen($row['id']));
        $intLeft     = \max($intLeft , $intMinChars);

        $strAddress  = Helper::getInstance()->substrCenter($row['address'] . $row['path'], $intLeft, ' [...] ');
        return \str_replace('[URL]', $strAddress, $label);
    }
}
