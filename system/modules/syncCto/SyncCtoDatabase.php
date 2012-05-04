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

/**
 * Core class for database operation
 */
class SyncCtoDatabase extends Backend
{
    /* -------------------------------------------------------------------------
     * Vars
     */

    // Singelten pattern
    protected static $instance    = null;
    
    // Vars 
    protected $arrBackupTables;
    protected $arrHiddenTables;
    protected $strSuffixZipName   = "DB-Backup.zip";
    protected $strFilenameSyncCto = "DB-Backup.synccto";
    protected $strFilenameSQL     = "DB-Backup.sql";
    protected $strTimestampFormat;
    protected $intMaxMemoryUsage;
    
    // Objects 
    protected $objSyncCtoHelper;
    
    /**
     * @var XMLReader 
     */
    protected $objXMLReader;

    /**
     * List of default ignore values
     * 
     * @var array
     */
    protected $arrDefaultValueFunctionIgnore = array(
        "NOW",
        "CURRENT_TIMESTAMP",
    );

    /**
     * List of default ignore values
     * 
     * @var array 
     */
    protected $arrDefaultValueTypIgnore = array(
        'text',
        'tinytext',
        'mediumtext',
        'longtext',
        'blob',
        'tinyblob',
        'mediumblob',
        'longblob',
        'time',
        'date',
        'datetime'
    );

    /**
     * Search for special chars
     * 
     * @var array 
     */
    protected $arrSearchFor = array(
        "\\",
        "'"
    );

    /**
     * Replace special chars with
     * 
     * @var array 
     */
    protected $arrReplaceWith = array(
        "\\\\",
        "\\'"
    );

    /* -------------------------------------------------------------------------
     * Core
     */

    /**
     * Constructor
     */
    protected function __construct()
    {
        parent::__construct();

        // Init some vars
        $this->arrBackupTables = array();
        $this->strTimestampFormat = standardize($GLOBALS['TL_CONFIG']['datimFormat']);
        $this->intMaxMemoryUsage = intval(str_replace(array("m", "M", "k", "K"), array("000000", "000000", "000", "000"), ini_get('memory_limit')));
        $this->intMaxMemoryUsage = $this->intMaxMemoryUsage / 100 * 80;

        // Load hidden tables
        $this->arrHiddenTables = deserialize($GLOBALS['SYC_CONFIG']['table_hidden']);
        if (!is_array($this->arrHiddenTables))
        {
            $this->arrHiddenTables = array();
        }

        // Load Helper
        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();
    }

    /**
     * Get instance of SyncCtoDatabase
     * 
     * @return SyncCtoDatabase 
     */
    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new SyncCtoDatabase();
        }

        return self::$instance;
    }

    /**
     * Setter
     * 
     * @param string $name
     * @param string $value 
     */
    public function __set($name, $value)
    {
        switch ($name)
        {
            case "backupTables":
                if (!is_array($value))
                {
                    $this->arrBackupTables[] = $value;
                }
                else
                {
                    $this->arrBackupTables = $value;
                }
                break;

            case "filenameInsert":
                $this->strFilenameInsert = $value;
                break;

            case "filenameSyncCto":
                $this->strFilenameSyncCto = $value;
                break;

            case "suffixZipname":
                $this->strSuffixZipName = $value;
                break;

            case "timestampFormat":
                $this->strTimestampFormat = $value;
                break;

            default:
                break;
        }
    }

    /**
     * Getter
     * 
     * @param string $name
     * @return string 
     */
    public function __get($name)
    {
        switch ($name)
        {
            case "backupTables":
                return $this->arrBackupTables;

            case "filenameSyncCto":
                return $this->strFilenameSyncCto;

            case "filenameSQL":
                return $this->strFilenameSQL;

            case "suffixZipname":
                return $this->strSuffixZipName;

            case "timestampFormat":
                return $this->strTimestampFormat;

            default:
                return null;
        }
    }

    /* -------------------------------------------------------------------------
     * Create functions
     */

    /**
     * Check if we have enough ram, if not, write all data to file
     * 
     * @param XMLWriter $objXml
     * @param resource $objGzFile 
     */
    protected function checkRAM(XMLWriter $objXml, $objGzFile)
    {
        if ($this->intMaxMemoryUsage < memory_get_usage(true))
        {
            $strXMLFlush = $objXml->flush(true);
            gzputs($objGzFile, $strXMLFlush, strlen($strXMLFlush));
        }
    }

    /**
     * Function for creating a sql/xml dump file.
     * 
     * @param array $mixTables Table or a list of tables for backup
     * @param string $strZip Name of zip file
     * @param bool $booTempFolder Should the tmp folde used instead of backupfolder
     * @return void 
     */
    public function runDump($mixTables, $booTempFolder, $booOnlyMachine = false)
    {
        // Set time limit to unlimited
        set_time_limit(0);

        // Add to the backup array all tables
        if (is_array($mixTables))
        {
            $this->arrBackupTables = array_merge($this->arrBackupTables, $mixTables);
        }
        else if ($mixTables != "" && $mixTables != null)
        {
            $this->arrBackupTables[] = $mixTables;
        }

        // make the backup array unique
        $this->arrBackupTables = array_unique($this->arrBackupTables);

        // Check if we have some tables for backup
        if (!is_array($this->arrBackupTables) || $this->arrBackupTables == null || count($this->arrBackupTables) == 0)
        {
            throw new Exception("No tables found for backup.");
        }

        // Get a list of all Tables
        $arrTables = $this->Database->listTables();

        // Write some tempfiles
        $strRandomToken = md5(time() . " | " . rand(0, 65535));

        // Write SQL file
        if ($booOnlyMachine == false)
        {
            $objFileSQL = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "TempSQLDump.$strRandomToken"));
            $objFileSQL->write("");
        }

        // Write gzip xml file
        $objGzFile = new File($this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "TempSyncCtoDump.$strRandomToken"));
        $objGzFile->write("");
        $objGzFile->close();

        // Compression
        $objGzFile = gzopen(TL_ROOT . "/" . $this->objSyncCtoHelper->standardizePath($GLOBALS['SYC_PATH']['tmp'], "TempSyncCtoDump.$strRandomToken"), "wb");

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

        foreach ($arrTables as $key => $TableName)
        {
            // Check if the current table marked as backup
            if (!in_array($TableName, $this->arrBackupTables))
            {
                continue;
            }

            // Check if we have enough ram
            $this->checkRAM($objXml, $objGzFile);

            // Get data
            $arrStructure = $this->getTableStructure($TableName);

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

        $objXml->startElement('data');

        foreach ($arrTables as $key => $TableName)
        {
            // Check if the current table marked as backup
            if (!in_array($TableName, $this->arrBackupTables))
            {
                continue;
            }

            // Check if we have enough ram
            $this->checkRAM($objXml, $objGzFile);

            // Check if table is in blacklist
            if (!in_array($TableName, $this->arrBackupTables))
            {
                continue;
            }

            // Get fields
            $fields = $this->Database->listFields($TableName);

            $arrFieldMeta = array();

            foreach ($fields as $key => $value)
            {
                if ($value["type"] == "index")
                {
                    continue;
                }

                $arrFieldMeta[$value["name"]] = $value;
            }

            $intElementsPerRequest = 500;

            $objXml->startElement('table');
            $objXml->writeAttribute('name', $TableName);

            for ($i = 0; true; $i++)
            {
                // Check if we have enough ram
                $this->checkRAM($objXml, $objGzFile);

                $objData = $this->Database
                        ->prepare("SELECT * FROM $TableName")
                        ->limit($intElementsPerRequest, ($i * $intElementsPerRequest))
                        ->executeUncached();

                if ($objData->numRows == 0)
                {
                    break;
                }

                while ($row = $objData->fetchAssoc())
                {
                    // Check if we have enough ram
                    $this->checkRAM($objXml, $objGzFile);

                    $objXml->startElement('row');
                    $objXml->writeAttribute("id", $row["id"]);

                    foreach ($row as $field_key => $field_data)
                    {
                        $objXml->startElement('field');
                        $objXml->writeAttribute("name", $field_key);
                        
                        if (!isset($field_data))
                        {
                            $objXml->writeAttribute("type", "null");
                            $objXml->text("NULL");
                        }
                        else if ($field_data != "")
                        {
                            switch (strtolower($arrFieldMeta[$field_key]['type']))
                            {
                                case 'binary':
                                case 'varbinary':
                                case 'blob':
                                case 'tinyblob':
                                case 'mediumblob':
                                case 'longblob':
                                    $objXml->writeAttribute("type", "blob");
                                    $objXml->text("0x" . bin2hex($field_data));
                                    break;

                                case 'tinyint':
                                case 'smallint':
                                case 'mediumint':
                                case 'int':
                                case 'integer':
                                case 'bigint':
                                    $objXml->writeAttribute("type", "int");
                                    $objXml->text($field_data);
                                    break;

                                case 'float':
                                case 'double':
                                case 'real':
                                case 'decimal':
                                case 'numeric':
                                    $objXml->writeAttribute("type", "decimal");
                                    $objXml->text($field_data);
                                    break;

                                case 'date':
                                case 'datetime':
                                case 'timestamp':
                                case 'time':
                                case 'year':
                                    $objXml->writeAttribute("type", "date");
                                    $objXml->text("'" . $field_data . "'");
                                    break;

                                case 'char':
                                case 'varchar':
                                case 'text':
                                case 'tinytext':
                                case 'mediumtext':
                                case 'longtext':
                                case 'enum':
                                case 'set':
                                    $objXml->writeAttribute("type", "text");
                                    $objXml->writeCdata("'" . str_replace($this->arrSearchFor, $this->arrReplaceWith, $field_data) . "'");
                                    break;

                                default:
                                    $objXml->writeAttribute("type", "default");
                                    $objXml->writeCdata("'" . str_replace($this->arrSearchFor, $this->arrReplaceWith, $field_data) . "'");
                                    break;
                            }
                        }
                        else
                        {
                            $objXml->writeAttribute("type", "empty");
                            $objXml->text("''");
                        }

                        $objXml->endElement(); // End field
                    }

                    $objXml->endElement(); // End row
                }
            }

            $objXml->endElement(); // End table
        }

        $objXml->endElement(); // End data

        $objXml->endElement(); // End database

        $strXMLFlush = $objXml->flush(true);
        gzputs($objGzFile, $strXMLFlush, strlen($strXMLFlush));
        gzclose($objGzFile);

        if ($booOnlyMachine == false)
        {
            // Write header for sql file
            $today = date("Y-m-d");
            $time  = date("H:i:s");

            // Write Header
            $string .= "-- syncCto SQL Dump\r\n";
            $string .= "-- Version " . $GLOBALS['SYC_VERSION'] . "\r\n";
            $string .= "-- http://men-at-work.de\r\n";
            $string .= "-- \r\n";
            $string .= "-- Time stamp : $today at $time\r\n";
            $string .= "\r\n";
            $string .= "SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";\r\n";
            $string .= "\r\n";
            $string .= "-- --------------------------------------------------------\r\n";
            $string .= "\r\n";

            $objFileSQL->append($string, "");
            $objFileSQL->close();
            $string = "";

            // Run each table
            foreach ($arrTables as $key => $TableName)
            {
                // Check if table is in blacklist
                if (!in_array($TableName, $this->arrBackupTables))
                {
                    continue;
                }

                // Get data
                $arrStructure = $this->getTableStructure($TableName);

                // Check if empty
                if (count($arrStructure) == 0)
                {
                    continue;
                }

                // Write SQL
                $string .= "-- \r\n";
                $string .= "-- Dumping table $TableName \r\n";
                $string .= "-- \r\n";
                $string .= "\r\n";
                $string .= $this->buildSQLTable($arrStructure, $TableName);
                $string .= "\r\n";
                $string .= "\r\n";

                $objFileSQL->append($string, "");
                $objFileSQL->close();
                $string = "";

                // Get fields
                $fields = $this->Database->listFields($TableName);

                $arrFieldMeta = array();

                foreach ($fields as $key => $value)
                {
                    if ($value["type"] == "index")
                    {
                        continue;
                    }

                    $arrFieldMeta[$value["name"]] = $value;
                }

                $intElementsPerRequest = 500;
                $booFirstEntry         = true;

                for ($i = 0; true; $i++)
                {
                    $objData = $this->Database
                            ->prepare("SELECT * FROM $TableName")
                            ->limit($intElementsPerRequest, ($i * $intElementsPerRequest))
                            ->executeUncached();

                    $strSQL = "";

                    // Check if we have some files
                    if ($objData->numRows == 0)
                    {
                        // if end reach insert ';'
                        if ($booFirstEntry != true)
                        {
                            $strSQL .= ";\r\n\r\n";
                        }

                        $strSQL .= "-- --------------------------------------------------------\r\n\r\n";

                        $objFileSQL->append($strSQL, "");
                        $objFileSQL->close();

                        break;
                    }

                    // Start INSERT INTO
                    if ($i == 0)
                    {
                        $strSQL .= "INSERT IGNORE INTO " . $TableName . " (`";
                        $strSQL .= implode("`, `", array_keys($arrFieldMeta));
                        $strSQL .= "`) VALUES";
                    }

                    // Run through each row
                    while ($row = $objData->fetchAssoc())
                    {
                        $arrTableData = array();

                        foreach (array_keys($arrFieldMeta) as $fieldName)
                        {
                            if (!isset($row[$fieldName]))
                            {
                                $arrTableData[] = "NULL";
                            }
                            else if ($row[$fieldName] != "")
                            {
                                switch (strtolower($arrFieldMeta[$fieldName]['type']))
                                {
                                    case 'blob':
                                    case 'tinyblob':
                                    case 'mediumblob':
                                    case 'longblob':
                                        $arrTableData[] = "0x" . bin2hex($row[$fieldName]);
                                        break;

                                    case 'smallint':
                                    case 'int':
                                        $arrTableData[] = $row[$fieldName];
                                        break;

                                    case 'text':
                                    case 'mediumtext':
                                        if (strpos($row[$fieldName], "'") != false)
                                        {
                                            $arrTableData[] = "0x" . bin2hex($row[$fieldName]);
                                            break;
                                        }
                                    default:
                                        $arrTableData[] = "'" . str_replace($this->arrSearchFor, $this->arrReplaceWith, $row[$fieldName]) . "'";
                                        break;
                                }
                            }
                            else
                            {
                                $arrTableData[] = "''";
                            }
                        }

                        if ($booFirstEntry == true)
                        {
                            $booFirstEntry = false;
                            $strSQL .= "\r\n(" . implode(", ", $arrTableData) . ")";
                        }
                        else
                        {
                            $strSQL .= ",\r\n(" . implode(", ", $arrTableData) . ")";
                        }

                        if (strlen($strSQL) > 100000)
                        {
                            $objFileSQL->append($strSQL, "");
                            $objFileSQL->close();
                            $strSQL = "";
                        }
                    }

                    if (strlen($strSQL) != 0)
                    {
                        $objFileSQL->append($strSQL, "");
                        $objFileSQL->close();
                        $strSQL = "";
                    }
                }
            }
        }

        if ($booOnlyMachine == false)
        {
            $objFileSQL->close();
        }

        $strFilename = date($this->strTimestampFormat) . "_" . $this->strSuffixZipName;

        if ($booTempFolder)
        {
            $strPath = $GLOBALS['SYC_PATH']['tmp'];
        }
        else
        {
            $strPath = $GLOBALS['SYC_PATH']['db'];
        }

        $objZipArchive = new ZipArchiveCto();
        $objZipArchive->open($strPath . $strFilename, ZipArchiveCto::CREATE);

        if ($booOnlyMachine == false)
        {
            $objZipArchive->addFile("system/tmp/TempSQLDump.$strRandomToken", $this->strFilenameSQL);
        }

        $objZipArchive->addFile("system/tmp/TempSyncCtoDump.$strRandomToken", $this->strFilenameSyncCto);

        $objZipArchive->close();

        $objFiles = Files::getInstance();

        if ($booOnlyMachine == false)
        {
            $objFiles->delete("system/tmp/TempSQLDump.$strRandomToken");
        }
        $objFiles->delete("system/tmp/TempSyncCtoDump.$strRandomToken");

        return $strFilename;
    }

    protected function doRestoreStructure()
    {
        // Buffer
        $arrTables = array();

        // Current Values
        $strCurrentTable         = "";
        $strCurrentNodeAttribute = "";
        $strCurrentNodeName      = "";

        while ($this->objXMLReader->read())
        {
            switch ($this->objXMLReader->nodeType)
            {
                case XMLReader::TEXT:
                case XMLReader::CDATA:
                    switch ($strCurrentNodeName)
                    {
                        case "field":
                            $arrTables[$strCurrentTable]['TABLE_FIELDS'][$strCurrentNodeAttribute] = $this->objXMLReader->value;
                            break;

                        case "option":
                            $arrTables[$strCurrentTable]['TABLE_OPTIONS'] = $this->objXMLReader->value;
                            break;

                        case "def":
                            $arrTables[$strCurrentTable]['TABLE_CREATE_DEFINITIONS'][$strCurrentNodeAttribute] = $this->objXMLReader->value;
                            break;
                    }
                    break;

                case XMLReader::ELEMENT:
                    $strCurrentNodeName = $this->objXMLReader->localName;

                    switch ($this->objXMLReader->localName)
                    {
                        case "table":
                            $strCurrentTable = $this->objXMLReader->getAttribute("name");
                            break;

                        case "def":
                        case "option":
                        case "field":
                            $strCurrentNodeAttribute = $this->objXMLReader->getAttribute("name");
                            break;
                    }
                    break;

                case XMLReader::END_ELEMENT:
                    switch ($this->objXMLReader->localName)
                    {
                        case "structure":
                            $arrRestored = array();

                            try
                            {
                                foreach ($arrTables as $key => $value)
                                {
                                    if (empty($value))
                                    {
                                        continue;
                                    }

                                    $this->Database->query("DROP TABLE IF EXISTS " . "synccto_temp_" . $key);
                                    $this->Database->query($this->buildSQLTable($value, "synccto_temp_" . $key));

                                    $arrRestored[] = $key;
                                }
                            }
                            catch (Exception $exc)
                            {
                                foreach ($arrRestored as $key => $value)
                                {
                                    $this->Database->query("DROP TABLE IF EXISTS " . "synccto_temp_" . $value);
                                }

                                throw $exc;
                            }

                            return $arrRestored;
                    }
                    break;
            }
        }
    }

    protected function doRestoreData()
    {
        // Config
        $intMaxInsert = 1000;

        // Buffer
        $arrValues = array();
        $arrFields = array();

        // Current Values
        $strCurrentTable             = "";
        $strCurrentNodeAttributeName = "";
        $strCurrentNodeAttributeType = "";
        $strCurrentNodeName          = "";
        $intCounter                  = 0;
       
        while ($this->objXMLReader->read())
        {
            switch ($this->objXMLReader->nodeType)
            {
                case XMLReader::TEXT:
                case XMLReader::CDATA:
                    switch ($strCurrentNodeName)
                    {
                        case "field":
                            $arrValues[$intCounter][$strCurrentNodeAttributeName] = $this->objXMLReader->value;
                            break;
                    }
                    break;

                case XMLReader::ELEMENT:
                    $strCurrentNodeName = $this->objXMLReader->localName;
                    switch ($this->objXMLReader->localName)
                    {
                        case "table":
                            $strCurrentTable = $this->objXMLReader->getAttribute("name");
                            $arrValues       = array();
                            $arrFields = array();
                            $intCounter = 0;
                            break;

                        case "field":
                            $strCurrentNodeAttributeName = $this->objXMLReader->getAttribute("name");
                            $strCurrentNodeAttributeType = $this->objXMLReader->getAttribute("type");

                            if (!in_array($strCurrentNodeAttributeName, $arrFields))
                            {
                                $arrFields[] = $strCurrentNodeAttributeName;
                            }
                            break;
                    }
                    break;

                case XMLReader::END_ELEMENT:
                    switch ($this->objXMLReader->localName)
                    {
                        case "row":
                            $intCounter++;
                            if (count($arrValues) >= $intMaxInsert)
                            {
                                $strBody = "INSERT INTO synccto_temp_" . $strCurrentTable . " (`";
                                $strBody .= implode("`, `", $arrFields);
                                $strBody .= "`) VALUES \n";

                                foreach ($arrValues as $keyValue => $valueValue)
                                {
                                    $arrInsertValue = array();
                                    foreach ($arrFields as $keyField => $valueField)
                                    {
                                        $arrInsertValue[] = $valueValue[$valueField];
                                    }

                                    $strBody .= "(" . implode(",", $arrInsertValue) . "),\n";
                                }

                                $strBody = preg_replace("/,\\n$/", "", $strBody);

                                $this->Database->query($strBody);

                                $arrValues = array();
                            }
                            break;

                        case "table":
                            if (count($arrValues) == 0)
                            {
                                break;
                            }

                            $strBody = "INSERT INTO synccto_temp_" . $strCurrentTable . " (`";
                            $strBody .= implode("`, `", $arrFields);
                            $strBody .= "`) VALUES \n";

                            foreach ($arrValues as $keyValue => $valueValue)
                            {
                                $arrInsertValue = array();
                                foreach ($arrFields as $keyField => $valueField)
                                {
                                    $arrInsertValue[] = $valueValue[$valueField];
                                }

                                $strBody .= "(" . implode(",", $arrInsertValue) . "),\n";
                            }

                            $strBody = preg_replace("/,\\n$/", "", $strBody);

                            $this->Database->query($strBody);

                            $arrValues = array();

                            break;
                    }
                    break;
            }
        }        
    }

    /**
     * Restore database-backup from zip
     * 
     * @param string $strRestoreFile Path to file like system/backup/backup.zip
     * @param bool $booTruncate 
     * @return type 
     */
    public function runRestore($strRestoreFile)
    {
        // Set time out for database. Ticket #2653
        if ($GLOBALS['TL_CONFIG']['syncCto_custom_settings'] == true
                && intval($GLOBALS['TL_CONFIG']['syncCto_wait_timeout']) > 0
                && intval($GLOBALS['TL_CONFIG']['syncCto_interactive_timeout']) > 0)
        {
            $this->Database->query('SET SESSION wait_timeout = GREATEST(' . intval($GLOBALS['TL_CONFIG']['syncCto_wait_timeout']) . ', @@wait_timeout), SESSION interactive_timeout = GREATEST(' . intval($GLOBALS['TL_CONFIG']['syncCto_interactive_timeout']) . ', @@wait_timeout);');
        }
        else
        {
            $this->Database->query('SET SESSION wait_timeout = GREATEST(28000, @@wait_timeout), SESSION interactive_timeout = GREATEST(28000, @@wait_timeout);');
        }

        switch (pathinfo($strRestoreFile, PATHINFO_EXTENSION))
        {
            case "zip":
                $objZipRead = new ZipReader($strRestoreFile);

                // Get structure
                if ($objZipRead->getFile($this->strFilenameSyncCto))
                {
                    $objGzFile = new File("system/tmp/$this->strFilenameSyncCto.gz");
                    $objGzFile->write($objZipRead->unzip());
                    $objGzFile->close();

                    $arrRestoreTables = $this->runRestoreFromXML("system/tmp/$this->strFilenameSyncCto.gz");
                }
                else
                {
                    $arrRestoreTables = $this->runRestoreFromSer($strRestoreFile);
                }
                break;

            case "synccto":
                $arrRestoreTables = $this->runRestoreFromXML($strRestoreFile);
                break;

            default:
                throw new Exception("Not supportet or Unknown file type.");
                break;
        }
        
        // Rename temp tables
        foreach ($arrRestoreTables as $key => $value)
        {
            $this->Database->query("DROP TABLE IF EXISTS " . $value);
            $this->Database->query("RENAME TABLE " . "synccto_temp_" . $value . " TO " . $value);
        }

        // Get a list of all Tables
        foreach ($this->Database->listTables() as $key => $value)
        {
            if (preg_match("/synccto_temp_.*/", $value))
            {
                $this->Database->query("DROP TABLE IF EXISTS $value");
            }
        }

        return;
    }

    protected function runRestoreFromXML($strRestoreFile)
    {
        // Unzip XML
        $objGzFile = gzopen(TL_ROOT . "/" . $strRestoreFile, "r");

        $objXMLFile = new File("system/tmp/" . basename($strRestoreFile) . ".xml");
        $objXMLFile->write("");
        $objXMLFile->close();

        while (true)
        {
            $strConten = gzread($objGzFile, 500000);

            if ($strConten == false || empty($strConten))
            {
                break;
            }

            $objXMLFile->append($strConten, "");
            $objXMLFile->close();
        }

        // Read XML
        $this->objXMLReader = new XMLReader();
        $this->objXMLReader->open(TL_ROOT . "/system/tmp/" . basename($strRestoreFile) . ".xml");

        while ($this->objXMLReader->read())
        {
            switch ($this->objXMLReader->nodeType)
            {
                case XMLReader::ELEMENT:
                    switch ($this->objXMLReader->localName)
                    {
                        case "structure":
                            $arrRestoreTables = $this->doRestoreStructure();
                            break;

                        case "data":
                            $this->doRestoreData();
                            break;
                    }
                    break;
            }
        }
        
        $objXMLFile->delete();

        return $arrRestoreTables;
    }

    protected function runRestoreFromSer($strRestoreFile)
    {
        $objZipArchive    = new ZipArchiveCto();
        $objTempfile      = tmpfile();
        $arrRestoreTables = array();

        try
        {
            // Open ZIP Archive
            $objZipArchive->open($strRestoreFile);

            // Get structure
            if ($objZipArchive->locateName($this->strFilenameTable) === false)
            {
                throw new Exception("Could not load SQL file table. Maybe damaged?");
            }

            $mixTables = $objZipArchive->getFromName($this->strFilenameTable);
            $mixTables = trimsplit("\n", $mixTables);

            // Create temp tables
            foreach ($mixTables as $key => $value)
            {
                if (empty($value))
                {
                    continue;
                }

                $value = deserialize($value);

                if (!is_array($value))
                {
                    throw new Exception("Could not load SQL file table. Maybe damaged?");
                }

                $this->Database->query("DROP TABLE IF EXISTS " . "synccto_temp_" . $value["name"]);
                $this->Database->query($this->buildSQLTable($value["value"], "synccto_temp_" . $value["name"]));

                $arrRestoreTables[] = $value["name"];
            }

            // Get insert
            if ($objZipArchive->locateName($this->strFilenameInsert) === false)
            {
                throw new Exception("Could not load SQL file inserts. Maybe damaged?");
            }

            $strContent = $objZipArchive->getFromName($this->strFilenameInsert);

            // Write temp File

            fputs($objTempfile, $strContent, strlen($strContent));

            unset($strContent);

            // Set pointer on position zero
            rewind($objTempfile);

            $i       = 0;
            while ($mixLine = fgets($objTempfile))
            {
                $i++;

                if (empty($mixLine) || strlen($mixLine) == 0)
                {
                    continue;
                }

                $mixLine = json_decode(@gzuncompress(base64_decode($mixLine)), true);

                if ($mixLine == FALSE)
                {
                    throw new Exception("Could not load SQL file inserts or unzip it. Maybe damaged on line $i?");
                }

                if (!is_array($mixLine))
                {
                    throw new Exception("Could not load SQL file inserts. Maybe damaged on line $i?");
                }

                $strSQL = $this->buildSQLInsert("synccto_temp_" . $mixLine['table'], array_keys($mixLine['values']), $mixLine['values'], true);
                $this->Database->query($strSQL);
            }

            $objZipArchive->close();
            fclose($objTempfile);

            return $arrRestoreTables;
        }
        catch (Exception $exc)
        {
            foreach ($arrRestoreTables as $key => $value)
            {
                $this->Database->query("DROP TABLE IF EXISTS " . "synccto_temp_" . $value);
            }

            $objZipArchive->close();
            fclose($objTempfile);

            throw $exc;
        }
    }

    /* -------------------------------------------------------------------------
     * Helper Functions for building tables and inserts.
     */

    /**
     * Build a array with the structur of the database
     * 
     * @return array 
     */
    private function getTableStructure($strTableName)
    {
        $return = array();

        // Get list of fields
        $fields = $this->Database->listFields($strTableName);

        // Get list of indicies
        $arrIndexes = $this->Database->prepare("SHOW INDEX FROM `$strTableName`")->executeUncached()->fetchAllAssoc();



        foreach ($fields as $field)
        {
            if ($field["type"] == "index")
            {
                if ($field["name"] == "PRIMARY")
                {
                    $return['TABLE_CREATE_DEFINITIONS'][$field["name"]] = "PRIMARY KEY (`" . implode("`,`", $field["index_fields"]) . "`)";
                }
                else if ($field["index"] == "UNIQUE")
                {
                    $return['TABLE_CREATE_DEFINITIONS'][$field["name"]] = "UNIQUE KEY `" . $field["name"] . "` (`" . implode("`,`", $field["index_fields"]) . "`)";
                }
                else if ($field["index"] == "KEY")
                {
                    foreach ($arrIndexes as $valueIndexes)
                    {
                        if ($valueIndexes["Key_name"] == $field["name"])
                        {
                            switch ($valueIndexes["Index_type"])
                            {
                                case "FULLTEXT":
                                    $return['TABLE_CREATE_DEFINITIONS'][$field["name"]] = "FULLTEXT KEY `" . $field['name'] . "` (`" . implode("`,`", $field["index_fields"]) . "`)";
                                    break;

                                default:
                                    $return['TABLE_CREATE_DEFINITIONS'][$field["name"]] = "KEY `" . $field['name'] . "` (`" . implode("`,`", $field["index_fields"]) . "`)";
                                    break;
                            }

                            break;
                        }
                    }
                }

                continue;
            }

            unset($field['index']);

            $name          = $field['name'];
            $field['name'] = '`' . $field['name'] . '`';

            // Field type
            if (strlen($field['length']))
            {
                $field['type'] .= '(' . $field['length'] . (strlen($field['precision']) ? ',' . $field['precision'] : '') . ')';

                unset($field['length']);
                unset($field['precision']);
            }

            // Default values
            if (in_array(strtolower($field['type']), $this->arrDefaultValueTypIgnore) || stristr($field['extra'], 'auto_increment'))
            {
                unset($field['default']);
            }
            else if (strtolower($field['default']) == 'null')
            {
                $field['default'] = "default NULL";
            }
            else if (is_null($field['default']))
            {
                $field['default'] = "";
            }
            else if (in_array(strtoupper($field['default']), $this->arrDefaultValueFunctionIgnore))
            {
                $field['default'] = "default " . $field['default'];
            }
            else
            {
                $field['default'] = "default '" . $field['default'] . "'";
            }

            $return['TABLE_FIELDS'][$name] = trim(implode(' ', $field));
        }

        // Table status
        $objStatus = $this->Database->prepare("SHOW TABLE STATUS")->executeUncached();

        while ($row = $objStatus->fetchAssoc())
        {
            if ($row['Name'] != $strTableName)
                continue;

            $return['TABLE_OPTIONS'] = " ENGINE=" . $row['Engine'] . " DEFAULT CHARSET=" . substr($row['Collation'], 0, strpos($row['Collation'], "_")) . "";
            if ($row['Auto_increment'] != "")
                $return['TABLE_OPTIONS'] .= " AUTO_INCREMENT=" . $row['Auto_increment'] . " ";
        }

        return $return;
    }

    /**
     * Build a "CREATE TABLE" sql statemant
     * 
     * @param array $arrTable Table Informations
     * @param type $strName Table name
     * @return string 
     */
    private function buildSQLTable($arrTable, $strName)
    {
        $string = "CREATE TABLE `" . $strName . "` (\n  " . implode(",\n  ", $arrTable['TABLE_FIELDS']) . (count($arrTable['TABLE_CREATE_DEFINITIONS']) ? ',' : '') . "\n";

        if (is_Array($arrTable['TABLE_CREATE_DEFINITIONS']))
            $string .= "  " . implode(",\n  ", $arrTable['TABLE_CREATE_DEFINITIONS']) . "\n";

        $string .= ")" . $arrTable['TABLE_OPTIONS'] . ";";

        return $string;
    }

    /**
     * Build a sql statement for "INSERT IGNORE INTO"
     * 
     * @param type $strTable Table name
     * @param type $arrKeys Columnames
     * @param type $arrData Data for insert
     * @return string 
     */
    private function buildSQLInsert($strTable, $arrKeys, $arrData, $booPrepare = false)
    {
        $strBody = "INSERT IGNORE INTO " . $strTable . " (`";
        $strBody .= implode("`, `", $arrKeys);
        $strBody .= "`) VALUES ( ";

        for ($i = 0; $i < count($arrKeys); $i++)
        {
            if (isset($arrData[$arrKeys[$i]]))
            {
                $strBody .= $arrData[$arrKeys[$i]];
            }
            else
            {
                $strBody .= "''";
            }

            if ($i < count($arrKeys) - 1)
                $strBody .= ", ";
        }

        $strBody .= ")";

        if ($booPrepare)
        {
            return $strBody;
        }
        else
        {
            return $strBody;
        }
    }

}

?>