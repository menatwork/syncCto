<?php

namespace MenAtWork\SyncCto\Clients;

/**
 * Interface IClient
 *
 * @package MenAtWork\SyncCto\Clients
 */
interface IClient
{
    /**
     * Set the title of this client. Something like "Remote: Server @ dida".
     *
     * @param string $title
     *
     * @return IClient The current instance.
     */
    public function setTitle(string $title): IClient;

    /**
     * Get the title of this client.
     *
     * @return string The current title.
     */
    public function getTitle(): string;

    /* -------------------------------------------------------------------------
     * Connection functions.
     */

    /**
     * Init the connection if needed.
     * This is not for daily connection more for the one time connection handshake and all.
     *
     * @return void
     */
    public function startConnection(): void;

    /* -------------------------------------------------------------------------
     * Security function.
     */

    /**
     * Enable the referrer check on the client
     *
     * @return bool
     */
    public function referrerEnable(): bool;

    /**
     * Disable the referrer check on the client
     *
     * @return bool
     */
    public function referrerDisable(): bool;

    /**
     * Set the attention flag from client
     *
     * @param bool $booState
     */
    public function setAttentionFlag(bool $booState);

    /**
     * Set the reffere flag from client
     *
     * @param bool $booState
     */
    public function setDisplayErrors(bool $booState);

    /* -------------------------------------------------------------------------
     * Information
     */

    /**
     * Get version from client syncCto
     *
     * @return string
     */
    public function getVersionSyncCto(): string;

    /**
     * Get version from client contao
     *
     * @return string
     */
    public function getVersionContao(): string;

    /**
     * Get version from client contao
     *
     * @return string
     */
    public function getVersionCtoCommunication(): string;

    /**
     * Get parameter from client
     *
     * @return array
     */
    public function getClientParameter(): array;

    /**
     * Get informations for purgedata
     *
     * @return array
     */
    public function getPurgData(): array;

    /* -------------------------------------------------------------------------
     * Maintance
     */

    /**
     * Clear tempfolder
     *
     * @return mixed
     */
    public function purgeTempFolder();

    /**
     * Remove temp tables.
     *
     * @return mixed
     */
    public function purgeTempTables();

    /**
     * Clean the cache.
     *
     * @return mixed
     */
    public function purgeCache();

    /**
     * Rebuild the cache.
     *
     * @return mixed
     */
    public function createCache();

    /**
     * Use the contao function for maintance
     *
     * @return bool
     */
    public function runMaintenance($arrSettings): bool;

    /**
     * Call the last operations on client side.
     *
     * @return array with information.
     */
    public function runFinalOperations(): array;

    /* -------------------------------------------------------------------------
     * File Operations
     */

    /**
     * Compare a file list with the filesystem
     *
     * @param array $arrChecksumList
     *
     * @param bool  $blnDisableDbafsConflicts
     *
     * @return array
     *
     * @throws \Exception
     */
    public function runCecksumCompare(array $arrChecksumList, $blnDisableDbafsConflicts = false): array;

    /**
     * Get a list with fileinformations from files
     *
     * @param array $arrFileList
     *
     * @return mixed
     */
    public function getChecksumFiles($arrFileList = []);

    /**
     * Get a list with files from contao core
     *
     * @return mixed
     */
    public function getChecksumCore();

    /**
     * Get a list with folder from contao core
     *
     * @return mixed
     */
    public function getChecksumFolderCore();

    /**
     * Get a list with folder from contao core
     *
     * @return mixed
     */
    public function getChecksumFolderFiles();

    /**
     * Check for deleted files
     *
     * @param array $arrFilelist
     *
     * @return array
     */
    public function checkDeleteFiles(array $arrFilelist): array;

    /**
     * Check for deleted files
     *
     * @param array $arrFilelist
     *
     * @return array
     */
    public function searchDeleteFolders(array $arrFilelist);

    /**
     * Send a file to the client
     *
     * @param        $strFolder
     * @param string $strFile File + path. Start from TL_ROOT.
     *
     * @param string $strMD5
     * @param int    $intTyp
     * @param string $strSplitname
     *
     * @return bool [true|false]
     */
    public function sendFile($strFolder, string $strFile, $strMD5 = "", $intTyp = 1, $strSplitname = ""): bool;

    /**
     * Send a file to the client
     *
     * @param        $strSource
     * @param        $strDestination
     * @param string $strMD5
     * @param int    $intTyp
     * @param string $strSplitname
     *
     * @return bool [true|false]
     */
    public function sendFileNewDestination(
        $strSource,
        $strDestination,
        $strMD5 = "",
        $intTyp = 1,
        $strSplitname = ""
    ): bool;

    /**
     * Import files from temp folder to the target source. If we have tl_files the system
     * tries to solve problems with the dbafs data from contao.
     *
     * @param array   $arrFileList The list with all files.
     *
     * @param boolean $blnIsDbafs  If true the system tries to run the support for the Contao dbafs.
     *
     * @return array Return a array with information from the client.
     */
    public function runFileImport(array $arrFileList, bool $blnIsDbafs): array;

    /**
     * Update the DBAFS
     *
     * @param array $arrFileList The list with all files.
     *
     * @return mixed
     */
    public function updateDbafs(array $arrFileList);

    /**
     * Delete files
     *
     * @param array   $arrFileList The list with all files.
     *
     * @param boolean $blnIsDbafs  If true the system tries to run the support for the Contao dbafs.
     *
     * @return array Return a array with information from the client.
     */
    public function deleteFiles(array $arrFileList, bool $blnIsDbafs): array;

    /**
     * Build splitfiles back to one big file
     *
     * @param string $strSplitname
     *
     * @param int    $intSplitcount
     *
     * @param string $strMovepath
     *
     * @param string $strMD5
     *
     * @return mixed
     */
    public function buildSingleFile(string $strSplitname, int $intSplitcount, string $strMovepath, string $strMD5);

    /**
     * Build splitfiles back to one big file
     *
     * @param string $strSplitname
     *
     * @param int    $intSplitcount
     *
     * @param string $strMovepath
     *
     * @param string $strMD5
     *
     * @return mixed
     */
    public function runSplitFiles(string $strSplitname, int $intSplitcount, string $strMovepath, string $strMD5);

    /**
     * Get a file
     *
     * @param string $strPath
     *
     * @param string $strSavePath
     *
     * @return boolean
     *
     * @throws \Exception
     */
    public function getFile(string $strPath, string $strSavePath);

    /**
     * Get a list or a string with path information from
     * syncCto.
     *
     * @param string $strName
     *
     * @return mixed
     */
    public function getPathList(string $strName = '');

    /**
     * Get for a list of files the DBAFS information. If the file in not in the DBAFS
     * add it to the DBAFS and than get the information.
     *
     * @param array $arrFiles List of files
     *
     * @return array Return the file list with the information from the dbafs.
     */
    public function getDbafsInformationFor(array $arrFiles);

    /* -------------------------------------------------------------------------
     * Database Operations
     */

    /**
     * Import a SQL zip
     *
     * @param type $filename
     *
     * @return type
     */
    public function runSQLImport($filename, $additionalSQL);

    public function runDatabaseDump($arrTables, $booTempFolder);

    /**
     * Drop tables on client site
     *
     * @param array   $arrTables
     * @param boolean $blnBackup
     *
     * @return void
     */
    public function dropTable($arrTables, $blnBackup);

    /**
     * Exceute SQL commands on client side
     *
     * @param array $arrSQL array([ID] => <br/>array("prepare" => [String(SQL)], "execute" => array([mix]) ) <br/>)
     *
     * @return array array([ID] => response)
     */
    public function executeSQL($arrSQL);

    /**
     * Returns a list without the hidden tables
     *
     * @return array
     */
    public function getRecommendedTables();

    /**
     * Returns a list without the hidden tables
     *
     * @return array
     */
    public function getNoneRecommendedTables();


    /**
     * Returns a list without the hidden tables
     *
     * @return array
     */
    public function getHiddenTables();

    /**
     * Returns a list without the hidden tables
     *
     * @return array
     */
    public function getPreparedHiddenTablesPlaceholder();

    /**
     * Get a list with the hashes for the database.
     *
     * @return array
     */
    public function getTablesHash($tables);

    /* -------------------------------------------------------------------------
     * Config Operations
     */

    /**
     * Import localconfig
     *
     * @return type
     */
    public function runLocalConfigImport();

    public function getLocalConfig();

    public function getPhpFunctions();

    public function getProFunctions();

    public function getPhpConfigurations();

    public function getExtendedInformation($strDateFormate);

    /**
     * Create a file which contains the relative path
     *
     * @return boolean
     * @throws Exception
     */
    public function createPathconfig();
}