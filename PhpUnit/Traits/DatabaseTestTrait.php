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
        $this->setProperty($connection, 'params', array_merge($parameters, ['dbname' => $this->getDatabaseName()]));
        $this->setProperty($this->em, 'conn', $connection);

        $connection->exec('SET FOREIGN_KEY_CHECKS = 0;');
        $tables = array_column($connection->fetchAll(sprintf(
            "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '%s' AND (AUTO_INCREMENT > 1 OR TABLE_ROWS > 0)",
            $connection->getDatabase()
        )), 'TABLE_NAME');
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
        $this->dm->getConnection()->dropDatabase($this->dm->getConfiguration()->getDefaultDB());
        $this->dm->getSchemaManager()->createCollections();
    }

    /**
     * @param object $entity
     *
     * @throws Exception
     */
    protected function pfe(object $entity): void
    {
        $this->pf($entity, TRUE);
    }

    /**
     * @param object $document
     *
     * @throws Exception
     */
    protected function pfd(object $document): void
    {
        $this->pf($document, FALSE);
    }

    /**
     * --------------------------------------- HELPERS ----------------------------------------------
     */

    /**
     * @param object $object
     * @param bool   $mysql
     *
     * @throws Exception
     */
    private function pf(object $object, bool $mysql = FALSE): void
    {
        if ($mysql && isset($this->em)) {
            $this->em->persist($object);
            $this->em->flush($object);
        } elseif (isset($this->dm)) {
            $this->dm->persist($object);
            $this->dm->flush($object);
        } else {
            throw new LogicException('Database is not set');
        }
    }

    /**
     * @return string
     */
    private function getDatabaseName(): string
    {
        return sprintf('%s%s', $this->em->getConnection()->getDatabase(), getenv('TEST_TOKEN'));
    }

}
