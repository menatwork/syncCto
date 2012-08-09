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
 * @copyright  MEN AT WORK 2012
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */
class SyncCtoUpdater extends Backend
{

    // Single Files 
    protected $arrFiles = array();
    // Tables 
    protected $arrTables = array(
        "tl_synccto_clients",
        "tl_requestcache",
        "tl_ctocom_cache"
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

        $this->objSyncCtoFiles    = SyncCtoFiles::getInstance();
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
        if (self::$instance == null)
        {
            self::$instance = new SyncCtoUpdater();
        }

        return self::$instance;
    }

    /**
     * Build a zip with all files.
     * 
     * @param string $strZipPath Path for the file
     * @throws Exception 
     */
    public function buildUpdateZip($strZipPath)
    {
        // Open archive
        $this->objZipArchive = new ZipArchiveCto();

        if (($mixError = $this->objZipArchive->open($strZipPath, ZipArchiveCto::CREATE)) !== true)
        {
            throw new Exception($GLOBALS['TL_LANG']['MSC']['error'] . ": " . $this->objZipArchive->getErrorDescription($mixError));
        }

        $this->addFiles();
        $this->addDatabase();

        $this->objZipArchive->close();

    }

    protected function addFiles()
    {
        if (!file_exists(TL_ROOT . '/tl_files/syncCto_backups/dependencies.xml'))
        {
            throw new Exception("Missing dependencies.xml for autoupdater.");
        }

        $objXMLReader = new XMLReader();
        $objXMLReader->open(TL_ROOT . '/tl_files/syncCto_backups/dependencies.xml');

        $strCurrentNode = "";

        while ($objXMLReader->read())
        {
            switch ($objXMLReader->nodeType)
            {
                case XMLReader::ELEMENT:
                    $strCurrentNode = $objXMLReader->localName;
                    break;

                case XMLReader::TEXT:
                case XMLReader::CDATA:
                    if ($strCurrentNode == "path")
                    {
                        $strPath = preg_replace("/^TL_ROOT\//i", "", $objXMLReader->value, 1);

                        if (file_exists(TL_ROOT . "/" . $strPath))
                        {
                            if (!$this->objZipArchive->addFile($strPath, "FILES/" . $strPath))
                            {
                                throw new Exception("Could not add the file " . $strPath . " to the archive.");
                            }
                        }
                    }
                    break;
            }
        }

        if (!$this->objZipArchive->addFile('tl_files/syncCto_backups/dependencies.xml', "FILES/" . 'tl_files/syncCto_backups/dependencies.xml'))
        {
            throw new Exception('Could not add the file /tl_files/syncCto_backups/dependencies.xml to the archive.');
        }
    }

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

        foreach ($this->arrTables as $TableName)
        {
            // Get data
            $arrStructure = $this->objSyncCtoDatabase->getTableStructure($TableName);

            // Check if empty
            if (count($arrStructure) == 0)
            {
                continue;
            }

            $objXml->startElement('table');
            $objXml->writeAttribute("name", $TableName);

            $objXml->startElement('fields');
            if (is_array($arrStructure['TABLE_FIELDS']))
            {
                foreach ($arrStructure['TABLE_FIELDS'] as $keyField => $valueField)
                {
                    $objXml->startElement('field');
                    $objXml->writeAttribute("name", $keyField);
                    $objXml->text($valueField);
                    $objXml->endElement(); // End field
                }
            }
            $objXml->endElement(); // End fields

            $objXml->startElement('definitions');
            if (is_array($arrStructure['TABLE_CREATE_DEFINITIONS']))
            {
                foreach ($arrStructure['TABLE_CREATE_DEFINITIONS'] as $keyField => $valueField)
                {
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

?>
