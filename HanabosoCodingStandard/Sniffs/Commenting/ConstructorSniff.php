<?php declare(strict_types=1);

namespace HanabosoCodingStandard\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;

/**
 * Class ConstructorSniff
 *
 * @package HanabosoCodingStandard\Sniffs\Commenting
 */
final class ConstructorSniff extends SniffAbstract
{

    /**
     * @var string
     */
    public string $anonymousName = 'Anonymous';

    /**
     * @var string[]
     */
    protected array $comments = ['{NAME} constructor.'];

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

        if ($tokens[$phpcsFile->findNext(T_STRING, $stackPtr)][self::CONTENT] === '__construct') {
            $innerPosition = $phpcsFile->findPrevious(T_ANON_CLASS, $stackPtr);

            if (is_int($innerPosition)) {
                $this->processCommenting($phpcsFile, $stackPtr, self::TYPE_CONSTRUCTOR, $this->anonymousName);
            } else {
                $innerPosition = $phpcsFile->findPrevious(T_CLASS, $stackPtr);

                if (is_int($innerPosition)) {
                    $innerPosition = $phpcsFile->findNext(T_STRING, $innerPosition);

                    if (is_int($innerPosition)) {
                        $this->processCommenting(
                            $phpcsFile,
                            $stackPtr,
                            self::TYPE_CONSTRUCTOR,
                            $tokens[$innerPosition][self::CONTENT]
                        );
                    }
                }
            }
        }
    }

}
