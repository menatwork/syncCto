<?php

namespace MenAtWork\SyncCto\Database;

use Contao\Database;
use MenAtWork\SyncCto\Contao\API;
use function Webmozart\Assert\Tests\StaticAnalysis\implementsInterface;

/**
 * Class Diff
 *
 * @package MenAtWork\SyncCto\Database
 */
class Diff
{
    /**
     * Build the compare list for the database.
     *
     * @param array      $arrSourceTables           A list with all tables from the source.
     *
     * @param array      $arrDesTables              A list with all tables from the destination.
     *
     * @param array      $arrHiddenTables           A list with hidden tables. Merged from source and destination.
     *
     * @param array      $arrHiddenTablePlaceholder A list with regex expressions for the filter. The same like the
     *                                              $arrHiddenTables.
     *
     * @param array      $arrSourceTS               List with timestamps from the source.
     *
     * @param array      $arrDesTS                  List with timestamps from the destination.
     *
     * @param array      $arrAllowedTables          List with allowed tables. For example based on the user
     *                                              settings/rights.
     *
     * @param string     $strSrcName                Name of the source e.g. client or server.
     *
     * @param string     $strDesName                Name of the destination e.g. client or server.
     *
     * @return array
     */
    public static function getFormatedCompareList(
        $arrSourceTables,
        $arrDesTables,
        $arrHiddenTables,
        $arrHiddenTablePlaceholder,
        $arrSourceTS,
        $arrDesTS,
        $arrAllowedTables,
        $strSrcName,
        $strDesName
    ): array {
        // Remove hidden tables or tables without permission.
        if (is_array($arrHiddenTables) && count((array)$arrHiddenTables) != 0) {
            foreach ($arrSourceTables as $key => $value) {
                if (in_array($key, $arrHiddenTables)
                    || (is_array($arrAllowedTables) && in_array($key, $arrAllowedTables))) {
                    unset($arrSourceTables[$key]);
                }
            }

            foreach ($arrDesTables as $key => $value) {
                if (in_array($key, $arrHiddenTables)
                    || (is_array($arrAllowedTables) && in_array($key, $arrAllowedTables))) {
                    unset($arrDesTables[$key]);
                }
            }
        }

        // Remove hidden tables based on the regex.
        if (is_array($arrHiddenTablePlaceholder) && count((array)$arrHiddenTablePlaceholder) != 0) {
            foreach ($arrHiddenTablePlaceholder as $strRegex) {
                // Run each and check it with the given name.
                foreach ($arrSourceTables as $key => $value) {
                    if (preg_match('/^' . $strRegex . '$/', $key)) {
                        unset($arrSourceTables[$key]);
                    }
                }

                // Run each and check it with the given name.
                foreach ($arrDesTables as $key => $value) {
                    if (preg_match('/^' . $strRegex . '$/', $key)) {
                        unset($arrDesTables[$key]);
                    }
                }
            }
        }

        $arrCompareList = [];

        // Make a diff
        $arrMissingOnDes    = array_diff(array_keys($arrSourceTables), array_keys($arrDesTables));
        $arrMissingOnSource = array_diff(array_keys($arrDesTables), array_keys($arrSourceTables));

        // New Tables
        foreach ($arrMissingOnDes as $keySrcTables) {
            $strType = $arrSourceTables[$keySrcTables]['type'];

            $arrCompareList[$strType][$keySrcTables][$strSrcName]['name']    = $keySrcTables;
            $arrCompareList[$strType][$keySrcTables][$strSrcName]['tooltip'] = API::getReadableSize($arrSourceTables[$keySrcTables]['size'])
                . ', '
                . vsprintf(($arrSourceTables[$keySrcTables]['count'] == 1) ? $GLOBALS['TL_LANG']['MSC']['entry'] : $GLOBALS['TL_LANG']['MSC']['entries'],
                    [$arrSourceTables[$keySrcTables]['count']]);

            $arrCompareList[$strType][$keySrcTables][$strSrcName]['class'] = 'none';

            $arrCompareList[$strType][$keySrcTables][$strDesName]['name'] = '-';
            $arrCompareList[$strType][$keySrcTables]['diff']              = $GLOBALS['TL_LANG']['MSC']['create'];

            unset($arrSourceTables[$keySrcTables]);
        }

        // Del Tables
        foreach ($arrMissingOnSource as $keyDesTables) {
            $strType = $arrDesTables[$keyDesTables]['type'];

            $arrCompareList[$strType][$keyDesTables][$strDesName]['name']    = $keyDesTables;
            $arrCompareList[$strType][$keyDesTables][$strSrcName]['tooltip'] = API::getReadableSize($arrDesTables[$keyDesTables]['size'])
                . ', '
                . vsprintf(($arrDesTables[$keyDesTables]['count'] == 1) ? $GLOBALS['TL_LANG']['MSC']['entry'] : $GLOBALS['TL_LANG']['MSC']['entries'],
                    [$arrDesTables[$keyDesTables]['count']]);

            $arrCompareList[$strType][$keyDesTables][$strDesName]['class'] = 'none';

            $arrCompareList[$strType][$keyDesTables][$strSrcName]['name'] = '-';
            $arrCompareList[$strType][$keyDesTables]['diff']              = $GLOBALS['TL_LANG']['MSC']['delete'];
            $arrCompareList[$strType][$keyDesTables]['del']               = true;

            unset($arrDesTables[$keyDesTables]);
        }

        // Tables which exist on both systems
        foreach ($arrSourceTables as $keySrcTable => $valueSrcTable) {
            $strType = $valueSrcTable['type'];

            $arrCompareList[$strType][$keySrcTable][$strSrcName]['name']    = $keySrcTable;
            $arrCompareList[$strType][$keySrcTable][$strSrcName]['tooltip'] = API::getReadableSize($valueSrcTable['size'])
                . ', '
                . vsprintf(($valueSrcTable['count'] == 1) ? $GLOBALS['TL_LANG']['MSC']['entry'] : $GLOBALS['TL_LANG']['MSC']['entries'],
                    [$valueSrcTable['count']]);

            $valueClientTable = $arrDesTables[$keySrcTable];

            $arrCompareList[$strType][$keySrcTable][$strDesName]['name']    = $keySrcTable;
            $arrCompareList[$strType][$keySrcTable][$strDesName]['tooltip'] = API::getReadableSize($valueClientTable['size'])
                . ', '
                . vsprintf(($valueClientTable['count'] == 1) ? $GLOBALS['TL_LANG']['MSC']['entry'] : $GLOBALS['TL_LANG']['MSC']['entries'],
                    [$valueClientTable['count']]);

            // Get some diff information
            $arrNewId     = self::getDiffId($valueClientTable, $valueSrcTable);
            $arrDeletedId = self::getDiffId($valueSrcTable, $valueClientTable);
            $intDiffId    = count((array)$arrNewId) + count($arrDeletedId);
            $intDiff      = self::getDiff($valueSrcTable, $valueClientTable);

            // Add 'entry' or 'entries' to diff
            $arrCompareList[$strType][$keySrcTable]['diffCount']   = $intDiff;
            $arrCompareList[$strType][$keySrcTable]['diffCountId'] = $intDiffId;
            $arrCompareList[$strType][$keySrcTable]['diff']        = vsprintf(
                ($intDiff == 1)
                    ? $GLOBALS['TL_LANG']['MSC']['entry']
                    : $GLOBALS['TL_LANG']['MSC']['entries'],
                [$intDiff]
            );
            $arrCompareList[$strType][$keySrcTable]['diffNewId']     = $arrNewId;
            $arrCompareList[$strType][$keySrcTable]['diffDeletedId'] = $arrDeletedId;

            // Check timestamps
            if (
                array_key_exists($keySrcTable, $arrSourceTS['current'])
                && array_key_exists($keySrcTable, $arrSourceTS['lastSync'])
            ) {
                $isDiffMeta     = $arrSourceTS['current'][$keySrcTable]['metaDate'] != $arrSourceTS['lastSync'][$keySrcTable]['metaDate'];
                $isDiffCount    = $arrSourceTS['current'][$keySrcTable]['rowCount'] != $arrSourceTS['lastSync'][$keySrcTable]['rowCount'];
                $isDiffChange   = $arrSourceTS['current'][$keySrcTable]['lastUpdate'] != $arrSourceTS['lastSync'][$keySrcTable]['lastUpdate'];
                $isDiffChecksum = $arrSourceTS['current'][$keySrcTable]['checksum'] != $arrSourceTS['lastSync'][$keySrcTable]['checksum'];
                $isDiffFound    = $isDiffMeta || $isDiffCount || $isDiffChange || $isDiffChecksum;

                $elementClass   = [];
                $elementClass[] = ($isDiffFound) ? 'changed' : 'unchanged';
                $arrCompareList[$strType][$keySrcTable][$strSrcName]['method'] = [];
                if ($isDiffMeta) {
                    $elementClass[]                                                        = 'change-meta';
                    $arrCompareList[$strType][$keySrcTable][$strSrcName]['method']['meta'] = 'meta';
                }
                if ($isDiffCount) {
                    $elementClass[]                                                         = 'change-count';
                    $arrCompareList[$strType][$keySrcTable][$strSrcName]['method']['count'] = 'count';
                }
                if ($isDiffChange) {
                    $elementClass[]                                                          = 'change-update';
                    $arrCompareList[$strType][$keySrcTable][$strSrcName]['method']['update'] = 'update';
                }
                if ($isDiffChecksum) {
                    $elementClass[]                                                            = 'change-checksum';
                    $arrCompareList[$strType][$keySrcTable][$strSrcName]['method']['checksum'] = 'checksum';
                }

                $arrCompareList[$strType][$keySrcTable][$strSrcName]['class'] = implode(' ', $elementClass);
                $arrCompareList[$strType][$keySrcTable][$strSrcName]['state'] = ($isDiffFound) ? 'changed' : 'unchanged';
            } else {
                $arrCompareList[$strType][$keySrcTable][$strSrcName]['class'] = 'no-sync';
                $arrCompareList[$strType][$keySrcTable][$strSrcName]['state'] = 'no-sync';
            }

            if (
                array_key_exists($keySrcTable, $arrDesTS['current'])
                && array_key_exists($keySrcTable, $arrDesTS['lastSync'])
            ) {
                $isDiffMeta     = $arrDesTS['current'][$keySrcTable]['metaDate'] != $arrDesTS['lastSync'][$keySrcTable]['metaDate'];
                $isDiffCount    = $arrDesTS['current'][$keySrcTable]['rowCount'] != $arrDesTS['lastSync'][$keySrcTable]['rowCount'];
                $isDiffChange   = $arrDesTS['current'][$keySrcTable]['lastUpdate'] != $arrDesTS['lastSync'][$keySrcTable]['lastUpdate'];
                $isDiffChecksum = $arrDesTS['current'][$keySrcTable]['checksum'] != $arrDesTS['lastSync'][$keySrcTable]['checksum'];
                $isDiffFound    = $isDiffMeta || $isDiffCount || $isDiffChange || $isDiffChecksum;

                $elementClass   = [];
                $elementClass[] = ($isDiffFound) ? 'changed' : 'unchanged';
                $arrCompareList[$strType][$keySrcTable][$strDesName]['method'] = [];
                if ($isDiffMeta) {
                    $elementClass[]                                                        = 'change-meta';
                    $arrCompareList[$strType][$keySrcTable][$strDesName]['method']['meta'] = 'meta';
                }
                if ($isDiffCount) {
                    $elementClass[]                                                         = 'change-count';
                    $arrCompareList[$strType][$keySrcTable][$strDesName]['method']['count'] = 'count';
                }
                if ($isDiffChange) {
                    $elementClass[]                                                          = 'change-update';
                    $arrCompareList[$strType][$keySrcTable][$strDesName]['method']['update'] = 'update';
                }
                if ($isDiffChecksum) {
                    $elementClass[]                                                            = 'change-checksum';
                    $arrCompareList[$strType][$keySrcTable][$strDesName]['method']['checksum'] = 'checksum';
                }

                $arrCompareList[$strType][$keySrcTable][$strDesName]['class'] = implode(' ', $elementClass);
                $arrCompareList[$strType][$keySrcTable][$strDesName]['state'] = ($isDiffFound) ? 'changed' : 'unchanged';
            } else {
                $arrCompareList[$strType][$keySrcTable][$strDesName]['class'] = 'no-sync';
                $arrCompareList[$strType][$keySrcTable][$strDesName]['state'] = 'no-sync';
            }

            // Check CSS
            if (
                $arrCompareList[$strType][$keySrcTable][$strSrcName]['state'] == 'changed'
                && $arrCompareList[$strType][$keySrcTable]['client']['state'] == 'changed'
            ) {
                $arrCompareList[$strType][$keySrcTable][$strSrcName]['class'] .= ' changed-both';
                $arrCompareList[$strType][$keySrcTable][$strDesName]['class'] .= ' changed-both';
            }

            // Check if we have some changes
            if (
                $arrCompareList[$strType][$keySrcTable][$strSrcName]['state'] == 'unchanged'
                && $arrCompareList[$strType][$keySrcTable][$strDesName]['state'] == 'unchanged'
                && $arrCompareList[$strType][$keySrcTable]['diffCount'] == 0
            ) {
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
    public static function getDiff($arrSrcTables, $arrDesTables): int
    {
        return abs((int)$arrSrcTables['count'] - (int)$arrDesTables['count']);
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
    public static function getDiffId($arrSrcTables, $arrDesTables): array
    {
        $arrSrcId = [];
        $arrDesId = [];

        // Rebuild the id list.
        foreach ($arrSrcTables['ids'] as $arrIdRange) {
            if ($arrIdRange['start'] == $arrIdRange['end']) {
                $arrSrcId[] = intval($arrIdRange['start']);
            } else {
                for ($i = $arrIdRange['start']; $i < ($arrIdRange['end'] + 1); $i++) {
                    $arrSrcId[] = intval($i);
                }
            }
        }

        // Rebuild the id list.
        foreach ($arrDesTables['ids'] as $arrIdRange) {
            if ($arrIdRange['start'] == $arrIdRange['end']) {
                $arrDesId[] = intval($arrIdRange['start']);
            } else {
                for ($i = $arrIdRange['start']; $i < ($arrIdRange['end'] + 1); $i++) {
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
     * @param array $arrTimestampServer List with the server data.
     *
     * @param array $arrTimestampClient List with the client data.
     *
     * @param int   $intClientID        The ID of the client.
     *
     * @return array
     */
    public static function getAllTimeStamps($arrTimestampServer, $arrTimestampClient, $intClientID): array
    {
        $arrLocationLastTableTimestamp = [
            'server' => [],
            'client' => []
        ];

        foreach ($arrLocationLastTableTimestamp as $location => $v) {
            $columnName            = sprintf('%s_timestamp', $location);
            $mixLastTableTimestamp = \Contao\Database::getInstance()
                                                     ->prepare(sprintf(
                                                         'SELECT %s FROM tl_synccto_clients WHERE id=?',
                                                         $columnName
                                                     ))
                                                     ->limit(1)
                                                     ->execute($intClientID)
                                                     ->fetchAllAssoc();

            if ('' != $mixLastTableTimestamp[0][$columnName]) {
                $arrLocationLastTableTimestamp[$location] = unserialize($mixLastTableTimestamp[0][$columnName]);

                // This is support for oöder version where we have a simple timestamp.
                foreach ($arrLocationLastTableTimestamp[$location] as $table => $data) {
                    if (!is_array($data)) {
                        $arrLocationLastTableTimestamp[$location][$table] = [
                            'rowCount'   => -1,
                            'metaDate'   => $data,
                            'lastUpdate' => null,
                            'checksum'   => null
                        ];
                    }
                }
            } else {
                $arrLocationLastTableTimestamp[$location] = [];
            }
        }

        // Return the arrays
        return [
            'server' => [
                'current'  => $arrTimestampServer,
                'lastSync' => $arrLocationLastTableTimestamp['server'],
            ],
            'client' => [
                'current'  => $arrTimestampClient,
                'lastSync' => $arrLocationLastTableTimestamp['client'],
            ],
        ];
    }

    /**
     * Get the hash for a tables.
     *
     * @param array $tables List of names of the table
     *
     * @return array The checksum list.
     */
    public static function getHashForTables($tables): array
    {
        $return = [];
        foreach ($tables as $table) {
            $return[$table] = self::getHashForTable($table);
        }

        return $return;
    }

    /**
     * Get the hash for a table.
     *
     * @param string $table Name of the table
     *
     * @return string|null The checksum or null.
     */
    protected static function getHashForTable($table): ?string
    {
        if (!Database::getInstance()->tableExists($table)) {
            return null;
        }

        $checksum = Database::getInstance()
                            ->prepare(sprintf('CHECKSUM TABLE %s', $table))
                            ->execute()
                            ->fetchAllAssoc();

        return $checksum['checksum'] ?? null;
    }
}