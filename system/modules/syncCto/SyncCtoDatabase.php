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
 * Core class for database operation
 */
class SyncCtoDatabase extends Backend
{
    /* -------------------------------------------------------------------------
     * Vars
     */

    // Singelten pattern
    protected static $instance           = null;
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
    protected $Database;

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
     * A list with allowed keys for the field.
     * 
     * @var array 
     */
    protected $arrAllowedFieldKeys = array(
        'name',
        'type',
        'attributes',
        'null',
        'extra',
        'default'
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
        $this->strTimestampFormat = str_replace(array_keys($GLOBALS['SYC_CONFIG']['folder_file_replacement']), array_values($GLOBALS['SYC_CONFIG']['folder_file_replacement']), $GLOBALS['TL_CONFIG']['datimFormat']);
        $this->intMaxMemoryUsage = SyncCtoModuleClient::parseSize(ini_get('memory_limit'));
        $this->intMaxMemoryUsage = $this->intMaxMemoryUsage / 100 * 80;

        // Load hidden tables
        $this->arrHiddenTables = deserialize($GLOBALS['SYC_CONFIG']['table_hidden']);
        if (!is_array($this->arrHiddenTables))
        {
            $this->arrHiddenTables = array();
        }

        // Load Helper
        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();
        $this->Database         = Database::getInstance();       
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
     * Delete functions
     */

    /**
     * Drop tables
     * 
     * @param array $arrTables List with tables
     * @param boolean $blnBackup if true the system will make a bakup from all tables
     */
    public function dropTable($arrTables, $blnBackup = true)
    {
        if ($blnBackup == true)
        {
            $this->strSuffixZipName = 'Auto-DB-Backup_RPC-Drop.zip';
            $this->runDump($arrTables, false);
        }

        $arrKnownTables = $this->Database->listTables();

        foreach ($arrTables as $value)
        {
            if (in_array($value, $arrKnownTables))
            {
                $this->Database->query("DROP TABLE $value");
            }
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
    public function runDump($mixTables, $booTempFolder, $booOnlyMachine = true)
    {
        // Set time limit to unlimited
        set_time_limit(0);

        // Set limit for db query. Ticket #163
        if ($GLOBALS['TL_CONFIG']['syncCto_custom_settings'] == true && intval($GLOBALS['TL_CONFIG']['syncCto_db_query_limt']) > 0)
        {
            $intElementsPerRequest = intval($GLOBALS['TL_CONFIG']['syncCto_db_query_limt']);
        }
        else
        {
            $intElementsPerRequest = 500;
        }

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

        // Push structure into file.
        $strXMLFlush = $objXml->flush(true);
        gzputs($objGzFile, $strXMLFlush, strlen($strXMLFlush));

        $objXml->endElement(); // End structure

        $objXml->startElement('data');

        foreach ($arrTables as $key => $TableName)
        {
            // Check if the current table marked as backup
            if (!in_array($TableName, $this->arrBackupTables))
            {
                continue;
            }

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

            $objXml->startElement('table');
            $objXml->writeAttribute('name', $TableName);

            for ($i = 0; true; $i++)
            {
                // Push into file.
                $strXMLFlush = $objXml->flush(true);
                gzputs($objGzFile, $strXMLFlush, strlen($strXMLFlush));

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
                                    $objXml->writeCdata(base64_encode(str_replace($this->arrSearchFor, $this->arrReplaceWith, $field_data)));

                                    break;

                                default:
                                    $objXml->writeAttribute("type", "default");
                                    $objXml->writeCdata(base64_encode(str_replace($this->arrSearchFor, $this->arrReplaceWith, $field_data)));
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
                            if ($strCurrentNodeAttributeType == "text" || $strCurrentNodeAttributeType == "default")
                            {
                                $arrValues[$intCounter][$strCurrentNodeAttributeName] = "'" . base64_decode($this->objXMLReader->value) . "'";
                            }
                            else
                            {
                                $arrValues[$intCounter][$strCurrentNodeAttributeName] = $this->objXMLReader->value;
                            }

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
    public function runRestore($strRestoreFile, $arrSuffixSQL = null)
    {
        try
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

            // After insert, call some SQL
            if (is_array($arrSuffixSQL))
            {
                foreach ($arrSuffixSQL as $key => $value)
                {
                    $this->Database->query($value['query']);
                }
            }

            // Rename temp tables
            foreach ($arrRestoreTables as $key => $value)
            {
                $this->Database->query("DROP TABLE IF EXISTS " . $value);
                $this->Database->query("RENAME TABLE " . "synccto_temp_" . $value . " TO " . $value);
            }
        }
        catch (Exception $exc)
        {
            // Drop synccto_temp tables
            foreach ($this->Database->listTables() as $key => $value)
            {
                if (preg_match("/synccto_temp_.*/", $value))
                {
                    $this->Database->query("DROP TABLE IF EXISTS $value");
                }
            }

            throw $exc;
        }

        // Drop synccto_temp tables
        foreach ($this->Database->listTables() as $key => $value)
        {
            if (preg_match("/synccto_temp_.*/", $value))
            {
                $this->Database->query("DROP TABLE IF EXISTS $value");
            }
        }

        return $arrSuffixSQL;
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

                $value = unserialize($value);

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
     * Diff function
     */

    /**
     * Build the compare list for the database.
     *
     * @param array  $arrSourceTables           A list with all tables from the source.
     *
     * @param array  $arrDesTables              A list with all tables from the destination.
     *
     * @param array  $arrHiddenTables           A list with hidden tables. Merged from source and destination.
     *
     * @param array  $arrHiddenTablePlaceholder A list with regex expressions for the filter. The same like the $arrHiddenTables.
     *
     * @param array  $arrSourceTS               List with timestamps from the source.
     *
     * @param array  $arrDesTS                  List with timestamps from the destination.
     *
     * @param array  $arrAllowedTables          List with allowed tables. For example based on the user settings/rights.
     *
     * @param string $strSrcName                Name of the source e.g. client or server.
     *
     * @param string $strDesName                Name of the destination e.g. client or server.
     *
     * @return array
     */
    public function getFormatedCompareList($arrSourceTables, $arrDesTables, $arrHiddenTables, $arrHiddenTablePlaceholder, $arrSourceTS, $arrDesTS, $arrAllowedTables, $strSrcName, $strDesName)
    {
        // Remove hidden tables or tables without permission.
        if (is_array($arrHiddenTables) && count($arrHiddenTables) != 0)
        {
            foreach ($arrSourceTables as $key => $value)
            {
                if (in_array($key, $arrHiddenTables) || (is_array($arrAllowedTables) && in_array($key, $arrAllowedTables)))
                {
                    unset($arrSourceTables[$key]);
                }
            }

            foreach ($arrDesTables as $key => $value)
            {
                if (in_array($key, $arrHiddenTables) || (is_array($arrAllowedTables) && in_array($key, $arrAllowedTables)))
                {
                    unset($arrDesTables[$key]);
                }
            }
        }

        // Remove hidden tables based on the regex.
        if (is_array($arrHiddenTablePlaceholder) && count($arrHiddenTablePlaceholder) != 0)
        {
            foreach ($arrHiddenTablePlaceholder as $strRegex)
            {
                // Run each and check it with the given name.
                foreach ($arrSourceTables as $key => $value)
                {
                    if (preg_match('/^' . $strRegex . '$/', $key))
                    {
                        unset($arrSourceTables[$key]);
                    }
                }

                // Run each and check it with the given name.
                foreach ($arrDesTables as $key => $value)
                {
                    if (preg_match('/^' . $strRegex . '$/', $key))
                    {
                        unset($arrDesTables[$key]);
                    }
                }
            }
        }

        $arrCompareList = array();

        // Make a diff
        $arrMissingOnDes    = array_diff(array_keys($arrSourceTables), array_keys($arrDesTables));
        $arrMissingOnSource = array_diff(array_keys($arrDesTables), array_keys($arrSourceTables));

        // New Tables
        foreach ($arrMissingOnDes as $keySrcTables)
        {
            $strType = $arrSourceTables[$keySrcTables]['type'];

            $arrCompareList[$strType][$keySrcTables][$strSrcName]['name']    = $keySrcTables;
            $arrCompareList[$strType][$keySrcTables][$strSrcName]['tooltip'] = $this->getReadableSize($arrSourceTables[$keySrcTables]['size'])
                    . ', '
                    . vsprintf(($arrSourceTables[$keySrcTables]['count'] == 1) ? $GLOBALS['TL_LANG']['MSC']['entry'] : $GLOBALS['TL_LANG']['MSC']['entries'], array($arrSourceTables[$keySrcTables]['count']));

            $arrCompareList[$strType][$keySrcTables][$strSrcName]['class'] = 'none';

            $arrCompareList[$strType][$keySrcTables][$strDesName]['name'] = '-';
            $arrCompareList[$strType][$keySrcTables]['diff']              = $GLOBALS['TL_LANG']['MSC']['create'];

            unset($arrSourceTables[$keySrcTables]);
        }
        
        // Del Tables
        foreach ($arrMissingOnSource as $keyDesTables)
        {
            $strType = $arrDesTables[$keyDesTables]['type'];

            $arrCompareList[$strType][$keyDesTables][$strDesName]['name']    = $keyDesTables;
            $arrCompareList[$strType][$keyDesTables][$strSrcName]['tooltip'] = $this->getReadableSize($arrDesTables[$keyDesTables]['size'])
                    . ', '
                    . vsprintf(($arrDesTables[$keyDesTables]['count'] == 1) ? $GLOBALS['TL_LANG']['MSC']['entry'] : $GLOBALS['TL_LANG']['MSC']['entries'], array($arrDesTables[$keyDesTables]['count']));

            $arrCompareList[$strType][$keyDesTables][$strDesName]['class'] = 'none';

            $arrCompareList[$strType][$keyDesTables][$strSrcName]['name'] = '-';
            $arrCompareList[$strType][$keyDesTables]['diff']              = $GLOBALS['TL_LANG']['MSC']['delete'];
            $arrCompareList[$strType][$keyDesTables]['del']               = true;

            unset($arrDesTables[$keyDesTables]);
        }

        // Tables which exist on both systems
        foreach ($arrSourceTables as $keySrcTable => $valueSrcTable)
        {
            $strType = $valueSrcTable['type'];

            $arrCompareList[$strType][$keySrcTable][$strSrcName]['name']    = $keySrcTable;
            $arrCompareList[$strType][$keySrcTable][$strSrcName]['tooltip'] = $this->getReadableSize($valueSrcTable['size'])
                    . ', '
                    . vsprintf(($valueSrcTable['count'] == 1) ? $GLOBALS['TL_LANG']['MSC']['entry'] : $GLOBALS['TL_LANG']['MSC']['entries'], array($valueSrcTable['count']));

            $valueClientTable = $arrDesTables[$keySrcTable];

            $arrCompareList[$strType][$keySrcTable][$strDesName]['name']    = $keySrcTable;
            $arrCompareList[$strType][$keySrcTable][$strDesName]['tooltip'] = $this->getReadableSize($valueClientTable['size'])
                    . ', '
                    . vsprintf(($valueClientTable['count'] == 1) ? $GLOBALS['TL_LANG']['MSC']['entry'] : $GLOBALS['TL_LANG']['MSC']['entries'], array($valueClientTable['count']));

            // Get some diff information
            $arrNewId     = $this->getDiffId($valueClientTable, $valueSrcTable);
            $arrDeletedId = $this->getDiffId($valueSrcTable, $valueClientTable);
            $intDiffId    = count($arrNewId) + count($arrDeletedId);
            $intDiff      = $this->getDiff($valueSrcTable, $valueClientTable);

            // Add 'entry' or 'entries' to diff
            $arrCompareList[$strType][$keySrcTable]['diffCount']     = $intDiff;
            $arrCompareList[$strType][$keySrcTable]['diffCountId']   = $intDiffId;
            $arrCompareList[$strType][$keySrcTable]['diff']          = vsprintf(($intDiff == 1) ? $GLOBALS['TL_LANG']['MSC']['entry'] : $GLOBALS['TL_LANG']['MSC']['entries'], array($intDiff));
            $arrCompareList[$strType][$keySrcTable]['diffNewId']     = $arrNewId;
            $arrCompareList[$strType][$keySrcTable]['diffDeletedId'] = $arrDeletedId;

            // Check timestamps
            if (array_key_exists($keySrcTable, $arrSourceTS['current']) && array_key_exists($keySrcTable, $arrSourceTS['lastSync']))
            {
                if ($arrSourceTS['current'][$keySrcTable] == $arrSourceTS['lastSync'][$keySrcTable])
                {
                    $arrCompareList[$strType][$keySrcTable][$strSrcName]['class'] = 'unchanged';
                }
                else
                {
                    $arrCompareList[$strType][$keySrcTable][$strSrcName]['class'] = 'changed';
                }
            }
            else
            {
                $arrCompareList[$strType][$keySrcTable][$strSrcName]['class'] = 'no-sync';
            }

            if (array_key_exists($keySrcTable, $arrDesTS['current']) && array_key_exists($keySrcTable, $arrDesTS['lastSync']))
            {
                if ($arrDesTS['current'][$keySrcTable] == $arrDesTS['lastSync'][$keySrcTable])
                {
                    $arrCompareList[$strType][$keySrcTable][$strDesName]['class'] = 'unchanged';
                }
                else
                {
                    $arrCompareList[$strType][$keySrcTable][$strDesName]['class'] = 'changed';
                }
            }
            else
            {
                $arrCompareList[$strType][$keySrcTable][$strDesName]['class'] = 'no-sync';
            }

            // Check CSS
            if ($arrCompareList[$strType][$keySrcTable][$strSrcName]['class'] == 'changed' && $arrCompareList[$strType][$keySrcTable]['client']['class'] == 'changed')
            {
                $arrCompareList[$strType][$keySrcTable][$strSrcName]['class'] = 'changed-both';
                $arrCompareList[$strType][$keySrcTable][$strDesName]['class'] = 'changed-both';
            }

            // Check if we have some changes
            if ($arrCompareList[$strType][$keySrcTable][$strSrcName]['class'] == 'unchanged'
                    && $arrCompareList[$strType][$keySrcTable][$strDesName]['class'] == 'unchanged'
                    && $arrCompareList[$strType][$keySrcTable]['diffCount'] == 0
            )
            {
                unset($arrCompareList[$strType][$keySrcTable]);
                continue;
            }
        }

        return $arrCompareList;
    }

    /**
     * Get the calculated difference between the two given arrays
     *
     * @param array $arrSrcTables
     *
     * @param array $arrDesTables
     *
     * @return int
     */
    public function getDiff($arrSrcTables, $arrDesTables)
    {
        return abs(intval($arrSrcTables['count']) - intval($arrDesTables['count']));        
    }

    /**
     * Check the id list from both sides and check with arrays are missing.
     *
     * @param array $arrSrcTables
     *
     * @param array $arrDesTables
     *
     * @return array
     */
    public function getDiffId($arrSrcTables, $arrDesTables)
    {
        $arrSrcId = array();
        $arrDesId = array();

        // Rebuild the id list.
        foreach($arrSrcTables['ids'] as $arrIdRange)
        {
            if($arrIdRange['start'] == $arrIdRange['end'])
            {
                $arrSrcId[] = intval($arrIdRange['start']);
            }
            else
            {
                for($i = $arrIdRange['start'] ; $i < ($arrIdRange['end'] + 1) ; $i++)
                {
                    $arrSrcId[] = intval($i);
                }
            }
        }

        // Rebuild the id list.
        foreach($arrDesTables['ids'] as $arrIdRange)
        {
            if($arrIdRange['start'] == $arrIdRange['end'])
            {
                $arrDesId[] = intval($arrIdRange['start']);
            }
            else
            {
                for($i = $arrIdRange['start'] ; $i < ($arrIdRange['end'] + 1) ; $i++)
                {
                    $arrDesId[] = intval($i);
                }
            }
        }

        // Make a diff from both id arrays.
        return array_diff($arrDesId, $arrSrcId);
    }

    /**
     * Return all timestamps from client and server from current and last sync
     * 
     * @return array 
     */
    public function getAllTimeStamps($arrTimestampServer, $arrTimestampClient, $intClientID)
    {
        $arrLocationLastTableTimstamp = array('server' => array(), 'client' => array());

        foreach ($arrLocationLastTableTimstamp AS $location => $v)
        {
            $mixLastTableTimestamp = $this->Database
                    ->prepare("SELECT " . $location . "_timestamp FROM tl_synccto_clients WHERE id=?")
                    ->limit(1)
                    ->execute($intClientID)
                    ->fetchAllAssoc();

            if (strlen($mixLastTableTimestamp[0][$location . "_timestamp"]) != 0)
            {
                $arrLocationLastTableTimstamp[$location] = unserialize($mixLastTableTimestamp[0][$location . "_timestamp"]);
            }
            else
            {
                $arrLocationLastTableTimstamp[$location] = array();
            }
        }

        // Return the arrays
        return array(
            'server' => array(
                'current'  => $arrTimestampServer,
                'lastSync' => $arrLocationLastTableTimstamp['server']
            ),
            'client'   => array(
                'current'  => $arrTimestampClient,
                'lastSync' => $arrLocationLastTableTimstamp['client']
            )
        );
    }

    /* -------------------------------------------------------------------------
     * Helper Functions for building tables and inserts.
     */

    /**
     * Build a array with the structur of the database
     * 
     * @return array 
     */
    public function getTableStructure($strTableName)
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

            // Remove elements from the list, we did not want.
            foreach (array_diff(array_keys($field), $this->arrAllowedFieldKeys) as $strKeyForUnset)
            {
                unset($field[$strKeyForUnset]);
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