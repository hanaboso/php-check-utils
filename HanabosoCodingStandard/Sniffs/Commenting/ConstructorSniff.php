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
     * @var string[]
     */
    public $comments = ['{NAME} constructor.'];

    /**
     * @var string
     */
    public $anonymousName = 'Anonymous';

    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_FUNCTION];
    }

    /**
     * @param File  $file
     * @param mixed $position
     */
    public function process(File $file, $position): void
    {
        $tokens = $file->getTokens();

        if ($tokens[$file->findNext(T_STRING, $position)][self::CONTENT] === '__construct') {
            $innerPosition = $file->findPrevious(T_ANON_CLASS, $position);

            if (is_int($innerPosition)) {
                $this->processCommenting($file, $position, self::TYPE_CONSTRUCTOR, $this->anonymousName);
            } else {
                $innerPosition = $file->findPrevious(T_CLASS, $position);

                if (is_int($innerPosition)) {
                    $innerPosition = $file->findNext(T_STRING, $innerPosition);

                    if (is_int($innerPosition)) {
                        $this->processCommenting(
                            $file,
                            $position,
                            self::TYPE_CONSTRUCTOR,
                            $tokens[$innerPosition][self::CONTENT]
                        );
                    }
                }
            }
        }
    }

}
