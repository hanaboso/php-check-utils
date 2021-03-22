<?php declare(strict_types=1);

namespace HanabosoCodingStandard\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;

/**
 * Class InterfaceSniff
 *
 * @package HanabosoCodingStandard\Sniffs\Commenting
 */
final class InterfaceSniff extends SniffAbstract
{

    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_INTERFACE];
    }

    /**
     * @param File  $phpcsFile
     * @param mixed $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $this->processCommenting($phpcsFile, $stackPtr, self::TYPE_INTERFACE);
    }

}
