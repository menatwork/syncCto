<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

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

class SyncCtoRPCFunctions extends Backend
{
    /* -------------------------------------------------------------------------
     * Vars
     */
    
    protected $objSyncCtoFiles;
    protected $objSyncCtoHelper;
    protected $objSyncCtoDatabase;
    protected $objSyncCtoMeasurement;
    protected $BackendUser;
    protected $Encryption;
    protected $Config;

    /* -------------------------------------------------------------------------
     * Core
     */
    
    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->BackendUser = BackendUser::getInstance();

        parent::__construct();

        $this->Encryption = Encryption::getInstance();
        $this->Config = Config::getInstance();

        $this->objSyncCtoFiles = SyncCtoFiles::getInstance();
        $this->objSyncCtoDatabase = SyncCtoDatabase::getInstance();
        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();

        $this->loadLanguageFile("syncCto");

        $this->arrDebug = array();
    }

    /* -------------------------------------------------------------------------
     * RPC Functions - Helper 
     */

    /**
     * Send the version number of this syncCto
     * 
     * @return string
     */
    public function getVersionSyncCto()
    {
        return $GLOBALS['SYC_VERSION'];
    }

    /**
     * Send informations about this php instalation
     * 
     * @return array
     */
    public function getClientParameter()
    {
        return array(
            'max_execution_time' => ini_get('max_execution_time'),
            'memory_limit' => ini_get('memory_limit'),
            'file_uploads' => ini_get('file_uploads'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size')
        );
    }

    /**
     * Import configuration entries
     *
     * @param array $arrConfig
     * @return array 
     */
    public function importConfig($arrConfig)
    {
        $arrLocalConfig = $this->objSyncCtoHelper->loadConfigs(SyncCtoEnum::LOADCONFIG_KEYS_ONLY);

        foreach ($arrConfig as $key => $value)
        {
            if (in_array($key, $arrLocalConfig))
            {
                $this->Config->update("\$GLOBALS['TL_CONFIG']['" . $key . "']", $value);
            }
            else
            {
                $this->Config->add("\$GLOBALS['TL_CONFIG']['" . $key . "']", $value);
            }
        }

        return true;
    }

    
}

?>