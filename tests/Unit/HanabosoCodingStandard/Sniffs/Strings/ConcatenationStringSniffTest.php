<?php declare(strict_types=1);

namespace Hanaboso\TestsPhpCheckUtils\Unit\HanabosoCodingStandard\Sniffs\Strings;

use Hanaboso\TestsPhpCheckUtils\KernelTestCaseAbstract;
use HanabosoCodingStandard\Sniffs\Strings\ConcatenationStringSniff;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Class ConcatenationStringSniffTest
 *
 * @package Hanaboso\TestsPhpCheckUtils\Unit\HanabosoCodingStandard\Sniffs\Strings
 */
#[CoversClass(ConcatenationStringSniff::class)]
final class ConcatenationStringSniffTest extends KernelTestCaseAbstract
{

    public function testRegister(): void
    {
        self::assertEquals(['PHPCS_T_STRING_CONCAT'], (new ConcatenationStringSniff())->register());
    }

    public function testProcess(): void
    {
        $testString = 'a' . 'b';
        $item       = [
            'message'  => 'Use sprintf() instead of concatenation with "."',
            'source'   => 'HanabosoCodingStandard.Strings.ConcatenationString',
            'listener' => 'HanabosoCodingStandard\Sniffs\Strings\ConcatenationStringSniff',
            'severity' => 5,
            'fixable'  => FALSE,
        ];

        $res = $this->processSniffTest(__DIR__ . '/ConcatenationStringSniffTest.php', ConcatenationStringSniff::class);

        self::assertEquals('ab', $testString);
        self::assertEquals([25 => [27 => [$item]]], $res->getErrors());
    }

}
