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

    public const ERROR_MESSAGE = 'Public method toArray() should be the last one.';

    private const TO_ARRAY = 'toArray';

    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_FUNCTION];
    }

    /**
     * @param File $file
     * @param int  $position
     */
    public function process(File $file, $position): void
    {
        $tokens = $file->getTokens();

        if (!$this->isToArray($tokens, $position)) {
            return;
        }

        $pos = (int) (($tokens[$position]['scope_closer'] ?? $position) + 1);

        while ($pos < $file->numTokens) {
            $token = $tokens[$pos];
            $code  = $token['code'];
            // Class and other possible scope ends
            if ($code === 'PHPCS_T_CLOSE_CURLY_BRACKET') {
                return;
            }

            if ($code === T_FUNCTION) {
                if ($this->isPublicNonStatic($tokens, $pos)) {
                    $file->addError(self::ERROR_MESSAGE, $position, 'Comment');
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
