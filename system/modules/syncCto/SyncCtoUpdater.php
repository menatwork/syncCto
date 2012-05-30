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

        $this->objSyncCtoFiles = SyncCtoFiles::getInstance();
        $this->objSyncCtoDatabase = SyncCtoDatabase::getInstance();
        $this->objSyncCtoErClient = new SyncCtoErClient();
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
        $this->loadFileListFromEr();

        foreach ($this->arrFiles as $value)
        {
            $value['path'] = preg_replace("/^TL_ROOT\//i", "", $value['path'], 1);

            if (file_exists(TL_ROOT . "/" . $value['path']))
            {
                if (!$this->objZipArchive->addFile($value['path'], "FILES/" . $value['path']))
                {
                    throw new Exception("Could not at the file " . $value['path'] . " to the archive.");
                }
            }
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

    // - Helper ----------------------------------------------------------------

    protected function loadAllInstalledExtensions()
    {
        // Load installed extensions
        $arrExtensions = $this->Database
                ->prepare("SELECT * FROM tl_repository_installs")
                ->execute()
                ->fetchAllAssoc();

        $arrSort = array();

        foreach ($arrExtensions as $value)
        {
            $arrSort[$value["extension"]] = $value;
        }

        return $arrSort;
    }

    protected function loadFileListFromEr()
    {
        $arrInstalledExtensions = $this->loadAllInstalledExtensions();

        if (!key_exists("syncCto", $arrInstalledExtensions))
        {
            throw new Exception("SyncCto is not installed over the ER, please only use the ER version.");
        }

        $arrDependencies   = $this->objSyncCtoErClient->getDependenciesFor($arrInstalledExtensions['syncCto']['extension'], $arrInstalledExtensions['syncCto']['version']);
        $arrDependencies[] = array(
            "name" => $arrInstalledExtensions['syncCto']['extension'],
            "version" => $arrInstalledExtensions['syncCto']['version']
        );

        $arrDependenciesDone = array();

        while (count($arrDependencies) != 0)
        {
            $arrKeys = array_keys($arrDependencies);
            $mixKey  = $arrKeys[0];

            if (in_array($arrDependencies[$mixKey]['name'], $arrDependenciesDone))
            {
                unset($arrDependencies[$mixKey]);
                continue;
            }

            if (key_exists($arrDependencies[$mixKey]['name'], $arrInstalledExtensions))
            {
                $strExtensionName = $arrDependencies[$mixKey]['name'];

                $arrDependencies = array_merge($arrDependencies, $this->objSyncCtoErClient->getDependenciesFor($strExtensionName, $arrInstalledExtensions[$strExtensionName]['version']));
                $this->arrFiles = array_merge($this->arrFiles, $this->objSyncCtoErClient->getFileListFor($strExtensionName, $arrInstalledExtensions[$strExtensionName]['version']));
            }
            else
            {
                $arrDependencies = array_merge($arrDependencies, $this->objSyncCtoErClient->getDependenciesFor($arrDependencies[$mixKey]['name'], $arrDependencies[$mixKey]['version']));
                $this->arrFiles = array_merge($this->arrFiles, $this->objSyncCtoErClient->getFileListFor($arrDependencies[$mixKey]['name'], $arrDependencies[$mixKey]['version']));
            }

            $arrDependenciesDone[] = $arrDependencies[$mixKey]['name'];
            unset($arrDependencies[$mixKey]);
        }
    }

}

class SyncCtoErClient extends RepositoryBackendModule
{

    protected $strWSDL;

    /**
     * Initialize object (do not remove)
     */
    public function __construct()
    {
        parent::__construct();

        $this->strWSDL = trim($GLOBALS['TL_CONFIG']['repository_wsdl']);

        $this->client = new SoapClient($this->strWSDL,
                        array(
                            'soap_version' => SOAP_1_2,
                            'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | 1
                        )
        );
    }

    /**
     * Get a list with all files for one extension
     * 
     * @param string $strExtension Name of extension
     * @param int $intVersion Version of the extension
     */
    public function getFileListFor($strExtension, $intVersion)
    {
        $arrReturn = array();

        $options = array(
            'name' => $strExtension,
            'version' => $intVersion
        );

        $arrExtensionList = $this->client->getFileList($options);

        foreach ($arrExtensionList as $key => $value)
        {
            $arrReturn[$key]["path"] = (string) $value->path;
            $arrReturn[$key]["hash"] = (string) $value->hash;
            $arrReturn[$key]["size"] = (string) $value->size;
        }

        return $arrReturn;
    }

    public function getDependenciesFor($strExtension, $intVersion)
    {
        $arrReturn = array();

        $options = array(
            'names' => $strExtension,
            'versions' => $intVersion,
            'sets' => 'dependencies'
        );

        $arrExtensionList = $this->client->getExtensionList($options);

        foreach ($arrExtensionList as $key => $value)
        {
            if ($value->name == $strExtension && $value->version == $intVersion && is_array($value->dependencies))
            {
                $arrReturn = array();

                foreach ($value->dependencies as $dependenciesKey => $dependenciesValue)
                {
                    $arrReturn[$dependenciesKey]["name"]    = (string) $dependenciesValue->extension;
                    $arrReturn[$dependenciesKey]["version"] = (string) $dependenciesValue->maxversion;
                }

                return $arrReturn;
            }
        }

        return array();
    }

}

?>
