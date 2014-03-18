<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL 
 * @filesource
 */

/**
 * ER Client 
 */
class SyncCtoRunOnceEr extends RepositoryBackendModule
{

    protected $strWSDL;
    protected $arrFiles = array();
    protected $blnNoError = true;

    /**
     * Initialize object (do not remove)
     */
    public function __construct()
    {
        try
        {
            parent::__construct();

            $this->strWSDL = trim($GLOBALS['TL_CONFIG']['repository_wsdl']);

            $this->client = new SoapClient($this->strWSDL,
                            array(
                                'soap_version' => SOAP_1_2,
                                'compression'  => SOAP_COMPRESSION_ACCEPT | ZLIB_ENCODING_GZIP | 1
                            )
            );
        }
        catch (Exception $exc)
        {
            $this->log($exc->getMessage(), __CLASS__ . ' ' . __FUNCTION__, 'ERROR');
            $this->blnNoError = false;
        }
    }

    public function run()
    {
        if ($this->blnNoError == false)
        {
            return;
        }

        try
        {
            $this->loadFilelist();
            $this->writeXML();
        }
        catch (Exception $exc)
        {
            $this->log($exc->getMessage(), __CLASS__ . ' ' . __FUNCTION__, TL_ERROR);
            $_SESSION['TL_ERROR'][] = $exc->getMessage();
        }
    }

    protected function writeXML()
    {
        // Create XML File
        $objXml = new XMLWriter();
        $objXml->openMemory();
        $objXml->setIndent(true);
        $objXml->setIndentString("\t");

        // XML Start
        $objXml->startDocument('1.0', 'UTF-8');
        $objXml->startElement('dependencies_filelist');

        // Write meta (header)
        $objXml->startElement('metatags');
        $objXml->writeElement('version', $GLOBALS['SYC_VERSION']);
        $objXml->writeElement('create_unix', time());
        $objXml->writeElement('create_date', date('Y-m-d', time()));
        $objXml->writeElement('create_time', date('H:i', time()));
        $objXml->endElement(); // End metatags

        foreach ($this->arrFiles as $strDependencies => $arrFiles)
        {
            $objXml->startElement('dependency');
            $objXml->writeAttribute('name', $strDependencies);

            foreach ($arrFiles as $arrFile)
            {
                $objXml->startElement('file');
                $objXml->writeAttribute('id', $arrFile['hash']);

                $objXml->writeElement('path', $arrFile['path']);
                $objXml->writeElement('size', $arrFile['size']);
                $objXml->writeElement('hash', $arrFile['hash']);

                $objXml->endElement(); // End file
            }

            $objXml->endElement(); // End dependency
        }

        $objXml->endElement(); // End doc

        $objFile = new File($GLOBALS['TL_CONFIG']['uploadPath'] . '/syncCto_backups/dependencies.xml');
        $objFile->write($objXml->flush());
        $objFile->close();
    }

    protected function loadFilelist()
    {
        $arrInstalledExtensions = $this->loadAllInstalledExtensions();

        if (!key_exists("syncCto", $arrInstalledExtensions))
        {
            throw new Exception('syncCto is not installed via the Extension Repository, please only use the official version.');
        }

        $arrDependencies   = $this->getDependenciesFor($arrInstalledExtensions['syncCto']['extension'], $arrInstalledExtensions['syncCto']['version']);
        $arrDependencies[] = array(
            "name"    => $arrInstalledExtensions['syncCto']['extension'],
            "version" => $arrInstalledExtensions['syncCto']['version']
        );

        $arrDependenciesDone = array();

        while (count($arrDependencies) != 0)
        {
            $arrEntry = array_pop($arrDependencies);

            if (in_array($arrEntry['name'], $arrDependenciesDone))
            {
                continue;
            }

            if (key_exists($arrEntry['name'], $arrInstalledExtensions))
            {
                $strExtensionName = $arrEntry['name'];

                $arrDependencies                   = array_merge($arrDependencies, $this->getDependenciesFor($strExtensionName, $arrInstalledExtensions[$strExtensionName]['version']));
                $this->arrFiles[$strExtensionName] = $this->getFileListFor($strExtensionName, $arrInstalledExtensions[$strExtensionName]['version']);
            }
            else
            {
                $strExtensionName = $arrEntry['name'];

                $arrDependencies                   = array_merge($arrDependencies, $this->getDependenciesFor($strExtensionName, $arrEntry['version']));
                $this->arrFiles[$strExtensionName] = $this->getFileListFor($strExtensionName, $arrEntry['version']);
            }

            $arrDependenciesDone[] = $arrEntry['name'];
        }
    }

    // - Helper ----------------------------------------------------------------

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
            'name'    => $strExtension,
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
            'names'    => $strExtension,
            'versions' => $intVersion,
            'sets'     => 'dependencies'
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

    // Helper

    /**
     * Standardize path for folder
     * No TL_ROOT, No starting /
     * 
     * @return string the normalized path
     */
    public function standardizePath()
    {
        $arrPath = func_get_args();

        if (count($arrPath) == 0 || $arrPath == null || $arrPath == "")
        {
            return "";
        }

        $strVar = "";

        foreach ($arrPath as $itPath)
        {
            $itPath = str_replace(array(TL_ROOT, "\\"), array("", "/"), $itPath);
            $itPath = explode("/", $itPath);

            foreach ($itPath as $itFolder)
            {
                if ($itFolder == "" || $itFolder == "." || $itFolder == "..")
                {
                    continue;
                }

                $strVar .= "/" . $itFolder;
            }
        }

        return preg_replace("/^\//i", "", $strVar);
    }

}