<?php

namespace ListLicenses\Exceptions;

use RuntimeException;

class ListLicenseCommand extends RuntimeException
{
    public static function notJson(string $filename): ListLicenseCommand
    {
        return new self(sprintf('"%s" is not a valid JSON file.', $filename));
    }

    public static function notComposer(string $filename): ListLicenseCommand
    {
        return new self(sprintf('"%s" is not a composer.json file.', $filename));
    }

    public static function unknownFormat(string $format): ListLicenseCommand
    {
        return new self(sprintf('"%s" is not a valid format.', $format));
    }

    public static function cannotWrite(?string $outfile): ListLicenseCommand
    {
        return new self(sprintf('"%s" is not writable.', $outfile));
    }

    public static function unknownColumn(string $column): ListLicenseCommand
    {
        return new self(sprintf('"%s" is not a valid column name.', $column));
    }

    public static function cannotOpen(string $filename): ListLicenseCommand
    {
        return new self(sprintf('"%s" is not a valid file.', $filename));
    }

    public static function wrongFormat(string $format): ListLicenseCommand
    {
        return new self(sprintf('"%s" is not a valid format.', $format));
    }
}
