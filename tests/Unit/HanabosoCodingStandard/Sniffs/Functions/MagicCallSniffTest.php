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
     * @var string
     */
    private string $foo = 'bar';

    /**
     *
     */
    public function errors(): void
    {
        // Negative
        $arr = ['foo', 'bar'];
        $arr = ['this', 'bar'];
        $arr = [$this->foo, 'bar'];
        $arr = [$this->foo, $this->foo];
        $arr = [$this => 'foo', 'bar'];
        $arr = [$this, 'foobar'];

        // Positive
        $arr = [$this, 'compare'];
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
        self::assertCount(8, $res->getErrors());
        self::assertEquals(
            [
                36 => [17 => [$item]],
                37 => [22 => [$item]],
                38 => [22 => [$item]],
                39 => [22 => [$item]],
                40 => [22 => [$item]],
                43 => [14 => [$item]],
                47 => [14 => [$item]],
                51 => [14 => [$item]],
            ],
            $res->getErrors()
        );
    }

}