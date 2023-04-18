<?php declare(strict_types=1);

namespace Hanaboso\PhpCheckUtils\PhpUnit\Extension\FinalMock;

use DG\BypassFinals as DGBypassFinals;
use PHPUnit\Event\Application\Started;
use PHPUnit\Event\Application\StartedSubscriber;

/**
 * Class BypassFinals
 *
 * @package Hanaboso\PhpCheckUtils\PhpUnit\Extension\FinalMock
 */
final class BypassFinals implements StartedSubscriber
{

    /**
     * @param Started $event
     *
     * @return void
     */
    public function notify(Started $event): void
    {
        $event;
        DGBypassFinals::enable();
    }

}
