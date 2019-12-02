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
     * @param File  $file
     * @param mixed $position
     *
     * @return int|void
     */
    public function process(File $file, $position)
    {
        $this->processCommenting($file, $position, self::TYPE_INTERFACE);
    }

}
