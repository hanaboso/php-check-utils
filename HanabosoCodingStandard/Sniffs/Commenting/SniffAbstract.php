<?php declare(strict_types=1);

namespace HanabosoCodingStandard\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Class SniffAbstract
 *
 * @package HanabosoCodingStandard\Sniffs\Commenting
 */
abstract class SniffAbstract implements Sniff
{

    protected const string TYPE_CLASS       = 'Class';
    protected const string TYPE_INTERFACE   = 'Interface';
    protected const string TYPE_TRAIT       = 'Trait';
    protected const string TYPE_CONSTRUCTOR = 'Constructor';

    protected const string CODE    = 'code';
    protected const string CONTENT = 'content';

    private const string PATTERN = '#\r\n|\r|\n#';
    private const array REPLACE  = [
        '{NAMESPACE}',
        '{NAME}',
        '{TYPE}',
    ];

    /**
     * @var string[]
     */
    protected array $comments = [
        '{TYPE} {NAME}',
        '@package {NAMESPACE}',
    ];

    /**
     * @param File $file
     * @param int  $position
     *
     * @return string
     */
    protected function getNamespaceName(File $file, int $position): string
    {
        $result     = 'Unknown';
        $tokens     = $file->getTokens();
        $namespaces = [];

        $position = $file->findPrevious(T_NAMESPACE, $position);

        if (is_int($position)) {
            $position = $file->findNext(T_STRING, $position);

            if (is_int($position)) {
                $closePosition = $file->findEndOfStatement($position);

                for (; $position < $closePosition; $position++) {
                    $namespaces[] = $tokens[$position][self::CONTENT];
                }

                $result = implode('', $namespaces);
            }
        }

        return $result;
    }

    /**
     * @param File $file
     * @param int  $position
     *
     * @return string[]
     */
    protected function getDocumentComment(File $file, int $position): array
    {
        $result        = [];
        $tokens        = $file->getTokens();
        $startPosition = $file->findPrevious(T_DOC_COMMENT_OPEN_TAG, $position);

        if (is_int($startPosition)) {
            $iterator      = 0;
            $closePosition = $file->findNext(T_DOC_COMMENT_CLOSE_TAG, $startPosition + 1);

            for (; $startPosition < $closePosition; $startPosition++) {
                $token                  = $tokens[$startPosition];
                $isWhiteSpace           = $token[self::CODE] === T_DOC_COMMENT_WHITESPACE;
                $isWhiteSpaceNewLine    = preg_match(self::PATTERN, $token[self::CONTENT]) === 1;
                $isStringOrTagCharacter = in_array($token[self::CODE], [T_DOC_COMMENT_STRING, T_DOC_COMMENT_TAG], TRUE);

                if ($isStringOrTagCharacter || $isWhiteSpace && !$isWhiteSpaceNewLine) {
                    $line              = sprintf('%s%s', $result[$iterator] ?? '', $token[self::CONTENT]);
                    $result[$iterator] = preg_replace('!\s+!', ' ', $line);
                }

                if ($isWhiteSpace && $isWhiteSpaceNewLine) {
                    $iterator++;
                }
            }
        }

        return array_filter(
            array_map(static fn($item) => trim($item ?? ''), $result),
            static fn(string $item): bool => strlen($item) > 0,
        );
    }

    /**
     * @param File        $file
     * @param int         $position
     * @param string      $type
     * @param string|NULL $customName
     */
    protected function processCommenting(File $file, int $position, string $type, ?string $customName = NULL): void
    {
        $position = $file->findNext(T_STRING, $position);

        if (is_int($position)) {
            $comments = $this->getDocumentComment($file, $position);

            foreach ($this->comments as $comment) {
                $comment = $this->replacePlaceholders($file, $position, $type, $comment, $customName);

                if (!in_array($comment, $comments, TRUE)) {
                    $file->addError(
                        sprintf("Usage of %s comment without '%s' is not allowed.", lcfirst($type), $comment),
                        $position,
                        'Comment',
                    );
                }
            }
        }
    }

    /**
     * @param File        $file
     * @param int         $position
     * @param string      $type
     * @param string      $string
     * @param string|NULL $customName
     *
     * @return string
     */
    protected function replacePlaceholders(
        File $file,
        int $position,
        string $type,
        string $string,
        ?string $customName = NULL,
    ): string
    {
        return str_replace(
            self::REPLACE,
            [
                $this->getNamespaceName($file, $position),
                $customName ?: $file->getTokens()[$position][self::CONTENT],
                $type,
            ],
            $string,
        );
    }

}
