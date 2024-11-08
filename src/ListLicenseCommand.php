<?php

namespace ListLicenses;

use stdClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListLicenseCommand extends Command
{
    protected static string $defaultName = 'license:list';
    protected static string $defaultColumns = 'name,homepage,description,license,version,time';
    private bool $debug;
    private string $format;
    private ?array $columns;
    private ?string $outfile;
    private OutputInterface $output;

    protected function configure(): void
    {
        $this
            ->setDescription('list all packages and their licenses used in the composer.lock file')
            ->setName(self::$defaultName)
            ->setDefinition(
                new InputDefinition([
                    new InputArgument(
                        'composer.lock', InputArgument::OPTIONAL,
                        'The composer.lock file for which you want to list all packages and licenses
                    Defaults to composer.lock'
                    ),
                    new InputOption('debug', 'd', InputOption::VALUE_NONE, 'Show debug information'),
                    new InputOption(
                        'format',
                        'f',
                        InputOption::VALUE_OPTIONAL,
                        'Output format: json, xml, csv, yaml, defaults to csv'
                    ),
                    new InputOption(
                        'columns',
                        'c',
                        InputOption::VALUE_OPTIONAL,
                        'Comma separated list of columns to output, defaults to all: '.self::$defaultColumns
                    ),
                    new InputOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output file, defaults to stdout'),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->debug = $input->getOption('debug');
        $this->format = $this->validateOutputFormat($input->getOption('format'));
        $this->columns = $this->validateColumns($input->getOption('columns'));
        $this->outfile = $input->getOption('output');
        $this->output = $output;

        $filename = $this->getComposerLockFilename($input);
        $this->output->writeln("Analyzing <info>$filename</info>");
        $obj = $this->readInput($filename);
        $text = $this->convert($obj);
        $this->writeOutput($text);

        return Command::SUCCESS;
    }

    private function validateOutputFormat(?string $format): string
    {
        if (is_null($format)) {
            return 'csv';
        }
        if (in_array($format, ['csv', 'xml', 'yaml', 'json'])) {
            return $format;
        }
        return 'csv';
    }

    private function getComposerLockFilename(InputInterface $input): mixed
    {
        $filename = $input->getArgument('composer.lock');
        if (!$filename) {
            $filename = "composer.lock";
        }
        return $filename;
    }

    private function getComposerLockContents(mixed $text): string
    {
        $string = @file_get_contents($text);
        if (!$string) {
            throw new RuntimeException(sprintf('Cannot open "%s"', $text));
        }
        return $string;
    }

    private function convertComposerContentToObject(string $string, string $text): stdClass
    {
        $obj = @json_decode($string);
        if (!$obj) {
            throw new RuntimeException(sprintf('The file "%s" does not appear to be JSON', $text));
        }
        if (!isset($obj->packages)) {
            throw new RuntimeException(sprintf('The file "%s" does not appear to a composer package', $text));
        }
        return $obj;
    }

    private function addPackages(stdClass $obj): array
    {
        $packages = [];
        foreach ($obj->packages as $package) {
            if ($this->debug) {
                $this->output->writeln("<info>Adding name: $package->name</info>");
            }
            $packages[] = new Package($package);
        }
        return $packages;
    }

    private function serializePackages(array $packages): string
    {
        //$packages = $this->filter($packages);
        $serial = new Serial($this->columns);
        return match ($this->format) {
            'json' => $serial->json($packages),
            'xml' => $serial->xml($packages),
            'csv' => $serial->csv($packages),
            'yaml' => $serial->yaml($packages),
            default => throw new RuntimeException(sprintf('Unknown format "%s"', $this->format)),
        };
    }

    private function readInput(mixed $filename): stdClass
    {
        $json = $this->getComposerLockContents($filename);
        return $this->convertComposerContentToObject($json, $filename);
    }

    private function convert(stdClass $obj): string
    {
        $packages = $this->addPackages($obj);
        return $this->serializePackages($packages);
    }

    private function writeOutput(string $text): void
    {
        if (is_null($this->outfile)) {
            print $text;
            return;
        }

        if (!file_put_contents($this->outfile, $text)) {
            throw new RuntimeException(sprintf('Cannot write to "%s"', $this->outfile));
        }
    }

    private function validateColumns(?string $columnString): array
    {
        if (is_null($columnString)) {
            return explode(",", self::$defaultColumns);
        }
        $columns = explode(",", $columnString);
        $expected = explode(",", self::$defaultColumns);
        foreach ($columns as $column) {
            if (!in_array($column, $expected)) {
                throw new RuntimeException(sprintf('The column "%s" is not recognized', $column));
            }
        }
        return $columns;
    }
}
