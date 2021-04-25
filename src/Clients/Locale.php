<?php

namespace MenAtWork\SyncCto\Clients;

/**
 * Class Locale
 *
 * @package MenAtWork\SyncCto\Clients
 */
class Locale implements ILocal
{
    use TraitClient;

    /**
     * @inheritDoc
     */
    public function startConnection()
    {
        // TODO: Implement startConnection() method.
    }

    /**
     * @inheritDoc
     */
    public function referrerDisable()
    {
        // TODO: Implement referrerDisable() method.
    }

    /**
     * @inheritDoc
     */
    public function referrerEnable()
    {
        // TODO: Implement referrerEnable() method.
    }

    /**
     * @inheritDoc
     */
    public function getVersionSyncCto()
    {
        // TODO: Implement getVersionSyncCto() method.
    }

    /**
     * @inheritDoc
     */
    public function getVersionContao()
    {
        // TODO: Implement getVersionContao() method.
    }

    /**
     * @inheritDoc
     */
    public function getVersionCtoCommunication()
    {
        // TODO: Implement getVersionCtoCommunication() method.
    }

    /**
     * @inheritDoc
     */
    public function getClientParameter()
    {
        // TODO: Implement getClientParameter() method.
    }

    /**
     * @inheritDoc
     */
    public function getPurgData()
    {
        // TODO: Implement getPurgData() method.
    }

    /**
     * @inheritDoc
     */
    public function setAttentionFlag($booState)
    {
        // TODO: Implement setAttentionFlag() method.
    }

    /**
     * @inheritDoc
     */
    public function setDisplayErrors($booState)
    {
        // TODO: Implement setDisplayErrors() method.
    }

    /**
     * @inheritDoc
     */
    public function purgeTempFolder()
    {
        // TODO: Implement purgeTempFolder() method.
    }

    public function purgeTempTables()
    {
        // TODO: Implement purgeTempTables() method.
    }

    /**
     * @inheritDoc
     */
    public function purgeCache()
    {
        // TODO: Implement purgeCache() method.
    }

    /**
     * @inheritDoc
     */
    public function createCache()
    {
        // TODO: Implement createCache() method.
    }

    /**
     * @inheritDoc
     */
    public function runMaintenance($arrSettings)
    {
        // TODO: Implement runMaintenance() method.
    }

    /**
     * @inheritDoc
     */
    public function runFinalOperations()
    {
        // TODO: Implement runFinalOperations() method.
    }

    /**
     * @inheritDoc
     */
    public function runCecksumCompare($arrChecksumList, $blnDisableDbafsConflicts = false)
    {
        // TODO: Implement runCecksumCompare() method.
    }

    /**
     * @inheritDoc
     */
    public function getChecksumFiles($arrFileList = null)
    {
        // TODO: Implement getChecksumFiles() method.
    }

    /**
     * @inheritDoc
     */
    public function getChecksumCore()
    {
        // TODO: Implement getChecksumCore() method.
    }

    /**
     * @inheritDoc
     */
    public function getChecksumFolderCore()
    {
        // TODO: Implement getChecksumFolderCore() method.
    }

    /**
     * @inheritDoc
     */
    public function getChecksumFolderFiles()
    {
        // TODO: Implement getChecksumFolderFiles() method.
    }

    /**
     * @inheritDoc
     */
    public function checkDeleteFiles($arrChecksumList)
    {
        // TODO: Implement checkDeleteFiles() method.
    }

    /**
     * @inheritDoc
     */
    public function searchDeleteFolders($arrChecksumList)
    {
        // TODO: Implement searchDeleteFolders() method.
    }

    /**
     * @inheritDoc
     */
    public function sendFile($strFolder, $strFile, $strMD5 = "", $intTyp = 1, $strSplitname = "")
    {
        // TODO: Implement sendFile() method.
    }

    /**
     * @inheritDoc
     */
    public function sendFileNewDestination($strSource, $strDestination, $strMD5 = "", $intTyp = 1, $strSplitname = "")
    {
        // TODO: Implement sendFileNewDestination() method.
    }

    /**
     * @inheritDoc
     */
    public function runFileImport($arrFileList, $blnIsDbafs)
    {
        // TODO: Implement runFileImport() method.
    }

    /**
     * @inheritDoc
     */
    public function updateDbafs($arrFileList)
    {
        // TODO: Implement updateDbafs() method.
    }

    /**
     * @inheritDoc
     */
    public function deleteFiles($arrFileList, $blnIsDbafs)
    {
        // TODO: Implement deleteFiles() method.
    }

    /**
     * @inheritDoc
     */
    public function buildSingleFile($strSplitname, $intSplitcount, $strMovepath, $strMD5)
    {
        // TODO: Implement buildSingleFile() method.
    }

    /**
     * @inheritDoc
     */
    public function runSplitFiles($strSrcFile, $strDesFolder, $strDesFile, $intSizeLimit)
    {
        // TODO: Implement runSplitFiles() method.
    }

    /**
     * @inheritDoc
     */
    public function getFile($strPath, $strSavePath)
    {
        // TODO: Implement getFile() method.
    }

    /**
     * @inheritDoc
     */
    public function getPathList($strName = null)
    {
        // TODO: Implement getPathList() method.
    }

    /**
     * @inheritDoc
     */
    public function getDbafsInformationFor($arrFiles)
    {
        // TODO: Implement getDbafsInformationFor() method.
    }

    /**
     * @inheritDoc
     */
    public function runSQLImport($filename, $additionalSQL)
    {
        // TODO: Implement runSQLImport() method.
    }

    public function runDatabaseDump($arrTables, $booTempFolder)
    {
        // TODO: Implement runDatabaseDump() method.
    }

    /**
     * @inheritDoc
     */
    public function dropTable($arrTables, $blnBackup)
    {
        // TODO: Implement dropTable() method.
    }

    /**
     * @inheritDoc
     */
    public function executeSQL($arrSQL)
    {
        // TODO: Implement executeSQL() method.
    }

    /**
     * @inheritDoc
     */
    public function getRecommendedTables()
    {
        // TODO: Implement getRecommendedTables() method.
    }

    /**
     * @inheritDoc
     */
    public function getNoneRecommendedTables()
    {
        // TODO: Implement getNoneRecommendedTables() method.
    }

    /**
     * @inheritDoc
     */
    public function getHiddenTables()
    {
        // TODO: Implement getHiddenTables() method.
    }

    /**
     * @inheritDoc
     */
    public function getPreparedHiddenTablesPlaceholder()
    {
        // TODO: Implement getPreparedHiddenTablesPlaceholder() method.
    }

    /**
     * @inheritDoc
     */
    public function getTablesHash($tables)
    {
        // TODO: Implement getTablesHash() method.
    }

    /**
     * @inheritDoc
     */
    public function runLocalConfigImport()
    {
        // TODO: Implement runLocalConfigImport() method.
    }

    /**
     * @inheritDoc
     */
    public function getLocalConfig()
    {
        // TODO: Implement getLocalConfig() method.
    }

    /**
     * @inheritDoc
     */
    public function getPhpFunctions()
    {
        // TODO: Implement getPhpFunctions() method.
    }

    /**
     * @inheritDoc
     */
    public function getProFunctions()
    {
        // TODO: Implement getProFunctions() method.
    }

    /**
     * @inheritDoc
     */
    public function getPhpConfigurations()
    {
        // TODO: Implement getPhpConfigurations() method.
    }

    /**
     * @inheritDoc
     */
    public function getExtendedInformation($strDateFormate)
    {
        // TODO: Implement getExtendedInformation() method.
    }

    /**
     * @inheritDoc
     */
    public function createPathconfig()
    {
        // TODO: Implement createPathconfig() method.
    }

    /**
     * @inheritDoc
     */
    public function startAutoUpdater($strZipPath)
    {
        // TODO: Implement startAutoUpdater() method.
    }
}