<?php
/**
 * This file is part of the binary-writer project.
 *
 * PHP 8.1 | 8.2 | 8.3
 *
 * Copyright Alexandre Tranchant <alexandre.tranchant@gmail.com> 2024
 * Copyright Longitude One 2024
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

declare(strict_types=1);

namespace LongitudeOne\SpatialWriter\Strategy;

use LongitudeOne\SpatialTypes\Enum\TypeEnum;
use LongitudeOne\SpatialTypes\Interfaces\LineStringInterface;
use LongitudeOne\SpatialTypes\Interfaces\MultiLineStringInterface;
use LongitudeOne\SpatialTypes\Interfaces\MultiPointInterface;
use LongitudeOne\SpatialTypes\Interfaces\MultiPolygonInterface;
use LongitudeOne\SpatialTypes\Interfaces\PointInterface;
use LongitudeOne\SpatialTypes\Interfaces\PolygonInterface;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;
use LongitudeOne\SpatialWriter\Exception\UnsupportedSpatialInterfaceException;
use LongitudeOne\SpatialWriter\Exception\UnsupportedSpatialTypeException;

/**
 * Well-Known binary adapter.
 *
 * This class is responsible for converting a spatial interface to its well-known binary representation.
 */
class WkbBinaryStrategy implements BinaryStrategyInterface
{
    /**
     * Convert a spatial interface to its well-known binary representation.
     *
     * Well-Known binary representation is a standard binary format for representing simple and complex geometries,
     * defined by the Open Geospatial Consortium (OGC).
     *
     * @see https://libgeos.org/specifications/wkb/#standard-wkb
     *
     * @param SpatialInterface $spatial the spatial interface to convert into EWKB format
     *
     * @return string a binary string representing the spatial interface in EWKB format
     *
     * @throws UnsupportedSpatialTypeException      when the spatial type is not supported
     * @throws UnsupportedSpatialInterfaceException when the spatial interface is not supported
     */
    public function executeStrategy(SpatialInterface $spatial): string
    {
        $wkb = $this->writeByteOrder();
        $wkb .= $this->writeType($spatial);
        $wkb .= $this->writeCoordinates($spatial);

        return $wkb;
    }

    /**
     * Write the byte order.
     *
     * The byte order is always little endian.
     *
     * @return string a binary string representing the byte order in little endian
     */
    private function writeByteOrder(): string
    {
        // We always write into little endian
        return pack('C', 1);
    }

    /**
     * Write the coordinates.
     *
     * @param SpatialInterface $spatial the spatial interface to write
     *
     * @return string a binary string representing the coordinates
     *
     * @throws UnsupportedSpatialInterfaceException when the spatial interface is not supported
     * @throws UnsupportedSpatialTypeException      when the spatial type is not supported
     */
    private function writeCoordinates(SpatialInterface $spatial): string
    {
        return match (true) {
            $spatial instanceof PointInterface => $this->writePoint($spatial),
            $spatial instanceof LineStringInterface => $this->writeLineString($spatial),
            $spatial instanceof PolygonInterface => $this->writePolygon($spatial),
            $spatial instanceof MultiPointInterface => $this->writeMultiPoint($spatial),
            $spatial instanceof MultiLineStringInterface => $this->writeMultiLineString($spatial),
            $spatial instanceof MultiPolygonInterface => $this->writeMultiPolygon($spatial),

            default => throw new UnsupportedSpatialInterfaceException($spatial::class),
        };
    }

    /**
     * Write a line string.
     *
     * @param LineStringInterface $lineString the line string to write
     *
     * @return string a binary string representing the line string
     */
    private function writeLineString(LineStringInterface $lineString): string
    {
        $wkb = pack('L', count($lineString->getPoints()));
        foreach ($lineString->getPoints() as $point) {
            $wkb .= $this->writePoint($point);
        }

        return $wkb;
    }

    /**
     * Write a multi-line string.
     *
     * @param MultiLineStringInterface $multiLineString the multi-line string to write
     *
     * @return string a binary string representing the multi-line string
     *
     * @throws UnsupportedSpatialInterfaceException when the spatial interface is not supported
     * @throws UnsupportedSpatialTypeException      when the spatial type is not supported
     */
    private function writeMultiLineString(MultiLineStringInterface $multiLineString): string
    {
        $wkb = pack('L', count($multiLineString->getLineStrings()));
        foreach ($multiLineString->getLineStrings() as $lineString) {
            $wkb .= $this->executeStrategy($lineString);
        }

        return $wkb;
    }

    /**
     * Write a multipoint.
     *
     * @param MultiPointInterface $multiPoint the multipoint to write
     *
     * @return string a binary string representing the multipoint
     *
     * @throws UnsupportedSpatialTypeException      when the spatial type is not supported
     * @throws UnsupportedSpatialInterfaceException when the spatial interface is not supported
     */
    private function writeMultiPoint(MultiPointInterface $multiPoint): string
    {
        $wkb = pack('L', count($multiPoint->getPoints()));

        foreach ($multiPoint->getPoints() as $point) {
            $wkb .= $this->executeStrategy($point);
        }

        return $wkb;
    }

    /**
     * Write a multipolygon.
     *
     * @param MultiPolygonInterface $multiPolygon the multipolygon to write
     *
     * @return string a binary string representing the multipolygon
     *
     * @throws UnsupportedSpatialTypeException      when the spatial type is not supported
     * @throws UnsupportedSpatialInterfaceException when the spatial interface is not supported
     */
    private function writeMultiPolygon(MultiPolygonInterface $multiPolygon): string
    {
        $wkb = pack('L', count($multiPolygon->getPolygons()));
        foreach ($multiPolygon->getPolygons() as $polygon) {
            $wkb .= $this->executeStrategy($polygon);
        }

        return $wkb;
    }

    /**
     * Write a point.
     *
     * @param PointInterface $point the point to write
     *
     * @return string a binary string representing the point
     */
    private function writePoint(PointInterface $point): string
    {
        return pack('dd', $point->getX(), $point->getY());
    }

    /**
     * Write a polygon.
     *
     * @param PolygonInterface $polygon the polygon to write
     *
     * @return string a binary string representing the polygon
     */
    private function writePolygon(PolygonInterface $polygon): string
    {
        $wkb = pack('L', count($polygon->getRings()));

        foreach ($polygon->getRings() as $ring) {
            $wkb .= $this->writeLineString($ring);
        }

        return $wkb;
    }

    /**
     * Write the type.
     *
     * @param SpatialInterface $spatial the spatial interface to write
     *
     * @return string a binary string representing the type
     *
     * @throws UnsupportedSpatialTypeException when the spatial type is not supported
     */
    private function writeType(SpatialInterface $spatial): string
    {
        return match ($spatial->getType()) {
            TypeEnum::POINT->value => pack('L', 1),
            TypeEnum::LINESTRING->value => pack('L', 2),
            TypeEnum::POLYGON->value => pack('L', 3),
            TypeEnum::MULTIPOINT->value => pack('L', 4),
            TypeEnum::MULTILINESTRING->value => pack('L', 5),
            TypeEnum::MULTIPOLYGON->value => pack('L', 6),
            TypeEnum::COLLECTION->value => pack('L', 7),
            default => throw new UnsupportedSpatialTypeException(sprintf('WKB adapter does not support spatial type %s', $spatial->getType()))
        };
    }
}
