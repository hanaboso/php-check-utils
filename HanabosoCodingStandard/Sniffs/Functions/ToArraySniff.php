<?php declare(strict_types=1);

namespace HanabosoCodingStandard\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Class ToArraySniff
 *
 * @package HanabosoCodingStandard\Sniffs\Functions
 */
final class ToArraySniff implements Sniff
{

    public const string ERROR_MESSAGE = 'Public method toArray() should be the last one.';

    private const string TO_ARRAY = 'toArray';

    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_FUNCTION];
    }

    /**
     * @param File  $phpcsFile
     * @param mixed $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        if (!$this->isToArray($tokens, $stackPtr)) {
            return;
        }

        $pos = (int) (($tokens[$stackPtr]['scope_closer'] ?? $stackPtr) + 1);

        while ($pos < $phpcsFile->numTokens) {
            $token = $tokens[$pos];
            $code  = $token['code'];
            // Class and other possible scope ends
            if ($code === T_CLOSE_CURLY_BRACKET) {
                return;
            }

            if ($code === T_FUNCTION) {
                if ($this->isPublicNonStatic($tokens, $pos)) {
                    $phpcsFile->addError(self::ERROR_MESSAGE, $stackPtr, 'Comment');
                }
            }

            $pos++;
        }
    }

    /**
     * @param mixed[] $tokens
     * @param int     $position
     *
     * @return bool
     */
    private function isToArray(array $tokens, int $position): bool
    {
        return $this->isPublicNonStatic($tokens, $position)
            && $this->getFunctionName($tokens, $position) === self::TO_ARRAY;
    }

    /**
     * @param mixed[] $tokens
     * @param int     $position
     *
     * @return bool
     */
    private function isPublicNonStatic(array $tokens, int $position): bool
    {
        $pos = $position - 1;

        for (; $pos > 0; $pos--) {
            $code = $tokens[$pos]['code'];
            if ($code === T_WHITESPACE) {
                continue;
            }

            return $code === T_PUBLIC;
        }

        return FALSE;
    }

    /**
     * @param mixed[] $tokens
     * @param int     $position
     *
     * @return string
     */
    private function getFunctionName(array $tokens, int $position): string
    {
        $pos   = $position + 1;
        $limit = $tokens[$position]['parenthesis_opener'];

        for (; $pos < $limit; $pos++) {
            $token = $tokens[$pos];
            if ($token['code'] === T_WHITESPACE) {
                continue;
            }

            if ($token['code'] != T_STRING) {
                return '';
            }

            return $token['content'];
        }

        return '';
    }

}
