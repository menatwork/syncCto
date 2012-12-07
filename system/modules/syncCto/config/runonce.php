<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

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

/**
 * ER Client 
 */
class SyncCtoErClient extends RepositoryBackendModule
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
								'soap_version'	 => SOAP_1_2,
								'compression'	 => SOAP_COMPRESSION_ACCEPT | ZLIB_ENCODING_GZIP | 1
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
		$this->checkFolders();


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

	protected function checkFolders()
	{
		// Get folders from config
		$strBackupDB	 = $this->standardizePath($GLOBALS['SYC_PATH']['db']);
		$strBackupFile	 = $this->standardizePath($GLOBALS['SYC_PATH']['file']);
		$strTemp		 = $this->standardizePath($GLOBALS['SYC_PATH']['tmp']);

		$objHt	 = new File('system/modules/syncCto/config/.htaccess');
		$strHT	 = $objHt->getContent();
		$objHt->close();

		// Check each one 
		if (!file_exists(TL_ROOT . '/' . $strBackupDB))
		{
			new Folder($strBackupDB);

			$objFile = new File($strBackupDB . '/' . '.htaccess');
			$objFile->write($strHT);
			$objFile->close();
		}

		if (!file_exists(TL_ROOT . '/' . $strBackupFile))
		{
			new Folder($strBackupFile);

			$objFile = new File($strBackupFile . '/' . '.htaccess');
			$objFile->write($strHT);
			$objFile->close();
		}

		if (!file_exists(TL_ROOT . '/' . $strTemp))
		{
			new Folder($strTemp);
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

		$objFile = new File('tl_files/syncCto_backups/dependencies.xml');
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

		$arrDependencies	 = $this->getDependenciesFor($arrInstalledExtensions['syncCto']['extension'], $arrInstalledExtensions['syncCto']['version']);
		$arrDependencies[]	 = array(
			"name"		 => $arrInstalledExtensions['syncCto']['extension'],
			"version"	 => $arrInstalledExtensions['syncCto']['version']
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

				$arrDependencies					 = array_merge($arrDependencies, $this->getDependenciesFor($strExtensionName, $arrInstalledExtensions[$strExtensionName]['version']));
				$this->arrFiles[$strExtensionName]	 = $this->getFileListFor($strExtensionName, $arrInstalledExtensions[$strExtensionName]['version']);
			}
			else
			{
				$strExtensionName = $arrEntry['name'];

				$arrDependencies					 = array_merge($arrDependencies, $this->getDependenciesFor($strExtensionName, $arrEntry['version']));
				$this->arrFiles[$strExtensionName]	 = $this->getFileListFor($strExtensionName, $arrEntry['version']);
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
			'name'		 => $strExtension,
			'version'	 => $intVersion
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
			'names'		 => $strExtension,
			'versions'	 => $intVersion,
			'sets'		 => 'dependencies'
		);

		$arrExtensionList = $this->client->getExtensionList($options);

		foreach ($arrExtensionList as $key => $value)
		{
			if ($value->name == $strExtension && $value->version == $intVersion && is_array($value->dependencies))
			{
				$arrReturn = array();

				foreach ($value->dependencies as $dependenciesKey => $dependenciesValue)
				{
					$arrReturn[$dependenciesKey]["name"]	 = (string) $dependenciesValue->extension;
					$arrReturn[$dependenciesKey]["version"]	 = (string) $dependenciesValue->maxversion;
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

$objSyncCtoErClient = new SyncCtoErClient();
$objSyncCtoErClient->run();
?>
