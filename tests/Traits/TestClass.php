<?php declare(strict_types=1);

namespace Hanaboso\TestsPhpCheckUtils\Traits;

use Hanaboso\PhpCheckUtils\PhpUnit\Traits\ControllerTestTrait;
use Hanaboso\PhpCheckUtils\PhpUnit\Traits\CustomAssertTrait;
use Hanaboso\PhpCheckUtils\PhpUnit\Traits\DatabaseTestTrait;
use Hanaboso\PhpCheckUtils\PhpUnit\Traits\PrivateTrait;

/**
 *
 */
final class TestClass
{

    use ControllerTestTrait;
    use CustomAssertTrait;
    use DatabaseTestTrait;
    use PrivateTrait;

}
