<?php declare(strict_types=1);

namespace Hanaboso\TestsPhpCheckUtils;

use Exception;
use Hanaboso\PhpCheckUtils\PhpUnit\Traits\CustomAssertTrait;
use Hanaboso\PhpCheckUtils\PhpUnit\Traits\PrivateTrait;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\LocalFile;
use PHP_CodeSniffer\Runner;
use PHPUnit\Framework\TestCase;

/**
 * Class KernelTestCaseAbstract
 *
 * @package Hanaboso\TestsPhpCheckUtils
 */
abstract class KernelTestCaseAbstract extends TestCase
{

    use PrivateTrait;
    use CustomAssertTrait;

    /**
     * @var Runner
     */
    protected Runner $runner;

    /**
     * KernelTestCaseAbstract constructor.
     *
     * @param string|null $name
     * @param array       $data
     * @param string      $dataName
     *
     * @throws Exception
     */
    public function __construct(?string $name = NULL, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        include_once __DIR__ . '/../vendor/autoload.php';
        include_once __DIR__ . '/../vendor/squizlabs/php_codesniffer/autoload.php';
        $this->runner         = new Runner();
        $this->runner->config = new Config([''], FALSE);
        $this->runner->init();
    }

    /**
     * @param string $file
     * @param string $sniff
     *
     * @return LocalFile
     */
    protected function processSniffTest(string $file, string $sniff): LocalFile
    {
        $this->runner->ruleset->sniffs = [$sniff => new $sniff()];
        $this->runner->ruleset->populateTokenListeners();
        $file = new LocalFile($file, $this->runner->ruleset, $this->runner->config);
        $file->process();

        return $file;
    }

}
