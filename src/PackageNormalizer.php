<?php

namespace ListLicenses;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

readonly class PackageNormalizer implements NormalizerInterface
{
    public function __construct(
        private array $columns
    ) {
    }

    public function normalize(
        mixed $object,
        ?string $format = null,
        array $context = []
    ): array {
        // TODO: Implement normalize() method.
        $response = [];
        foreach ($this->columns as $column) {
            $response[$column] = $object->$column;
        }
        return $response;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Package;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Package::class => true,
        ];
    }
}
