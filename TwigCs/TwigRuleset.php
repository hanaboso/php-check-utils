<?php declare(strict_types=1);

namespace Hanaboso\PhpCheckUtils\TwigCs;

use FriendsOfTwig\Twigcs\RegEngine\RulesetBuilder;
use FriendsOfTwig\Twigcs\RegEngine\RulesetConfigurator;
use FriendsOfTwig\Twigcs\Rule\LowerCaseVariable;
use FriendsOfTwig\Twigcs\Rule\RegEngineRule;
use FriendsOfTwig\Twigcs\Rule\RuleInterface;
use FriendsOfTwig\Twigcs\Rule\TrailingSpace;
use FriendsOfTwig\Twigcs\Rule\UnusedMacro;
use FriendsOfTwig\Twigcs\Rule\UnusedVariable;
use FriendsOfTwig\Twigcs\Ruleset\RulesetInterface;
use FriendsOfTwig\Twigcs\Validator\Violation;

/**
 * Class TwigRuleset
 *
 * @package Hanaboso\PhpCheckUtils\TwigCs
 */
class TwigRuleset implements RulesetInterface
{

    /**
     * TwigRuleset constructor.
     *
     * @param int $twigMajorVersion
     */
    public function __construct(protected int $twigMajorVersion)
    {
    }

    /**
     * @return RuleInterface[]
     */
    public function getRules(): array
    {
        $configurator = new RulesetConfigurator();
        $configurator->setTwigMajorVersion($this->twigMajorVersion);
        $builder = new RulesetBuilder($configurator);

        return [
            new RegEngineRule(Violation::SEVERITY_ERROR, $builder->build()),
            new TrailingSpace(Violation::SEVERITY_ERROR),
            new LowerCaseVariable(Violation::SEVERITY_WARNING),
            new UnusedMacro(Violation::SEVERITY_WARNING),
            new UnusedVariable(Violation::SEVERITY_WARNING),
        ];
    }

}
