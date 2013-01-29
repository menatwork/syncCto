<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2013 
 * @package    SyncCtoAutoUpdater
 * @license    GNU/LGPL 
 * @filesource
 */

/**
 * SyncCtoAutoUpdater 
 */
class SyncCtoAutoUpdater extends Backend
{

    // Vars
    protected $booZipArchiveCto = false;

    // Const
    const TEMP_PATH     = "system/tmp/";
    const ZIP_FILE_PATH = "FILES";
    const ZIP_FILE_SQL  = "SQL";

    /**
     * List of default ignore values for database
     * 
     * @var array
     */
    protected $arrDefaultValueFunctionIgnore = array(
        "NOW",
        "CURRENT_TIMESTAMP",
    );

    /**
     * List of default ignore values for database
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
     * Constructor 
     */
    public function __construct()
    {
        parent::__construct();

        $this->checkExtensions();
    }

    /**
     * Check some extensions 
     */
    protected function checkExtensions()
    {
        if (file_exists(TL_ROOT . "/system/libraries/ZipArchiveCto.php"))
        {
            $this->booZipArchiveCto = true;
        }
    }

    /**
     * Run Update
     * 
     * @param string $strZipPath Path to the zip file.
     */
    public function update($strZipPath)
    {
        // Check if update archive exists
        if (!file_exists(TL_ROOT . "/" . $strZipPath))
        {
            throw new Exception("Update archive was not found: '$strZipPath'.");
        }

        $arrErrors = array();

        // Update file system
        $this->updateFile($strZipPath);

        // Update database
        $mixResult = $this->updateDatabase($strZipPath);
        
        if ($mixResult !== true && is_array($mixResult))
        {
            $arrErrors = array_merge($arrErrors, $mixResult);
        }

        if (count($arrErrors) == 0)
        {
            return true;
        }
        else
        {
            return $arrErrors;
        }
    }

    /**
     * Update the filesystem 
     */
    protected function updateFile($strZipPath)
    {
        $arrFiles = array();

        // If ZipArchiveCto is installed, use it. If not use contao`s zipReader.
        if ($this->booZipArchiveCto)
        {
            $arrFiles = $this->unzipBySyncCto($strZipPath);
        }
        else
        {
            $arrFiles = $this->unzipByContao($strZipPath);
        }

        $this->moveFiles($arrFiles);
    }

    /**
     * Unzip the file by using the ZipArchiveCto class
     * 
     * @param sring $strZipPath Path to the zip
     * @return array A list with current path and taget path.
     * @throws Exception 
     */
    protected function unzipBySyncCto($strZipPath)
    {
        // New archive
        $objZipArchive = new ZipArchiveCto();

        // Open archive
        if (($mixError = $objZipArchive->open($strZipPath, ZipArchiveCto::CREATE)) !== true)
        {
            throw new Exception($GLOBALS['TL_LANG']['MSC']['error'] . ": " . $objZipArchive->getErrorDescription($mixError));
        }

        // Create tmp folder
        new Folder(self::TEMP_PATH . '/syncCtoAutoUpdate');

        $arrMoveList = array();

        // Extract all files to temp folder
        for ($i = 0; $i < $objZipArchive->numFiles; $i++)
        {
            $filename = $objZipArchive->getNameIndex($i);

            // Check if the file part of the folder "FILES"
            if (!preg_match("/^" . self::ZIP_FILE_PATH . "\//i", $filename))
            {
                continue;
            }

            // Build path
            $movePath   = preg_replace("/^" . self::ZIP_FILE_PATH . "\//i", "", $filename);
            $targetPath = self::TEMP_PATH . 'syncCtoAutoUpdate/';

            $arrMoveList[$targetPath . $filename] = $movePath;

            // Extract file
            if (!$objZipArchive->extractTo($targetPath, $filename))
            {
                throw new Exception("Error by extract file: " . $filename);
            }
        }

        return $arrMoveList;
    }

    /**
     * Unzip the file by using the ZipReader class from contao
     * 
     * @param sring $strZipPath Path to the zip
     * @return array A list with current path and taget path.
     * @throws Exception 
     */
    protected function unzipByContao($strZipPath)
    {
        $arrMoveList = array();

        $objZipReader = new ZipReader($strZipPath);

        // Create tmp folder
        new Folder(self::TEMP_PATH . '/syncCtoAutoUpdate');

        foreach ($objZipReader->getFileList() as $value)
        {
            // Check if the file part of the folder "FILES"
            if (!preg_match("/^" . self::ZIP_FILE_PATH . "\//i", $value))
            {
                continue;
            }

            $movePath   = preg_replace("/^" . self::ZIP_FILE_PATH . "\//i", "", $value);
            $targetPath = self::TEMP_PATH . 'syncCtoAutoUpdate/' . $movePath;

            $arrMoveList[$targetPath] = $movePath;

            // Write file
            $objZipReader->getFile($value);
            $objFile = new File($targetPath);
            $objFile->write($objZipReader->unzip());
            $objFile->close();

            unset($objFile);
        }

        return $arrMoveList;
    }

    /**
     * Move a file from a to b
     * 
     * @param array $arrMoveList
     * @throws Exception 
     */
    protected function moveFiles($arrMoveList)
    {
        $objFiles = Files::getInstance();

        foreach ($arrMoveList as $key => $value)
        {
            $strFolderPath = dirname($value);

            if ($strFolderPath != ".")
            {
                $objFolder = new Folder($strFolderPath);
                unset($objFolder);
            }

            if ($objFiles->copy($key, $value) == false)
            {
                throw new Exception("Could not move tmp file to destination. $key TO $value");
            }
        }
    }

    /**
     * Update the database.
     */
    protected function updateDatabase($strZipPath)
    {
        $arrError = array();

        // New archive
        $objZipArchive = new ZipArchiveCto();

        // Open archive
        if (($mixError = $objZipArchive->open($strZipPath, ZipArchiveCto::CREATE)) !== true)
        {
            throw new Exception($GLOBALS['TL_LANG']['MSC']['error'] . ": " . $objZipArchive->getErrorDescription($mixError));
        }

        // Read XML
        $xmlReader = new XMLReader();
        $xmlReader->XML($objZipArchive->getFromName("SQL/sql.xml"));

        // Init tmp vars
        $strTableName = "";
        $arrFieldList = array();
        $arrDefList = array();
        $strOptions = array();

        while ($xmlReader->read())
        {
            switch ($xmlReader->nodeType)
            {
                case XMLReader::ELEMENT:
                    switch ($xmlReader->localName)
                    {
                        case "table":
                            $strTableName = $xmlReader->getAttribute("name");
                            $arrFieldList = array();
                            $arrDefList = array();
                            break;

                        case "field":
                            $arrFieldList[$xmlReader->getAttribute("name")] = $xmlReader->readString();
                            break;

                        case "def":
                            $arrDefList[$xmlReader->getAttribute("name")] = $xmlReader->readString();
                            break;

                        case "option":
                            $strOptions = $xmlReader->readString();
                            break;
                    }
                    break;
                case XMLReader::END_ELEMENT:
                    switch ($xmlReader->localName)
                    {
                        case "table":
                            $mixResult = $this->compareDatabase($strTableName, $arrFieldList, $arrDefList, $strOptions);
                            if ($mixResult !== true && is_array($mixResult))
                            {
                                $arrError = array_merge($arrError, $mixResult);
                            }
                            break;
                    }
                    break;
            }
        }

        if (count($arrError) == 0)
        {
            return true;
        }
        else
        {
            return $arrError;
        }
    }

    /**
     * Compare extern und local database
     * 
     * @param type $strTableName
     * @param type $arrFieldList
     * @param type $arrDefList
     * @param type $strOptions
     * @return boolean 
     */
    protected function compareDatabase($strTableName, $arrFieldList, $arrDefList, $strOptions)
    {
        $arrError = array();

        if (in_array($strTableName, $this->Database->listTables()))
        {
            $mixResult = $this->compareStructur($strTableName, $arrFieldList, $arrDefList);
            if ($mixResult !== true && is_array($mixResult))
            {
                $arrError = array_merge($arrError, $mixResult);
            }
        }
        else
        {
            try
            {
                $this->Database->query($this->buildSQLTable($strTableName, $arrFieldList, $arrDefList, $strOptions));
            }
            catch (Exception $exc)
            {
                $arrError = array_merge($arrError, array($strTableName => $exc->getMessage()));
            }
        }

        if (count($arrError) == 0)
        {
            return true;
        }
        else
        {
            return $arrError;
        }
    }

    /**
     * Compare the fields and definitions
     * 
     * @param type $strTableName
     * @param type $arrFieldList
     * @param type $arrDefList
     * @return boolean 
     */
    protected function compareStructur($strTableName, $arrFieldList, $arrDefList)
    {
        $arrClientInformation = $this->getTableStructure($strTableName);
        $arrError             = array();

        // Update Columns        
        foreach ($arrFieldList as $keyServer => $valueServer)
        {
            try
            {
                if (key_exists($keyServer, $arrClientInformation['TABLE_FIELDS']))
                {
                    if ($valueServer != $arrClientInformation['TABLE_FIELDS'][$keyServer])
                    {
                        // Update column
                        $this->Database
                                ->prepare("ALTER TABLE `$strTableName` MODIFY COLUMN $valueServer")
                                ->execute();
                    }
                    else
                    {
                        // Nothing to do
                    }
                }
                else
                {
                    // Add new columns
                    $this->Database
                            ->prepare("ALTER TABLE `$strTableName` ADD COLUMN $valueServer")
                            ->execute();
                }
            }
            catch (Exception $exc)
            {
                $arrError[$strTableName][$keyServer] = $exc->getMessage();
            }

            // Unset
            unset($arrFieldList[$keyServer]);
            unset($arrClientInformation['TABLE_FIELDS'][$keyServer]);
        }

        foreach ($arrClientInformation['TABLE_FIELDS'] as $keyClient => $valueClient)
        {
            try
            {
                // Remove columns
                $this->Database
                        ->prepare("ALTER TABLE `$strTableName` DROP COLUMN `$keyClient`")
                        ->execute();
            }
            catch (Exception $exc)
            {
                $arrError[$strTableName][$keyServer] = $exc->getMessage();
            }

            unset($arrClientInformation['TABLE_FIELDS'][$keyServer]);
        }

        // Update definitions
        foreach ($arrDefList as $keyServer => $valueServer)
        {
            try
            {
                if (key_exists($keyServer, $arrClientInformation['TABLE_CREATE_DEFINITIONS']))
                {
                    if ($valueServer != $arrClientInformation['TABLE_CREATE_DEFINITIONS'][$keyServer])
                    {
                        if (preg_match("/^PRIMARY/i", $valueServer))
                        {
                            // Remove 
                            $this->Database
                                    ->prepare("ALTER TABLE `$strTableName` DROP PRIMARY KEY")
                                    ->execute();

                            // Add 
                            $this->Database
                                    ->prepare("ALTER TABLE `$strTableName` ADD $valueServer")
                                    ->execute();
                        }

                        if (preg_match("/^INDEX/i", $valueServer))
                        {
                            // Remove 
                            $this->Database
                                    ->prepare("ALTER TABLE `$strTableName` DROP INDEX `$keyServer`")
                                    ->execute();

                            // Add 
                            $this->Database
                                    ->prepare("ALTER TABLE `$strTableName` ADD $valueServer")
                                    ->execute();
                        }

                        if (preg_match("/^KEY/i", $valueServer))
                        {
                            // Remove 
                            $this->Database
                                    ->prepare("ALTER TABLE `$strTableName` DROP KEY `$keyServer`")
                                    ->execute();

                            // Add 
                            $this->Database
                                    ->prepare("ALTER TABLE `$strTableName` ADD $valueServer")
                                    ->execute();
                        }
                    }
                    else
                    {
                        // Nothing to do
                    }
                }
                else
                {
                    if (preg_match("/^PRIMARY/i", $valueServer))
                    {
                        // Add 
                        $this->Database
                                ->prepare("ALTER TABLE `$strTableName` ADD $valueServer")
                                ->execute();
                    }

                    if (preg_match("/^INDEX/i", $valueServer))
                    {
                        // Add 
                        $this->Database
                                ->prepare("ALTER TABLE `$strTableName` ADD $valueServer")
                                ->execute();
                    }

                    if (preg_match("/^KEY/i", $valueServer))
                    {
                        // Add 
                        $this->Database
                                ->prepare("ALTER TABLE `$strTableName` ADD $valueServer")
                                ->execute();
                    }
                }
            }
            catch (Exception $exc)
            {
                $arrError[$strTableName][$keyServer] = $exc->getMessage();
            }

            // Unset
            unset($arrDefList[$keyServer]);
            unset($arrClientInformation['TABLE_CREATE_DEFINITIONS'][$keyServer]);
        }

        // Remove definitions
        foreach ($arrClientInformation['TABLE_CREATE_DEFINITIONS'] as $keyClient => $valueCleint)
        {
            try
            {
                if (preg_match("/^PRIMARY/i", $valueCleint))
                {
                    // Remove 
                    $this->Database
                            ->prepare("ALTER TABLE `$strTableName` DROP PRIMARY KEY")
                            ->execute();
                }

                if (preg_match("/^INDEX/i", $valueCleint))
                {
                    // Remove 
                    $this->Database
                            ->prepare("ALTER TABLE `$strTableName` DROP INDEX `$keyClient`")
                            ->execute();
                }

                if (preg_match("/^KEY/i", $valueCleint))
                {
                    // Remove 
                    $this->Database
                            ->prepare("ALTER TABLE `$strTableName` DROP KEY `$keyClient`")
                            ->execute();
                }
            }
            catch (Exception $exc)
            {
                $arrError[$strTableName][$keyServer] = $exc->getMessage();
            }
        }

        if (count($arrError) == 0)
        {
            return true;
        }
        else
        {
            return $arrError;
        }
    }

    // - Helper ----------------------------------------------------------------

    /**
     * Build a "CREATE TABLE" sql statemant
     * 
     * @param array $arrTable Table Informations
     * @param type $strName Table name
     * @return string 
     */
    private function buildSQLTable($strTableName, $arrFieldList, $arrDefList, $strOptions)
    {
        $string = "CREATE TABLE `" . $strTableName . "` (\n  " . implode(",\n  ", $arrFieldList) . (count($arrDefList) ? ',' : '') . "\n";

        if (is_Array($arrDefList))
            $string .= "  " . implode(",\n  ", $arrDefList) . "\n";

        $string .= ")" . $strOptions . ";";

        return $string;
    }

    /**
     * Get information about a table.
     * 
     * @param type $strTableName
     * @return string 
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

}

?>
