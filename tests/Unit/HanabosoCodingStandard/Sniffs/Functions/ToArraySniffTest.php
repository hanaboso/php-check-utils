<?php declare(strict_types=1);

namespace Hanaboso\TestsPhpCheckUtils\Unit\HanabosoCodingStandard\Sniffs\Functions;

use Hanaboso\TestsPhpCheckUtils\KernelTestCaseAbstract;
use HanabosoCodingStandard\Sniffs\Functions\ToArraySniff;

/**
 * Class ToArraySniffTest
 *
 * @package Hanaboso\TestsPhpCheckUtils\Unit\HanabosoCodingStandard\Sniffs\Functions
 */
final class ToArraySniffTest extends KernelTestCaseAbstract
{

    /**
     *
     */
    public function toArray(): array
    {
        return [];
    }

    /**
     *
     */
    public function testToArrayFirst(): void
    {
        $err = [
            'message'  => ToArraySniff::ERROR_MESSAGE,
            'source'   => 'HanabosoCodingStandard.Functions.ToArray.Comment',
            'listener' => ToArraySniff::class,
            'severity' => 5,
            'fixable'  => FALSE,
        ];

        $res = $this->processSniffTest(__DIR__ . '/ToArraySniffTest.php', ToArraySniff::class);

        self::assertEquals(
            [
                19 => [
                    12 => [$err],
                ],
            ],
            $res->getErrors()
        );
    }

    /**
     *
     */
    public function testToArrayFirstInterface(): void
    {
        $err = [
            'message'  => ToArraySniff::ERROR_MESSAGE,
            'source'   => 'HanabosoCodingStandard.Functions.ToArray.Comment',
            'listener' => ToArraySniff::class,
            'severity' => 5,
            'fixable'  => FALSE,
        ];
        $res = $this->processSniffTest(__DIR__ . '/ToArrayInterface.php', ToArraySniff::class);
        self::assertEquals(
            [
                16 => [
                    12 => [$err],
                ],
            ],
            $res->getErrors()
        );
    }
}
