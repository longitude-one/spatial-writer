<?php
/**
 * This file is part of the binary-writer project.
 *
 * PHP 8.4 | 8.5
 *
 * Copyright Alexandre Tranchant <alexandre.tranchant@gmail.com> 2024-2026
 * Copyright Longitude One 2024-2026
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

declare(strict_types=1);

namespace LongitudeOne\SpatialWriter\Strategy;

use LongitudeOne\Core\Enum\GeometryTypeEnum;
use LongitudeOne\SpatialTypes\Interfaces\PointInterface;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;
use LongitudeOne\SpatialWriter\Exception\JsonEncodingException;
use LongitudeOne\SpatialWriter\Exception\UnsupportedDimensionException;
use LongitudeOne\SpatialWriter\Exception\UnsupportedSpatialInterfaceException;
use LongitudeOne\SpatialWriter\Exception\UnsupportedSpatialTypeException;

/**
 * Encodes supported geometries using RFC 7946 without transforming coordinates.
 */
class GeoJsonStrategy implements StrategyInterface
{
    /**
     * Encode a geometry, preserving its coordinates and omitting reference metadata.
     *
     * @param SpatialInterface $spatial the geometry to encode
     *
     * @throws UnsupportedDimensionException        when the geometry declares M
     * @throws UnsupportedSpatialTypeException      when the type is not supported
     * @throws UnsupportedSpatialInterfaceException when a Point interface is missing
     * @throws JsonEncodingException                when JSON encoding fails
     */
    public function executeStrategy(SpatialInterface $spatial): string
    {
        if ($spatial->hasM()) {
            throw new UnsupportedDimensionException('GeoJSON does not support measured coordinates.');
        }

        if (GeometryTypeEnum::POINT !== $spatial->getType()) {
            throw new UnsupportedSpatialTypeException('This GeoJSON strategy does not support '.$spatial->getType()->name.'.');
        }

        if (!$spatial instanceof PointInterface) {
            throw new UnsupportedSpatialInterfaceException($spatial::class);
        }

        try {
            return json_encode(['type' => 'Point', 'coordinates' => $spatial->toArray()], JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new JsonEncodingException('Unable to encode the geometry as GeoJSON.', previous: $exception);
        }
    }
}
