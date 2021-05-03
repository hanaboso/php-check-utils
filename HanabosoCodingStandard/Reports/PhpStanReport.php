<?php declare(strict_types=1);

namespace HanabosoCodingStandard\Reports;

use PHPStan\Analyser\Error;
use PHPStan\Command\AnalysisResult;
use PHPStan\Command\ErrorFormatter\ErrorFormatter;
use PHPStan\Command\Output;

/**
 * Class PhpStanReport
 *
 * @package HanabosoCodingStandard\Reports
 */
final class PhpStanReport implements ErrorFormatter
{

    /**
     * @var string
     */
    private string $root;

    /**
     * PhpStanReport constructor.
     */
    public function __construct()
    {
        $this->root = sprintf('%s/', getcwd());
    }

    /**
     * @param AnalysisResult $analysisResult
     * @param Output         $output
     *
     * @return int
     */
    public function formatErrors(AnalysisResult $analysisResult, Output $output): int
    {
        $style = $output->getStyle();

        /** @var Error[][] $fileErrors */
        $fileErrors = [];

        foreach ($analysisResult->getFileSpecificErrors() as $fileSpecificError) {
            if (!isset($fileErrors[$fileSpecificError->getFile()])) {
                $fileErrors[$fileSpecificError->getFile()] = [];
            }

            $fileErrors[$fileSpecificError->getFile()][] = $fileSpecificError;
        }

        foreach ($fileErrors as $file => $errors) {
            foreach ($errors as $error) {
                echo sprintf(
                    '%s:%s: %s',
                    str_replace($this->root, '', $file),
                    $error->getLine() ?? '1',
                    $error->getMessage(),
                );
                $style->newLine();
            }
        }

        foreach ($analysisResult->getNotFileSpecificErrors() as $notFileSpecificError) {
            echo $notFileSpecificError;
            $style->newLine();
        }

        if ($analysisResult->getTotalErrorsCount() !== 0) {
            echo sprintf('%sErrors: %s%s%s', PHP_EOL, $analysisResult->getTotalErrorsCount(), PHP_EOL, PHP_EOL);
        }

        return $analysisResult->hasErrors() ? 1 : 0;
    }

}
