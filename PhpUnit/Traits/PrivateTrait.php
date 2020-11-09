<?php declare(strict_types=1);

namespace Hanaboso\PhpCheckUtils\PhpUnit\Traits;

use LogicException;
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
     * @param mixed $object
     * @param mixed $propertyName
     * @param mixed $value
     *
     * @throws ReflectionException
     */
    protected function setProperty($object, $propertyName, $value): void
    {
        $reflection = new ReflectionObject($object);

        do {
            if ($reflection->hasProperty($propertyName)) {
                $property = $reflection->getProperty($propertyName);
                $property->setAccessible(TRUE);
                $property->setValue($object, $value);

                return;
            }

            $reflection = $reflection->getParentClass();
        } while ($reflection);

        throw new LogicException(sprintf("Property '%s' Not Found!", $propertyName));
    }

    /**
     * @param object $object
     * @param string $propertyName
     *
     * @return mixed
     * @throws ReflectionException
     */
    protected function getProperty(object $object, string $propertyName)
    {
        $reflection = new ReflectionObject($object);

        do {
            if ($reflection->hasProperty($propertyName)) {
                $property = $reflection->getProperty($propertyName);
                $property->setAccessible(TRUE);

                return $property->getValue($object);
            }

            $reflection = $reflection->getParentClass();
        } while ($reflection);

        throw new LogicException(sprintf("Property '%s' Not Found!", $propertyName));
    }

    /**
     * @param object $object
     * @param string $instance
     *
     * @return array
     */
    protected function getPropertyByInstance(object $object, string $instance): array
    {
        if (get_class($object) === $instance) {
            return [NULL, $object];
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

        return [NULL, NULL];
    }

    /**
     * @param object $object
     * @param string $methodName
     * @param array  $parameters
     *
     * @return mixed
     * @throws ReflectionException
     */
    protected function invokeMethod(object $object, string $methodName, array $parameters = [])
    {
        $reflection = new ReflectionObject($object);

        do {
            if ($reflection->hasMethod($methodName)) {
                $method = $reflection->getMethod($methodName);
                $method->setAccessible(TRUE);

                return $method->invokeArgs($object, $parameters);
            }

            $reflection = $reflection->getParentClass();
        } while ($reflection);

        throw new LogicException(sprintf("Method '%s' Not Found!", $methodName));
    }

}
