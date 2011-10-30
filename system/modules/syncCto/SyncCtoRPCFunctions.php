<?php

if (!defined('TL_ROOT'))
    die('You cannot access this file directly!');

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

    protected $objSyncCtoFiles;
    protected $objSyncCtoHelper;
    protected $objSyncCtoDatabase;
    protected $objSyncCtoMeasurement;
    protected $BackendUser;
    protected $Encryption;
    protected $Config;

    /**
     * Constructor
     */
    function __construct()
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
     */
    public function getVersionSyncCto()
    {
        return $GLOBALS['SYC_VERSION'];
    }

    /**
     * Send informations about this php instalation
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

    /* -------------------------------------------------------------------------
     * Config Operations
     */

    public function importConfig($arrConfig)
    {
        $arrLocalConfig = $this->objSyncCtoHelper->loadConfig(SyncCtoEnum::LOADCONFIG_KEYS_ONLY);

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

    /*
     * -------------------------------------------------------------------------
     * -------------------------------------------------------------------------
     * 
     * ALT
     * 
     * -------------------------------------------------------------------------
     * -------------------------------------------------------------------------
     */

    /* -------------------------------------------------------------------------
     * RPC Functions - KA 
     */

    public function rpc_file_get($strPath)
    {
        $strFilePath = TL_ROOT . $strPath;

        if (!file_exists($strFilePath))
        {
            $this->arrError[] = "Can not find " . $strFilePath;
            return;
        }

        // Read file
        $fh = fopen($strFilePath, 'rb');

        if ($fh === FALSE)
        {
            $this->arrError[] = "Error by reading file " . $strFilePath;
            return;
        }

        while (!feof($fh))
        {
            $content .= fread($fh, 512);
        }

        fclose($fh);
        unset($fh);

        $this->mixOutput["RPC_FILE_GET"] = $content;
        $this->mixOutput["md5"] = md5_file($strFilePath);

        unset($content);

        return true;
    }
}

?>