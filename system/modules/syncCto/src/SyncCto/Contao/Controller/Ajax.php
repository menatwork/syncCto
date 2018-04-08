<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

namespace SyncCto\Contao\Controller;

use Contao\File;
use SyncCto\Config\Enum;
use SyncCto\Contao\Communicator\Client;

/**
 * Beta Class for syncCto and AJAX
 */
class Ajax extends Client
{
    // Return vars
    protected $strMsg;
    protected $strError;
    protected $booSuccess;
    protected $strFile;
    protected $intTime;
    // Vars
    protected $intClientID;
    protected $arrListCompare;

    public function __construct()
    {
        parent::__construct();

        $this->strMsg = "";
        $this->strError = "";
        $this->booSuccess = FALSE;
        $this->intTime = 0;
    }

    protected function loadTempList()
    {
        $objCompareList = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "synccomparelistTo-ID-" . $this->intClientID . ".txt"));

        $strContent = $objCompareList->getContent();
        if (strlen($strContent) == 0)
        {
            $this->arrListCompare = array();
        }
        else
        {
            $this->arrListCompare = unserialize($strContent);
        }

        $objCompareList->close();
    }

    protected function saveTempLists()
    {
        $objCompareList = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "synccomparelistTo-ID-" . $this->intClientID . ".txt"));
        $objCompareList->write(serialize($this->arrListCompare));
        $objCompareList->close();
    }

    protected function output()
    {
        // clean output;
        ob_clean();

        // echo answer
        echo json_encode(array(
            "success" => $this->booSuccess,
            "msg" => $this->strMsg,
            "error_msg" => $this->strError,
            "file" => $this->strFile,
            "upload_time" => $this->intTime
        ));

        // end
        exit();
    }

    public function ajaxSendFile()
    {
        try
        {
            // Load and check ID
            if (strlen($this->Input->post("id")) == 0)
            {
                throw new \Exception("No client id was send.");
            }

            $this->intClientID = intval($this->Input->post("id"));

            // Load and check compare list
            $this->loadTempList();
            if(!is_array($this->arrListCompare) && count($this->arrListCompare) == 0 )
            {
                throw new \Exception("File list is empty.");
            }

            // Set client by id for connection
            $this->setClientBy($this->intClientID);
            // Upload file from server to client
            $this->sendFile();

            // Return okay
            $this->booSuccess = true;
        }
        catch (\Exception $exc)
        {
            // Return error
            $this->strError = $exc->getMessage();
            $this->booSuccess = false;
        }

        // Output msg
        $this->output();
    }

    protected function sendFile()
    {
        if (strlen($strFile = $this->Input->post("file")) == 0)
        {
            throw new \Exception("No file was send.");
        }

        if (!file_exists(TL_ROOT . "/" . $strFile))
        {
            throw new \Exception("File: $strFile not exists.");
        }

        parent::sendFile(dirname($strFile), basename($strFile), md5_file(TL_ROOT . "/" . $strFile), Enum::UPLOAD_SYNC_TEMP);
    }
}
