<?php

namespace ListLicenses;

use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class Serial
{

    private array $encoders;
    /** @var ObjectNormalizer[] */
    private array $normalizers;
    private Serializer $serializer;

    public function __construct(private readonly array $columns)
    {
        $this->encoders = [
            new XmlEncoder(),
            new JsonEncoder(),
            new CsvEncoder(),
            new YamlEncoder(),
        ];
        $this->normalizers = [new PackageNormalizer($this->columns)];
        $this->serializer = new Serializer($this->normalizers, $this->encoders);
    }

    public function json(mixed $object): string
    {
        return $this->serializer->serialize($object, 'json');
    }

    public function xml(mixed $object): string
    {
        return $this->serializer->serialize($object, 'xml');
    }

    public function csv(mixed $object): string
    {
        return $this->serializer->serialize($object, 'csv');
    }

    public function yaml(mixed $object): string
    {
        return $this->serializer->serialize($object, 'yaml');
    }
}
