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
     * @param File  $phpcsFile
     * @param mixed $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        if (!in_array($phpcsFile->getTokens()[$stackPtr - 2]['content'], $this->allowedConstants, TRUE)) {
            $phpcsFile->addError(
                'Use sprintf() instead of concatenation with "."',
                $stackPtr,
                'HanabosoCodingStandard.Strings.ConcatenationString',
            );
        }
    }

}
