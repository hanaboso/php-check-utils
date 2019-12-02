<?php declare(strict_types=1);

namespace HanabosoCodingStandard\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;

/**
 * Class TraitSniff
 *
 * @package HanabosoCodingStandard\Sniffs\Commenting
 */
final class TraitSniff extends SniffAbstract
{

    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_TRAIT];
    }

    /**
     * @param File  $file
     * @param mixed $position
     *
     * @return int|void
     */
    public function process(File $file, $position)
    {
        $this->processCommenting($file, $position, self::TYPE_TRAIT);
    }

}
