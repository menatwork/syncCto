<?php

/**
 * This file is part of MenAtWork/syncCto.
 *
 * (c) 2025 MEN AT WORK
 *
 * @package    MenAtWork/SyncCto
 * @author     Stefan Heimes <heimes@men-at-work.de>
 * @copyright  2025 MEN AT WORK
 * @license    GNU/LGPL
 * @filesource
 */

namespace MenAtWork\SyncCto\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class DbMigration extends AbstractMigration
{
    /**
     * @param Connection $connection
     */
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @inheritDoc
     */
    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        // The use of "tableExists" isn't reliable here, as the table name might be in different cases.
        $knownTableNames = $schemaManager->listTableNames();
        if (
            empty($knownTableNames)
            || !in_array('tl_synccto_stats', $knownTableNames)
        ) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function run(): MigrationResult
    {
        try {
            $result = $this->connection->executeQuery(
                "RENAME TABLE tl_synccto_stats TO tl_syncCto_stats;"
            );
        } catch (Exception $e) {
            return $this->createResult(
                false,
                $e->getMessage()
            );
        }

        return $this->createResult(
            true,
            'Rename tl_synccto_stats TO tl_syncCto_stats'
        );
    }
}