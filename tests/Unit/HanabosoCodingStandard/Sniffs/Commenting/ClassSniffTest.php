<?php declare(strict_types=1);

namespace Hanaboso\TestsPhpCheckUtils\Unit\HanabosoCodingStandard\Sniffs\Commenting;

use Hanaboso\TestsPhpCheckUtils\KernelTestCaseAbstract;
use HanabosoCodingStandard\Sniffs\Commenting\ClassSniff;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ClassSniff::class)]
final class ClassSniffTest extends KernelTestCaseAbstract
{

    public function testRegister(): void
    {
        self::assertEquals([333], (new ClassSniff())->register());
    }

    public function testProcess(): void
    {
        $err1 = [
            'message'  => 'Usage of class comment without \'Class ClassSniffTest\' is not allowed.',
            'source'   => 'HanabosoCodingStandard.Commenting.Class.Comment',
            'listener' => 'HanabosoCodingStandard\Sniffs\Commenting\ClassSniff',
            'severity' => 5,
            'fixable'  => FALSE,
        ];

        $err2 = [
            'message'  => 'Usage of class comment without \'@package Hanaboso\TestsPhpCheckUtils\Unit\HanabosoCodingStandard\Sniffs\Commenting\' is not allowed.',
            'source'   => 'HanabosoCodingStandard.Commenting.Class.Comment',
            'listener' => 'HanabosoCodingStandard\Sniffs\Commenting\ClassSniff',
            'severity' => 5,
            'fixable'  => FALSE,
        ];

        $res = $this->processSniffTest(__DIR__ . '/ClassSniffTest.php', ClassSniff::class);

        self::assertEquals([10 => [8 => [$err1, $err2]]], $res->getErrors());
    }

}
