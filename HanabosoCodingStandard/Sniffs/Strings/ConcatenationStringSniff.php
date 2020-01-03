<?php declare(strict_types=1);

namespace HanabosoCodingStandard\Sniffs\Strings;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Class ConcatenationStringSniff
 *
 * @package HanabosoCodingStandard\Sniffs\Strings
 */
final class ConcatenationStringSniff implements Sniff
{

    /**
     * @var string[]
     */
    public array $allowedConstants = ['__DIR__'];

    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_STRING_CONCAT];
    }

    /**
     * @param File $file
     * @param int  $position
     */
    public function process(File $file, $position): void
    {
        if (!in_array($file->getTokens()[$position - 2]['content'], $this->allowedConstants, TRUE)) {
            $file->addError(
                'Use sprintf() instead of concatenation with "."',
                $position,
                'HanabosoCodingStandard.Strings.ConcatenationString'
            );
        }
    }

}
