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

use LongitudeOne\SpatialTypes\Interfaces\CollectionInterface;
use LongitudeOne\SpatialTypes\Interfaces\LineStringInterface;
use LongitudeOne\SpatialTypes\Interfaces\MultiLineStringInterface;
use LongitudeOne\SpatialTypes\Interfaces\MultiPointInterface;
use LongitudeOne\SpatialTypes\Interfaces\MultiPolygonInterface;
use LongitudeOne\SpatialTypes\Interfaces\PointInterface;
use LongitudeOne\SpatialTypes\Interfaces\PolygonInterface;
use LongitudeOne\SpatialTypes\Interfaces\PolyhedralSurfaceInterface;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;
use LongitudeOne\SpatialWriter\Exception\UnsupportedSpatialInterfaceException;
use LongitudeOne\SpatialWriter\Strategy\Wkt\WktTypeEncoder;

/**
 * Writes WKT in coordinate order X Y [Z] [M], without SRID metadata.
 */
class WktTextStrategy implements StrategyInterface
{
    /**
     * Convert a spatial object to Well-Known Text, preserving its dimensions.
     *
     * @see https://libgeos.org/specifications/wkt/
     *
     * @param SpatialInterface $spatial the spatial object to convert
     *
     * @throws UnsupportedSpatialInterfaceException when the spatial interface is not supported
     * @throws \JsonException                       when a coordinate is not finite
     */
    public function executeStrategy(SpatialInterface $spatial): string
    {
        return $this->writeGeometry($spatial);
    }

    /**
     * Write a geometry body, including its surrounding parentheses.
     *
     * @param SpatialInterface $spatial the spatial object to convert
     */
    private function writeBody(SpatialInterface $spatial): string
    {
        if ($spatial->isEmpty()) {
            return 'EMPTY';
        }

        return '('.$this->writeCoordinates($spatial).')';
    }

    /**
     * Write a coordinate without locale-dependent decimal separators.
     *
     * JSON uses PHP's serialize_precision setting, whose default (-1) preserves
     * floating-point values using their shortest round-trip representation.
     *
     * @param float|int $coordinate the ordinate to format
     *
     * @throws \JsonException when the coordinate is not finite
     */
    private function writeCoordinate(float|int $coordinate): string
    {
        return json_encode($coordinate, JSON_THROW_ON_ERROR);
    }

    /**
     * Write the contents of a nonempty geometry body.
     *
     * @param SpatialInterface $spatial the spatial object to convert
     */
    private function writeCoordinates(SpatialInterface $spatial): string
    {
        return match (true) {
            $spatial instanceof PointInterface => $this->writePoint($spatial),
            $spatial instanceof LineStringInterface => implode(', ', array_map($this->writePoint(...), $spatial->getPoints())),
            $spatial instanceof PolygonInterface => $this->writeMembers($spatial->getRings()),
            $spatial instanceof MultiPointInterface => $this->writeMembers($spatial->getPoints()),
            $spatial instanceof MultiLineStringInterface => $this->writeMembers($spatial->getLineStrings()),
            $spatial instanceof MultiPolygonInterface => $this->writeMembers($spatial->getPolygons()),
            $spatial instanceof PolyhedralSurfaceInterface => $this->writeMembers($spatial->getPatches()),
            $spatial instanceof CollectionInterface => implode(', ', array_map($this->writeGeometry(...), $spatial->getElements())),
            default => throw new UnsupportedSpatialInterfaceException($spatial::class),
        };
    }

    /**
     * Write a complete WKT geometry, keeping collection members free of prefixes.
     *
     * @param SpatialInterface $spatial the spatial object to convert
     */
    private function writeGeometry(SpatialInterface $spatial): string
    {
        return (new WktTypeEncoder())->writeType($spatial).' '.$this->writeBody($spatial);
    }

    /**
     * Write the bodies of homogeneous geometry members.
     *
     * @param SpatialInterface[] $members the members to convert
     */
    private function writeMembers(array $members): string
    {
        return implode(', ', array_map($this->writeBody(...), $members));
    }

    /**
     * Write the ordinates of a nonempty point in its declared dimension.
     *
     * @param PointInterface $point the point to convert
     */
    private function writePoint(PointInterface $point): string
    {
        return implode(' ', array_map($this->writeCoordinate(...), $point->toArray()));
    }
}
