<?php

namespace ListLicenses;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

readonly class PackageNormalizer implements NormalizerInterface
{
    public function __construct(
        private array $columns
    ) {
    }

    /**
     * Called by Serializer->normalize to normalize the Package into the response
     * @param  mixed  $data
     * @param  string|null  $format
     * @param  array  $context
     * @return array
     */
    public function normalize(
        mixed $data,
        ?string $format = null,
        array $context = []
    ): array {
        $response = [];
        foreach ($this->columns as $column) {
            $response[$column] = $data->$column;
        }
        return $response;
    }

    /**
     * Called by Serializer->getNormalizer to validate that $data is of the correct instance
     * @param  mixed  $data
     * @param  string|null  $format
     * @param  array  $context
     * @return bool
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Package;
    }

    /**
     * Called by Serializer->getNormalizer to discover the supported types
     * @param  string|null  $format
     * @return true[]
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            Package::class => true,
        ];
    }
}
