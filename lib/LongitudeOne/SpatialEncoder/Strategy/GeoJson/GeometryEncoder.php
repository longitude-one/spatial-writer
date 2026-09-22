<?php
/**
 * This file is part of the spatial-encoder project.
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

namespace LongitudeOne\SpatialEncoder\Strategy\GeoJson;

use LongitudeOne\Core\Enum\GeometryTypeEnum;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedGeometryStructureException;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialInterfaceException;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialTypeException;
use LongitudeOne\SpatialTypes\Interfaces\CollectionInterface;
use LongitudeOne\SpatialTypes\Interfaces\LineStringInterface;
use LongitudeOne\SpatialTypes\Interfaces\MultiLineStringInterface;
use LongitudeOne\SpatialTypes\Interfaces\MultiPointInterface;
use LongitudeOne\SpatialTypes\Interfaces\MultiPolygonInterface;
use LongitudeOne\SpatialTypes\Interfaces\PointInterface;
use LongitudeOne\SpatialTypes\Interfaces\PolygonInterface;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;

/**
 * Builds GeoJSON geometry objects without changing their coordinates.
 *
 * @internal
 */
final class GeometryEncoder
{
    /**
     * Encode a supported geometry using its corresponding spatial interface.
     *
     * @param SpatialInterface $spatial the geometry to encode
     *
     * @return array{type: string, coordinates?: array<mixed>, geometries?: list<array<string, mixed>>}
     */
    public function encode(SpatialInterface $spatial): array
    {
        return match ($spatial->getType()) {
            GeometryTypeEnum::GEOMETRYCOLLECTION => $this->encodeCollection($spatial),
            GeometryTypeEnum::POINT => $this->encodePoint($spatial),
            GeometryTypeEnum::LINESTRING => $this->encodeLineString($spatial),
            GeometryTypeEnum::MULTIPOINT => $this->encodeMultiPoint($spatial),
            GeometryTypeEnum::MULTILINESTRING => $this->encodeMultiLineString($spatial),
            GeometryTypeEnum::POLYGON => $this->encodePolygon($spatial),
            GeometryTypeEnum::MULTIPOLYGON => $this->encodeMultiPolygon($spatial),
            default => throw new UnsupportedSpatialTypeException('This GeoJSON strategy does not support '.$spatial->getType()->name.'.'),
        };
    }

    /**
     * Recursively retain every member, including nested and EMPTY geometries.
     *
     * @param SpatialInterface $spatial the collection to encode
     *
     * @return array{type: string, geometries: list<array<string, mixed>>}
     */
    private function encodeCollection(SpatialInterface $spatial): array
    {
        if (!$spatial instanceof CollectionInterface) {
            throw new UnsupportedSpatialInterfaceException($spatial::class);
        }

        $geometries = [];
        foreach ($spatial->getElements() as $element) {
            $geometries[] = $this->encode($element);
        }

        return ['type' => 'GeometryCollection', 'geometries' => $geometries];
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
     * Preserve polygon membership using the shared Polygon orientation policy.
     *
     * @param SpatialInterface $spatial the multi-polygon to encode
     *
     * @return array{type: string, coordinates: (float|int)[][][][]}
     */
    private function encodeMultiPolygon(SpatialInterface $spatial): array
    {
        if (!$spatial instanceof MultiPolygonInterface) {
            throw new UnsupportedSpatialInterfaceException($spatial::class);
        }

        $coordinates = [];
        foreach ($spatial->getPolygons() as $polygon) {
            $rings = $this->encodePolygon($polygon)['coordinates'];
            if ([] === $rings) {
                throw new UnsupportedGeometryStructureException('A GeoJSON MultiPolygon cannot contain an EMPTY Polygon.');
            }

            $coordinates[] = $rings;
        }

        return ['type' => 'MultiPolygon', 'coordinates' => $coordinates];
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

    /**
     * Preserve every ring and ordinate after checking the approved XY orientation.
     *
     * @param SpatialInterface $spatial the polygon to encode
     *
     * @return array{type: string, coordinates: (float|int)[][][]}
     */
    private function encodePolygon(SpatialInterface $spatial): array
    {
        if (!$spatial instanceof PolygonInterface) {
            throw new UnsupportedSpatialInterfaceException($spatial::class);
        }

        $coordinates = $spatial->toArray();
        foreach ($coordinates as $index => $ring) {
            $area = $this->signedRingArea($ring);
            if (0.0 === $area || (0 === $index && $area < 0) || (0 !== $index && $area > 0)) {
                throw new UnsupportedGeometryStructureException('GeoJSON requires counterclockwise exterior rings and clockwise interior rings with nonzero XY signed area.');
            }
        }

        return ['type' => 'Polygon', 'coordinates' => $coordinates];
    }

    /**
     * Compute twice the signed XY area, translated to the first vertex for precision.
     *
     * The model already enforces ring length and closure. This is not a
     * simplicity or topology check. Non-finite ordinates retain the JSON failure path.
     *
     * @param (float|int)[][] $ring the positions of a closed ring
     */
    private function signedRingArea(array $ring): float
    {
        $area = 0.0;
        $origin = $ring[0];
        $previous = $origin;
        foreach ($ring as $position) {
            $area += ($previous[0] - $origin[0]) * ($position[1] - $origin[1])
                - ($position[0] - $origin[0]) * ($previous[1] - $origin[1]);
            $previous = $position;
        }

        return $area;
    }
}
