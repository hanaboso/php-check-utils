<?php declare(strict_types=1);

namespace HanabosoCodingStandard\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use ReflectionClass;
use Throwable;

/**
 * Class MagicCallSniff
 *
 * @package HanabosoCodingStandard\Sniffs\Functions
 */
final class MagicCallSniff implements Sniff
{

    public const ERROR_MESSAGE = 'Callback from array is not allowed, use anonymous function instead.';

    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_OPEN_SHORT_ARRAY];
    }

    /**
     * @param File $file
     * @param int  $position
     */
    public function process(File $file, $position): void
    {
        $tokens = $file->getTokens();
        $end    = $tokens[$position]['bracket_closer'];
        $caller = NULL;
        $method = NULL;
        $i      = ++$position;
        for (; $i < $end; $i++) {
            $type = $tokens[$i]['type'];
            if ($type === 'T_VARIABLE' && $tokens[$i]['content'] == '$this') {
                $caller = $this->getSelfFqn($file);

                break;
            }
            if ($type === 'T_SELF'
                && $tokens[$i + 1]['type'] === 'T_DOUBLE_COLON'
                && $tokens[$i + 2]['content'] === 'class'
            ) {
                // self::class
                $caller = $this->getSelfFqn($file);

                break;
            }
            if ($type === 'T_STRING'
                && $tokens[$i + 1]['type'] === 'T_DOUBLE_COLON'
                && $tokens[$i + 2]['content'] === 'class'
            ) {
                $className = $tokens[$i]['content'];
                if ($className === $this->getSelfClassname($file)) {
                    $caller = $this->getSelfFqn($file);
                } else {
                    $caller = $this->findInUsages($file, $className);
                }

                break;
            }
            if ($type === 'T_CONSTANT_ENCAPSED_STRING') {
                // Classname in string
                $caller = $tokens[$i]['content'];

                break;
            }
            if ($type === 'T_NS_SEPARATOR') {
                // Fully Qualified Classname
                $caller = $this->getFqn($file, $position);

                break;
            }
        }
        $method = $tokens[$file->findPrevious(T_CONSTANT_ENCAPSED_STRING, $end)]['content'];
        if (!$caller || !$method) {
            return;
        }
        try {
            /** @phpstan-var class-string $callerTrimmed */
            $callerTrimmed = trim($caller, '\'');
            $class         = new ReflectionClass($callerTrimmed);
        } catch (Throwable $t) {
            // Not a class
            return;
        }
        /** @phpstan-var class-string $methodName */
        $methodName = trim($method, '\'');
        if ($class->hasMethod($methodName)) {
            $file->addError(self::ERROR_MESSAGE, $position, 'Comment');
        }
    }

    /**
     * @param File $file
     * @param int  $position
     *
     * @return string
     */
    private function getFqn(File $file, int $position): string
    {
        $fqn      = '';
        $types    = ['T_STRING', 'T_NS_SEPARATOR'];
        $tokens   = $file->getTokens();
        $position = $file->findNext(T_STRING, --$position) ?: 0;
        $type     = 0;
        while ($tokens[$position]['type'] === $types[$type]) {
            $fqn .= $tokens[$position]['content'];
            $position++;
            $type ^= 1;
        }

        return $fqn;
    }

    /**
     * @param File $file
     *
     * @return string
     */
    private function getSelfFqn(File $file): string
    {
        $ns    = $this->getFqn($file, $file->findNext(T_NAMESPACE, 0) ?: 0);
        $class = $this->getSelfClassname($file);

        return sprintf('%s\\%s', $ns, $class);
    }

    /**
     * @param File $file
     *
     * @return string
     */
    private function getSelfClassname(File $file): string
    {
        $tokens = $file->getTokens();

        return $tokens[($file->findNext(T_CLASS, 0) ?: 0) + 2]['content'];
    }

    /**
     * @param File   $file
     * @param string $content
     *
     * @return string
     */
    private function findInUsages(File $file, string $content): string
    {
        $pos = $file->findNext(T_USE, 0);
        while ($pos) {
            $ns = $this->getFqn($file, $pos);
            if (substr($ns, -strlen($content)) === $content) {
                return $ns;
            }
            $pos = $file->findNext(T_USE, $pos + 1);
        }

        return '';
    }

}
