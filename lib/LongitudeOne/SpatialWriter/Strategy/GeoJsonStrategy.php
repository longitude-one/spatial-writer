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
use LongitudeOne\SpatialTypes\Interfaces\LineStringInterface;
use LongitudeOne\SpatialTypes\Interfaces\MultiLineStringInterface;
use LongitudeOne\SpatialTypes\Interfaces\MultiPointInterface;
use LongitudeOne\SpatialTypes\Interfaces\PointInterface;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;
use LongitudeOne\SpatialWriter\Exception\JsonEncodingException;
use LongitudeOne\SpatialWriter\Exception\UnsupportedDimensionException;
use LongitudeOne\SpatialWriter\Exception\UnsupportedGeometryStructureException;
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
     * @throws UnsupportedDimensionException         when the geometry declares M
     * @throws UnsupportedSpatialTypeException       when the type is not supported
     * @throws UnsupportedSpatialInterfaceException  when the geometry interface is missing
     * @throws UnsupportedGeometryStructureException when the geometry has an incompatible structure
     * @throws JsonEncodingException                 when JSON encoding fails
     */
    public function executeStrategy(SpatialInterface $spatial): string
    {
        if ($spatial->hasM()) {
            throw new UnsupportedDimensionException('GeoJSON does not support measured coordinates.');
        }

        $geometry = match ($spatial->getType()) {
            GeometryTypeEnum::POINT => $this->encodePoint($spatial),
            GeometryTypeEnum::LINESTRING => $this->encodeLineString($spatial),
            GeometryTypeEnum::MULTIPOINT => $this->encodeMultiPoint($spatial),
            GeometryTypeEnum::MULTILINESTRING => $this->encodeMultiLineString($spatial),
            default => throw new UnsupportedSpatialTypeException('This GeoJSON strategy does not support '.$spatial->getType()->name.'.'),
        };

        try {
            return json_encode($geometry, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new JsonEncodingException('Unable to encode the geometry as GeoJSON.', previous: $exception);
        }
    }

    /**
     * Preserve a line's positions, allowing EMPTY but rejecting singleton lines.
     *
     * @param SpatialInterface $spatial the line string to encode
     *
     * @return array{type: string, coordinates: (float|int)[][]}
     */
    private function encodeLineString(SpatialInterface $spatial): array
    {
        if (!$spatial instanceof LineStringInterface) {
            throw new UnsupportedSpatialInterfaceException($spatial::class);
        }

        $coordinates = $spatial->toArray();
        if (1 === count($coordinates)) {
            throw new UnsupportedGeometryStructureException('A non-empty GeoJSON LineString requires at least two positions.');
        }

        return ['type' => 'LineString', 'coordinates' => $coordinates];
    }

    /**
     * Preserve line membership and order, rejecting EMPTY and singleton members.
     *
     * @param SpatialInterface $spatial the multi-line string to encode
     *
     * @return array{type: string, coordinates: (float|int)[][][]}
     */
    private function encodeMultiLineString(SpatialInterface $spatial): array
    {
        if (!$spatial instanceof MultiLineStringInterface) {
            throw new UnsupportedSpatialInterfaceException($spatial::class);
        }

        $coordinates = $spatial->toArray();
        foreach ($coordinates as $line) {
            if (count($line) < 2) {
                throw new UnsupportedGeometryStructureException('Every GeoJSON MultiLineString member requires at least two positions.');
            }
        }

        return ['type' => 'MultiLineString', 'coordinates' => $coordinates];
    }

    /**
     * Preserve all positions, rejecting EMPTY members without omitting them.
     *
     * @param SpatialInterface $spatial the multi-point to encode
     *
     * @return array{type: string, coordinates: (float|int)[][]}
     */
    private function encodeMultiPoint(SpatialInterface $spatial): array
    {
        if (!$spatial instanceof MultiPointInterface) {
            throw new UnsupportedSpatialInterfaceException($spatial::class);
        }

        $coordinates = $spatial->toArray();
        if (in_array([], $coordinates, true)) {
            throw new UnsupportedGeometryStructureException('A GeoJSON MultiPoint cannot contain an EMPTY Point.');
        }

        return ['type' => 'MultiPoint', 'coordinates' => $coordinates];
    }

    /**
     * Preserve a point's coordinates.
     *
     * @param SpatialInterface $spatial the point to encode
     *
     * @return array{type: string, coordinates: (float|int)[]}
     */
    private function encodePoint(SpatialInterface $spatial): array
    {
        if (!$spatial instanceof PointInterface) {
            throw new UnsupportedSpatialInterfaceException($spatial::class);
        }

        return ['type' => 'Point', 'coordinates' => $spatial->toArray()];
    }
}
