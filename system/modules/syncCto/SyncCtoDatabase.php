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
    public function runDump($arrTables, $booTempFolder)
    {
        if (is_array($arrTables) && is_array($this->arrBackupTables))
        {
            $this->arrBackupTables = array_unique(array_merge($this->arrBackupTables, $arrTables));
        }

        if (!is_array($this->arrBackupTables) || $this->arrBackupTables == null || count($this->arrBackupTables) == 0)
        {
            throw new Exception("No tables found for backup.");
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

        $objZipWrite = new ZipWriter($strPath . $strFilename);  

        $arrTables = $this->getTableStructure();        
        $arrData = $this->getTableData();               

        $objZipWrite->addString($this->buildFileSQLTables($arrTables) . $this->buildFileSQLInsert($arrData), $this->strFilenameSQL);
        $objZipWrite->addString(serialize($arrData), $this->strFilenameInsert);       
        $objZipWrite->addString(serialize($arrTables), $this->strFilenameTable);
        
        $objZipWrite->close();

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
        $objZipRead = new ZipReader($strRestoreFile);

        if (!$objZipRead->getFile($this->strFilenameInsert))
        {
            throw new Exception("Could not load SQL file inserts. Maybe damaged?");
        }

        $arrInsert = deserialize($objZipRead->unzip());

        if (!$objZipRead->getFile($this->strFilenameTable))
        {
            throw new Exception("Could not load SQL file table. Maybe damaged?");
        }

        $arrRestoreTables = deserialize($objZipRead->unzip());

        if (!is_array($arrInsert) || !is_array($arrRestoreTables))
        {
            throw new Exception("Could not load SQL files. Maybe damaged?");
        }

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

        try
        {
            // Create temp tables
            foreach ($arrRestoreTables as $key => $value)
            {
                $this->Database->prepare("DROP TABLE IF EXISTS " . "synccto_temp_" . $key)->executeUncached();
                $this->Database->prepare($this->buildSQLTable($value, "synccto_temp_" . $key))->executeUncached();
            }

            // Inserts
            foreach ($arrInsert as $table)
            {
                if (!empty($table['values']))
                {
                    foreach ($table['values'] as $value)
                    {
                        $strSQL = $this->buildSQLInsert("synccto_temp_" . $table['name'], $table['keys'], $value, true);
                        $this->Database->prepare($strSQL)->executeUncached();
                    }
                }
            }

            // Rename temp tables
            foreach ($arrRestoreTables as $key => $value)
            {
                $this->Database->prepare("DROP TABLE IF EXISTS " . $key)->executeUncached();
                $this->Database->prepare("RENAME TABLE " . "synccto_temp_" . $key . " TO " . $key)->executeUncached();
            }
        }
        catch (Exception $exc)
        {
            foreach ($arrRestoreTables as $key => $value)
            {
                $this->Database->prepare("DROP TABLE IF EXISTS " . "synccto_temp_" . $key)->executeUncached();
            }

            throw $exc;
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
    private function getTableStructure()
    {
        $tables = $this->Database->listTables();

        // Check if a table is selected
        if (!count($tables))
        {
            throw new Exception($GLOBALS['TL_LANG']['ERR']['missing_tables_selection']);
        }

        $return = array();
        
        foreach ($tables as $table)
        {
            // Check if table is in blacklist
            if (!in_array($table, $this->arrBackupTables))
            {
                continue;
            }

            // Get list of fields
            $fields = $this->Database->listFields($table);

            // Get list of indicies
            $arrIndexes = $this->Database->prepare("SHOW INDEX FROM `$table`")->executeUncached()->fetchAllAssoc();

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
                                $return[$table]['TABLE_CREATE_DEFINITIONS'][$field["name"]] = 'PRIMARY KEY  (`' . $field["name"] . '`)';
                                break;

                            case 'UNIQUE':
                                $return[$table]['TABLE_CREATE_DEFINITIONS'][$field["name"]] = 'UNIQUE KEY `' . $field["name"] . '` (`' . $field["name"] . '`)';
                                break;

                            case 'FULLTEXT':
                                $return[$table]['TABLE_CREATE_DEFINITIONS'][$field["name"]] = 'FULLTEXT KEY `' . $field["name"] . '` (`' . $field["name"] . '`)';
                                break;

                            default:
                                if ((strpos(' ' . $field['type'], 'text') || strpos(' ' . $field['type'], 'char')) && ($field['null'] == 'NULL'))
                                {
                                    $return[$table]['TABLE_CREATE_DEFINITIONS'][$field["name"]] = 'FULLTEXT KEY `' . $field["name"] . '` (`' . $field["name"] . '`)';
                                }
                                else
                                {
                                    $return[$table]['TABLE_CREATE_DEFINITIONS'][$field["name"]] = 'KEY `' . $field["name"] . '` (`' . $field["name"] . '`)';
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
                            $return[$table]['TABLE_CREATE_DEFINITIONS'][$field["name"]] = "PRIMARY KEY (`" . implode("`,`", $field["index_fields"]) . "`)";
                        }
                        else if ($field["index"] == "UNIQUE")
                        {
                            foreach ($field["index_fields"] as $keyField => $valueField)
                            {
                                foreach ($arrIndexes as $keyIndexe => $valueIndexe)
                                {
                                    if ($valueIndexe["Column_name"] == $valueField)
                                    {
                                        $return[$table]['TABLE_CREATE_DEFINITIONS'][$field["name"]] = "UNIQUE KEY (`" . implode("`,`", $field["index_fields"]) . "`)";
                                        break;
                                    }
                                }
                            }
                        }
                        else if ($field["index"] == "KEY")
                        {
                            $strTyp;
                            $arrReturn = array();

                            foreach ($field["index_fields"] as $keyField => $valueField)
                            {
                                foreach ($arrIndexes as $keyIndexe => $valueIndexe)
                                {
                                    if ($valueIndexe["Column_name"] == $valueField)
                                    {
                                        $return[$table]['TABLE_CREATE_DEFINITIONS'][$field["name"]] = "KEY $valueField (`$valueField`)";
                                        break;
                                    }
                                }
                            }

                            switch ($strTyp)
                            {
                                case "FULLTEXT":
                                    $return[$table]['TABLE_CREATE_DEFINITIONS'][$field["name"]] = "FULLTEXT KEY `" . $field['name'] . "` (" . implode(",", $arrReturn) . ")";
                                    break;

                                default:
                                    $return[$table]['TABLE_CREATE_DEFINITIONS'][$field["name"]] = "KEY `" . $field['name'] . "` (" . implode(",", $arrReturn) . ")";
                                    break;
                            }
                        }

                        continue;
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

                $return[$table]['TABLE_FIELDS'][$name] = trim(implode(' ', $field));
            }
        }
        
        // Table status
        $objStatus = $this->Database->prepare("SHOW TABLE STATUS")->executeUncached();
        while ($row = $objStatus->fetchAssoc())
        {
            if (!in_array($row['Name'], $this->arrBackupTables))
                continue;

            $return[$row['Name']]['TABLE_OPTIONS'] = " ENGINE=" . $row['Engine'] . " DEFAULT CHARSET=" . substr($row['Collation'], 0, strpos($row['Collation'], "_")) . "";
            if ($row['Auto_increment'] != "")
                $return[$row['Name']]['TABLE_OPTIONS'] .= " AUTO_INCREMENT=" . $row['Auto_increment'] . " ";
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

        $arrValues = array();
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

    /**
     * Build a whole insert sql file
     * 
     * @param array $arrData Array with datas
     * @return stirng 
     */
    private function buildFileSQLInsert($arrData)
    {
        if (count($arrData) == 0)
        {
            $string .= "-- No tables found in database.\r\n";
        }
        else
        {
            foreach ($arrData as $table)
            {
                if (!empty($table['values']))
                {
                    $string .= "-- \r\n";
                    $string .= "-- Dumping data for table " . $table['name'] . "\r\n";
                    $string .= "-- \r\n";
                    $string .= "\r\n";

                    foreach ($table['values'] as $value)
                    {
                        $string .= $this->buildSQLInsert($table['name'], $table['keys'], $value, false);
                        $string .= ";\r\n";
                    }

                    $string .= "\r\n";
                    $string .= "-- --------------------------------------------------------\r\n";
                    $string .= "\r\n";
                }
            }
        }

        return $string . "\r\n";
    }

}

?>