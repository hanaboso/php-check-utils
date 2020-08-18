<?php declare(strict_types=1);

namespace Hanaboso\TestsPhpCheckUtils\Unit\HanabosoCodingStandard\Sniffs\Functions;

/**
 * Interface ToArrayInterface
 *
 * @package Hanaboso\TestsPhpCheckUtils\Unit\HanabosoCodingStandard\Sniffs\Functions
 */
interface ToArrayInterface
{

    /**
     * @return mixed[]
     */
    public function toArray(): array;

    /**
     * @return mixed[]
     */
    public function test(): array;
}
