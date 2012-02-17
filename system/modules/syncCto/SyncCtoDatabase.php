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
 * @copyright  MEN AT WORK 2011
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

    //- Singelten pattern --------
    protected static $instance = null;
    //- Vars ---------------------
    protected $arrBackupTables;
    protected $arrHiddenTables;
    protected $strSuffixZipName = "DB-Backup.zip";
    protected $strFilenameTable = "DB-Backup_tbl.txt";
    protected $strFilenameInsert = "DB-Backup_ins.txt";
    protected $strFilenameSQL = "DB-Backup.sql";
    protected $strTimestampFormat;
    //- Objects ------------------
    protected $objSyncCtoHelper;

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

    /* -------------------------------------------------------------------------
     * Core
     */

    /**
     * Constructor
     */
    protected function __construct()
    {
        parent::__construct();

        $this->arrBackupTables = array();
        $this->arrHiddenTables = deserialize($GLOBALS['SYC_CONFIG']['table_hidden']);
        if (!is_array($this->arrHiddenTables))
        {
            $this->arrHiddenTables = array();
        }

        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();

        $this->strTimestampFormat = standardize($GLOBALS['TL_CONFIG']['datimFormat']);
    }

    /**
     * Get instance of SyncCtoDatabase
     * 
     * @return SyncCtoDatabase 
     */
    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new SyncCtoDatabase();

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
                    throw new Exception("Value must be type of array.");

                $this->arrBackupTables = $value;
                break;

            case "filenameInsert":
                $this->strFilenameInsert = $value;
                break;

            case "filenameTable":
                $this->strFilenameTable = $value;
                break;

            case "filenameSQL":
                $this->strFilenameSQL = $value;
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

            case "filenameInsert":
                return $this->strFilenameInsert;

            case "filenameTable":
                return $this->strFilenameTable;

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
     * Function for creating a sql dump file.
     * 
     * @param array $arrTables Table list for backup
     * @param string $strZip Name of zip file
     * @param bool $booTempFolder Schould the tmp folde used instead of backupfolder
     * @return null 
     */
    public function runDump($arrTables, $booTempFolder, $booOnlyMachine = false)
    {         
        var_dump('memory_usage_peak', $this->getReadableSize(memory_get_peak_usage(true)));
        
        $time_start = microtime(true);

        // Check if there are any tables selected for an export
        if (is_array($arrTables) && is_array($this->arrBackupTables))
        {
            $this->arrBackupTables = array_unique(array_merge($this->arrBackupTables, $arrTables));
        }

        if (!is_array($this->arrBackupTables) || $this->arrBackupTables == null || count($this->arrBackupTables) == 0)
        {
            throw new Exception("No tables found for backup.");
        }

        // Get a list of all Tables
        $arrTables = $this->Database->listTables();

        // Write some tempfiles
        $strRandomToken = md5(time() . " | " . rand(0, 65535));
        
        // ---------------------------------------------------------------------
        
        // Set memory limits in bytes
        $strMemoryLimit = ini_get('memory_limit');
        
        if(stripos($strMemoryLimit, 'M'))
        {
            $memoryLimit = (int) (((str_replace('M', '', $strMemoryLimit) * 1024) * 1024) / 3);
        }
        elseif(stripos($strMemoryLimit, 'G'))
        {
            $memoryLimit = (int) ((((str_replace('G', '', $strMemoryLimit) * 1024) * 1024) * 1024) / 3);
        }
        else
        {
            // Set to 128MB
            $memoryLimit = 131072;
        }
        unset($strMemoryLimit);
        
        $currentMemory = (memory_get_usage());
        if ($currentMemory > $memoryLimit)
        {
            var_dump("Line 250: " . $this ->getReadableSize($currentMemory));
        }
        
        /**
         * Create Files 
         */
        // Create XML File
        $objFile = new File("system/tmp/TempXMLDump.xml");
        $objFile->write("");
        $objFile->close();

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
        $objXml->writeElement('create_unix', time());
        $objXml->writeElement('create_date', date('Y-m-d', time()));
        $objXml->writeElement('create_time', date('H:i', time()));
        $objXml->endElement(); // End metatags

        $objFile->append($objXml->flush(true));
        $objFile->close();

        // ---------------------------------------------------------------------        
        // Create SQL File

        $objFileSQL = new File("system/tmp/TempSQLDump.sql");
        $objFileSQL->write("");
        $objFileSQL->close();

        // Write header        
        $objFileSQL->append("-- syncCto SQL Dump\r\n" .
                "-- Version " . $GLOBALS['SYC_VERSION'] . "\r\n" .
                "-- http://men-at-work.de\r\n" .
                "-- \r\n" .
                "-- Time stamp       : " . date('Y-m-d', time()) . " at " . date('H:i', time()) . "\r\n" .
                "\r\n" .
                "SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";\r\n" .
                "\r\n" .
                "\r\n" .
                "-- --------------------------------------------------------\r\n" .
                "\r\n", "");
        $objFileSQL->close();

        $currentMemory = (memory_get_usage());
        if ($currentMemory > $memoryLimit)
        {
            var_dump("Line 305: " . $this ->getReadableSize($currentMemory));
        }        
        
        /**
         * Write Table inforamtion and content to file 
         */
        // Run each table
        foreach ($arrTables as $key => $TableName)
        {
            // Get data
            $arrStructure = $this->getTableStructure($TableName);

            // Check if empty
            if (count($arrStructure) == 0)
            {
                continue;
            }

            // Write SQL
            $objFileSQL->append("-- \r");
            $objFileSQL->append("-- Dumping data for table $TableName");
            $objFileSQL->append("-- \r");
            $objFileSQL->append("\r");
            $objFileSQL->append($this->buildSQLTable($arrStructure, $TableName));

            $objXml->startElement('TABLE_STRUCTURE');
            $objXml->writeAttribute('name', $TableName);

            foreach ($arrStructure AS $key => $value)
            {
                if (is_array($value))
                {
                    $objXml->startElement($key);

                    foreach ($value As $k => $v)
                    {
                        $objXml->startElement('field');
                        $objXml->writeAttribute('name', $k);
                        $objXml->text($v);
                        $objXml->endElement(); // End field
                        
                        $currentMemory = (memory_get_usage());
                        if ($currentMemory > $memoryLimit)
                        {
                            var_dump("Line 349: " . $this ->getReadableSize($currentMemory));
                        }                        
                    }

                    $objXml->endElement(); // End undefined                    
                }
                else
                {
                    $objXml->writeElement($key, $value);
                }

                $currentMemory = (memory_get_usage());
                if ($currentMemory > $memoryLimit)
                {
                    $objFile->append($objXml->flush(true));
                    $objFile->close();
                }
            }

            $objXml->endElement(); // End TABLE_STRUCTURE 
        }

        $strSQL = "";
        
        // Run each table
        foreach ($arrTables as $key => $TableName)
        {                        
            $time_table_start = microtime(true);
            
            // Get data from table
            if (!in_array($TableName, $this->arrBackupTables) || in_array($TableName, $this->arrHiddenTables))
            {
                $objFileSQL->append("-- --------------------------------------------------------\r");
                $objFileSQL->append("\r");
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

            unset($fields);
            
            $objXml->startElement('TABLE_VALUES');
            $objXml->writeAttribute('name', $TableName);

            $objCount = $this->Database->prepare("SELECT Count(*) as count FROM $TableName")->executeUncached();           
            $intElementsPerRequest = 500;
           
            for ($i = 0; TRUE; $i++)
            {
                
                if (($i * $intElementsPerRequest) > $objCount->count)
                {
                    break;
                }

                $objData = $this->Database->prepare("SELECT * FROM $TableName")->limit($intElementsPerRequest, ($i * $intElementsPerRequest))->executeUncached();
                
                while ($row = $objData->fetchAssoc())
                {
                    $objXml->startElement('ROW');

                    $arrTableData = array("table" => $TableName, "values" => array());
                    
                    foreach ($row as $field_key => $field_data)
                    {                        
                        if (!isset($field_data))
                        {
                            $arrTableData['values'][$field_key] = "NULL";

                            $objXml->startElement('field');
                            $objXml->writeAttribute('type', $arrFieldMeta[$field_key]['type']);
                            $objXml->writeAttribute('name', $field_key);
                            $objXml->writeCData('NULL');
                            $objXml->endElement(); // End field
                        }
                        else if ($field_data != "")
                        {
                            switch (strtolower($arrFieldMeta[$field_key]['type']))
                            {
                                case 'blob':
                                case 'tinyblob':
                                case 'mediumblob':
                                case 'longblob':
                                    $arrTableData['values'][$field_key] = "0x" . bin2hex($field_data);

                                    $objXml->startElement('field');
                                    $objXml->writeAttribute('name', $field_key);
                                    $objXml->writeAttribute('type', $arrFieldMeta[$field_key]['type']);
                                    $objXml->writeCData("0x" . bin2hex($field_data));
                                    $objXml->endElement(); // End field                                    
                                    break;
                                default:
                                    $arrTableData['values'][$field_key] = $field_data;

                                    $objXml->startElement('field');
                                    $objXml->writeAttribute('name', $field_key);
                                    $objXml->writeAttribute('type', $arrFieldMeta[$field_key]['type']);
                                    $objXml->writeCData($field_data);
                                    $objXml->endElement(); // End field                                      
                                    break;
                            }
                        }
                        else
                        {
                            $arrTableData['values'][$field_key] = "NULL";

                            $objXml->startElement('field');
                            $objXml->writeAttribute('type', $arrFieldMeta[$field_key]['type']);
                            $objXml->writeAttribute('name', $field_key);
                            $objXml->writeCData('NULL');
                            $objXml->endElement(); // End field
                        }
                        
                        $currentMemory = (memory_get_usage());
                        if ($currentMemory > $memoryLimit)
                        {
                            var_dump("for: $i , table: $TableName");
                            var_dump($this ->getReadableSize($memoryLimit));
                            var_dump($this ->getReadableSize($currentMemory));

                            $objFile->append($objXml->flush(true));
                            $objFile->close();
                        }                        
                    }

                    $strSQL .= $this->buildSQLInsert($arrTableData["table"], array_keys($arrTableData["values"]), $arrTableData["values"]) . "\n";
                    
                    $currentMemory = (memory_get_usage());
                    if ($currentMemory > $memoryLimit)
                    {
                        var_dump("for: $i , table: $TableName");
                        var_dump($this ->getReadableSize($memoryLimit));
                        var_dump($this ->getReadableSize($currentMemory));
                        
                        $objFileSQL->append(substr($strSQL, 0, -1));
                        $objFileSQL->close();                        
                        $strSQL = "";

                        $objFile->append($objXml->flush(true));
                        $objFile->close();
                    }
                    $objXml->endElement(); // End ROW
                }

                $currentMemory = (memory_get_usage());
                if ($currentMemory > $memoryLimit)
                {
                    var_dump("for: $i , table: $TableName");
                    var_dump($this ->getReadableSize($memoryLimit));
                    var_dump($this ->getReadableSize($currentMemory));                    
                    
                    if(strlen($strSQL) > 0)
                    {
                        $objFileSQL->append(substr($strSQL, 0, -1));
                        $objFileSQL->close();                        
                        $strSQL = "";
                    }

                    $objFile->append($objXml->flush(true));
                    $objFile->close();
                }
            }

            $objXml->endElement(); // End TABLE_VALUES
            
            $time_table_end = microtime(true);
            $time = $time_table_end - $time_table_start;
            var_dump('-----------------------------------------------------------');
            var_dump("for: $i , table: $TableName, time: $time seconds");

            $currentMemory = (memory_get_usage());
            if ($currentMemory > $memoryLimit)
            {
                var_dump("for: $i , table: $TableName");
                var_dump($this ->getReadableSize($memoryLimit));
                var_dump($this ->getReadableSize($currentMemory));                
                
                if(strlen($strSQL) > 0)
                {
                    $objFileSQL->append(substr($strSQL, 0, -1));
                    $objFileSQL->close();                        
                    $strSQL = "";
                }                
                
                $objFile->append($objXml->flush(true));
                $objFile->close();
            }
        }

        // Add analysis values
        $objXml->startElement('analysis');

        $time_end = microtime(true);
        $time = $time_end - $time_start;
        var_dump('-----------------------------------------------------------');
        var_dump('memory_limit', $this->getReadableSize($memoryLimit));
        var_dump('memory_usage_peak', $this->getReadableSize(memory_get_peak_usage(true)));
        var_dump("Create SQL and XML in $time seconds");


        $objXml->writeElement('memory_limit', $this->getReadableSize($memoryLimit));
        $objXml->writeElement('memory_usage_peak', $this->getReadableSize(memory_get_peak_usage(true)));

        $objXml->endElement(); // End analysis          

        $objXml->endElement(); // End dump
        
        if(strlen($strSQL) > 0)
        {
            $objFileSQL->append(substr($strSQL, 0, -1));
            $objFileSQL->close();
            unset($strSQL);
        }           
        
        $objFileSQL->append("\r");
        $objFileSQL->append("-- --------------------------------------------------------\r");
        $objFileSQL->append("\r");
        $objFileSQL->close();
        
        // Final flush to make sure we haven't missed anything
        $objFile->append($objXml->flush(true));
        $objFile->close();       
        exit();
        
        // ---------------------------------------------------------------------

        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        // ---------------------------------------------------------------------
        // Write header for sql file
        $today = date("Y-m-d");
        $time = date("H:i:s");

        // Write Header
        $string .= "-- syncCto SQL Dump\r\n";
        $string .= "-- Version " . $GLOBALS['SYC_VERSION'] . "\r\n";
        $string .= "-- http://men-at-work.de\r\n";
        $string .= "-- \r\n";
        $string .= "-- Time stamp       : $today at $time\r\n";
        $string .= "\r\n";
        $string .= "SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";\r\n";
        $string .= "\r\n";
        $string .= "\r\n";
        $string .= "-- --------------------------------------------------------\r\n";
        $string .= "\r\n";

        $objFileSQL->append($string, "");




        // Run each table
        foreach ($arrTables as $key => $TableName)
        {
            // Get data
            $arrStructure = $this->getTableStructure($TableName);

            // Check if empty
            if (count($arrStructure) == 0)
            {
                continue;
            }

            // Write serialize array in file
            $objFileStructure->append(serialize(array("name" => $TableName, "value" => $arrStructure)));

            // Write SQL 
            $objFileSQL->append("-- \r");
            $objFileSQL->append("-- Dumping data for table $TableName");
            $objFileSQL->append("-- \r");
            $objFileSQL->append("\r");
            $objFileSQL->append($this->buildSQLTable($arrStructure, $TableName));

            // Get data from table
            if (!in_array($TableName, $this->arrBackupTables) || in_array($TableName, $this->arrHiddenTables))
            {
                $objFileSQL->append("-- --------------------------------------------------------\r");
                $objFileSQL->append("\r");
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

            $objCount = $this->Database->prepare("SELECT Count(*) as count FROM $TableName")->executeUncached();
            $intElementsPerRequest = 500;

            for ($i = 0; $i < 2; $i++)
            {
                if (($i * $intElementsPerRequest) > $objCount->count)
                {
                    break;
                }

                $objData = $this->Database->prepare("SELECT * FROM $TableName")->limit($intElementsPerRequest, ($i * $intElementsPerRequest))->executeUncached();

                $strSQL = "";
                $strSer = "";

                while ($row = $objData->fetchAssoc())
                {

                    $arrTableData = array("table" => $TableName, "values" => array());

                    foreach ($row as $field_key => $field_data)
                    {
                        if (!isset($field_data))
                        {
                            $arrTableData['values'][$field_key] = "NULL";
                        }
                        else if ($field_data != "")
                        {
                            switch (strtolower($arrFieldMeta[$field_key]['type']))
                            {
                                case 'blob':
                                case 'tinyblob':
                                case 'mediumblob':
                                case 'longblob':
                                    $arrTableData['values'][$field_key] = "0x" . bin2hex($field_data);
                                    break;

                                case 'smallint':
                                case 'int':
                                    $arrTableData['values'][$field_key] = $field_data;
                                    break;

                                case 'text':
                                case 'mediumtext':
                                    if (strpos($field_data, "'") != false)
                                    {
                                        $arrTableData['values'][$field_key] = "0x" . bin2hex($field_data);
                                        break;
                                    }
                                default:
                                    $arrTableData['values'][$field_key] = " '" . str_replace(array("\\", "'", "\r", "\n"), array("\\\\", "\\'", "\\r", "\\n"), $field_data) . "'";
                                    break;
                            }
                        }
                        else
                        {
                            $arrTableData['values'][$field_key] = "''";
                        }
                    }

                    $strSQL .= $this->buildSQLInsert($arrTableData["table"], array_keys($arrTableData["values"]), $arrTableData["values"]) . "\n";
                    $strSer .= base64_encode(gzcompress(json_encode($arrTableData))) . "\n";

                    if (strlen($strSQL) > 100000)
                    {
                        $objFileSQL->append(substr($strSQL, 0, -1));
                        $strSQL = "";
                    }

                    if (strlen($strSer) > 100000)
                    {
                        $objFileData->append(substr($strSer, 0, -1));
                        $strSer = "";
                    }
                }

                if (strlen($strSQL) != 0)
                {
                    $objFileSQL->append(substr($strSQL, 0, -1));
                    $strSQL = "";
                }

                if (strlen($strSer) != 0)
                {
                    $objFileData->append(substr($strSer, 0, -1));
                    $strSer = "";
                }
            }

            $objFileSQL->append("\r");
            $objFileSQL->append("-- --------------------------------------------------------\r");
            $objFileSQL->append("\r");
        }

        $objFileSQL->close();
        $objFileStructure->close();
        $objFileData->close();

        $strFilename = date($this->strTimestampFormat) . "_" . $this->strSuffixZipName;

        if ($booTempFolder)
        {
            $strPath = $GLOBALS['SYC_PATH']['tmp'];
        }
        else
        {
            $strPath = $GLOBALS['SYC_PATH']['db'];
        }

        $objZipWrite = new ZipWriter($strPath . $strFilename);

        $objZipWrite->addFile("system/tmp/TempSQLDump.$strRandomToken", $this->strFilenameSQL);
        $objZipWrite->addFile("system/tmp/TempStructureDump.$strRandomToken", $this->strFilenameTable);
        $objZipWrite->addFile("system/tmp/TempDataDump.$strRandomToken", $this->strFilenameInsert);

        $objZipWrite->close();

        $objFiles = Files::getInstance();

        $objFiles->delete("system/tmp/TempSQLDump.$strRandomToken");
        $objFiles->delete("system/tmp/TempStructureDump.$strRandomToken");
        $objFiles->delete("system/tmp/TempDataDump.$strRandomToken");

        return $strFilename;
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

        $objZipRead = new ZipReader($strRestoreFile);

        // Get structure
        if (!$objZipRead->getFile($this->strFilenameTable))
        {
            throw new Exception("Could not load SQL file table. Maybe damaged?");
        }

        $mixTables = $objZipRead->unzip();
        $mixTables = trimsplit("\n", $mixTables);

        $arrRestoreTables = array();

        try
        {
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
        }
        catch (Exception $exc)
        {
            foreach ($arrRestoreTables as $key => $value)
            {
                $this->Database->query("DROP TABLE IF EXISTS " . "synccto_temp_" . $value);
            }

            throw $exc;
        }

        try
        {
            // Get insert
            if (!$objZipRead->getFile($this->strFilenameInsert))
            {
                throw new Exception("Could not load SQL file inserts. Maybe damaged?");
            }

            $strContent = $objZipRead->unzip();

            // Write temp File
            $objTempfile = tmpfile();
            fputs($objTempfile, $strContent, strlen($strContent));

            unset($strContent);

            // Set pointer on position zero
            rewind($objTempfile);

            $i = 0;
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

                try
                {
                    $strSQL = $this->buildSQLInsert("synccto_temp_" . $mixLine['table'], array_keys($mixLine['values']), $mixLine['values'], true);
                    $this->Database->query($strSQL);
                }
                catch (Exception $exc)
                {
                    foreach ($arrRestoreTables as $key => $value)
                    {
                        $this->Database->query("DROP TABLE IF EXISTS " . "synccto_temp_" . $value);
                    }

                    throw $exc;
                }
            }

            fclose($objTempfile);

            // Rename temp tables
            foreach ($arrRestoreTables as $key => $value)
            {
                $this->Database->query("DROP TABLE IF EXISTS " . $value);
                $this->Database->query("RENAME TABLE " . "synccto_temp_" . $value . " TO " . $value);
            }
        }
        catch (Exception $exc)
        {
            foreach ($arrRestoreTables as $key => $value)
            {
                $this->Database->query("DROP TABLE IF EXISTS " . "synccto_temp_" . $value);
            }

            throw $exc;
        }

        // Get a list of all Tables
        foreach ($this->Database->listTables() as $key => $value)
        {
            if (stripos($value, "synccto_temp_") !== FALSE)
            {
                $this->Database->query("DROP TABLE IF EXISTS $value");
            }
        }

        return;
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
        $tables = $this->Database->listTables();

        // Check if a table is selected
        if (!count($tables) || !in_array($strTableName, $tables))
        {
            throw new Exception($GLOBALS['TL_LANG']['ERR']['missing_tables_selection']);
        }

        $return = array();

        // Check if table is in blacklist
        if (!in_array($strTableName, $this->arrBackupTables))
        {
            return $return;
        }

        // Get list of fields
        $fields = $this->Database->listFields($strTableName);

        // Get list of indicies
        $arrIndexes = $this->Database->prepare("SHOW INDEX FROM `$strTableName`")->executeUncached()->fetchAllAssoc();

        // Bugfix: If we have Contao 2.9.x use a temp array for the TABLE_CREATE_DEFINITIONS
        if (version_compare(VERSION, '2.10', '<'))
        {
            $arrTempIndex = array();
        }

        foreach ($fields as $field)
        {
            if (version_compare(VERSION, '2.10', '<'))
            {
                // Indices
                if (strlen($field['index']) != 0)
                {
                    switch ($field['index'])
                    {
                        case 'PRIMARY':
                            $arrTempIndex["PRIMARY"]["body"] = 'PRIMARY KEY  (`%s`)';
                            $arrTempIndex["PRIMARY"]["fields"][] = $field["name"];
                            break;

                        case 'UNIQUE':
                            $arrTempIndex[$field["name"]]["body"] = 'UNIQUE KEY `%s` (`%s`)';
                            $arrTempIndex[$field["name"]]["fields"][] = $field["name"];
                            break;

                        case 'FULLTEXT':
                            $arrTempIndex[$field["name"]]["body"] = 'FULLTEXT KEY `%s` (`%s`)';
                            $arrTempIndex[$field["name"]]["fields"][] = $field["name"];
                            break;

                        default:
                            if ((strpos(' ' . $field['type'], 'text') || strpos(' ' . $field['type'], 'char')) && ($field['null'] == 'NULL'))
                            {
                                $arrTempIndex[$field["name"]]["body"] = 'FULLTEXT KEY `%s` (`%s`)';
                                $arrTempIndex[$field["name"]]["fields"][] = $field["name"];
                            }
                            else
                            {
                                $arrTempIndex[$field["name"]]["body"] = 'KEY `%s` (`%s`)';
                                $arrTempIndex[$field["name"]]["fields"][] = $field["name"];
                            }
                            break;
                    }
                }
            }
            else
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
                        foreach ($arrIndexes as $keyIndexes => $valueIndexes)
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
            }

            // Bugfix: if we have contao 2.9.x build the TABLE_CREATE_DEFINITIONS from temp array
            if (version_compare(VERSION, '2.10', '<'))
            {
                foreach ($arrTempIndex as $key => $value)
                {
                    if ($key == 'PRIMARY')
                    {
                        $return['TABLE_CREATE_DEFINITIONS'][$key] = vsprintf($value["body"], array(implode("`, `", $value["fields"])));
                    }
                    else
                    {
                        $return['TABLE_CREATE_DEFINITIONS'][$key] = vsprintf($value["body"], array($key, implode("`, `", $value["fields"])));
                    }
                }
            }

            unset($field['index']);

            $name = $field['name'];
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
     * Build a array with data from all tables
     * 
     * @return array 
     */
    private function getTableData()
    {
        $tables = $this->Database->listTables();

        if (!count($tables))
        {
            throw new Exception($GLOBALS['TL_LANG']['ERR']['missing_tables_selection']);
        }

        $arrReturn = array();

        foreach ($tables as $table)
        {
            if (!in_array($table, $this->arrBackupTables))
            {
                continue;
            }

            if (in_array($table, $this->arrHiddenTables))
            {
                continue;
            }

            $objData = $this->Database->prepare("SELECT * FROM $table")->executeUncached();
            $fields = $this->Database->listFields($table);

            foreach ($fields as $key => $value)
            {
                if ($value["type"] == "index")
                {
                    continue;
                }

                $arrReturn[$table]['keys'][] = $value['name'];
            }

            $ii = 0;

            while ($row = $objData->fetchRow())
            {
                $arrReturn[$table]['name'] = $table;

                $i = 0;

                foreach ($row as $field_data)
                {
                    if (!isset($field_data))
                    {
                        $arrReturn[$table]['values'][$ii][$fields[$i]['name']] = "NULL";
                    }
                    else if ($field_data != "")
                    {
                        switch (strtolower($fields[$i]['type']))
                        {
                            case 'blob':
                            case 'tinyblob':
                            case 'mediumblob':
                            case 'longblob':
                                $arrReturn[$table]['values'][$ii][$fields[$i]['name']] = "0x" . bin2hex($field_data);
                                break;

                            case 'smallint':
                            case 'int':
                                $arrReturn[$table]['values'][$ii][$fields[$i]['name']] = $field_data;
                                break;

                            case 'text':
                            case 'mediumtext':
                                if (strpos($field_data, "'") != false)
                                {
                                    $arrReturn[$table]['values'][$ii][$fields[$i]['name']] = "0x" . bin2hex($field_data);
                                    break;
                                }
                            default:
                                $arrReturn[$table]['values'][$ii][$fields[$i]['name']] = " '" . str_replace(array("\\", "'", "\r", "\n"), array("\\\\", "\\'", "\\r", "\\n"), $field_data) . "'";
                                break;
                        }
                    }

                    $i++;
                }

                $ii++;
            }
        }

        return $arrReturn;
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
        $string = "";

        $string .= "CREATE TABLE `" . $strName . "` (\n  " . implode(",\n  ", $arrTable['TABLE_FIELDS']) . (count($arrTable['TABLE_CREATE_DEFINITIONS']) ? ',' : '') . "\n";

        if (is_Array($arrTable['TABLE_CREATE_DEFINITIONS']))
            $string .= "  " . implode(",\n  ", $arrTable['TABLE_CREATE_DEFINITIONS']) . "\n";

        $string .= ")" . $arrTable['TABLE_OPTIONS'] . ";\r\n\r\n";

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

    /* -------------------------------------------------------------------------
     * Functions for creating SQL for backup
     */

    /**
     * Build a whole sql dump file
     * 
     * @param array $arrTables Array with Tables
     * @return string 
     */
    private function buildFileSQLTables($arrTables)
    {
        $today = date("Y-m-d");
        $time = date("H:i:s");

        $string .= "-- syncCto SQL Dump\r\n";
        $string .= "-- Version " . SyncCtoGetVersion . "\r\n";
        $string .= "-- http://men-at-work.de\r\n";
        $string .= "-- \r\n";
        $string .= "-- Time stamp       : $today at $time\r\n";
        $string .= "\r\n";
        $string .= "SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";\r\n";
        $string .= "\r\n";
        $string .= "-- --------------------------------------------------------\r\n";

        if (count($arrTables) == 0)
        {
            $string .= "-- No tables found in database.";
        }
        else
        {
            foreach (array_keys($arrTables) as $table)
            {
                $string .= "\r\n";
                $string .= "-- \r\n";
                $string .= "-- Table structure for table '$table'\r\n";
                $string .= "-- \r\n";
                $string .= "\r\n";
                $string .= "DROP TABLE IF EXISTS `" . $table . "`;\r\n";
                $string .= "CREATE TABLE `" . $table . "` (\n  " . implode(",\n  ", $arrTables[$table]['TABLE_FIELDS']) . (count($arrTables[$table]['TABLE_CREATE_DEFINITIONS']) ? ',' : '') . "\n";

                if (is_Array($arrTables[$table]['TABLE_CREATE_DEFINITIONS']))
                {
                    $string .= "  " . implode(",\n  ", $arrTables[$table]['TABLE_CREATE_DEFINITIONS']) . "\n";
                }

                $string .= ")" . $arrTables[$table]['TABLE_OPTIONS'] . ";\r\n\r\n";

                $string .= "-- --------------------------------------------------------\r\n";
            }
        }

        return $string;
    }

}

?>