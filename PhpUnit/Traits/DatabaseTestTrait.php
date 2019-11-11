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

    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @throws Exception
     */
    protected function clearMysql(): void
    {
        $connection = $this->em->getConnection();
        $parameters = $this->getProperty($connection, 'params');
        $this->setProperty($connection, 'params', array_merge($parameters, ['dbname' => $this->getEmDatabaseName()]));
        [$name, $value] = $this->getPropertyByInstance($this->em, EntityManager::class);
        $this->setProperty($value, 'conn', $connection);

        if ($name) {
            $this->setProperty($this->em, $name, $value);
        }

        $connection->exec('SET FOREIGN_KEY_CHECKS = 0;');
        $tables = array_column(
            $connection->fetchAll(
                sprintf(
                    "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '%s' AND (AUTO_INCREMENT > 1 OR TABLE_ROWS > 0) AND TABLE_TYPE = 'BASE TABLE'",
                    $connection->getDatabase()
                )
            ),
            'TABLE_NAME'
        );
        foreach ($connection->getSchemaManager()->listTables() as $table) {
            if (in_array($table->getName(), $tables, TRUE)) {
                $this->em->getConnection()->exec(sprintf('TRUNCATE TABLE `%s`;', $table->getName()));
            }
        }
        $connection->exec('SET FOREIGN_KEY_CHECKS = 0;');
    }

    /**
     * @throws Exception
     */
    protected function clearMongo(): void
    {
        $this->dm->getConfiguration()->setDefaultDB($this->getDmDatabaseName());
        $this->dm->getSchemaManager()->dropDatabases();
        $this->dm->getSchemaManager()->createCollections();
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
