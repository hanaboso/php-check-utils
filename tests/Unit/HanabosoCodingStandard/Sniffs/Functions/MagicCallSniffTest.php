<?php declare(strict_types=1);

namespace Hanaboso\TestsPhpCheckUtils\Unit\HanabosoCodingStandard\Sniffs\Functions;

use Hanaboso\TestsPhpCheckUtils\KernelTestCaseAbstract;
use HanabosoCodingStandard\Sniffs\Functions\MagicCallSniff;
use PHP_CodeSniffer\Config;

/**
 * Class MagicCallSniffTest
 *
 * @package Hanaboso\TestsPhpCheckUtils\Unit\HanabosoCodingStandard\Sniffs\Functions
 */
final class MagicCallSniffTest extends KernelTestCaseAbstract
{

    /**
     *
     */
    public function errors(): void
    {
        usort($arr, [$this, 'compare']);
        usort($arr, [Config::class, 'printConfigData']);
        usort($arr, [MagicCallSniffTest::class, 'compare']);
        usort($arr, [self::class, 'compare']);
        usort(
            $arr,
            ['Hanaboso\TestsPhpCheckUtils\Unit\HanabosoCodingStandard\Sniffs\Functions\MagicCallSniffTest', 'compare']
        );
        usort(
            $arr,
            ['\Hanaboso\TestsPhpCheckUtils\Unit\HanabosoCodingStandard\Sniffs\Functions\MagicCallSniffTest', 'compare']
        );
        usort(
            $arr,
            [
                \Hanaboso\TestsPhpCheckUtils\Unit\HanabosoCodingStandard\Sniffs\Functions\MagicCallSniffTest::class,
                'compare',
            ]
        );
    }

    /**
     * @return bool
     */
    public function compare(): bool
    {
        return TRUE;
    }

    /**
     *
     */
    public function testMagicCall(): void
    {
        $item = [
            'message'  => MagicCallSniff::ERROR_MESSAGE,
            'source'   => 'HanabosoCodingStandard.Functions.MagicCall.Comment',
            'listener' => MagicCallSniff::class,
            'severity' => 5,
            'fixable'  => FALSE,
        ];

        $res = $this->processSniffTest(__DIR__ . '/MagicCallSniffTest.php', MagicCallSniff::class);
        self::assertCount(7, $res->getErrors());
        self::assertEquals(
            [
                22 => [22 => [$item]],
                23 => [22 => [$item]],
                24 => [22 => [$item]],
                25 => [22 => [$item]],
                28 => [14 => [$item]],
                32 => [14 => [$item]],
                36 => [14 => [$item]],
            ],
            $res->getErrors()
        );
    }

}