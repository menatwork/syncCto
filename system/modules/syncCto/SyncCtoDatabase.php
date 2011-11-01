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
    protected $strSuffixZipName = "DB-Backup.zip";
    protected $strFilenameTable = "DB-Backup_tbl.txt";
    protected $strFilenameInsert = "DB-Backup_ins.txt";
    protected $strFilenameSQL = "DB-Backup.sql";
    protected $strTimestampFormat = "Ymd_H-i-s";
    //- Objects ------------------
    protected $objSyncCtoHelper;

    /**
     * -= Config =-
     * List of default ignore values
     * @var array 
     */
    protected $arrDefaultValueFunctionIgnore = array(
        "NOW",
        "CURRENT_TIMESTAMP",
    );

    /**
     * -= Config =-
     * List of default ignore values
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
     * Load language
     */
    protected function __construct()
    {
        parent::__construct();

        $this->arrBackupTables = array();
        $this->objSyncCtoHelper = SyncCtoHelper::getInstance();
    }

    /**
     * Get instance of SyncCtoDatabase
     * @return SyncCtoDatabase 
     */
    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new SyncCtoDatabase();

        return self::$instance;
    }

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
            $strPath = $GLOBALS['SYC_PATH']['tmp'];
        else
            $strPath = $GLOBALS['SYC_PATH']['db'];

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
     * Restore database-bakup from zip
     * 
     * @param string $strRestoreFile Path to file like system/backup/backup.zip
     * @param bool $booTruncate 
     * @return type 
     */
    public function runRestore($strRestoreFile)
    {
        $objZipRead = new ZipReader($this->objSyncCtoHelper->buildPathWoTL($strRestoreFile));

        if (!$objZipRead->getFile($this->strFilenameInsert))
            throw new Exception("Could not load Insert SQL File. Maybe damaged?");

        $arrInsert = deserialize($objZipRead->unzip());

        if (!$objZipRead->getFile($this->strFilenameTable))
            throw new Exception("Could not load Table SQL File. Maybe damaged?");

        $arrRestoreTables = deserialize($objZipRead->unzip());

        if (!is_array($arrInsert) || !is_array($arrRestoreTables))
            throw new Exception("Could not load SQL Files. Maybe damaged?");

        try
        {
            // Create temp tables
            foreach ($arrRestoreTables as $key => $value)
            {
                $this->Database->prepare("DROP TABLE IF EXISTS " . "synccto_temp_" . $key)->execute();
                $this->Database->prepare($this->buildSQLTable($value, "synccto_temp_" . $key))->execute();
            }

            // Inserts
            foreach ($arrInsert as $table)
            {
                if (!empty($table['values']))
                {
                    foreach ($table['values'] as $value)
                    {
                        $strSQL = $this->buildSQLInsert("synccto_temp_" . $table['name'], $table['keys'], $value, true);
                        $this->Database->prepare($strSQL)->execute();
                    }
                }
            }

            // Rename temp tables
            foreach ($arrRestoreTables as $key => $value)
            {
                $this->Database->prepare("DROP TABLE IF EXISTS " . $key)->execute();
                $this->Database->prepare("RENAME TABLE " . "synccto_temp_" . $key . " TO " . $key)->execute();
            }
        }
        catch (Exception $exc)
        {
            foreach ($arrRestoreTables as $key => $value)
                $this->Database->prepare("DROP TABLE IF EXISTS " . "synccto_temp_" . $key)->execute();

            throw $exc;
        }

        return;
    }

    /* -------------------------------------------------------------------------
     * Helper Functions for building tables and inserts.
     */

    /**
     * Build a array with the structer of the database
     * 
     * @return array 
     */
    private function getTableStructure()
    {
        $tables = $this->Database->listTables();

        // Check if a table is selected
        if (!count($tables))
            throw new Exception($GLOBALS['TL_LANG']['syncCto']['zero_tables']);

        $return = array();

        foreach ($tables as $table)
        {
            // Check if table is in blacklist
            if (!in_array($table, $this->arrBackupTables))
                continue;

            // Liste der Felder lesen
            $fields = $this->Database->listFields($table);

            // Indicies
            $arrIndexes = $this->Database->prepare("SHOW INDEX FROM `$table`")->execute()->fetchAllAssoc();

            foreach ($fields as $field)
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
                                    $strTyp = $valueIndexe["Index_type"];
                                    $arrReturn[] = "`$valueField` (" . $valueIndexe["Sub_part"] . ")";

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
                                    $strTyp = $valueIndexe["Index_type"];
                                    $arrReturn[] = "`$valueField`";

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
                else
                {
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
        }

        // Table status
        $objStatus = $this->Database->prepare("SHOW TABLE STATUS")->execute();
        while ($zeile = $objStatus->fetchAssoc())
        {

            if (!in_array($zeile['Name'], $this->arrBackupTables))
                continue;

            $return[$zeile['Name']]['TABLE_OPTIONS'] = " ENGINE=" . $zeile['Engine'] . " DEFAULT CHARSET=" . substr($zeile['Collation'], 0, strpos($zeile['Collation'], "_")) . "";
            if ($zeile['Auto_increment'] != "")
                $return[$zeile['Name']]['TABLE_OPTIONS'] .= " AUTO_INCREMENT=" . $zeile['Auto_increment'] . " ";
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
            throw new Exception($GLOBALS['TL_LANG']['syncCto']['zero_tables']);

        $arrReturn = array();

        foreach ($tables as $table)
        {
            if (!in_array($table, $this->arrBackupTables))
                continue;

            $objData = $this->Database->prepare("SELECT * FROM $table")->execute();
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
                    else
                    {
                        $arrReturn[$table]['values'][$ii][$fields[$i]['name']] = "''";
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
     * Build a sql statemant for "INSERT IGNORE INTO"
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
        for ($i = 0; $i < count($arrData); $i++)
        {
            if ($booPrepare)
            {
                $strBody .= $arrData[$arrKeys[$i]];
            }
            else
            {
                $strBody .= $arrData[$arrKeys[$i]];
            }

            if ($i < count($arrData) - 1)
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
        $heute = date("Y-m-d");
        $uhrzeit = date("H:i:s");

        $string .= "-- syncCto SQL Dump\r\n";
        $string .= "-- Version " . SyncCtoGetVersion . "\r\n";
        $string .= "-- http://men-at-work.de\r\n";
        $string .= "-- \r\n";
        $string .= "-- Time stamp       : $heute at $uhrzeit\r\n";
        $string .= "\r\n";
        $string .= "SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";\r\n";
        $string .= "\r\n";
        $string .= "-- --------------------------------------------------------\r\n";

        if (count($arrTables) == 0)
            $string .= "-- No tables found in database.";
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
                    $string .= "  " . implode(",\n  ", $arrTables[$table]['TABLE_CREATE_DEFINITIONS']) . "\n";

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
            $string .= "-- No tables found in database.\r\n";
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