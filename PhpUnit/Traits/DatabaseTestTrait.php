<?php declare(strict_types=1);

namespace Hanaboso\PhpCheckUtils\PhpUnit\Traits;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Exception;
use LogicException;

/**
 * Trait DatabaseTestTrait
 *
 * @package Hanaboso\PhpCheckUtils\PhpUnit\Traits
 */
trait DatabaseTestTrait
{

    use PrivateTrait;
    use RestoreErrorHandlersTrait;

    /**
     * @var DocumentManager
     */
    protected DocumentManager $dm;

    /**
     * @var EntityManager
     */
    protected EntityManager $em;

    /**
     * @param mixed[] $alwaysClearTables
     * @param mixed[] $neverClearTables
     *
     * @throws Exception
     */
    protected function clearMysql(array $alwaysClearTables = [], array $neverClearTables = []): void
    {
        $connection = $this->em->getConnection();
        $parameters = $this->getProperty($connection, 'params');
        $this->setProperty($connection, 'params', array_merge($parameters, ['dbname' => $this->getEmDatabaseName()]));
        [$name, $value] = $this->getPropertyByInstance($this->em, EntityManager::class);

        if($value){
            $this->setProperty($value, 'conn', $connection);
        }

        if ($name) {
            $this->setProperty($this->em, $name, $value);
        }

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0;');
        $tables = array_column(
            $connection->fetchAllAssociative(
                sprintf(
                    "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '%s' AND (AUTO_INCREMENT > 1 OR TABLE_ROWS > 0) AND TABLE_TYPE = 'BASE TABLE'",
                    $connection->getDatabase(),
                ),
            ),
            'TABLE_NAME',
        );
        $tables = array_unique(array_merge($tables, $alwaysClearTables));
        $tables = array_diff($tables, $neverClearTables);

        foreach ($connection->createSchemaManager()->listTables() as $table) {
            if (in_array($table->getName(), $tables, TRUE)) {
                // @phpstan-ignore-next-line
                $this->em->getConnection()->executeStatement('TRUNCATE TABLE `%s`;', $table->getName());
            }
        }
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0;');
    }

    /**
     * @throws Exception
     */
    protected function clearMongo(): void
    {
        if (!class_exists('Doctrine\\ODM\\MongoDB\\DocumentManager')) {
            throw new LogicException('Package "doctrine/mongodb-odm" does not exist. Please install it first.');
        }

        $this->dm->getConfiguration()->setDefaultDB($this->getDmDatabaseName());
        $this->dm->getSchemaManager()->dropDatabases();
    }

    /**
     * @param object $entity
     * @param bool   $flush
     *
     * @throws Exception
     */
    protected function pfe(object $entity, bool $flush = TRUE): void
    {
        $this->pf($entity, TRUE, $flush);
    }

    /**
     * @param object $document
     * @param bool   $flush
     *
     * @throws Exception
     */
    protected function pfd(object $document, bool $flush = TRUE): void
    {
        if (!class_exists('Doctrine\\ODM\\MongoDB\\DocumentManager')) {
            throw new LogicException('Package "doctrine/mongodb-odm" does not exist. Please install it first.');
        }

        $this->pf($document, FALSE, $flush);
    }

    /**
     *
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        if (isset($this->em)) {
            $this->em->close();
        }

        if (isset($this->dm)) {
            $this->dm->close();
        }

        $this->restoreErrorHandler();
        $this->restoreExceptionHandler();
    }

    /**
     * --------------------------------------- HELPERS ----------------------------------------------
     */

    /**
     * @param object $object
     * @param bool   $mysql
     * @param bool   $flush
     *
     * @throws Exception
     */
    private function pf(object $object, bool $mysql = FALSE, bool $flush = TRUE): void
    {
        if ($mysql && isset($this->em)) {
            $db = $this->em;
        } else if (!$mysql && isset($this->dm)) {
            $db = $this->dm;
        } else {
            throw new LogicException('Database is not set');
        }

        $db->persist($object);
        if ($flush) {
            $db->flush();
        }
        unset($db);
    }

    /**
     * @return string
     */
    private function getEmDatabaseName(): string
    {
        return sprintf('%s%s', $this->em->getConnection()->getDatabase(), getenv('TEST_TOKEN'));
    }

    /**
     * @return string
     */
    private function getDmDatabaseName(): string
    {
        return sprintf('%s%s', $this->dm->getConfiguration()->getDefaultDB(), getenv('TEST_TOKEN'));
    }

}
