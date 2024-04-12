<?php declare(strict_types=1);

namespace Hanaboso\TestsPhpCheckUtils\Unit\HanabosoCodingStandard\Sniffs\Commenting;

use Hanaboso\TestsPhpCheckUtils\KernelTestCaseAbstract;
use HanabosoCodingStandard\Sniffs\Commenting\InterfaceSniff;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Class InterfaceSniffTest
 *
 * @package Hanaboso\TestsPhpCheckUtils\Unit\HanabosoCodingStandard\Sniffs\Commenting
 */
#[CoversClass(InterfaceSniff::class)]
final class InterfaceSniffTest extends KernelTestCaseAbstract implements NullInterface
{

    public function testRegister(): void
    {
        self::assertEquals([371], (new InterfaceSniff())->register());
    }

    public function testProcess(): void
    {
        $err1 = [
            'message'  => 'Usage of interface comment without \'Interface NullInterface\' is not allowed.',
            'source'   => 'HanabosoCodingStandard.Commenting.Interface.Comment',
            'listener' => 'HanabosoCodingStandard\Sniffs\Commenting\InterfaceSniff',
            'severity' => 5,
            'fixable'  => FALSE,
        ];

        $err2 = [
            'message'  => 'Usage of interface comment without \'@package Hanaboso\TestsPhpCheckUtils\Unit\HanabosoCodingStandard\Sniffs\Commenting\' is not allowed.',
            'source'   => 'HanabosoCodingStandard.Commenting.Interface.Comment',
            'listener' => 'HanabosoCodingStandard\Sniffs\Commenting\InterfaceSniff',
            'severity' => 5,
            'fixable'  => FALSE,
        ];

        $res = $this->processSniffTest(__DIR__ . '/NullInterface.php', InterfaceSniff::class);

        self::assertEquals([5 => [11 => [$err1, $err2]]], $res->getErrors());
    }

}
