<?php declare(strict_types=1);

namespace Hanaboso\PhpCheckUtils\PhpUnit\Traits;

use ReflectionClass;
use ReflectionException;
use ReflectionObject;

/**
 * Trait PrivateTrait
 *
 * @package Hanaboso\PhpCheckUtils\PhpUnit\Traits
 */
trait PrivateTrait
{

    /**
     * @param mixed  $object
     * @param string $propertyName
     * @param mixed  $value
     *
     * @throws ReflectionException
     */
    protected function setProperty($object, $propertyName, $value): void
    {
        $reflection = new ReflectionObject($object);
        $property   = $reflection->getProperty($propertyName);
        $property->setAccessible(TRUE);
        $property->setValue($object, $value);
    }

    /**
     * @param mixed  $object
     * @param string $propertyName
     *
     * @return mixed
     * @throws ReflectionException
     */
    protected function getProperty($object, $propertyName)
    {
        $reflection = new ReflectionObject($object);
        $property   = $reflection->getProperty($propertyName);
        $property->setAccessible(TRUE);

        return $property->getValue($object);
    }

    /**
     * @param mixed  $object
     * @param string $instance
     *
     * @return mixed
     */
    protected function getPropertyByInstance($object, string $instance)
    {
        if ($object instanceof $instance) {
            return $object;
        }

        $reflection = new ReflectionObject($object);
        $properties = $reflection->getProperties();

        foreach ($properties as $property) {
            $property->setAccessible(TRUE);
            $obj = $property->getValue($object);
            if ($obj instanceof $instance) {

                return [$property->getName(), $property->getValue($object)];
            }
        }

        return NULL;
    }

    /**
     * @param mixed  $object
     * @param string $methodName
     * @param array  $parameters
     *
     * @return mixed
     * @throws ReflectionException
     */
    protected function invokeMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass(get_class($object));
        $method     = $reflection->getMethod($methodName);
        $method->setAccessible(TRUE);

        return $method->invokeArgs($object, $parameters);
    }

}
