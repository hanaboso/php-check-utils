<?php declare(strict_types=1);

namespace Hanaboso\PhpCheckUtils\PhpUnit\Traits;

/**
 * Trait CustomAssertTrait
 *
 * @package Hanaboso\PhpCheckUtils\PhpUnit\Traits
 */
trait CustomAssertTrait
{

    /**
     *
     */
    public static function assertFake(): void
    {
        self::assertEmpty([]);
    }

}
