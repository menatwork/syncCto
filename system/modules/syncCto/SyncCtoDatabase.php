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

    /**
     * Instance
     * @var SyncCtoDatabase 
     */
    protected static $instance = null;

    /**
     * Array of Backups
     * @var array
     */
    protected $arrBackupTables;

    /**
     * -= Config =-
     * List of default values for ignore
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
    public function __construct()
    {
        parent::__construct();
        $this->loadLanguageFile('SyncCtoController');
        
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

    /* -------------------------------------------------------------------------
     * Create functions
     */

    /**
     * Function for creating a empty zip folder.
     * 
     * @param bool $booTempFolder Should the zip file create in the tmp folder instead of db backup folder.
     * @return array("id"=>[int],"name"=>[string]) 
     */
    public function runCreateZip($booTempFolder = false)
    {
        $intTstamp = time();

        $objZip = new ZipArchive();

        if ($booTempFolder == TRUE)
            $strFilename = TL_ROOT . $GLOBALS['syncCto']['path']['tmp'] . $this->buildFilename($intTstamp);
        else
            $strFilename = TL_ROOT . $GLOBALS['syncCto']['path']['db'] . $this->buildFilename($intTstamp);

        if ($objZip->open($strFilename, ZIPARCHIVE::CREATE) !== TRUE)
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['syncCto']['cant_open'], array($strFilename)));

        $objZip->addFromString("readme.txt", vsprintf($GLOBALS['TL_LANG']['syncCto']['readme'], array(date($GLOBALS['SyncCto']['settings']['time_format'], $intTstamp))));

        $objZip->close();
        
        unset($objZip);

        if (!file_exists($strFilename))
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['syncCto']['file_not_exists'], array($strFilename)));

        return array("id" => $intTstamp, "name" => $this->buildFilename($intTstamp));
    }

    /**
     * Function for creating a sql dump file and save it in an exsisting zip file.
     * 
     * @param array $arrTables Table list for backup
     * @param string $strZip Name of zip file
     * @param bool $booTempFolder Schould the tmp folde used instead of backupfolder
     * @return null 
     */
    public function runDumpSQL($arrTables, $strZip, $booTempFolder = false)
    {
        $this->arrBackupTables = $arrTables;

        if ($booTempFolder == TRUE)
            $strFilename = TL_ROOT . $GLOBALS['syncCto']['path']['tmp'] . $strZip;
        else
            $strFilename = TL_ROOT . $GLOBALS['syncCto']['path']['db'] . $strZip;

        if (!file_exists($strFilename))
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['syncCto']['file_not_exists'], array($strFilename)));

        $objZip = new ZipArchive();

        if ($objZip->open($strFilename) !== TRUE)
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['syncCto']['cant_open'], array($strFilename)));

        $arrTables = $this->getTableStructure();
        $arrData = $this->getTableData();

        $objZip->addFromString(DB_SQL, $this->buildSQL($arrTables) . $this->buildInsert($arrData));

        $objZip->close();

        return;
    }

    /**
     * Function for creating a sql file for syncCto and save it in an exsisting zip file.
     * 
     * @param array $arrTables Table list for backup
     * @param string $strZip Name of zip file
     * @param bool $booTempFolder Schould the tmp folde used instead of backupfolder
     * @return null 
     */
    public function runDumpInsert($arrTables, $strZip, $booTempFolder = false)
    {
        @set_time_limit(60);

        $this->arrBackupTables = $arrTables;

        if ($booTempFolder == TRUE)
            $strFilename = TL_ROOT . "/" . $GLOBALS['syncCto']['path']['tmp'] . $strZip;
        else
            $strFilename = TL_ROOT . "/" . $GLOBALS['syncCto']['path']['db'] . $strZip;

        if (!file_exists($strFilename))
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['syncCto']['file_not_exists'], array($strFilename)));

        $objZip = new ZipArchive();

        if ($objZip->open($strFilename) !== TRUE)
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['syncCto']['cant_open'], array($strFilename)));

        $arrTables = $this->getTableStructure();
        $arrData = $this->getTableData();

        $objZip->addFromString(DB_SERIALIZED_TABLES, serialize($arrTables));
        $objZip->addFromString(DB_SERIALIZED_INSERTS, serialize($arrData));

        $objZip->close();

        return;
    }

    /**
     * Check if all files inside the zip are okay.
     *
     * @param string $strRestoreFile FilePath, start at TL_ROOT. Example tl_files/syncCto_backups/database/20110511_08-18-40_DB-Backup.zip
     * @param bool $booTlRoot Should we start at TL_ROOT or by SyncCto DB-Backup folder.
     * @param bool $booTempFolder Schould we start at temp folder of syncCto. The TL_ROOT, if activated, is first.
     * @return bool
     */
    public function runCheckZip($strRestoreFile, $booTlRoot = true, $booTempFolder = false)
    {
        @set_time_limit(20);

        if ($booTlRoot)
            $strFilename = TL_ROOT . "/" . $strRestoreFile;
        else if ($booTempFolder)
            $strFilename = TL_ROOT . $GLOBALS['syncCto']['path']['tmp'] . $strRestoreFile;
        else
            $strFilename = TL_ROOT . $GLOBALS['syncCto']['path']['db'] . $strRestoreFile;

        if (!file_exists($strFilename))
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['syncCto']['file_not_exists'], array($strFilename)));

        $objZip = zip_open($strFilename);

        $booTables = FALSE;
        $booInsert = FALSE;

        while (($objRead = zip_read($objZip)) !== false)
        {
            if (zip_entry_name($objRead) == DB_SERIALIZED_TABLES)
            {
                $intSize = zip_entry_filesize($objRead);
                $arrRestoreTables = deserialize(zip_entry_read($objRead, $intSize));
                if (is_array($arrRestoreTables))
                    $booTables = true;
                else
                    throw new Exception($GLOBALS['TL_LANG']['syncCto']['table_dmg']);
            }

            if (zip_entry_name($objRead) == DB_SERIALIZED_INSERTS)
            {
                $intSize = zip_entry_filesize($objRead);
                $arrInsert = deserialize(zip_entry_read($objRead, $intSize));
                if (is_array($arrInsert))
                    $booInsert = true;
                else
                    throw new Exception($GLOBALS['TL_LANG']['syncCto']['insert_dmg']);
            }
        }

        if (!$booTables)
            throw new Exception($GLOBALS['TL_LANG']['syncCto']['missing_table_file']);

        if (!$booInsert)
            throw new Exception($GLOBALS['TL_LANG']['syncCto']['missing_insert_file']);

        return true;
    }

    /**
     * Restore database-bakup from zip
     * 
     * @param string $strRestoreFile Path to file like system/backup/backup.zip
     * @param bool $booTruncate 
     * @return type 
     */
    public function runRestore($strRestoreFile, $booTruncate = false)
    {
        @set_time_limit(60);

        $arrRestoreTables = FALSE;
        $arrInsert = FALSE;

        $objZip = zip_open(TL_ROOT . "/" . $strRestoreFile);

        while (($objRead = zip_read($objZip)) !== false)
        {
            if (zip_entry_name($objRead) == DB_SERIALIZED_TABLES)
            {
                $intSize = zip_entry_filesize($objRead);
                $arrRestoreTables = deserialize(zip_entry_read($objRead, $intSize));
            }

            if (zip_entry_name($objRead) == DB_SERIALIZED_INSERTS)
            {
                $intSize = zip_entry_filesize($objRead);
                $arrInsert = deserialize(zip_entry_read($objRead, $intSize));
            }
        }

        try
        {
            /**
             * Create Table
             */
            if ($arrRestoreTables !== FALSE)
            {
                foreach ($arrRestoreTables as $key => $value)
                {
                    $this->Database->prepare("DROP TABLE IF EXISTS " . "synccto_temp_" . $key)->execute();
                    $this->Database->prepare($this->buildCreateTableSQL($value, "synccto_temp_" . $key))->execute();
                }
            }
            else
            {
                throw new Exception($GLOBALS['TL_LANG']['syncCto']['reading_table_file']);
            }

            /**
             * Create Insert
             */
            if ($arrInsert !== FALSE)
            {
                foreach ($arrInsert as $table)
                {
                    if (!empty($table['values']))
                    {
                        foreach ($table['values'] as $value)
                        {
                            $arrSQL = $this->buildInsertDataSQL("synccto_temp_" . $table['name'], $table['keys'], $value);

                            call_user_func_array(array($this->Database->prepare($arrSQL["body"]), "execute"), $arrSQL["value"]);
                            //$this->Database->prepare($arrSQL["body"])->execute($arrSQL["value"]);                            
                        }
                    }
                }
            }
            else
            {
                throw new Exception($GLOBALS['TL_LANG']['syncCto']['reading_insert_file']);
            }

            /**
             * Rename Temptables
             */
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
     * Build a file name. 
     * 
     * @param int $intTstamp
     * @return string 
     */
    private function buildFilename($intTstamp)
    {
        return vsprintf("%s_" . DB_ZIP, array(date($GLOBALS['syncCto']['settings']['time_format'], $intTstamp)));
    }

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


            foreach ($fields as $field)
            {
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

                // Indices
                if (strlen($field['index']))
                {
                    switch ($field['index'])
                    {
                        case 'PRIMARY': $return[$table]['TABLE_CREATE_DEFINITIONS'][$name] = 'PRIMARY KEY  (`' . $name . '`)';
                            break;

                        case 'UNIQUE': $return[$table]['TABLE_CREATE_DEFINITIONS'][$name] = 'UNIQUE KEY `' . $name . '` (`' . $name . '`)';
                            break;

                        case 'FULLTEXT': $return[$table]['TABLE_CREATE_DEFINITIONS'][$name] = 'FULLTEXT KEY `' . $name . '` (`' . $name . '`)';
                            break;

                        default: if ((strpos(' ' . $field['type'], 'text') || strpos(' ' . $field['type'], 'char')) && ($field['null'] == 'NULL')) // Fulltext-Search bei text-Fields
                                $return[$table]['TABLE_CREATE_DEFINITIONS'][$name] = 'FULLTEXT KEY `' . $name . '` (`' . $name . '`)';
                            else
                                $return[$table]['TABLE_CREATE_DEFINITIONS'][$name] = 'KEY `' . $name . '` (`' . $name . '`)';
                            break;
                    }
                    unset($field['index']);
                }
                $return[$table]['TABLE_FIELDS'][$name] = trim(implode(' ', $field));
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
                            default: $arrReturn[$table]['values'][$ii][$fields[$i]['name']] = " '" . str_replace(array("\\", "'", "\r", "\n"), array("\\\\", "\\'", "\\r", "\\n"), $field_data) . "'";
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
    private function buildCreateTableSQL($arrTable, $strName)
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
    private function buildInsertDataSQL($strTable, $arrKeys, $arrData)
    {
        $strBody = "INSERT IGNORE INTO " . $strTable . " (";

        for ($i = 0; $i < count($arrKeys); $i++)
        {
            $strBody .= $arrKeys[$i];

            if ($i < count($arrKeys) - 1)
                $strBody .= ", ";
        }

        $strBody .= ") VALUES ( ";

        $arrValues = array();

        for ($i = 0; $i < count($arrData); $i++)
        {
            $arrValues[$i] = trim(str_replace("'", "", $arrData[$arrKeys[$i]]));

            $strBody .= "?";

            if ($i < count($arrData) - 1)
                $strBody .= ", ";
        }

        $strBody .= ")";

        return array("body" => $strBody, "value" => $arrValues);
    }

    /* -------------------------------------------------------------------------
     * Build Section
     */

    /**
     * Build a whole sql dump file
     * 
     * @param array $arrTables Array with Tables
     * @return string 
     */
    private function buildSQL($arrTables)
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
    private function buildInsert($arrData)
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

                    foreach ($table['values'] as $valueaaa)
                    {
                        $string .= $this->buildInsertDataSQL($table['name'], $table['keys'], $valueaaa);
                        $string .= "\r\n";
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