<?php

namespace ListLicenses;

use stdClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
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
    private OutputInterface $error;

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
        $this->error = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;

        $this->debug = $input->getOption('debug');
        $this->format = $this->validateOutputFormat($input->getOption('format'));
        $this->columns = $this->validateColumns($input->getOption('columns'));
        $this->outfile = $input->getOption('output');
        $this->output = $output;

        $filename = $this->getComposerLockFilename($input);
        $this->error->writeln("Analyzing <info>$filename</info>");
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

        throw Exceptions\ListLicenseCommand::wrongFormat($format);
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
                throw Exceptions\ListLicenseCommand::unknownColumn($column);
            }
        }
        return $columns;
    }

    private function getComposerLockFilename(InputInterface $input): mixed
    {
        $filename = $input->getArgument('composer.lock');
        if (!$filename) {
            $filename = "composer.lock";
        }
        return $filename;
    }

    private function readInput(mixed $filename): stdClass
    {
        $json = $this->getComposerLockContents($filename);
        return $this->convertComposerContentToObject($json, $filename);
    }

    private function getComposerLockContents(mixed $text): string
    {
        $string = @file_get_contents($text);
        if (!$string) {
            throw Exceptions\ListLicenseCommand::cannotOpen($text);
        }
        return $string;
    }

    private function convertComposerContentToObject(string $string, string $filename): stdClass
    {
        $obj = @json_decode($string);
        if (!$obj) {
            throw Exceptions\ListLicenseCommand::notJson($filename);
        }
        if (!isset($obj->packages)) {
            throw Exceptions\ListLicenseCommand::notComposer($filename);
        }
        return $obj;
    }

    private function convert(stdClass $obj): string
    {
        $packages = $this->addPackages($obj);
        return $this->serializePackages($packages);
    }

    private function addPackages(stdClass $obj): array
    {
        $packages = [];
        foreach ($obj->packages as $package) {
            if ($this->debug) {
                $this->error->writeln("<info>Adding name: $package->name</info>");
            }
            $packages[] = new Package($package);
        }
        return $packages;
    }

    private function serializePackages(array $packages): string
    {
        $serial = new Serial($this->columns);
        return match ($this->format) {
            'json' => $serial->json($packages),
            'xml' => $serial->xml($packages),
            'csv' => $serial->csv($packages),
            'yaml' => $serial->yaml($packages),
            default => throw Exceptions\ListLicenseCommand::unknownFormat($this->format)
        };
    }

    private function writeOutput(string $text): void
    {
        if (is_null($this->outfile)) {
            $this->output->writeln($text);
            return;
        }

        if (!file_put_contents($this->outfile, $text)) {
            throw Exceptions\ListLicenseCommand::cannotWrite($this->outfile);
        }
    }
}
