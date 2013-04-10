<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2013 
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
    public function __construct()
    {
        $this->BackendUser = BackendUser::getInstance();

        parent::__construct();

        $this->Encryption = Encryption::getInstance();
        $this->Config = Config::getInstance();

        $this->objSyncCtoFiles = SyncCtoFiles::getInstance();
        $this->objSyncCtoDatabase = SyncCtoDatabase::getInstance();
        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();

        $this->loadLanguageFile("default");

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
     * Return localconfig
     * 
     * @param array $arrConfigBlacklist Blacklist entries for localconfig
     * @return array 
     */
    public function getLocalConfig($arrConfigBlacklist)
    {
        // Load localconfig
        $arrConfig = $this->objSyncCtoHelper->loadConfigs(SyncCtoEnum::LOADCONFIG_KEY_VALUE);

        // Kick blacklist entries
        foreach ($arrConfig as $key => $value)
        {
            if (in_array($key, $arrConfigBlacklist))
            {
                unset($arrConfig[$key]);
            }
        }

        return $arrConfig;
    }

    /**
     * Return a list of all syncCto path or a special path.
     * 
     * @param stirng $strName - Null or the name of a path like db,file,debug,tmp
     * @return [array|string]
     */
    public function getPathList($strName = null)
    {
        if ($strName == null)
        {
            return array(
                'db' => $GLOBALS['SYC_PATH']['db'],
                'file' => $GLOBALS['SYC_PATH']['file'],
                'debug' => $GLOBALS['SYC_PATH']['debug'],
                'tmp' => $GLOBALS['SYC_PATH']['tmp']
            );
        }
        else
        {
            switch ($strName)
            {
                case 'db':
                case 'file':
                case 'debug':
                case 'tmp':
                    return $GLOBALS['SYC_PATH'][$strName];

                default:
                    throw new Exception("Unknown field");
            }
        }
    }

    /**
     * Set the syncFrom flag in der localconfig
     * 
     * @param boolean $booMode
     * @return boolean 
     */
    public function setAttentionFlag($booMode)
    {
        $arrLocalConfig = $this->objSyncCtoHelper->loadConfigs(SyncCtoEnum::LOADCONFIG_KEYS_ONLY);

        if (in_array("\$GLOBALS['TL_CONFIG']['syncCto_attentionFlag']", $arrLocalConfig))
        {
            $this->Config->update("\$GLOBALS['TL_CONFIG']['syncCto_attentionFlag']", $booMode);
        }
        else
        {
            $this->Config->add("\$GLOBALS['TL_CONFIG']['syncCto_attentionFlag']", $booMode);
        }

        return true;
    }

    /**
     * Set the displayErrors flag
     * @param boolean $booState
     * @return boolean 
     */
    public function setDisplayErrors($booState)
    {
        if (key_exists("ctoCom_disableRefererCheck", $GLOBALS['TL_CONFIG']))
        {
            $this->Config->update("\$GLOBALS['TL_CONFIG']['displayErrors']", $booState);
        }
        else
        {
            $this->Config->add("\$GLOBALS['TL_CONFIG']['displayErrors']", $booState);
        }

        return true;
    }

    /* -------------------------------------------------------------------------
     * Extended syncCto core functions
     */

    /**
     * Get file and rebuild the array for checking delete files
     * 
     * @param string $strMD5
     * @param string $strFilename
     * @return mixed 
     */
    public function checkDeleteFiles($strMD5, $strFilename)
    {
        if (!key_exists($strFilename, $_FILES))
        {
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['unknown_file'], array($strFilename)));
        }

        if (md5_file($_FILES[$strFilename]["tmp_name"]) != $strMD5)
        {
            throw new Exception($GLOBALS['TL_LANG']['ERR']['checksum_error']);
        }

        $objFiles = Files::getInstance();
        $objFiles->move_uploaded_file($_FILES[$strFilename]["tmp_name"], $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "syncListInc.syncCto"));

        $objFile         = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "syncListInc.syncCto"));
        $arrChecksumList = deserialize($objFile->getContent());
        $objFile->close();

        if (!is_array($arrChecksumList))
        {
            throw new Exception("Could not rebuild array.");
        }

        return $this->objSyncCtoFiles->checkDeleteFiles($arrChecksumList);
    }
    
    /**
     * Get file and rebuild the array for checking delete files
     * 
     * @param string $strMD5
     * @param string $strFilename
     * @return mixed 
     */
    public function searchDeleteFolders($strMD5, $strFilename)
    {
        if (!key_exists($strFilename, $_FILES))
        {
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['unknown_file'], array($strFilename)));
        }

        if (md5_file($_FILES[$strFilename]["tmp_name"]) != $strMD5)
        {
            throw new Exception($GLOBALS['TL_LANG']['ERR']['checksum_error']);
        }

        $objFiles = Files::getInstance();
        $objFiles->move_uploaded_file($_FILES[$strFilename]["tmp_name"], $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "syncFolderListInc.syncCto"));

        $objFile         = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "syncFolderListInc.syncCto"));
        $arrChecksumList = deserialize($objFile->getContent());
        $objFile->close();

        if (!is_array($arrChecksumList))
        {
            throw new Exception("Could not rebuild array.");
        }

        return $this->objSyncCtoFiles->searchDeleteFolders($arrChecksumList);
    }

    /**
     * Get filelist and rebuild the array and run checksum compare.
     * 
     * @param string $strMD5
     * @param string $strFilename
     * @return mixed 
     */
    public function runCecksumCompare($strMD5, $strFilename)
    {
        if (!key_exists($strFilename, $_FILES))
        {
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['unknown_file'], array($strFilename)));
        }

        if (md5_file($_FILES[$strFilename]["tmp_name"]) != $strMD5)
        {
            throw new Exception($GLOBALS['TL_LANG']['ERR']['checksum_error']);
        }

        $objFiles = Files::getInstance();
        $objFiles->move_uploaded_file($_FILES[$strFilename]["tmp_name"], $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "syncListInc.syncCto"));

        $objFile         = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "syncListInc.syncCto"));
        $arrChecksumList = deserialize($objFile->getContent());
        $objFile->close();

        if (!is_array($arrChecksumList))
        {
            throw new Exception("Could not rebuild array.");
        }

        return $this->objSyncCtoFiles->runCecksumCompare($arrChecksumList);
    }

    /**
     * Execute a SQL command
     * 
     * @param array $arrSQL <p>array([ID] => <br/>array("prepare" => [String(SQL)], "execute" => array([mix]) ) <br/>)</p>
     * @return array <p>array([ID] => [mix])</p>
     */
    public function executeSQL($arrSQL)
    {
        if (!is_array($arrSQL))
        {
            return false;
        }

        $arrResponse = array();

        foreach ($arrSQL as $key => $value)
        {
            $mixDatabase = $this->Database->prepare($value['prepare']);
            call_user_func_array(array($mixDatabase, "execute"), $value["execute"]);
        }

        return $arrResponse;
    }

}