<?php declare(strict_types=1);

namespace Hanaboso\PhpCheckUtils\PhpUnit\Extension\FinalMock;

use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * Class FinalMockExtension
 *
 * @package Hanaboso\PhpCheckUtils\PhpUnit\Extension\FinalMock
 */
final class FinalMockExtension implements Extension
{

    /**
     * @param Configuration       $configuration
     * @param Facade              $facade
     * @param ParameterCollection $parameters
     *
     * @return void
     */
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $configuration;
        $parameters;
        $facade->registerSubscriber(new BypassFinals());
    }

}
