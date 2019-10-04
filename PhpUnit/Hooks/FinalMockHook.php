<?php declare(strict_types=1);

namespace Hanaboso\PhpCheckUtils\PhpUnit\Hooks;

use DG\BypassFinals;
use PHPUnit\Runner\BeforeTestHook;

/**
 * Class FinalMockHook
 *
 * @package Hanaboso\PhpCheckUtils\PhpUnit\Hooks
 */
final class FinalMockHook implements BeforeTestHook
{

    /**
     * @param string $test
     */
    public function executeBeforeTest(string $test): void
    {
        $test;
        BypassFinals::enable();
    }

}
