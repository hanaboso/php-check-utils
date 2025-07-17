<?php declare(strict_types=1);

namespace Hanaboso\TestsPhpCheckUtils\Unit\HanabosoCodingStandard\Sniffs\Commenting;

use Hanaboso\TestsPhpCheckUtils\KernelTestCaseAbstract;
use HanabosoCodingStandard\Sniffs\Commenting\ConstructorSniff;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Class ConstructorSniffTest
 *
 * @package Hanaboso\TestsPhpCheckUtils\Unit\HanabosoCodingStandard\Sniffs\Commenting
 */
#[CoversClass(ConstructorSniff::class)]
final class ConstructorSniffTest extends KernelTestCaseAbstract
{

    public function testRegister(): void
    {
        self::assertEquals([310], (new ConstructorSniff())->register());
    }

    public function testProcess(): void
    {
        $item = [
            'message'  => 'Usage of constructor comment without \'NullClass constructor.\' is not allowed.',
            'source'   => 'HanabosoCodingStandard.Commenting.Constructor.Comment',
            'listener' => 'HanabosoCodingStandard\Sniffs\Commenting\ConstructorSniff',
            'severity' => 5,
            'fixable'  => FALSE,
        ];

        $res = $this->processSniffTest(__DIR__ . '/NullClass.php', ConstructorSniff::class);

        self::assertEquals([7 => [21 => [$item]]], $res->getErrors());
    }

}
