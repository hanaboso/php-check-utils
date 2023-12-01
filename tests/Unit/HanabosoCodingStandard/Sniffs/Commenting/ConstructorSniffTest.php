<?php declare(strict_types=1);

namespace Hanaboso\TestsPhpCheckUtils\Unit\HanabosoCodingStandard\Sniffs\Commenting;

use Hanaboso\TestsPhpCheckUtils\KernelTestCaseAbstract;
use HanabosoCodingStandard\Sniffs\Commenting\ConstructorSniff;

/**
 * Class ConstructorSniffTest
 *
 * @package Hanaboso\TestsPhpCheckUtils\Unit\HanabosoCodingStandard\Sniffs\Commenting
 */
final class ConstructorSniffTest extends KernelTestCaseAbstract
{

    public function __construct(?string $name = NULL, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    /**
     * @covers \HanabosoCodingStandard\Sniffs\Commenting\ConstructorSniff::register
     */
    public function testRegister(): void
    {
        self::assertEquals([347], (new ConstructorSniff())->register());
    }

    /**
     * @covers \HanabosoCodingStandard\Sniffs\Commenting\ConstructorSniff::process
     */
    public function testProcess(): void
    {
        $item = [
            'message'  => 'Usage of constructor comment without \'ConstructorSniffTest constructor.\' is not allowed.',
            'source'   => 'HanabosoCodingStandard.Commenting.Constructor.Comment',
            'listener' => 'HanabosoCodingStandard\Sniffs\Commenting\ConstructorSniff',
            'severity' => 5,
            'fixable'  => FALSE,
        ];

        $res = $this->processSniffTest(__DIR__ . '/ConstructorSniffTest.php', ConstructorSniff::class);

        self::assertEquals([16 => [21 => [$item]]], $res->getErrors());
    }

}
