<?php declare(strict_types=1);

namespace Hanaboso\PhpCheckUtils\PhpUnit\Traits;

use PHPUnit\Runner\ErrorHandler;

/**
 * Trait RestoreErrorHandlersTrait
 *
 * @package Hanaboso\PhpCheckUtils\PhpUnit\Traits
 */
trait RestoreErrorHandlersTrait
{

    /**
     * @return void
     */
    protected function restoreExceptionHandler(): void
    {
        while (TRUE) {
            $previousHandler = set_exception_handler(static fn() => NULL);
            restore_exception_handler();
            if ($previousHandler === NULL) {
                break;
            }
            restore_exception_handler();
        }
    }

    /**
     * @return void
     */
    protected function restoreErrorHandler(): void
    {
        while (TRUE) {
            $previousHandler = set_error_handler(NULL);
            restore_error_handler();
            // @phpstan-ignore-next-line
            $isPhpUnitErrorHandler = ($previousHandler instanceof ErrorHandler);
            if ($previousHandler === NULL || $isPhpUnitErrorHandler) {
                break;
            }
            restore_error_handler();
        }
    }

}
