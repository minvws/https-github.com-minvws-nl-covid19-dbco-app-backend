<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use InvalidArgumentException;
use ptlis\DiffParser\Changeset;
use ptlis\DiffParser\File as DiffFile;
use ptlis\DiffParser\Hunk;
use ptlis\DiffParser\Parser;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Node\File;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

use function array_key_exists;
use function file_get_contents;
use function is_string;
use function join;

/**
 * @codeCoverageIgnore
 */
class CheckCoverage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkcoverage:diff {coverageFile} {coveragePathPrefix} {gitDiff}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create git diff and check all updated / new lines against the collected coverage';

    public function handle(): int
    {
        $diffOutput = $this->getDiff($this->getStringArgument('gitDiff'));
        $fileUncoveredLinesMap = $this->createFileUncoveredLinesMap(
            $this->getStringArgument('coverageFile'),
            $this->getStringArgument('coveragePathPrefix'),
        );

        $parser = new Parser();
        $changeset = $parser->parse($diffOutput, Parser::VCS_GIT);

        $missingCoverage = $this->getMissingCoverage($changeset, $fileUncoveredLinesMap);

        foreach ($missingCoverage as $filename => $linesWithoutCoverage) {
            $lines = join(", ", $linesWithoutCoverage);
            echo "Uncovered file: $filename, lines: $lines.\n";
        }
        return empty($missingCoverage) ? 0 : 1;
    }

    private function createFileUncoveredLinesMap(string $coverageFile, string $pathPrefix): array
    {
        /** @var CodeCoverage $coverageData */
        $coverageData = include $coverageFile;
        $coverageData->includeUncoveredFiles();
        $report = $coverageData->getReport();
        $fileUncoveredLinesMap = [];
        foreach ($report as $value) {
            if ($value instanceof File) {
                $filename = $pathPrefix . $value->pathAsString();
                $fileUncoveredLinesMap[$filename] = $this->getUncoveredLines($value);
            }
        }
        return $fileUncoveredLinesMap;
    }

    private function getDiff(string $gitDiffPath): string
    {
        $contents = file_get_contents($gitDiffPath);
        if ($contents === false) {
            throw new FileNotFoundException($gitDiffPath);
        }
        return $contents;
    }

    private function getUncoveredLines(File $value): array
    {
        $uncoveredLines = [];
        foreach ($value->lineCoverageData() as $lineNumber => $coverageInfo) {
            if (empty($coverageInfo)) {
                $uncoveredLines[$lineNumber] = true;
            }
        }
        return $uncoveredLines;
    }

    private function getMissingCoverage(Changeset $changeset, array $fileUncoveredLinesMap): array
    {
        $missingCoverage = [];

        foreach ($changeset->files as $changedFile) {
            if ($changedFile->operation === DiffFile::DELETED) {
                continue;
            }
            $uncoveredLines = $fileUncoveredLinesMap[$changedFile->newFilename] ?? [];
            foreach ($changedFile->hunks as $hunk) {
                $uncoveredLinesInDiff = $this->getUncoveredLinesInDiff($hunk, $uncoveredLines);
                if (!empty($uncoveredLinesInDiff)) {
                    $missingCoverage[$changedFile->newFilename] = $uncoveredLinesInDiff;
                }
            }
        }
        return $missingCoverage;
    }

    private function getUncoveredLinesInDiff(Hunk $hunk, array $uncoveredLines): array
    {
        $uncoveredLinesInDiff = [];
        for ($lineNumber = $hunk->newStart; $lineNumber < $hunk->newStart + $hunk->newCount; $lineNumber++) {
            if (array_key_exists($lineNumber, $uncoveredLines)) {
                $uncoveredLinesInDiff[] = $lineNumber;
            }
        }
        return $uncoveredLinesInDiff;
    }

    private function getStringArgument(string $key): string
    {
        $value = $this->argument($key);
        if (is_string($value)) {
            return $value;
        }
        throw new InvalidArgumentException("param $key should be a single string");
    }
}
