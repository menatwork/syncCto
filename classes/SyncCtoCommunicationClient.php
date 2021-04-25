<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

/**
 * Communication Class
 *
 * Extends CtoCommunication witch special RPC-Requests
 */
class SyncCtoCommunicationClient extends \MenAtWork\CtoCommunicationBundle\Controller\Server
{
    /* -------------------------------------------------------------------------
     * Vars
     */

    // Singelton Pattern
    protected static $instance = null;
    // Objects
    protected $objSyncCtoFiles;
    protected $objSyncCtoHelper;
    // Vars
    protected $arrClientData = array();

    /* -------------------------------------------------------------------------
     * Core
     */

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // Objects
        $this->objSyncCtoFiles  = SyncCtoFiles::getInstance();
        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();
    }

    /**
     * Singelton Pattern
     *
     * @return SyncCtoCommunicationClient
     */
    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new SyncCtoCommunicationClient();
        }

        return self::$instance;
    }

    /**
     * Set client by id
     *
     * @param int $id
     *
     * @return array
     *
     * @throws Exception
     */
    public function setClientBy($id)
    {
        // Load Client from database
        $objClient = \Database::getInstance()->prepare("SELECT * FROM tl_synccto_clients WHERE id = %s")
            ->limit(1)
            ->execute((int)$id);

        // Check if a client was loaded
        if ($objClient->numRows == 0)
        {
            throw new Exception($GLOBALS['TL_LANG']['ERR']['unknown_client']);
        }

        $strUrl = $objClient->address . ":" . $objClient->port . '/ctoCommunication';

        $this->setClient($strUrl, $objClient->apikey, $objClient->codifyengine);

        if ($objClient->http_auth == true)
        {
            $this->setHttpAuth($objClient->http_username, $objClient->http_password);
        }

        // Set debug modus for ctoCom.
        if ($GLOBALS['TL_CONFIG']['syncCto_debug_mode'] == true)
        {
            $this->setDebug(true);
            $this->setMeasurement(true);

            $this->setFileDebug($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['debug'], "CtoComDebug.txt"));
            $this->setFileMeasurement($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['debug'], "CtoComMeasurement.txt"));
        }

        $this->arrClientData = array(
            "title"   => $objClient->title,
            "address" => $objClient->address,
            "port"    => $objClient->port
        );

        return $this->arrClientData;
    }

    /**
     * Return a list with basic client informations
     *
     * @return array {title, address, port}
     */
    public function getClientData()
    {
        return $this->arrClientData;
    }





}

?>
