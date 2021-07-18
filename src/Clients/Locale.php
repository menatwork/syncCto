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
    public function startConnection(): void
    {
        // Nothing to do for local.
    }

    /**
     * @inheritDoc
     */
    public function referrerEnable(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function referrerDisable(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function setAttentionFlag(bool $booState)
    {
        // TODO: Implement setAttentionFlag() method.
    }

    public function setDisplayErrors(bool $booState)
    {
        // TODO: Implement setDisplayErrors() method.
    }

    public function getVersionSyncCto(): string
    {
        // TODO: Implement getVersionSyncCto() method.
    }

    public function getVersionContao(): string
    {
        // TODO: Implement getVersionContao() method.
    }

    public function getVersionCtoCommunication(): string
    {
        // TODO: Implement getVersionCtoCommunication() method.
    }

    public function getClientParameter(): array
    {
        // TODO: Implement getClientParameter() method.
    }

    public function getPurgData(): array
    {
        // TODO: Implement getPurgData() method.
    }

    public function purgeTempFolder()
    {
        // TODO: Implement purgeTempFolder() method.
    }

    public function purgeTempTables()
    {
        // TODO: Implement purgeTempTables() method.
    }

    public function purgeCache()
    {
        // TODO: Implement purgeCache() method.
    }

    public function createCache()
    {
        // TODO: Implement createCache() method.
    }

    public function runMaintenance($arrSettings): bool
    {
        // TODO: Implement runMaintenance() method.
    }

    public function runFinalOperations(): array
    {
        // TODO: Implement runFinalOperations() method.
    }

    public function runCecksumCompare(array $arrChecksumList, $blnDisableDbafsConflicts = false): array
    {
        // TODO: Implement runCecksumCompare() method.
    }

    public function getChecksumFiles($arrFileList = [])
    {
        // TODO: Implement getChecksumFiles() method.
    }

    public function getChecksumCore()
    {
        // TODO: Implement getChecksumCore() method.
    }

    public function getChecksumFolderCore()
    {
        // TODO: Implement getChecksumFolderCore() method.
    }

    public function getChecksumFolderFiles()
    {
        // TODO: Implement getChecksumFolderFiles() method.
    }

    public function checkDeleteFiles(array $arrFilelist): array
    {
        // TODO: Implement checkDeleteFiles() method.
    }

    public function searchDeleteFolders(array $arrFilelist)
    {
        // TODO: Implement searchDeleteFolders() method.
    }

    public function sendFile($strFolder, string $strFile, $strMD5 = "", $intTyp = 1, $strSplitname = ""): bool
    {
        // TODO: Implement sendFile() method.
    }

    public function sendFileNewDestination(
        $strSource,
        $strDestination,
        $strMD5 = "",
        $intTyp = 1,
        $strSplitname = ""
    ): bool {
        // TODO: Implement sendFileNewDestination() method.
    }

    public function runFileImport(array $arrFileList, bool $blnIsDbafs): array
    {
        // TODO: Implement runFileImport() method.
    }

    public function updateDbafs(array $arrFileList)
    {
        // TODO: Implement updateDbafs() method.
    }

    public function deleteFiles(array $arrFileList, bool $blnIsDbafs): array
    {
        // TODO: Implement deleteFiles() method.
    }

    public function buildSingleFile(string $strSplitname, int $intSplitcount, string $strMovepath, string $strMD5)
    {
        // TODO: Implement buildSingleFile() method.
    }

    public function runSplitFiles(string $strSplitname, int $intSplitcount, string $strMovepath, string $strMD5)
    {
        // TODO: Implement runSplitFiles() method.
    }

    public function getFile(string $strPath, string $strSavePath)
    {
        // TODO: Implement getFile() method.
    }

    public function getPathList(string $strName = '')
    {
        // TODO: Implement getPathList() method.
    }

    public function getDbafsInformationFor(array $arrFiles)
    {
        // TODO: Implement getDbafsInformationFor() method.
    }

    public function runSQLImport($filename, $additionalSQL)
    {
        // TODO: Implement runSQLImport() method.
    }

    public function runDatabaseDump($arrTables, $booTempFolder)
    {
        // TODO: Implement runDatabaseDump() method.
    }

    public function dropTable($arrTables, $blnBackup)
    {
        // TODO: Implement dropTable() method.
    }

    public function executeSQL($arrSQL)
    {
        // TODO: Implement executeSQL() method.
    }

    public function getRecommendedTables()
    {
        // TODO: Implement getRecommendedTables() method.
    }

    public function getNoneRecommendedTables()
    {
        // TODO: Implement getNoneRecommendedTables() method.
    }

    public function getHiddenTables()
    {
        // TODO: Implement getHiddenTables() method.
    }

    public function getPreparedHiddenTablesPlaceholder()
    {
        // TODO: Implement getPreparedHiddenTablesPlaceholder() method.
    }

    public function getTablesHash($tables)
    {
        // TODO: Implement getTablesHash() method.
    }

    public function runLocalConfigImport()
    {
        // TODO: Implement runLocalConfigImport() method.
    }

    public function getLocalConfig()
    {
        // TODO: Implement getLocalConfig() method.
    }

    public function getPhpFunctions()
    {
        // TODO: Implement getPhpFunctions() method.
    }

    public function getProFunctions()
    {
        // TODO: Implement getProFunctions() method.
    }

    public function getPhpConfigurations()
    {
        // TODO: Implement getPhpConfigurations() method.
    }

    public function getExtendedInformation($strDateFormate)
    {
        // TODO: Implement getExtendedInformation() method.
    }

    public function createPathconfig()
    {
        // TODO: Implement createPathconfig() method.
    }
}