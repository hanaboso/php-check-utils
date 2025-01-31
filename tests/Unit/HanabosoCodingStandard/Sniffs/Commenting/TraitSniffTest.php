<?php declare(strict_types=1);

namespace Hanaboso\TestsPhpCheckUtils\Unit\HanabosoCodingStandard\Sniffs\Commenting;

use Hanaboso\TestsPhpCheckUtils\KernelTestCaseAbstract;
use HanabosoCodingStandard\Sniffs\Commenting\TraitSniff;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Class TraitSniffTest
 *
 * @package Hanaboso\TestsPhpCheckUtils\Unit\HanabosoCodingStandard\Sniffs\Commenting
 */
#[CoversClass(TraitSniff::class)]
final class TraitSniffTest extends KernelTestCaseAbstract
{

    public function testRegister(): void
    {
        self::assertEquals([337], (new TraitSniff())->register());
    }

    public function testProcess(): void
    {
        $err1 = [
            'message'  => 'Usage of trait comment without \'Trait NullTrait\' is not allowed.',
            'source'   => 'HanabosoCodingStandard.Commenting.Trait.Comment',
            'listener' => 'HanabosoCodingStandard\Sniffs\Commenting\TraitSniff',
            'severity' => 5,
            'fixable'  => FALSE,
        ];

        $err2 = [
            'message'  => 'Usage of trait comment without \'@package Hanaboso\TestsPhpCheckUtils\Unit\HanabosoCodingStandard\Sniffs\Commenting\' is not allowed.',
            'source'   => 'HanabosoCodingStandard.Commenting.Trait.Comment',
            'listener' => 'HanabosoCodingStandard\Sniffs\Commenting\TraitSniff',
            'severity' => 5,
            'fixable'  => FALSE,
        ];

        $res = $this->processSniffTest(__DIR__ . '/NullTrait.php', TraitSniff::class);

        self::assertEquals([5 => [7 => [$err1, $err2]]], $res->getErrors());
    }

}
