<?php

namespace MenAtWork\SyncCto\DcGeneral\Events;
use Backend;
use File;
use SyncCtoDatabase;
use SyncCtoErClient;
use SyncCtoFiles;
use ZipArchiveCto;

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */
class SyncCtoUpdater extends Backend
{

    // Single Files 
    protected $arrFiles = array();
    // Tables 
    protected $arrTables = array();

    /**
     * Black filelist
     *  - All regex functions are working.
     *  - User TL_ROOT for root folder.
     *
     * @var array
     */
    protected $arrBlackFiles = array(
        '.*/runonce.php',
    );

    /**
     * @var SyncCtoUpdater
     */
    protected static $instance = null;

    /**
     * @var SyncCtoFiles
     */
    protected $objSyncCtoFiles;

    /**
     * @var SyncCtoDatabase;
     */
    protected $objSyncCtoDatabase;

    /**
     * @var SyncCtoErClient
     */
    protected $objSyncCtoErClient;

    /**
     * @var ZipArchiveCto
     */
    protected $objZipArchive;

    /**
     * Constructor
     */
    protected function __construct()
    {
        parent::__construct();

        $this->objSyncCtoFiles = SyncCtoFiles::getInstance();
        $this->objSyncCtoDatabase = SyncCtoDatabase::getInstance();
    }

    /**
     * Clone
     *
     * @return SyncCtoUpdater
     */
    public function __clone()
    {
        return self::$instance;
    }

    /**
     * Get current instnce
     *
     * @return SyncCtoUpdater
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new SyncCtoUpdater();
        }

        return self::$instance;
    }

    /**
     * Build a zip with all files.
     *
     * @param string $strZipPath Path for the file
     *
     * @throws Exception
     */
    public function buildUpdateZip($strZipPath)
    {
        // Open archive
        $this->objZipArchive = new ZipArchiveCto();

        if (($mixError = $this->objZipArchive->open($strZipPath, ZipArchiveCto::CREATE)) !== true) {
            throw new Exception($GLOBALS['TL_LANG']['MSC']['error'] . ": " . $this->objZipArchive->getErrorDescription($mixError));
        }

        // Add files
        $this->addFiles();
        // Add db update file        
        $this->addDatabase();

        $this->objZipArchive->close();
    }

    /**
     * Read a xml file and add all files to archive
     *
     * @throws Exception
     */
    protected function addFiles()
    {
        // Check if xml exists
        if (!file_exists(TL_ROOT . '/' . $GLOBALS['TL_CONFIG']['uploadPath'] . '/syncCto_backups/dependencies.xml')) {
            throw new Exception("Missing dependencies.xml for autoupdater.");
        }

        // Create a new reader
        $objXMLReader = new XMLReader();
        $objXMLReader->open(TL_ROOT . '/' . $GLOBALS['TL_CONFIG']['uploadPath'] . '/syncCto_backups/dependencies.xml');

        $strCurrentNode = "";

        // Run through each line
        while ($objXMLReader->read()) {
            switch ($objXMLReader->nodeType) {
                case XMLReader::ELEMENT:
                    $strCurrentNode = $objXMLReader->localName;
                    break;

                case XMLReader::TEXT:
                case XMLReader::CDATA:
                    if ($strCurrentNode == "path") {
                        // Check if file is in blacklist
                        foreach ($this->arrBlackFiles as $strBlackfile) {
                            if (preg_match('^' . $strBlackfile . '^i', $objXMLReader->value)) {
                                continue;
                            }
                        }

                        // Remove the TL_ROOT and the first '/'
                        $strPath = preg_replace("/^TL_ROOT\//i", "", $objXMLReader->value, 1);

                        // If we have a sql parse it
                        if (preg_match("/\.sql$/i", $strPath)) {
                            $this->parseSQL($strPath);
                        }

                        // If file exists add it to archive
                        if (file_exists(TL_ROOT . "/" . $strPath)) {
                            if (!$this->objZipArchive->addFile($strPath, "FILES/" . $strPath)) {
                                throw new Exception("Could not add the file " . $strPath . " to the archive.");
                            }
                        }
                    }
                    break;
            }
        }

        // Add the dependencies.xml
        if (!$this->objZipArchive->addFile($GLOBALS['TL_CONFIG']['uploadPath'] . '/syncCto_backups/dependencies.xml', "FILES/" . $GLOBALS['TL_CONFIG']['uploadPath'] . '/syncCto_backups/dependencies.xml')) {
            throw new Exception('Could not add the file /' . $GLOBALS['TL_CONFIG']['uploadPath'] . '/syncCto_backups/dependencies.xml to the archive.');
        }
    }

    /**
     * Parse a sql file and try to get all table names
     *
     * @param string $strPath Path to the SQL file
     *
     * @return void
     */
    protected function parseSQL($strPath)
    {
        // Check if exists
        if (!file_exists(TL_ROOT . "/" . $strPath)) {
            return;
        }

        // Open file and read each single line
        $objFile = new File($strPath);

        foreach ($objFile->getContentAsArray() as $key => $value) {
            // Search for 'Create Table'
            if (preg_match("/.*CREATE TABLE `.*` \(.*/", $value)) {
                $value = trim($value);
                $arrCreate = preg_split("/(.*CREATE TABLE `|` \(.*)/", $value);
                $this->arrTables[] = trim($arrCreate[1]);
            }
        }
    }

    /**
     * Create a xml file with table informations
     */
    protected function addDatabase()
    {
        // Create XML File
        $objXml = new XMLWriter();
        $objXml->openMemory();
        $objXml->setIndent(true);
        $objXml->setIndentString("\t");

        // XML Start
        $objXml->startDocument('1.0', 'UTF-8');
        $objXml->startElement('database');

        // Write meta (header)
        $objXml->startElement('metatags');
        $objXml->writeElement('version', $GLOBALS['SYC_VERSION']);
        $objXml->writeElement('create_unix', time());
        $objXml->writeElement('create_date', date('Y-m-d', time()));
        $objXml->writeElement('create_time', date('H:i', time()));
        $objXml->endElement(); // End metatags

        $objXml->startElement('structure');

        foreach ($this->arrTables as $TableName) {
            // Get data
            $arrStructure = $this->objSyncCtoDatabase->getTableStructure($TableName);

            // Check if empty
            if (count($arrStructure) == 0) {
                continue;
            }

            $objXml->startElement('table');
            $objXml->writeAttribute("name", $TableName);

            $objXml->startElement('fields');
            if (is_array($arrStructure['TABLE_FIELDS'])) {
                foreach ($arrStructure['TABLE_FIELDS'] as $keyField => $valueField) {
                    $objXml->startElement('field');
                    $objXml->writeAttribute("name", $keyField);
                    $objXml->text($valueField);
                    $objXml->endElement(); // End field
                }
            }
            $objXml->endElement(); // End fields

            $objXml->startElement('definitions');
            if (is_array($arrStructure['TABLE_CREATE_DEFINITIONS'])) {
                foreach ($arrStructure['TABLE_CREATE_DEFINITIONS'] as $keyField => $valueField) {
                    $objXml->startElement('def');
                    $objXml->writeAttribute("name", $keyField);
                    $objXml->text($valueField);
                    $objXml->endElement(); // End field
                }
            }
            $objXml->endElement(); // End fields

            $objXml->startElement("option");
            $objXml->text($arrStructure['TABLE_OPTIONS']);
            $objXml->endElement();

            $objXml->endElement(); // End table
        }

        $objXml->endElement(); // End structure
        $objXml->endElement(); // End database

        $this->objZipArchive->addFromString("SQL/sql.xml", $objXml->flush(true));
    }

}