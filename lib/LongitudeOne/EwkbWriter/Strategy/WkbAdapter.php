<?php
/**
 * This file is part of the ewkb-writer project.
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

namespace LongitudeOne\EwkbWriter\Strategy;

use LongitudeOne\EwkbWriter\Exception\UnsupportedSpatialInterfaceException;
use LongitudeOne\EwkbWriter\Exception\UnsupportedSpatialTypeException;
use LongitudeOne\Spatial\PHP\Types\LineStringInterface;
use LongitudeOne\Spatial\PHP\Types\MultiLineStringInterface;
use LongitudeOne\Spatial\PHP\Types\MultiPointInterface;
use LongitudeOne\Spatial\PHP\Types\MultiPolygonInterface;
use LongitudeOne\Spatial\PHP\Types\PointInterface;
use LongitudeOne\Spatial\PHP\Types\PolygonInterface;
use LongitudeOne\Spatial\PHP\Types\SpatialInterface;

/**
 * Well-Known binary adapter.
 *
 * This class is responsible for converting a spatial interface to its well-known binary representation.
 */
class WkbAdapter implements AdapterInterface
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
    public function convert(SpatialInterface $spatial): string
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
            $wkb .= $this->convert($lineString);
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
            $wkb .= $this->convert($point);
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
            $wkb .= $this->convert($polygon);
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
            SpatialInterface::POINT => pack('L', 1),
            SpatialInterface::LINESTRING => pack('L', 2),
            SpatialInterface::POLYGON => pack('L', 3),
            SpatialInterface::MULTIPOINT => pack('L', 4),
            SpatialInterface::MULTILINESTRING => pack('L', 5),
            SpatialInterface::MULTIPOLYGON => pack('L', 6),
            // TODO GEOMETRYCOLLECTION pack 7
            default => throw new UnsupportedSpatialTypeException(sprintf('WKB adapter does not support spatial type %s', $spatial->getType()))
        };
    }
}
